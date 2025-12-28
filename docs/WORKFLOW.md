# Development Workflow

**Standard development workflow for CampaignPress**

---

## Overview

This document outlines the standard development workflow for contributing to CampaignPress, from initial setup through deployment.

---

## Environment Setup

### 1. Prerequisites

**Required Software:**
- WordPress 6.9+
- PHP 8.1+ (minimum 7.4)
- MySQL 8.0+ or MariaDB 10.5+
- Node.js 18+
- npm 9+
- Git

**Recommended Tools:**
- Local by Flywheel or XAMPP
- VS Code with extensions:
  - PHP Intelephense
  - ESLint
  - Prettier
  - WordPress Snippets

### 2. Initial Setup

```bash
# Clone repository
git clone https://github.com/mrwalker511/campaign-office.git
cd campaign-office

# Install Node dependencies
npm install

# Install PHP dependencies (if using Composer)
composer install

# Create local WordPress install
# - Install WordPress 6.9+
# - Place theme in wp-content/themes/campaign-office/

# Activate theme in WordPress admin
# - Go to Appearance → Themes
# - Activate CampaignPress

# Flush permalinks
# - Go to Settings → Permalinks
# - Click "Save Changes"
```

### 3. Development Server

```bash
# Start Vite dev server (hot module replacement)
npm run dev

# In separate terminal, start WordPress
# (Local by Flywheel or php -S localhost:8000)
```

---

## Development Cycle

### Step 1: Create Feature Branch

```bash
# Update main branch
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/feature-name

# Example:
git checkout -b feature/add-sms-integration
```

### Step 2: Develop Feature

**Workflow:**
1. Read relevant documentation (CLAUDE.md, ARCHITECTURE.md)
2. Review existing code for patterns
3. Write code following standards
4. Test locally
5. Commit incrementally

**Example Development Session:**
```bash
# Make changes to files
# Edit includes/premium/integrations/sms-integration.php

# Test changes in browser
# Verify functionality works

# Run linting
npm run lint

# Format code
npm run format

# Build for testing
npm run build

# Test built assets
```

### Step 3: Testing

**Manual Testing Checklist:**
- [ ] Feature works as expected
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug.log
- [ ] Responsive design (mobile, tablet, desktop)
- [ ] Accessibility (keyboard navigation, screen reader)
- [ ] Cross-browser (Chrome, Firefox, Safari, Edge)
- [ ] Party color schemes work
- [ ] No conflicts with other plugins

**Automated Testing (if available):**
```bash
# Run PHP tests
vendor/bin/phpunit

# Run JavaScript tests
npm test

# Check code standards
npm run lint
```

### Step 4: Commit Changes

**Commit Message Format:**
```
type(scope): Brief description

Optional detailed explanation

Closes #issue-number
```

**Example:**
```bash
git add includes/premium/integrations/sms-integration.php
git add docs/INTEGRATIONS.md

git commit -m "feat(integrations): Add Twilio SMS integration

Implemented Twilio SMS integration for text message campaigns:
- Added TwilioAPI wrapper class
- Created admin interface for API credentials
- Implemented opt-in/opt-out management
- Added SMS send functionality
- Updated integration documentation

Closes #234"
```

### Step 5: Push and Pull Request

```bash
# Push feature branch
git push origin feature/add-sms-integration

# Create pull request on GitHub
# - Go to repository
# - Click "New Pull Request"
# - Select feature branch
# - Fill out PR template
# - Request review
```

**Pull Request Template:**
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Manual testing completed
- [ ] Automated tests pass
- [ ] Accessibility tested
- [ ] Cross-browser tested

## Checklist
- [ ] Code follows style guidelines
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] No security vulnerabilities introduced
```

### Step 6: Code Review

**Reviewer Checklist:**
- Code quality and standards
- Security concerns
- Performance implications
- Documentation completeness
- Test coverage

**Address Feedback:**
```bash
# Make requested changes
# ... edit files ...

# Commit changes
git add .
git commit -m "fix: Address code review feedback"

# Push updates
git push origin feature/add-sms-integration
```

### Step 7: Merge

**After approval:**
```bash
# Merge via GitHub interface
# or locally:
git checkout main
git merge feature/add-sms-integration
git push origin main

# Delete feature branch
git branch -d feature/add-sms-integration
git push origin --delete feature/add-sms-integration
```

---

## Build Process

### Development Build

```bash
# Start dev server with hot module replacement
npm run dev

