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
│   └── example.spec.js
├── integration/            # PHP integration tests
├── javascript/             # JavaScript test configuration
│   ├── __tests__/         # JavaScript unit tests
│   ├── mocks/             # Test mocks (styles, files)
│   └── setup.js           # Jest setup
├── performance/            # Performance tests (Lighthouse, Web Vitals)
│   ├── run-performance-tests.js
│   └── web-vitals.test.js
├── premium/                # Premium feature tests
├── unit/                   # PHP unit tests
│   └── test-sample.php
├── utilities/              # Test helper utilities
│   └── class-test-helper.php
├── bootstrap.php           # PHPUnit bootstrap
├── theme-check.js          # WordPress theme validation
└── wp-config-test.php      # Test database configuration
```

## Quick Start

See [TESTING-QUICK-START.md](../TESTING-QUICK-START.md) in the theme root.

## Test Types

### PHP Tests
- **Unit**: `tests/unit/` - Test individual PHP functions and classes
- **Integration**: `tests/integration/` - Test WordPress integration
- **Premium**: `tests/premium/` - Test premium features

### JavaScript Tests
- **Unit**: `tests/javascript/__tests__/` - Test React components and JS functions
- **E2E**: `tests/e2e/` - Browser automation tests

### Quality Tests
- **Accessibility**: `tests/accessibility/` - WCAG 2.1 AA compliance
- **Performance**: `tests/performance/` - Lighthouse, Web Vitals
- **Theme Check**: `tests/theme-check.js` - WordPress standards

## Running Tests

```bash
# All tests
./run-all-tests.sh

# Specific types
composer test              # PHP unit tests
npm run test:js           # JavaScript tests
npm run test:e2e          # E2E tests
npm run test:a11y         # Accessibility
npm run test:performance  # Performance
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

## Documentation

Full testing documentation: [TESTING.md](../TESTING.md)
