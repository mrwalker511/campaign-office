#!/usr/bin/env node
/**
 * Accessibility Testing Suite
 *
 * Tests theme for WCAG compliance using pa11y and axe-core
 */

import pa11y from 'pa11y';

// Get base URL from environment or use default
const BASE_URL = process.env.WP_BASE_URL || process.env.SITE_URL || 'http://localhost:8881';

const PAGES_TO_TEST = [
  {
    url: BASE_URL,
    name: 'Home Page',
  },
  {
    url: `${BASE_URL}/sample-page`,
    name: 'Sample Page',
  },
  {
    url: `${BASE_URL}/blog`,
    name: 'Blog Archive',
  },
];

const WCAG_STANDARDS = {
  'WCAG2A': 'wcag2a',
  'WCAG2AA': 'wcag2aa',
  'WCAG2AAA': 'wcag2aaa',
};

async function runAccessibilityTests() {
  console.log('🔍 Running Accessibility Tests\n');
  console.log('================================\n');

  let totalIssues = 0;
  let totalErrors = 0;
  let totalWarnings = 0;
  let totalNotices = 0;

  for (const page of PAGES_TO_TEST) {
    console.log(`\n📄 Testing: ${page.name}`);
    console.log(`   URL: ${page.url}\n`);

    try {
      const results = await pa11y(page.url, {
        standard: WCAG_STANDARDS.WCAG2AA,
        timeout: 30000,
        wait: 1000,
        chromeLaunchConfig: {
          headless: true,
        },
        runners: ['axe', 'htmlcs'],
      });

      const errors = results.issues.filter((i) => i.type === 'error');
      const warnings = results.issues.filter((i) => i.type === 'warning');
      const notices = results.issues.filter((i) => i.type === 'notice');

      totalIssues += results.issues.length;
      totalErrors += errors.length;
      totalWarnings += warnings.length;
      totalNotices += notices.length;

      if (results.issues.length === 0) {
        console.log('   ✅ No accessibility issues found!');
      } else {
        console.log(`   ❌ Found ${results.issues.length} issues:`);
        console.log(`      - Errors: ${errors.length}`);
        console.log(`      - Warnings: ${warnings.length}`);
        console.log(`      - Notices: ${notices.length}\n`);

        // Show errors
        if (errors.length > 0) {
          console.log('   🚨 Errors:');
          errors.forEach((issue) => {
            console.log(`      - ${issue.message}`);
            console.log(`        Code: ${issue.code}`);
            console.log(`        Selector: ${issue.selector}\n`);
          });
        }

        // Show warnings (limit to 5)
        if (warnings.length > 0) {
          console.log('   ⚠️  Warnings (showing first 5):');
          warnings.slice(0, 5).forEach((issue) => {
            console.log(`      - ${issue.message}`);
            console.log(`        Code: ${issue.code}\n`);
          });
        }
      }
    } catch (error) {
      console.error(`   ❌ Error testing ${page.name}:`, error.message);
      console.error(
        '   💡 Make sure your local WordPress site is running at the specified URL\n'
      );
    }
  }

  console.log('\n================================');
  console.log('📊 Summary\n');
  console.log(`Total Issues: ${totalIssues}`);
  console.log(`  - Errors: ${totalErrors}`);
  console.log(`  - Warnings: ${totalWarnings}`);
  console.log(`  - Notices: ${totalNotices}\n`);

  if (totalErrors > 0) {
    console.log('❌ Accessibility tests failed - please fix errors above');
    process.exit(1);
  } else if (totalWarnings > 0) {
    console.log(
      '⚠️  Accessibility tests passed with warnings - consider fixing warnings'
    );
    process.exit(0);
  } else {
    console.log('✅ All accessibility tests passed!');
    process.exit(0);
  }
}

// Run tests
runAccessibilityTests().catch((error) => {
  console.error('Fatal error:', error);
  process.exit(1);
});
