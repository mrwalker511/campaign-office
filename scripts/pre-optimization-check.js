/**
 * Pre-Optimization Checklist
 * Validates environment before running optimization scripts
 * 
 * Usage:
 *   npm run pre-optimize
 */

import fs from 'fs/promises';
import { directories, getSiteUrl, logger, printConfig } from './config.js';

/**
 * Check if a path exists
 */
async function pathExists(filePath) {
    try {
        await fs.access(filePath);
        return true;
    } catch {
        return false;
    }
}

/**
 * Run all pre-optimization checks
 */
async function runChecks() {
    logger.header('Pre-Optimization Checklist');
    printConfig();

    const checks = [];

    // Check 1: Required directories exist
    const requiredDirs = [
        directories.images.source,
        directories.css.source,
        directories.js.source,
        'blocks'
    ];

    for (const dir of requiredDirs) {
        const exists = await pathExists(dir);
        checks.push({
            name: `Directory exists: ${dir}`,
            passed: exists
        });
    }

    // Check 2: package.json has required dependencies
    try {
        const packageJson = JSON.parse(await fs.readFile('package.json', 'utf8'));
        const requiredDeps = ['sharp', 'critical', 'lighthouse', 'esbuild', 'purgecss', 'lightningcss'];

        for (const dep of requiredDeps) {
            const inDev = packageJson.devDependencies && packageJson.devDependencies[dep];
            const inProd = packageJson.dependencies && packageJson.dependencies[dep];
            checks.push({
                name: `Dependency: ${dep}`,
                passed: !!(inDev || inProd)
            });
        }
    } catch (error) {
        checks.push({
            name: 'package.json readable',
            passed: false,
            error: error.message
        });
    }

    // Check 3: Key WordPress files exist
    const keyFiles = ['functions.php', 'style.css', 'theme.json'];
    for (const file of keyFiles) {
        const exists = await pathExists(file);
        checks.push({
            name: `File exists: ${file}`,
            passed: exists
        });
    }

    // Check 4: Site URL is configured
    const siteUrl = getSiteUrl();
    checks.push({
        name: `Site URL configured: ${siteUrl}`,
        passed: !!siteUrl
    });

    // Check 5: Site is reachable (non-blocking)
    try {
        const response = await fetch(siteUrl, {
            method: 'HEAD',
            signal: AbortSignal.timeout(5000)
        });
        checks.push({
            name: `Site reachable: ${siteUrl}`,
            passed: response.ok
        });
    } catch {
        checks.push({
            name: `Site reachable: ${siteUrl}`,
            passed: false,
            warning: true  // This is a warning, not a blocker
        });
    }

    // Display results
    logger.divider();
    console.log('\n📋 Check Results:\n');

    checks.forEach(check => {
        const icon = check.passed ? '✅' : (check.warning ? '⚠️' : '❌');
        console.log(`${icon} ${check.name}`);
    });

    const allPassed = checks.every(c => c.passed || c.warning);
    const criticalFailed = checks.filter(c => !c.passed && !c.warning);

    console.log('\n' + '═'.repeat(60) + '\n');

    if (allPassed) {
        console.log('✨ All checks passed! Ready for optimization.\n');
        console.log('Next steps:');
        console.log('   npm run speed-optimize    # Run full optimization pipeline');
        console.log('   npm run optimize:images   # Optimize images only');
        console.log('   npm run optimize:css      # Optimize CSS only');
        console.log('   npm run optimize:js       # Optimize JS only\n');
        process.exit(0);
    } else {
        console.log('❌ Some checks failed:\n');
        criticalFailed.forEach(c => {
            console.log(`   • ${c.name}`);
        });
        console.log('\nRun: npm install\n');
        process.exit(1);
    }
}

runChecks().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
