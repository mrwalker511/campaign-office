# Remove Sidebar from Homepage

The user reported a sidebar appearing on the homepage, interfering with the desired full-width design.
The `front-page.php` template explicitly calls `get_sidebar()` at the bottom.
To fix this, we will remove that call.

## User Review Required
None.

## Proposed Changes

### Template

#### [MODIFY] [front-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/front-page.php)
- Remove the conditional block calling `get_sidebar()`.

```php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
```

## Verification Plan

### Manual Verification
1.  Check Homepage: Verify the sidebar is no longer rendered at the bottom or side of the page.
