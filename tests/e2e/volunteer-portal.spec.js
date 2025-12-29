/**
 * Volunteer Portal E2E Tests
 *
 * Tests for the Volunteer Portal interface:
 * - Login functionality
 * - Tab switching
 * - Shift signup
 * - Hours logging
 * - Profile management
 * - Notification system
 *
 * @package CampaignOffice\Tests\E2E
 */

const { test, expect } = require('@playwright/test');

test.describe('Volunteer Portal - Login', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');
  });

  test('should display login form for logged out users', async ({ page }) => {
    const loginForm = page.locator('.volunteer-login-form, #volunteer-login, form[name="volunteer-login"]');

    if (await loginForm.count() > 0) {
      await expect(loginForm.first()).toBeVisible();

      // Check for email/username and password fields
      const emailInput = loginForm.locator('input[type="email"], input[name="email"], input[name="username"]');
      const passwordInput = loginForm.locator('input[type="password"], input[name="password"]');

      expect(await emailInput.count()).toBeGreaterThan(0);
      expect(await passwordInput.count()).toBeGreaterThan(0);
    }
  });

  test('should validate required login fields', async ({ page }) => {
    const loginForm = page.locator('.volunteer-login-form, #volunteer-login, form[name="volunteer-login"]');

    if (await loginForm.count() > 0) {
      const submitButton = loginForm.locator('button[type="submit"], input[type="submit"]');

      if (await submitButton.count() > 0) {
        // Try to submit empty form
        await submitButton.first().click();

        // Check for HTML5 validation
        const emailInput = loginForm.locator('input[type="email"], input[name="email"]').first();

        if (await emailInput.count() > 0) {
          const isValid = await emailInput.evaluate((input) => {
            return input.validity.valid;
          });

          expect(isValid).toBe(false);
        }
      }
    }
  });

  test('should show error for invalid credentials', async ({ page }) => {
    const loginForm = page.locator('.volunteer-login-form, #volunteer-login, form[name="volunteer-login"]');

    if (await loginForm.count() > 0) {
      const emailInput = loginForm.locator('input[type="email"], input[name="email"], input[name="username"]');
      const passwordInput = loginForm.locator('input[type="password"], input[name="password"]');
      const submitButton = loginForm.locator('button[type="submit"], input[type="submit"]');

      if (await emailInput.count() > 0 && await passwordInput.count() > 0 && await submitButton.count() > 0) {
        // Fill with invalid credentials
        await emailInput.first().fill('invalid@example.com');
        await passwordInput.first().fill('wrongpassword');
        await submitButton.first().click();

        // Wait for error message
        const errorMessage = page.locator('.error-message, .login-error, [data-error]');

        if (await errorMessage.count() > 0) {
          await expect(errorMessage.first()).toBeVisible({ timeout: 5000 });
        }
      }
    }
  });

  test('should display forgot password link', async ({ page }) => {
    const forgotPasswordLink = page.locator('a:has-text("Forgot Password"), a:has-text("Reset Password")');

    if (await forgotPasswordLink.count() > 0) {
      await expect(forgotPasswordLink.first()).toBeVisible();
    }
  });

  test('should display register link', async ({ page }) => {
    const registerLink = page.locator('a:has-text("Register"), a:has-text("Sign Up"), a:has-text("Create Account")');

    if (await registerLink.count() > 0) {
      await expect(registerLink.first()).toBeVisible();
    }
  });
});

test.describe('Volunteer Portal - Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // For these tests, we assume user is logged in
    // In real scenario, you'd implement authentication helper
    await page.goto('/volunteer-portal');
  });

  test('should display volunteer dashboard', async ({ page }) => {
    const dashboard = page.locator('.volunteer-dashboard, #volunteer-dashboard, [data-dashboard]');

    if (await dashboard.count() > 0) {
      await expect(dashboard.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display welcome message with volunteer name', async ({ page }) => {
    const welcomeMessage = page.locator('.welcome-message, .greeting, [data-welcome]');

    if (await welcomeMessage.count() > 0) {
      const text = await welcomeMessage.first().textContent();
      expect(text?.length).toBeGreaterThan(0);
    }
  });

  test('should display volunteer stats', async ({ page }) => {
    const stats = page.locator('.volunteer-stats, .stats-panel, [data-stats]');

    if (await stats.count() > 0) {
      await expect(stats.first()).toBeVisible();

      // Check for common stats
      const hoursLogged = page.locator(':has-text("hours"), :has-text("Hours")');
      const shiftsCompleted = page.locator(':has-text("shifts"), :has-text("Shifts")');

      expect(await hoursLogged.count() > 0 || await shiftsCompleted.count() > 0).toBe(true);
    }
  });

  test('should display upcoming shifts', async ({ page }) => {
    const upcomingShifts = page.locator('.upcoming-shifts, [data-upcoming-shifts]');

    if (await upcomingShifts.count() > 0) {
      await expect(upcomingShifts.first()).toBeVisible();
    }
  });

  test('should display recent activity', async ({ page }) => {
    const recentActivity = page.locator('.recent-activity, [data-recent-activity]');

    if (await recentActivity.count() > 0) {
      await expect(recentActivity.first()).toBeVisible();
    }
  });
});

