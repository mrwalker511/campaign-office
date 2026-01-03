/**
 * Modern CSS Optimization using LightningCSS and PurgeCSS
 * 
 * Usage:
 *   npm run optimize:css
 */

import { PurgeCSS } from 'purgecss';
import { transform } from 'lightningcss';
import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';
import { directories, purgeSafelist, browserTargets, logger } from './config.js';

/**
 * Optimize a single CSS file
 */
async function optimizeFile(cssFile, outputDir) {
    try {
        const filename = path.basename(cssFile);
        logger.step(`Processing: ${filename}`);

        // Read CSS content
        const css = await fs.readFile(cssFile, 'utf8');
        const originalSize = Buffer.byteLength(css, 'utf8');

        // Step 1: Remove unused CSS with PurgeCSS
        const purgeCSSResult = await new PurgeCSS().purge({
            content: [
                '**/*.php',
                'blocks/**/*.js',
                'assets/js/**/*.js',
                'templates/**/*.html',
                'parts/**/*.html'
            ],
            css: [{ raw: css }],
            safelist: purgeSafelist
        });

        const purgedCSS = purgeCSSResult[0]?.css || css;

        // Step 2: Minify with LightningCSS
        const { code } = transform({
            filename: cssFile,
            code: Buffer.from(purgedCSS),
            minify: true,
            targets: browserTargets
        });

        // Save minified CSS
        const outputPath = path.join(outputDir, filename);
        await fs.writeFile(outputPath, code);

        const minifiedSize = code.length;
        const savings = ((1 - minifiedSize / originalSize) * 100).toFixed(1);

        console.log(`   ${(originalSize / 1024).toFixed(2)}KB → ${(minifiedSize / 1024).toFixed(2)}KB (${savings}% smaller)`);
        logger.success(`Saved: ${outputPath}`);

        return {
            success: true,
            file: filename,
            originalSize,
            minifiedSize,
            savings: parseFloat(savings)
        };
    } catch (error) {
        logger.error(`Failed to optimize ${path.basename(cssFile)}: ${error.message}`);
        return {
            success: false,
            file: path.basename(cssFile),
            error: error.message
        };
    }
}

/**
 * Main optimization function
 */
async function optimizeCSS() {
    logger.header('CSS Optimization');

    const cssFiles = await glob(`${directories.css.source}/**/*.css`, {
        ignore: ['**/critical/**', '**/dist/**']
    });

    if (cssFiles.length === 0) {
        logger.warn('No CSS files found to optimize.');
        return;
    }

    console.log(`Found ${cssFiles.length} CSS file(s) to process\n`);

    const outputDir = directories.css.output;
    await fs.mkdir(outputDir, { recursive: true });

    const results = [];
    for (const cssFile of cssFiles) {
        const result = await optimizeFile(cssFile, outputDir);
        results.push(result);
        console.log('');
    }

    // Summary
    logger.divider();
    const successful = results.filter(r => r.success);
    const totalOriginal = successful.reduce((sum, r) => sum + r.originalSize, 0);
    const totalMinified = successful.reduce((sum, r) => sum + r.minifiedSize, 0);
    const totalSavings = totalOriginal > 0
        ? ((1 - totalMinified / totalOriginal) * 100).toFixed(1)
        : 0;

    console.log(`\n📊 Summary:`);
    console.log(`   Files processed: ${successful.length}/${results.length}`);
    console.log(`   Total savings: ${(totalOriginal / 1024).toFixed(2)}KB → ${(totalMinified / 1024).toFixed(2)}KB (${totalSavings}%)`);
    console.log(`   Output: ${outputDir}\n`);

    if (results.some(r => !r.success)) {
        console.log('⚠️  Some files failed to process:');
        results.filter(r => !r.success).forEach(r => {
            console.log(`   - ${r.file}: ${r.error}`);
        });
        console.log('');
    }
}

optimizeCSS().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
