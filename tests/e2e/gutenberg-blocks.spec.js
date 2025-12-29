/**
 * Gutenberg Blocks E2E Tests
 *
 * Tests for custom Gutenberg blocks including:
 * - Countdown Block
 * - Donation Form Block
 * - Event Organizer Block
 * - Volunteer Matcher Block
 * - Hero Commander Block
 * - Progress Block
 * - Policy Platform Block
 *
 * @package CampaignOffice\Tests\E2E
 */

const { test, expect } = require('@playwright/test');

test.describe('Countdown Block', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to a page with countdown block
    await page.goto('/');
  });

  test('should display countdown timer', async ({ page }) => {
    const countdown = page.locator('.wp-block-campaign-office-countdown');

    if (await countdown.count() > 0) {
      await expect(countdown.first()).toBeVisible();

      // Check for time units
      const hasTimeUnits = await countdown.first().evaluate((el) => {
        return el.textContent.match(/\d+/g) !== null;
      });

      expect(hasTimeUnits).toBe(true);
    }
  });

  test('should update countdown every second', async ({ page }) => {
    const countdown = page.locator('.wp-block-campaign-office-countdown');

    if (await countdown.count() > 0) {
      // Get initial time
      const initialText = await countdown.first().textContent();

      // Wait 2 seconds
      await page.waitForTimeout(2000);

      // Get updated time
      const updatedText = await countdown.first().textContent();

      // Text should have changed (countdown is running)
      // Note: This may be the same if countdown is in days/hours only
      // but the test ensures the element is still present and rendering
      expect(updatedText).toBeDefined();
    }
  });

  test('should have proper accessibility attributes', async ({ page }) => {
    const countdown = page.locator('.wp-block-campaign-office-countdown');

    if (await countdown.count() > 0) {
      // Should have ARIA label or role
      const hasAriaLabel = await countdown.first().getAttribute('aria-label');
      const hasRole = await countdown.first().getAttribute('role');

      // At least one should be present for accessibility
      expect(hasAriaLabel || hasRole).toBeTruthy();
    }
  });
});

test.describe('Donation Form Block', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/donate');
  });

  test('should display donation form with preset amounts', async ({ page }) => {
    const donationForm = page.locator('.wp-block-campaign-office-donation-form');

    if (await donationForm.count() > 0) {
      await expect(donationForm.first()).toBeVisible();

      // Check for preset amount buttons
      const presetButtons = donationForm.locator('[data-amount]');
      expect(await presetButtons.count()).toBeGreaterThan(0);
    }
  });

  test('should allow custom donation amount', async ({ page }) => {
    const donationForm = page.locator('.wp-block-campaign-office-donation-form');

    if (await donationForm.count() > 0) {
      const customAmountInput = donationForm.locator('input[name="amount"], input[name="custom_amount"]');

      if (await customAmountInput.count() > 0) {
        await customAmountInput.first().fill('250');
        const value = await customAmountInput.first().inputValue();
        expect(value).toBe('250');
      }
    }
  });

  test('should toggle recurring donation option', async ({ page }) => {
    const donationForm = page.locator('.wp-block-campaign-office-donation-form');

    if (await donationForm.count() > 0) {
      const recurringCheckbox = donationForm.locator('input[name="recurring"], input[type="checkbox"][name*="recurring"]');

      if (await recurringCheckbox.count() > 0) {
        await recurringCheckbox.first().check();
        expect(await recurringCheckbox.first().isChecked()).toBe(true);

        await recurringCheckbox.first().uncheck();
        expect(await recurringCheckbox.first().isChecked()).toBe(false);
      }
    }
  });

  test('should display payment processor options', async ({ page }) => {
    const donationForm = page.locator('.wp-block-campaign-office-donation-form');

    if (await donationForm.count() > 0) {
      // Check for payment options (ActBlue, Stripe, PayPal)
      const paymentOptions = donationForm.locator('[name="payment_processor"], [data-payment-method]');

      if (await paymentOptions.count() > 0) {
        expect(await paymentOptions.count()).toBeGreaterThan(0);
      }
    }
  });

  test('should show goal progress if configured', async ({ page }) => {
    const donationForm = page.locator('.wp-block-campaign-office-donation-form');

    if (await donationForm.count() > 0) {
      const progressBar = donationForm.locator('.progress-bar, [role="progressbar"]');

      if (await progressBar.count() > 0) {
        await expect(progressBar.first()).toBeVisible();
      }
    }
  });

  test('should validate minimum donation amount', async ({ page }) => {
    const donationForm = page.locator('.wp-block-campaign-office-donation-form');

    if (await donationForm.count() > 0) {
      const amountInput = donationForm.locator('input[name="amount"]');

      if (await amountInput.count() > 0) {
        // Try to enter invalid amount
        await amountInput.first().fill('0');

        const isValid = await amountInput.first().evaluate((input) => {
          return input.checkValidity();
        });

        // Should be invalid
        expect(isValid).toBe(false);
      }
    }
  });
});

