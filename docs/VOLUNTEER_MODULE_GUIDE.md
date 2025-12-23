# Volunteer Management Module - User Guide

**Version:** 2.0.0
**Last Updated:** 2024-12-23

---

## Table of Contents

1. [Overview](#overview)
2. [Free Version Features](#free-version-features)
3. [Premium Version Features](#premium-version-features)
4. [Installation & Setup](#installation--setup)
5. [Using Volunteer Forms](#using-volunteer-forms)
6. [Managing Volunteers](#managing-volunteers)
7. [Volunteer Portal](#volunteer-portal)
8. [Shortcodes Reference](#shortcodes-reference)
9. [Hooks & Filters](#hooks--filters)
10. [Database Schema](#database-schema)
11. [Troubleshooting](#troubleshooting)

---

## Overview

The Volunteer Management module provides a complete system for recruiting, organizing, and mobilizing volunteers for political campaigns. It includes both free and premium features to match your campaign's needs.

### Key Capabilities

- ✅ **Volunteer Signups** - Comprehensive form with contact info, skills, interests, and availability
- ✅ **Admin Dashboard** - Manage volunteers with filtering, search, and bulk actions
- ✅ **Self-Service Portal** - Volunteers can manage their own profiles and assignments
- ✅ **Shift Scheduling** - Create shifts with capacity limits and signup tracking
- ✅ **Hours Tracking** - Log and verify volunteer hours
- ✅ **Leaderboards** - Recognize top performers with rankings
- ✅ **CSV Export** - Export volunteer data for external tools
- ✅ **Integration Hooks** - Connect to email, SMS, and CRM systems

---

## Free Version Features

### 1. Volunteer Signup Form

Comprehensive volunteer registration form capturing:

**Contact Information:**
- First name and last name (required)
- Email address (required)
- Phone number
- Street address
- City, State, ZIP

**Volunteer Preferences:**
- Interests (checkboxes):
  - Door-to-door canvassing
  - Phone banking
  - Event support
  - Data entry
  - Social media outreach
  - Fundraising
- Availability (checkboxes):
  - Weekday mornings
  - Weekday afternoons
  - Weekday evenings
  - Weekends
- Skills and experience (textarea)

**Technical Features:**
- AJAX-powered submission
- Real-time validation
- Success/error messages
- Loading states
- Nonce security verification

### 2. Admin Dashboard

Access: **Campaign Data → Signups**

**Features:**
- View all volunteer signups in a table
- Search by name, email, or phone
- Filter by status (All, New, Contacted, Active)
- Pagination (20 volunteers per page)
- Status badges with color coding
- Direct email links
- Individual delete actions
- Bulk actions:
  - Mark as Contacted
  - Mark as Active
  - Delete selected

**Table Columns:**
- Name (with row actions)
- Email
- Phone
- Location (City, State)
- Interests
- Status
- Date signed up

### 3. CSV Export

Export all volunteer data to CSV format for:
- Mailchimp imports
- Voter file integration
- CRM systems (NationBuilder, NGP VAN)
- Data analysis

**Location:** Campaign Data → Signups → Export to CSV button

### 4. Volunteer Portal

A self-service dashboard where volunteers can:
- View their personal statistics
- See upcoming shifts
- Browse available shifts
- Sign up for shifts
- Log volunteer hours
- Update their profile
- View their leaderboard rank

### 5. Leaderboard

Display top volunteers with rankings by hours and activity count.

**Features:**
- Configurable number of volunteers to display
- Time period filtering (all, this week, this month)
- Medal icons for top 3 positions
- Total hours and activities displayed

---

## Premium Version Features

### 1. Advanced Volunteer Scheduling

**Shift Management:**
- Create shifts with full details
- Set capacity limits
- Assign coordinators
- Recurring shift support (daily, weekly, bi-weekly, monthly)
- Shift types (canvassing, phone banking, events, etc.)
- Location management
- Required skills specification

**Volunteer Assignments:**
- Assign volunteers to shifts
- Track confirmation status
- Manage cancellations
- Track no-shows
- Send automated reminders

### 2. Check-In/Check-Out System

**Features:**
- Mobile-friendly check-in interface
- QR code support
- Geolocation verification
- Check-out with hours calculation
- Real-time attendance tracking
- No-show detection

**Access:** Via shortcode `[cp_volunteer_checkin]`

### 3. Hours Verification

**Workflow:**
- Volunteers submit hours
- Staff review and verify
- Track verification history
- Flag unverified hours
- Export verified hours for compliance

### 4. Availability Calendar

**Features:**
- Volunteers set availability
- Visual calendar interface
- Recurring availability
- Conflict detection
- Availability-based shift suggestions

**Access:** Via shortcode `[cp_volunteer_availability]`

### 5. Mobile Volunteer Interface

**Features:**
- Mobile-optimized interface
- Touch-friendly controls
- Offline data collection
- GPS location tracking
- Photo upload support

### 6. REST API

Full API access for:
- Volunteer data management
- Shift operations
- Hours tracking
- Scheduling automation
- Third-party integrations

**Endpoints:**
- `/wp-json/campaignpress/v1/volunteers`
- `/wp-json/campaignpress/v1/shifts`
- `/wp-json/campaignpress/v1/hours`

---

## Installation & Setup

### Automatic Setup

The volunteer module is automatically loaded when you activate the CampaignPress theme.

**Database tables are created automatically:**
1. `wp_cp_volunteers` - Volunteer records
2. `wp_cp_volunteer_shifts` - Shift schedules
3. `wp_cp_volunteer_hours` - Hours log
4. `wp_cp_volunteer_assignments` - Shift signups

### Manual Setup (if needed)

If tables don't create automatically, you can trigger table creation by:

1. Deactivating and reactivating the theme
2. Visiting **Appearance → CampaignPress Options**
3. Or using WP-CLI:
   ```bash
   wp theme activate campaignpress
   ```

### Verify Installation

Check that tables exist in your database:

```sql
SHOW TABLES LIKE 'wp_cp_volunteer%';
```

You should see:
- `wp_cp_volunteers`
- `wp_cp_volunteer_shifts`
- `wp_cp_volunteer_hours`
- `wp_cp_volunteer_assignments`

---

## Using Volunteer Forms

### Basic Volunteer Signup Form

Add a volunteer signup form to any page or post:

```php
// Shortcode
[cp_volunteer_form]
```

**Customizable attributes:**

```php
// With custom title
[cp_volunteer_form title="Join Our Campaign"]

// Custom button text
[cp_volunteer_form submit_text="Sign Up Now!"]

// Link to specific opportunity
[cp_volunteer_form opportunity_id="123"]

// Combined
[cp_volunteer_form title="Join Our Campaign" submit_text="Sign Up Now!" opportunity_id="123"]
```

### Using in Page Builder

1. Create a new page or post
2. Add a **Shortcode** block
3. Enter `[cp_volunteer_form]`
4. Customize with attributes as needed
5. Publish or update the page

### Using in Templates

For developers, use in template files:

```php
<?php echo do_shortcode('[cp_volunteer_form title="Join Our Campaign"]'); ?>
```

### Styling the Form

The form uses CSS classes you can customize:

```css
/* Main container */
.cp-volunteer-form-wrapper { }

/* Form rows */
.cp-volunteer-signup-form .cp-form-row { }

/* Form groups */
.cp-volunteer-signup-form .cp-form-group { }

/* Submit button */
.cp-volunteer-submit-btn { }

/* Success message */
.cp-success-message { }

/* Error message */
.cp-error-message { }
```

Add custom CSS in:
- **Appearance → Customize → Additional CSS**
- Or create a child theme

---

## Managing Volunteers

### Viewing Volunteers

1. Go to **Campaign Data → Signups**
2. Browse the volunteer list
3. Use filters to narrow down:
   - **All** - Show all volunteers
   - **New** - Show only new volunteers
   - **Contacted** - Show only contacted volunteers
   - **Active** - Show only active volunteers

### Searching Volunteers

1. Use the search box at the top right
2. Search by:
   - First name
   - Last name
   - Email address
3. Click **Search Volunteers**

### Updating Volunteer Status

**Individual Volunteer:**

1. Find the volunteer in the list
2. Click **Edit** (if available) or use quick actions
3. Change status in the edit screen
4. Save changes

**Bulk Update:**

1. Check the boxes next to volunteers
2. Select action from "Bulk Actions" dropdown:
   - Mark as Contacted
   - Mark as Active
   - Delete
3. Click **Apply**

### Exporting Volunteers

1. Go to **Campaign Data → Signups**
2. Click **Export to CSV** button
3. CSV file will download with all volunteer data

**CSV columns:**
- ID
- First Name
- Last Name
- Email
- Phone
- Address
- City
- State
- ZIP
- Skills
- Interests
- Availability
- Volunteer Type
- Status
- Notes
- Source
- Opportunity ID
- Created At
- Updated At

### Deleting Volunteers

**Individual:**
1. Find the volunteer
2. Click **Delete** in row actions
3. Confirm deletion

**Bulk:**
1. Select volunteers
2. Choose "Delete" from bulk actions
3. Confirm deletion

---

## Volunteer Portal

### Setting Up the Portal

1. Create a new page titled "Volunteer Portal"
2. Add the shortcode:
   ```php
   [cp_volunteer_portal]
   ```
3. Publish the page
4. Share URL with volunteers

### Volunteer Login

Volunteers log in using their email address:
- No password required (simplified login)
- Cookie-based authentication (30-day session)
- Access to personal dashboard

### Dashboard Features

**Stats Cards:**
- ⏰ Total Hours Logged
- 📅 Shifts Completed
- 🎯 Upcoming Shifts
- 🏆 Volunteer Rank

**Tabs:**

1. **Upcoming Shifts**
   - View scheduled shifts
   - See shift details
   - Check-in button

2. **Available Shifts**
   - Browse open shifts
   - View shift details
   - Sign up button
   - Capacity tracking

3. **Log Hours**
   - Activity type dropdown
   - Date picker
   - Hours input (0.5 - 24)
   - Notes field
   - Recent activity list

4. **Profile**
   - Edit personal information
   - Update contact details
   - Modify skills
   - Update availability

### Leaderboard

Display top volunteers on any page:

```php
// Default (top 10, all time)
[cp_volunteer_leaderboard]

// Custom limit
[cp_volunteer_leaderboard limit="20"]

// This week only
[cp_volunteer_leaderboard period="week"]

// This month only
[cp_volunteer_leaderboard period="month"]
```

---

## Shortcodes Reference

### `[cp_volunteer_form]`

Display volunteer signup form.

**Attributes:**
- `title` - Form heading (default: "Volunteer Sign Up")
- `submit_text` - Button text (default: "Sign Me Up!")
- `opportunity_id` - Link to specific opportunity ID

**Example:**
```php
[cp_volunteer_form title="Join Our Team" submit_text="Sign Up Now!"]
```

### `[cp_volunteer_portal]`

Display volunteer portal (auto-login if already logged in).

**Attributes:**
- `auto_login` - Automatically redirect to login if not logged in (default: "false")

**Example:**
```php
[cp_volunteer_portal auto_login="true"]
```

### `[cp_volunteer_login]`

Display standalone volunteer login form.

**Attributes:** None

**Example:**
```php
[cp_volunteer_login]
```

### `[cp_volunteer_dashboard]`

Display volunteer dashboard (requires logged-in volunteer).

**Attributes:** None

**Example:**
```php
[cp_volunteer_dashboard]
```

### `[cp_volunteer_leaderboard]`

Display volunteer leaderboard.

**Attributes:**
- `limit` - Number of volunteers to show (default: 10)
- `period` - Time period: "all", "week", "month" (default: "all")

**Example:**
```php
[cp_volunteer_leaderboard limit="15" period="month"]
```

### `[cp_volunteer_shifts]` (Premium)

Display shift calendar for signup.

**Attributes:**
- `limit` - Number of shifts to show (default: 20)
- `type` - Filter by shift type (default: "all")

**Example:**
```php
[cp_volunteer_shifts limit="10" type="canvassing"]
```

### `[cp_volunteer_checkin]` (Premium)

Display check-in/check-out interface.

**Attributes:** None

**Example:**
```php
[cp_volunteer_checkin]
```

### `[cp_volunteer_availability]` (Premium)

Display availability calendar form.

**Attributes:** None

**Example:**
```php
[cp_volunteer_availability]
```

---

## Hooks & Filters

### Actions

#### `cp_volunteer_signup_success`

Fired after a volunteer successfully signs up.

**Parameters:**
- `$volunteer_id` (int) - The ID of the new volunteer
- `$volunteer_data` (array) - Array of volunteer data

**Example Usage:**
```php
add_action('cp_volunteer_signup_success', 'my_custom_volunteer_notification', 10, 2);

function my_custom_volunteer_notification($volunteer_id, $volunteer_data) {
    // Send custom email
    wp_mail(
        $volunteer_data['email'],
        'Welcome to the campaign!',
        'Thank you for signing up! We\'ll be in touch soon.'
    );

    // Add to Mailchimp
    // Add to CRM
    // Send Slack notification
    // etc.
}
```

### Filters

#### `cp_volunteer_form_fields`

Filter the fields displayed in the volunteer signup form.

**Example:**
```php
add_filter('cp_volunteer_form_fields', 'my_custom_volunteer_fields');

function my_custom_volunteer_fields($fields) {
    // Add custom field
    $fields['referral_source'] = array(
        'type' => 'select',
        'label' => 'How did you hear about us?',
        'options' => array(
            'friend' => 'Friend',
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'other' => 'Other'
        )
    );

    return $fields;
}
```

#### `cp_volunteer_status_options`

Filter the available status options.

**Example:**
```php
add_filter('cp_volunteer_status_options', 'my_custom_status_options');

function my_custom_status_options($statuses) {
    return array(
        'new' => 'New',
        'contacted' => 'Contacted',
        'active' => 'Active',
        'inactive' => 'Inactive',  // Custom status
        'lead' => 'Lead'          // Custom status
    );
}
```

---

## Database Schema

### `wp_cp_volunteers`

Stores volunteer records.

```sql
CREATE TABLE wp_cp_volunteers (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20) DEFAULT NULL,
    address varchar(255) DEFAULT NULL,
    city varchar(100) DEFAULT NULL,
    state varchar(2) DEFAULT NULL,
    zip varchar(10) DEFAULT NULL,
    skills text DEFAULT NULL,
    interests text DEFAULT NULL,
    availability text DEFAULT NULL,
    volunteer_type varchar(50) DEFAULT 'general',
    status varchar(20) DEFAULT 'new',
    notes text DEFAULT NULL,
    source varchar(100) DEFAULT NULL,
    opportunity_id bigint(20) DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY email (email),
    KEY status (status),
    KEY created_at (created_at)
)
```

### `wp_cp_volunteer_shifts`

Stores shift schedules.

```sql
CREATE TABLE wp_cp_volunteer_shifts (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    description text DEFAULT NULL,
    shift_date date NOT NULL,
    start_time time NOT NULL,
    end_time time NOT NULL,
    location varchar(255) DEFAULT NULL,
    capacity int(11) DEFAULT 10,
    filled int(11) DEFAULT 0,
    shift_type varchar(50) DEFAULT 'general',
    coordinator_id bigint(20) DEFAULT NULL,
    status varchar(20) DEFAULT 'open',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY shift_date (shift_date),
    KEY status (status)
)
```

### `wp_cp_volunteer_hours`

Stores volunteer hours logs.

```sql
CREATE TABLE wp_cp_volunteer_hours (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    volunteer_id bigint(20) UNSIGNED NOT NULL,
    shift_id bigint(20) UNSIGNED DEFAULT NULL,
    activity varchar(255) NOT NULL,
    hours decimal(5,2) NOT NULL,
    activity_date date NOT NULL,
    notes text DEFAULT NULL,
    verified tinyint(1) DEFAULT 0,
    verified_by bigint(20) DEFAULT NULL,
    verified_at datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY volunteer_id (volunteer_id),
    KEY activity_date (activity_date),
    KEY verified (verified)
)
```

### `wp_cp_volunteer_assignments`

Stores shift signups.

```sql
CREATE TABLE wp_cp_volunteer_assignments (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    volunteer_id bigint(20) UNSIGNED NOT NULL,
    shift_id bigint(20) UNSIGNED NOT NULL,
    status varchar(20) DEFAULT 'confirmed',
    checked_in tinyint(1) DEFAULT 0,
    checked_in_at datetime DEFAULT NULL,
    checked_out_at datetime DEFAULT NULL,
    notes text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY volunteer_id (volunteer_id),
    KEY shift_id (shift_id),
    KEY status (status),
    UNIQUE KEY volunteer_shift (volunteer_id, shift_id)
)
```

---

## Troubleshooting

### Volunteer Form Not Submitting

**Problem:** Clicking submit doesn't send data.

**Solutions:**
1. Check browser console for JavaScript errors
2. Verify jQuery is loaded
3. Check for conflicting plugins
4. Clear browser cache
5. Verify admin-ajax.php is accessible

### Volunteer Portal Not Loading

**Problem:** Portal shows blank or error message.

**Solutions:**
1. Verify volunteer tables exist in database
2. Check PHP error logs
3. Deactivate conflicting plugins
4. Ensure theme is updated
5. Check browser console for errors

### CSV Export Not Working

**Problem:** Clicking export doesn't download file.

**Solutions:**
1. Check file permissions
2. Verify export nonce is valid
3. Check PHP memory limit
4. Ensure output buffering is not interfering
5. Try in incognito/private mode

### Volunteer Not Receiving Emails

**Problem:** Volunteers signup but don't get confirmation emails.

**Solutions:**
1. Check WordPress email settings
2. Verify `wp_mail()` is working
3. Check spam folders
4. Configure SMTP plugin if needed
5. Add hooks for custom email functionality

### Database Tables Not Created

**Problem:** Volunteer tables don't exist in database.

**Solutions:**
1. Deactivate and reactivate theme
2. Check database user permissions
3. Verify `dbDelta()` function is working
4. Check for database errors in logs
5. Manually run table creation SQL

### Shift Signup Failing

**Problem:** Volunteers can't sign up for shifts.

**Solutions:**
1. Verify volunteer is logged in
2. Check shift capacity
3. Verify volunteer ID cookie is set
4. Check AJAX endpoint is responding
5. Review error logs

### Hours Not Saving

**Problem:** Hours logged don't save to database.

**Solutions:**
1. Verify volunteer ID is valid
2. Check hours value is within limits (0.5-24)
3. Verify nonce is valid
4. Check database connection
5. Review error logs

---

## Support & Resources

- **Documentation:** [campaignpress.com/docs](https://campaignpress.com/docs)
- **Code Review:** See `docs/VOLUNTEER_MODULE_REVIEW.md`
- **Issue Tracker:** GitHub Issues
- **Email Support:** support@campaignpress.com (Premium)

---

## Changelog

### Version 2.0.0 (2024-12-23)
- Initial release of volunteer management module
- Complete volunteer signup form
- Admin dashboard with filtering and search
- Volunteer self-service portal
- Shift scheduling system
- Hours tracking with verification
- Leaderboard functionality
- CSV export capability

---

**Need help?** Check the [Troubleshooting](#troubleshooting) section or visit our [documentation site](https://campaignpress.com/docs).