# Vite serves at http://localhost:5173
# WordPress proxies to this for HMR
```

### Production Build

```bash
# Build minified assets
npm run build

# Output to assets/dist/
# - assets/dist/js/blocks.js
# - assets/dist/js/crm.js
# - assets/dist/js/main.js
# - assets/dist/css/blocks.css
# - assets/dist/css/crm.css
# - assets/dist/css/main.css
```

### Watch Mode

```bash
# Auto-rebuild on file changes
npm run watch

# Useful for development without HMR
```

---

## Code Quality Workflow

### Linting

```bash
# Run ESLint
npm run lint

# Auto-fix issues
npm run lint:fix

# PHP_CodeSniffer (if installed)
vendor/bin/phpcs --standard=WordPress includes/
```

### Formatting

```bash
# Run Prettier
npm run format

# Format specific files
npx prettier --write "assets/js/**/*.js"
```

### Code Review

**Before Requesting Review:**
1. Run linters and fix issues
2. Format code with Prettier
3. Remove debugging code (console.log, var_dump)
4. Add necessary comments
5. Update documentation
6. Test thoroughly

---

## Database Workflow

### Schema Changes

**Steps:**
1. Update schema in appropriate database class
2. Increment database version number
3. Add migration function
4. Test on fresh install
5. Test on existing install (migration)
6. Document changes

**Example:**
```php
// In includes/premium/crm/class-crm-database.php

private static $db_version = '1.2.0'; // Increment

public static function update_database() {
    $current = get_option('cp_crm_db_version', '1.0.0');

    if (version_compare($current, '1.2.0', '<')) {
        self::migrate_to_1_2_0();
    }

    update_option('cp_crm_db_version', self::$db_version);
}

