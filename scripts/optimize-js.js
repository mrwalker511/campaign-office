/**
 * Modern JavaScript Optimization using ESBuild
 * Minifies, tree-shakes, and removes debug code
 * 
 * Usage:
 *   npm run optimize:js
 */

import esbuild from 'esbuild';
import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';
import { directories, logger } from './config.js';

/**
 * Optimize a single JS file
 */
async function optimizeFile(jsFile, outputDir) {
    const filename = path.basename(jsFile);

    try {
        logger.step(`Processing: ${filename}`);

        const originalContent = await fs.readFile(jsFile, 'utf8');
        const originalSize = Buffer.byteLength(originalContent, 'utf8');

        await esbuild.build({
            entryPoints: [jsFile],
            outfile: path.join(outputDir, filename),
            minify: true,
            target: 'es2020',
            format: 'iife',
            bundle: false,
            treeShaking: true,
            drop: ['console', 'debugger'],
            legalComments: 'none'
        });

        const minifiedContent = await fs.readFile(path.join(outputDir, filename), 'utf8');
        const minifiedSize = Buffer.byteLength(minifiedContent, 'utf8');
        const savings = ((1 - minifiedSize / originalSize) * 100).toFixed(1);

        console.log(`   ${(originalSize / 1024).toFixed(2)}KB → ${(minifiedSize / 1024).toFixed(2)}KB (${savings}% smaller)`);
        logger.success(`Saved: ${path.join(outputDir, filename)}`);

        return {
            success: true,
            file: filename,
            originalSize,
            minifiedSize,
            savings: parseFloat(savings)
        };
    } catch (error) {
        logger.error(`Failed to optimize ${filename}: ${error.message}`);
        return {
            success: false,
            file: filename,
            error: error.message
        };
    }
}

/**
 * Main optimization function
 */
async function optimizeJS() {
    logger.header('JavaScript Optimization');

    const jsFiles = await glob(`${directories.js.source}/**/*.js`, {
        ignore: ['**/dist/**', '**/*.min.js']
    });

    if (jsFiles.length === 0) {
        logger.warn('No JavaScript files found to optimize.');
        console.log(`   Searched in: ${directories.js.source}`);
        return;
    }

    console.log(`Found ${jsFiles.length} JS file(s) to process\n`);

    const outputDir = directories.js.output;
    await fs.mkdir(outputDir, { recursive: true });

    const results = [];
    for (const jsFile of jsFiles) {
        const result = await optimizeFile(jsFile, outputDir);
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

optimizeJS().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
