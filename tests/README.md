# Tests Directory

This directory contains all automated tests for the Campaign Office WordPress theme.

## Directory Structure

```
tests/
├── accessibility/          # WCAG compliance tests (Pa11y, Axe)
│   └── run-a11y-tests.js
├── bin/                    # Test utilities and scripts
│   └── install-wp-tests.sh # WordPress test library installer
├── e2e/                    # End-to-end tests (Playwright)
│   ├── example.spec.js
│   ├── gutenberg-blocks.spec.js
│   ├── design-studio.spec.js
│   ├── volunteer-portal.spec.js
│   ├── admin-interfaces.spec.js
│   ├── demo-import.spec.js
│   ├── customizer.spec.js
│   ├── volunteer-signup.spec.js
│   └── README.md
├── integration/            # PHP integration tests
│   ├── test-event-management.php
│   └── test-volunteer-management.php
├── javascript/             # JavaScript test configuration
│   ├── __tests__/         # JavaScript unit tests
│   ├── mocks/             # Test mocks (styles, files)
│   └── setup.js           # Jest setup
├── performance/            # Performance tests (Lighthouse, Web Vitals)
│   ├── run-performance-tests.js
│   └── web-vitals.test.js
├── premium/                # Premium feature tests
│   ├── test-crm-functionality.php
│   └── test-fec-compliance.php
├── security/               # Security vulnerability tests
│   ├── test-xss-vulnerabilities.php
│   ├── test-sql-injection.php
│   ├── test-authentication-authorization.php
│   └── test-encryption.php
├── unit/                   # PHP unit tests
│   ├── test-sample.php
│   ├── test-core-functions.php
│   └── test-custom-post-types.php
├── utilities/              # Test helper utilities
│   └── class-test-helper.php
├── bootstrap.php           # PHPUnit bootstrap
├── jest.config.js          # Jest configuration
├── playwright.config.js    # Playwright configuration
├── phpunit.xml.dist        # PHPUnit configuration
├── theme-check.js          # WordPress theme validation
├── wp-config-test.php      # Test database configuration
└── README.md               # This file
```

## Quick Start

### Prerequisites

1. **Install Dependencies**:

   ```bash
   npm install
   composer install
   ```

2. **Environment Configuration**:
   Copy `.env.example` to `.env` and set your WordPress URL:

   ```bash
   cp .env.example .env
   ```

   Edit `.env` and set:

   ```bash
   WP_BASE_URL=http://localhost:8881
   ```

   Or use any of these alternatives:
   - `http://campaignpress.local` (LocalWP)
   - `http://localhost/campaignpress` (XAMPP/MAMP)
   - Your custom WordPress installation URL

3. **WordPress Test Library** (for PHP tests):

   ```bash
   bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

## Test Types

### PHP Tests

- **Unit**: `tests/unit/` - Test individual PHP functions and classes
  - Core functions
  - Custom post types
  - WordPress integration
- **Integration**: `tests/integration/` - Test WordPress integration and interactions
  - Event management
  - Volunteer management
- **Security**: `tests/security/` - Security vulnerability tests
  - XSS prevention
  - SQL injection prevention
  - Authentication and authorization
  - Encryption
- **Premium**: `tests/premium/` - Test premium features
  - CRM functionality
  - FEC compliance

### JavaScript Tests

- **Unit**: `tests/javascript/__tests__/` - Test React components and JS functions
- **E2E**: `tests/e2e/` - Browser automation tests

### Quality Tests

- **Accessibility**: `tests/accessibility/` - WCAG 2.1 AA compliance
- **Performance**: `tests/performance/` - Lighthouse, Web Vitals
- **Theme Check**: `tests/theme-check.js` - WordPress standards

## Running Tests

### Quick Commands

```bash
# Run ALL tests (recommended)
npm run test:all

# Quick validation (fast - lint + JS only)
npm run test:quick
```

### Full Test Runner

```bash
# Windows (PowerShell)
.\run-all-tests.ps1

# macOS/Linux (Bash)
./run-all-tests.sh
```

### Individual Suites

```bash
composer test              # PHP unit tests
npm run test:js           # JavaScript tests
npm run test:e2e          # E2E tests (requires WP)
npm run test:a11y         # Accessibility
npm run test:performance  # Performance
npm run test:lint         # All linters
```

## Writing Tests

### PHP Test Example

```php
<?php
// tests/unit/test-my-feature.php
namespace CampaignOffice\Tests\Unit;
use WP_UnitTestCase;

class Test_My_Feature extends WP_UnitTestCase {
    public function test_something() {
        $this->assertTrue(true);
    }
}
```

### JavaScript Test Example

```javascript
// tests/javascript/__tests__/my-test.js
test('my test', () => {
  expect(1 + 1).toBe(2);
});
```

## Common Issues and Troubleshooting

### Tests Can't Connect to WordPress

**Problem**: Tests timeout or fail to connect
**Solution**:

- Ensure WordPress is running at the URL specified in `.env`
- Test the URL in your browser first
- Check that the WordPress site is accessible (not password protected)

### Playwright Tests Fail

**Problem**: E2E tests fail with "page not found" errors
**Solution**:

- Verify `WP_BASE_URL` is set correctly in `.env`
- Run `npx playwright install` to install browsers
- Use `npm run test:e2e:headed` to see what's happening

### PHP Tests Can't Find WordPress

**Problem**: "WordPress test library not found"
**Solution**:

- Run the WordPress test installer:

  ```bash
  bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
  ```

- Or set `WP_TESTS_DIR` environment variable to your WordPress test library location

### Lighthouse/Performance Tests Fail

**Problem**: Chrome won't launch or tests timeout
**Solution**:

- Ensure Google Chrome is installed
- Close other Chrome instances
- Try increasing timeout in `tests/performance/run-performance-tests.js`

### Jest Tests Fail to Run

**Problem**: Module import errors or configuration issues
**Solution**:

- Clear Jest cache: `npx jest --clearCache`
- Reinstall dependencies: `rm -rf node_modules && npm install`
- Check that `tests/jest.config.js` paths are correct

## Environment Variables Reference

| Variable | Purpose | Default | Example |
|----------|---------|---------|---------|
| `WP_BASE_URL` | WordPress site URL for tests | `http://localhost:8881` | `http://campaignpress.local` |
| `SITE_URL` | Alternative to WP_BASE_URL | `http://localhost:8881` | Same as WP_BASE_URL |
| `WP_TESTS_DIR` | WordPress test library path | System temp dir | `/tmp/wordpress-tests-lib` |

## Best Practices

1. **Always run tests before committing**:

   ```bash
   npm run test:all
   ```

2. **Write tests for new features**: When adding functionality, add corresponding tests

3. **Keep tests isolated**: Tests should not depend on each other

4. **Use descriptive test names**: Make it clear what each test is checking

5. **Mock external dependencies**: Don't make real API calls in tests

6. **Check coverage**: Aim for >80% code coverage

   ```bash
   npm run test:js:coverage
   ```

## Documentation

For more detailed documentation, see:

- **[Full Testing Guide: TESTING.md](../TESTING.md)** - Comprehensive testing documentation
- **[E2E Test Details: tests/e2e/README.md](./e2e/README.md)** - End-to-end test coverage
- **[Theme Development: CLAUDE.md](../CLAUDE.md)** - Theme architecture and development guide
