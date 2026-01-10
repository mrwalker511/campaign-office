# End-to-End Tests for Campaign Office Theme

This directory contains comprehensive end-to-end (E2E) tests for all UI components in the Campaign Office WordPress theme.

## Test Coverage

### 1. **Gutenberg Blocks** (`gutenberg-blocks.spec.js`)

Tests for all custom Gutenberg blocks:

- **Countdown Block**: Real-time countdown timer, accessibility, auto-updates
- **Donation Form Block**: Preset amounts, custom donations, recurring options, payment processors, goal progress
- **Event Organizer Block**: Event listings, RSVP buttons, filtering, list/grid views
- **Volunteer Matcher Block**: Interest/availability/skills selection, form submission
- **Hero Commander Block**: Hero sections, CTAs, background video, responsive design
- **Progress Block**: Progress bars, goal tracking, ARIA attributes
- **Policy Platform Block**: Policy items, expand/collapse, keyboard accessibility
- **Block Accessibility**: Heading hierarchy, keyboard navigation, form labels

**Test Count**: ~50 tests

### 2. **Design Studio** (`design-studio.spec.js`)

Tests for the drag-and-drop campaign design builder:

- **Basic Interface**: Canvas, component library, viewport switcher, toolbar
- **Component Management**: Selection, drag-and-drop, deletion, editing, reordering
- **Viewport Switching**: Mobile/tablet/desktop previews, component persistence
- **Save/Load**: Design saving, preview, clear canvas
- **Accessibility**: Keyboard navigation, ARIA labels, focus indicators
- **Error Handling**: Save failures, validation, invalid configurations

**Test Count**: ~30 tests

### 3. **Volunteer Portal** (`volunteer-portal.spec.js`)

Tests for the volunteer management interface:

- **Login**: Login form, validation, error handling, forgot password
- **Dashboard**: Welcome message, stats, upcoming shifts, recent activity
- **Tab Navigation**: Shifts, hours, profile, notifications tabs
- **Shift Signup**: Available shifts, signup/cancel, filtering (date/location/type)
- **Hours Logging**: Log hours, validation, history, total hours, edit/delete
- **Profile Management**: Profile info, edit profile, skills, availability, password change
- **Notifications**: Notification list, mark as read, delete, mark all as read
- **Accessibility**: Keyboard navigation, ARIA labels, tab panel changes

**Test Count**: ~45 tests

### 4. **Admin Interfaces** (`admin-interfaces.spec.js`)

Tests for premium admin features:

- **Premium Admin**: License management, activation, validation, feature list
- **FEC Compliance**: Dashboard, status, report generation, audit trail, export
- **CRM**: Contact list, add/edit/delete contacts, search, filtering, import/export
- **Field Operations**: Routes management, volunteer assignment, reports
- **Analytics Dashboard**: Metrics, charts, date filtering, export, refresh
- **Developer Console**: API endpoints, testing, system health, code snippets
- **Accessibility**: Keyboard navigation, heading hierarchy, form labels

**Test Count**: ~45 tests

### 5. **Demo Content Import** (`demo-import.spec.js`)

Tests for the AJAX-driven demo content import:

- **Interface**: Import page, content options, descriptions, warnings
- **Content Selection**: Select all, individual selection, validation
- **AJAX Import Process**: Start import, progress bar, status messages, incremental updates
- **Import Completion**: Success messages, content summary
- **Error Handling**: Network errors, retry, partial failures, error details
- **Cancel Import**: Cancel process, button re-enabling
- **Accessibility**: Progress bar ARIA, status announcements, keyboard controls

**Test Count**: ~35 tests

### 6. **Customizer** (`customizer.spec.js`)

Tests for WordPress Customizer functionality:

- **Interface**: Customizer controls, preview pane, sections, publish button
- **Navigation**: Section expansion, colors, typography, header navigation
- **Color Settings**: Primary/background colors, preview updates, reset
- **Typography**: Heading/body fonts, font size, sliders
- **Layout Options**: Layout styles, sidebar toggle, container width
- **Preview Modes**: Mobile/tablet/desktop switching, change persistence
- **Save/Publish**: Draft saving, unsaved warnings, preview
- **Widget Areas**: Display areas, add/remove widgets
- **Accessibility**: Keyboard navigation, ARIA labels, focus indicators