private static function migrate_to_1_2_0() {
    global $wpdb;
    // Add new column
    $wpdb->query("ALTER TABLE {$wpdb->prefix}cp_contacts
                  ADD COLUMN sms_opt_in TINYINT DEFAULT 0");
}
```

### Testing Migrations

```sql
-- Backup database first!

-- Test migration
UPDATE wp_options SET option_value = '1.1.0'
WHERE option_name = 'cp_crm_db_version';

-- Reload page to trigger migration
-- Verify new column exists
DESCRIBE wp_cp_contacts;
```

---

## Release Workflow

### Pre-Release Checklist

- [ ] All tests passing
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Version numbers incremented
- [ ] Build production assets
- [ ] Security audit completed
- [ ] Performance testing done
- [ ] Backward compatibility verified

### Version Numbering

**Semantic Versioning (MAJOR.MINOR.PATCH):**
- **MAJOR:** Breaking changes
- **MINOR:** New features (backward compatible)
- **PATCH:** Bug fixes

**Example:**
- 2.0.0 → 2.0.1 (bug fix)
- 2.0.1 → 2.1.0 (new feature)
- 2.1.0 → 3.0.0 (breaking change)

### Update Version Numbers

**Files to Update:**
1. `style.css` - Theme version
2. `package.json` - npm version
3. `functions.php` - CAMPAIGNPRESS_VERSION constant
4. `readme.txt` - Stable tag
5. `CHANGELOG.md` - Add release notes

### Create Release

```bash
# Create release branch
git checkout -b release/2.1.0

# Update version numbers
# ... edit files ...

# Build production assets
npm run build

# Commit changes
git add .
git commit -m "chore: Bump version to 2.1.0"

# Merge to main
git checkout main
git merge release/2.1.0

# Tag release
git tag -a v2.1.0 -m "Release version 2.1.0"

# Push
git push origin main --tags

# Create GitHub release
# - Go to Releases
# - Draft new release
# - Select tag v2.1.0
# - Copy CHANGELOG entry
# - Attach theme ZIP file
# - Publish release
```

---

## Deployment Workflow

### Production Deployment

**Option 1: Manual SFTP**
```bash
# Build production assets
npm run build

# Create theme ZIP
zip -r campaign-office-2.1.0.zip . \
  -x "*.git*" \
  -x "node_modules/*" \
  -x "vendor/*" \
  -x ".vscode/*"

# Upload via SFTP to wp-content/themes/
# Activate in WordPress admin
```

**Option 2: Git Deployment**
```bash
# On server
cd /path/to/wp-content/themes/campaign-office
git pull origin main
npm install
npm run build
```

**Option 3: Automated CI/CD**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    tags:
      - 'v*'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'

      - name: Install dependencies
        run: npm install

      - name: Build assets
        run: npm run build

      - name: Deploy via SSH
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /path/to/wp-content/themes/campaign-office
            git pull origin main
            npm install
            npm run build
```

### Post-Deployment

**Checklist:**
- [ ] Verify site loads
- [ ] Check for PHP errors
- [ ] Test critical functionality
- [ ] Verify assets load correctly
- [ ] Check party color schemes
- [ ] Test premium features (if applicable)
- [ ] Monitor error logs
- [ ] Clear caches (WordPress, CDN, browser)

---

## Hotfix Workflow

**For Critical Bugs in Production:**

```bash
# Create hotfix branch from main
git checkout main
git checkout -b hotfix/critical-bug-fix

# Fix the bug
# ... edit files ...

# Test fix
# ... verify bug is resolved ...

# Commit fix
git add .
git commit -m "fix: Critical security vulnerability in contact form

Fixed SQL injection vulnerability in contact form submission.
Replaced direct query with prepared statement.

Security: High Priority"

# Merge to main
git checkout main
git merge hotfix/critical-bug-fix

# Tag hotfix version
git tag -a v2.0.1 -m "Hotfix: Security patch"

# Push
git push origin main --tags

# Deploy immediately
# ... follow deployment workflow ...

# Delete hotfix branch
git branch -d hotfix/critical-bug-fix
```

---

## Documentation Workflow

### When to Update Documentation

**Update documentation when:**
- Adding new features
- Changing existing functionality
- Fixing bugs that affect usage
- Modifying architecture
- Changing configuration

**Files to Update:**
- `CHANGELOG.md` - All changes
- `README.md` - Major features
- `DEVELOPER-GUIDE.md` - Development changes
- `docs/[specific].md` - Feature-specific docs
- Inline code comments - Complex logic

### Documentation Standards

**Markdown Files:**
- Use headings for hierarchy
- Include code examples
- Add tables for comparisons
- Link between documents

**Code Comments:**
```php
/**
 * Calculate engagement score for contact
 *
 * @param int $contact_id Contact ID
 * @return int Engagement score (0-100)
 */
function calculate_engagement_score($contact_id) {
    // Implementation
}
```

---

## Collaboration Workflow

### Communication

**Channels:**
- GitHub Issues - Bug reports, feature requests
- Pull Requests - Code review discussions
- Discussions - General questions
- Email - Premium support

### Issue Workflow

1. **Report Issue** - Describe bug or feature
2. **Triage** - Assign labels, milestone, assignee
3. **Discuss** - Clarify requirements
4. **Implement** - Create feature branch
5. **Review** - Pull request review
6. **Merge** - Merge to main
7. **Close** - Close issue with reference to PR

---

## Best Practices

### DO

✓ Read documentation before coding
✓ Follow coding standards
✓ Write descriptive commit messages
✓ Test thoroughly
✓ Update documentation
✓ Request code review
✓ Fix linter errors
✓ Remove debugging code

### DON'T

✗ Commit to main directly
✗ Skip testing
✗ Ignore linter warnings
✗ Leave console.log() statements
✗ Hardcode sensitive data
✗ Modify WordPress core
✗ Use deprecated functions
✗ Break backward compatibility without major version bump

---

## Troubleshooting

### Common Issues

**Build Errors:**
```bash
# Clear node_modules and reinstall
rm -rf node_modules
npm install

# Clear npm cache
npm cache clean --force
```

**WordPress Errors:**
```php
// Enable debug mode in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Check debug.log
tail -f wp-content/debug.log
```

**Database Errors:**
```sql
-- Check table status
SHOW TABLE STATUS LIKE 'wp_cp_%';

-- Repair tables
REPAIR TABLE wp_cp_contacts;

-- Check for missing indexes
SHOW INDEX FROM wp_cp_contacts;
```

---

## Resources

### Documentation
- [Architecture](ARCHITECTURE.md)
- [Tech Stack](TECH_STACK.md)
- [Agent Workflows](AGENTS.md)
- [Developer Guide](../DEVELOPER-GUIDE.md)

### External Resources
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Semantic Versioning](https://semver.org/)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

**Last Updated:** December 28, 2025
**Version:** 2.0.0
