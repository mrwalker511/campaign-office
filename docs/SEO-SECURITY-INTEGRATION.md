# SEO and Security Plugin Integration Guide

**CampaignPress Theme - Version 2.0.0**

This guide provides detailed instructions for integrating popular SEO and security plugins with CampaignPress, along with recommended settings for political campaigns.

---

## Table of Contents

1. [SEO Plugins](#seo-plugins)
   - [Yoast SEO](#yoast-seo)
   - [Rank Math](#rank-math)
   - [All in One SEO](#all-in-one-seo)
2. [Security Plugins](#security-plugins)
   - [Wordfence Security](#wordfence-security)
   - [Sucuri Security](#sucuri-security)
   - [iThemes Security](#ithemes-security)
3. [Performance Optimization](#performance-optimization)
   - [WP Fastest Cache](#wp-fastest-cache)
   - [WP Rocket](#wp-rocket)
4. [SSL/HTTPS](#ssl-https)
5. [Campaign-Specific Best Practices](#campaign-specific-best-practices)

---

## SEO Plugins

### Yoast SEO

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "Yoast SEO" > Install & Activate
```

**Recommended Settings:**

#### General Settings
1. **First-time Configuration**
   - Navigate to: `SEO > General > Configuration Wizard`
   - Select: "Campaign/Political Organization"
   - Enter your candidate name and campaign tagline

#### Search Appearance

**Homepage:**
```
SEO > Search Appearance > Homepage
Title: [Candidate Name] for [Office] | [Tagline]
Meta Description: [Brief campaign message, 150-160 characters]
```

**Custom Post Types:**
```php
// CampaignPress automatically enables Yoast for custom post types
// Configure at: SEO > Search Appearance > Content Types

Issues:
- Show in search results: Yes
- Title template: %%title%% | Issues | %%sitename%%
- Meta description template: %%excerpt%%

Events:
- Show in search results: Yes
- Title template: %%title%% | %%date%% | %%sitename%%
- Meta description template: Join us for %%title%% on %%date%% in %%city%%

Endorsements:
- Show in search results: Yes
- Title template: %%title%% Endorses [Candidate Name]
- Meta description template: %%excerpt%%
```

**Social Media:**
```
SEO > Social > Facebook
- Enable Open Graph: Yes
- Default image: Upload campaign logo (1200x630px)

SEO > Social > Twitter
- Enable Twitter Card: Yes
- Card type: Summary with large image
- Default image: Upload campaign logo (1200x600px)
```

**Schema.org Integration:**

CampaignPress includes built-in schema markup. Verify at:
```
SEO > Search Appearance > Schema
- Organization Name: [Campaign Name]
- Organization Logo: [Upload logo]
- Schema Type: Political Organization
```

**XML Sitemaps:**
```
SEO > General > Features > XML Sitemaps: Enabled
Automatically includes: Issues, Events, Endorsements, Team Members
```

---

### Rank Math

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "Rank Math" > Install & Activate
```

**Setup Wizard:**
1. Navigate to: `Rank Math > Setup Wizard`
2. Select "Political Organization" as site type
3. Connect to Google Search Console
4. Configure social profiles

**Custom Post Type Settings:**
```php
// Navigate to: Rank Math > Titles & Meta > Post Types

Issues:
- Add in Sitemap: Yes
- Title: %title% | %sep% %sitename%
- Description: %excerpt%
- Rich Snippet Type: Article

Events:
- Add in Sitemap: Yes
- Title: %title% | %date% | %sep% %sitename%
- Description: %excerpt%
- Rich Snippet Type: Event
- Enable Event Schema: Yes

Endorsements:
- Add in Sitemap: Yes
- Title: %title% Endorses %sitename%
- Rich Snippet Type: Review
```

**Local SEO (for local campaigns):**
```
Rank Math > Local SEO
- Enable Local SEO: Yes
- Business Type: Political Organization
- Address: [Campaign headquarters]
- Phone: [Campaign office number]
- Opening Hours: [Office hours]
```

---

### All in One SEO

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "All in One SEO" > Install & Activate
```

**Configuration:**
```
All in One SEO > General Settings
- Home Page Title: [Candidate Name] for [Office] | [Tagline]
- Meta Description: [Campaign message, 150-160 characters]

All in One SEO > Feature Manager
- Enable: XML Sitemaps, Social Meta, Robots.txt
```

**Custom Post Types:**
```
All in One SEO > Search Appearance > Content Types
- Enable all CampaignPress post types (Issues, Events, etc.)
- Show in search results: Yes
```

---

## Security Plugins

### Wordfence Security

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "Wordfence" > Install & Activate
```

**Essential Campaign Security Settings:**

#### Firewall Configuration
```
Wordfence > Firewall > Manage Firewall
- Protection Level: Extended Protection (recommended for campaigns)
- Enable: Real-Time IP Blocklist
- Enable: Rate Limiting (prevents brute force attacks)
```

**Rate Limiting Settings (Critical for campaigns):**
```
Wordfence > Firewall > Rate Limiting
- Enable Rate Limiting: Yes

Recommended Limits:
- Human page views: 60 per minute
- Human pages not found (404s): 20 per minute
- Crawlers: 300 per minute
- Crawlers 404s: 60 per minute

These limits prevent DDoS attacks common during campaign season.
```

#### Login Security
```
Wordfence > Login Security
- Enable Two-Factor Authentication: Yes (required for all admins)
- Enable XML-RPC Authentication: No
- Enable reCAPTCHA: Yes
- Disable login for invalid usernames: Yes
```

**Two-Factor Authentication Setup:**
```php
// Force 2FA for all admin users (add to functions.php or child theme)
add_filter('wordfence_ls_require_2fa', function($required, $user) {
    if (in_array('administrator', $user->roles)) {
        return true; // Require 2FA for all admins
    }
    return $required;
}, 10, 2);
```

#### Scan Settings
```
Wordfence > Scan > Manage Scan
- Scan Schedule: Daily at 3:00 AM
- Enable: Scan for malware signatures
- Enable: Check file integrity
- Enable: Check for known security vulnerabilities
```

**Email Alerts:**
```
Wordfence > All Options > Email Alert Preferences
Send email alerts for:
- High severity: Immediately
- Break-in attempts: After 5 failed logins
- New administrator user: Immediately
- Plugin/theme changes: Immediately
```

---

### Sucuri Security

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "Sucuri Security" > Install & Activate
```

**Post-Hardening Settings:**
```
Sucuri > Settings > Hardening

Enable all hardening options:
- Verify WordPress Version: Yes
- Block PHP Execution: Yes (in uploads directory)
- Disable Theme/Plugin Editor: Yes
- Security Headers: Yes
- Block Proxy Access: Yes
```

**Audit Logging (Essential for campaigns):**
```
Sucuri > Dashboard > Audit Logs
Logs all:
- User logins/logouts
- Content changes
- Plugin/theme installations
- Failed login attempts
- File modifications

Retention: 90 days (increase for compliance)
```

**File Integrity Monitoring:**
```
Sucuri > Settings > File Integrity Monitor
- Enable: Yes
- Scan frequency: Every 6 hours
- Alert on: Any file modification outside WordPress admin
```

---

### iThemes Security

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "iThemes Security" > Install & Activate
```

**Security Check Configuration:**
```
Security > Security Check

Enable all recommended features:
- Strong Password Enforcement: Yes (min 12 characters)
- Two-Factor Authentication: Yes
- Away Mode: Yes (disable login during non-office hours)
- File Change Detection: Yes
- 404 Detection: Yes
- Brute Force Protection: Yes
```

**Campaign-Specific Settings:**

**Away Mode (Recommended for campaigns):**
```php
// Disable logins outside campaign office hours
Security > Settings > Away Mode
- Enable Away Mode: Yes
- Active Days: Monday-Saturday
- Active Hours: 6:00 PM - 8:00 AM
- Allowed IPs: [Add campaign staff IPs]
```

**Database Backups:**
```
Security > Settings > Database Backups
- Backup Schedule: Daily
- Retention: 30 days
- Email Backups: Yes (to campaign tech lead)
```

---

## Performance Optimization

### WP Fastest Cache

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "WP Fastest Cache" > Install & Activate
```

**Campaign-Optimized Settings:**
```
WP Fastest Cache > Settings

Enable:
- ✓ Cache System
- ✓ Preload
- ✓ Logged-in Users (cache for logged-out users only)
- ✓ Mobile Theme
- ✓ New Post (clear cache when publishing)
- ✓ Update Post
- ✓ Minify HTML
- ✓ Minify CSS
- ✓ Combine CSS
- ✓ Gzip
- ✓ Browser Caching
- ✓ Disable Emojis

Do NOT Enable:
- ✗ Minify JS (can break donation forms)
- ✗ Combine JS (can break event RSVP)

Cache Timeout: 6 hours (to keep event/fundraising data fresh)
```

**Preload Settings:**
```
Preload > Posts per session: 50
Preload > Homepage: Yes
Preload > Posts: Yes
Preload > Pages: Yes
Preload > Categories: Yes
Preload > Tags: No
```

**Exclude Pages from Cache:**
```
WP Fastest Cache > Exclude

Add these URLs:
- /volunteer (real-time signup form)
- /donate (payment processing)
- /events/*/rsvp (RSVP forms)
- /admin-ajax.php (AJAX requests)
```

---

### WP Rocket

**Installation:**
```bash
Download from WP Rocket website > Upload to WordPress > Activate
```

**Recommended Settings:**
```
WP Rocket > Cache
- Enable caching for mobile devices: Yes
- Separate cache files for mobile: No
- Enable for logged-in users: No
- Cache lifespan: 6 hours

WP Rocket > File Optimization
- Minify HTML: Yes
- Combine Google Fonts: Yes
- Minify CSS: Yes
- Minify JavaScript: No (can break forms)
- Delay JavaScript: No (can break donation/RSVP)

WP Rocket > Media
- Enable LazyLoad: Yes (images, iframes, videos)
- Exclude: Campaign logo, hero images

WP Rocket > Advanced Rules
Never cache URLs:
/volunteer
/donate
/(.*)rsvp(.*)
```

---

## SSL/HTTPS

### Really Simple SSL

**Installation:**
```bash
WordPress Admin > Plugins > Add New > Search "Really Simple SSL" > Install & Activate
```

**Auto-Configuration:**
```
Really Simple SSL will automatically:
- Detect SSL certificate
- Update all URLs to HTTPS
- Add HTTPS redirect rules
- Fix mixed content warnings
```

**Manual Verification:**
```bash
# Check SSL certificate
https://www.ssllabs.com/ssltest/analyze.html?d=yourdomain.com

Goal: A+ rating
```

**Force HTTPS via .htaccess (if not using plugin):**
```apache
# Add to .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Campaign-Specific Best Practices

### 1. Google Search Console Integration

**Setup:**
```bash
1. Visit: https://search.google.com/search-console
2. Add Property: https://yourdomain.com
3. Verify ownership (via DNS or HTML file)
4. Submit sitemap: https://yourdomain.com/sitemap_index.xml
```

**Monitor:**
- Search queries driving traffic
- Click-through rates (CTR)
- Mobile usability
- Core Web Vitals
- Manual actions/penalties

---

### 2. Google Analytics 4 Setup

**Installation via Plugin:**
```bash
Install: MonsterInsights or Site Kit by Google
Connect: Google Analytics property
```

**Event Tracking for Campaigns:**
```javascript
// CampaignPress includes built-in GA4 events
// Tracked automatically:
- Donation button clicks
- Volunteer form submissions
- Event RSVP submissions
- Issue page views
- Endorsement views
- Social media clicks

// View in GA4: Reports > Engagement > Events
```

**Custom Dimensions:**
```
GA4 > Configure > Custom Definitions

Add dimensions:
- Event_Type (rally, town_hall, fundraiser)
- Issue_Category (healthcare, education, economy)
- Volunteer_Interest (canvassing, phone_banking)
- Donation_Source (website, email, social)
```

---

### 3. Security Hardening Checklist

**File Permissions:**
```bash
# Set correct permissions
find . -type d -exec chmod 755 {} \;  # Directories
find . -type f -exec chmod 644 {} \;  # Files
chmod 600 wp-config.php               # Config file
```

**wp-config.php Security:**
```php
// Add security keys (generate at: https://api.wordpress.org/secret-key/1.1/salt/)
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

// Disable file editing
define('DISALLOW_FILE_EDIT', true);

// Limit login attempts
define('LIMIT_LOGIN_ATTEMPTS', 5);

// Set auto-save interval (reduce server load)
define('AUTOSAVE_INTERVAL', 300); // 5 minutes
```

**Database Security:**
```php
// Change default table prefix from wp_ to something unique
$table_prefix = 'camp2024_';  // Use in wp-config.php during installation
```

---

### 4. Backup Strategy

**Recommended Plugins:**
- UpdraftPlus (Free/Premium)
- BackupBuddy (Premium)
- VaultPress/Jetpack (Premium)

**Backup Schedule:**
```
Daily: Database backup
Weekly: Full site backup (files + database)
Before major updates: Full backup
Retention: 30 days minimum
Storage: Off-site (Dropbox, Google Drive, S3)
```

**UpdraftPlus Settings:**
```
UpdraftPlus > Settings
- Files: Weekly, 4 backups
- Database: Daily, 14 backups
- Remote Storage: Google Drive + Dropbox (redundancy)
- Email notifications: To tech lead
```

---

### 5. CDN Configuration (Optional, for high-traffic campaigns)

**Cloudflare Free Plan:**
```bash
1. Sign up: https://cloudflare.com
2. Add site
3. Update nameservers at domain registrar
4. Enable:
   - Auto Minify (HTML, CSS, JS)
   - Brotli compression
   - Rocket Loader: Off (breaks forms)
   - Always Use HTTPS: On
   - DDoS Protection: Auto
```

**Page Rules for Campaigns:**
```
*yourdomain.com/donate*
- Cache Level: Bypass

*yourdomain.com/volunteer*
- Cache Level: Bypass

*yourdomain.com/events/*/rsvp*
- Cache Level: Bypass

*yourdomain.com/*
- Cache Level: Standard
- Browser Cache TTL: 4 hours
```

---

### 6. GDPR/Privacy Compliance

**Required Pages:**
```
Create these pages via WordPress:
- Privacy Policy: /privacy-policy
- Cookie Policy: /cookie-policy
- Terms of Service: /terms
```

**Cookie Consent Plugin:**
```bash
Install: Cookie Notice & Compliance for GDPR/CCPA
Settings:
- Enable cookie notice: Yes
- Position: Bottom
- Button text: "I Understand"
- Privacy policy link: Yes
```

**CampaignPress Privacy Features:**
```php
// Built-in privacy features:
- Volunteer data export (GDPR right to access)
- Volunteer data deletion (GDPR right to erasure)
- Email opt-out links (CAN-SPAM compliance)
- Data retention limits (configurable)
```

---

### 7. Email Deliverability

**SMTP Plugin (Recommended):**
```bash
Install: WP Mail SMTP
Configure: Use Gmail/SendGrid/Mailgun for reliable delivery
```

**SPF/DKIM Records:**
```dns
# Add to DNS settings
TXT record: v=spf1 include:_spf.google.com ~all
DKIM: Add key from email provider
DMARC: v=DMARC1; p=quarantine; rua=mailto:tech@yourdomain.com
```

---

### 8. Monitoring & Uptime

**Recommended Services:**
- UptimeRobot (Free): Monitor site availability
- Jetpack Monitor (Free): WordPress-specific monitoring
- Google PageSpeed Insights: Performance tracking

**UptimeRobot Setup:**
```
1. Create monitor: https://uptimerobot.com
2. Monitor type: HTTPS
3. URL: https://yourdomain.com
4. Interval: 5 minutes
5. Alert contacts: Tech lead + campaign manager
```

---

## Integration Code Snippets

### Add Custom Schema Markup

```php
// Add to functions.php or child theme
function campaignpress_add_schema_markup() {
    if (is_singular('cp_event')) {
        $event_date = get_post_meta(get_the_ID(), '_cp_event_date', true);
        $event_time = get_post_meta(get_the_ID(), '_cp_event_time', true);
        $event_location = get_post_meta(get_the_ID(), '_cp_event_location', true);

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => get_the_title(),
            'startDate' => $event_date . 'T' . $event_time,
            'location' => array(
                '@type' => 'Place',
                'name' => $event_location,
            ),
            'organizer' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
            ),
        );

        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
    }
}
add_action('wp_head', 'campaignpress_add_schema_markup');
```

---

## Troubleshooting

### Cache Issues After Updates
```bash
Clear all caches after theme/plugin updates:
1. Plugin cache (WP Fastest Cache/WP Rocket)
2. Browser cache (Ctrl+Shift+R)
3. CDN cache (Cloudflare)
4. Server cache (contact host)
```

### Forms Not Submitting
```bash
Disable these optimizations temporarily:
- JavaScript minification
- JavaScript combining
- Delay JavaScript execution
- Rocket Loader (Cloudflare)
```

### Slow Admin Dashboard
```bash
Disable these for logged-in users:
- Page caching for logged-in users
- Object caching (if causing issues)
- Heartbeat Control (or limit to 60 seconds)
```

---

## Support Resources

- **CampaignPress Documentation**: https://campaignpress.org/docs
- **WordPress Security Guide**: https://wordpress.org/support/article/hardening-wordpress/
- **Yoast SEO Documentation**: https://yoast.com/help/
- **Wordfence Documentation**: https://www.wordfence.com/help/
- **Google Search Central**: https://developers.google.com/search/docs

---

**Last Updated:** November 2024
**CampaignPress Version:** 2.0.0

For campaign-specific security questions, consult with a cybersecurity professional experienced in political campaigns.