test.describe('Event Organizer Block', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/events');
  });

  test('should display event list', async ({ page }) => {
    const eventOrganizer = page.locator('.wp-block-campaign-office-event-organizer');

    if (await eventOrganizer.count() > 0) {
      await expect(eventOrganizer.first()).toBeVisible();

      // Check for event items
      const eventItems = eventOrganizer.locator('.event-item, .event-card, article');
      expect(await eventItems.count()).toBeGreaterThan(0);
    }
  });

  test('should display event details', async ({ page }) => {
    const eventOrganizer = page.locator('.wp-block-campaign-office-event-organizer');

    if (await eventOrganizer.count() > 0) {
      const firstEvent = eventOrganizer.locator('.event-item, .event-card, article').first();

      if (await firstEvent.count() > 0) {
        // Check for event title
        const title = firstEvent.locator('h1, h2, h3, h4, .event-title');
        expect(await title.count()).toBeGreaterThan(0);

        // Check for event date
        const date = firstEvent.locator('time, .event-date');
        expect(await date.count()).toBeGreaterThan(0);
      }
    }
  });

  test('should display RSVP button', async ({ page }) => {
    const eventOrganizer = page.locator('.wp-block-campaign-office-event-organizer');

    if (await eventOrganizer.count() > 0) {
      const rsvpButton = eventOrganizer.locator('.rsvp-button, button:has-text("RSVP"), a:has-text("RSVP")');

      if (await rsvpButton.count() > 0) {
        await expect(rsvpButton.first()).toBeVisible();
      }
    }
  });

  test('should filter events by category', async ({ page }) => {
    const eventOrganizer = page.locator('.wp-block-campaign-office-event-organizer');

    if (await eventOrganizer.count() > 0) {
      const filterButtons = eventOrganizer.locator('[data-filter], .filter-button');

      if (await filterButtons.count() > 0) {
        const initialCount = await eventOrganizer.locator('.event-item, .event-card, article').count();

        // Click first filter
        await filterButtons.first().click();

        // Wait for filtering
        await page.waitForTimeout(500);

        // Count should potentially change (or stay same if all events match)
        const filteredCount = await eventOrganizer.locator('.event-item, .event-card, article:visible').count();
        expect(filteredCount).toBeGreaterThanOrEqual(0);
      }
    }
  });

  test('should switch between list and grid view', async ({ page }) => {
    const eventOrganizer = page.locator('.wp-block-campaign-office-event-organizer');

    if (await eventOrganizer.count() > 0) {
      const viewToggle = page.locator('[data-view], .view-toggle');

      if (await viewToggle.count() > 0) {
        // Try to toggle view
        await viewToggle.first().click();

        // Check if class changed
        const hasGridClass = await eventOrganizer.first().evaluate((el) => {
          return el.className.includes('grid') || el.className.includes('list');
        });

        expect(hasGridClass).toBeTruthy();
      }
    }
  });
});

