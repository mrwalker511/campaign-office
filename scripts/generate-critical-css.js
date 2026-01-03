/**
 * Critical CSS Generator for CampaignPress Theme
 * Generates above-the-fold CSS for key pages
 * 
 * Usage:
 *   npm run critical-css                    # Use default config
 *   SITE_URL=http://mysite.local npm run critical-css
 *   HOST_ENV=staging npm run critical-css
 */

import { generate } from 'critical';
import fs from 'fs/promises';
import path from 'path';
import {
    getPages,
    getSiteUrl,
    directories,
    browserSettings,
    logger,
    printConfig
} from './config.js';

// Critical CSS configuration
const criticalConfig = {
    inline: false,
    dimensions: browserSettings.dimensions,
    penthouse: {
        timeout: browserSettings.timeout,
        renderWaitTime: browserSettings.renderWaitTime
    },
    extract: true,
    ignore: {
        atrule: ['@font-face'],
        rule: [/\.wp-block-/, /\.editor-/],
        decl: (node, value) => {
            return /wp-admin/.test(value);
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
 * Generate critical CSS for a single page
 */
async function generateForPage(page, outputDir) {
    try {
        logger.step(`Processing: ${page.name}`);
        console.log(`   URL: ${page.url}`);

        const { css } = await generate({
            ...criticalConfig,
            src: page.url,
            target: {
                css: page.output
            }
        });

        const fileSize = (Buffer.byteLength(css, 'utf8') / 1024).toFixed(2);
        logger.success(`Generated: ${page.output} (${fileSize} KB)`);

        return { success: true, page: page.name, size: fileSize };
    } catch (error) {
        logger.error(`Failed to generate critical CSS for ${page.name}`);
        console.error(`   ${error.message}`);

        return { success: false, page: page.name, error: error.message };
    }
}

/**
 * Main generator function
 */
async function generateCriticalCSS() {
    logger.header('Critical CSS Generator');
    printConfig();

    const siteUrl = getSiteUrl();
    const pages = getPages();

    // Check if site is reachable
    logger.step('Checking site availability...');
    const isReachable = await checkSiteReachability(siteUrl);

    if (!isReachable) {
        logger.error(`Site not reachable: ${siteUrl}`);
        console.log('\n💡 Tips:');
        console.log('   1. Ensure your WordPress site is running');
        console.log('   2. Set SITE_URL environment variable to your test host');
        console.log('   3. Example: SITE_URL=http://campaignpress.local npm run critical-css\n');
        process.exit(1);
    }

    logger.success(`Site is reachable: ${siteUrl}\n`);

    // Create output directory
    const outputDir = path.dirname(pages[0].output);
    await fs.mkdir(outputDir, { recursive: true });

    // Process all pages
    const results = [];
    for (const page of pages) {
        const result = await generateForPage(page, outputDir);
        results.push(result);
        console.log('');
    }

    // Summary
    logger.divider();
    const successful = results.filter(r => r.success).length;
    const failed = results.filter(r => !r.success).length;

    console.log(`\n📊 Summary: ${successful}/${results.length} pages processed successfully`);

    if (failed > 0) {
        console.log(`\n⚠️  Failed pages:`);
        results.filter(r => !r.success).forEach(r => {
            console.log(`   - ${r.page}: ${r.error}`);
        });
    }

    console.log('\n📝 Next steps:');
    console.log('   1. Review generated CSS files in assets/css/critical/');
    console.log('   2. Run: npm run lighthouse');
    console.log('   3. Inline critical CSS in functions.php\n');

    return results;
}

// Run generator
generateCriticalCSS().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
