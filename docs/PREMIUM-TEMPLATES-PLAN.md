# Premium Templates - Implementation Plan

## Overview
Advanced Template Library for CampaignPress Premium users, providing 50+ professionally designed campaign page templates organized by campaign type, election level, and use case.

---

## 1. Database Schema

### Table: `wp_cp_premium_templates`
```sql
CREATE TABLE wp_cp_premium_templates (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    template_key VARCHAR(100) NOT NULL UNIQUE,
    template_name VARCHAR(255) NOT NULL,
    template_description TEXT,
    template_data LONGTEXT NOT NULL,
    preview_image VARCHAR(500),
    category VARCHAR(50) NOT NULL,
    campaign_type VARCHAR(50),
    election_level VARCHAR(50),
    tags TEXT,
    is_premium BOOLEAN DEFAULT 1,
    featured BOOLEAN DEFAULT 0,
    downloads INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY category (category),
    KEY campaign_type (campaign_type),
    KEY election_level (election_level),
    KEY is_premium (is_premium),
    KEY featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2. Template Categories & Organization

### Categories
1. **Homepage Layouts** (15 templates)
   - Full-featured campaign home pages
   - Hero-focused designs
   - Issue-driven layouts
   - Grassroots movement styles

2. **Landing Pages** (10 templates)
   - Donation-focused
   - Volunteer recruitment
   - Event registration
   - Email signup
   - Petition signing

3. **About/Bio Pages** (8 templates)
   - Candidate biography
   - Team introduction
   - Our story
   - Values & vision

4. **Issues Pages** (7 templates)
   - Single issue deep-dive
   - Issue comparison
   - Policy platform
   - Issue grid

5. **Events Pages** (5 templates)
   - Event listing
   - Single event detail
   - Virtual event
   - Rally/town hall

6. **Get Involved** (5 templates)
   - Volunteer hub
   - Donation center
   - Take action
   - Contact/support

### Election Levels
- Local (City Council, Mayor, School Board)
- State (State Rep, State Senate, Governor)
- Congressional (House, Senate)
- Presidential

### Campaign Types
- Democratic
- Republican
- Independent
- Progressive
- Moderate
- Non-partisan

---

## 3. Template Data Structure

### JSON Format
```json
{
  "template_key": "hero_donation_landing",
  "template_name": "Hero Donation Landing",
  "template_description": "High-converting donation page with hero section, stats, and social proof",
  "category": "landing_pages",
  "campaign_type": "all",
  "election_level": "all",
  "tags": ["donation", "conversion", "hero", "stats"],
  "is_premium": true,
  "featured": true,
  "preview_image": "https://preview.campaignpress.com/templates/hero-donation.png",
  "components": [
    {
      "type": "hero",
      "variant": "centered",
      "settings": {
        "headline": "Join Our Movement",
        "subheadline": "Every dollar brings us closer to victory",
        "background_type": "gradient",
        "background_value": "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
        "cta_text": "Donate Now",
        "cta_style": "primary-large"
      }
    },
    {
      "type": "stats",
      "variant": "counters",
      "settings": {
        "stats": [
          {"label": "Donors", "value": "12,453", "icon": "💰"},
          {"label": "Volunteers", "value": "3,891", "icon": "👥"},
          {"label": "Events", "value": "127", "icon": "📅"}
        ]
      }
    },
    {
      "type": "donation",
      "variant": "tiers",
      "settings": {
        "tiers": [25, 50, 100, 250, 500, 1000],
        "recurring_option": true,
        "thermometer": true,
        "goal": 50000,
        "raised": 32750
      }
    },
    {
      "type": "testimonials",
      "variant": "carousel",
      "settings": {
        "testimonials": [
          {
            "quote": "This campaign represents real change for our community",
            "author": "Sarah Johnson",
            "location": "Springfield",
            "image": ""
          }
        ]
      }
    },
    {
      "type": "cta",
      "variant": "banner",
      "settings": {
        "headline": "Ready to Make a Difference?",
        "button_text": "Contribute Today",
        "background": "#2271b1"
      }
    }
  ],
  "custom_css": "",
  "responsive_settings": {
    "mobile": {
      "hide_components": [],
      "stack_order": "default"
    }
  },
  "seo": {
    "title": "Donate - Support Our Campaign",
    "description": "Join thousands of supporters and contribute to our grassroots movement for change."
  }
}
```

---

## 4. Premium Template Browser UI

### Location
- **Main**: Design Studio > Templates (enhanced version)
- **Secondary**: Quick access from Page Builder canvas

### Layout
```
┌─────────────────────────────────────────────────────────────┐
│  Premium Templates Library                    [Free | Pro]  │
├─────────────────────────────────────────────────────────────┤
│  Search: [_______________]  Filter: [Category ▼] [Type ▼]   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐       │
│  │ Preview │  │ Preview │  │ Preview │  │ Preview │       │
│  │  Image  │  │  Image  │  │  Image  │  │  Image  │       │
│  │ [LOCKED]│  │         │  │ [LOCKED]│  │         │       │
│  ├─────────┤  ├─────────┤  ├─────────┤  ├─────────┤       │
│  │Template │  │Template │  │Template │  │Template │       │
│  │  Name   │  │  Name   │  │  Name   │  │  Name   │       │
│  │ Premium │  │  Free   │  │ Premium │  │  Free   │       │
│  │[Preview]│  │ [Use]   │  │[Preview]│  │ [Use]   │       │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘       │
│                                                              │
│  [...more templates...]                                     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Features
- **Live Preview Modal**: Full-page preview with device switcher
- **Premium Badge**: Clear visual indicator for premium templates
- **Quick Filters**:
  - All / Free / Premium
  - Category dropdown
  - Election level
  - Campaign type
  - Tags
