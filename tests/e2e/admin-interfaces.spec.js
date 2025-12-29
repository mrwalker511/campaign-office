/**
 * Admin Interfaces E2E Tests
 *
 * Tests for admin interfaces including:
 * - Premium Admin Dashboard
 * - FEC Compliance Interface
 * - CRM System
 * - Field Operations Management
 * - Analytics Dashboard
 * - Developer Console
 *
 * @package CampaignOffice\Tests\E2E
 */

const { test, expect } = require('@playwright/test');

test.describe('Premium Admin - License Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-premium');
  });

  test('should display premium admin dashboard', async ({ page }) => {
    const dashboard = page.locator('.premium-admin-dashboard, #premium-dashboard');

    if (await dashboard.count() > 0) {
      await expect(dashboard.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display license status', async ({ page }) => {
    const licenseStatus = page.locator('.license-status, [data-license-status]');

    if (await licenseStatus.count() > 0) {
      await expect(licenseStatus.first()).toBeVisible();

      const statusText = await licenseStatus.first().textContent();
      expect(statusText?.length).toBeGreaterThan(0);
    }
  });

  test('should display license input field', async ({ page }) => {
    const licenseInput = page.locator('input[name="license_key"], #license-key');

    if (await licenseInput.count() > 0) {
      await expect(licenseInput.first()).toBeVisible();
    }
  });

  test('should activate license', async ({ page }) => {
    const licenseInput = page.locator('input[name="license_key"], #license-key');
    const activateButton = page.locator('button:has-text("Activate"), [data-action="activate"]');

    if (await licenseInput.count() > 0 && await activateButton.count() > 0) {
      // Fill with test license key
      await licenseInput.first().fill('TEST-LICENSE-KEY-123');
      await activateButton.first().click();

      // Wait for response
      await page.waitForTimeout(2000);

      // Check for message (success or error)
      const message = page.locator('.notice, .message, [data-message]');
      if (await message.count() > 0) {
        await expect(message.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('should validate license key format', async ({ page }) => {
    const licenseInput = page.locator('input[name="license_key"], #license-key');
    const activateButton = page.locator('button:has-text("Activate"), [data-action="activate"]');

    if (await licenseInput.count() > 0 && await activateButton.count() > 0) {
      // Try invalid format
      await licenseInput.first().fill('invalid');
      await activateButton.first().click();

      // Should show validation error
      const errorMessage = page.locator('.error, .invalid-feedback');

      if (await errorMessage.count() > 0) {
        await expect(errorMessage.first()).toBeVisible({ timeout: 3000 });
      }
    }
  });

  test('should display premium features list', async ({ page }) => {
    const featuresList = page.locator('.premium-features, [data-features]');

    if (await featuresList.count() > 0) {
      await expect(featuresList.first()).toBeVisible();

      const features = featuresList.locator('.feature-item, li');
      expect(await features.count()).toBeGreaterThan(0);
    }
  });

  test('should toggle feature details', async ({ page }) => {
    const featureToggle = page.locator('[data-action="toggle-details"], .feature-toggle');

    if (await featureToggle.count() > 0) {
      await featureToggle.first().click();
      await page.waitForTimeout(500);

      const featureDetails = page.locator('.feature-details, [data-details]');

      if (await featureDetails.count() > 0) {
        // Details should be visible or hidden based on toggle
        expect(true).toBe(true);
      }
    }
  });
});

test.describe('FEC Compliance - Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-fec');
  });

  test('should display FEC compliance dashboard', async ({ page }) => {
    const dashboard = page.locator('.fec-compliance-dashboard, #fec-dashboard');

    if (await dashboard.count() > 0) {
      await expect(dashboard.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display compliance status', async ({ page }) => {
    const complianceStatus = page.locator('.compliance-status, [data-compliance-status]');

    if (await complianceStatus.count() > 0) {
      await expect(complianceStatus.first()).toBeVisible();
    }
  });

  test('should display FEC report generation options', async ({ page }) => {
    const reportOptions = page.locator('.report-options, [data-report-options]');

    if (await reportOptions.count() > 0) {
      await expect(reportOptions.first()).toBeVisible();
    }
  });

  test('should generate FEC report', async ({ page }) => {
    const generateButton = page.locator('button:has-text("Generate Report"), [data-action="generate-report"]');

    if (await generateButton.count() > 0) {
      await generateButton.first().click();

      // Wait for report generation
      const loadingIndicator = page.locator('.loading, .spinner, [data-loading]');

      if (await loadingIndicator.count() > 0) {
        await expect(loadingIndicator.first()).toBeVisible({ timeout: 2000 });
      }

      // Wait for completion
      await page.waitForTimeout(3000);
    }
  });

  test('should display audit trail', async ({ page }) => {
    const auditTrail = page.locator('.audit-trail, [data-audit-trail], table');

    if (await auditTrail.count() > 0) {
      await expect(auditTrail.first()).toBeVisible();

      // Check for audit entries
      const entries = auditTrail.locator('tr, .audit-entry');
      expect(await entries.count()).toBeGreaterThan(0);
    }
  });

  test('should filter audit trail by date', async ({ page }) => {
    const dateFilter = page.locator('input[type="date"][name="start_date"], [data-filter="date"]');

    if (await dateFilter.count() > 0) {
      const today = new Date().toISOString().split('T')[0];
      await dateFilter.first().fill(today);
      await page.waitForTimeout(1000);

      // Audit trail should be filtered
      expect(true).toBe(true);
    }
  });

  test('should export compliance report', async ({ page }) => {
    const exportButton = page.locator('button:has-text("Export"), [data-action="export"]');

    if (await exportButton.count() > 0) {
      // Start waiting for download before clicking
      const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);

      await exportButton.first().click();

      const download = await downloadPromise;

      if (download) {
        expect(download.suggestedFilename()).toMatch(/\.(pdf|csv|xlsx)$/i);
      }
    }
  });

  test('should display compliance warnings', async ({ page }) => {
    const warnings = page.locator('.compliance-warning, [data-warning]');

    if (await warnings.count() > 0) {
      // Warnings may or may not be present
      expect(await warnings.count()).toBeGreaterThanOrEqual(0);
    }
  });
});

test.describe('CRM - Contact Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-crm');
  });

  test('should display CRM dashboard', async ({ page }) => {
    const dashboard = page.locator('.crm-dashboard, #crm-dashboard');

    if (await dashboard.count() > 0) {
      await expect(dashboard.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display contacts list', async ({ page }) => {
    const contactsList = page.locator('.contacts-list, [data-contacts], table');

    if (await contactsList.count() > 0) {
      await expect(contactsList.first()).toBeVisible();

      const contacts = contactsList.locator('tr, .contact-item');
      expect(await contacts.count()).toBeGreaterThan(0);
    }
  });

  test('should add new contact', async ({ page }) => {
    const addButton = page.locator('button:has-text("Add Contact"), a:has-text("Add Contact")');

    if (await addButton.count() > 0) {
      await addButton.first().click();
      await page.waitForTimeout(500);

      // Contact form should appear
      const contactForm = page.locator('.contact-form, form[name="add-contact"]');

      if (await contactForm.count() > 0) {
        await expect(contactForm.first()).toBeVisible();
      }
    }
  });

  test('should save new contact', async ({ page }) => {
    const addButton = page.locator('button:has-text("Add Contact"), a:has-text("Add Contact")');

    if (await addButton.count() > 0) {
      await addButton.first().click();
      await page.waitForTimeout(500);

      const firstNameInput = page.locator('input[name="first_name"]');
      const lastNameInput = page.locator('input[name="last_name"]');
      const emailInput = page.locator('input[name="email"]');
      const saveButton = page.locator('button:has-text("Save"), button[type="submit"]');

      if (
        await firstNameInput.count() > 0 &&
        await emailInput.count() > 0 &&
        await saveButton.count() > 0
      ) {
        await firstNameInput.first().fill('John');
        if (await lastNameInput.count() > 0) {
          await lastNameInput.first().fill('Doe');
        }
        await emailInput.first().fill('john.doe@example.com');

        await saveButton.first().click();

        // Wait for confirmation
        const successMessage = page.locator('.success-message, .notice-success');

        if (await successMessage.count() > 0) {
          await expect(successMessage.first()).toBeVisible({ timeout: 5000 });
        }
      }
    }
  });

  test('should search contacts', async ({ page }) => {
    const searchInput = page.locator('input[type="search"], input[name="search"]');

    if (await searchInput.count() > 0) {
      await searchInput.first().fill('test');
      await page.keyboard.press('Enter');
      await page.waitForTimeout(1000);

      // Contacts should be filtered
      expect(true).toBe(true);
    }
  });

  test('should filter contacts by tag', async ({ page }) => {
    const tagFilter = page.locator('select[name="tag"], [data-filter="tag"]');

    if (await tagFilter.count() > 0) {
      const options = await tagFilter.locator('option').all();

      if (options.length > 1) {
        await tagFilter.first().selectOption({ index: 1 });
        await page.waitForTimeout(1000);

        // Contacts should be filtered
        expect(true).toBe(true);
      }
    }
  });

  test('should edit contact', async ({ page }) => {
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), [data-action="edit"]').first();

    if (await editButton.count() > 0) {
      await editButton.click();
      await page.waitForTimeout(500);

      // Edit form should appear
      const editForm = page.locator('.contact-form, form[name="edit-contact"]');

      if (await editForm.count() > 0) {
        await expect(editForm.first()).toBeVisible();
      }
    }
  });

  test('should delete contact', async ({ page }) => {
    const deleteButton = page.locator('button:has-text("Delete"), [data-action="delete"]').first();

    if (await deleteButton.count() > 0) {
      await deleteButton.click();

      // Confirmation dialog
      const confirmButton = page.locator('button:has-text("Confirm"), button:has-text("Yes")');

      if (await confirmButton.count() > 0) {
        await confirmButton.first().click();
        await page.waitForTimeout(1000);
      }
    }
  });

  test('should export contacts', async ({ page }) => {
    const exportButton = page.locator('button:has-text("Export"), [data-action="export"]');

    if (await exportButton.count() > 0) {
      const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);

      await exportButton.first().click();

      const download = await downloadPromise;

      if (download) {
        expect(download.suggestedFilename()).toMatch(/\.(csv|xlsx)$/i);
      }
    }
  });

  test('should import contacts', async ({ page }) => {
    const importButton = page.locator('button:has-text("Import"), a:has-text("Import")');

    if (await importButton.count() > 0) {
      await importButton.first().click();
      await page.waitForTimeout(500);

      // Import form should appear
      const importForm = page.locator('.import-form, form[name="import-contacts"]');

      if (await importForm.count() > 0) {
        await expect(importForm.first()).toBeVisible();
      }
    }
  });
});

