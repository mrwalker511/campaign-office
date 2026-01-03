/**
 * Lighthouse Performance Testing for CampaignPress Theme
 * Tests all key pages and generates reports
 * 
 * Usage:
 *   npm run lighthouse                      # Use default config
 *   SITE_URL=http://mysite.local npm run lighthouse
 *   HOST_ENV=staging npm run lighthouse
 */

import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'fs/promises';
import path from 'path';
import {
    getPages,
    getSiteUrl,
    directories,
    performanceThresholds,
    logger,
    printConfig
} from './config.js';

// Lighthouse configuration
const lighthouseConfig = {
    extends: 'lighthouse:default',
    settings: {
        onlyCategories: ['performance', 'accessibility', 'best-practices'],
        formFactor: 'mobile',
        throttling: {
            rttMs: 150,
            throughputKbps: 1638.4,
            cpuSlowdownMultiplier: 4
        },
        screenEmulation: {
            mobile: true,
            width: 375,
            height: 667,
            deviceScaleFactor: 2
        }
    }
};

/**
 * Check if the site is reachable
 */
async function checkSiteReachability(url) {
    try {
        const response = await fetch(url, { method: 'HEAD', signal: AbortSignal.timeout(5000) });
        return response.ok;
    } catch {
        return false;
    }
}

/**
 * Run Lighthouse test on a single page
 */
async function runLighthouse(url, chrome) {
    const options = {
        logLevel: 'error',
        output: 'json',
        port: chrome.port
    };

    const runnerResult = await lighthouse(url, options, lighthouseConfig);
    return runnerResult.lhr;
}

/**
 * Format score with color
 */
function formatScore(score) {
    const percentage = Math.round(score * 100);
    if (percentage >= 90) return `✅ ${percentage}`;
    if (percentage >= 50) return `⚠️  ${percentage}`;
    return `❌ ${percentage}`;
}

/**
 * Test a single page
 */
async function testPage(page, chrome) {
    try {
        logger.step(`Testing: ${page.name}`);
        console.log(`   URL: ${page.url}\n`);

        const result = await runLighthouse(page.url, chrome);

        // Extract key metrics
        const performance = result.categories.performance.score;
        const accessibility = result.categories.accessibility.score;
        const bestPractices = result.categories['best-practices'].score;

        const lcp = result.audits['largest-contentful-paint'].numericValue;
        const cls = result.audits['cumulative-layout-shift'].numericValue;
        const tbt = result.audits['total-blocking-time'].numericValue;

        // Display results
        console.log(`   Performance:     ${formatScore(performance)}`);
        console.log(`   Accessibility:   ${formatScore(accessibility)}`);
        console.log(`   Best Practices:  ${formatScore(bestPractices)}\n`);

        console.log(`   Core Web Vitals:`);
        console.log(`   ├─ LCP: ${(lcp / 1000).toFixed(2)}s ${lcp < performanceThresholds.lcp ? '✅' : '❌'}`);
        console.log(`   ├─ CLS: ${cls.toFixed(3)} ${cls < performanceThresholds.cls ? '✅' : '❌'}`);
        console.log(`   └─ TBT: ${tbt.toFixed(0)}ms ${tbt < performanceThresholds.tbt ? '✅' : '❌'}`);

        return {
            success: true,
            page: page.name,
            url: page.url,
            scores: {
                performance: Math.round(performance * 100),
                accessibility: Math.round(accessibility * 100),
                bestPractices: Math.round(bestPractices * 100)
            },
            metrics: {
                lcp: (lcp / 1000).toFixed(2),
                cls: cls.toFixed(3),
                tbt: tbt.toFixed(0)
            }
        };
    } catch (error) {
        logger.error(`Failed to test ${page.name}: ${error.message}`);
        return {
            success: false,
            page: page.name,
            url: page.url,
            error: error.message
        };
    }
}

/**
 * Main test function
 */
async function runTests() {
    logger.header('Lighthouse Performance Tests');
    printConfig();

    const siteUrl = getSiteUrl();
    const pages = getPages();

    console.log(`Target: LCP <${performanceThresholds.lcp / 1000}s, CLS <${performanceThresholds.cls}, TBT <${performanceThresholds.tbt}ms\n`);

    // Check if site is reachable
    logger.step('Checking site availability...');
    const isReachable = await checkSiteReachability(siteUrl);

    if (!isReachable) {
        logger.error(`Site not reachable: ${siteUrl}`);
        console.log('\n💡 Tips:');
        console.log('   1. Ensure your WordPress site is running');
        console.log('   2. Set SITE_URL environment variable to your test host');
        console.log('   3. Example: SITE_URL=http://campaignpress.local npm run lighthouse\n');
        process.exit(1);
    }

    logger.success(`Site is reachable: ${siteUrl}\n`);
    logger.divider();

    // Launch Chrome
    let chrome;
    try {
        chrome = await chromeLauncher.launch({ chromeFlags: ['--headless'] });
    } catch (error) {
        logger.error(`Failed to launch Chrome: ${error.message}`);
        console.log('\n💡 Ensure Chrome/Chromium is installed on your system.\n');
        process.exit(1);
    }

    const results = [];

    try {
        for (const page of pages) {
            const result = await testPage(page, chrome);
            results.push(result);
            console.log('\n');
            logger.divider();
        }

        // Save results
        const reportDir = directories.reports;
        await fs.mkdir(reportDir, { recursive: true });

        const timestamp = new Date().toISOString().replace(/:/g, '-').split('.')[0];
        const reportPath = path.join(reportDir, `report-${timestamp}.json`);

        const reportData = {
            timestamp: new Date().toISOString(),
            siteUrl,
            thresholds: performanceThresholds,
            results
        };

        await fs.writeFile(reportPath, JSON.stringify(reportData, null, 2));
        console.log(`\n📝 Report saved: ${reportPath}\n`);

        // Summary
        const successfulResults = results.filter(r => r.success);
        const avgPerformance = successfulResults.length > 0
            ? successfulResults.reduce((sum, r) => sum + r.scores.performance, 0) / successfulResults.length
            : 0;

        console.log('═'.repeat(60));
        console.log(`📊 SUMMARY - Average Performance: ${formatScore(avgPerformance / 100)}`);
        console.log(`   Successful: ${successfulResults.length}/${results.length} pages`);
        console.log('═'.repeat(60) + '\n');

        if (avgPerformance >= performanceThresholds.targetScore) {
            console.log(`🎉 EXCELLENT! Target achieved: ${performanceThresholds.targetScore}+ performance score!\n`);
        } else if (avgPerformance >= 90) {
            console.log('✅ GOOD! Close to target. Review opportunities for improvement.\n');
        } else {
            console.log('⚠️  NEEDS WORK. Run optimization pipeline: npm run speed-optimize\n');
        }

    } finally {
        await chrome.kill();
    }

    return results;
}

// Run tests
runTests().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
