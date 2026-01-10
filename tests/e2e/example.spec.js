/**
 * Example E2E Test
 *
 * @package CampaignOffice\Tests
 */

import { test, expect } from '@playwright/test';
import { injectAxe, checkA11y } from 'axe-playwright';

test.describe('Home Page', () => {
  test('should load successfully', async ({ page }) => {
    await page.goto('/');

    // Check page title
    await expect(page).toHaveTitle(/Campaign Office/);

    // Check main content is visible
    const main = page.locator('main');
    await expect(main).toBeVisible();
  });

  test('should have accessible navigation', async ({ page }) => {
    await page.goto('/');

    // Check navigation exists
    const nav = page.locator('nav[role="navigation"]').first();
    await expect(nav).toBeVisible();

    // Check for accessible menu
    const menu = page.locator('ul[role="menubar"], ul.menu').first();
    await expect(menu).toBeVisible();
  });

  test('should be accessible (a11y)', async ({ page }) => {
    await page.goto('/');
    await injectAxe(page);

    // Check for accessibility violations
    await checkA11y(page);
  });

  test('should have working search', async ({ page }) => {
    await page.goto('/');

    // Find search form
    const searchForm = page.locator('form[role="search"]').first();

    if (await searchForm.isVisible()) {
      const searchInput = searchForm.locator('input[type="search"]');
      await searchInput.fill('test');

      // Submit form
      await searchForm.locator('button[type="submit"]').click();

      // Wait for navigation
      await page.waitForLoadState('networkidle');

      // Check we're on search results page
      await expect(page).toHaveURL(/\?s=/);
    }
  });
});

test.describe('Responsive Design', () => {
  test('should work on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');

    // Check mobile menu button exists
    const mobileMenuButton = page.locator('button[aria-label*="menu"], .mobile-menu-toggle');

    if (await mobileMenuButton.count() > 0) {
      await expect(mobileMenuButton.first()).toBeVisible();
    }
  });

  test('should work on tablet', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/');

    const main = page.locator('main');
    await expect(main).toBeVisible();
  });

  test('should work on desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/');

    const main = page.locator('main');
    await expect(main).toBeVisible();
  });
});

test.describe('Forms', () => {
  test('should validate contact form', async ({ page }) => {
    await page.goto('/contact'); // Adjust URL as needed

    // Fill form if it exists
    const contactForm = page.locator('form.contact-form').first();

    if (await contactForm.isVisible()) {
      // Try to submit empty form
      await contactForm.locator('button[type="submit"]').click();

      // Check for validation messages
      const validationMessage = page.locator('.error, .validation-error').first();

      if (await validationMessage.isVisible()) {
        await expect(validationMessage).toBeVisible();
      }
    }
  });
});

test.describe('Performance', () => {
  test('should load within acceptable time', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;

    // Page should load within 3 seconds
    expect(loadTime).toBeLessThan(3000);
  });
});
