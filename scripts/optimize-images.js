/**
 * Modern Image Optimization using Sharp
 * ESM version for Node.js compatibility
 */

import sharp from 'sharp';
import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';

async function optimizeImages() {
    console.log('🖼️  Optimizing images with Sharp...\n');

    const images = await glob('assets/images/**/*.{jpg,jpeg,png}', {
        ignore: ['**/dist/**']
    });

    const outputDir = 'assets/dist/images';
    await fs.mkdir(outputDir, { recursive: true });

    for (const imagePath of images) {
        const filename = path.basename(imagePath, path.extname(imagePath));
        const ext = path.extname(imagePath);

        try {
            console.log(`Processing: ${path.basename(imagePath)}`);

            // Generate AVIF (best compression)
            await sharp(imagePath)
                .avif({ quality: 50, effort: 9 })
                .toFile(path.join(outputDir, `${filename}.avif`));

            // Generate WebP (fallback)
            await sharp(imagePath)
                .webp({ quality: 75, effort: 6 })
                .toFile(path.join(outputDir, `${filename}.webp`));

            // Optimize original format
            if (ext === '.jpg' || ext === '.jpeg') {
                await sharp(imagePath)
                    .jpeg({ quality: 80, progressive: true, mozjpeg: true })
                    .toFile(path.join(outputDir, `${filename}${ext}`));
            } else if (ext === '.png') {
                await sharp(imagePath)
                    .png({ quality: 80, compressionLevel: 9 })
                    .toFile(path.join(outputDir, `${filename}${ext}`));
            }

            console.log(`  ✅ Generated AVIF, WebP, and optimized ${ext}\n`);
        } catch (error) {
            console.error(`  ❌ Error processing ${imagePath}:`, error.message);
        }
    }

    console.log('✨ Image optimization complete!\n');
}

optimizeImages().catch(console.error);
