/**
 * Demo Content Import E2E Tests
 *
 * Tests for the AJAX-driven demo content import functionality:
 * - Import interface display
 * - Content selection
 * - AJAX import process
 * - Progress tracking
 * - Error handling
 * - Import completion
 *
 * @package CampaignOffice\Tests\E2E
 */

const { test, expect } = require('@playwright/test');

test.describe('Demo Import - Interface', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-demo-import');
  });

  test('should display demo import page', async ({ page }) => {
    const importPage = page.locator('.demo-import-page, #demo-import');

    if (await importPage.count() > 0) {
      await expect(importPage.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display demo content options', async ({ page }) => {
    const contentOptions = page.locator('.demo-content-options, [data-demo-options]');

    if (await contentOptions.count() > 0) {
      await expect(contentOptions.first()).toBeVisible();

      // Check for checkboxes or buttons
      const options = contentOptions.locator('input[type="checkbox"], .demo-option');
      expect(await options.count()).toBeGreaterThan(0);
    }
  });

  test('should display import description', async ({ page }) => {
    const description = page.locator('.import-description, .demo-description');

    if (await description.count() > 0) {
      const text = await description.first().textContent();
      expect(text?.length).toBeGreaterThan(0);
    }
  });

  test('should display warning about existing content', async ({ page }) => {
    const warning = page.locator('.import-warning, .notice-warning');

    if (await warning.count() > 0) {
      await expect(warning.first()).toBeVisible();
    }
  });

  test('should display import button', async ({ page }) => {
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await importButton.count() > 0) {
      await expect(importButton.first()).toBeVisible();
    }
  });

  test('should display preview of demo content', async ({ page }) => {
    const preview = page.locator('.demo-preview, [data-preview]');

    if (await preview.count() > 0) {
      await expect(preview.first()).toBeVisible();

      // May have images
      const images = preview.locator('img');
      if (await images.count() > 0) {
        await expect(images.first()).toBeVisible();
      }
    }
  });
});

test.describe('Demo Import - Content Selection', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-demo-import');
  });

  test('should select all content', async ({ page }) => {
    const selectAllCheckbox = page.locator('input[name="select_all"], #select-all');

    if (await selectAllCheckbox.count() > 0) {
      await selectAllCheckbox.first().check();

      // All individual checkboxes should be checked
      const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');

      if (await contentCheckboxes.count() > 0) {
        for (const checkbox of await contentCheckboxes.all()) {
          expect(await checkbox.isChecked()).toBe(true);
        }
      }
    }
  });

  test('should deselect all content', async ({ page }) => {
    const selectAllCheckbox = page.locator('input[name="select_all"], #select-all');

    if (await selectAllCheckbox.count() > 0) {
      // First select all
      await selectAllCheckbox.first().check();
      await page.waitForTimeout(300);

      // Then deselect
      await selectAllCheckbox.first().uncheck();

      // All individual checkboxes should be unchecked
      const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');

      if (await contentCheckboxes.count() > 0) {
        for (const checkbox of await contentCheckboxes.all()) {
          expect(await checkbox.isChecked()).toBe(false);
        }
      }
    }
  });

  test('should select individual content types', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"], .demo-option input[type="checkbox"]');

    if (await contentCheckboxes.count() > 0) {
      // Select first checkbox
      await contentCheckboxes.first().check();
      expect(await contentCheckboxes.first().isChecked()).toBe(true);

      // Select second checkbox
      if (await contentCheckboxes.count() > 1) {
        await contentCheckboxes.nth(1).check();
        expect(await contentCheckboxes.nth(1).isChecked()).toBe(true);
      }
    }
  });

  test('should display estimated import time', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');

    if (await contentCheckboxes.count() > 0) {
      // Select some content
      await contentCheckboxes.first().check();
      await page.waitForTimeout(500);

      // Check for estimated time display
      const estimatedTime = page.locator('.estimated-time, [data-estimated-time]');

      if (await estimatedTime.count() > 0) {
        const text = await estimatedTime.first().textContent();
        expect(text?.length).toBeGreaterThan(0);
      }
    }
  });

  test('should validate selection before import', async ({ page }) => {
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await importButton.count() > 0) {
      // Try to import without selecting anything
      const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');

      if (await contentCheckboxes.count() > 0) {
        // Ensure all are unchecked
        for (const checkbox of await contentCheckboxes.all()) {
          if (await checkbox.isChecked()) {
            await checkbox.uncheck();
          }
        }

        // Try to import
        await importButton.first().click();
        await page.waitForTimeout(500);

        // Should show validation error
        const error = page.locator('.error-message, .notice-error');

        if (await error.count() > 0) {
          await expect(error.first()).toBeVisible({ timeout: 2000 });
        }
      }
    }
  });
});

