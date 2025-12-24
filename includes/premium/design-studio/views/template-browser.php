<?php
/**
 * Premium Template Browser View
 *
 * Displays the premium template library with filtering and preview.
 *
 * @package CampaignPress
 * @subpackage Premium/DesignStudio
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$is_premium = isset($is_premium) ? $is_premium : false;
$templates = isset($templates) ? $templates : array();
$categories = $this->get_categories();
?>

<div class="wrap cp-premium-templates-wrap">
    <div class="cp-templates-header">
        <div class="cp-header-content">
            <h1>
                <?php esc_html_e('Template Library', 'campaign-office'); ?>
                <span class="cp-beta-badge">PREMIUM</span>
            </h1>
            <p class="description">
                <?php esc_html_e('Professional campaign page templates designed for conversion and engagement.', 'campaign-office'); ?>
            </p>
        </div>

        <?php if (!$is_premium) : ?>
            <div class="cp-upgrade-banner">
                <div class="cp-upgrade-content">
                    <span class="dashicons dashicons-lock"></span>
                    <div>
                        <strong><?php esc_html_e('Premium Feature', 'campaign-office'); ?></strong>
                        <p><?php esc_html_e('Upgrade to access 50+ professional templates', 'campaign-office'); ?></p>
                    </div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-upgrade')); ?>" class="button button-primary">
                        <?php esc_html_e('Upgrade Now', 'campaign-office'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="cp-templates-toolbar">
        <div class="cp-toolbar-left">
            <div class="cp-search-box">
                <span class="dashicons dashicons-search"></span>
                <input type="text" id="cp-template-search" placeholder="<?php esc_attr_e('Search templates...', 'campaign-office'); ?>" class="cp-input">
            </div>

            <select id="cp-filter-category" class="cp-select">
                <option value=""><?php esc_html_e('All Categories', 'campaign-office'); ?></option>
                <?php foreach ($categories as $key => $category) : ?>
                    <option value="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($category['name']); ?> (<?php echo absint($category['count']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="cp-filter-type" class="cp-select">
                <option value=""><?php esc_html_e('All Types', 'campaign-office'); ?></option>
                <option value="democratic"><?php esc_html_e('Democratic', 'campaign-office'); ?></option>
                <option value="republican"><?php esc_html_e('Republican', 'campaign-office'); ?></option>
                <option value="independent"><?php esc_html_e('Independent', 'campaign-office'); ?></option>
                <option value="progressive"><?php esc_html_e('Progressive', 'campaign-office'); ?></option>
                <option value="all"><?php esc_html_e('Non-partisan', 'campaign-office'); ?></option>
            </select>

            <select id="cp-filter-level" class="cp-select">
                <option value=""><?php esc_html_e('All Levels', 'campaign-office'); ?></option>
                <option value="local"><?php esc_html_e('Local', 'campaign-office'); ?></option>
                <option value="state"><?php esc_html_e('State', 'campaign-office'); ?></option>
                <option value="congressional"><?php esc_html_e('Congressional', 'campaign-office'); ?></option>
                <option value="presidential"><?php esc_html_e('Presidential', 'campaign-office'); ?></option>
            </select>

            <div class="cp-filter-toggle">
                <label>
                    <input type="checkbox" id="cp-filter-premium" <?php checked($is_premium); ?>>
                    <?php esc_html_e('Premium Only', 'campaign-office'); ?>
                </label>
            </div>
        </div>

        <div class="cp-toolbar-right">
            <label for="cp-page-selector" style="margin-right: 10px; font-weight: 600;">
                <?php esc_html_e('Apply to:', 'campaign-office'); ?>
            </label>
            <select id="cp-page-selector" class="cp-select" style="min-width: 250px;">
                <option value=""><?php esc_html_e('Select a page...', 'campaign-office'); ?></option>
                <?php
                $pages = get_pages();
                foreach ($pages as $page) :
                ?>
                    <option value="<?php echo esc_attr($page->ID); ?>">
                        <?php echo esc_html($page->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="cp-templates-grid" id="cp-templates-container">
        <?php if (empty($templates)) : ?>
            <div class="cp-no-templates">
                <span class="dashicons dashicons-admin-page"></span>
                <h3><?php esc_html_e('No Templates Found', 'campaign-office'); ?></h3>
                <p><?php esc_html_e('Try adjusting your filters or search query.', 'campaign-office'); ?></p>
            </div>
        <?php else : ?>
            <?php foreach ($templates as $template) : ?>
                <?php
                $is_locked = $template->is_premium && !$is_premium;
                $template_data = json_decode($template->template_data, true);
                ?>
                <div class="cp-template-card <?php echo $is_locked ? 'cp-locked' : ''; ?>" data-template="<?php echo esc_attr($template->template_key); ?>" data-category="<?php echo esc_attr($template->category); ?>" data-type="<?php echo esc_attr($template->campaign_type); ?>">
                    <?php if ($template->featured) : ?>
                        <div class="cp-featured-badge">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e('Featured', 'campaign-office'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($template->is_premium) : ?>
                        <div class="cp-premium-badge">
                            <span class="dashicons dashicons-awards"></span>
                            <?php esc_html_e('Premium', 'campaign-office'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="cp-template-preview">
                        <?php if ($template->preview_image) : ?>
                            <img src="<?php echo esc_url($template->preview_image); ?>" alt="<?php echo esc_attr($template->template_name); ?>">
                        <?php else : ?>
                            <div class="cp-preview-placeholder">
                                <span class="dashicons dashicons-admin-page"></span>
                            </div>
                        <?php endif; ?>

                        <div class="cp-template-overlay">
                            <button class="cp-btn cp-btn-preview" data-template="<?php echo esc_attr($template->template_key); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php esc_html_e('Preview', 'campaign-office'); ?>
                            </button>
                            <?php if (!$is_locked) : ?>
                                <button class="cp-btn cp-btn-use" data-template="<?php echo esc_attr($template->template_key); ?>">
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php esc_html_e('Use Template', 'campaign-office'); ?>
                                </button>
                            <?php else : ?>
                                <button class="cp-btn cp-btn-locked" disabled>
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php esc_html_e('Premium Only', 'campaign-office'); ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_locked) : ?>
                            <div class="cp-lock-overlay">
                                <span class="dashicons dashicons-lock"></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="cp-template-info">
                        <h3><?php echo esc_html($template->template_name); ?></h3>
                        <p><?php echo esc_html(wp_trim_words($template->template_description, 15)); ?></p>

                        <div class="cp-template-meta">
                            <span class="cp-meta-item">
                                <span class="dashicons dashicons-category"></span>
                                <?php echo esc_html($categories[$template->category]['name'] ?? ucfirst($template->category)); ?>
                            </span>
                            <span class="cp-meta-item">
                                <span class="dashicons dashicons-clock"></span>
                                <?php echo esc_html($template->setup_time); ?>
                            </span>
                            <span class="cp-meta-item">
                                <span class="dashicons dashicons-download"></span>
                                <?php echo number_format($template->downloads); ?>
                            </span>
                        </div>

                        <?php if ($template->tags) : ?>
                            <div class="cp-template-tags">
                                <?php
                                $tags = explode(',', $template->tags);
                                foreach (array_slice($tags, 0, 3) as $tag) :
                                ?>
                                    <span class="cp-tag"><?php echo esc_html(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Template Preview Modal -->
<div id="cp-preview-modal" class="cp-modal" style="display: none;">
    <div class="cp-modal-overlay"></div>
    <div class="cp-modal-content">
        <div class="cp-modal-header">
            <h2 id="cp-modal-title"><?php esc_html_e('Template Preview', 'campaign-office'); ?></h2>
            <button class="cp-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="cp-modal-body" id="cp-modal-body">
            <div class="cp-loading">
                <span class="spinner is-active"></span>
                <p><?php esc_html_e('Loading preview...', 'campaign-office'); ?></p>
            </div>
        </div>
        <div class="cp-modal-footer">
            <button class="button button-secondary cp-modal-close">
                <?php esc_html_e('Close', 'campaign-office'); ?>
            </button>
            <button class="button button-primary" id="cp-modal-use-template">
                <?php esc_html_e('Use This Template', 'campaign-office'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Premium Template Browser Styles */
