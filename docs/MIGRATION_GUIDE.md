# Migration Guide: Theme to Theme + Plugin

**Migrating from Campaign Office 1.x to 2.0 Architecture**

This guide helps you migrate from the all-in-one theme (v1.x) to the new theme + plugin architecture (v2.0).

---

## 🎯 What's Changing?

Campaign Office is splitting into two parts:

**Before (v1.x)**:
```
Everything in one theme
├── Templates
├── Styles
├── Custom Post Types
├── Volunteer Management
├── Event Management
└── All Features
```

**After (v2.0)**:
```
Campaign Office THEME           Campaign Office Core PLUGIN
├── Templates                   ├── Custom Post Types
├── Styles                      ├── Volunteer Management
├── Block Views                 ├── Event Management
└── Customizer Options          ├── Contact Management
                                └── Data Management
```

---

## ✅ Benefits of Migrating

- ✅ **Keep your data** when switching themes
- ✅ **WordPress.org compliant** - eligible for official directories
- ✅ **Better updates** - theme and plugin can update independently
- ✅ **More flexible** - use with any theme
- ✅ **Professional architecture** - follows WordPress best practices

---

## 📋 Migration Process

### Step 1: Backup Everything

```bash
# Backup your database
wp db export backup-$(date +%Y%m%d).sql

# Backup your theme
cd wp-content/themes
tar -czf campaign-office-backup-$(date +%Y%m%d).tar.gz campaign-office/

# Backup your uploads
tar -czf uploads-backup-$(date +%Y%m%d).tar.gz ../uploads/
```

**Or use a WordPress backup plugin:**
- UpdraftPlus
- BackWPup
- Duplicator

### Step 2: Install Campaign Office Core Plugin

**Option A: Via WordPress Admin**
1. Go to Plugins → Add New
2. Search for "Campaign Office Core"
3. Click "Install Now"
4. Click "Activate"

**Option B: Manual Installation**
1. Download `campaign-office-core.zip`
2. Go to Plugins → Add New → Upload Plugin
3. Choose the ZIP file
4. Click "Install Now" then "Activate"

**Option C: Via WP-CLI**
```bash
wp plugin install campaign-office-core --activate
```

### Step 3: Update Campaign Office Theme

**Option A: Via WordPress Admin**
1. Go to Appearance → Themes
2. Find Campaign Office theme
3. Click "Update" if available

**Option B: Manual Update**
1. Download the latest Campaign Office theme
2. Deactivate the old theme
3. Delete the old theme folder
4. Upload the new theme
5. Activate it

**Option C: Via WP-CLI**
```bash
wp theme update campaign-office
```

### Step 4: Verify Migration

After activation, check:

- [ ] All content types are visible (Issues, Events, Volunteers)
- [ ] Existing content is intact
- [ ] Admin menus work correctly
- [ ] Volunteer portal is accessible
- [ ] Event RSVP works
- [ ] No PHP errors in debug log

---

## 🔍 What Happens to My Data?

### ✅ Safe (Data Preserved)

All your data remains in WordPress database:
- ✅ Volunteers
- ✅ Events
- ✅ Issues
- ✅ Endorsements
- ✅ Team members
- ✅ RSVPs
- ✅ Volunteer hours
- ✅ All custom fields

### 📁 File Locations Don't Change

- Template files stay in theme
- Custom post type data stays in database
- Media files stay in uploads folder

---

## ⚠️ Potential Issues & Solutions

### Issue: "Custom post types not showing"

**Cause**: Plugin not activated
**Solution**:
```bash
wp plugin activate campaign-office-core
wp rewrite flush
```

### Issue: "404 errors on custom post type pages"