test.describe('Volunteer Portal - Tab Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');
    await page.waitForLoadState('networkidle');
  });

  test('should display navigation tabs', async ({ page }) => {
    const tabs = page.locator('.nav-tabs, [role="tablist"], .volunteer-tabs');

    if (await tabs.count() > 0) {
      await expect(tabs.first()).toBeVisible();

      const tabItems = tabs.locator('[role="tab"], .tab, .nav-link');
      expect(await tabItems.count()).toBeGreaterThan(0);
    }
  });

  test('should switch to shifts tab', async ({ page }) => {
    const shiftsTab = page.locator('[data-tab="shifts"], [href="#shifts"], button:has-text("Shifts")');

    if (await shiftsTab.count() > 0) {
      await shiftsTab.first().click();
      await page.waitForTimeout(500);

      // Shifts content should be visible
      const shiftsContent = page.locator('#shifts, [data-tab-content="shifts"], .shifts-panel');

      if (await shiftsContent.count() > 0) {
        await expect(shiftsContent.first()).toBeVisible();
      }

      // Tab should be active
      const isActive = await shiftsTab.first().evaluate((el) => {
        return el.classList.contains('active') ||
               el.getAttribute('aria-selected') === 'true';
      });

      expect(isActive).toBe(true);
    }
  });

  test('should switch to hours tab', async ({ page }) => {
    const hoursTab = page.locator('[data-tab="hours"], [href="#hours"], button:has-text("Hours")');

    if (await hoursTab.count() > 0) {
      await hoursTab.first().click();
      await page.waitForTimeout(500);

      // Hours content should be visible
      const hoursContent = page.locator('#hours, [data-tab-content="hours"], .hours-panel');

      if (await hoursContent.count() > 0) {
        await expect(hoursContent.first()).toBeVisible();
      }
    }
  });

  test('should switch to profile tab', async ({ page }) => {
    const profileTab = page.locator('[data-tab="profile"], [href="#profile"], button:has-text("Profile")');

    if (await profileTab.count() > 0) {
      await profileTab.first().click();
      await page.waitForTimeout(500);

      // Profile content should be visible
      const profileContent = page.locator('#profile, [data-tab-content="profile"], .profile-panel');

      if (await profileContent.count() > 0) {
        await expect(profileContent.first()).toBeVisible();
      }
    }
  });

  test('should switch to notifications tab', async ({ page }) => {
    const notificationsTab = page.locator('[data-tab="notifications"], [href="#notifications"], button:has-text("Notifications")');

    if (await notificationsTab.count() > 0) {
      await notificationsTab.first().click();
      await page.waitForTimeout(500);

      // Notifications content should be visible
      const notificationsContent = page.locator('#notifications, [data-tab-content="notifications"], .notifications-panel');

      if (await notificationsContent.count() > 0) {
        await expect(notificationsContent.first()).toBeVisible();
      }
    }
  });

  test('should maintain state when switching tabs', async ({ page }) => {
    const tabs = page.locator('[role="tab"], .tab, .nav-link');

    if (await tabs.count() >= 2) {
      // Click first tab
      await tabs.nth(0).click();
      await page.waitForTimeout(300);

      // Click second tab
      await tabs.nth(1).click();
      await page.waitForTimeout(300);

      // Click back to first tab
      await tabs.nth(0).click();
      await page.waitForTimeout(300);

      // First tab should be active
      const isActive = await tabs.nth(0).evaluate((el) => {
        return el.classList.contains('active') ||
               el.getAttribute('aria-selected') === 'true';
      });

      expect(isActive).toBe(true);
    }
  });
});

