/**
 * Post-Optimization Verification
 * Validates that all optimized assets were generated successfully
 * 
 * Usage:
 *   npm run post-optimize
 */

import fs from 'fs/promises';
import { directories, logger } from './config.js';

/**
 * Get files in a directory
 */
async function getFiles(dir) {
    try {
        return await fs.readdir(dir);
    } catch {
        return null;
    }
}

/**
 * Get directory size
 */
async function getDirSize(dir) {
    try {
        const files = await fs.readdir(dir);
        let totalSize = 0;
        for (const file of files) {
            const stats = await fs.stat(`${dir}/${file}`);
            totalSize += stats.size;
        }
        return totalSize;
    } catch {
        return 0;
    }
}

/**
 * Run post-optimization verification
 */
async function runVerification() {
    logger.header('Post-Optimization Verification');

    const checks = [];

    // Check 1: Optimized images exist
    const imageFiles = await getFiles(directories.images.output);
    if (imageFiles) {
        const avifFiles = imageFiles.filter(f => f.endsWith('.avif'));
        const webpFiles = imageFiles.filter(f => f.endsWith('.webp'));
        const imageSize = await getDirSize(directories.images.output);

        checks.push({
            name: `AVIF images: ${avifFiles.length} files`,
            passed: avifFiles.length > 0
        });
        checks.push({
            name: `WebP images: ${webpFiles.length} files`,
            passed: webpFiles.length > 0
        });
        checks.push({
            name: `Total image output: ${(imageSize / 1024 / 1024).toFixed(2)}MB`,
            passed: true,
            info: true
        });
    } else {
        checks.push({
            name: `Optimized images directory: ${directories.images.output}`,
            passed: false
        });
    }

    // Check 2: Minified CSS exists
    const cssFiles = await getFiles(directories.css.output);
    if (cssFiles) {
        const cssSize = await getDirSize(directories.css.output);
        checks.push({
            name: `Minified CSS: ${cssFiles.length} files (${(cssSize / 1024).toFixed(1)}KB)`,
            passed: cssFiles.length > 0
        });
    } else {
        checks.push({
            name: `Minified CSS directory: ${directories.css.output}`,
            passed: false
        });
    }

    // Check 3: Minified JS exists
    const jsFiles = await getFiles(directories.js.output);
    if (jsFiles) {
        const jsSize = await getDirSize(directories.js.output);
        checks.push({
            name: `Minified JS: ${jsFiles.length} files (${(jsSize / 1024).toFixed(1)}KB)`,
            passed: jsFiles.length > 0
        });
    } else {
        checks.push({
            name: `Minified JS directory: ${directories.js.output}`,
            passed: false
        });
    }

    // Check 4: Critical CSS generated
    const criticalFiles = await getFiles(directories.css.critical);
    if (criticalFiles) {
        const criticalSize = await getDirSize(directories.css.critical);
        checks.push({
            name: `Critical CSS: ${criticalFiles.length} files (${(criticalSize / 1024).toFixed(1)}KB)`,
            passed: criticalFiles.length >= 4  // Expecting 4 pages
        });
    } else {
        checks.push({
            name: `Critical CSS directory: ${directories.css.critical}`,
            passed: false,
            warning: true  // Critical CSS is optional
        });
    }

    // Check 5: Lighthouse reports exist
    const reportFiles = await getFiles(directories.reports);
    if (reportFiles) {
        const jsonReports = reportFiles.filter(f => f.endsWith('.json'));
        checks.push({
            name: `Lighthouse reports: ${jsonReports.length} available`,
            passed: jsonReports.length > 0
        });
    } else {
        checks.push({
            name: `Lighthouse reports directory: ${directories.reports}`,
            passed: false,
            warning: true  // Reports are optional
        });
    }

    // Display results
    logger.divider();
    console.log('\n📋 Verification Results:\n');

    checks.forEach(check => {
        let icon = '✅';
        if (!check.passed && check.warning) icon = '⚠️';
        else if (!check.passed) icon = '❌';
        else if (check.info) icon = 'ℹ️';
        console.log(`${icon} ${check.name}`);
    });

    const criticalPassed = checks.filter(c => !c.warning && !c.info).every(c => c.passed);
    const warnings = checks.filter(c => c.warning && !c.passed);

    console.log('\n' + '═'.repeat(60) + '\n');

    if (criticalPassed) {
        console.log('🎉 Optimization complete! All critical assets generated.\n');

        if (warnings.length > 0) {
            console.log('⚠️  Optional items not generated:');
            warnings.forEach(w => console.log(`   • ${w.name}`));
            console.log('');
        }

        console.log('📝 Next steps:');
        console.log('   1. Update functions.php to use optimized assets');
        console.log('   2. Run: npm run lighthouse');
        console.log('   3. Deploy optimized theme\n');
    } else {
        console.log('⚠️  Some optimizations may have failed.\n');
        console.log('Review the output above and re-run: npm run speed-optimize\n');
        process.exit(1);
    }
}

runVerification().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
