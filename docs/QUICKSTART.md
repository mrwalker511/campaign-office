# CampaignPress Quick Start Guide

Get your political campaign website up and running in **5 minutes**!

## 📋 Prerequisites

- WordPress 6.4 or higher
- PHP 7.4 or higher
- Fresh WordPress installation recommended

## 🚀 Installation

### Method 1: WordPress Admin (Recommended)

1. **Log in** to your WordPress admin panel
2. Navigate to **Appearance → Themes**
3. Click **Add New → Upload Theme**
4. Choose the `campaign-office.zip` file
5. Click **Install Now**
6. Click **Activate**

### Method 2: FTP/File Manager

1. Extract the `campaign-office.zip` file
2. Upload the `campaign-office` folder to `/wp-content/themes/`
3. Go to **Appearance → Themes** in WordPress admin
4. Click **Activate** on CampaignPress

## ⚡ 5-Minute Setup

### Step 1: Import Demo Content (Optional but Recommended)

1. Go to **Appearance → Demo Content**
2. Click **Import Demo Content**
3. Wait for the import to complete (~30 seconds)
4. You now have sample issues, events, team members, and pages!

### Step 2: Choose Your Color Scheme

1. Go to **CampaignPress → Design** in the admin menu
2. Select your party affiliation or preferred color scheme:
   - **Democrat Blue** (default)
   - **Republican Red**
   - **Independent Purple**
   - **Green Party**
   - **Neutral** (non-partisan)
3. Click **Save Changes**

### Step 3: Upload Your Campaign Logo

1. Go to **Appearance → Customize**
2. Click **Site Identity**
3. Click **Select Logo**
4. Upload your campaign logo (recommended: 300x100px PNG with transparency)
5. Click **Publish**

### Step 4: Set Up Navigation Menus

1. Go to **Appearance → Menus**
2. Create a menu named "Primary Navigation"
3. Add pages to your menu:
   - Home
   - Issues
   - Events
   - Team
   - Endorsements
   - Volunteer
   - Donate
   - Contact
4. Assign to **Primary Menu** location
5. Click **Save Menu**

### Step 5: Configure Homepage

1. Go to **Settings → Reading**
2. Select **A static page**
3. Choose your homepage from the dropdown
4. Choose a blog page (or create one)
5. Click **Save Changes**

## 🎨 Customization

### Add Your First Issue

1. Go to **Issues → Add New**
2. Enter the issue title (e.g., "Education Reform")
3. Write your policy position
4. Add a featured image (recommended: 1200x600px)
5. Click **Publish**

### Add Your First Event

1. Go to **Events → Add New**
2. Enter event title
3. Scroll down to **Event Details** meta box
4. Fill in:
   - Event Date & Time
   - Location/Address
   - RSVP Link (optional)
5. Click **Publish**

### Add Team Members

1. Go to **Team → Add New**
2. Enter team member's name
3. Add bio in the content area
4. Upload headshot (recommended: 400x400px square)
5. Scroll to **Team Member Details**:
   - Position/Title
   - Email (optional)
   - Phone (optional)
   - Social Links (optional)
6. Click **Publish**

### Add Endorsements

1. Go to **Endorsements → Add New**
2. Enter endorser's name or organization
3. Add endorsement quote in content
4. Upload photo/logo (recommended: 150x150px)
5. Click **Publish**

### Add Volunteer Opportunities

1. Go to **Volunteer → Add New**
2. Enter opportunity title (e.g., "Phone Banking")
3. Describe the role and requirements
4. Add sign-up link if available
5. Click **Publish**

## 🛠️ Theme Options

Access all theme settings at **CampaignPress** in your admin menu.

### General Settings
- Site tagline/slogan
- Contact information
- Social media profiles
- Google Analytics ID

### Design Settings
- Color scheme selection
- Custom primary color
- Custom accent color
- Logo width adjustment
- Typography settings

### Homepage Settings
- Hero section options
- Featured content
- Call-to-action buttons

### Footer Settings
- Footer text/copyright
- Footer widget areas
- Footer menu

## 📱 Widgets

Add widgets to your sidebar and footer:

1. Go to **Appearance → Widgets**
2. Find available widget areas:
   - **Main Sidebar** (pages & posts)
   - **Footer 1, 2, 3** (three footer columns)
3. Drag widgets into areas
4. Configure and save

### Recommended Widgets
- **Recent Events** (custom widget)
- **Volunteer Sign-Up** (custom widget)
- **Donate Button** (custom widget)
- **Search**
- **Recent Posts**
- **Categories**

## 🎯 Campaign Essentials Checklist

### Content
- [ ] Upload campaign logo
- [ ] Write campaign bio/about page
- [ ] Add 3-5 key issues with positions
- [ ] Add upcoming events
- [ ] Add team members with photos
- [ ] Add endorsements
- [ ] Create volunteer opportunities

### Design
- [ ] Select color scheme
- [ ] Customize primary/accent colors (optional)
- [ ] Upload hero image for homepage
- [ ] Add favicon (Customize → Site Identity)

