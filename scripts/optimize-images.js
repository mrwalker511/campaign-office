/**
 * Modern Image Optimization using Sharp
 * Generates AVIF, WebP, and optimized original formats
 * 
 * Usage:
 *   npm run optimize:images
 */

import sharp from 'sharp';
import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';
import { directories, logger } from './config.js';

/**
 * Optimize a single image
 */
async function optimizeImage(imagePath, outputDir) {
    const filename = path.basename(imagePath, path.extname(imagePath));
    const ext = path.extname(imagePath).toLowerCase();

    try {
        logger.step(`Processing: ${path.basename(imagePath)}`);

        const originalStats = await fs.stat(imagePath);
        const originalSize = originalStats.size;
        let totalOutputSize = 0;

        // Generate AVIF (best compression)
        const avifPath = path.join(outputDir, `${filename}.avif`);
        await sharp(imagePath)
            .avif({ quality: 50, effort: 9 })
            .toFile(avifPath);
        const avifStats = await fs.stat(avifPath);
        totalOutputSize += avifStats.size;

        // Generate WebP (fallback)
        const webpPath = path.join(outputDir, `${filename}.webp`);
        await sharp(imagePath)
            .webp({ quality: 75, effort: 6 })
            .toFile(webpPath);
        const webpStats = await fs.stat(webpPath);
        totalOutputSize += webpStats.size;

        // Optimize original format
        const optimizedPath = path.join(outputDir, `${filename}${ext}`);
        if (ext === '.jpg' || ext === '.jpeg') {
            await sharp(imagePath)
                .jpeg({ quality: 80, progressive: true, mozjpeg: true })
                .toFile(optimizedPath);
        } else if (ext === '.png') {
            await sharp(imagePath)
                .png({ quality: 80, compressionLevel: 9 })
                .toFile(optimizedPath);
        }
        const optimizedStats = await fs.stat(optimizedPath);
        totalOutputSize += optimizedStats.size;

        console.log(`   AVIF: ${(avifStats.size / 1024).toFixed(1)}KB`);
        console.log(`   WebP: ${(webpStats.size / 1024).toFixed(1)}KB`);
        console.log(`   ${ext.toUpperCase()}: ${(optimizedStats.size / 1024).toFixed(1)}KB`);
        logger.success(`Generated 3 formats for ${filename}`);

        return {
            success: true,
            file: path.basename(imagePath),
            originalSize,
            formats: {
                avif: avifStats.size,
                webp: webpStats.size,
                original: optimizedStats.size
            }
        };
    } catch (error) {
        logger.error(`Failed to optimize ${path.basename(imagePath)}: ${error.message}`);
        return {
            success: false,
            file: path.basename(imagePath),
            error: error.message
        };
    }
}

/**
 * Main optimization function
 */
async function optimizeImages() {
    logger.header('Image Optimization');

    const images = await glob(`${directories.images.source}/**/*.{jpg,jpeg,png,JPG,JPEG,PNG}`, {
        ignore: ['**/dist/**']
    });

    if (images.length === 0) {
        logger.warn('No images found to optimize.');
        console.log(`   Searched in: ${directories.images.source}`);
        return;
    }

    console.log(`Found ${images.length} image(s) to process\n`);

    const outputDir = directories.images.output;
    await fs.mkdir(outputDir, { recursive: true });

    const results = [];
    for (const imagePath of images) {
        const result = await optimizeImage(imagePath, outputDir);
        results.push(result);
        console.log('');
    }

    // Summary
    logger.divider();
    const successful = results.filter(r => r.success);
    const totalOriginal = successful.reduce((sum, r) => sum + r.originalSize, 0);
    const totalAvif = successful.reduce((sum, r) => sum + r.formats.avif, 0);
    const totalWebp = successful.reduce((sum, r) => sum + r.formats.webp, 0);

    console.log(`\n📊 Summary:`);
    console.log(`   Images processed: ${successful.length}/${results.length}`);
    console.log(`   Original total: ${(totalOriginal / 1024 / 1024).toFixed(2)}MB`);
    console.log(`   AVIF total: ${(totalAvif / 1024 / 1024).toFixed(2)}MB (${((1 - totalAvif / totalOriginal) * 100).toFixed(0)}% smaller)`);
    console.log(`   WebP total: ${(totalWebp / 1024 / 1024).toFixed(2)}MB (${((1 - totalWebp / totalOriginal) * 100).toFixed(0)}% smaller)`);
    console.log(`   Output: ${outputDir}\n`);

    if (results.some(r => !r.success)) {
        console.log('⚠️  Some images failed to process:');
        results.filter(r => !r.success).forEach(r => {
            console.log(`   - ${r.file}: ${r.error}`);
        });
        console.log('');
    }
}

optimizeImages().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