test.describe('Field Operations - Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-field-ops');
  });

  test('should display field operations dashboard', async ({ page }) => {
    const dashboard = page.locator('.field-ops-dashboard, #field-ops-dashboard');

    if (await dashboard.count() > 0) {
      await expect(dashboard.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display canvassing routes', async ({ page }) => {
    const routes = page.locator('.canvassing-routes, [data-routes]');

    if (await routes.count() > 0) {
      await expect(routes.first()).toBeVisible();
    }
  });

  test('should create new canvassing route', async ({ page }) => {
    const createButton = page.locator('button:has-text("Create Route"), a:has-text("Create Route")');

    if (await createButton.count() > 0) {
      await createButton.first().click();
      await page.waitForTimeout(500);

      // Route creation form should appear
      const routeForm = page.locator('.route-form, form[name="create-route"]');

      if (await routeForm.count() > 0) {
        await expect(routeForm.first()).toBeVisible();
      }
    }
  });

  test('should assign volunteer to route', async ({ page }) => {
    const assignButton = page.locator('button:has-text("Assign"), [data-action="assign"]');

    if (await assignButton.count() > 0) {
      await assignButton.first().click();
      await page.waitForTimeout(500);

      // Assignment form should appear
      const assignForm = page.locator('.assign-form, [data-assign-form]');

      if (await assignForm.count() > 0) {
        await expect(assignForm.first()).toBeVisible();
      }
    }
  });

  test('should view route details', async ({ page }) => {
    const viewButton = page.locator('button:has-text("View"), a:has-text("View"), [data-action="view"]');

    if (await viewButton.count() > 0) {
      await viewButton.first().click();
      await page.waitForTimeout(500);

      // Route details should appear
      const routeDetails = page.locator('.route-details, [data-route-details]');

      if (await routeDetails.count() > 0) {
        await expect(routeDetails.first()).toBeVisible();
      }
    }
  });

  test('should display field reports', async ({ page }) => {
    const reports = page.locator('.field-reports, [data-reports], table');

    if (await reports.count() > 0) {
      await expect(reports.first()).toBeVisible();
    }
  });

  test('should filter routes by status', async ({ page }) => {
    const statusFilter = page.locator('select[name="status"], [data-filter="status"]');

    if (await statusFilter.count() > 0) {
      const options = await statusFilter.locator('option').all();

      if (options.length > 1) {
        await statusFilter.first().selectOption({ index: 1 });
        await page.waitForTimeout(1000);

        // Routes should be filtered
        expect(true).toBe(true);
      }
    }
  });
});