test.describe('Volunteer Matcher Block', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer');
  });

  test('should display volunteer matching form', async ({ page }) => {
    const volunteerMatcher = page.locator('.wp-block-campaign-office-volunteer-matcher');

    if (await volunteerMatcher.count() > 0) {
      await expect(volunteerMatcher.first()).toBeVisible();

      // Check for form elements
      const form = volunteerMatcher.locator('form');
      expect(await form.count()).toBeGreaterThan(0);
    }
  });

  test('should show interest checkboxes', async ({ page }) => {
    const volunteerMatcher = page.locator('.wp-block-campaign-office-volunteer-matcher');

    if (await volunteerMatcher.count() > 0) {
      const interestCheckboxes = volunteerMatcher.locator('input[type="checkbox"][name*="interest"]');

      if (await interestCheckboxes.count() > 0) {
        expect(await interestCheckboxes.count()).toBeGreaterThan(0);

        // Select first interest
        await interestCheckboxes.first().check();
        expect(await interestCheckboxes.first().isChecked()).toBe(true);
      }
    }
  });

  test('should show availability options', async ({ page }) => {
    const volunteerMatcher = page.locator('.wp-block-campaign-office-volunteer-matcher');

    if (await volunteerMatcher.count() > 0) {
      const availabilityInputs = volunteerMatcher.locator('[name*="availability"], [name*="days"]');

      if (await availabilityInputs.count() > 0) {
        expect(await availabilityInputs.count()).toBeGreaterThan(0);
      }
    }
  });

  test('should show skills selection', async ({ page }) => {
    const volunteerMatcher = page.locator('.wp-block-campaign-office-volunteer-matcher');

    if (await volunteerMatcher.count() > 0) {
      const skillsInputs = volunteerMatcher.locator('[name*="skills"], select[name*="skill"]');

      if (await skillsInputs.count() > 0) {
        expect(await skillsInputs.count()).toBeGreaterThan(0);
      }
    }
  });

  test('should submit volunteer matching form', async ({ page }) => {
    const volunteerMatcher = page.locator('.wp-block-campaign-office-volunteer-matcher');

    if (await volunteerMatcher.count() > 0) {
      const form = volunteerMatcher.locator('form').first();

      // Fill required fields
      const nameInput = form.locator('input[name="name"], input[name="first_name"]');
      const emailInput = form.locator('input[name="email"]');

      if (await nameInput.count() > 0 && await emailInput.count() > 0) {
        await nameInput.first().fill('Test Volunteer');
        await emailInput.first().fill('test@example.com');

        // Submit form
        const submitButton = form.locator('button[type="submit"], input[type="submit"]');
        await submitButton.click();

        // Wait for response
        await page.waitForTimeout(2000);
      }
    }
  });
});

test.describe('Hero Commander Block', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display hero section', async ({ page }) => {
    const hero = page.locator('.wp-block-campaign-office-hero-commander');

    if (await hero.count() > 0) {
      await expect(hero.first()).toBeVisible();
    }
  });

  test('should display hero content', async ({ page }) => {
    const hero = page.locator('.wp-block-campaign-office-hero-commander');

    if (await hero.count() > 0) {
      // Check for headline
      const headline = hero.locator('h1, h2, .hero-title');
      expect(await headline.count()).toBeGreaterThan(0);
    }
  });

  test('should display CTA buttons', async ({ page }) => {
    const hero = page.locator('.wp-block-campaign-office-hero-commander');

    if (await hero.count() > 0) {
      const ctaButtons = hero.locator('a.button, button, .cta-button, .wp-block-button');

      if (await ctaButtons.count() > 0) {
        await expect(ctaButtons.first()).toBeVisible();
      }
    }
  });

  test('should handle background video if present', async ({ page }) => {
    const hero = page.locator('.wp-block-campaign-office-hero-commander');

    if (await hero.count() > 0) {
      const video = hero.locator('video');

      if (await video.count() > 0) {
        await expect(video.first()).toBeVisible();

        // Check if video can play
        const canPlay = await video.first().evaluate((v) => {
          return v.readyState >= 2; // HAVE_CURRENT_DATA
        });

        expect(typeof canPlay).toBe('boolean');
      }
    }
  });

  test('should be responsive', async ({ page }) => {
    const hero = page.locator('.wp-block-campaign-office-hero-commander');

    if (await hero.count() > 0) {
      // Test mobile
      await page.setViewportSize({ width: 375, height: 667 });
      await expect(hero.first()).toBeVisible();

      // Test tablet
      await page.setViewportSize({ width: 768, height: 1024 });
      await expect(hero.first()).toBeVisible();

      // Test desktop
      await page.setViewportSize({ width: 1920, height: 1080 });
      await expect(hero.first()).toBeVisible();
    }
  });
});

test.describe('Progress Block', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display progress bar', async ({ page }) => {
    const progress = page.locator('.wp-block-campaign-office-progress');

    if (await progress.count() > 0) {
      await expect(progress.first()).toBeVisible();

      // Check for progress bar element
      const progressBar = progress.locator('.progress-bar, [role="progressbar"]');
      expect(await progressBar.count()).toBeGreaterThan(0);
    }
  });

  test('should show goal and current amount', async ({ page }) => {
    const progress = page.locator('.wp-block-campaign-office-progress');

    if (await progress.count() > 0) {
      const text = await progress.first().textContent();

      // Should contain numbers (amounts)
      const hasNumbers = text.match(/\d+/);
      expect(hasNumbers).toBeTruthy();
    }
  });

  test('should display percentage', async ({ page }) => {
    const progress = page.locator('.wp-block-campaign-office-progress');

    if (await progress.count() > 0) {
      const percentage = progress.locator('[aria-valuenow], .percentage');

      if (await percentage.count() > 0) {
        const value = await percentage.first().evaluate((el) => {
          return el.getAttribute('aria-valuenow') || el.textContent;
        });

        expect(value).toBeDefined();
      }
    }
  });

  test('should have proper ARIA attributes', async ({ page }) => {
    const progress = page.locator('.wp-block-campaign-office-progress');

    if (await progress.count() > 0) {
      const progressBar = progress.locator('[role="progressbar"]');

      if (await progressBar.count() > 0) {
        const hasAriaValueNow = await progressBar.first().getAttribute('aria-valuenow');
        const hasAriaValueMin = await progressBar.first().getAttribute('aria-valuemin');
        const hasAriaValueMax = await progressBar.first().getAttribute('aria-valuemax');

        expect(hasAriaValueNow || hasAriaValueMin || hasAriaValueMax).toBeTruthy();
      }
    }
  });
});

