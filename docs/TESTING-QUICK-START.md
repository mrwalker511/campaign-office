# Testing Guide - Campaign Office Theme

Complete testing documentation with commands to run the full test suite.

## Quick Start (TL;DR)

```bash
# Run ALL tests (lint + unit + theme check)
npm run test:all

# Quick validation (lint + JS tests only - fast)
npm run test:quick

# PowerShell full test suite (Windows)
.\tests\run-all-tests.ps1

# Bash full test suite (macOS/Linux)
./tests/run-all-tests.sh
```

---

## Prerequisites

| Requirement | Version | Check Command |
|-------------|---------|---------------|
| PHP | 7.4+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |

### One-Time Setup

```bash
# Install all dependencies
composer install
npm install

# Install Playwright browsers (for E2E tests)
npx playwright install
```

---

## Test Commands Reference

### 🚀 Primary Commands

| Command | Description | Speed |
|---------|-------------|-------|
| `npm run test:all` | Full test suite (lint, JS, PHP, theme check) | ~2-3 min |
| `npm run test:quick` | Fast validation (lint + JS only) | ~30 sec |
| `npm test` | Standard test (PHP + JS + lint) | ~1-2 min |

### 📦 Individual Test Suites

#### JavaScript Tests (Jest)
```bash
npm run test:js              # Run all JS tests
npm run test:js:watch        # Watch mode (re-runs on changes)
npm run test:js:coverage     # With code coverage report
```

#### PHP Tests (PHPUnit)
```bash
composer test                # Run all PHP tests
composer test:unit           # Unit tests only
composer test:coverage       # With coverage (outputs to /coverage)
```

#### Linting
```bash
npm run test:lint            # All linters (JS + CSS + PHP)
npm run lint:js              # JavaScript only (ESLint)
npm run lint:js:fix          # JavaScript with auto-fix
npm run lint:css             # CSS only (Stylelint)
npm run lint:css:fix         # CSS with auto-fix
composer phpcs               # PHP CodeSniffer
composer phpcbf              # PHP CodeSniffer with auto-fix
```

#### Theme Validation
```bash
node tests/theme-check.js    # WordPress theme standards check
```

### 🌐 Integration Tests (Require Running WordPress)

These tests require a running WordPress instance with the theme active.

```bash
# E2E Tests (Playwright)
npm run test:e2e             # Headless browser tests
npm run test:e2e:headed      # With visible browser
npm run test:e2e:ui          # Interactive UI mode

# Accessibility Tests
npm run test:a11y            # WCAG 2.1 AA compliance

# Performance Tests
npm run test:performance     # Lighthouse + Web Vitals
```

---

## Test Runner Scripts

### Windows (PowerShell)

```powershell
# Basic run (lint, PHP, JS, theme check)
.\tests\run-all-tests.ps1

# Run with all tests including E2E
.\tests\run-all-tests.ps1 -All

# Selective tests
.\tests\run-all-tests.ps1 -JS -Lint      # JS and lint only
.\tests\run-all-tests.ps1 -PHP           # PHP only
.\tests\run-all-tests.ps1 -E2E -A11y     # E2E and accessibility
```

**Available flags:**
- `-PHP` - PHP unit tests
- `-JS` - JavaScript tests
- `-Lint` - All linters
- `-ThemeCheck` - WordPress theme standards
- `-A11y` - Accessibility tests
- `-E2E` - End-to-end tests
- `-Performance` - Performance tests
- `-All` - Run everything

### macOS/Linux (Bash)

```bash
# Basic run
./tests/run-all-tests.sh

# With environment variables
RUN_E2E=true RUN_A11Y=true ./tests/run-all-tests.sh
```

---

## Directory Structure

```
tests/
├── unit/                    # PHP unit tests
│   ├── test-core-functions.php
│   ├── test-custom-post-types.php
│   └── test-sample.php
├── integration/             # PHP integration tests
├── javascript/              # JavaScript test setup
│   ├── __tests__/          # Jest test files
│   ├── mocks/              # Test mocks
│   └── setup.js            # Jest setup
├── e2e/                     # Playwright E2E tests
│   ├── customizer.spec.js
│   ├── volunteer-portal.spec.js
│   └── ...
├── accessibility/           # Pa11y/Axe tests
├── performance/             # Lighthouse tests
├── security/                # Security tests
├── jest.config.js          # Jest configuration
├── phpunit.xml.dist        # PHPUnit configuration
├── playwright.config.js    # Playwright configuration
├── run-all-tests.ps1       # Windows test runner
└── run-all-tests.sh        # Unix test runner
```

---

## Writing Tests

### PHP Test Example

```php
<?php
// tests/unit/test-my-feature.php
namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;

class Test_My_Feature extends WP_UnitTestCase {
    
    public function test_function_returns_expected_value() {
        $result = my_function('input');
        $this->assertEquals('expected', $result);
    }
    
    public function test_action_is_registered() {
        $this->assertTrue(has_action('init', 'my_callback'));
    }
}
```

### JavaScript Test Example

```javascript
// tests/javascript/__tests__/my-test.js
import { myFunction } from '../../../assets/js/my-module';

describe('myFunction', () => {
    test('returns expected value', () => {
        expect(myFunction('input')).toBe('expected');
    });
    
    test('handles edge cases', () => {
        expect(myFunction(null)).toBeNull();
    });
});
```

### E2E Test Example

```javascript
// tests/e2e/my-feature.spec.js
import { test, expect } from '@playwright/test';

test.describe('My Feature', () => {
    test('loads correctly', async ({ page }) => {
        await page.goto('/my-page');
        await expect(page.locator('h1')).toContainText('Welcome');
    });
});
```

---

## CI/CD Integration

### GitHub Actions

```yaml
# .github/workflows/test.yml
name: Tests
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - run: composer install
      - run: npm install
      - run: npm run test:all
```

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| "PHPUnit not found" | Run `composer install` |
| "Jest command not found" | Run `npm install` |
| "WordPress test library not found" | Run `bash tests/bin/install-wp-tests.sh` |
| E2E tests timeout | Increase timeout in `playwright.config.js` |
| PHP syntax errors | Run `composer lint` to check all files |

### Reset Everything

```bash
# Clean and reinstall
rm -rf node_modules vendor
composer install
npm install
```

---

## Coverage Reports

```bash
# JavaScript coverage (outputs to /coverage)
npm run test:js:coverage

# PHP coverage (outputs to /coverage)
composer test:coverage

# View HTML report
open coverage/index.html   # macOS
start coverage/index.html  # Windows
```
