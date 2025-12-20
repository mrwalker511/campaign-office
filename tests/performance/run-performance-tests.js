#!/usr/bin/env node
/**
 * Performance Testing Suite
 *
 * Tests theme performance using Lighthouse
 */

import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'fs';
import path from 'path';

const PAGES_TO_TEST = [
  {
    url: 'http://localhost:8888',
    name: 'Home Page',
  },
  {
    url: 'http://localhost:8888/sample-page',
    name: 'Sample Page',
  },
];

const THRESHOLDS = {
  performance: 90,
  accessibility: 90,
  'best-practices': 80,
  seo: 90,
};

async function runLighthouseTest(url, name) {
  console.log(`\n🔍 Testing: ${name}`);
  console.log(`   URL: ${url}`);

  const chrome = await chromeLauncher.launch({ chromeFlags: ['--headless'] });

  const options = {
    logLevel: 'error',
    output: 'json',
    onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'],
    port: chrome.port,
  };

  try {
    const runnerResult = await lighthouse(url, options);

    await chrome.kill();

    const { lhr } = runnerResult;
    const scores = {
      performance: lhr.categories.performance.score * 100,
      accessibility: lhr.categories.accessibility.score * 100,
      'best-practices': lhr.categories['best-practices'].score * 100,
      seo: lhr.categories.seo.score * 100,
    };

    // Display scores
    console.log('\n   📊 Scores:');
    Object.entries(scores).forEach(([category, score]) => {
      const threshold = THRESHOLDS[category];
      const emoji = score >= threshold ? '✅' : '❌';
      console.log(
        `      ${emoji} ${category.padEnd(20)}: ${score.toFixed(1)} (threshold: ${threshold})`
      );
    });

    // Display key metrics
    console.log('\n   ⚡ Key Metrics:');
    const metrics = lhr.audits.metrics.details.items[0];
    console.log(`      First Contentful Paint: ${metrics.firstContentfulPaint}ms`);
    console.log(`      Largest Contentful Paint: ${metrics.largestContentfulPaint}ms`);
    console.log(`      Total Blocking Time: ${metrics.totalBlockingTime}ms`);
    console.log(`      Cumulative Layout Shift: ${metrics.cumulativeLayoutShift}`);
    console.log(`      Speed Index: ${metrics.speedIndex}ms`);

    // Save detailed report
    const reportsDir = path.join(process.cwd(), 'lighthouse-reports');
    if (!fs.existsSync(reportsDir)) {
      fs.mkdirSync(reportsDir, { recursive: true });
    }

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const filename = `${name.toLowerCase().replace(/\s+/g, '-')}-${timestamp}.json`;
    const filepath = path.join(reportsDir, filename);

    fs.writeFileSync(filepath, JSON.stringify(lhr, null, 2));
    console.log(`\n   💾 Detailed report saved: ${filepath}`);

    return { name, scores, metrics, passed: Object.entries(scores).every(([cat, score]) => score >= THRESHOLDS[cat]) };
  } catch (error) {
    console.error(`\n   ❌ Error testing ${name}:`, error.message);
    await chrome.kill();
    return { name, scores: {}, metrics: {}, passed: false, error: error.message };
  }
}

async function runPerformanceTests() {
  console.log('🚀 Running Performance Tests\n');
  console.log('================================');

  const results = [];

  for (const page of PAGES_TO_TEST) {
    const result = await runLighthouseTest(page.url, page.name);
    results.push(result);
  }

  // Summary
  console.log('\n\n================================');
  console.log('📊 Performance Test Summary\n');

  const allPassed = results.every((r) => r.passed);

  results.forEach((result) => {
    const status = result.passed ? '✅ PASS' : '❌ FAIL';
    console.log(`${status}: ${result.name}`);
  });

  console.log('\n================================');

  if (allPassed) {
    console.log('✅ All performance tests passed!');
    process.exit(0);
  } else {
    console.log('❌ Some performance tests failed - see details above');
    process.exit(1);
  }
}

// Run tests
runPerformanceTests().catch((error) => {
  console.error('Fatal error:', error);
  process.exit(1);
});
