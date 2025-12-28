# Production Reference

**Complete guide to deploying and shipping CampaignPress**

Version: 2.0.0 | Last Updated: December 28, 2025

---

## Table of Contents

1. [Pre-Launch Checklist](#pre-launch-checklist)
2. [Production Deployment](#production-deployment)
3. [Performance Optimization](#performance-optimization)
4. [Security Hardening](#security-hardening)
5. [Monitoring & Maintenance](#monitoring--maintenance)
6. [Troubleshooting](#troubleshooting)

---

## Pre-Launch Checklist

### Code Quality

- [ ] All code follows WordPress coding standards
- [ ] ESLint passes with no errors
- [ ] PHP_CodeSniffer passes (if installed)
- [ ] No `console.log()` or `var_dump()` in code
- [ ] All debug code removed
- [ ] Comments added for complex logic
- [ ] CHANGELOG.md updated
- [ ] Version numbers incremented

### Security Audit

- [ ] All inputs sanitized
- [ ] All outputs escaped
- [ ] SQL queries use prepared statements
- [ ] Nonces verified on forms
- [ ] Capability checks on admin features
- [ ] File upload validation
- [ ] CSRF protection enabled
- [ ] XSS vulnerabilities addressed
- [ ] No hardcoded credentials
- [ ] `.env` files not committed

### Performance

- [ ] Assets minified (`npm run build`)
- [ ] Images optimized
- [ ] Database queries optimized
- [ ] No N+1 query problems
- [ ] Caching strategy implemented
- [ ] Lazy loading enabled
- [ ] Critical CSS inlined
- [ ] JavaScript deferred where possible

### Functionality

- [ ] All features tested
- [ ] Forms submit correctly
- [ ] Email sending works
- [ ] Payment processing tested
- [ ] CRM operations functional
- [ ] Premium features work
- [ ] License validation works
- [ ] No 404 errors on site

### Accessibility

- [ ] WCAG 2.1 AA compliance verified
- [ ] Keyboard navigation works
- [ ] Screen reader tested
- [ ] Color contrast meets standards
- [ ] ARIA labels present
- [ ] Alt text on images
- [ ] Form labels associated
- [ ] Skip links functional

### Compatibility

- [ ] WordPress 6.9+ tested
- [ ] PHP 8.1+ tested
- [ ] MySQL 8.0+ tested
- [ ] Chrome tested
- [ ] Firefox tested
- [ ] Safari tested
- [ ] Edge tested
- [ ] Mobile responsive
- [ ] Tablet responsive

### Content

- [ ] Demo content removed
- [ ] Placeholder text replaced
- [ ] Images have proper licensing
- [ ] Privacy policy published
- [ ] Terms of service published
- [ ] Contact information correct
- [ ] Social media links valid
- [ ] Footer copyright updated

### SEO

- [ ] Meta titles set
- [ ] Meta descriptions added
- [ ] Open Graph tags configured
- [ ] Twitter cards configured
- [ ] Sitemap generated
- [ ] Robots.txt configured
- [ ] Analytics installed
- [ ] Search Console verified

### Legal & Compliance

- [ ] GPL license maintained
- [ ] FEC compliance tested (if applicable)
- [ ] GDPR compliance verified
- [ ] Cookie notice displayed
- [ ] Privacy policy links
- [ ] Terms of service accepted

---

## Production Deployment

### Environment Setup

**Server Requirements:**
- Ubuntu 22.04 LTS or Debian 11
- Nginx 1.20+ or Apache 2.4+
- PHP 8.1+ with OPcache
- MySQL 8.0+ or MariaDB 10.5+
- Redis or Memcached (recommended)
- SSL certificate (Let's Encrypt)

**PHP Configuration:**
```ini
memory_limit = 512M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
```

**MySQL Configuration:**
```ini
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
```

### Deployment Methods

**Option 1: Manual SFTP**
```bash
# Build production assets
npm run build

# Create clean ZIP
zip -r campaign-office-2.0.0.zip . \
    -x "*.git*" \
    -x "node_modules/*" \
    -x "vendor/*" \
    -x ".vscode/*" \
    -x "*.env"

# Upload via SFTP to wp-content/themes/
# Activate in WordPress admin
```

**Option 2: Git Deployment**
```bash
# On server
cd /var/www/html/wp-content/themes/campaign-office
git pull origin main
npm install --production
npm run build

# Set permissions
chown -R www-data:www-data .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

**Option 3: CI/CD (GitHub Actions)**
```yaml
name: Deploy Production

on:
  push:
    tags:
      - 'v*'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'

      - name: Install and Build
        run: |
          npm ci
          npm run build

      - name: Deploy via SSH
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/html/wp-content/themes/campaign-office
            git pull origin main
            npm install --production
            npm run build
            chown -R www-data:www-data .
```

### Post-Deployment Steps

**1. Verify Installation**
```bash
# Check WordPress version
wp core version

# Check theme activation
wp theme list

# Flush rewrite rules
wp rewrite flush

# Verify database
wp db check
```

**2. Configure WordPress**
```php
// wp-config.php additions
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('DISALLOW_FILE_EDIT', true);
define('WP_POST_REVISIONS', 5);
define('AUTOSAVE_INTERVAL', 300);
```

**3. Set Up Caching**
```bash
# Install Redis object cache plugin
wp plugin install redis-cache --activate
wp redis enable

# Or configure W3 Total Cache
wp plugin install w3-total-cache --activate
```

**4. Configure CDN**
- CloudFlare: Add site, update nameservers
- Set caching rules
- Enable Auto Minify
- Configure SSL/TLS
- Enable Brotli compression

**5. Test Critical Paths**
- Homepage loads
- Donation forms work
- Contact forms send
- Events display correctly
- CRM accessible (premium)
- API endpoints respond (enterprise)

---

## Performance Optimization

### Asset Optimization

**Minification:**
```bash
# Already done by Vite
npm run build
```

**Image Optimization:**
```bash
# Install imagemin
npm install -g imagemin-cli

# Optimize images
imagemin assets/images/* --out-dir=assets/images/optimized

# Or use WordPress plugin
wp plugin install imagify --activate
```

**Lazy Loading:**
```html
<img src="image.jpg" loading="lazy" alt="Description">
```

### Caching Strategy

**Browser Caching (Nginx):**
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2?)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

**Object Caching:**
```php
// WordPress transients
$data = get_transient('expensive_data');
if (false === $data) {
    $data = expensive_function();
    set_transient('expensive_data', $data, HOUR_IN_SECONDS);
}
```

**Redis Configuration:**
```php
// wp-config.php
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
define('WP_CACHE_KEY_SALT', 'campaignpress_');
```

### Database Optimization

**Indexes:**
```sql
-- Check for missing indexes
SHOW INDEX FROM wp_cp_contacts;

-- Add index if missing
CREATE INDEX idx_email ON wp_cp_contacts(email);
```

**Query Optimization:**
```php
// Bad - N+1 query
foreach ($posts as $post) {
    $meta = get_post_meta($post->ID, 'key', true);
}

// Good - Single query
$posts = get_posts(array(
    'meta_key' => 'key',
    'posts_per_page' => -1
));
```

**Database Cleanup:**
```bash
# Optimize tables
wp db optimize

# Clean revisions
wp post delete $(wp post list --post_type='revision' --format=ids) --force

# Clean transients
wp transient delete --all
```

---

## Security Hardening

### WordPress Security

**File Permissions:**
```bash
# Directories
find . -type d -exec chmod 755 {} \;

# Files
find . -type f -exec chmod 644 {} \;

# wp-config.php
chmod 600 wp-config.php
```

**Disable File Editing:**
```php
// wp-config.php
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);  // If you want to prevent plugin/theme installs
```

**Security Headers (Nginx):**
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:;" always;
```

**SSL/TLS:**
```nginx
# Force HTTPS
server {
    listen 80;
    server_name campaignpress.com www.campaignpress.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS configuration
server {
    listen 443 ssl http2;
    server_name campaignpress.com www.campaignpress.com;

    ssl_certificate /etc/letsencrypt/live/campaignpress.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/campaignpress.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
}
```

### Application Security

**Environment Variables:**
```php
// Never commit these to git
// Use .env file or wp-config.php
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('AUTH_KEY', getenv('AUTH_KEY'));
define('CAMPAIGNPRESS_LICENSE_KEY', getenv('LICENSE_KEY'));
```

**Rate Limiting (Nginx):**
```nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

location /wp-json/campaignpress/ {
    limit_req zone=api burst=20 nodelay;
}
```

**Fail2Ban:**
```bash
# Install
apt-get install fail2ban

# Configure for WordPress
cat > /etc/fail2ban/filter.d/wordpress.conf << EOF
[Definition]
failregex = ^<HOST> .* "POST .*wp-login.php
ignoreregex =
EOF

cat > /etc/fail2ban/jail.local << EOF
[wordpress]
enabled = true
filter = wordpress
logpath = /var/log/nginx/access.log
maxretry = 3
bantime = 3600
EOF

# Restart
systemctl restart fail2ban
```

---

## Monitoring & Maintenance

### Monitoring

**Uptime Monitoring:**
- UptimeRobot (free tier available)
- Pingdom
- StatusCake

**Error Tracking:**
- Sentry (error tracking)
- Rollbar
- New Relic (APM)

**Analytics:**
- Google Analytics 4
- Matomo (self-hosted)
- Plausible (privacy-focused)

**WordPress Monitoring:**
```bash
# Install ManageWP Worker plugin
wp plugin install worker --activate

# Or MainWP Dashboard for self-hosted
```

### Backup Strategy

**Automated Backups:**
```bash
# Database backup
wp db export /backups/db-$(date +%Y%m%d).sql
gzip /backups/db-$(date +%Y%m%d).sql

# Files backup
tar -czf /backups/files-$(date +%Y%m%d).tar.gz /var/www/html

# Automate with cron
crontab -e
# Add: 0 2 * * * /path/to/backup-script.sh
```

**Backup Retention:**
- Daily backups: Keep 7 days
- Weekly backups: Keep 4 weeks
- Monthly backups: Keep 12 months

**Off-Site Storage:**
- AWS S3
- Backblaze B2
- Google Cloud Storage
- UpdraftPlus plugin with remote storage

### Maintenance Tasks

**Daily:**
- Monitor uptime
- Check error logs
- Review analytics

**Weekly:**
- Update plugins/themes
- Check backups
- Review security logs
- Clean spam comments

**Monthly:**
- Update WordPress core
- Review performance metrics
- Database optimization
- Security scan
- Broken link check

**Quarterly:**
- Full security audit
- Performance audit
- Content review
- SEO audit
- Accessibility check

---

## Troubleshooting

### Common Issues

**White Screen of Death:**
```bash
# Enable debug mode
# Add to wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

# Check debug.log
tail -f /var/www/html/wp-content/debug.log

# Disable plugins
wp plugin deactivate --all

# Switch to default theme
wp theme activate twentytwentyfour
```

**Database Connection Error:**
```bash
# Verify credentials
wp config get DB_NAME
wp config get DB_USER

# Test connection
mysql -u username -p databasename

# Repair database
wp db repair
```

**500 Internal Server Error:**
```bash
# Check PHP error log
tail -f /var/log/php-fpm/error.log

# Check Nginx error log
tail -f /var/log/nginx/error.log

# Increase PHP memory
# wp-config.php:
define('WP_MEMORY_LIMIT', '512M');
```

**Slow Performance:**
```bash
# Enable query monitor plugin
wp plugin install query-monitor --activate

# Check slow queries
wp db query "SHOW PROCESSLIST;"

# Clear all caches
wp cache flush
wp transient delete --all
wp rewrite flush
```

**Premium Features Not Working:**
```bash
# Check license
# Admin → CampaignPress Pro → License

# Verify file exists
ls includes/premium/premium-init.php

# Check PHP errors
tail -f wp-content/debug.log | grep -i premium
```

### Emergency Procedures

**Site Compromised:**
1. Take site offline (maintenance mode)
2. Change all passwords (WordPress, database, hosting, FTP)
3. Scan for malware (Wordfence, Sucuri)
4. Restore from clean backup
5. Update all software
6. Review access logs
7. Bring site back online
8. Monitor closely

**Data Loss:**
1. Stop all write operations
2. Identify scope of loss
3. Check available backups
4. Restore from most recent clean backup
5. Verify data integrity
6. Resume operations
7. Implement better backup strategy

**Performance Crisis:**
1. Enable caching immediately
2. Disable expensive plugins temporarily
3. Increase server resources if possible
4. Implement CDN
5. Optimize database
6. Review slow query log
7. Scale horizontally if needed

---

## Launch Day Checklist

### T-24 Hours

- [ ] Final backup created
- [ ] All tests passing
- [ ] Staging environment matches production
- [ ] DNS records ready (if switching)
- [ ] SSL certificate installed
- [ ] CDN configured
- [ ] Monitoring enabled
- [ ] Team notified

### T-1 Hour

- [ ] Maintenance mode enabled
- [ ] Final database backup
- [ ] Deploy code
- [ ] Run database migrations
- [ ] Clear all caches
- [ ] Test critical paths
- [ ] Verify SSL working
- [ ] Check forms/payments

### T-0 (Launch)

- [ ] Disable maintenance mode
- [ ] Test homepage
- [ ] Test donation flow
- [ ] Test contact form
- [ ] Verify analytics tracking
- [ ] Check mobile responsiveness
- [ ] Monitor error logs
- [ ] Announce launch

### T+1 Hour

- [ ] No critical errors
- [ ] Performance acceptable
- [ ] Forms working
- [ ] Payments processing
- [ ] Monitoring stable
- [ ] Team debriefing

---

## Resources

**Tools:**
- [GTmetrix](https://gtmetrix.com/) - Performance testing
- [WebPageTest](https://www.webpagetest.org/) - Performance analysis
- [SSL Labs](https://www.ssllabs.com/) - SSL testing
- [SecurityHeaders.com](https://securityheaders.com/) - Security headers check
- [WAVE](https://wave.webaim.org/) - Accessibility testing

**Documentation:**
- [WordPress.org Hardening](https://wordpress.org/support/article/hardening-wordpress/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Let's Encrypt Docs](https://letsencrypt.org/docs/)

---

**Last Updated:** December 28, 2025
**Version:** 2.0.0