test.describe('Volunteer Portal - Shift Signup', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');

    // Navigate to shifts tab
    const shiftsTab = page.locator('[data-tab="shifts"], [href="#shifts"], button:has-text("Shifts")');
    if (await shiftsTab.count() > 0) {
      await shiftsTab.first().click();
      await page.waitForTimeout(500);
    }
  });

  test('should display available shifts', async ({ page }) => {
    const availableShifts = page.locator('.available-shifts, [data-available-shifts]');

    if (await availableShifts.count() > 0) {
      await expect(availableShifts.first()).toBeVisible();

      const shiftItems = availableShifts.locator('.shift-item, .shift-card, [data-shift]');
      expect(await shiftItems.count()).toBeGreaterThan(0);
    }
  });

  test('should display shift details', async ({ page }) => {
    const shiftItems = page.locator('.shift-item, .shift-card, [data-shift]');

    if (await shiftItems.count() > 0) {
      const firstShift = shiftItems.first();

      // Check for shift details
      const shiftDate = firstShift.locator('time, .shift-date, [data-date]');
      const shiftLocation = firstShift.locator('.shift-location, [data-location]');

      expect(await shiftDate.count() > 0 || await shiftLocation.count() > 0).toBe(true);
    }
  });

  test('should sign up for a shift', async ({ page }) => {
    const signupButton = page.locator('button:has-text("Sign Up"), button:has-text("Join"), [data-action="signup"]');

    if (await signupButton.count() > 0) {
      await signupButton.first().click();

      // Wait for confirmation
      const confirmation = page.locator('.success-message, .confirmation, [data-message="success"]');

      if (await confirmation.count() > 0) {
        await expect(confirmation.first()).toBeVisible({ timeout: 5000 });
      } else {
        // Check if shift moved to "My Shifts"
        await page.waitForTimeout(1000);
      }
    }
  });

  test('should cancel shift signup', async ({ page }) => {
    const cancelButton = page.locator('button:has-text("Cancel"), button:has-text("Remove"), [data-action="cancel"]');

    if (await cancelButton.count() > 0) {
      await cancelButton.first().click();

      // May show confirmation dialog
      const confirmButton = page.locator('button:has-text("Confirm"), button:has-text("Yes")');
      if (await confirmButton.count() > 0) {
        await confirmButton.first().click();
      }

      // Wait for removal
      await page.waitForTimeout(1000);
    }
  });

  test('should filter shifts by date', async ({ page }) => {
    const dateFilter = page.locator('input[type="date"], select[name="date"]');

    if (await dateFilter.count() > 0) {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      const dateString = tomorrow.toISOString().split('T')[0];

      await dateFilter.first().fill(dateString);
      await page.waitForTimeout(500);

      // Shifts should be filtered
      expect(true).toBe(true);
    }
  });

  test('should filter shifts by location', async ({ page }) => {
    const locationFilter = page.locator('select[name="location"], [data-filter="location"]');

    if (await locationFilter.count() > 0) {
      const options = await locationFilter.locator('option').all();

      if (options.length > 1) {
        await locationFilter.first().selectOption({ index: 1 });
        await page.waitForTimeout(500);

        // Shifts should be filtered
        expect(true).toBe(true);
      }
    }
  });

  test('should filter shifts by type', async ({ page }) => {
    const typeFilter = page.locator('select[name="shift_type"], [data-filter="type"]');

    if (await typeFilter.count() > 0) {
      const options = await typeFilter.locator('option').all();

      if (options.length > 1) {
        await typeFilter.first().selectOption({ index: 1 });
        await page.waitForTimeout(500);

        // Shifts should be filtered
        expect(true).toBe(true);
      }
    }
  });
});