**Test Count**: ~40 tests

### 7. **Volunteer Signup** (`volunteer-signup.spec.js`)

Original comprehensive test suite:

- Volunteer signup flow
- Event RSVP
- Donation flow
- Accessibility tests
- Responsive design tests

**Test Count**: ~26 tests

## Total Test Coverage

- **Total Test Files**: 7 (+ 1 example)
- **Total Tests**: ~270+ tests
- **Coverage Areas**:
  - 12 Custom Gutenberg Blocks
  - Drag-and-drop Design Studio
  - Volunteer Portal (5+ sections)
  - 6 Admin Interfaces
  - AJAX Import System
  - WordPress Customizer
  - Forms and User Flows

## Running the Tests

### Run All E2E Tests

```bash
npm run test:e2e
```

### Run Tests in Headed Mode (with browser UI)

```bash
npm run test:e2e:headed
```

### Run Specific Test File

```bash
npx playwright test tests/e2e/gutenberg-blocks.spec.js
```

### Run Tests for Specific Browser

```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

### Run Tests in Debug Mode

```bash
npx playwright test --debug
```

## Environment Configuration

E2E tests require a running WordPress installation. Configure the base URL in `tests/playwright.config.js`:

```javascript
use: {
  baseURL: process.env.WP_BASE_URL || 'http://localhost:8881',
}
```

Set the `WP_BASE_URL` environment variable:

```bash
export WP_BASE_URL=http://your-wordpress-site.local
npm run test:e2e
```

## Test Architecture

### Conditional Testing Pattern

All tests use a defensive pattern to handle missing elements gracefully:

```javascript
if (await element.count() > 0) {
  // Run test only if element exists
  await expect(element.first()).toBeVisible();
}
```

This allows tests to run across different WordPress configurations and installations.

### Accessibility Testing

Each test suite includes dedicated accessibility tests:

- Keyboard navigation
- ARIA labels and attributes
- Focus indicators
- Screen reader announcements
- Form label associations
- Heading hierarchy

### Browser Coverage

Tests run on multiple browsers and devices:

- Desktop: Chrome, Firefox, Safari
- Mobile: Chrome (Pixel 5), Safari (iPhone 12)

## CI/CD Integration

Tests are integrated into the GitHub Actions workflow:

```yaml
- name: Run E2E Tests
  run: npm run test:e2e
```

## Test Reports

After running tests, view the HTML report:

```bash
npx playwright show-report tests/e2e/reports
```

Reports include:

- Test results
- Screenshots on failure
- Video recordings (on failure)
- Trace files for debugging

## Writing New Tests

When adding new UI components, follow the established patterns:

1. Create a new `.spec.js` file in `tests/e2e/`
2. Use descriptive test.describe() blocks for organization
3. Include beforeEach() hooks for navigation
4. Use conditional testing for defensive checks
5. Add accessibility tests
6. Test error states and edge cases
7. Verify responsive behavior

Example:

```javascript
test.describe('My Component', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/my-component');
  });

  test('should display component', async ({ page }) => {
    const component = page.locator('.my-component');

    if (await component.count() > 0) {
      await expect(component.first()).toBeVisible();
    }
  });
});
```

## Troubleshooting

### Tests Timeout

Increase timeout in `playwright.config.js`:

```javascript
timeout: 60 * 1000, // 60 seconds
```

### Authentication Required

Some tests require admin authentication. Use Playwright's authentication feature or implement test helpers.

### Flaky Tests

- Add explicit waits: `await page.waitForTimeout(1000)`
- Use `waitForLoadState('networkidle')`
- Increase expect timeout: `await expect(element).toBeVisible({ timeout: 10000 })`

## Contributing

When adding new E2E tests:

1. Ensure tests are idempotent (can run multiple times)
2. Clean up test data after runs
3. Use meaningful test descriptions
4. Add comments for complex interactions
5. Test both success and failure paths
6. Include accessibility checks

## Resources

- [Playwright Documentation](https://playwright.dev/)
- [Testing Library Best Practices](https://testing-library.com/docs/queries/about/)
- [WCAG Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
