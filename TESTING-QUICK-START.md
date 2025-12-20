# Testing Quick Start Guide

Get started testing the Campaign Office theme in 5 minutes.

## 1️⃣ Install Dependencies

```bash
# Install PHP testing tools
composer install

# Install JavaScript testing tools
npm install
```

## 2️⃣ Run Tests

### Quick Test (No WordPress Required)

```bash
# Run all local tests
npm test

# This runs:
# - PHP linting
# - PHPCS (code standards)
# - Jest (JavaScript tests)
# - ESLint & Stylelint
# - Theme check
```

### Complete Test Suite

```bash
# Run everything including PHP unit tests
./run-all-tests.sh
```

## 3️⃣ Individual Test Commands

```bash
# PHP
composer test              # Unit tests
composer phpcs            # Code standards
composer lint             # Syntax check

# JavaScript
npm run test:js           # Unit tests
npm run lint:js           # ESLint
npm run lint:css          # Stylelint

# Theme validation
node tests/theme-check.js # WordPress theme requirements
```

## 4️⃣ Development Workflow

### While Coding

```bash
# Watch JavaScript tests
npm run test:js:watch

# Auto-fix linting issues
npm run lint:js:fix
npm run lint:css:fix
composer phpcbf
```

### Before Committing

```bash
# Run all linting
npm run test:lint

# Run all tests
npm test
```

### Before Releasing

```bash
# 1. All tests
./run-all-tests.sh

# 2. Start WordPress (e.g., Local, MAMP, Docker)
# Then run:

npm run test:a11y         # Accessibility
npm run test:e2e          # Browser tests
npm run test:performance  # Lighthouse

# 3. Manual WordPress Theme Check plugin
# https://wordpress.org/plugins/theme-check/
```

## 5️⃣ Writing Tests

### PHP Test Example

Create `tests/unit/test-my-feature.php`:

```php
<?php
namespace CampaignOffice\Tests\Unit;
use WP_UnitTestCase;

class Test_My_Feature extends WP_UnitTestCase {
    public function test_it_works() {
        $this->assertTrue(true);
    }
}
```

### JavaScript Test Example

Create `assets/js/__tests__/my-component.test.js`:

```javascript
import { render, screen } from '@testing-library/react';

test('renders hello', () => {
  render(<div>Hello</div>);
  expect(screen.getByText('Hello')).toBeInTheDocument();
});
```

### E2E Test Example

Create `tests/e2e/my-test.spec.js`:

```javascript
import { test, expect } from '@playwright/test';

test('homepage loads', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Campaign Office/);
});
```

## 📚 Next Steps

Read [TESTING.md](./TESTING.md) for complete documentation including:
- Detailed setup instructions
- Advanced testing patterns
- Troubleshooting guide
- CI/CD integration
- Best practices

## 🆘 Common Issues

**"WordPress test library not found"**
```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

**"Cannot find module" (JavaScript)**
```bash
npm install
```

**"Browser not found" (E2E)**
```bash
npx playwright install
```

**Tests failing?**
- Check you've run `composer install` and `npm install`
- For E2E/A11y/Performance tests, WordPress must be running
- Check database credentials in `tests/wp-config-test.php`

## 🎯 Test Coverage Goals

Aim for:
- **PHP**: 70%+ code coverage
- **JavaScript**: 80%+ code coverage
- **Accessibility**: 0 errors, minimal warnings
- **Performance**: 90+ Lighthouse scores
- **Code Standards**: 100% PHPCS compliance

---

**Ready to test? Run:** `npm test`
