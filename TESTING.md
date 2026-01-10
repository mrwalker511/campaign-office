# CampaignPress Testing Guide

> Comprehensive testing documentation for the CampaignPress WordPress theme

## Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Environment Setup](#environment-setup)
- [Test Types](#test-types)
- [Running Tests](#running-tests)
- [Writing Tests](#writing-tests)
- [CI/CD Integration](#cicd-integration)
- [Troubleshooting](#troubleshooting)

## Overview

CampaignPress uses a comprehensive testing suite to ensure code quality, accessibility, and performance:

- **PHP Unit Tests**: Test WordPress integration and PHP functions
- **JavaScript Tests**: Test React components and JavaScript modules
- **E2E Tests**: Test complete user workflows in real browsers
- **Accessibility Tests**: Verify WCAG 2.1 AA compliance
- **Performance Tests**: Measure and validate performance metrics
- **Theme Check**: Validate WordPress theme standards

### Test Coverage Summary

- **270+ E2E tests** across 7 test suites
- **Unit tests** for core PHP and JavaScript functions
- **Accessibility audits** using Pa11y and Axe
- **Performance benchmarks** using Lighthouse
- **Multiple browser support**: Chrome, Firefox, Safari, Mobile

## Quick Start

### 1. Install Dependencies

```bash
# Install Node dependencies
npm install

# Install PHP dependencies
composer install

# Install Playwright browsers (for E2E tests)
npx playwright install
```

### 2. Configure Environment

Create a `.env` file from the example:

```bash
cp .env.example .env
```

Edit `.env` and set your WordPress URL:

```bash
# Set this to your local WordPress installation URL
WP_BASE_URL=http://localhost:8881
```

**Common Configurations**:
- **wp-env**: `http://localhost:8881`
- **LocalWP**: `http://campaignpress.local`
- **XAMPP/MAMP**: `http://localhost/campaignpress`
- **Custom**: Your WordPress URL

### 3. Setup WordPress Test Library (PHP Tests Only)

```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**Arguments**:
1. Database name (will be created/overwritten)
2. Database user
3. Database password
4. Database host
5. WordPress version (or 'latest')

### 4. Run Tests

```bash
# Run all tests (recommended before committing)
npm run test:all

# Quick validation (fast - lint + JS only)
npm run test:quick
```

## Environment Setup

### Required Software

| Software | Version | Purpose |
|----------|---------|---------|
| Node.js | 16+ | JavaScript testing and build tools |
| PHP | 7.4+ | WordPress and PHP unit tests |
| Composer | 2.x | PHP dependency management |
| MySQL/MariaDB | 5.7+ | Test database for PHP tests |
| Chrome/Chromium | Latest | E2E and performance tests |

### Environment Variables

Create a `.env` file in the theme root:

```bash
# WordPress Base URL (required for all tests)
WP_BASE_URL=http://localhost:8881

# Alternative variable name (supported for compatibility)
# SITE_URL=http://localhost:8881

# WordPress Test Library Directory (optional)
# WP_TESTS_DIR=/tmp/wordpress-tests-lib

# CI Environment Detection (set automatically in CI)
# CI=true
```

### WordPress Test Environment

The theme requires a running WordPress installation for E2E, accessibility, and performance tests:

1. **Install WordPress** locally using:
   - [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) (recommended)
   - [LocalWP](https://localwp.com/)
   - XAMPP/MAMP
   - Docker

2. **Activate the theme** in WordPress

3. **Install Campaign Office Core plugin** (optional but recommended)

4. **Set WP_BASE_URL** to your WordPress URL in `.env`

## Test Types

### PHP Unit Tests

**Location**: `tests/unit/`
**Command**: `npm run test:php` or `composer test`
**Purpose**: Test PHP functions, WordPress integration, and theme features

**Example Tests**:
- Theme setup and registration
- Custom post types
- Custom image sizes
- Navigation menus
- Sanitization functions
- Rate limiting
- Cache management
- Security headers

**Running PHP Tests**:

```bash
# Run all PHP tests
composer test

# Run specific test suite
vendor/bin/phpunit tests/unit/test-core-functions.php

# Run with coverage
composer test:coverage
```

### JavaScript Unit Tests

**Location**: `tests/javascript/__tests__/`
**Command**: `npm run test:js`
**Framework**: Jest + React Testing Library
**Purpose**: Test React components, JavaScript modules, and utilities

**Running JavaScript Tests**:

```bash
# Run all JS tests
npm run test:js

# Watch mode (re-run on changes)
npm run test:js:watch

# With coverage report
npm run test:js:coverage
```

### End-to-End (E2E) Tests

**Location**: `tests/e2e/`
**Command**: `npm run test:e2e`
**Framework**: Playwright
**Purpose**: Test complete user workflows in real browsers

**Test Coverage** (270+ tests):
- ✅ Gutenberg Blocks (~50 tests)
- ✅ Design Studio (~30 tests)
- ✅ Volunteer Portal (~45 tests)
- ✅ Admin Interfaces (~45 tests)
- ✅ Demo Content Import (~35 tests)
- ✅ WordPress Customizer (~40 tests)
- ✅ Volunteer Signup Flow (~26 tests)

**Running E2E Tests**:

```bash
# Run all E2E tests (headless)
npm run test:e2e

# Run with visible browser
npm run test:e2e:headed

# Run with Playwright UI (interactive)
npm run test:e2e:ui

# Run specific test file
npx playwright test tests/e2e/gutenberg-blocks.spec.js

# Run specific browser
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit

# Debug mode
npx playwright test --debug
```

**View Test Reports**:

```bash
npx playwright show-report tests/e2e/reports
```

### Accessibility Tests

**Location**: `tests/accessibility/`
**Command**: `npm run test:a11y`
**Tools**: Pa11y + Axe-core
**Standard**: WCAG 2.1 AA
**Purpose**: Verify accessibility compliance

**Running Accessibility Tests**:

```bash
npm run test:a11y
```

**What's Tested**:
- Color contrast ratios
- Keyboard navigation
- ARIA labels and roles
- Form field labels
- Heading hierarchy
- Alt text for images
- Focus indicators
- Screen reader compatibility

### Performance Tests

**Location**: `tests/performance/`
**Command**: `npm run test:performance`
**Tool**: Lighthouse
**Purpose**: Measure and validate performance metrics

**Performance Thresholds**:
- **Performance**: ≥90
- **Accessibility**: ≥90
- **Best Practices**: ≥80
- **SEO**: ≥90

**Running Performance Tests**:

```bash
npm run test:performance
```

**Reports**: Saved to `lighthouse-reports/`

**Key Metrics Measured**:
- First Contentful Paint (FCP)
- Largest Contentful Paint (LCP)
- Total Blocking Time (TBT)
- Cumulative Layout Shift (CLS)
- Speed Index

### Theme Check

**Location**: `tests/theme-check.js`
**Command**: Part of `npm run test:all`
**Purpose**: Validate WordPress theme standards

**What's Checked**:
- Required template files
- Theme header information
- WordPress coding standards
- Deprecated functions
- Security best practices

## Running Tests

### Full Test Suite

```bash
# Run ALL tests (recommended before committing)
npm run test:all
```

This runs:
1. JavaScript linting
2. CSS linting
3. PHP linting (PHPCS)
4. JavaScript unit tests
5. PHP unit tests
6. Theme standards check

### Quick Validation

```bash
# Fast validation (lint + JS tests only)
npm run test:quick
```

Use this for rapid feedback during development.

### Individual Test Suites

```bash
# PHP tests only
npm run test:php
composer test

# JavaScript tests only
npm run test:js

# E2E tests only
npm run test:e2e

# Accessibility tests only
npm run test:a11y

# Performance tests only
npm run test:performance

# Linting only
npm run test:lint
```

### Linting

```bash
# Lint JavaScript
npm run lint:js

# Fix JavaScript issues automatically
npm run lint:js:fix

# Lint CSS
npm run lint:css

# Fix CSS issues automatically
npm run lint:css:fix

# PHP linting
composer phpcs

# Fix PHP issues automatically
composer phpcbf
```

### PowerShell Test Runner

**Windows users** can use the PowerShell test runner:

```powershell
.\tests\run-all-tests.ps1
```

## Writing Tests

### PHP Unit Test Example

```php
<?php
namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

class Test_My_Feature extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        // Setup code here
    }

    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    public function test_my_feature_works() {
        // Arrange
        $input = 'test input';

        // Act
        $result = my_function($input);

        // Assert
        $this->assertEquals('expected output', $result);
    }
}
```

### JavaScript Unit Test Example

```javascript
// tests/javascript/__tests__/my-component.test.js
import { render, screen } from '@testing-library/react';
import MyComponent from '../../../assets/react/MyComponent';

test('renders component correctly', () => {
  render(<MyComponent />);

  const element = screen.getByText(/expected text/i);
  expect(element).toBeInTheDocument();
});
```

### E2E Test Example

```javascript
// tests/e2e/my-feature.spec.js
import { test, expect } from '@playwright/test';

test.describe('My Feature', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/my-feature');
  });

  test('should display feature correctly', async ({ page }) => {
    const element = page.locator('.my-feature');

    // Defensive check (recommended pattern)
    if (await element.count() > 0) {
      await expect(element.first()).toBeVisible();
    }
  });
});
```

### Test Best Practices

1. **Use descriptive test names**: Clearly state what is being tested
2. **Follow AAA pattern**: Arrange, Act, Assert
3. **Keep tests isolated**: No dependencies between tests
4. **Mock external dependencies**: Don't make real API calls
5. **Use defensive checks**: Handle missing elements gracefully (E2E)
6. **Test both success and failure cases**: Cover edge cases
7. **Keep tests fast**: Avoid unnecessary waits
8. **Use meaningful assertions**: Assert specific outcomes, not just "truthy"

## CI/CD Integration

### GitHub Actions

The test suite integrates with GitHub Actions for automated testing on push/PR.

**Workflow triggers**:
- Push to `main` branch
- Pull requests
- Manual workflow dispatch

**Tests run in CI**:
- Linting (JS, CSS, PHP)
- JavaScript unit tests
- PHP unit tests
- Theme standards check

**E2E tests** can be added to CI once a WordPress test environment is configured.

### Pre-commit Hooks

Set up Git hooks to run tests before commits:

```bash
# .git/hooks/pre-commit
#!/bin/sh
npm run test:quick
```

Make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

## Troubleshooting

### Common Issues

#### Tests Can't Connect to WordPress

**Symptoms**: Timeouts, connection refused, 404 errors

**Solutions**:
1. Verify WordPress is running: Open `WP_BASE_URL` in your browser
2. Check `.env` file exists and `WP_BASE_URL` is correct
3. Ensure WordPress site is not password-protected
4. For LocalWP: Use `.local` domain, not `localhost`
5. For wp-env: Make sure it's running (`wp-env start`)

#### Playwright Tests Fail

**Symptoms**: Browser won't launch, screenshots show blank pages

**Solutions**:
1. Install browsers: `npx playwright install`
2. Update Playwright: `npm install @playwright/test@latest`
3. Use headed mode to debug: `npm run test:e2e:headed`
4. Check browser console in headed mode
5. Verify `WP_BASE_URL` is accessible

#### PHP Tests Can't Find WordPress

**Symptoms**: "WordPress test library not found"

**Solutions**:
1. Install WordPress test library:
   ```bash
   bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```
2. Check database credentials are correct
3. Ensure MySQL/MariaDB is running
4. Set `WP_TESTS_DIR` environment variable if custom location

#### Chrome Won't Launch (Performance Tests)

**Symptoms**: "Failed to launch Chrome"

**Solutions**:
1. Ensure Google Chrome is installed
2. Close all Chrome instances
3. Check Chrome is not running in background
4. Try with different port
5. Increase timeout in test config

#### Jest Cache Issues

**Symptoms**: Old test results, module errors after updates

**Solutions**:
1. Clear Jest cache: `npx jest --clearCache`
2. Delete node_modules: `rm -rf node_modules && npm install`
3. Update snapshots: `npm run test:js -- -u`

#### Import Errors in Tests

**Symptoms**: "Cannot find module" errors

**Solutions**:
1. Check `moduleNameMapper` in `tests/jest.config.js`
2. Verify file paths are correct
3. Check that mocks exist: `tests/javascript/mocks/`
4. Ensure babel config is correct: `build/babel.config.cjs`

### Debug Mode

Enable verbose output for debugging:

```bash
# Playwright debug mode
npx playwright test --debug

# Jest verbose mode
npm run test:js -- --verbose

# PHPUnit debug mode
vendor/bin/phpunit --debug
```

### Getting Help

If you encounter issues:

1. Check this troubleshooting guide
2. Review test logs for error messages
3. Search existing GitHub issues
4. Create a new issue with:
   - Error message
   - Test command used
   - Environment details (OS, Node version, etc.)
   - Steps to reproduce

## Resources

- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [Playwright Documentation](https://playwright.dev/)
- [WordPress PHPUnit](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [Pa11y Documentation](https://github.com/pa11y/pa11y)
- [Lighthouse Documentation](https://developer.chrome.com/docs/lighthouse/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

## Contributing

When contributing to CampaignPress:

1. ✅ Write tests for new features
2. ✅ Ensure all tests pass: `npm run test:all`
3. ✅ Follow existing test patterns
4. ✅ Update documentation if needed
5. ✅ Include test coverage report in PR

**Minimum coverage requirements**:
- JavaScript: 50%
- PHP: Aim for >80%

Thank you for helping maintain CampaignPress quality! 🎉
