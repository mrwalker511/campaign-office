# CampaignPress Developer Setup & Workflow

This guide details the typical workflow a developer follows when setting up the CampaignPress theme, along with the verification steps to ensure all modules are functional and "ready to go."

---

## 1. Environment Preparation

### Developer Mode Configuration
To enable all premium features and bypass license checks during development:
1.  Verify `dev-license-helper.php` exists in the root directory.
2.  (Optional) Add `define('CAMPAIGNPRESS_DEV_MODE', true);` to `wp-config.php`.

**Verification:**
- Navigate to `CampaignPress -> License` in the WordPress admin.
- You should see "Development Mode Active" and test keys for Professional and Free tiers.

---

## 2. Theme Initialization

### Core Functionality Check
Activate the theme. CampaignPress will check for the `Campaign Office Core` plugin.
- **Action:** If the plugin is missing, a notice will appear with an install/activate button.
- **Goal:** Ensure Custom Post Types (Issues, Events, etc.) are available.

**Verification:**
- Check for the following menu items in the sidebar:
    - **Issues** (`cp_issue`)
    - **Events** (`cp_event`)
    - **Endorsements** (`cp_endorsement`)
    - **Team Members** (`cp_team`)
    - **Volunteer Opportunities** (`cp_volunteer`)

---

## 3. Rapid Setup: Demo Content

### One-Click Import
1.  Navigate to `Appearance -> Demo Content`.
2.  Click **Import Demo Content**.
3.  The system will create pages, menu items, and sample CPT entries.

**Verification:**
- Verify pages: **Home**, **About**, **Volunteer Opportunities**, **Donate**.
- Verify menu items: The primary menu should contain links to the new pages.

---

## 4. Module Configuration

### Volunteer Portal
- **Setup:** Create a new page and add the shortcode `[cp_volunteer_portal]`.
- **Functionality:** Handles login, registration, shift signups, and hour logging.

**Verification:**
- Check if `wp_cp_volunteer_shifts`, `wp_cp_volunteer_hours`, etc., tables are created in the database.
- Use a test user to log in via the portal.

### Donation System
- **Setup:** Configure payment processors in `CampaignPress -> Donations`.
- **Integration:** Use `[cp_donation_button processor="actblue"]` on any page.

**Verification:**
- Verify button output: Ensure the "Donate" button links to the correct external processor URL with campaign tracking parameters.

### Design Studio
- **Setup:** Go to any page and use the **Design Studio** meta box to customize the layout.
- **Workflow:** Set primary colors, typography, and section padding.

**Verification:**
- Check front-end: Verify that custom CSS variables (e.g., `--cp-primary-color`) are output in the `<head>` and applied to elements.

---

## 5. Premium Operations (Professional Tier)

### Voter CRM
- **Setup:** Ensure `CRM` module is enabled in `CampaignPress -> Features`.
- **Workflow:** Import a voter CSV or capture data via website forms.

**Verification:**
- Check `CampaignPress -> CRM` for contact list.
- Verify engagement scoring is updating after interactions.

### Field Operations
- **Setup:** Create a "Walk List" in `Field Ops -> Canvassing`.
- **Workflow:** Assign addresses to a volunteer.

**Verification:**
- Check if AJAX handlers for `ajax_get_walk_list` return valid JSON data.

---

## 6. Comprehensive Test Suite (Manual)

| Component | Test Action | Expected Result |
|-----------|-------------|-----------------|
| **Blocks** | Insert "Donation Button" block | Block renders in editor and frontend |
| **Forms** | Submit Volunteer Signup | Entry appears in Volunteer Database |
| **SEO** | View Page Source | Open Graph and Twitter Card meta tags present |
| **A11y** | Tab through homepage | Skip-link appears; focus states visible |
| **Performance** | Run Lighthouse | Zero external font requests (Privacy/Speed) |

---

## Conclusion
The CampaignPress theme is engineered to be "ready to go" upon activation. All core modules are pre-hooked, and demo content provides a functional starting point for developers to customize and launch campaign sites within hours.
