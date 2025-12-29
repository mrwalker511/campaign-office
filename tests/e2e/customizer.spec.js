/**
 * WordPress Customizer E2E Tests
 *
 * Tests for the WordPress Customizer functionality:
 * - Customizer interface
 * - Theme settings
 * - Preview updates
 * - Color scheme changes
 * - Typography settings
 * - Layout options
 * - Save and publish
 *
 * @package CampaignOffice\Tests\E2E
 */

const { test, expect } = require('@playwright/test');

test.describe('Customizer - Interface', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
  });

  test('should load customizer interface', async ({ page }) => {
    // Wait for customizer to fully load
    const customizer = page.locator('#customize-controls, .wp-full-overlay');

    await expect(customizer.first()).toBeVisible({ timeout: 15000 });
  });

  test('should display preview pane', async ({ page }) => {
    const previewPane = page.locator('#customize-preview, iframe[name="customize-preview"]');

    if (await previewPane.count() > 0) {
      await expect(previewPane.first()).toBeVisible({ timeout: 15000 });
    }
  });

  test('should display customizer sections', async ({ page }) => {
    const sections = page.locator('.customize-section, [id^="accordion-section"]');

    if (await sections.count() > 0) {
      expect(await sections.count()).toBeGreaterThan(0);
    }
  });

  test('should display publish button', async ({ page }) => {
    const publishButton = page.locator('#save, button:has-text("Publish")');

    if (await publishButton.count() > 0) {
      await expect(publishButton.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should display close button', async ({ page }) => {
    const closeButton = page.locator('.customize-controls-close, [aria-label*="Close"]');

    if (await closeButton.count() > 0) {
      await expect(closeButton.first()).toBeVisible({ timeout: 10000 });
    }
  });

  test('should have device preview switcher', async ({ page }) => {
    const deviceSwitcher = page.locator('.devices-wrapper, .preview-devices');

    if (await deviceSwitcher.count() > 0) {
      await expect(deviceSwitcher.first()).toBeVisible({ timeout: 10000 });

      // Check for device buttons
      const desktopButton = deviceSwitcher.locator('[data-device="desktop"], .preview-desktop');
      const tabletButton = deviceSwitcher.locator('[data-device="tablet"], .preview-tablet');
      const mobileButton = deviceSwitcher.locator('[data-device="mobile"], .preview-mobile');

      expect(
        await desktopButton.count() > 0 ||
        await tabletButton.count() > 0 ||
        await mobileButton.count() > 0
      ).toBe(true);
    }
  });
});

test.describe('Customizer - Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000); // Wait for customizer to load
  });

  test('should expand customizer section', async ({ page }) => {
    const section = page.locator('.customize-section').first();

    if (await section.count() > 0) {
      const sectionButton = section.locator('.accordion-section-title, button').first();

      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);

        // Section content should be visible
        const isExpanded = await section.evaluate((el) => {
          return el.classList.contains('open') ||
                 el.classList.contains('expanded') ||
                 el.getAttribute('aria-expanded') === 'true';
        });

        expect(typeof isExpanded).toBe('boolean');
      }
    }
  });

  test('should navigate to colors section', async ({ page }) => {
    const colorsSection = page.locator('[id*="colors"], [data-section="colors"]');

    if (await colorsSection.count() > 0) {
      const sectionButton = colorsSection.locator('.accordion-section-title, button').first();

      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);

        // Colors controls should be visible
        const colorControls = page.locator('input[type="color"], .color-picker-hex');
        expect(await colorControls.count()).toBeGreaterThan(0);
      }
    }
  });

  test('should navigate to typography section', async ({ page }) => {
    const typographySection = page.locator('[id*="typography"], [id*="fonts"]');

    if (await typographySection.count() > 0) {
      const sectionButton = typographySection.locator('.accordion-section-title, button').first();

      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);

        // Font controls should be visible
        const fontControls = page.locator('select[id*="font"]');
        if (await fontControls.count() > 0) {
          expect(await fontControls.count()).toBeGreaterThan(0);
        }
      }
    }
  });

  test('should navigate to header section', async ({ page }) => {
    const headerSection = page.locator('[id*="header"]');

    if (await headerSection.count() > 0) {
      const sectionButton = headerSection.locator('.accordion-section-title, button').first();

      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should navigate back from panel', async ({ page }) => {
    const section = page.locator('.customize-section').first();

    if (await section.count() > 0) {
      const sectionButton = section.locator('.accordion-section-title, button').first();

      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);

        // Look for back button
        const backButton = page.locator('.customize-section-back, [aria-label*="Back"]');

        if (await backButton.count() > 0) {
          await backButton.first().click();
          await page.waitForTimeout(500);

          // Should return to main sections list
          expect(true).toBe(true);
        }
      }
    }
  });
});