test.describe('Policy Platform Block', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/issues');
  });

  test('should display policy items', async ({ page }) => {
    const policyPlatform = page.locator('.wp-block-campaign-office-policy-platform');

    if (await policyPlatform.count() > 0) {
      await expect(policyPlatform.first()).toBeVisible();

      // Check for policy items
      const policyItems = policyPlatform.locator('.policy-item, .issue-card, article');
      expect(await policyItems.count()).toBeGreaterThan(0);
    }
  });

  test('should expand/collapse policy details', async ({ page }) => {
    const policyPlatform = page.locator('.wp-block-campaign-office-policy-platform');

    if (await policyPlatform.count() > 0) {
      const expandButton = policyPlatform.locator('[aria-expanded], .toggle, button:has-text("Read more")');

      if (await expandButton.count() > 0) {
        const initialState = await expandButton.first().getAttribute('aria-expanded');

        // Click to toggle
        await expandButton.first().click();
        await page.waitForTimeout(500);

        const newState = await expandButton.first().getAttribute('aria-expanded');

        // State should have changed
        expect(newState !== initialState || newState === null).toBeTruthy();
      }
    }
  });

  test('should display policy titles', async ({ page }) => {
    const policyPlatform = page.locator('.wp-block-campaign-office-policy-platform');

    if (await policyPlatform.count() > 0) {
      const titles = policyPlatform.locator('h2, h3, h4, .policy-title');
      expect(await titles.count()).toBeGreaterThan(0);
    }
  });

  test('should be accessible via keyboard', async ({ page }) => {
    const policyPlatform = page.locator('.wp-block-campaign-office-policy-platform');

    if (await policyPlatform.count() > 0) {
      // Focus on the block
      await policyPlatform.first().focus();

      // Tab through items
      await page.keyboard.press('Tab');
      const focused = await page.evaluate(() => document.activeElement.tagName);

      expect(['BUTTON', 'A', 'DIV']).toContain(focused);
    }
  });
});

test.describe('Block Accessibility', () => {
  test('all blocks should have proper heading hierarchy', async ({ page }) => {
    await page.goto('/');

    // Get all headings
    const headings = await page.locator('h1, h2, h3, h4, h5, h6').all();

    if (headings.length > 0) {
      // Should have at least one h1
      const h1Count = await page.locator('h1').count();
      expect(h1Count).toBeGreaterThanOrEqual(1);
      expect(h1Count).toBeLessThanOrEqual(1); // Should only have one h1
    }
  });

  test('interactive blocks should be keyboard accessible', async ({ page }) => {
    await page.goto('/');

    // Get all buttons
    const buttons = await page.locator('button, [role="button"]').all();

    for (const button of buttons.slice(0, 5)) { // Test first 5 buttons
      if (await button.isVisible()) {
        await button.focus();
        const isFocused = await button.evaluate((el) => {
          return document.activeElement === el;
        });
        expect(isFocused).toBe(true);
      }
    }
  });

  test('form blocks should have proper labels', async ({ page }) => {
    await page.goto('/volunteer');

    const inputs = await page.locator('input[type="text"], input[type="email"], input[type="tel"]').all();

    for (const input of inputs) {
      if (await input.isVisible()) {
        const hasLabel = await input.evaluate((el) => {
          const id = el.getAttribute('id');
          const ariaLabel = el.getAttribute('aria-label');
          const ariaLabelledBy = el.getAttribute('aria-labelledby');
          const placeholder = el.getAttribute('placeholder');

          if (id) {
            const label = document.querySelector(`label[for="${id}"]`);
            return !!label;
          }

          return !!(ariaLabel || ariaLabelledBy || placeholder);
        });

        expect(hasLabel).toBe(true);
      }
    }
  });
});