.cp-premium-templates-wrap {
    margin: 0 -20px 0 -2px;
}

.cp-templates-header {
    background: #fff;
    padding: 2rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #ddd;
}

.cp-header-content h1 {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.cp-beta-badge {
    background: #d63638;
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.625rem;
    font-weight: 700;
}

.cp-upgrade-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-top: 1.5rem;
}

.cp-upgrade-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.cp-upgrade-content .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
}

.cp-upgrade-content strong {
    font-size: 1.25rem;
    display: block;
    margin-bottom: 0.25rem;
}

.cp-upgrade-content p {
    margin: 0;
    opacity: 0.9;
}

.cp-upgrade-content .button {
    margin-left: auto;
}

.cp-templates-toolbar {
    background: #fff;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.cp-toolbar-left {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}

.cp-toolbar-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cp-search-box {
    position: relative;
    min-width: 250px;
}

.cp-search-box .dashicons {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.cp-input {
    width: 100%;
    padding: 0.5rem 0.5rem 0.5rem 35px;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
}

.cp-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
    min-width: 150px;
}

.cp-templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    padding: 0 2rem 2rem;
}

.cp-template-card {
    background: #fff;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s;
    position: relative;
}

.cp-template-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.cp-template-card.cp-locked {
    opacity: 0.8;
}

.cp-featured-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #f0b849;
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.cp-premium-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #2271b1;
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.cp-template-preview {
    position: relative;
    height: 240px;
    overflow: hidden;
    background: #f5f5f5;
}