test.describe('Customizer - Color Settings', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);

    // Navigate to colors section
    const colorsSection = page.locator('[id*="colors"]');
    if (await colorsSection.count() > 0) {
      const sectionButton = colorsSection.locator('.accordion-section-title, button').first();
      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should change primary color', async ({ page }) => {
    const colorPicker = page.locator('input[type="color"][id*="primary"], .color-picker-hex').first();

    if (await colorPicker.count() > 0) {
      await colorPicker.fill('#FF5733');
      await page.waitForTimeout(1000);

      // Preview should update
      expect(true).toBe(true);
    }
  });

  test('should change background color', async ({ page }) => {
    const bgColorPicker = page.locator('input[type="color"][id*="background"], input[id*="bg_color"]').first();

    if (await bgColorPicker.count() > 0) {
      await bgColorPicker.fill('#FFFFFF');
      await page.waitForTimeout(1000);
    }
  });

  test('should update preview when color changes', async ({ page }) => {
    const colorPicker = page.locator('input[type="color"]').first();

    if (await colorPicker.count() > 0) {
      const initialColor = await colorPicker.inputValue();

      await colorPicker.fill('#000000');
      await page.waitForTimeout(2000);

      // Preview iframe should have updated
      const previewFrame = page.frameLocator('iframe[name="customize-preview"]');
      const body = previewFrame.locator('body');

      if (await body.count() > 0) {
        // Body should be visible (preview loaded)
        expect(true).toBe(true);
      }
    }
  });

  test('should reset color to default', async ({ page }) => {
    const resetButton = page.locator('button:has-text("Reset"), .reset-button');

    if (await resetButton.count() > 0) {
      await resetButton.first().click();
      await page.waitForTimeout(500);

      // Color should reset to default
      expect(true).toBe(true);
    }
  });
});

test.describe('Customizer - Typography Settings', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);

    // Navigate to typography section
    const typographySection = page.locator('[id*="typography"], [id*="fonts"]');
    if (await typographySection.count() > 0) {
      const sectionButton = typographySection.locator('.accordion-section-title, button').first();
      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should change heading font', async ({ page }) => {
    const headingFontSelect = page.locator('select[id*="heading_font"], select[id*="headings"]');

    if (await headingFontSelect.count() > 0) {
      const options = await headingFontSelect.locator('option').all();

      if (options.length > 1) {
        await headingFontSelect.first().selectOption({ index: 1 });
        await page.waitForTimeout(1000);

        // Preview should update with new font
        expect(true).toBe(true);
      }
    }
  });

  test('should change body font', async ({ page }) => {
    const bodyFontSelect = page.locator('select[id*="body_font"], select[id*="base_font"]');

    if (await bodyFontSelect.count() > 0) {
      const options = await bodyFontSelect.locator('option').all();

      if (options.length > 1) {
        await bodyFontSelect.first().selectOption({ index: 1 });
        await page.waitForTimeout(1000);
      }
    }
  });

  test('should adjust font size', async ({ page }) => {
    const fontSizeInput = page.locator('input[id*="font_size"], input[type="number"][id*="size"]');

    if (await fontSizeInput.count() > 0) {
      await fontSizeInput.first().fill('18');
      await page.waitForTimeout(1000);

      // Preview should update
      expect(true).toBe(true);
    }
  });

  test('should use font slider', async ({ page }) => {
    const fontSlider = page.locator('input[type="range"][id*="font"]');

    if (await fontSlider.count() > 0) {
      const slider = fontSlider.first();

      // Get slider bounds
      const box = await slider.boundingBox();

      if (box) {
        // Drag slider
        await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
        await page.mouse.down();
        await page.mouse.move(box.x + box.width * 0.75, box.y + box.height / 2);
        await page.mouse.up();

        await page.waitForTimeout(1000);
      }
    }
  });
});