- **Search**: Real-time search by name, description, tags
- **Favorites**: Save favorite templates
- **Recently Used**: Show last 5 used templates
- **Upgrade CTA**: For free users viewing premium templates

---

## 5. Template Categories Detail

### 5.1 Homepage Layouts (15 templates)

#### Template 1: "Progressive Leader"
- **Type**: Democratic, Progressive
- **Components**: Video hero, policy tabs, endorsements grid, donation CTA
- **Best For**: Progressive candidates, issue-focused campaigns
- **Color Scheme**: Blue gradient (#2271b1 → #135e96)

#### Template 2: "Conservative Champion"
- **Type**: Republican, Conservative
- **Components**: Split hero, values timeline, testimonial carousel, volunteer CTA
- **Best For**: Conservative candidates, values-driven campaigns
- **Color Scheme**: Red gradient (#CC0000 → #8B0000)

#### Template 3: "Independent Voice"
- **Type**: Independent, Non-partisan
- **Components**: Minimal hero, issue accordion, team grid, contact form
- **Best For**: Independent candidates, bipartisan appeal
- **Color Scheme**: Purple gradient (#663399 → #9966CC)

#### Template 4: "Grassroots Movement"
- **Type**: All, Progressive
- **Components**: Rally hero with countdown, volunteer opportunities, social feed, petition
- **Best For**: Movement-building, community organizing
- **Color Scheme**: Green gradient (#228B22 → #006400)

#### Template 5: "Professional Executive"
- **Type**: All, Moderate
- **Components**: Professional hero, experience timeline, policy issues, team
- **Best For**: Executive offices (Mayor, Governor), experienced candidates
- **Color Scheme**: Navy blue (#003366 → #0066CC)

[...11-15 more homepage templates...]

### 5.2 Landing Pages (10 templates)

#### Template 16: "Conversion Maximizer"
- **Purpose**: Donation
- **Components**: Hero with urgency timer, social proof stats, tiered donation form, FAQ
- **Conversion Elements**: Exit-intent popup, progress thermometer, matching gift banner
- **A/B Test Ready**: Yes

#### Template 17: "Volunteer Hub"
- **Purpose**: Volunteer recruitment
- **Components**: Impact hero, opportunity cards, shift calendar, testimonials
- **Features**: Skill-based sorting, location filter, availability picker
- **Integration**: CRM sync for volunteer data

[...8 more landing pages...]

### 5.3 About/Bio Pages (8 templates)

#### Template 26: "The Journey"
- **Components**: Timeline hero, photo story, values cards, personal message video
- **Best For**: Candidate biography, personal story
- **Features**: Photo gallery integration, video embed

[...7 more bio templates...]

### 5.4 Issues Pages (7 templates)

#### Template 34: "Policy Platform"
- **Components**: Issue grid with icons, detailed policy tabs, endorsement quotes
- **Features**: Downloadable policy PDFs, social sharing per issue
- **Best For**: Policy-focused campaigns, issue comparison

[...6 more issues templates...]

### 5.5 Events Pages (5 templates)

#### Template 41: "Event Central"
- **Components**: Featured event hero, calendar view, map integration, RSVP form
- **Features**: Google Calendar sync, recurring events, virtual event support
- **Best For**: High-event volume campaigns

[...4 more event templates...]

### 5.6 Get Involved (5 templates)

#### Template 46: "Action Center"
- **Components**: Multi-option hero, action cards (donate/volunteer/share/contact)
- **Features**: Progress tracking, gamification badges, social sharing
- **Best For**: Engagement-focused campaigns, GOTV efforts

[...4 more action templates...]

---

## 6. Premium Gating Strategy

### Free Tier Access
- 3 basic templates (currently implemented)
- Preview all templates
- Template filtering and search

### Premium Tier Access
- All 50+ premium templates
- One-click template application
- Template customization
- Save custom templates
- Import/export templates
- Priority template requests

### Premium Gate Implementation
```php
// Check if user can access premium templates
if (!cp_is_premium_active()) {
    // Show upgrade CTA overlay
    display_premium_template_upgrade_cta($template);
    return;
}

// Allow template usage
apply_template($template_id, $post_id);
```

---

## 7. Technical Implementation

### File Structure
```
includes/
  premium/
    design-studio/
      premium-templates-init.php          # Main initialization
      class-premium-templates.php         # Template management class
      class-template-browser.php          # Browser UI class
      class-template-importer.php         # Import/export functionality
      templates/
        homepage/
          progressive-leader.json
          conservative-champion.json
          [...]
        landing-pages/
          conversion-maximizer.json
          [...]
        [other categories...]
      views/
        template-browser.php              # Template browser page
        template-preview-modal.php        # Preview modal
        upgrade-cta.php                   # Premium upgrade overlay
```

### Class Structure

#### Class: `CP_Premium_Templates`
```php
class CP_Premium_Templates {
    private $templates_table;

    // Core Methods
    public function __construct()
    public function create_templates_table()
    public function register_premium_templates()
    public function get_template($template_key)
    public function get_templates_by_category($category)
    public function search_templates($query)
    public function filter_templates($filters)
    public function apply_template($template_key, $post_id)
    public function preview_template($template_key)

    // Admin Methods
    public function add_admin_menu()
    public function render_template_browser()
    public function enqueue_browser_assets()

    // AJAX Handlers
    public function ajax_get_template_preview()
    public function ajax_apply_template()
    public function ajax_search_templates()
}
```

---

## 8. Sample Templates (First 5 to Build)

### Priority 1 Templates (MVP)
1. **"Progressive Leader"** (Homepage) - Democratic/Progressive campaigns
2. **"Conversion Maximizer"** (Landing) - Donation optimization
3. **"Volunteer Hub"** (Landing) - Volunteer recruitment
4. **"The Journey"** (Bio) - Candidate story
5. **"Policy Platform"** (Issues) - Issue-focused layout

### Template Data Files
Each template as a JSON file in `includes/premium/design-studio/templates/[category]/`

---

## 9. UI/UX Enhancements

### Template Card Design
```html
<div class="cp-template-card">
    <div class="cp-template-preview">
        <img src="[preview-image]" alt="[template-name]">
        <div class="cp-template-overlay">
            <button class="cp-btn-preview">👁️ Preview</button>
            <button class="cp-btn-use">✓ Use Template</button>
        </div>
        <span class="cp-premium-badge">⭐ Premium</span>
    </div>
    <div class="cp-template-info">
        <h4>[Template Name]</h4>
        <p>[Short description]</p>
        <div class="cp-template-meta">
            <span class="cp-category">[Category]</span>
            <span class="cp-downloads">↓ [downloads]</span>
            <span class="cp-rating">★★★★★</span>
        </div>
        <div class="cp-template-tags">
            <span class="tag">tag1</span>
            <span class="tag">tag2</span>
        </div>
    </div>
</div>
```

### Preview Modal
- Full-screen preview
- Device switcher (desktop/tablet/mobile)
- "Use This Template" CTA
- "Customize First" option
- Template details sidebar
- Related templates suggestions

---

## 10. Implementation Timeline

### Phase 1: Foundation (Week 1)
- [ ] Create database table
- [ ] Build base template management class
- [ ] Create 5 priority templates (JSON data)
- [ ] Basic template browser UI

### Phase 2: Premium Integration (Week 2)
- [ ] Integrate with premium licensing system
- [ ] Add premium gates
- [ ] Build upgrade CTAs
- [ ] Template preview modal

### Phase 3: Enhancement (Week 3)
- [ ] Add 20 more templates (total 25)
- [ ] Search and filtering
- [ ] Template favorites
- [ ] Usage analytics

### Phase 4: Polish (Week 4)
- [ ] Add remaining 25 templates (total 50)
- [ ] Advanced preview features
- [ ] Import/export functionality
- [ ] Documentation

---

## 11. Success Metrics

### Engagement Metrics
- Template browser page views
- Template previews per session
- Template application rate
- Most popular templates
- Search queries

### Conversion Metrics
- Free → Premium conversions triggered by templates
- Premium user template usage rate
- Templates per premium user
- Template satisfaction ratings

### Business Metrics
- Increase in premium subscriptions
- User retention improvement
- Support ticket reduction (easier design)
- Time-to-launch for new campaigns

---

## 12. Future Enhancements (Post-MVP)

1. **Template Marketplace**
   - User-submitted templates
   - Designer community
   - Template ratings and reviews

2. **AI-Powered Recommendations**
   - Suggest templates based on campaign type
   - Auto-populate template data from candidate info
   - Smart content suggestions

3. **Template Builder**
   - Allow premium users to save custom templates
   - Template cloning and variation
   - Share templates across team sites

4. **Seasonal Templates**
   - Election cycle templates
   - Holiday-themed designs
   - Trending design styles

5. **Industry Templates**
   - Labor union campaigns
   - Ballot initiatives
   - Non-profit advocacy
   - Corporate campaigns

---

## 13. Marketing & Documentation

### Marketing Points
- "50+ professionally designed templates"
- "Launch your campaign site in 5 minutes"
- "Conversion-optimized designs"
- "Tested with winning campaigns"
- "New templates added monthly"

### Documentation Needed
- Template browser guide
- Template customization tutorial
- Video walkthrough for each template category
- Best practices guide
- Template showcase page

---

## Next Steps
1. Get approval on database schema
2. Create first 5 template JSON files
3. Build `CP_Premium_Templates` class
4. Design template browser UI
5. Integrate premium gating
6. Test with sample data
7. Launch beta to premium users

---

**Questions to Resolve:**
1. Should templates include demo images/content or just structure?
2. Template versioning strategy?
3. Template update notifications for users?
4. Allow users to lock templates to prevent accidental changes?
5. Multi-site template sharing mechanism?
