# Testing Quick Start Guide

Get started with testing the Campaign Office theme in 5 minutes!

## Prerequisites

- PHP 7.4+
- Composer
- Node.js 18+
- MySQL/MariaDB
- WordPress (for E2E tests)

## Quick Setup

### 1. Clone and Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Set Up WordPress Test Library

```bash
# Run the installation script
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**Replace `root` and `''` with your MySQL username and password.**

### 3. Install Playwright (for E2E tests)

```bash
npx playwright install
```

## Running Tests

### Run Everything

```bash
./tests/run-all-tests.sh
```

This runs all PHP, JavaScript, and E2E tests.

### Run Individual Test Suites

```bash
# PHP Unit Tests
./vendor/bin/phpunit

# JavaScript Tests
npm run test:js

# E2E Tests
npm run test:e2e

# Security Tests
./vendor/bin/phpunit tests/security

# Accessibility Tests
npm run test:a11y
```

## Quick Test Examples

### Test if WordPress is loaded (PHP)

```bash
./vendor/bin/phpunit tests/unit/test-sample.php
```

### Test JavaScript functionality

```bash
npm test sample.test.js
```

### Test a user flow (E2E)

```bash
npx playwright test volunteer-signup.spec.js
```

## Watch Mode (Development)

```bash
# JavaScript tests in watch mode
npm run test:js:watch

# E2E tests with UI
npm run test:e2e:ui
```

## Troubleshooting

### "WordPress test library not found"

Re-run the installation script:
```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

### Database connection errors

Check your MySQL credentials in `tests/wp-config-test.php`

### Tests timing out

Increase timeout in `phpunit.xml.dist` or `playwright.config.js`

## Next Steps

- Read full documentation: [TESTING.md](./TESTING.md)
- Review existing tests in `tests/` directory
- Write your first test following the examples

## Need Help?

- Check [TESTING.md](./TESTING.md) for detailed documentation
- Review test examples in `tests/` directories
- Open an issue on GitHub

---

**Happy Testing! 🧪**
