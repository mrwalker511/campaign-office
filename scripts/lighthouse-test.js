/**
 * Lighthouse Performance Testing for Political Theme
 * Tests all key pages and generates reports
 */

const lighthouse = require('lighthouse');
const chromeLauncher = require('chrome-launcher');
const fs = require('fs');
const path = require('path');

// Test pages
const testPages = [
    {
        url: 'http://localhost:8080/',
        name: 'Homepage'
    },
    {
        url: 'http://localhost:8080/events/',
        name: 'Events'
    },
    {
        url: 'http://localhost:8080/donate/',
        name: 'Donate'
    },
    {
        url: 'http://localhost:8080/volunteer/',
        name: 'Volunteer'
    }
];

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
 * Run Lighthouse test on a single page
 */
async function runLighthouse(url, chrome) {
    const options = {
        logLevel: 'info',
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
 * Main test function
 */
async function runTests() {
    console.log('🚀 Running Lighthouse Performance Tests\n');
    console.log('Target: LCP <1.5s, CLS <0.05, INP <200ms\n');
    console.log('═'.repeat(80) + '\n');

    const chrome = await chromeLauncher.launch({ chromeFlags: ['--headless'] });
    const results = [];

    try {
        for (const page of testPages) {
            console.log(`📊 Testing: ${page.name}`);
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
            console.log(`   ├─ LCP: ${(lcp / 1000).toFixed(2)}s ${lcp < 2500 ? '✅' : '❌'}`);
            console.log(`   ├─ CLS: ${cls.toFixed(3)} ${cls < 0.1 ? '✅' : '❌'}`);
            console.log(`   └─ TBT: ${tbt.toFixed(0)}ms ${tbt < 300 ? '✅' : '❌'}\n`);

            results.push({
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
            });

            console.log('─'.repeat(80) + '\n');
        }

        // Save results
        const reportDir = 'lighthouse-reports';
        if (!fs.existsSync(reportDir)) {
            fs.mkdirSync(reportDir, { recursive: true });
        }

        const timestamp = new Date().toISOString().replace(/:/g, '-').split('.')[0];
        const reportPath = path.join(reportDir, `report-${timestamp}.json`);
        fs.writeFileSync(reportPath, JSON.stringify(results, null, 2));

        console.log(`📝 Report saved: ${reportPath}\n`);

        // Summary
        const avgPerformance = results.reduce((sum, r) => sum + r.scores.performance, 0) / results.length;
        console.log('═'.repeat(80));
        console.log(`📊 SUMMARY - Average Performance: ${formatScore(avgPerformance / 100)}`);
        console.log('═'.repeat(80) + '\n');

        if (avgPerformance >= 95) {
            console.log('🎉 EXCELLENT! Target achieved: 95+ performance score!\n');
        } else if (avgPerformance >= 90) {
            console.log('✅ GOOD! Close to target. Review opportunities for improvement.\n');
        } else {
            console.log('⚠️  NEEDS WORK. Run optimization pipeline: npm run speed-optimize\n');
        }

    } finally {
        await chrome.kill();
    }
}

// Run tests
runTests().catch(error => {
    console.error('Fatal error:', error);
    process.exit(1);
});
