<?php
/**
 * Heroicons Browser
 *
 * Admin page for browsing and copying Heroicons
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class CP_Icons_Browser
 *
 * Manages the Heroicons browser admin page
 */
class CP_Icons_Browser {

	/**
	 * Available icons by category
	 *
	 * @var array
	 */
	private $icon_categories = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize categories
		$this->init_categories();

		// Admin menu
		add_action('admin_menu', array($this, 'add_admin_menu'));

		// AJAX handlers
		add_action('wp_ajax_cp_search_icons', array($this, 'ajax_search_icons'));
		add_action('wp_ajax_cp_get_icon_svg', array($this, 'ajax_get_icon_svg'));
		add_action('wp_ajax_cp_get_icons_by_category', array($this, 'ajax_get_icons_by_category'));

		// Enqueue assets
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
	}

	/**
	 * Initialize icon categories
	 */
	private function init_categories() {
		$this->icon_categories = array(
			'all' => array(
				'name' => __('All Icons', 'campaignpress'),
				'icons' => $this->get_all_icon_names(),
			),
			'campaign' => array(
				'name' => __('Campaign Essentials', 'campaignpress'),
				'icons' => array('megaphone', 'heart', 'star', 'flag', 'users', 'user-group', 'hand-raised', 'hand-thumb-up', 'hand-thumb-down', 'chart-bar', 'currency-dollar', 'building-office', 'globe-alt', 'map-pin'),
			),
			'arrows' => array(
				'name' => __('Arrows & Navigation', 'campaignpress'),
				'icons' => array('arrow-right', 'arrow-left', 'arrow-up', 'arrow-down', 'arrow-long-right', 'arrow-long-left', 'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down', 'arrow-path', 'arrow-uturn-left', 'arrows-right-left', 'arrows-up-down'),
			),
			'communication' => array(
				'name' => __('Communication', 'campaignpress'),
				'icons' => array('envelope', 'phone', 'chat-bubble-left-right', 'chat-bubble-left', 'bell', 'megaphone', 'speaker-wave', 'microphone', 'video-camera', 'paper-airplane'),
			),
			'ui' => array(
				'name' => __('User Interface', 'campaignpress'),
				'icons' => array('check', 'x-mark', 'plus', 'minus', 'cog-6-tooth', 'bars-3', 'magnifying-glass', 'funnel', 'pencil', 'trash', 'eye', 'eye-slash', 'lock-closed', 'lock-open'),
			),
			'files' => array(
				'name' => __('Files & Documents', 'campaignpress'),
				'icons' => array('document', 'document-text', 'folder', 'folder-open', 'clipboard', 'clipboard-document', 'archive-box', 'arrow-down-tray', 'arrow-up-tray', 'photo', 'film'),
			),
			'social' => array(
				'name' => __('Social & Sharing', 'campaignpress'),
				'icons' => array('share', 'bookmark', 'heart', 'star', 'chat-bubble-left-right', 'hand-thumb-up', 'link', 'rss', 'globe-alt'),
			),
			'status' => array(
				'name' => __('Status & Alerts', 'campaignpress'),
				'icons' => array('check-circle', 'x-circle', 'exclamation-triangle', 'information-circle', 'question-mark-circle', 'bell-alert', 'shield-check', 'shield-exclamation', 'light-bulb', 'sparkles'),
			),
			'data' => array(
				'name' => __('Data & Charts', 'campaignpress'),
				'icons' => array('chart-bar', 'chart-pie', 'presentation-chart-bar', 'table-cells', 'squares-2x2', 'list-bullet', 'numbered-list', 'calendar', 'clock', 'calculator'),
			),
			'people' => array(
				'name' => __('People & Users', 'campaignpress'),
				'icons' => array('user', 'users', 'user-group', 'user-circle', 'user-plus', 'user-minus', 'identification', 'briefcase', 'academic-cap'),
			),
		);
	}

	/**
	 * Get all available icon names from the filesystem
	 *
	 * @return array
	 */
	private function get_all_icon_names() {
		$icons = array();
		$icons_dir = get_template_directory() . '/assets/icons/24/outline/';

		if (is_dir($icons_dir)) {
			$files = glob($icons_dir . '*.svg');
			foreach ($files as $file) {
				$icon_name = basename($file, '.svg');
				$icons[] = $icon_name;
			}
			sort($icons);
		}

		return $icons;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'themes.php',
			__('Heroicons Browser', 'campaignpress'),
			__('Heroicons', 'campaignpress'),
			'edit_theme_options',
			'heroicons-browser',
			array($this, 'render_browser_page')
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_assets($hook) {
		if ($hook !== 'appearance_page_heroicons-browser') {
			return;
		}

		wp_enqueue_style(
			'cp-icons-browser',
			get_template_directory_uri() . '/assets/css/icons-browser.css',
			array(),
			CAMPAIGNPRESS_VERSION
		);

		wp_enqueue_script(
			'cp-icons-browser',
			get_template_directory_uri() . '/assets/js/icons-browser.js',
			array('jquery'),
			CAMPAIGNPRESS_VERSION,
			true
		);

		wp_localize_script('cp-icons-browser', 'cpIconsBrowser', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('cp_icons_browser'),
			'i18n' => array(
				'copied' => __('Copied to clipboard!', 'campaignpress'),
				'copyFailed' => __('Failed to copy', 'campaignpress'),
				'loading' => __('Loading icons...', 'campaignpress'),
			),
		));
	}

	/**
	 * Render browser page
	 */
	public function render_browser_page() {
		?>
		<div class="wrap cp-icons-browser-page">
			<h1><?php _e('Heroicons Browser', 'campaignpress'); ?></h1>
			<p class="description">
				<?php _e('Browse and copy beautiful Heroicons for use in your campaign website. Click any icon to copy its function call.', 'campaignpress'); ?>
			</p>

			<div class="cp-icons-browser-controls">
				<div class="cp-icons-search">
					<input
						type="text"
						id="cp-icon-search"
						class="regular-text"
						placeholder="<?php esc_attr_e('Search icons...', 'campaignpress'); ?>"
					/>
				</div>

				<div class="cp-icons-filters">
					<label for="cp-icon-category"><?php _e('Category:', 'campaignpress'); ?></label>
					<select id="cp-icon-category">
						<?php foreach ($this->icon_categories as $key => $category) : ?>
							<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($category['name']); ?></option>
						<?php endforeach; ?>
					</select>

					<label for="cp-icon-style"><?php _e('Style:', 'campaignpress'); ?></label>
					<select id="cp-icon-style">
						<option value="outline"><?php _e('Outline', 'campaignpress'); ?></option>
						<option value="solid"><?php _e('Solid', 'campaignpress'); ?></option>
						<option value="mini"><?php _e('Mini (20px)', 'campaignpress'); ?></option>
						<option value="micro"><?php _e('Micro (16px)', 'campaignpress'); ?></option>
					</select>

					<label for="cp-icon-size-display"><?php _e('Display Size:', 'campaignpress'); ?></label>
					<select id="cp-icon-size-display">
						<option value="sm"><?php _e('Small', 'campaignpress'); ?></option>
						<option value="md" selected><?php _e('Medium', 'campaignpress'); ?></option>
						<option value="lg"><?php _e('Large', 'campaignpress'); ?></option>
						<option value="xl"><?php _e('Extra Large', 'campaignpress'); ?></option>
					</select>
				</div>
			</div>

			<div class="cp-icons-stats">
				<span class="cp-icons-count"><?php printf(__('%d icons available', 'campaignpress'), count($this->icon_categories['all']['icons'])); ?></span>
			</div>

			<div class="cp-icons-grid-wrapper">
				<div id="cp-icons-grid" class="cp-icons-grid">
					<?php $this->render_icons_grid('all', 'outline'); ?>
				</div>
			</div>

			<div class="cp-icons-usage">
				<h2><?php _e('Usage Examples', 'campaignpress'); ?></h2>
				<div class="cp-usage-examples">
					<div class="cp-usage-example">
						<h3><?php _e('In PHP Templates', 'campaignpress'); ?></h3>
						<code>&lt;?php echo campaignpress_get_heroicon('star', 'outline'); ?&gt;</code>
					</div>
					<div class="cp-usage-example">
						<h3><?php _e('With Custom Size', 'campaignpress'); ?></h3>
						<code>&lt;?php echo campaignpress_get_ui_icon('calendar', array('style' => 'solid')); ?&gt;</code>
					</div>
					<div class="cp-usage-example">
						<h3><?php _e('In Gutenberg', 'campaignpress'); ?></h3>
						<p><?php _e('Use the "Heroicon" block from the block inserter', 'campaignpress'); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div id="cp-icon-copied-notice" class="cp-copied-notice" style="display: none;">
			<span class="dashicons dashicons-yes-alt"></span>
			<span class="cp-copied-text"><?php _e('Copied to clipboard!', 'campaignpress'); ?></span>
		</div>
		<?php
	}

	/**
	 * Render icons grid
	 */
	private function render_icons_grid($category = 'all', $style = 'outline') {
		$icons = isset($this->icon_categories[$category]['icons']) ? $this->icon_categories[$category]['icons'] : $this->icon_categories['all']['icons'];

		foreach ($icons as $icon) {
			$icon_svg = campaignpress_get_heroicon($icon, $style, array('aria-hidden' => 'true'));
			if (!empty($icon_svg)) {
				?>
				<div class="cp-icon-item" data-icon="<?php echo esc_attr($icon); ?>" data-style="<?php echo esc_attr($style); ?>">
					<div class="cp-icon-preview">
						<?php echo $icon_svg; ?>
					</div>
					<div class="cp-icon-name"><?php echo esc_html($icon); ?></div>
					<button class="cp-icon-copy-btn" data-icon="<?php echo esc_attr($icon); ?>" data-style="<?php echo esc_attr($style); ?>">
						<?php _e('Copy', 'campaignpress'); ?>
					</button>
				</div>
				<?php
			}
		}
	}

	/**
	 * AJAX: Search icons
	 */
	public function ajax_search_icons() {
		check_ajax_referer('cp_icons_browser', 'nonce');

		$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
		$style = isset($_POST['style']) ? sanitize_text_field($_POST['style']) : 'outline';
		$all_icons = $this->get_all_icon_names();

		$results = array();
		foreach ($all_icons as $icon) {
			if (empty($search) || stripos($icon, $search) !== false) {
				$icon_svg = campaignpress_get_heroicon($icon, $style, array('aria-hidden' => 'true'));
				if (!empty($icon_svg)) {
					$results[] = array(
						'name' => $icon,
						'svg' => $icon_svg,
					);
				}
			}
		}

		wp_send_json_success($results);
	}

	/**
	 * AJAX: Get icon SVG
	 */
	public function ajax_get_icon_svg() {
		check_ajax_referer('cp_icons_browser', 'nonce');

		$icon = isset($_POST['icon']) ? sanitize_text_field($_POST['icon']) : '';
		$style = isset($_POST['style']) ? sanitize_text_field($_POST['style']) : 'outline';

		if (empty($icon)) {
			wp_send_json_error(__('Icon name is required', 'campaignpress'));
		}

		$icon_svg = campaignpress_get_heroicon($icon, $style, array('aria-hidden' => 'true'));

		if (empty($icon_svg)) {
			wp_send_json_error(__('Icon not found', 'campaignpress'));
		}

		wp_send_json_success(array(
			'svg' => $icon_svg,
			'php_code' => "<?php echo campaignpress_get_heroicon('{$icon}', '{$style}'); ?>",
		));
	}

	/**
	 * AJAX: Get icons by category
	 */
	public function ajax_get_icons_by_category() {
		check_ajax_referer('cp_icons_browser', 'nonce');

		$category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'all';
		$style = isset($_POST['style']) ? sanitize_text_field($_POST['style']) : 'outline';

		$icons = isset($this->icon_categories[$category]['icons']) ? $this->icon_categories[$category]['icons'] : $this->icon_categories['all']['icons'];

		$results = array();
		foreach ($icons as $icon) {
			$icon_svg = campaignpress_get_heroicon($icon, $style, array('aria-hidden' => 'true'));
			if (!empty($icon_svg)) {
				$results[] = array(
					'name' => $icon,
					'svg' => $icon_svg,
				);
			}
		}

		wp_send_json_success($results);
	}
}

// Initialize
new CP_Icons_Browser();
