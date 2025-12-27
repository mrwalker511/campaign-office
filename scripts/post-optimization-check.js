/**
 * Post-Optimization Verification
 * ESM version for Node.js compatibility
 */

import fs from 'fs/promises';

console.log('✅ Post-Optimization Verification\n');
console.log('═'.repeat(60) + '\n');

const checks = [];

// Check 1: Optimized images exist
const optimizedImagesDir = 'assets/dist/images';
try {
    const files = await fs.readdir(optimizedImagesDir);
    const avifFiles = files.filter(f => f.endsWith('.avif'));
    const webpFiles = files.filter(f => f.endsWith('.webp'));

    checks.push({
        name: `AVIF images generated: ${avifFiles.length}`,
        passed: avifFiles.length > 0
    });

    checks.push({
        name: `WebP images generated: ${webpFiles.length}`,
        passed: webpFiles.length > 0
    });
} catch {
    checks.push({
        name: 'Optimized images directory exists',
        passed: false
    });
}

// Check 2: Minified CSS exists
const minCSSDir = 'assets/dist/css';
try {
    const files = await fs.readdir(minCSSDir);
    checks.push({
        name: `Minified CSS files: ${files.length}`,
        passed: files.length > 0
    });
} catch {
    checks.push({
        name: 'Minified CSS directory exists',
        passed: false
    });
}

// Check 3: Minified JS exists
const minJSDir = 'assets/dist/js';
try {
    const files = await fs.readdir(minJSDir);
    checks.push({
        name: `Minified JS files: ${files.length}`,
        passed: files.length > 0
    });
} catch {
    checks.push({
        name: 'Minified JS directory exists',
        passed: false
    });
}

// Check 4: Critical CSS generated
const criticalCSSDir = 'assets/css/critical';
try {
    const files = await fs.readdir(criticalCSSDir);
    checks.push({
        name: `Critical CSS files: ${files.length}`,
        passed: files.length >= 4 // Should have 4 pages
    });
} catch {
    checks.push({
        name: 'Critical CSS directory exists',
        passed: false
    });
}

// Display results
checks.forEach(check => {
    const icon = check.passed ? '✅' : '⚠️';
    console.log(`${icon} ${check.name}`);
});

console.log('\n' + '═'.repeat(60) + '\n');

const allPassed = checks.every(c => c.passed);

if (allPassed) {
    console.log('🎉 Optimization complete! All assets generated.\n');
    console.log('Next steps:');
    console.log('1. Update functions.php to use optimized assets');
    console.log('2. Run: npm run lighthouse');
    console.log('3. Deploy optimized theme\n');
} else {
    console.log('⚠️  Some optimizations may have failed.\n');
    console.log('Review the output above and re-run: npm run speed-optimize\n');
}