### Settings
- [ ] Set up navigation menu
- [ ] Configure homepage
- [ ] Add social media links
- [ ] Set up contact information
- [ ] Add Google Analytics (optional)

### Pages
- [ ] Create About page
- [ ] Create Contact page
- [ ] Create Donate page
- [ ] Create Privacy Policy page
- [ ] Create Press/Media page (optional)

### SEO & Performance
- [ ] Install Yoast SEO or Rank Math plugin
- [ ] Create XML sitemap
- [ ] Submit sitemap to Google Search Console
- [ ] Install caching plugin (WP Rocket, W3 Total Cache)
- [ ] Optimize images (ShortPixel, Imagify)

## 🔧 Advanced Configuration

### Custom Color Scheme

If the preset color schemes don't match your needs:

1. Go to **CampaignPress → Design**
2. Scroll to **Custom Colors**
3. Set your custom primary color
4. Set your custom accent color
5. Click **Save Changes**

### Homepage Layout Options

Choose from multiple homepage templates:

1. Edit your homepage
2. In the right sidebar, click **Template**
3. Choose from:
   - **Default Homepage** (standard layout)
   - **Political Home (Block)** (block-based)
   - **Home - Hero Video** (video background)
   - **Home - Split Screen** (modern split design)
   - **Home - Grassroots** (community-focused)
   - **Home - Issues First** (policy-centered)

### Page Layout Options

Control sidebar on individual pages:

1. Edit any page or post
2. Find **Layout Options** meta box
3. Choose:
   - **Default** (use theme setting)
   - **Sidebar Right**
   - **Sidebar Left**
   - **No Sidebar** (full width)

### Block Editor Patterns

CampaignPress includes custom block patterns:

1. Edit a page in the block editor
2. Click the **+** (Add block) button
3. Click the **Patterns** tab
4. Browse **CampaignPress** category for:
   - Hero sections
   - Call-to-action blocks
   - Testimonial layouts
   - Event listings
   - Team member grids
   - Donation forms

## 🆘 Troubleshooting

### Theme looks broken / fonts not loading
**Solution:** The theme uses self-hosted fonts. Download font files from Google Fonts and place them in `/assets/fonts/`. See `/assets/fonts/README.md` for detailed instructions.

### Demo content won't import
**Solution:**
1. Increase PHP memory limit to at least 256MB
2. Increase PHP max execution time to 300 seconds
3. Try importing again

### Permalink 404 errors (Events, Issues not found)
**Solution:**
1. Go to **Settings → Permalinks**
2. Click **Save Changes** (don't change anything)
3. This flushes rewrite rules and fixes 404s

### Color scheme not changing
**Solution:**
1. Clear your browser cache (Ctrl+Shift+Delete)
2. Clear WordPress cache if using a caching plugin
3. Try in an incognito/private browser window

### Images are huge / slow loading
**Solution:**
1. Install **Regenerate Thumbnails** plugin
2. Run it to create proper image sizes
3. Install an image optimization plugin (ShortPixel, Imagify)

## 🌟 Pro Tips

### 1. Use Block Patterns for Faster Design
Instead of building layouts from scratch, use the pre-built patterns in the **Patterns** tab.

### 2. Set Featured Images
Always set a featured image on posts, issues, events, and team members for better visual appeal.

### 3. Keep Events Updated
Regularly add new events and remove past events to keep your site fresh and active.

### 4. Leverage Social Proof
Add endorsements from well-known figures or organizations to build credibility.

### 5. Mobile-First Testing
70% of political website traffic is mobile. Test your site on phone and tablet before launch.

### 6. Create Urgency
Use countdown blocks for events and donation deadlines to drive action.

### 7. A/B Test Donation CTAs
Try different button text, colors, and placements to optimize donations.

## 📚 Next Steps

- **Read Full Documentation:** [campaignpress.com/docs](https://campaignpress.com/docs)
- **Watch Video Tutorials:** [campaignpress.com/videos](https://campaignpress.com/videos)
- **Get Support:** [campaignpress.com/support](https://campaignpress.com/support)
- **Join Community:** [facebook.com/groups/campaignpress](https://facebook.com/groups/campaignpress)

## 🔒 Security Checklist

Before going live:
- [ ] Update WordPress to latest version
- [ ] Update all plugins to latest versions
- [ ] Install a security plugin (Wordfence, Sucuri)
- [ ] Enable SSL certificate (HTTPS)
- [ ] Set strong admin passwords
- [ ] Limit login attempts
- [ ] Regular backups (UpdraftPlus, BackupBuddy)
- [ ] Remove default "admin" username
- [ ] Enable two-factor authentication

## 🎉 You're Ready to Launch!

Your campaign website is now set up and ready to help you win!

Need help? Contact support at [support@campaignpress.com](mailto:support@campaignpress.com)

---

**Last Updated:** 2025-01-08
**Version:** 2.0.0
**Questions?** Check out [README.md](README.md) for more detailed information.
