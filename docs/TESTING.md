# Campaign Office Theme - Testing Guide

Comprehensive testing documentation for the Campaign Office WordPress theme.

## Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Test Types](#test-types)
4. [Running Tests](#running-tests)
5. [Writing Tests](#writing-tests)
6. [Continuous Integration](#continuous-integration)
7. [Code Coverage](#code-coverage)
8. [Troubleshooting](#troubleshooting)

---

## Overview

This theme includes a comprehensive test suite covering:

- **Security Tests**: SQL injection, XSS, CSRF, authentication, encryption
- **Unit Tests**: Core functions, custom post types, utility functions
- **Integration Tests**: Volunteer management, events, premium features
- **JavaScript Tests**: Frontend functionality, AJAX handlers
- **E2E Tests**: Complete user flows (signup, RSVP, donations)
- **Accessibility Tests**: WCAG 2.1 AA compliance
- **Performance Tests**: Web Vitals, Lighthouse scores

**Test Coverage Goal**: 80%+ code coverage

---

## Getting Started

### Prerequisites

1. **PHP** 7.4+ with extensions:
   - mysqli
   - mbstring
   - xml

2. **Composer** for PHP dependencies

3. **Node.js** 18+ and npm

4. **WordPress Test Library**

### Installation

#### 1. Install PHP Dependencies

```bash
composer install
```

#### 2. Install Node Dependencies

```bash
npm install
```

#### 3. Set Up WordPress Test Library

```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**Note**: Adjust database credentials as needed. This creates a separate test database.

#### 4. Configure Environment

Create `tests/wp-config-test.php` (if not exists):

```php
<?php
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
```

---

## Test Types

### Security Tests (`tests/security/`)

Tests for vulnerabilities identified in code review:

- **SQL Injection**: `test-sql-injection.php`
- **XSS Vulnerabilities**: `test-xss-vulnerabilities.php`
- **Authentication/Authorization**: `test-authentication-authorization.php`
- **Encryption**: `test-encryption.php`

**Run Security Tests**:
```bash
./vendor/bin/phpunit tests/security
```

### Unit Tests (`tests/unit/`)

Tests for individual functions and classes:

- **Core Functions**: `test-core-functions.php`
- **Custom Post Types**: `test-custom-post-types.php`

**Run Unit Tests**:
```bash
./vendor/bin/phpunit tests/unit
```

### Integration Tests (`tests/integration/`)

Tests for feature integration:

- **Volunteer Management**: `test-volunteer-management.php`
- **Event Management**: `test-event-management.php`

**Run Integration Tests**:
```bash
./vendor/bin/phpunit tests/integration
```

### Premium Feature Tests (`tests/premium/`)

Tests for premium functionality:

- **CRM**: `test-crm-functionality.php`
- **FEC Compliance**: `test-fec-compliance.php`

**Run Premium Tests**:
```bash
./vendor/bin/phpunit tests/premium
```

### JavaScript Tests (`tests/javascript/`)

Jest-based tests for frontend code:

- **Volunteer Form**: `volunteer-form.test.js`
- **Components**: Various component tests

**Run JavaScript Tests**:
```bash
npm run test:js
```

**Run with Coverage**:
```bash
npm run test:js -- --coverage
```

### E2E Tests (`tests/e2e/`)

Playwright tests for complete user flows:

- **Volunteer Signup**: `volunteer-signup.spec.js`
- **Event RSVP**: Included in volunteer-signup.spec.js
- **Donations**: Included in volunteer-signup.spec.js
- **Accessibility**: Keyboard navigation, screen readers

**Run E2E Tests**:
```bash
npm run test:e2e
```

**Run E2E Tests in UI Mode**:
```bash
npm run test:e2e:ui
```

### Accessibility Tests (`tests/accessibility/`)

WCAG 2.1 AA compliance tests using Pa11y/Axe:

**Run Accessibility Tests**:
```bash
npm run test:a11y
```

### Performance Tests (`tests/performance/`)

Lighthouse and Web Vitals tests:

**Run Performance Tests**:
```bash
npm run test:performance
```

---

## Running Tests

### Run All Tests

```bash
./tests/run-all-tests.sh
```

This script runs:
1. PHP unit tests
2. PHP integration tests
3. PHP security tests
4. JavaScript tests
5. E2E tests
6. Accessibility tests
7. Performance tests

### Run Specific Test Files

```bash
# Run single PHP test file
./vendor/bin/phpunit tests/unit/test-core-functions.php

# Run single JS test file
npm test volunteer-form.test.js

# Run single E2E test
npx playwright test volunteer-signup.spec.js
```

### Run Tests with Filters

```bash
# Run tests matching pattern
./vendor/bin/phpunit --filter test_volunteer

# Run tests in specific group
./vendor/bin/phpunit --group security
```

### Watch Mode (JavaScript)

```bash
npm run test:js:watch
```

---

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
        // Setup code
    }

    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    public function test_something() {
        $result = my_function( 'input' );
        $this->assertEquals( 'expected', $result );
    }
}
```

### JavaScript Test Example

```javascript
describe('My Component', () => {
  test('should do something', () => {
    const result = myFunction('input');
    expect(result).toBe('expected');
  });
});
```

### E2E Test Example

```javascript
const { test, expect } = require('@playwright/test');

test('should complete user flow', async ({ page }) => {
  await page.goto('/page');
  await page.fill('input[name="field"]', 'value');
  await page.click('button[type="submit"]');
  await expect(page.locator('.success')).toBeVisible();
});
```

---

## Test Helper Methods

The `Test_Helper` class provides useful methods:

### Creating Test Data

```php
// Create test post
$post_id = Test_Helper::create_test_post( array(
    'post_title' => 'Test Post',
    'post_type'  => 'post',
) );

// Create test user
$user_id = Test_Helper::create_test_user( 'editor' );

// Create test volunteer
$volunteer_id = Test_Helper::create_test_volunteer( array(
    'email' => 'test@example.com',
) );

// Create test event
$event_id = Test_Helper::create_test_event( array(
    'event_date' => '2025-12-31',
) );

// Create test CRM contact
$contact_id = Test_Helper::create_test_crm_contact( array(
    'first_name' => 'John',
    'last_name'  => 'Doe',
) );
```

### Cleanup

```php
// Clean up all test data
Test_Helper::cleanup();
```

---

## Continuous Integration

### GitHub Actions

The theme includes a GitHub Actions workflow (`.github/workflows/tests.yml`) that runs on every push and pull request:

1. **PHP Tests** (PHP 7.4, 8.0, 8.1, 8.2)
2. **JavaScript Tests**
3. **E2E Tests**
4. **Code Coverage** (uploaded to Codecov)

### Local CI Simulation

```bash
# Run all tests as CI would
./tests/run-all-tests.sh

# Check for code style issues
composer run phpcs

# Fix code style automatically
composer run phpcbf
```

---

## Code Coverage

### PHP Code Coverage

```bash
# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage

# Open in browser
open coverage/index.html
```

### JavaScript Code Coverage

```bash
# Run tests with coverage
npm run test:js -- --coverage

# Coverage report in coverage/lcov-report/index.html
```

### Coverage Goals

- **Overall**: 80%+
- **Security-critical code**: 95%+
- **Core functionality**: 85%+
- **UI components**: 70%+

---

## Troubleshooting

### Common Issues

#### 1. "WordPress test library not found"

**Solution**:
```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

#### 2. "Class not found" errors

**Solution**:
```bash
composer dump-autoload
```

#### 3. Database connection errors

**Solution**: Check `tests/wp-config-test.php` credentials match your MySQL setup.

#### 4. E2E tests timeout

**Solution**: Increase timeout in `playwright.config.js`:
```javascript
timeout: 60000, // 60 seconds
```

#### 5. Memory exhaustion during tests

**Solution**: Increase PHP memory limit:
```bash
php -d memory_limit=512M ./vendor/bin/phpunit
```

### Test Database Issues

If tests are failing due to database state:

```bash
# Drop and recreate test database
mysql -e "DROP DATABASE IF EXISTS wordpress_test; CREATE DATABASE wordpress_test;"

# Reinstall test library
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

---

## Best Practices

### 1. Test Independence

Each test should be independent and not rely on other tests:

```php
public function setUp(): void {
    parent::setUp();
    // Create fresh test data
}

public function tearDown(): void {
    parent::tearDown();
    Test_Helper::cleanup(); // Clean up after each test
}
```

### 2. Meaningful Test Names

```php
// Good
public function test_volunteer_signup_validates_email_format() { }

// Bad
public function test_signup() { }
```

### 3. One Assertion Per Test (when possible)

```php
// Good
public function test_volunteer_status_can_be_active() {
    $volunteer = Test_Helper::create_test_volunteer();
    Test_Helper::update_volunteer_status( $volunteer, 'active' );

    $updated = Test_Helper::get_volunteer( $volunteer );
    $this->assertEquals( 'active', $updated->status );
}
```

### 4. Use Test Helpers

Always use `Test_Helper` methods instead of direct database queries in tests.

### 5. Mock External Services

```php
// Mock HTTP requests
add_filter( 'pre_http_request', function() {
    return array(
        'response' => array( 'code' => 200 ),
        'body'     => json_encode( array( 'success' => true ) ),
    );
} );
```

---

## Test Coverage by Feature

| Feature | Unit Tests | Integration Tests | E2E Tests | Coverage |
|---------|-----------|-------------------|-----------|----------|
| Volunteer Management | ✅ | ✅ | ✅ | 85% |
| Event Management | ✅ | ✅ | ✅ | 82% |
| CRM | ✅ | ✅ | ⚠️ | 75% |
| FEC Compliance | ✅ | ✅ | ⚠️ | 78% |
| Security | ✅ | ✅ | ✅ | 90% |
| Custom Post Types | ✅ | ⚠️ | ⚠️ | 70% |

**Legend**: ✅ Complete | ⚠️ Partial | ❌ Missing

---

## Contributing

When adding new features:

1. Write tests FIRST (TDD approach)
2. Ensure 80%+ coverage for new code
3. Run full test suite before committing
4. Update this documentation if adding new test types

---

## Resources

- [WordPress Unit Testing](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [Jest Documentation](https://jestjs.io/)
- [Playwright Documentation](https://playwright.dev/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

## Support

For testing questions:

1. Check this documentation
2. Review existing tests for examples
3. Open an issue on GitHub
4. Contact the development team

---

**Last Updated**: 2025-12-28
**Version**: 2.0.0
