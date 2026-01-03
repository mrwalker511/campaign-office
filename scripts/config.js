/**
 * Build Scripts Configuration
 * 
 * Centralized configuration for all optimization scripts.
 * Supports environment variables and multiple test hosts.
 * 
 * Usage:
 *   - Set SITE_URL environment variable, OR
 *   - Create a .env file with SITE_URL=http://your-host:port, OR
 *   - Configure hosts below for named environments
 */

import { existsSync, readFileSync } from 'fs';
import { resolve } from 'path';

// Load .env file if it exists
const envPath = resolve(process.cwd(), '.env');
if (existsSync(envPath)) {
    const envContent = readFileSync(envPath, 'utf8');
    envContent.split('\n').forEach(line => {
        const [key, ...valueParts] = line.split('=');
        if (key && valueParts.length) {
            process.env[key.trim()] = valueParts.join('=').trim().replace(/^["']|["']$/g, '');
        }
    });
}

/**
 * Named host configurations
 * Add your test environments here
 */
const hosts = {
    local: 'http://localhost:8881',
    localwp: 'http://campaignpress.local',
    staging: 'http://staging.campaignpress.com',
    production: 'https://campaignpress.com',
    // Add more hosts as needed
};

/**
 * Get the active site URL
 * Priority: SITE_URL env var > HOST_ENV env var > default to 'local'
 */
export function getSiteUrl() {
    // Direct URL override
    if (process.env.SITE_URL) {
        return process.env.SITE_URL.replace(/\/$/, ''); // Remove trailing slash
    }

    // Named host environment
    const hostEnv = process.env.HOST_ENV || 'local';
    if (hosts[hostEnv]) {
        return hosts[hostEnv];
    }

    console.warn(`⚠️  Unknown HOST_ENV "${hostEnv}", falling back to local`);
    return hosts.local;
}

/**
 * Pages configuration for critical CSS and Lighthouse testing
 * Override with PAGES_CONFIG env var pointing to a JSON file
 */
export function getPages() {
    // Check for custom pages config file
    if (process.env.PAGES_CONFIG && existsSync(process.env.PAGES_CONFIG)) {
        try {
            const customPages = JSON.parse(readFileSync(process.env.PAGES_CONFIG, 'utf8'));
            return customPages.map(page => ({
                ...page,
                url: page.url.startsWith('http') ? page.url : `${getSiteUrl()}${page.url}`
            }));
        } catch (error) {
            console.warn(`⚠️  Failed to load custom pages config: ${error.message}`);
        }
    }

    const siteUrl = getSiteUrl();

    return [
        {
            url: `${siteUrl}/`,
            output: 'assets/css/critical/home.css',
            name: 'Homepage (Hero + Countdown)',
            slug: 'home'
        },
        {
            url: `${siteUrl}/events/`,
            output: 'assets/css/critical/events.css',
            name: 'Events (Calendar + RSVP)',
            slug: 'events'
        },
        {
            url: `${siteUrl}/donate/`,
            output: 'assets/css/critical/donate.css',
            name: 'Donate (Blockchain Form)',
            slug: 'donate'
        },
        {
            url: `${siteUrl}/volunteer/`,
            output: 'assets/css/critical/volunteer.css',
            name: 'Volunteer (Skill Matcher)',
            slug: 'volunteer'
        }
    ];
}

/**
 * Asset directories configuration
 */
export const directories = {
    images: {
        source: 'assets/images',
        output: 'assets/dist/images'
    },
    css: {
        source: 'assets/css',
        output: 'assets/dist/css',
        critical: 'assets/css/critical'
    },
    js: {
        source: 'assets/js',
        output: 'assets/dist/js'
    },
    reports: 'lighthouse-reports'
};

/**
 * Browser/Lighthouse settings
 */
export const browserSettings = {
    dimensions: [
        { width: 1300, height: 900 },  // Desktop
        { width: 768, height: 900 },   // Tablet
        { width: 375, height: 900 }    // Mobile
    ],
    timeout: 60000,
    renderWaitTime: 1000
};

/**
 * PurgeCSS safelist patterns
 */
export const purgeSafelist = {
    standard: [
        /^wp-/,
        /^has-/,
        /^is-/,
        /^campaignpress-/,
        /^cp-/,
        /^dashicons/,
        /^screen-reader-text/
    ],
    deep: [
        /modal/,
        /popover/,
        /tooltip/,
        /dropdown/
    ]
};

/**
 * Target browsers for CSS/JS optimization
 */
export const browserTargets = {
    chrome: 95,
    firefox: 90,
    safari: 14
};

/**
 * Performance thresholds
 */
export const performanceThresholds = {
    lcp: 2500,      // Largest Contentful Paint (ms)
    cls: 0.1,       // Cumulative Layout Shift
    tbt: 300,       // Total Blocking Time (ms)
    targetScore: 95 // Lighthouse score target
};

/**
 * Logger utility for consistent output
 */
export const logger = {
    info: (msg) => console.log(`ℹ️  ${msg}`),
    success: (msg) => console.log(`✅ ${msg}`),
    warn: (msg) => console.log(`⚠️  ${msg}`),
    error: (msg) => console.error(`❌ ${msg}`),
    header: (msg) => console.log(`\n${'═'.repeat(60)}\n🚀 ${msg}\n${'═'.repeat(60)}\n`),
    divider: () => console.log('─'.repeat(60)),
    step: (msg) => console.log(`📍 ${msg}`)
};

// Export configuration summary for debugging
export function printConfig() {
    console.log('\n📋 Current Configuration:');
    console.log(`   Site URL: ${getSiteUrl()}`);
    console.log(`   Host Env: ${process.env.HOST_ENV || 'local (default)'}`);
    console.log(`   Pages: ${getPages().length} configured\n`);
}