.cp-template-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cp-preview-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.cp-preview-placeholder .dashicons {
    font-size: 64px;
    width: 64px;
    height: 64px;
    color: rgba(255,255,255,0.5);
}

.cp-template-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    opacity: 0;
    transition: opacity 0.3s;
    padding: 1rem;
}

.cp-template-card:hover .cp-template-overlay {
    opacity: 1;
}

.cp-lock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.cp-lock-overlay .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #fff;
}

.cp-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.cp-btn-preview {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 2px solid #fff;
}

.cp-btn-preview:hover {
    background: #fff;
    color: #2271b1;
}

.cp-btn-use {
    background: #2271b1;
    color: #fff;
}

.cp-btn-use:hover {
    background: #135e96;
}

.cp-btn-locked {
    background: #666;
    color: #fff;
    cursor: not-allowed;
}

.cp-template-info {
    padding: 1.5rem;
}

.cp-template-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
}

.cp-template-info p {
    color: #666;
    font-size: 0.875rem;
    margin: 0 0 1rem 0;
    line-height: 1.5;
}

.cp-template-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
    font-size: 0.75rem;
    color: #666;
}

.cp-meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.cp-meta-item .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.cp-template-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cp-tag {
    background: #f5f5f5;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #666;
}

.cp-no-templates {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #999;
}

.cp-no-templates .dashicons {
    font-size: 64px;
    width: 64px;
    height: 64px;
    margin-bottom: 1rem;
}

/* Modal Styles */
.cp-modal {
    position: fixed;
    inset: 0;
    z-index: 100000;
}

.cp-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.7);
}

.cp-modal-content {
    position: relative;
    max-width: 1200px;
    max-height: 90vh;
    margin: 5vh auto;
    background: #fff;
    border-radius: 0.5rem;
    display: flex;
    flex-direction: column;
}

.cp-modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cp-modal-header h2 {
    margin: 0;
}

.cp-modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

.cp-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
}

.cp-modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.cp-loading {
    text-align: center;
    padding: 4rem 2rem;
}
</style>

<script>
jQuery(document).ready(function($) {
    var currentTemplate = null;

    // Search templates
    $('#cp-template-search').on('input', function() {
        var query = $(this).val().toLowerCase();
        filterTemplates();
    });

    // Filter templates
    $('#cp-filter-category, #cp-filter-type, #cp-filter-level, #cp-filter-premium').on('change', function() {
        filterTemplates();
    });

    function filterTemplates() {
        var search = $('#cp-template-search').val().toLowerCase();
        var category = $('#cp-filter-category').val();
        var type = $('#cp-filter-type').val();
        var level = $('#cp-filter-level').val();
        var premiumOnly = $('#cp-filter-premium').is(':checked');

        $('.cp-template-card').each(function() {
            var $card = $(this);
            var visible = true;

            if (search) {
                var text = $card.text().toLowerCase();
                visible = visible && text.includes(search);
            }

            if (category) {
                visible = visible && $card.data('category') === category;
            }

            if (type) {
                var cardType = $card.data('type');
                visible = visible && (cardType === type || cardType === 'all');
            }

            if (premiumOnly) {
                visible = visible && !$card.hasClass('cp-locked');
            }

            $card.toggle(visible);
        });
    }

    // Preview template
    $(document).on('click', '.cp-btn-preview', function() {
        var templateKey = $(this).data('template');
        currentTemplate = templateKey;
        $('#cp-preview-modal').fadeIn(300);
        // Load preview via AJAX
        $('#cp-modal-body').html('<div class="cp-loading"><span class="spinner is-active"></span><p>Loading preview...</p></div>');
    });

    // Use template
    $(document).on('click', '.cp-btn-use, #cp-modal-use-template', function() {
        var templateKey = currentTemplate || $(this).data('template');
        var postId = $('#cp-page-selector').val();

        if (!postId) {
            alert(cpTemplates.strings.select_page);
            return;
        }

        if ($(this).hasClass('cp-btn-locked')) {
            alert(cpTemplates.strings.premium_required);
            return;
        }

        $(this).prop('disabled', true).text(cpTemplates.strings.applying);

        $.ajax({
            url: cpTemplates.ajax_url,
            type: 'POST',
            data: {
                action: 'cp_apply_premium_template',
                template_key: templateKey,
                post_id: postId,
                nonce: cpTemplates.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(cpTemplates.strings.success);
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    }
                } else {
                    alert(response.data.message || cpTemplates.strings.error);
                }
            },
            error: function() {
                alert(cpTemplates.strings.error);
            },
            complete: function() {
                $(this).prop('disabled', false).text('Use Template');
            }
        });
    });

    // Close modal
    $('.cp-modal-close, .cp-modal-overlay').on('click', function() {
        $('#cp-preview-modal').fadeOut(300);
        currentTemplate = null;
    });
});
</script>