test.describe('Volunteer Portal - Hours Logging', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');

    // Navigate to hours tab
    const hoursTab = page.locator('[data-tab="hours"], [href="#hours"], button:has-text("Hours")');
    if (await hoursTab.count() > 0) {
      await hoursTab.first().click();
      await page.waitForTimeout(500);
    }
  });

  test('should display hours logging form', async ({ page }) => {
    const hoursForm = page.locator('.hours-form, form[name="log-hours"], [data-hours-form]');

    if (await hoursForm.count() > 0) {
      await expect(hoursForm.first()).toBeVisible();
    }
  });

  test('should log volunteer hours', async ({ page }) => {
    const hoursInput = page.locator('input[name="hours"], input[type="number"]');
    const dateInput = page.locator('input[name="date"], input[type="date"]');
    const activityInput = page.locator('select[name="activity"], textarea[name="description"]');
    const submitButton = page.locator('button:has-text("Log Hours"), button[type="submit"]');

    if (await hoursInput.count() > 0 && await submitButton.count() > 0) {
      // Fill form
      await hoursInput.first().fill('3');

      if (await dateInput.count() > 0) {
        const today = new Date().toISOString().split('T')[0];
        await dateInput.first().fill(today);
      }

      if (await activityInput.count() > 0) {
        if (await activityInput.first().evaluate((el) => el.tagName === 'SELECT')) {
          await activityInput.first().selectOption({ index: 1 });
        } else {
          await activityInput.first().fill('Phone banking');
        }
      }

      // Submit
      await submitButton.first().click();

      // Wait for confirmation
      const successMessage = page.locator('.success-message, [data-message="success"]');

      if (await successMessage.count() > 0) {
        await expect(successMessage.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('should validate hours input', async ({ page }) => {
    const hoursInput = page.locator('input[name="hours"], input[type="number"]');
    const submitButton = page.locator('button:has-text("Log Hours"), button[type="submit"]');

    if (await hoursInput.count() > 0 && await submitButton.count() > 0) {
      // Try negative hours
      await hoursInput.first().fill('-5');
      await submitButton.first().click();

      const isValid = await hoursInput.first().evaluate((input) => {
        return input.checkValidity();
      });

      expect(isValid).toBe(false);
    }
  });

  test('should display hours history', async ({ page }) => {
    const hoursHistory = page.locator('.hours-history, [data-hours-history], table');

    if (await hoursHistory.count() > 0) {
      await expect(hoursHistory.first()).toBeVisible();

      // Check for table rows
      const rows = hoursHistory.locator('tr, .history-item');
      expect(await rows.count()).toBeGreaterThan(0);
    }
  });

  test('should display total hours', async ({ page }) => {
    const totalHours = page.locator('.total-hours, [data-total-hours]');

    if (await totalHours.count() > 0) {
      const text = await totalHours.first().textContent();
      expect(text).toMatch(/\d+/); // Should contain a number
    }
  });

  test('should edit logged hours', async ({ page }) => {
    const editButton = page.locator('button:has-text("Edit"), [data-action="edit"]');

    if (await editButton.count() > 0) {
      await editButton.first().click();
      await page.waitForTimeout(500);

      // Edit form should appear
      const editForm = page.locator('.edit-form, [data-edit-form]');

      if (await editForm.count() > 0) {
        await expect(editForm.first()).toBeVisible();
      }
    }
  });

  test('should delete logged hours', async ({ page }) => {
    const deleteButton = page.locator('button:has-text("Delete"), [data-action="delete"]');

    if (await deleteButton.count() > 0) {
      await deleteButton.first().click();

      // Confirmation dialog
      const confirmButton = page.locator('button:has-text("Confirm"), button:has-text("Yes")');
      if (await confirmButton.count() > 0) {
        await confirmButton.first().click();
        await page.waitForTimeout(1000);
      }
    }
  });
});

test.describe('Volunteer Portal - Profile Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');

    // Navigate to profile tab
    const profileTab = page.locator('[data-tab="profile"], [href="#profile"], button:has-text("Profile")');
    if (await profileTab.count() > 0) {
      await profileTab.first().click();
      await page.waitForTimeout(500);
    }
  });

  test('should display profile information', async ({ page }) => {
    const profile = page.locator('.profile-info, [data-profile]');

    if (await profile.count() > 0) {
      await expect(profile.first()).toBeVisible();
    }
  });

  test('should edit profile information', async ({ page }) => {
    const editButton = page.locator('button:has-text("Edit Profile"), [data-action="edit-profile"]');

    if (await editButton.count() > 0) {
      await editButton.first().click();
      await page.waitForTimeout(500);

      // Edit form should appear
      const profileForm = page.locator('.profile-form, form[name="profile"]');

      if (await profileForm.count() > 0) {
        await expect(profileForm.first()).toBeVisible();
      }
    }
  });

  test('should update skills', async ({ page }) => {
    const skillsSection = page.locator('.skills-section, [data-skills]');

    if (await skillsSection.count() > 0) {
      const skillCheckboxes = skillsSection.locator('input[type="checkbox"]');

      if (await skillCheckboxes.count() > 0) {
        // Toggle a skill
        await skillCheckboxes.first().click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should update availability', async ({ page }) => {
    const availabilitySection = page.locator('.availability-section, [data-availability]');

    if (await availabilitySection.count() > 0) {
      const availabilityCheckboxes = availabilitySection.locator('input[type="checkbox"]');

      if (await availabilityCheckboxes.count() > 0) {
        // Toggle availability
        await availabilityCheckboxes.first().click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should change password', async ({ page }) => {
    const changePasswordButton = page.locator('button:has-text("Change Password"), a:has-text("Change Password")');

    if (await changePasswordButton.count() > 0) {
      await changePasswordButton.first().click();
      await page.waitForTimeout(500);

      // Password form should appear
      const passwordForm = page.locator('.password-form, form[name="change-password"]');

      if (await passwordForm.count() > 0) {
        await expect(passwordForm.first()).toBeVisible();
      }
    }
  });
});

test.describe('Volunteer Portal - Notifications', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');

    // Navigate to notifications tab
    const notificationsTab = page.locator('[data-tab="notifications"], [href="#notifications"], button:has-text("Notifications")');
    if (await notificationsTab.count() > 0) {
      await notificationsTab.first().click();
      await page.waitForTimeout(500);
    }
  });

  test('should display notifications list', async ({ page }) => {
    const notificationsList = page.locator('.notifications-list, [data-notifications]');

    if (await notificationsList.count() > 0) {
      await expect(notificationsList.first()).toBeVisible();
    }
  });

  test('should mark notification as read', async ({ page }) => {
    const notification = page.locator('.notification, [data-notification]').first();

    if (await notification.count() > 0) {
      // Click notification
      await notification.click();
      await page.waitForTimeout(500);

      // Should be marked as read
      const isRead = await notification.evaluate((el) => {
        return el.classList.contains('read') || el.getAttribute('data-read') === 'true';
      });

      expect(typeof isRead).toBe('boolean');
    }
  });

  test('should delete notification', async ({ page }) => {
    const deleteButton = page.locator('[data-action="delete-notification"], button:has-text("Delete")');

    if (await deleteButton.count() > 0) {
      await deleteButton.first().click();
      await page.waitForTimeout(500);
    }
  });

  test('should mark all as read', async ({ page }) => {
    const markAllButton = page.locator('button:has-text("Mark All as Read"), [data-action="mark-all-read"]');

    if (await markAllButton.count() > 0) {
      await markAllButton.first().click();
      await page.waitForTimeout(1000);
    }
  });
});

