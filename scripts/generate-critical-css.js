/**
 * Critical CSS Generator for Political Theme
 * Generates above-the-fold CSS for key pages
 * ESM version for Node.js compatibility
 */

import { generate } from 'critical';
import fs from 'fs/promises';
import path from 'path';

// Pages to generate critical CSS for
const pages = [
    {
        url: 'http://localhost:8881/',
        output: 'assets/css/critical/home.css',
        name: 'Homepage (Hero + Countdown)'
    },
    {
        url: 'http://localhost:8881/events/',
        output: 'assets/css/critical/events.css',
        name: 'Events (Calendar + RSVP)'
    },
    {
        url: 'http://localhost:8881/donate/',
        output: 'assets/css/critical/donate.css',
        name: 'Donate (Blockchain Form)'
    },
    {
        url: 'http://localhost:8881/volunteer/',
        output: 'assets/css/critical/volunteer.css',
        name: 'Volunteer (Skill Matcher)'
    }
];

// Critical CSS configuration
const criticalConfig = {
    inline: false,
    dimensions: [
        {
            height: 900,
            width: 1300
        },
        {
            height: 900,
            width: 768
        },
        {
            height: 900,
            width: 375
        }
    ],
    penthouse: {
        timeout: 60000,
        renderWaitTime: 1000
    },
    extract: true,
    ignore: {
        atrule: ['@font-face'],
        rule: [/\.wp-block-/, /\.editor-/],
        decl: (node, value) => {
            // Ignore WordPress admin styles
            return /wp-admin/.test(value);
        }
    }
};

/**
 * Generate critical CSS for all pages
 */
async function generateCriticalCSS() {
    console.log('🚀 Generating Critical CSS for Political Theme...\n');

    // Create output directory
    const outputDir = path.dirname(pages[0].output);
    await fs.mkdir(outputDir, { recursive: true });

    for (const page of pages) {
        try {
            console.log(`📄 Processing: ${page.name}`);
            console.log(`   URL: ${page.url}`);

            const { css } = await generate({
                ...criticalConfig,
                src: page.url,
                target: {
                    css: page.output
                }
            });

            const fileSize = (Buffer.byteLength(css, 'utf8') / 1024).toFixed(2);
            console.log(`   ✅ Generated: ${page.output} (${fileSize} KB)\n`);

        } catch (error) {
            console.error(`   ❌ Error generating critical CSS for ${page.name}:`);
            console.error(`   ${error.message}\n`);
        }
    }

    console.log('✨ Critical CSS generation complete!\n');
    console.log('📝 Next steps:');
    console.log('   1. Review generated CSS files in assets/css/critical/');
    console.log('   2. Run: npm run lighthouse');
    console.log('   3. Inline critical CSS in functions.php\n');
}

// Run generator
generateCriticalCSS().catch(error => {
    console.error('Fatal error:', error);
    process.exit(1);
});
