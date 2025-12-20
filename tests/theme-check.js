#!/usr/bin/env node
/**
 * WordPress Theme Check Script
 *
 * Validates theme meets WordPress.org requirements
 */

import fs from 'fs';
import path from 'path';

const THEME_ROOT = process.cwd();
const REQUIRED_FILES = [
  'style.css',
  'index.php',
  'screenshot.png',
  'readme.txt',
];

const REQUIRED_TEMPLATES = [
  // Core templates (at least one should exist)
  ['index.php'],
  // Comments template
  ['comments.php'],
];

const REQUIRED_FUNCTIONS = [
  'wp_head',
  'wp_footer',
  'body_class',
  'wp_body_open',
];

const RECOMMENDED_FILES = [
  'header.php',
  'footer.php',
  'sidebar.php',
  '404.php',
  'archive.php',
  'search.php',
  'single.php',
  'page.php',
];

function checkFile(filepath) {
  return fs.existsSync(path.join(THEME_ROOT, filepath));
}

function checkStyleCSS() {
  console.log('\n📄 Checking style.css...');

  const stylePath = path.join(THEME_ROOT, 'style.css');
  if (!fs.existsSync(stylePath)) {
    console.log('   ❌ style.css not found');
    return false;
  }

  const styleContent = fs.readFileSync(stylePath, 'utf8');

  const requiredHeaders = [
    'Theme Name',
    'Theme URI',
    'Author',
    'Author URI',
    'Description',
    'Version',
    'License',
    'License URI',
    'Text Domain',
  ];

  let allHeadersPresent = true;

  requiredHeaders.forEach((header) => {
    const regex = new RegExp(`${header}:`, 'i');
    if (!regex.test(styleContent)) {
      console.log(`   ❌ Missing header: ${header}`);
      allHeadersPresent = false;
    }
  });

  // Check license
  if (!/License: GNU General Public License|License: GPL/i.test(styleContent)) {
    console.log('   ⚠️  License should be GPL compatible');
  }

  if (allHeadersPresent) {
    console.log('   ✅ All required headers present');
  }

  return allHeadersPresent;
}

function checkFunctions() {
  console.log('\n🔧 Checking required functions...');

  const files = [
    'header.php',
    'footer.php',
    'functions.php',
    'index.php',
  ];

  const themeContent = files
    .filter((file) => checkFile(file))
    .map((file) => fs.readFileSync(path.join(THEME_ROOT, file), 'utf8'))
    .join('\n');

  let allFunctionsPresent = true;

  REQUIRED_FUNCTIONS.forEach((func) => {
    const regex = new RegExp(`${func}\\s*\\(`, 'i');
    if (!regex.test(themeContent)) {
      console.log(`   ❌ Missing function call: ${func}()`);
      allFunctionsPresent = false;
    } else {
      console.log(`   ✅ Found: ${func}()`);
    }
  });

  return allFunctionsPresent;
}