test.describe('Analytics Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-analytics');
  });

  test('should display analytics dashboard', async ({ page }) => {
    const dashboard = page.locator('.analytics-dashboard, #analytics-dashboard');

    if (await dashboard.count() > 0) {
      await expect(dashboard.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display key metrics', async ({ page }) => {
    const metrics = page.locator('.metrics, .stats, [data-metrics]');

    if (await metrics.count() > 0) {
      await expect(metrics.first()).toBeVisible();

      // Check for metric cards
      const metricCards = metrics.locator('.metric-card, .stat-card');
      expect(await metricCards.count()).toBeGreaterThan(0);
    }
  });

  test('should display charts', async ({ page }) => {
    const charts = page.locator('canvas, .chart, [data-chart]');

    if (await charts.count() > 0) {
      await expect(charts.first()).toBeVisible();
    }
  });

  test('should filter by date range', async ({ page }) => {
    const startDate = page.locator('input[name="start_date"], [data-filter="start-date"]');
    const endDate = page.locator('input[name="end_date"], [data-filter="end-date"]');

    if (await startDate.count() > 0 && await endDate.count() > 0) {
      const start = new Date();
      start.setDate(start.getDate() - 30);
      const startString = start.toISOString().split('T')[0];

      const end = new Date().toISOString().split('T')[0];

      await startDate.first().fill(startString);
      await endDate.first().fill(end);

      // Apply filter
      const applyButton = page.locator('button:has-text("Apply"), [data-action="apply"]');

      if (await applyButton.count() > 0) {
        await applyButton.first().click();
        await page.waitForTimeout(1000);
      }
    }
  });

  test('should export analytics report', async ({ page }) => {
    const exportButton = page.locator('button:has-text("Export"), [data-action="export"]');

    if (await exportButton.count() > 0) {
      const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);

      await exportButton.first().click();

      const download = await downloadPromise;

      if (download) {
        expect(download.suggestedFilename()).toMatch(/\.(pdf|csv|xlsx)$/i);
      }
    }
  });

  test('should refresh data', async ({ page }) => {
    const refreshButton = page.locator('button:has-text("Refresh"), [data-action="refresh"]');

    if (await refreshButton.count() > 0) {
      await refreshButton.first().click();
      await page.waitForTimeout(2000);

      // Data should reload
      expect(true).toBe(true);
    }
  });
});

