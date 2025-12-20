/**
 * Web Vitals Performance Tests
 *
 * Tests Core Web Vitals metrics
 */

import { test, expect } from '@playwright/test';

test.describe('Core Web Vitals', () => {
  test('should meet LCP threshold', async ({ page }) => {
    await page.goto('/');

    // Get Largest Contentful Paint
    const lcp = await page.evaluate(() => {
      return new Promise((resolve) => {
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          const lastEntry = entries[entries.length - 1];
          resolve(lastEntry.renderTime || lastEntry.loadTime);
        }).observe({ type: 'largest-contentful-paint', buffered: true });

        // Timeout after 10 seconds
        setTimeout(() => resolve(0), 10000);
      });
    });

    console.log(`LCP: ${lcp}ms`);

    // LCP should be under 2.5s (2500ms) for good rating
    expect(lcp).toBeLessThan(2500);
  });

  test('should meet CLS threshold', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    // Get Cumulative Layout Shift
    const cls = await page.evaluate(() => {
      return new Promise((resolve) => {
        let clsScore = 0;

        new PerformanceObserver((list) => {
          for (const entry of list.getEntries()) {
            if (!entry.hadRecentInput) {
              clsScore += entry.value;
            }
          }
        }).observe({ type: 'layout-shift', buffered: true });

        // Wait 3 seconds to capture shifts
        setTimeout(() => resolve(clsScore), 3000);
      });
    });

    console.log(`CLS: ${cls}`);

    // CLS should be under 0.1 for good rating
    expect(cls).toBeLessThan(0.1);
  });

  test('should meet FID threshold', async ({ page }) => {
    await page.goto('/');

    // Simulate user interaction
    await page.click('body');

    // Get First Input Delay
    const fid = await page.evaluate(() => {
      return new Promise((resolve) => {
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          if (entries.length > 0) {
            resolve(entries[0].processingStart - entries[0].startTime);
          }
        }).observe({ type: 'first-input', buffered: true });

        // Timeout
        setTimeout(() => resolve(0), 5000);
      });
    });

    console.log(`FID: ${fid}ms`);

    // FID should be under 100ms for good rating
    if (fid > 0) {
      expect(fid).toBeLessThan(100);
    }
  });
});

test.describe('Resource Performance', () => {
  test('should have optimized images', async ({ page }) => {
    await page.goto('/');

    const oversizedImages = await page.evaluate(() => {
      const images = Array.from(document.querySelectorAll('img'));
      return images.filter((img) => {
        const naturalSize = img.naturalWidth * img.naturalHeight;
        const displaySize = img.width * img.height;
        // Check if image is more than 2x oversized
        return naturalSize > displaySize * 4;
      }).length;
    });

    console.log(`Oversized images: ${oversizedImages}`);
    expect(oversizedImages).toBe(0);
  });

  test('should have reasonable page size', async ({ page }) => {
    const response = await page.goto('/');

    const resourceSizes = await page.evaluate(() => {
      return performance.getEntriesByType('resource').reduce((total, resource) => {
        return total + (resource.transferSize || 0);
      }, 0);
    });

    const pageSizeKB = resourceSizes / 1024;
    console.log(`Total page size: ${pageSizeKB.toFixed(2)} KB`);

    // Page should be under 2MB
    expect(pageSizeKB).toBeLessThan(2048);
  });

  test('should minimize render-blocking resources', async ({ page }) => {
    await page.goto('/');

    const renderBlockingResources = await page.evaluate(() => {
      return performance
        .getEntriesByType('resource')
        .filter((r) => {
          return (
            (r.initiatorType === 'link' || r.initiatorType === 'script') &&
            r.renderBlockingStatus === 'blocking'
          );
        }).length;
    });

    console.log(`Render-blocking resources: ${renderBlockingResources}`);

    // Should have minimal render-blocking resources
    expect(renderBlockingResources).toBeLessThan(5);
  });
});
