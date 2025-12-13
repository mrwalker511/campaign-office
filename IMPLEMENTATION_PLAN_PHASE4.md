# Implementation Plan - Political Studio Phase 4 (Advanced Blocks)

**Goal:** Implement 8 Priority Custom Blocks with Elementor-rivaling features using React and the WordPress Block API (v3).

## 1. Architecture & Registration
*   **File:** `blocks/registration.php`
*   **Logic:** PHP function to register all blocks in the `blocks/` directory.
*   **Asset Loading:** Ensure `vite` build output is enqueued (or standard `block.json` loading if built).
*   **Shared Components:** Create `assets/js/components/` for reusable UI (Style Panel, Responsive Wrapper).

## 2. Block Specifications

### 1. Donation Form Block (`campaignpress/donation-form`)
*   **Features:** Drag-drop donation tiers, Crypto toggle.
*   **Components:** `DonationTiersEditor`, `CryptoToggle`, `BlockchainAddress`.
*   **Dynamic:** Hydrated React on frontend for "Live Toggle".

### 2. Event Organizer Block (`campaignpress/event-organizer`)
*   **Features:** Calendar view, RSVP counter, Map pins.
*   **Components:** `CalendarPreview`, `LocationPin`, `RSVPCounter`.

### 3. Volunteer Matcher Block (`campaignpress/volunteer-matcher`)
*   **Features:** Skills filtering, Role capacity.
*   **Components:** `SkillSelector`, `RoleCard`, `RadiusFilter`.

### 4. Policy Platform Block (`campaignpress/policy-platform`)
*   **Features:** Accordions, Voter support bars (interactive), PDF download.
*   **Components:** `PolicyAccordion`, `SupportMeter`.

### 5. Countdown Mission Control (`campaignpress/mission-control`)
*   **Features:** Weather sync, Momentum tracker. *Upgrade of existing Countdown or new block.*
*   **Components:** `WeatherWidget`, `MomentumGraph`.

### 6. Hero Commander (`campaignpress/hero-commander`)
*   **Features:** Parallax BG, Typewriter text, Sticky CTA.
*   **Components:** `ParallaxContainer`, `TypewriterHeading`, `StickyBar`.

### 7. Inline Style Panel (`campaignpress/style-panel`)
*   **Note:** This works best as a **Plugin/Sidebar** for other blocks, or a **Wrapper Block**.
*   **Implementation:** A "Style Container" block that wraps InnerBlocks and applies advanced CSS vars.
*   **Controls:** Padding/Margin visualizer, Google Fonts picker.

### 8. Responsive Section Wrapper (`campaignpress/section-wrapper`)
*   **Implementation:** Container block with breakpoints.
*   **Controls:** Breakpoint toggle, Gradient generator.

## 3. Execution Order
1.  **Loader:** Create `blocks/registration.php`.
2.  **Shared Utils:** Setup `assets/js/utils/` for API calls/styles.
3.  **Blocks 1-4 (Engagement):** Donation, Events, Volunteer, Policy.
4.  **Blocks 5-8 (Layout/Visual):** Mission Control, Hero, Style Panel, Section.

## 4. Verification
*   **Build:** Ensure `npm run build` (or `vite build`) processes the JSX. *Assumption: User will run build, or I provide compiled code if possible (unlikely for full React without node). I will write the SOURCE code.*
*   **Editor:** Verify blocks appear in "CampaignPress Blocks" category.
*   **Frontend:** Verify interactivity (toggles, accordions).
