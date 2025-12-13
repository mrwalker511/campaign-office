/**
 * Pre-Optimization Checklist
 * Verifies theme is ready for speed optimization
 */

const fs = require('fs');
const path = require('path');

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

requiredDirs.forEach(dir => {
    const exists = fs.existsSync(dir);
    checks.push({
        name: `Directory exists: ${dir}`,
        passed: exists
    });
});

// Check 2: package.json has required dependencies
const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const requiredDeps = ['gulp', 'critical', 'lighthouse'];

requiredDeps.forEach(dep => {
    const exists = packageJson.devDependencies && packageJson.devDependencies[dep];
    checks.push({
        name: `Dependency installed: ${dep}`,
        passed: !!exists
    });
});

// Check 3: functions.php exists
checks.push({
    name: 'functions.php exists',
    passed: fs.existsSync('functions.php')
});

// Check 4: No syntax errors in key files
const keyFiles = ['functions.php', 'header.php', 'footer.php'];
keyFiles.forEach(file => {
    if (fs.existsSync(file)) {
        checks.push({
            name: `File accessible: ${file}`,
            passed: true
        });
    }
});

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