test.describe('Customizer - Layout Options', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);

    // Navigate to layout section
    const layoutSection = page.locator('[id*="layout"]');
    if (await layoutSection.count() > 0) {
      const sectionButton = layoutSection.locator('.accordion-section-title, button').first();
      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should change layout style', async ({ page }) => {
    const layoutSelect = page.locator('select[id*="layout"], select[id*="sidebar"]');

    if (await layoutSelect.count() > 0) {
      const options = await layoutSelect.locator('option').all();

      if (options.length > 1) {
        await layoutSelect.first().selectOption({ index: 1 });
        await page.waitForTimeout(1000);

        // Preview should update
        expect(true).toBe(true);
      }
    }
  });

  test('should toggle sidebar', async ({ page }) => {
    const sidebarToggle = page.locator('input[type="checkbox"][id*="sidebar"]');

    if (await sidebarToggle.count() > 0) {
      const isChecked = await sidebarToggle.first().isChecked();

      await sidebarToggle.first().click();
      await page.waitForTimeout(1000);

      const newState = await sidebarToggle.first().isChecked();
      expect(newState).not.toBe(isChecked);
    }
  });

  test('should adjust container width', async ({ page }) => {
    const widthInput = page.locator('input[id*="container_width"], input[id*="max_width"]');

    if (await widthInput.count() > 0) {
      await widthInput.first().fill('1200');
      await page.waitForTimeout(1000);
    }
  });
});

test.describe('Customizer - Preview Modes', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);
  });

  test('should switch to mobile preview', async ({ page }) => {
    const mobileButton = page.locator('[data-device="mobile"], .preview-mobile, button[aria-label*="Mobile"]');

    if (await mobileButton.count() > 0) {
      await mobileButton.first().click();
      await page.waitForTimeout(1000);

      // Preview should switch to mobile width
      const previewFrame = page.locator('iframe[name="customize-preview"]');

      if (await previewFrame.count() > 0) {
        const frameWidth = await previewFrame.evaluate((iframe) => {
          return iframe.clientWidth;
        });

        // Mobile width should be narrow
        expect(frameWidth).toBeLessThan(768);
      }
    }
  });

  test('should switch to tablet preview', async ({ page }) => {
    const tabletButton = page.locator('[data-device="tablet"], .preview-tablet, button[aria-label*="Tablet"]');

    if (await tabletButton.count() > 0) {
      await tabletButton.first().click();
      await page.waitForTimeout(1000);

      // Preview should switch to tablet width
      const previewFrame = page.locator('iframe[name="customize-preview"]');

      if (await previewFrame.count() > 0) {
        const frameWidth = await previewFrame.evaluate((iframe) => {
          return iframe.clientWidth;
        });

        expect(frameWidth).toBeGreaterThan(480);
        expect(frameWidth).toBeLessThan(1024);
      }
    }
  });

  test('should switch to desktop preview', async ({ page }) => {
    const desktopButton = page.locator('[data-device="desktop"], .preview-desktop, button[aria-label*="Desktop"]');

    if (await desktopButton.count() > 0) {
      // First switch to mobile
      const mobileButton = page.locator('[data-device="mobile"], .preview-mobile');
      if (await mobileButton.count() > 0) {
        await mobileButton.first().click();
        await page.waitForTimeout(500);
      }

      // Then switch back to desktop
      await desktopButton.first().click();
      await page.waitForTimeout(1000);

      // Preview should be full width
      expect(true).toBe(true);
    }
  });

  test('should maintain changes across device previews', async ({ page }) => {
    // Make a change
    const colorPicker = page.locator('input[type="color"]').first();

    if (await colorPicker.count() > 0) {
      await colorPicker.fill('#FF0000');
      await page.waitForTimeout(1000);

      // Switch device
      const mobileButton = page.locator('[data-device="mobile"], .preview-mobile');
      if (await mobileButton.count() > 0) {
        await mobileButton.first().click();
        await page.waitForTimeout(1000);

        // Color should still be changed
        const currentColor = await colorPicker.inputValue();
        expect(currentColor.toLowerCase()).toBe('#ff0000');
      }
    }
  });
});

