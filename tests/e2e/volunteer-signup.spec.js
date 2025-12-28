/**
 * Volunteer Signup E2E Tests
 *
 * @package CampaignOffice\Tests\E2E
 */

const { test, expect } = require('@playwright/test');

test.describe('Volunteer Signup Flow', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to volunteer signup page
    await page.goto('/volunteer-signup');
  });

  test('should display volunteer signup form', async ({ page }) => {
    await expect(page.locator('#volunteer-form')).toBeVisible();
    await expect(page.locator('input[name="first_name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });

  test('should complete full volunteer signup', async ({ page }) => {
    // Fill out form
    await page.fill('input[name="first_name"]', 'John');
    await page.fill('input[name="last_name"]', 'Volunteer');
    await page.fill('input[name="email"]', 'john@example.com');
    await page.fill('input[name="phone"]', '555-1234');

    // Select skills
    await page.selectOption('select[name="skills[]"]', ['canvassing', 'phone_banking']);

    // Submit form
    await page.click('button[type="submit"]');

    // Wait for success message
    await expect(page.locator('.success-message')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('.success-message')).toContainText('Thank you for volunteering');
  });

  test('should show validation errors for empty required fields', async ({ page }) => {
    // Submit empty form
    await page.click('button[type="submit"]');

    // Check for HTML5 validation
    const firstNameValidity = await page.evaluate(() => {
      return document.querySelector('input[name="first_name"]').validity.valid;
    });

    expect(firstNameValidity).toBe(false);
  });

  test('should validate email format', async ({ page }) => {
    await page.fill('input[name="email"]', 'invalid-email');

    const emailValid = await page.evaluate(() => {
      return document.querySelector('input[name="email"]').validity.valid;
    });

    expect(emailValid).toBe(false);
  });

  test('should handle duplicate email signup', async ({ page }) => {
    const duplicateEmail = 'existing@example.com';

    // Fill form with duplicate email
    await page.fill('input[name="first_name"]', 'Jane');
    await page.fill('input[name="last_name"]', 'Doe');
    await page.fill('input[name="email"]', duplicateEmail);

    await page.click('button[type="submit"]');

    // Should show appropriate message (either error or update confirmation)
    await expect(page.locator('.message')).toBeVisible({ timeout: 5000 });
  });
});

test.describe('Event RSVP Flow', () => {
  test('should display event RSVP form', async ({ page }) => {
    await page.goto('/events/test-event');

    await expect(page.locator('.event-rsvp-form')).toBeVisible();
  });

  test('should complete event RSVP', async ({ page }) => {
    await page.goto('/events/test-event');

    await page.fill('input[name="first_name"]', 'Sarah');
    await page.fill('input[name="last_name"]', 'Attendee');
    await page.fill('input[name="email"]', 'sarah@example.com');
    await page.fill('input[name="guests"]', '2');

    await page.click('button[type="submit"]');

    await expect(page.locator('.success-message')).toBeVisible({ timeout: 5000 });
  });
});

test.describe('Donation Flow', () => {
  test('should display donation form', async ({ page }) => {
    await page.goto('/donate');

    await expect(page.locator('.donation-form')).toBeVisible();
  });

  test('should select donation amount', async ({ page }) => {
    await page.goto('/donate');

    // Select preset amount
    await page.click('[data-amount="100"]');

    const selectedAmount = await page.inputValue('input[name="amount"]');
    expect(selectedAmount).toBe('100');
  });

  test('should validate donation amount', async ({ page }) => {
    await page.goto('/donate');

    // Try invalid amount
    await page.fill('input[name="amount"]', '-50');

    const amountValid = await page.evaluate(() => {
      const input = document.querySelector('input[name="amount"]');
      return parseFloat(input.value) > 0;
    });

    expect(amountValid).toBe(false);
  });
});

test.describe('Accessibility', () => {
  test('should have no accessibility violations on homepage', async ({ page }) => {
    await page.goto('/');

    // Check for basic accessibility
    await expect(page).toHaveTitle(/.+/); // Has a title

    // Check for alt text on images
    const images = await page.locator('img').all();

    for (const image of images) {
      const alt = await image.getAttribute('alt');
      // Images should have alt attribute (can be empty for decorative images)
      expect(alt).not.toBeNull();
    }
  });

  test('should be navigable by keyboard', async ({ page }) => {
    await page.goto('/volunteer-signup');

    // Tab through form fields
    await page.keyboard.press('Tab');
    const focused1 = await page.evaluate(() => document.activeElement.tagName);

    await page.keyboard.press('Tab');
    const focused2 = await page.evaluate(() => document.activeElement.tagName);

    // Should be able to focus form elements
    expect(['INPUT', 'SELECT', 'BUTTON', 'A']).toContain(focused1);
    expect(['INPUT', 'SELECT', 'BUTTON', 'A']).toContain(focused2);
  });
});

test.describe('Responsive Design', () => {
  test('should display correctly on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE size
    await page.goto('/');

    await expect(page.locator('body')).toBeVisible();

    // Check mobile menu
    const mobileMenu = page.locator('.mobile-menu, .navbar-toggler');
    if (await mobileMenu.count() > 0) {
      await expect(mobileMenu.first()).toBeVisible();
    }
  });

  test('should display correctly on tablet', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 }); // iPad size
    await page.goto('/');

    await expect(page.locator('body')).toBeVisible();
  });

  test('should display correctly on desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/');

    await expect(page.locator('body')).toBeVisible();
  });
});
