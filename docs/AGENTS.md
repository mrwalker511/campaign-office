# Development Agent Workflows

**Guide for working with Claude Code agents on CampaignPress**

---

## Overview

This document provides workflow guidance for AI development agents (like Claude Code) working on the CampaignPress codebase. It establishes conventions, patterns, and best practices to maintain code quality and consistency.

---

## Agent Responsibilities

### 1. Code Understanding
- Read existing code before making changes
- Understand architectural patterns
- Follow established conventions
- Reference CLAUDE.md for context

### 2. Code Quality
- Write secure code (sanitize inputs, escape outputs)
- Follow WordPress coding standards
- Maintain consistent formatting
- Add clear comments for complex logic

### 3. Testing
- Test changes before committing
- Verify no regressions
- Check accessibility compliance
- Test across browsers

### 4. Documentation
- Update relevant documentation
- Add inline comments
- Document new features
- Update CHANGELOG.md

---

## Common Workflows

### Workflow 1: Adding a New Gutenberg Block

**Steps:**
1. Read `includes/free/gutenberg-blocks.php`
2. Create block component in `assets/react/blocks/`
3. Register block in gutenberg-blocks.php
4. Add render callback function
5. Style block in `assets/css/blocks.css`
6. Build assets: `npm run build`
7. Test in block editor
8. Update documentation

**Code Pattern:**
```php
// In includes/free/gutenberg-blocks.php
register_block_type('campaignpress/my-new-block', array(
    'editor_script' => 'campaignpress-blocks-js',
    'editor_style'  => 'campaignpress-blocks-css',
    'render_callback' => 'campaignpress_render_my_new_block',
    'attributes' => array(
        'title' => array('type' => 'string', 'default' => ''),
        'content' => array('type' => 'string', 'default' => '')
    )
));

function campaignpress_render_my_new_block($attributes) {
    $title = esc_html($attributes['title']);
    $content = wp_kses_post($attributes['content']);

    ob_start();
    ?>
    <div class="cp-block-my-new-block">
        <h3><?php echo $title; ?></h3>
        <div><?php echo $content; ?></div>
    </div>
    <?php
    return ob_get_clean();
}
```

### Workflow 2: Adding a Premium Feature

**Steps:**
1. Read `includes/premium/premium-init.php`
2. Determine license tier requirement
3. Create module directory in `includes/premium/[module-name]/`
4. Create init file `[module]-init.php`
5. Add feature to premium-init.php features array
6. Implement feature logic
7. Add admin interface
8. Test license gating
9. Update documentation

**Code Pattern:**
```php
// In includes/premium/premium-init.php
private $features = array(
    'my_new_feature' => array(
        'name' => 'My New Feature',
        'description' => 'Description of feature',
        'tier' => 'professional', // basic, professional, enterprise
        'file' => 'my-new-feature/my-new-feature-init.php',
        'enabled' => true
    )
);

// In includes/premium/my-new-feature/my-new-feature-init.php
class CampaignPress_My_New_Feature {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        // Feature initialization
    }
}

CampaignPress_My_New_Feature::get_instance();
```

### Workflow 3: Modifying the Design System

**Steps:**
1. Read `theme.json` for current tokens
2. Read `DESIGN_SYSTEM.md` for design philosophy
3. Determine if new token or modification needed
4. Update `theme.json` if adding tokens
5. Update `assets/css/design-system-wp69.css` if needed
6. Use WordPress CSS variables in components
7. Test in block editor
8. Verify party color schemes still work
9. Update design documentation

**Code Pattern:**
```json
// In theme.json
{
  "settings": {
    "color": {
      "palette": [
        {
          "name": "New Color",
          "slug": "new-color",
          "color": "#hexcode"
        }
      ]
    }
  }
}
```

```css
/* In component CSS */
.my-component {
    color: var(--wp--preset--color--new-color);
    font-family: var(--wp--preset--font-family--display);
    padding: var(--wp--preset--spacing--8);
}
```

### Workflow 4: Database Schema Changes

**Steps:**
1. Read `includes/premium/crm/class-crm-database.php`
2. Design schema change
3. Create migration function
4. Update version number
5. Add version check in init
6. Test on fresh install
7. Test on existing install (migration)
8. Update database documentation

**Code Pattern:**
```php
class CRM_Database {
    private static $db_version = '1.1.0'; // Increment version

    public static function update_database() {
        $current_version = get_option('cp_crm_db_version', '1.0.0');

        if (version_compare($current_version, '1.1.0', '<')) {
            self::migrate_to_1_1_0();
        }

        update_option('cp_crm_db_version', self::$db_version);
    }

    private static function migrate_to_1_1_0() {
        global $wpdb;
        $table = $wpdb->prefix . 'cp_contacts';

        $wpdb->query("ALTER TABLE {$table} ADD COLUMN new_field VARCHAR(255)");
    }
}
```

### Workflow 5: Security Patch

**Steps:**
1. Identify security issue
2. Read WordPress security best practices
3. Implement fix using WordPress security functions
4. Test exploit is prevented
5. Check for similar patterns elsewhere
6. Update security documentation
7. Consider backporting to older versions

**Code Pattern:**
```php
// Bad - SQL injection vulnerability
$results = $wpdb->get_results("SELECT * FROM table WHERE id = " . $_GET['id']);

// Good - Prepared statement
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM table WHERE id = %d",
    absint($_GET['id'])
));

// Bad - XSS vulnerability
echo $_POST['user_input'];

// Good - Escaped output
echo esc_html($_POST['user_input']);

// Bad - Missing nonce
if (isset($_POST['action'])) {
    do_action();
}

// Good - Nonce verification
if (isset($_POST['action']) &&
    wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    do_action();
}
```