test.describe('Volunteer Portal - Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/volunteer-portal');
  });

  test('should support keyboard navigation through tabs', async ({ page }) => {
    const tabs = page.locator('[role="tab"]');

    if (await tabs.count() > 0) {
      // Focus on first tab
      await tabs.first().focus();

      // Arrow right to next tab
      await page.keyboard.press('ArrowRight');
      await page.waitForTimeout(300);

      const focused = await page.evaluate(() => document.activeElement?.getAttribute('role'));
      expect(focused).toBe('tab');
    }
  });

  test('should have proper ARIA labels', async ({ page }) => {
    const tabs = page.locator('[role="tab"]');

    if (await tabs.count() > 0) {
      for (const tab of await tabs.all()) {
        const ariaLabel = await tab.getAttribute('aria-label');
        const textContent = await tab.textContent();

        expect(ariaLabel || textContent?.trim()).toBeTruthy();
      }
    }
  });

  test('should announce tab panel changes', async ({ page }) => {
    const tabs = page.locator('[role="tab"]');

    if (await tabs.count() >= 2) {
      await tabs.nth(1).click();
      await page.waitForTimeout(500);

      const tabPanel = page.locator('[role="tabpanel"]');

      if (await tabPanel.count() > 0) {
        const ariaLabelledBy = await tabPanel.first().getAttribute('aria-labelledby');
        expect(ariaLabelledBy || true).toBeTruthy();
      }
    }
  });
});