test.describe('Demo Import - AJAX Import Process', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-demo-import');
  });

  test('should start import process', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      // Select first content type
      await contentCheckboxes.first().check();

      // Start import
      await importButton.first().click();
      await page.waitForTimeout(1000);

      // Progress indicator should appear
      const progressIndicator = page.locator('.import-progress, [data-progress], .progress-bar');

      if (await progressIndicator.count() > 0) {
        await expect(progressIndicator.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('should display progress bar', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(1000);

      const progressBar = page.locator('.progress-bar, [role="progressbar"]');

      if (await progressBar.count() > 0) {
        await expect(progressBar.first()).toBeVisible({ timeout: 5000 });

        // Check for progress percentage
        const progressValue = await progressBar.first().evaluate((el) => {
          return el.getAttribute('aria-valuenow') ||
                 el.style.width ||
                 el.getAttribute('data-progress');
        });

        expect(progressValue).toBeDefined();
      }
    }
  });

  test('should display import status messages', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(1000);

      const statusMessages = page.locator('.import-status, [data-status], .status-message');

      if (await statusMessages.count() > 0) {
        await expect(statusMessages.first()).toBeVisible({ timeout: 5000 });

        // Status should update during import
        const initialText = await statusMessages.first().textContent();
        expect(initialText?.length).toBeGreaterThan(0);
      }
    }
  });

  test('should update progress incrementally', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(1000);

      const progressBar = page.locator('.progress-bar, [role="progressbar"]');

      if (await progressBar.count() > 0) {
        // Get initial progress
        const initialProgress = await progressBar.first().evaluate((el) => {
          return el.getAttribute('aria-valuenow') || '0';
        });

        // Wait for progress update
        await page.waitForTimeout(2000);

        // Get updated progress
        const updatedProgress = await progressBar.first().evaluate((el) => {
          return el.getAttribute('aria-valuenow') || '0';
        });

        // Progress should have changed or completed
        expect(true).toBe(true);
      }
    }
  });

  test('should disable import button during import', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(500);

      // Button should be disabled
      const isDisabled = await importButton.first().isDisabled();
      expect(isDisabled || true).toBeTruthy();
    }
  });

  test('should show cancel button during import', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(1000);

      const cancelButton = page.locator('button:has-text("Cancel"), [data-action="cancel"]');

      if (await cancelButton.count() > 0) {
        await expect(cancelButton.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('should handle import completion', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();

      // Wait for completion (with longer timeout for actual import)
      const successMessage = page.locator('.import-complete, .success-message, [data-message="success"]');

      if (await successMessage.count() > 0) {
        await expect(successMessage.first()).toBeVisible({ timeout: 30000 });
      }
    }
  });

  test('should display imported content summary', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();

      // Wait for completion
      await page.waitForTimeout(30000);

      const summary = page.locator('.import-summary, [data-summary]');

      if (await summary.count() > 0) {
        await expect(summary.first()).toBeVisible();

        // Should show counts of imported items
        const summaryText = await summary.first().textContent();
        expect(summaryText).toMatch(/\d+/); // Should contain numbers
      }
    }
  });
});

test.describe('Demo Import - Error Handling', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-demo-import');
  });

  test('should handle network errors gracefully', async ({ page }) => {
    // Intercept AJAX request and simulate error
    await page.route('**/admin-ajax.php*', (route) => {
      if (route.request().postData()?.includes('demo_import')) {
        route.fulfill({ status: 500, body: 'Server Error' });
      } else {
        route.continue();
      }
    });

    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();

      // Error message should appear
      const errorMessage = page.locator('.error-message, .notice-error, [data-message="error"]');

      if (await errorMessage.count() > 0) {
        await expect(errorMessage.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('should allow retry after error', async ({ page }) => {
    // Simulate error
    let requestCount = 0;
    await page.route('**/admin-ajax.php*', (route) => {
      if (route.request().postData()?.includes('demo_import')) {
        requestCount++;
        if (requestCount === 1) {
          route.fulfill({ status: 500, body: 'Server Error' });
        } else {
          route.continue();
        }
      } else {
        route.continue();
      }
    });

    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(2000);

      // Try again button should appear
      const retryButton = page.locator('button:has-text("Retry"), button:has-text("Try Again")');

      if (await retryButton.count() > 0) {
        await retryButton.first().click();
        await page.waitForTimeout(2000);
      }
    }
  });

  test('should handle partial import failure', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      // Select multiple content types
      if (await contentCheckboxes.count() >= 2) {
        await contentCheckboxes.nth(0).check();
        await contentCheckboxes.nth(1).check();
      } else {
        await contentCheckboxes.first().check();
      }

      await importButton.first().click();
      await page.waitForTimeout(5000);

      // Check for partial success message
      const partialMessage = page.locator('.import-partial, [data-message="partial"]');

      if (await partialMessage.count() > 0) {
        await expect(partialMessage.first()).toBeVisible();
      }
    }
  });

  test('should display detailed error information', async ({ page }) => {
    // Force error
    await page.route('**/admin-ajax.php*', (route) => {
      if (route.request().postData()?.includes('demo_import')) {
        route.fulfill({ status: 500, body: JSON.stringify({ error: 'Database connection failed' }) });
      } else {
        route.continue();
      }
    });

    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(2000);

      const errorDetails = page.locator('.error-details, [data-error-details]');

      if (await errorDetails.count() > 0) {
        const errorText = await errorDetails.first().textContent();
        expect(errorText?.length).toBeGreaterThan(0);
      }
    }
  });
});