---

## Code Standards

### PHP Coding Standards

**Follow WordPress Coding Standards:**
- Indentation: Tabs (not spaces)
- Braces: Allman style (opening brace on new line)
- Naming: snake_case for functions, variables
- Class names: PascalCase with underscores

**Example:**
```php
function campaignpress_my_function( $param1, $param2 )
{
    if ( $param1 === $param2 )
    {
        return true;
    }

    return false;
}

class CampaignPress_My_Class
{
    private $property;

    public function my_method()
    {
        // Method body
    }
}
```

### JavaScript Coding Standards

**ESLint Configuration:**
- Indentation: 2 spaces
- Quotes: Single quotes
- Semicolons: Required
- Naming: camelCase

**Example:**
```javascript
const myFunction = (param1, param2) => {
  if (param1 === param2) {
    return true;
  }

  return false;
};

class MyClass {
  constructor() {
    this.property = 'value';
  }

  myMethod() {
    // Method body
  }
}
```

### CSS Coding Standards

**BEM Methodology:**
```css
/* Block */
.cp-block-name { }

/* Element */
.cp-block-name__element { }

/* Modifier */
.cp-block-name--modifier { }
```

**WordPress CSS Variables:**
```css
.component {
    /* Use WordPress design tokens */
    color: var(--wp--preset--color--primary);
    font-family: var(--wp--preset--font-family--display);

    /* Fallback for unsupported */
    color: #0053c3;
    color: var(--wp--preset--color--primary, #0053c3);
}
```

---

## Testing Checklist

### Before Committing

- [ ] Code follows WordPress coding standards
- [ ] All inputs are sanitized
- [ ] All outputs are escaped
- [ ] SQL queries use prepared statements
- [ ] Nonces are verified for forms
- [ ] Capability checks for admin features
- [ ] No console.log() or var_dump() left in code
- [ ] Code is commented where necessary
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated

### Accessibility Testing

- [ ] Keyboard navigation works
- [ ] ARIA labels are present
- [ ] Color contrast meets WCAG 2.1 AA
- [ ] Screen reader testing
- [ ] Reduced motion respected

### Browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Mobile)

### Performance Testing

- [ ] No unnecessary database queries
- [ ] Assets are minified
- [ ] Images are optimized
- [ ] Lazy loading where appropriate

---

## Git Workflow

### Branch Naming

- **Feature:** `feature/feature-name`
- **Bug Fix:** `fix/bug-description`
- **Hotfix:** `hotfix/critical-bug`
- **Release:** `release/version-number`

### Commit Messages

**Format:**
```
type(scope): Brief description

Detailed explanation if needed

Closes #issue-number
```

**Types:**
- feat: New feature
- fix: Bug fix
- docs: Documentation
- style: Formatting
- refactor: Code restructuring
- perf: Performance improvement
- test: Adding tests
- chore: Maintenance

**Example:**
```
feat(crm): Add engagement scoring algorithm

Implemented engagement scoring based on:
- Recency of interactions
- Frequency of engagement
- Quality of responses
- Overall responsiveness

Closes #123
```

---

## Common Pitfalls to Avoid

### Security Pitfalls

1. **Never trust user input**
   ```php
   // Bad
   $value = $_POST['field'];

   // Good
   $value = sanitize_text_field($_POST['field']);
   ```

2. **Always escape output**
   ```php
   // Bad
   echo $variable;

   // Good
   echo esc_html($variable);
   ```

3. **Use nonces for forms**
   ```php
   // Bad
   if ($_POST['submit']) {
       // Process form
   }

   // Good
   if (wp_verify_nonce($_POST['_wpnonce'], 'action')) {
       // Process form
   }
   ```

### Performance Pitfalls

1. **Avoid N+1 queries**
   ```php
   // Bad
   foreach ($posts as $post) {
       $meta = get_post_meta($post->ID, 'key', true);
   }

   // Good
   $posts = get_posts(array('meta_key' => 'key'));
   ```

2. **Use transients for expensive operations**
   ```php
   $data = get_transient('expensive_data');
   if (false === $data) {
       $data = expensive_operation();
       set_transient('expensive_data', $data, HOUR_IN_SECONDS);
   }
   ```

### Code Quality Pitfalls

1. **Don't modify WordPress core**
2. **Don't use deprecated functions**
3. **Don't hardcode paths or URLs**
4. **Don't use global variables unnecessarily**

---

## Resources for Agents

### Essential Reading

- `docs/CLAUDE.md` - Complete architecture overview
- `docs/ARCHITECTURE.md` - System architecture
- `docs/TECH_STACK.md` - Technology stack
- `DEVELOPER-GUIDE.md` - Developer documentation
- `DESIGN_SYSTEM.md` - Design system guide

### WordPress Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Theme Handbook](https://developer.wordpress.org/themes/)
- [WordPress Security](https://developer.wordpress.org/apis/security/)

### Reference Implementation

Look at existing code for patterns:
- `includes/free/custom-post-types.php` - CPT registration
- `includes/free/gutenberg-blocks.php` - Block registration
- `includes/premium/crm/class-crm-database.php` - Database operations
- `functions.php` - Theme initialization

---

## Conclusion

Following these workflows ensures:
- **Consistency** across the codebase
- **Security** by following best practices
- **Quality** through testing and review
- **Documentation** for future maintainers

---

**Last Updated:** December 28, 2025
**Version:** 2.0.0