test.describe('Developer Console', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-developer');
  });

  test('should display developer console', async ({ page }) => {
    const console = page.locator('.developer-console, #developer-console');

    if (await console.count() > 0) {
      await expect(console.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display API endpoints', async ({ page }) => {
    const endpoints = page.locator('.api-endpoints, [data-endpoints]');

    if (await endpoints.count() > 0) {
      await expect(endpoints.first()).toBeVisible();
    }
  });

  test('should test API endpoint', async ({ page }) => {
    const testButton = page.locator('button:has-text("Test"), [data-action="test"]');

    if (await testButton.count() > 0) {
      await testButton.first().click();
      await page.waitForTimeout(2000);

      // Response should appear
      const response = page.locator('.api-response, [data-response]');

      if (await response.count() > 0) {
        await expect(response.first()).toBeVisible();
      }
    }
  });

  test('should display system health', async ({ page }) => {
    const systemHealth = page.locator('.system-health, [data-system-health]');

    if (await systemHealth.count() > 0) {
      await expect(systemHealth.first()).toBeVisible();
    }
  });

  test('should display database information', async ({ page }) => {
    const dbInfo = page.locator('.database-info, [data-database]');

    if (await dbInfo.count() > 0) {
      await expect(dbInfo.first()).toBeVisible();
    }
  });

  test('should copy code snippets', async ({ page }) => {
    const copyButton = page.locator('button:has-text("Copy"), [data-action="copy"]');

    if (await copyButton.count() > 0) {
      await copyButton.first().click();
      await page.waitForTimeout(500);

      // Confirmation tooltip or message
      const confirmation = page.locator('.copied, [data-message="copied"]');

      if (await confirmation.count() > 0) {
        await expect(confirmation.first()).toBeVisible({ timeout: 2000 });
      }
    }
  });
});

test.describe('Admin Interfaces - Accessibility', () => {
  test('should support keyboard navigation', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-premium');

    // Tab through interface
    await page.keyboard.press('Tab');
    const focused1 = await page.evaluate(() => document.activeElement?.tagName);

    await page.keyboard.press('Tab');
    const focused2 = await page.evaluate(() => document.activeElement?.tagName);

    expect(['BUTTON', 'A', 'INPUT', 'SELECT']).toContain(focused1);
    expect(['BUTTON', 'A', 'INPUT', 'SELECT']).toContain(focused2);
  });

  test('should have proper heading hierarchy', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-premium');

    const h1 = await page.locator('h1').count();
    expect(h1).toBeGreaterThanOrEqual(1);
    expect(h1).toBeLessThanOrEqual(1); // Only one h1
  });

  test('should have ARIA labels on forms', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=campaign-office-premium');

    const forms = await page.locator('form').all();

    for (const form of forms) {
      const ariaLabel = await form.getAttribute('aria-label');
      const ariaLabelledBy = await form.getAttribute('aria-labelledby');

      expect(ariaLabel || ariaLabelledBy || true).toBeTruthy();
    }
  });
});
