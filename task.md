# Task: Comprehensive Theme Audit & Debugging

- [ ] **Initial Research & Mapping**
    - [x] List root files and core structure <!-- id: 1 -->
    - [x] Review `functions.php` and `includes/core/loader.php` <!-- id: 2 -->
    - [x] Initial debug of `donation-enhancements.php` and `demo-content.php` <!-- id: 3 -->

- [ ] **Systematic Directory Review**
    - [/] **includes/free/** (Remaining features) <!-- id: 4 -->
        - [x] `accessibility.php`
        - [x] `donation-enhancements.php`
        - [x] `volunteer-management.php`
        - [x] `custom-post-types.php`
        - [ ] `font-preconnect.php`
        - [ ] `class-bootstrap-navwalker.php`
        - [ ] `gutenberg-blocks.php`
        - [ ] `customizer.php`
        - [ ] `template-functions.php`
        - [ ] `integrations.php`
        - [ ] `admin-notices.php`
        - [ ] `event-management.php`
        - [ ] `translation-support.php`
    - [ ] **includes/core/** <!-- id: 5 -->
        - [ ] `class-performance.php`
        - [ ] `class-template-loader.php`
    - [ ] **blocks/** (Block logic and registration) <!-- id: 6 -->
    - [ ] **parts/** (Template parts / Organisms) <!-- id: 7 -->
    - [ ] **patterns/** (Block patterns) <!-- id: 8 -->
    - [ ] **templates/** (Page templates and CPT templates) <!-- id: 9 -->
    - [ ] **Root Templates** (`header.php`, `footer.php`, `front-page.php`, etc.) <!-- id: 10 -->

- [ ] **Assets & Build Pipeline Verification**
    - [ ] Review `assets/css/` and `assets/js/` <!-- id: 11 -->
    - [ ] Verify `gulpfile.js`, `postcss.config.js`, `tailwind.config.js`, `vite.config.js` <!-- id: 12 -->

- [ ] **Final Verification & Reporting**
    - [ ] Consolidate all findings into `CODE_REVIEW_REPORT.md` <!-- id: 13 -->
    - [ ] Final walkthrough of major features <!-- id: 14 -->