**Cause**: Rewrite rules not flushed
**Solution**:
1. Go to Settings → Permalinks
2. Click "Save Changes" (don't change anything)
3. Or run: `wp rewrite flush`

### Issue: "Volunteer portal not found"

**Cause**: Page needs to be recreated
**Solution**:
1. Go to Pages → Add New
2. Create page titled "Volunteer Portal"
3. Add shortcode: `[campaign_office_volunteer_portal]`
4. Publish

### Issue: "Admin menus duplicated"

**Cause**: Both old and new versions running
**Solution**:
1. Check Plugins page
2. Deactivate any old Campaign Office functionality plugins
3. Keep only "Campaign Office Core" active

### Issue: "Styling looks different"

**Cause**: Theme updated with new styles
**Solution**:
1. Go to Appearance → Customize
2. Review and update color schemes
3. Check typography settings
4. May need to re-save customizer options

---

## 🧪 Testing Your Migration

Run through this checklist after migration:

### Content Checks
- [ ] Navigate to Issues → All Issues
- [ ] Create a new issue and verify it saves
- [ ] Navigate to Events → All Events
- [ ] Create a new event and verify RSVP works
- [ ] Navigate to Volunteers → All Volunteers
- [ ] Check existing volunteer data is intact

### Frontend Checks
- [ ] Visit homepage - loads correctly
- [ ] Visit /issues - archive displays
- [ ] Visit /events - event list shows
- [ ] Visit /volunteer-portal - portal works
- [ ] Test volunteer registration
- [ ] Test event RSVP

### Admin Checks
- [ ] Check for PHP errors in debug.log
- [ ] Verify all admin menus appear
- [ ] Test creating/editing content
- [ ] Check settings pages work
- [ ] Verify dashboard widgets load

### Performance Checks
- [ ] Run speed test (GTmetrix, Pingdom)
- [ ] Check page load times
- [ ] Verify no JavaScript errors (browser console)
- [ ] Test on mobile devices

---

## 🔄 Rollback Plan

If you encounter issues, you can rollback:

### Quick Rollback

1. **Deactivate plugin**:
   ```bash
   wp plugin deactivate campaign-office-core
   ```

2. **Downgrade theme** (if you have backup):
   ```bash
   cd wp-content/themes
   rm -rf campaign-office
   tar -xzf campaign-office-backup-YYYYMMDD.tar.gz
   ```

3. **Restore database** (if needed):
   ```bash
   wp db import backup-YYYYMMDD.sql
   ```

### Full Rollback

1. Restore entire site from backup
2. Contact support with error details
3. Wait for fix before trying again

---

## 📊 Database Changes

The migration does NOT change your database structure. It only changes WHERE the code lives:

```
Before: Theme registers CPTs
After:  Plugin registers CPTs (same database structure)
```

**Database remains identical:**
- Same post types (`cp_event`, `cp_volunteer`, etc.)
- Same post meta keys
- Same taxonomy terms
- Same table structure

---

## 🚀 Post-Migration Optimization

After successful migration, optimize:

### 1. Clear All Caches

```bash
# WordPress object cache
wp cache flush

# Rewrite rules
wp rewrite flush

# If using caching plugin
wp w3-total-cache flush all  # W3 Total Cache
wp rocket clean --all        # WP Rocket
```

### 2. Update Search Index

If using a search plugin:
```bash
wp searchindex rebuild  # Relevanssi
wp elasticpress index   # ElasticPress
```

### 3. Regenerate Permalinks

```bash
wp rewrite structure '/%postname%/' --hard
wp rewrite flush
```

### 4. Update Sitemaps

If using SEO plugin:
```bash
wp yoast sitemap generate  # Yoast SEO
```

---

## 💡 Frequently Asked Questions

### Q: Will I lose my volunteer data?

**A**: No. All data remains in the WordPress database. The plugin just provides the interface to access it.

### Q: Do I need both theme and plugin?

**A**: For full functionality, yes. However:
- Plugin works with any theme
- Theme works without plugin (with reduced features)

### Q: Can I use a different theme?

**A**: Yes! The plugin works with any WordPress theme. You may need to style it to match.

### Q: What about premium features?

**A**: Premium features will be moved to a separate "Campaign Office Pro" plugin in the future.

### Q: Will automatic updates work?

**A**: Yes, both theme and plugin will update through WordPress admin as usual.

### Q: How do I migrate from another campaign theme?

**A**: That requires custom migration. Contact support for assistance.

---

## 📞 Getting Help

If you encounter issues during migration:

1. **Check Debug Log**: Enable WP_DEBUG in wp-config.php
2. **Test Plugin**: Deactivate other plugins temporarily
3. **Test Theme**: Try a default theme (Twenty Twenty-Four)
4. **Documentation**: Read theme and plugin docs
5. **Support**: Contact via:
   - Support forum
   - GitHub issues
   - Email support

---

## 📝 Migration Checklist

Print this checklist and mark off as you complete each step:

### Pre-Migration
- [ ] Backup database
- [ ] Backup theme files
- [ ] Backup uploads folder
- [ ] Document current settings
- [ ] Screenshot important pages
- [ ] Test on staging site first (if available)

### Migration
- [ ] Install Campaign Office Core plugin
- [ ] Activate plugin
- [ ] Update Campaign Office theme
- [ ] Flush permalinks
- [ ] Clear all caches

### Post-Migration
- [ ] Verify all content visible
- [ ] Test volunteer registration
- [ ] Test event RSVP
- [ ] Check admin interfaces
- [ ] Test on mobile
- [ ] Run speed test
- [ ] Check for errors
- [ ] Update documentation

### Cleanup
- [ ] Delete old backups (after confirming success)
- [ ] Update any custom code
- [ ] Inform team members of changes
- [ ] Update internal documentation

---

## 🎓 Next Steps

After successful migration:

1. **Explore New Features**: Check plugin settings for new options
2. **Optimize Integration**: Read the [Architecture Guide](THEME_PLUGIN_ARCHITECTURE.md)
3. **Customize**: Use hooks and filters for customization
4. **Stay Updated**: Subscribe to update notifications

---

## 📚 Additional Resources

- [Theme + Plugin Architecture](THEME_PLUGIN_ARCHITECTURE.md)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)
- [Campaign Office Documentation](https://campaignoffice.com/docs)

---

**Migration Support**: support@campaignoffice.com
**Last Updated**: 2025-12-29
**Migration Version**: 1.x → 2.0