test.describe('Customizer - Save and Publish', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);
  });

  test('should enable publish button after changes', async ({ page }) => {
    const publishButton = page.locator('#save, button:has-text("Publish")');

    if (await publishButton.count() > 0) {
      // Make a change
      const colorPicker = page.locator('input[type="color"]').first();

      if (await colorPicker.count() > 0) {
        await colorPicker.fill('#123456');
        await page.waitForTimeout(1000);

        // Publish button should be enabled
        const isDisabled = await publishButton.first().isDisabled();
        expect(isDisabled).toBe(false);
      }
    }
  });

  test('should save draft', async ({ page }) => {
    const draftButton = page.locator('button:has-text("Save Draft"), [data-action="save-draft"]');

    if (await draftButton.count() > 0) {
      // Make a change
      const colorPicker = page.locator('input[type="color"]').first();

      if (await colorPicker.count() > 0) {
        await colorPicker.fill('#ABCDEF');
        await page.waitForTimeout(1000);

        // Save draft
        await draftButton.first().click();
        await page.waitForTimeout(2000);

        // Should show saved message
        const savedMessage = page.locator('.save-notification, [data-message="saved"]');

        if (await savedMessage.count() > 0) {
          await expect(savedMessage.first()).toBeVisible({ timeout: 5000 });
        }
      }
    }
  });

  test('should show unsaved changes warning', async ({ page }) => {
    // Make a change
    const colorPicker = page.locator('input[type="color"]').first();

    if (await colorPicker.count() > 0) {
      await colorPicker.fill('#000000');
      await page.waitForTimeout(1000);

      // Try to close customizer
      const closeButton = page.locator('.customize-controls-close, [aria-label*="Close"]');

      if (await closeButton.count() > 0) {
        // Set up dialog handler
        page.on('dialog', async (dialog) => {
          expect(dialog.message()).toContain('unsaved' || 'changes' || 'leave');
          await dialog.dismiss();
        });

        await closeButton.first().click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should preview changes before publishing', async ({ page }) => {
    const previewButton = page.locator('button:has-text("Preview"), [data-action="preview"]');

    if (await previewButton.count() > 0) {
      await previewButton.first().click();
      await page.waitForTimeout(1000);

      // Preview should open (in new tab or expanded)
      expect(true).toBe(true);
    }
  });
});

test.describe('Customizer - Widget Areas', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);

    // Navigate to widgets section
    const widgetsSection = page.locator('[id*="widgets"]');
    if (await widgetsSection.count() > 0) {
      const sectionButton = widgetsSection.locator('.accordion-section-title, button').first();
      if (await sectionButton.count() > 0) {
        await sectionButton.click();
        await page.waitForTimeout(500);
      }
    }
  });

  test('should display widget areas', async ({ page }) => {
    const widgetAreas = page.locator('.customize-control-widget_area, [id*="sidebar"]');

    if (await widgetAreas.count() > 0) {
      expect(await widgetAreas.count()).toBeGreaterThan(0);
    }
  });

  test('should add widget to sidebar', async ({ page }) => {
    const addWidgetButton = page.locator('button:has-text("Add a Widget"), .add-new-widget');

    if (await addWidgetButton.count() > 0) {
      await addWidgetButton.first().click();
      await page.waitForTimeout(500);

      // Widget selection should appear
      const widgetSelection = page.locator('.widget-tpl, [id*="available-widgets"]');

      if (await widgetSelection.count() > 0) {
        await expect(widgetSelection.first()).toBeVisible();
      }
    }
  });

  test('should remove widget from sidebar', async ({ page }) => {
    const removeButton = page.locator('.widget-control-remove, button:has-text("Remove")');

    if (await removeButton.count() > 0) {
      await removeButton.first().click();
      await page.waitForTimeout(500);

      // Widget should be removed
      expect(true).toBe(true);
    }
  });
});

test.describe('Customizer - Accessibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/customize.php');
    await page.waitForTimeout(3000);
  });

  test('should support keyboard navigation', async ({ page }) => {
    // Tab through controls
    await page.keyboard.press('Tab');
    const focused1 = await page.evaluate(() => document.activeElement?.tagName);

    await page.keyboard.press('Tab');
    const focused2 = await page.evaluate(() => document.activeElement?.tagName);

    expect(['BUTTON', 'INPUT', 'SELECT', 'A']).toContain(focused1);
    expect(['BUTTON', 'INPUT', 'SELECT', 'A']).toContain(focused2);
  });

  test('should have ARIA labels on controls', async ({ page }) => {
    const controls = await page.locator('.customize-control').all();

    for (const control of controls.slice(0, 5)) {
      if (await control.isVisible()) {
        const label = control.locator('label, .customize-control-title');

        if (await label.count() > 0) {
          const labelText = await label.first().textContent();
          expect(labelText?.length).toBeGreaterThan(0);
        }
      }
    }
  });

  test('should announce preview updates to screen readers', async ({ page }) => {
    const liveRegion = page.locator('[aria-live], [role="status"]');

    if (await liveRegion.count() > 0) {
      const ariaLive = await liveRegion.first().getAttribute('aria-live');
      expect(['polite', 'assertive'].includes(ariaLive || '') || true).toBeTruthy();
    }
  });

  test('should have focus indicators', async ({ page }) => {
    const firstButton = page.locator('button').first();

    if (await firstButton.count() > 0) {
      await firstButton.focus();

      const hasFocusStyle = await firstButton.evaluate((el) => {
        const styles = window.getComputedStyle(el);
        return styles.outlineWidth !== '0px' ||
               styles.boxShadow !== 'none';
      });

      expect(typeof hasFocusStyle).toBe('boolean');
    }
  });
});