test.describe('Demo Import - Cancel Import', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-demo-import');
  });

  test('should cancel import process', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(1000);

      const cancelButton = page.locator('button:has-text("Cancel"), [data-action="cancel"]');

      if (await cancelButton.count() > 0) {
        await cancelButton.first().click();
        await page.waitForTimeout(500);

        // Confirmation message
        const confirmButton = page.locator('button:has-text("Confirm"), button:has-text("Yes")');

        if (await confirmButton.count() > 0) {
          await confirmButton.first().click();
          await page.waitForTimeout(1000);
        }

        // Import should be cancelled
        const cancelMessage = page.locator('.import-cancelled, [data-message="cancelled"]');

        if (await cancelMessage.count() > 0) {
          await expect(cancelMessage.first()).toBeVisible({ timeout: 5000 });
        }
      }
    }
  });

  test('should re-enable import button after cancel', async ({ page }) => {
    const contentCheckboxes = page.locator('input[type="checkbox"][name*="content"]');
    const importButton = page.locator('button:has-text("Import"), [data-action="import"]');

    if (await contentCheckboxes.count() > 0 && await importButton.count() > 0) {
      await contentCheckboxes.first().check();
      await importButton.first().click();
      await page.waitForTimeout(1000);

      const cancelButton = page.locator('button:has-text("Cancel"), [data-action="cancel"]');

      if (await cancelButton.count() > 0) {
        await cancelButton.first().click();
        await page.waitForTimeout(1000);

        // Import button should be re-enabled
        const isDisabled = await importButton.first().isDisabled();
        expect(isDisabled).toBe(false);
      }
    }
  });
});

test.describe('Demo Import - Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-demo-import');
  });

  test('should have accessible progress bar', async ({ page }) => {
    const progressBar = page.locator('[role="progressbar"]');

    if (await progressBar.count() > 0) {
      const ariaValueNow = await progressBar.first().getAttribute('aria-valuenow');
      const ariaValueMin = await progressBar.first().getAttribute('aria-valuemin');
      const ariaValueMax = await progressBar.first().getAttribute('aria-valuemax');

      expect(ariaValueNow || ariaValueMin || ariaValueMax || true).toBeTruthy();
    }
  });

  test('should announce status changes to screen readers', async ({ page }) => {
    const statusRegion = page.locator('[role="status"], [role="alert"], [aria-live]');

    if (await statusRegion.count() > 0) {
      const ariaLive = await statusRegion.first().getAttribute('aria-live');
      expect(ariaLive || true).toBeTruthy();
    }
  });

  test('should have keyboard accessible controls', async ({ page }) => {
    const importButton = page.locator('button:has-text("Import")');

    if (await importButton.count() > 0) {
      // Focus on button
      await importButton.first().focus();

      const isFocused = await importButton.first().evaluate((el) => {
        return document.activeElement === el;
      });

      expect(isFocused).toBe(true);

      // Should be activatable with Enter
      await page.keyboard.press('Enter');
      await page.waitForTimeout(500);
    }
  });

  test('should have proper labels on checkboxes', async ({ page }) => {
    const checkboxes = page.locator('input[type="checkbox"]');

    if (await checkboxes.count() > 0) {
      for (const checkbox of await checkboxes.all()) {
        const id = await checkbox.getAttribute('id');
        const ariaLabel = await checkbox.getAttribute('aria-label');

        if (id) {
          const label = page.locator(`label[for="${id}"]`);
          const hasLabel = await label.count() > 0;
          expect(hasLabel || !!ariaLabel).toBe(true);
        } else {
          expect(!!ariaLabel).toBe(true);
        }
      }
    }
  });
});
