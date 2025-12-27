/**
 * Modern CSS Optimization using LightningCSS and PurgeCSS
 * ESM version for Node.js compatibility
 */

import { PurgeCSS } from 'purgecss';
import { transform } from 'lightningcss';
import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';

async function optimizeCSS() {
    console.log('🎨 Optimizing CSS with LightningCSS and PurgeCSS...\n');

    const cssFiles = await glob('assets/css/**/*.css', {
        ignore: ['**/critical/**', '**/dist/**']
    });

    const outputDir = 'assets/dist/css';
    await fs.mkdir(outputDir, { recursive: true });

    for (const cssFile of cssFiles) {
        try {
            console.log(`Processing: ${path.basename(cssFile)}`);

            // Read CSS content
            const css = await fs.readFile(cssFile, 'utf8');

            // Step 1: Remove unused CSS with PurgeCSS
            const purgeCSSResult = await new PurgeCSS().purge({
                content: [
                    '**/*.php',
                    'blocks/**/*.js',
                    'assets/js/**/*.js'
                ],
                css: [{ raw: css }],
                safelist: {
                    standard: [
                        /^wp-/,
                        /^has-/,
                        /^is-/,
                        /^campaignpress-/,
                        /^cp-/,
                        /^dashicons/,
                        /^screen-reader-text/
                    ],
                    deep: [
                        /modal/,
                        /popover/,
                        /tooltip/,
                        /dropdown/
                    ]
                }
            });

            const purgedCSS = purgeCSSResult[0].css;

            // Step 2: Minify with LightningCSS
            const { code } = transform({
                filename: cssFile,
                code: Buffer.from(purgedCSS),
                minify: true,
                targets: {
                    chrome: 95,
                    firefox: 90,
                    safari: 14
                }
            });

            // Save minified CSS
            const outputPath = path.join(outputDir, path.basename(cssFile));
            await fs.writeFile(outputPath, code);

            const originalSize = (Buffer.byteLength(css, 'utf8') / 1024).toFixed(2);
            const minifiedSize = (code.length / 1024).toFixed(2);
            const savings = ((1 - code.length / Buffer.byteLength(css, 'utf8')) * 100).toFixed(1);

            console.log(`  ✅ ${originalSize}KB → ${minifiedSize}KB (${savings}% smaller)\n`);
        } catch (error) {
            console.error(`  ❌ Error processing ${cssFile}:`, error.message);
        }
    }

    console.log('✨ CSS optimization complete!\n');
}

optimizeCSS().catch(console.error);
