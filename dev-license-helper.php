<?php
/**
 * Development License Testing Helper
 *
 * This file provides mock license server responses for testing the license
 * system during development without needing an actual license server.
 *
 * @package CampaignPress
 * @since 2.1.0
 *
 * USAGE:
 * 1. Include this file in your wp-config.php (development only!)
 * 2. Use the test license keys provided below
 * 3. Test license activation/deactivation flows
 *
 * Add to wp-config.php (before "That's all, stop editing!"):
 *   require_once __DIR__ . '/wp-content/themes/campaign-office/dev-license-helper.php';
 */

if (!defined('ABSPATH')) {
	exit;
}

// Force development mode when this file is included
if (!defined('CAMPAIGNPRESS_DEV_MODE')) {
	define('CAMPAIGNPRESS_DEV_MODE', true);
}

// Only run in development environments
if (!defined('CAMPAIGNPRESS_DEV_MODE') || !CAMPAIGNPRESS_DEV_MODE) {
	return;
}

/**
 * Enable mock license server mode
 *
 * This filter tells the premium system that a mock license server is available,
 * allowing license validation to proceed without a real license server configured.
 */
add_filter('campaignpress_mock_license_server_enabled', '__return_true');

/**
 * TEST LICENSE KEYS
 *
 * These are valid test keys for different license tiers.
 * Use these in the License activation page during testing.
 *
 * Professional: CP-DEV-PROFESSIONAL-2024-X1Y2Z3W4V5U6
 * Email: dev@campaignpress.test
 */
define('CP_TEST_LICENSE_FREE', 'CP-DEV-FREE-2024-F1R2E3E4EEEE');
define('CP_TEST_LICENSE_PROFESSIONAL', 'CP-DEV-PROFESSIONAL-2024-X1Y2Z3W4V5U6');
define('CP_TEST_LICENSE_EXPIRED', 'CP-DEV-EXPIRED-2024-M1N2O3P4Q5R6');
define('CP_TEST_LICENSE_INVALID', 'CP-DEV-INVALID-2024-FAKEFAKEFAKE');

// Test email for all license keys
define('CP_TEST_LICENSE_EMAIL', 'dev@campaignpress.test');

/**
 * Mock License Server Response
 *
 * Intercepts license validation requests and returns mock responses
 * based on the test license keys above.
 */
add_filter('pre_http_request', function($preempt, $args, $url) {
	// Only intercept license server requests
	if (strpos($url, '/validate') === false && strpos($url, '/deactivate') === false) {
		return $preempt;
	}

	// Get the license key from the request
	$license_key = isset($args['body']['license_key']) ? $args['body']['license_key'] : '';
	$email = isset($args['body']['email']) ? $args['body']['email'] : '';

	// Validation endpoint
	if (strpos($url, '/validate') !== false) {
		return wp_remote_retrieve_mock_response($license_key, $email);
	}

	// Deactivation endpoint
	if (strpos($url, '/deactivate') !== false) {
		return array(
			'response' => array(
				'code' => 200,
				'message' => 'OK',
			),
			'body' => json_encode(array(
				'success' => true,
				'message' => 'License deactivated successfully (mock)',
			)),
		);
	}

	return $preempt;
}, 10, 3);

/**
 * Get mock license server response
 *
 * @param string $license_key License key being validated
 * @param string $email Email address
 * @return array Mock HTTP response
 */
function wp_remote_retrieve_mock_response($license_key, $email) {
	// Free License
	if ($license_key === CP_TEST_LICENSE_FREE) {
		return array(
			'response' => array(
				'code' => 200,
				'message' => 'OK',
			),
			'body' => json_encode(array(
				'success' => true,
				'message' => 'License validated successfully',
				'data' => array(
					'license_key' => $license_key,
					'license_type' => 'free',
					'email' => $email,
					'expiry_date' => date('Y-m-d', strtotime('+100 years')),
					'site_limit' => 1,
					'sites_active' => 1,
					'customer_name' => 'Test User (Free)',
					'features' => array(
						'basic_templates',
					),
				),
			)),
		);
	}

	// Professional License
	if ($license_key === CP_TEST_LICENSE_PROFESSIONAL) {
		return array(
			'response' => array(
				'code' => 200,
				'message' => 'OK',
			),
			'body' => json_encode(array(
				'success' => true,
				'message' => 'License validated successfully',
				'data' => array(
					'license_key' => $license_key,
					'license_type' => 'professional',
					'email' => $email,
					'expiry_date' => date('Y-m-d', strtotime('+1 year')),
					'site_limit' => 5,
					'sites_active' => 1,
					'customer_name' => 'Test User (Professional)',
					'features' => array(
						'premium_templates',
						'custom_blocks',
						'email_integration',
						'advanced_analytics',
						'donor_management',
						'volunteer_portal',
						'compliance_tools',
						'field_operations',
						'auto_updates',
					),
				),
			)),
		);
	}

	// Expired License
	if ($license_key === CP_TEST_LICENSE_EXPIRED) {
		return array(
			'response' => array(
				'code' => 200,
				'message' => 'OK',
			),
			'body' => json_encode(array(
				'success' => false,
				'message' => 'License has expired',
				'data' => array(
					'license_key' => $license_key,
					'license_type' => 'professional',
					'email' => $email,
					'expiry_date' => date('Y-m-d', strtotime('-30 days')),
					'expired' => true,
				),
			)),
		);
	}

	// Invalid/Unknown License
	return array(
		'response' => array(
			'code' => 200,
			'message' => 'OK',
		),
		'body' => json_encode(array(
			'success' => false,
			'message' => 'Invalid license key or email address',
		)),
	);
}

/**
 * Add development notice to admin
 */
add_action('admin_notices', function() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$screen = get_current_screen();
	if ($screen && $screen->id === 'appearance_page_campaignpress-license') {
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php _e('Development Mode Active', 'campaign-office'); ?></strong><br>
				<?php _e('Mock license server is active. Use test license keys from dev-license-helper.php', 'campaign-office'); ?>
			</p>
			<p style="font-family: monospace; font-size: 0.9em;">
				<strong>Test License Keys:</strong><br>
				Free: <?php echo esc_html(CP_TEST_LICENSE_FREE); ?><br>
				Professional: <?php echo esc_html(CP_TEST_LICENSE_PROFESSIONAL); ?><br>
				Email: <?php echo esc_html(CP_TEST_LICENSE_EMAIL); ?>
			</p>
		</div>
		<?php
	}
});

// Log that mock license server is active
if (defined('WP_DEBUG') && WP_DEBUG) {
	error_log('[CampaignPress] Mock license server active for testing');
}
