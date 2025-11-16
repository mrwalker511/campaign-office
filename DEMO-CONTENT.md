# CampaignPress Demo Content Guide

## Overview

CampaignPress includes a comprehensive demo content system that allows you to quickly populate your site with sample campaign data. This is perfect for:

- **Testing** the theme's features before adding real content
- **Demonstrations** to show clients or stakeholders
- **Learning** how to structure your campaign content
- **Development** when building custom features

## What's Included

The demo content system creates:

### Policy Issues (6 items)
- Universal Healthcare
- Quality Public Education
- Climate Action & Clean Energy
- Economic Opportunity for All
- Criminal Justice Reform
- Infrastructure & Transportation

Each issue includes detailed policy positions with HTML formatting.

### Campaign Events (4 items)
- Community Town Hall - Healthcare Discussion
- Grassroots Fundraiser BBQ
- Get Out The Vote Rally
- Candidate Debate - Community Issues

Events include full metadata: date, time, location, address, city, state, ZIP, and RSVP link.

### Endorsements (8 items)
- Mayor Jennifer Williams
- Springfield Teachers Association
- Dr. Robert Chen - Community Health Director
- Small Business Coalition of Springfield
- Environmental Action League
- Police Chief (Ret.) Marcus Johnson
- Young Democrats of Springfield
- Rev. Sarah Martinez - Faith Leaders Coalition

### Team Members (5 items)
- Sarah Johnson - Campaign Manager
- Michael Torres - Finance Director
- Emily Washington - Communications Director
- David Kim - Field Director
- Maria Rodriguez - Policy Director

Each team member includes a detailed bio.

### Volunteer Opportunities (4 items)
- Door-to-Door Canvassing
- Phone Banking
- Event Support & Logistics
- Social Media & Digital Outreach

### Sample Pages (2 pages)
- **Sample Homepage** - Demonstrates all CampaignPress custom blocks:
  - Donation Button
  - Campaign Progress Meter
  - Event Countdown
  - Issue Cards
  - Volunteer CTA

- **About Page** - Sample candidate biography with values and platform

## How to Import Demo Content

### Method 1: Via Admin Interface (Recommended)

1. Log into WordPress admin
2. Go to **Appearance → Demo Content**
3. Click the **"Import Demo Content"** button
4. Wait for the import to complete (takes 5-10 seconds)
5. You'll see a success message when finished

### Method 2: Via Admin Notice

When you first activate CampaignPress, you'll see a welcome notice with a link to import demo content. Just click the button!

## Viewing Demo Content

After import, you can view the demo content in several ways:

### Custom Post Types
- **Issues** → Go to **Issues** in the admin menu
- **Events** → Go to **Events** in the admin menu
- **Endorsements** → Go to **Endorsements** in the admin menu
- **Team** → Go to **Team** in the admin menu
- **Volunteer Opportunities** → Go to **Volunteer Opportunities** in the admin menu

### Sample Pages
- Go to **Pages** → **All Pages**
- Look for pages titled:
  - "Sample Homepage - CampaignPress Demo"
  - "About - Sample Bio"

### Frontend Display
Create a menu and add the demo pages, or set the Sample Homepage as your front page:

1. Go to **Settings → Reading**
2. Select "A static page" for your homepage
3. Choose "Sample Homepage - CampaignPress Demo"
4. Save changes

## Working with Demo Content

### Editing Demo Content
All demo content is regular WordPress content - you can edit it just like any other post or page:

1. Go to the relevant admin section (Issues, Events, etc.)
2. Click on the item you want to edit
3. Make your changes
4. Click **Update** to save

### Using Demo Content as Templates
The demo content is designed to serve as templates for your real content:

1. Open a demo item in the editor
2. Study the formatting and structure
3. Create a new item using the same pattern
4. Replace the demo content with your real campaign information

### Featured Images
Demo content does not include featured images (to keep the download size small). You can add your own:

1. Edit any demo post
2. Click "Set featured image" in the right sidebar
3. Upload or select an image
4. Click "Set featured image"

## Deleting Demo Content

When you're ready to remove the demo content:

### Complete Removal

1. Go to **Appearance → Demo Content**
2. Click the **"Delete Demo Content"** button
3. Confirm the deletion
4. All demo posts and pages will be permanently removed

**Important:** This action cannot be undone. Make sure you've created your real content before deleting the demo!

### Selective Deletion

If you want to keep some demo items:

1. Go to the relevant admin section (Issues, Events, etc.)
2. Select the items you want to delete using checkboxes
3. Choose "Move to Trash" from the Bulk Actions dropdown
4. Click Apply

## Demo Content Data Structure

### Issues
- **Post Type:** `cp_issue`
- **Taxonomy:** `issue_category`
- **Categories:** Healthcare, Education, Environment, Economy, Justice, Infrastructure

### Events
- **Post Type:** `cp_event`
- **Taxonomy:** `event_type`
- **Types:** Town Hall, Fundraiser, Rally, Debate
- **Meta Fields:**
  - `_cp_event_date` (Y-m-d format)
  - `_cp_event_time` (H:i format)
  - `_cp_event_location`
  - `_cp_event_address`
  - `_cp_event_city`
  - `_cp_event_state`
  - `_cp_event_zip`
  - `_cp_event_rsvp_link`

### Endorsements
- **Post Type:** `cp_endorsement`
- **Content:** Endorser name as title, quote as content

### Team Members
- **Post Type:** `cp_team`
- **Content:** Name and role as title, bio as content

### Volunteer Opportunities
- **Post Type:** `cp_volunteer`
- **Content:** Role as title, description as content, summary as excerpt

## Customizing Demo Content

### Before Import
You can customize what gets imported by editing:
```
/includes/free/demo-content.php
```

Look for the `import_*()` methods and modify the arrays with your preferred content.

### After Import
Simply edit the posts like any other WordPress content through the admin interface.

## Troubleshooting

### Import Button Not Working
- Make sure you're logged in as an administrator
- Check that JavaScript is enabled in your browser
- Try refreshing the page

### Demo Content Not Appearing
- Go to the relevant admin section (Issues, Events, etc.)
- Make sure "All" is selected in the filter
- Check that posts aren't in the trash

### Can't Delete Demo Content
- Make sure you're logged in as an administrator
- Try deleting items individually through their admin sections
- Check that you confirmed the deletion prompt

### Duplicate Content After Re-importing
The demo importer creates new posts each time you import. If you've already imported and want to start fresh:

1. Delete existing demo content first
2. Then import again

## Best Practices

### For Testing
- Import demo content immediately after theme activation
- Explore all custom post types
- Test the Gutenberg blocks on sample pages
- Review how content is displayed on the frontend

### For Development
- Use demo content to test custom features
- Keep demo content separate from real content
- Delete and re-import to test fresh installations

### For Client Demos
- Import demo content before showing the theme
- Customize the candidate name in Customizer settings
- Add a few featured images to make it more realistic
- Walk through the admin interface to show content management

### Before Going Live
- **Always delete demo content** before launching the real campaign
- Replace with real campaign information
- Double-check that no demo pages are set as homepage
- Remove demo content from any menus you created

## Next Steps

After importing demo content:

1. **Explore the Admin** - Check out all the custom post types
2. **View the Frontend** - See how content displays on your site
3. **Customize the Theme** - Go to Appearance → Customize
4. **Add Your Branding** - Upload logo, set colors, add social links
5. **Create Real Content** - Start building your actual campaign pages
6. **Delete Demo Content** - Remove sample data when ready

## Support

If you have questions about the demo content system:

- Check the main [README.md](README.md) for general theme documentation
- Visit the CampaignPress support forum
- Review the code in `/includes/free/demo-content.php`

---

**Remember:** Demo content is for testing and demonstration only. Always delete it before launching your real campaign!
