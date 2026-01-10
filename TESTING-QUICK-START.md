# Testing Quick Start Checklist

> 5-minute setup guide to get testing running

## ✅ Setup Checklist

### 1. Install Dependencies (2 minutes)

```bash
npm install
composer install
npx playwright install
```

### 2. Configure Environment (1 minute)

```bash
# Copy environment template
cp .env.example .env

# Edit .env and set your WordPress URL
# WP_BASE_URL=http://localhost:8881
```

**Common URLs**:
- wp-env: `http://localhost:8881`
- LocalWP: `http://campaignpress.local`
- XAMPP: `http://localhost/campaignpress`

### 3. Setup PHP Tests (1 minute - optional)

Only needed if running PHP unit tests:

```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

### 4. Run Tests (1 minute)

```bash
# Quick validation (recommended for dev)
npm run test:quick

# Full test suite (recommended before commit)
npm run test:all
```

## 🚀 Common Commands

### During Development

```bash
# Fast feedback loop (lint + JS tests)
npm run test:quick

# Watch mode for JS tests (re-runs on save)
npm run test:js:watch

# Run linters only
npm run lint:js
npm run lint:css
```

### Before Committing

```bash
# Run all tests
npm run test:all

# Fix linting issues automatically
npm run lint:js:fix
npm run lint:css:fix
composer phpcbf
```

### Testing Specific Features

```bash
# E2E tests (requires WordPress running)
npm run test:e2e

# E2E tests with visible browser (for debugging)
npm run test:e2e:headed

# Accessibility tests
npm run test:a11y

# Performance tests
npm run test:performance
```

## 🐛 Quick Troubleshooting

### Issue: Tests can't connect to WordPress

**Fix**:
1. Open your WordPress site in browser to verify it's running
2. Check `.env` file has correct `WP_BASE_URL`
3. Make sure site is not password protected

### Issue: Playwright tests fail

**Fix**:
```bash
npx playwright install
npm run test:e2e:headed  # See what's happening
```

### Issue: Jest cache problems

**Fix**:
```bash
npx jest --clearCache
```

### Issue: PHP tests can't find WordPress

**Fix**:
```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

## 📚 Full Documentation

For comprehensive documentation, see:
- **[TESTING.md](./TESTING.md)** - Complete testing guide
- **[tests/README.md](./tests/README.md)** - Test directory overview
- **[tests/e2e/README.md](./tests/e2e/README.md)** - E2E test details

## 🎯 Pre-Commit Workflow

Recommended workflow before every commit:

```bash
# 1. Run quick tests
npm run test:quick

# 2. If all pass, run full suite
npm run test:all

# 3. Commit your changes
git add .
git commit -m "Your commit message"
```

## 💡 Tips

- Use `test:quick` during active development (fast)
- Use `test:all` before committing/pushing (comprehensive)
- Use `test:e2e:headed` when debugging E2E test failures
- Keep your `.env` file up to date with your WordPress URL
- Run `npm run test:js:coverage` to check test coverage

## ✨ That's It!

You're ready to test! See [TESTING.md](./TESTING.md) for detailed documentation.
