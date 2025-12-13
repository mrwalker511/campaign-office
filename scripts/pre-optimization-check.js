/**
 * Pre-Optimization Checklist
 * ESM version for Node.js compatibility
 */

import fs from 'fs/promises';

console.log('🔍 Pre-Optimization Checklist\n');
console.log('═'.repeat(60) + '\n');

const checks = [];

// Check 1: Required directories exist
const requiredDirs = [
    'assets/images',
    'assets/css',
    'assets/js',
    'blocks'
];

for (const dir of requiredDirs) {
    try {
        await fs.access(dir);
        checks.push({
            name: `Directory exists: ${dir}`,
            passed: true
        });
    } catch {
        checks.push({
            name: `Directory exists: ${dir}`,
            passed: false
        });
    }
}

// Check 2: package.json has required dependencies
try {
    const packageJson = JSON.parse(await fs.readFile('package.json', 'utf8'));
    const requiredDeps = ['sharp', 'critical', 'lighthouse'];

    for (const dep of requiredDeps) {
        const exists = packageJson.devDependencies && packageJson.devDependencies[dep];
        checks.push({
            name: `Dependency installed: ${dep}`,
            passed: !!exists
        });
    }
} catch (error) {
    checks.push({
        name: 'package.json readable',
        passed: false
    });
}

// Check 3: functions.php exists
try {
    await fs.access('functions.php');
    checks.push({
        name: 'functions.php exists',
        passed: true
    });
} catch {
    checks.push({
        name: 'functions.php exists',
        passed: false
    });
}

// Check 4: Key files accessible
const keyFiles = ['functions.php', 'header.php', 'footer.php'];
for (const file of keyFiles) {
    try {
        await fs.access(file);
        checks.push({
            name: `File accessible: ${file}`,
            passed: true
        });
    } catch {
        checks.push({
            name: `File accessible: ${file}`,
            passed: false
        });
    }
}

// Display results
checks.forEach(check => {
    const icon = check.passed ? '✅' : '❌';
    console.log(`${icon} ${check.name}`);
});

const allPassed = checks.every(c => c.passed);

console.log('\n' + '═'.repeat(60) + '\n');

if (allPassed) {
    console.log('✨ All checks passed! Ready for optimization.\n');
    console.log('Next step: npm run speed-optimize\n');
    process.exit(0);
} else {
    console.log('❌ Some checks failed. Please fix issues before optimizing.\n');
    console.log('Run: npm install\n');
    process.exit(1);
}
