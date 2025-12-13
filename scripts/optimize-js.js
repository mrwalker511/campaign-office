/**
 * Modern JavaScript Optimization using ESBuild
 * ESM version for Node.js compatibility
 */

import esbuild from 'esbuild';
import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';

async function optimizeJS() {
    console.log('⚡ Optimizing JavaScript with ESBuild...\n');

    const jsFiles = await glob('assets/js/**/*.js', {
        ignore: ['**/min/**']
    });

    const outputDir = 'assets/js/min';
    await fs.mkdir(outputDir, { recursive: true });

    for (const jsFile of jsFiles) {
        try {
            console.log(`Processing: ${path.basename(jsFile)}`);

            await esbuild.build({
                entryPoints: [jsFile],
                outfile: path.join(outputDir, path.basename(jsFile)),
                minify: true,
                target: 'es2020',
                format: 'iife',
                bundle: false,
                treeShaking: true,
                drop: ['console', 'debugger'],
                legalComments: 'none'
            });

            const originalContent = await fs.readFile(jsFile, 'utf8');
            const minifiedContent = await fs.readFile(path.join(outputDir, path.basename(jsFile)), 'utf8');

            const originalSize = (Buffer.byteLength(originalContent, 'utf8') / 1024).toFixed(2);
            const minifiedSize = (Buffer.byteLength(minifiedContent, 'utf8') / 1024).toFixed(2);
            const savings = ((1 - Buffer.byteLength(minifiedContent, 'utf8') / Buffer.byteLength(originalContent, 'utf8')) * 100).toFixed(1);

            console.log(`  ✅ ${originalSize}KB → ${minifiedSize}KB (${savings}% smaller)\n`);
        } catch (error) {
            console.error(`  ❌ Error processing ${jsFile}:`, error.message);
        }
    }

    console.log('✨ JavaScript optimization complete!\n');
}

optimizeJS().catch(console.error);
