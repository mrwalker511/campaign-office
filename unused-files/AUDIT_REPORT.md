# Theme Audit: Political Theme 2025 Standard

## 1. Capability Inventory (Current State)

### Theme Architecture
*   **Type:** Classic Hybrid Theme (uses PHP templates + `theme.json` for styles/settings).
*   **Build System:** Vite 5.0, Node.js 18+.
*   **Framework:** "CampaignPress" custom design system (replacing Bootstrap).

### Core Features
*   **Custom Post Types:**
    *   `cp_issue` (Key Issues)
    *   `cp_event` (Events)
*   **Templates:**
    *   `front-page.php`: Custom hero, issues grid, events list.
    *   `header.php` / `footer.php`: Standard classic structure.
    *   Sidebar & Footer Widget areas.
*   **Customizer Options:**
    *   Candidate Name, Office Seeking.
    *   Hero Video/Image toggles.
    *   Donation & Volunteer URLs.
    *   Organization Logo.

### Integrations & functionality
*   **Donations:** Button shortcuts (integration with payment processors implied via URL fields).
*   **Volunteer:** Management system files detected in `includes/free/volunteer-management.php`.
*   **Security:** Headers (X-Frame-Options), Escaping, Nonces.

---

## 2. Rubric Scorecard

| Category | Score (0-5) | Evidence | Impact |
| :--- | :---: | :--- | :--- |
| **Editing/Architecture** | **4** | `theme.json` is robust (colors, fonts, layout). Classic templates (`header.php`) limit full FSE, but "Hybrid" approach is stable for 2025. | **High:** Good maintainability, easy customization via Block Editor features. |
| **Performance** | **5** | Bootstrap removed for lightweight `design-system-wp69.css` (~25KB). `functions.php` includes font preconnects. Scripts loaded in footer. | **High:** Should result in excellent Core Web Vitals (LCP/CLS). |
| **Accessibility (A11y)** | **4** | `skip-link` present in `header.php`. Semantic HTML tags (`<nav>`, `<main>`, `<article>`). **Gap:** "Read More" links in `front-page.php` lack `aria-label` context. | **Critical:** "Read More" is a WCAG failure (2.4.4). |
| **Security** | **5** | Excellent escaping (`esc_html`, `esc_url`) throughout templates. Security headers added in `functions.php`. Nonces used in AJAX. | **High:** Robust against XSS and CSRF. |
| **Political UX** | **4** | Dedicated Event/Issue CPTs are great. Hero section is well-optimized for campaigns. **Gap:** Missing built-in "Paid for by" disclaimer field (relies on widgets). | **Medium:** "Paid for by" is a legal requirement in US. |
| **Compliance** | **3** | Privacy Policy link support via Footer Menu. **Gap:** Google Fonts loaded from Google CDN (GDPR risk in EU). No built-in Cookie Consent banner. | **Medium:** Legal risk depending on jurisdiction. |

---

## 3. Gaps & Recommendations Backlog

### P0: Critical Issues (Immediate Action)
*   **[A11y] Fix "Read More" Links:** Update `front-page.php` (and other loops) to include `aria-label="Read more about [Post Title]"`.
    *   *Location:* `front-page.php`
*   **[Legal] "Paid for by" Disclaimer:** Ensure this appears on every page. Currently relies on user adding a footer widget. Recommend hardcoding a field in Customizer and outputting in `footer.php` for safety.

### P1: High Priority
*   **[Privacy] Localize Google Fonts:** `theme.json` loads fonts from `fonts.googleapis.com`. For strict privacy (and perf), download and serve locally.
*   **[UX] Missing 404 Template:** Check if `404.php` has a "Return to Home" or "Donate" CTA.

### P2: Medium Priority (Future Enhancements)
*   **[FSE] Convert to Block Templates:** Slowly migrate `header.php` to `parts/header.html` for full FSE editing.
*   **[Features] Social Share:** Ensure standard social share buttons are present on `single.php`.

---

## 4. Verification Checklist

### Automated Testing
- [ ] **Lighthouse / PSI:** Run on Home, Issue, and Event pages. Target: Performance > 90, Accessibility > 90.
- [ ] **Axe DevTools:** Scan Homepage for contrast and hidden focus issues.

### Manual Verification
- [ ] **Keyboard Nav:** Tab through the entire homepage. Verify `Skip to content` works.
- [ ] **Screen Reader:** Listen to "Read More" links. Do they say "Read More about [Issue Name]"?
- [ ] **Legal Check:** Verify "Paid for by" disclaimer is visible on mobile and desktop.
- [ ] **Privacy:** Check Network tab. Are fonts loading from Google? (If flagged for remediation).
