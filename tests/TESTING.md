# Testing Framework Documentation

Complete testing framework for Campaign Office WordPress Theme.

## 📋 Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Test Types](#test-types)
- [Setup](#setup)
- [Running Tests](#running-tests)
- [Writing Tests](#writing-tests)
- [CI/CD Integration](#cicd-integration)
- [Troubleshooting](#troubleshooting)

---

## Overview

This theme includes a comprehensive testing framework covering:

- ✅ **PHP Unit Tests** - WordPress/PHP functionality testing
- ✅ **JavaScript Tests** - React components and JS functionality
- ✅ **Code Standards** - PHPCS (WordPress Coding Standards)
- ✅ **Accessibility Tests** - WCAG 2.1 AA compliance
- ✅ **E2E Tests** - Browser automation with Playwright
- ✅ **Performance Tests** - Lighthouse and Web Vitals
- ✅ **Theme Check** - WordPress.org theme requirements

---

## Quick Start

### Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### Run All Tests

```bash
# Run complete test suite (excluding tests that need WordPress running)
./run-all-tests.sh

# Or using npm
npm test
```

### Run Specific Test Types

```bash
# PHP tests
composer test

# JavaScript tests
npm run test:js

# Linting
npm run test:lint

# Theme check
node tests/theme-check.js
```

---

## Test Types

### 1. PHP Unit Tests (PHPUnit)

Tests WordPress/PHP functionality using PHPUnit with WordPress test library.

**Location**: `tests/unit/`, `tests/integration/`, `tests/premium/`

**Run**:
```bash
composer test

# With coverage
composer test:coverage
```

**Example Test**:
```php
<?php
namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;

class Test_My_Feature extends WP_UnitTestCase {
    public function test_something() {
        $this->assertTrue(true);
    }
}
```

### 2. JavaScript Tests (Jest)

Tests React components and JavaScript functionality.

**Location**: `tests/javascript/__tests__/`, `**/*.test.js`

**Run**:
```bash
npm run test:js

# Watch mode
npm run test:js:watch

# With coverage
npm run test:js:coverage
```

**Example Test**:
```javascript
import { render, screen } from '@testing-library/react';

test('renders component', () => {
  render(<MyComponent />);
  expect(screen.getByText(/hello/i)).toBeInTheDocument();
});
```

### 3. Code Standards (PHPCS)

Validates PHP code meets WordPress coding standards.

**Run**:
```bash
composer phpcs

# Auto-fix issues
composer phpcbf

# Theme-specific check
composer phpcs:theme
```

**Configuration**: `phpcs.xml.dist`

### 4. Accessibility Tests (Pa11y + Axe)

Tests for WCAG 2.1 Level AA compliance.

**Location**: `tests/accessibility/`

**Run**:
```bash
npm run test:a11y
```

**Requirements**: WordPress site must be running

**Configure URLs**: Edit `tests/accessibility/run-a11y-tests.js`

```javascript
const PAGES_TO_TEST = [
  { url: 'http://localhost:8888', name: 'Home Page' },
  { url: 'http://localhost:8888/about', name: 'About Page' },
];
```

### 5. E2E Tests (Playwright)

Browser automation testing across multiple browsers.

**Location**: `tests/e2e/`

**Run**:
```bash
npm run test:e2e

# With browser visible
npm run test:e2e:headed

# Specific browser
npx playwright test --project=chromium
```

**Example Test**:
```javascript
import { test, expect } from '@playwright/test';

test('homepage loads', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Campaign Office/);
});
```

### 6. Performance Tests (Lighthouse)

Tests page load performance, metrics, and Web Vitals.

**Location**: `tests/performance/`

**Run**:
```bash
npm run test:performance
```

**Thresholds** (in `tests/performance/run-performance-tests.js`):
- Performance: 90
- Accessibility: 90
- Best Practices: 80
- SEO: 90

### 7. Theme Check

Validates WordPress theme requirements.

**Run**:
```bash
node tests/theme-check.js
```

**Checks**:
- Required files (style.css, index.php, screenshot.png, etc.)
- Theme header information
- Required WordPress functions
- Function prefixing
- Text domain consistency

### 8. Linting

**JavaScript**:
```bash
npm run lint:js
npm run lint:js:fix
```

**CSS**:
```bash
npm run lint:css
npm run lint:css:fix
```

---

## Setup

### PHP Testing Setup

1. **Install WordPress Test Library**:

```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

Replace with your database credentials:
- `wordpress_test` - Test database name
- `root` - Database user
- `''` - Database password
- `localhost` - Database host

2. **Set Environment Variables** (optional):

```bash
export WP_TESTS_DIR=/tmp/wordpress-tests-lib
export WP_CORE_DIR=/tmp/wordpress
```

3. **Configure Database**:

Edit `tests/wp-config-test.php` if needed.

### E2E Testing Setup

1. **Start WordPress**:

```bash
# Example using wp-env (if you have it)
npx @wordpress/env start

# Or your local development setup
# Adjust baseURL in playwright.config.js to match
```

2. **Configure Base URL**:

Edit `playwright.config.js`:
```javascript
use: {
  baseURL: 'http://localhost:8888', // Your WordPress URL
}
```

### Accessibility Testing Setup

Same as E2E - requires running WordPress instance.

Edit URLs in `tests/accessibility/run-a11y-tests.js`.

---

## Running Tests

### Development Workflow

```bash
# 1. Watch mode for JS while developing
npm run test:js:watch

# 2. Run linting before committing
npm run test:lint

# 3. Run all tests before pushing
./run-all-tests.sh
```

### Before Releasing

```bash
# 1. All local tests
RUN_PHP_TESTS=true \
RUN_JS_TESTS=true \
RUN_LINT=true \
RUN_THEME_CHECK=true \
./run-all-tests.sh

# 2. Start WordPress and run integration tests
npm run test:a11y
npm run test:e2e
npm run test:performance

# 3. Manual WordPress Theme Check plugin
# Install and run in WordPress admin
```

### Continuous Integration

```bash
# CI-optimized run
npm test  # Runs PHP + JS + Lint (no WordPress needed)
```

---

## Writing Tests

### PHP Unit Test Example

Create `tests/unit/test-my-feature.php`:

```php
<?php
namespace CampaignOffice\Tests\Unit;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

class Test_My_Feature extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        // Setup code
    }

    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    public function test_feature_works() {
        // Create test data
        $post_id = Test_Helper::create_test_post([
            'post_title' => 'Test Post',
        ]);

        // Test your feature
        $result = my_theme_function($post_id);

        // Assert expectations
        $this->assertNotEmpty($result);
        $this->assertEquals('expected', $result);
    }
}
```

### JavaScript Test Example

Create `assets/js/__tests__/my-component.test.js`:

```javascript
import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import MyComponent from '../MyComponent';

describe('MyComponent', () => {
  test('renders correctly', () => {
    render(<MyComponent title="Test" />);
    expect(screen.getByText('Test')).toBeInTheDocument();
  });

  test('handles click events', async () => {
    const handleClick = jest.fn();
    const user = userEvent.setup();

    render(<MyComponent onClick={handleClick} />);

    const button = screen.getByRole('button');
    await user.click(button);

    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  test('updates state', async () => {
    render(<MyComponent />);

    const input = screen.getByRole('textbox');
    await userEvent.type(input, 'Hello');

    expect(input).toHaveValue('Hello');
  });
});
```

### E2E Test Example

Create `tests/e2e/my-feature.spec.js`:

```javascript
import { test, expect } from '@playwright/test';

test.describe('My Feature', () => {
  test('user can complete workflow', async ({ page }) => {
    // Navigate
    await page.goto('/');

    // Interact
    await page.click('text=Get Started');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('button[type="submit"]');

    // Assert
    await expect(page.locator('.success-message')).toBeVisible();
  });

  test('validates form inputs', async ({ page }) => {
    await page.goto('/contact');

    // Submit empty form
    await page.click('button[type="submit"]');

    // Check validation
    const error = page.locator('.error-message');
    await expect(error).toContainText('required');
  });
});
```

### Accessibility Test Example

Add to `tests/accessibility/run-a11y-tests.js`:

```javascript
const PAGES_TO_TEST = [
  { url: 'http://localhost:8888/my-page', name: 'My Page' },
];
```

---

## Test Helpers

### PHP Helpers

Available in `tests/utilities/class-test-helper.php`:

```php
// Create test post
$post_id = Test_Helper::create_test_post([
    'post_title' => 'Test',
    'post_type' => 'page',
]);

// Create test user
$user_id = Test_Helper::create_test_user('editor');

// Create test term
$term_id = Test_Helper::create_test_term('category', [
    'name' => 'Test Category',
]);

// Access private methods/properties
$value = Test_Helper::get_private_property($object, 'property');
$result = Test_Helper::call_private_method($object, 'method', [$arg1]);

// Cleanup
Test_Helper::cleanup();
```

### JavaScript Helpers

Available globally in Jest tests:

```javascript
// WordPress i18n mocks
wp.i18n.__('Translate me');
wp.i18n._n('Singular', 'Plural', 2);

// WordPress blocks
wp.blocks.registerBlockType('my/block', { ... });

// WordPress data
wp.data.select('core/editor');
wp.data.dispatch('core/editor');
```

---

## CI/CD Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress_test
        ports:
          - 3306:3306

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          tools: composer

      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'

      - name: Install PHP dependencies
        run: composer install

      - name: Install Node dependencies
        run: npm install

      - name: Setup WordPress Tests
        run: bash tests/bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest

      - name: Run tests
        run: ./run-all-tests.sh
```

---

## Coverage Reports

### PHP Coverage

```bash
composer test:coverage
```

Opens `coverage/index.html` in browser.

### JavaScript Coverage

```bash
npm run test:js:coverage
```

View `coverage/lcov-report/index.html`.

---

## Troubleshooting

### PHP Tests

**Issue**: `WordPress test library not found`

**Solution**:
```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**Issue**: `Database connection failed`

**Solution**: Check database credentials in `tests/wp-config-test.php`

### JavaScript Tests

**Issue**: `Cannot find module`

**Solution**:
```bash
npm install
```

**Issue**: `Transform failed`

**Solution**: Check `babel.config.cjs` exists and has correct presets

### E2E Tests

**Issue**: `Navigation timeout`

**Solution**:
- Ensure WordPress is running
- Check `baseURL` in `playwright.config.js`
- Increase timeout in test

**Issue**: `Browser not found`

**Solution**:
```bash
npx playwright install
```

### Accessibility Tests

**Issue**: `Cannot connect to localhost`

**Solution**:
- Start WordPress development server
- Update URLs in `tests/accessibility/run-a11y-tests.js`

### Performance Tests

**Issue**: `Chrome launcher failed`

**Solution**:
```bash
npm install chrome-launcher --save-dev
```

**Issue**: `Lighthouse timeout`

**Solution**: Increase timeout in `tests/performance/run-performance-tests.js`

---

## Best Practices

### General

1. ✅ **Run tests before committing**
2. ✅ **Write tests for new features**
3. ✅ **Keep tests simple and focused**
4. ✅ **Use descriptive test names**
5. ✅ **Don't test WordPress core functionality**

### PHP Tests

1. ✅ **Use setUp/tearDown for test data**
2. ✅ **Clean up after each test**
3. ✅ **Use Test_Helper utilities**
4. ✅ **Test one thing per test method**
5. ✅ **Use data providers for multiple cases**

### JavaScript Tests

1. ✅ **Test user interactions, not implementation**
2. ✅ **Use Testing Library queries properly**
3. ✅ **Await async operations**
4. ✅ **Mock external dependencies**
5. ✅ **Test accessibility in components**

### E2E Tests

1. ✅ **Test critical user flows**
2. ✅ **Use page object pattern for complex pages**
3. ✅ **Wait for elements properly**
4. ✅ **Don't rely on exact text matches**
5. ✅ **Test across multiple browsers**

---

## Resources

### Documentation

- [PHPUnit](https://phpunit.de/documentation.html)
- [WordPress PHPUnit](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [Jest](https://jestjs.io/docs/getting-started)
- [Testing Library](https://testing-library.com/docs/)
- [Playwright](https://playwright.dev/)
- [Pa11y](https://pa11y.org/)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)

### WordPress Standards

- [Theme Review Guidelines](https://make.wordpress.org/themes/handbook/review/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [Theme Unit Test](https://codex.wordpress.org/Theme_Unit_Test)

---

## Quick Reference

```bash
# All tests (local only)
./run-all-tests.sh

# Individual test types
composer test              # PHP unit tests
npm run test:js           # JavaScript tests
npm run test:lint         # All linting
node tests/theme-check.js # Theme validation

# Tests requiring WordPress
npm run test:a11y         # Accessibility
npm run test:e2e          # E2E browser tests
npm run test:performance  # Performance/Lighthouse

# Development
npm run test:js:watch     # Watch mode
composer phpcbf           # Auto-fix PHP
npm run lint:js:fix       # Auto-fix JS
npm run lint:css:fix      # Auto-fix CSS

# Coverage
composer test:coverage    # PHP coverage
npm run test:js:coverage  # JS coverage
```

---

## Getting Help

- Check [Troubleshooting](#troubleshooting) section
- Review example tests in `tests/` directory
- Read inline code comments
- Check test framework documentation links above

---

**Happy Testing! 🎉**
