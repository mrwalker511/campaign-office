<?php
/**
 * Template for displaying search forms
 *
 * @package CampaignPress
 * @since 1.0.0
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label for="search-field-<?php echo esc_attr( uniqid() ); ?>">
        <span class="screen-reader-text"><?php echo esc_html_x( 'Search for:', 'label', 'campaign-office' ); ?></span>
    </label>
    <div class="search-form-wrapper">
        <input type="search" id="search-field-<?php echo esc_attr( uniqid() ); ?>" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'campaign-office' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
        <button type="submit" class="search-submit">
            <span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'campaign-office' ); ?></span>
            <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M17.5 17.5L13.875 13.875M15.8333 9.16667C15.8333 12.8486 12.8486 15.8333 9.16667 15.8333C5.48477 15.8333 2.5 12.8486 2.5 9.16667C2.5 5.48477 5.48477 2.5 9.16667 2.5C12.8486 2.5 15.8333 5.48477 15.8333 9.16667Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</form>