function checkPrefixing() {
  console.log('\n🏷️  Checking function prefixing...');

  if (!checkFile('functions.php')) {
    console.log('   ⚠️  functions.php not found');
    return false;
  }

  const functionsContent = fs.readFileSync(
    path.join(THEME_ROOT, 'functions.php'),
    'utf8'
  );

  // Check for common unprefixed functions
  const unprefixedPattern = /function\s+(init|setup|scripts|styles)\s*\(/g;
  const matches = functionsContent.match(unprefixedPattern);

  if (matches && matches.length > 0) {
    console.log('   ⚠️  Found potentially unprefixed functions:');
    matches.forEach((match) => console.log(`      - ${match}`));
    console.log('   💡 Consider prefixing with your theme slug');
    return false;
  }

  console.log('   ✅ Function prefixing looks good');
  return true;
}

function checkScreenshot() {
  console.log('\n🖼️  Checking screenshot...');

  if (!checkFile('screenshot.png')) {
    console.log('   ❌ screenshot.png not found');
    return false;
  }

  const stats = fs.statSync(path.join(THEME_ROOT, 'screenshot.png'));
  const sizeKB = stats.size / 1024;

  console.log(`   📏 Screenshot size: ${sizeKB.toFixed(2)} KB`);

  if (sizeKB > 1024) {
    console.log('   ⚠️  Screenshot is larger than 1MB - consider optimizing');
  } else {
    console.log('   ✅ Screenshot size is good');
  }

  return true;
}

function checkTextDomain() {
  console.log('\n🌍 Checking text domain...');

  const stylePath = path.join(THEME_ROOT, 'style.css');
  const styleContent = fs.readFileSync(stylePath, 'utf8');

  const textDomainMatch = styleContent.match(/Text Domain:\s*(.+)/i);
  if (!textDomainMatch) {
    console.log('   ❌ Text Domain not specified in style.css');
    return false;
  }

  const textDomain = textDomainMatch[1].trim();
  console.log(`   📝 Text Domain: ${textDomain}`);

  // Check if text domain is used in translation functions
  const phpFiles = getAllPHPFiles(THEME_ROOT);
  let inconsistentDomains = false;

  phpFiles.forEach((file) => {
    const content = fs.readFileSync(file, 'utf8');
    const domainMatches = content.match(/__\(.+?,\s*['"](.+?)['"]\)/g);

    if (domainMatches) {
      domainMatches.forEach((match) => {
        const domain = match.match(/['"](.+?)['"]\s*\)/);
        if (domain && domain[1] !== textDomain) {
          if (!inconsistentDomains) {
            console.log('   ⚠️  Inconsistent text domains found:');
            inconsistentDomains = true;
          }
          console.log(`      ${path.relative(THEME_ROOT, file)}: ${domain[1]}`);
        }
      });
    }
  });

  if (!inconsistentDomains) {
    console.log('   ✅ Text domain is consistent');
  }

  return !inconsistentDomains;
}

function getAllPHPFiles(dir) {
  let files = [];
  const items = fs.readdirSync(dir);

  items.forEach((item) => {
    const fullPath = path.join(dir, item);
    const stat = fs.statSync(fullPath);

    // Skip vendor and node_modules
    if (item === 'vendor' || item === 'node_modules' || item === '.git') {
      return;
    }

    if (stat.isDirectory()) {
      files = files.concat(getAllPHPFiles(fullPath));
    } else if (item.endsWith('.php')) {
      files.push(fullPath);
    }
  });

  return files;
}

async function runThemeCheck() {
  console.log('🎨 WordPress Theme Check\n');
  console.log('================================');

  const results = {
    requiredFiles: true,
    styleCSS: true,
    functions: true,
    prefixing: true,
    screenshot: true,
    textDomain: true,
  };

  // Check required files
  console.log('\n📁 Checking required files...');
  REQUIRED_FILES.forEach((file) => {
    if (checkFile(file)) {
      console.log(`   ✅ ${file}`);
    } else {
      console.log(`   ❌ ${file} - REQUIRED`);
      results.requiredFiles = false;
    }
  });

  // Check recommended files
  console.log('\n📁 Checking recommended files...');
  RECOMMENDED_FILES.forEach((file) => {
    if (checkFile(file)) {
      console.log(`   ✅ ${file}`);
    } else {
      console.log(`   ⚠️  ${file} - recommended`);
    }
  });

  // Run all checks
  results.styleCSS = checkStyleCSS();
  results.functions = checkFunctions();
  results.prefixing = checkPrefixing();
  results.screenshot = checkScreenshot();
  results.textDomain = checkTextDomain();

  // Summary
  console.log('\n================================');
  console.log('📊 Theme Check Summary\n');

  const allPassed = Object.values(results).every((r) => r === true);

  if (allPassed) {
    console.log('✅ All checks passed!');
    console.log('\n💡 Next steps:');
    console.log('   1. Run PHPCS: composer phpcs');
    console.log('   2. Test with Theme Check plugin in WordPress');
    console.log('   3. Test theme with sample data');
    process.exit(0);
  } else {
    console.log('❌ Some checks failed - please review above');
    process.exit(1);
  }
}

// Run theme check
runThemeCheck().catch((error) => {
  console.error('Fatal error:', error);
  process.exit(1);
});
