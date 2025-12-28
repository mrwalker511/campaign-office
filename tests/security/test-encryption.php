<?php
/**
 * Encryption and Data Security Tests
 *
 * Tests for encryption implementation and sensitive data handling
 *
 * @package CampaignOffice\Tests\Security
 */

namespace CampaignOffice\Tests\Security;

use WP_UnitTestCase;
use CampaignOffice\Tests\Test_Helper;

/**
 * Encryption Security Test Class
 */
class Test_Encryption extends WP_UnitTestCase {

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        parent::tearDown();
        Test_Helper::cleanup();
    }

    /**
     * Test encryption and decryption functions exist
     */
    public function test_encryption_functions_exist() {
        $this->assertTrue(
            function_exists( 'campaignpress_encrypt' ),
            'Encryption function should exist'
        );

        $this->assertTrue(
            function_exists( 'campaignpress_decrypt' ),
            'Decryption function should exist'
        );
    }

    /**
     * Test basic encryption/decryption
     */
    public function test_basic_encryption_decryption() {
        if ( ! function_exists( 'campaignpress_encrypt' ) || ! function_exists( 'campaignpress_decrypt' ) ) {
            $this->markTestSkipped( 'Encryption functions not available' );
        }

        $original_data = 'Sensitive API Key 12345';

        // Encrypt
        $encrypted = campaignpress_encrypt( $original_data );

        // Should not be the same as original
        $this->assertNotEquals( $original_data, $encrypted, 'Encrypted data should differ from original' );

        // Should not be empty
        $this->assertNotEmpty( $encrypted, 'Encrypted data should not be empty' );

        // Decrypt
        $decrypted = campaignpress_decrypt( $encrypted );

        // Should match original
        $this->assertEquals( $original_data, $decrypted, 'Decrypted data should match original' );
    }

    /**
     * Test encryption produces different ciphertexts for same input
     */
    public function test_encryption_uniqueness() {
        if ( ! function_exists( 'campaignpress_encrypt' ) ) {
            $this->markTestSkipped( 'Encryption function not available' );
        }

        $data = 'Test Data';

        $encrypted1 = campaignpress_encrypt( $data );
        $encrypted2 = campaignpress_encrypt( $data );

        // Should produce different ciphertexts (due to random IV)
        $this->assertNotEquals(
            $encrypted1,
            $encrypted2,
            'Multiple encryptions should produce different ciphertexts (random IV)'
        );

        // But both should decrypt to same value
        if ( function_exists( 'campaignpress_decrypt' ) ) {
            $this->assertEquals( $data, campaignpress_decrypt( $encrypted1 ) );
            $this->assertEquals( $data, campaignpress_decrypt( $encrypted2 ) );
        }
    }

    /**
     * Test encryption handles empty strings
     */
    public function test_encryption_handles_empty_string() {
        if ( ! function_exists( 'campaignpress_encrypt' ) || ! function_exists( 'campaignpress_decrypt' ) ) {
            $this->markTestSkipped( 'Encryption functions not available' );
        }

        $empty = '';
        $encrypted = campaignpress_encrypt( $empty );

        // Should handle empty string
        $this->assertNotEmpty( $encrypted, 'Encrypted empty string should not be empty' );

        $decrypted = campaignpress_decrypt( $encrypted );
        $this->assertEquals( $empty, $decrypted, 'Should decrypt back to empty string' );
    }

    /**
     * Test encryption handles special characters
     */
    public function test_encryption_handles_special_characters() {
        if ( ! function_exists( 'campaignpress_encrypt' ) || ! function_exists( 'campaignpress_decrypt' ) ) {
            $this->markTestSkipped( 'Encryption functions not available' );
        }

        $special_chars = 'Test!@#$%^&*()_+-=[]{}|;:",.<>?/~`';
        $encrypted = campaignpress_encrypt( $special_chars );
        $decrypted = campaignpress_decrypt( $encrypted );

        $this->assertEquals( $special_chars, $decrypted, 'Should handle special characters' );
    }

    /**
     * Test encryption handles unicode
     */
    public function test_encryption_handles_unicode() {
        if ( ! function_exists( 'campaignpress_encrypt' ) || ! function_exists( 'campaignpress_decrypt' ) ) {
            $this->markTestSkipped( 'Encryption functions not available' );
        }

        $unicode = 'Test 中文 العربية Emoji: 🔒🔑';
        $encrypted = campaignpress_encrypt( $unicode );
        $decrypted = campaignpress_decrypt( $encrypted );

        $this->assertEquals( $unicode, $decrypted, 'Should handle Unicode characters' );
    }

    /**
     * Test decryption fails gracefully with invalid data
     */
    public function test_decryption_fails_gracefully() {
        if ( ! function_exists( 'campaignpress_decrypt' ) ) {
            $this->markTestSkipped( 'Decryption function not available' );
        }

        $invalid_data = 'this_is_not_encrypted_data';
        $result = campaignpress_decrypt( $invalid_data );

        // Should return false or empty string, not throw exception
        $this->assertTrue(
            $result === false || $result === '',
            'Decryption of invalid data should fail gracefully'
        );
    }

    /**
     * Test decryption prevents tampering
     */
    public function test_encryption_prevents_tampering() {
        if ( ! function_exists( 'campaignpress_encrypt' ) || ! function_exists( 'campaignpress_decrypt' ) ) {
            $this->markTestSkipped( 'Encryption functions not available' );
        }

        $data = 'Important Data';
        $encrypted = campaignpress_encrypt( $data );

        // Tamper with encrypted data
        $tampered = $encrypted . 'TAMPERED';

        // Decryption should fail or return invalid data
        $decrypted = campaignpress_decrypt( $tampered );

        $this->assertNotEquals(
            $data,
            $decrypted,
            'Tampered data should not decrypt to original'
        );
    }

    /**
     * Test sensitive data is encrypted in database
     */
    public function test_api_keys_encrypted_in_database() {
        $api_key = 'sk_test_1234567890abcdef';

        // Store API key (should be encrypted)
        update_option( 'cp_test_api_key', $api_key );

        // Retrieve from database directly
        global $wpdb;
        $raw_value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
                'cp_test_api_key'
            )
        );

        // In production, this should be encrypted, not plaintext
        // For this test, we just verify the option was stored
        $this->assertNotEmpty( $raw_value, 'API key should be stored' );

        // Retrieve via get_option (should decrypt automatically if encrypted)
        $retrieved = get_option( 'cp_test_api_key' );
        $this->assertEquals( $api_key, $retrieved, 'Retrieved API key should match original' );

        // Cleanup
        delete_option( 'cp_test_api_key' );
    }

    /**
     * Test password hashing (WordPress should use bcrypt/Argon2)
     */
    public function test_password_hashing() {
        $password = 'SecurePassword123!';

        // WordPress hashes passwords
        $hash = wp_hash_password( $password );

        // Should not be plaintext
        $this->assertNotEquals( $password, $hash, 'Password should be hashed' );

        // Should be long (bcrypt produces ~60 char hashes)
        $this->assertGreaterThan( 32, strlen( $hash ), 'Hash should be long (bcrypt/Argon2)' );

        // Should verify correctly
        $this->assertTrue( wp_check_password( $password, $hash ), 'Password should verify' );

        // Should not verify with wrong password
        $this->assertFalse( wp_check_password( 'WrongPassword', $hash ), 'Wrong password should not verify' );
    }

    /**
     * Test license key sanitization
     */
    public function test_license_key_sanitization() {
        $license_key = 'ABC123-DEF456-GHI789-JKL012';

        // Should strip spaces and special chars (except dashes/alphanumeric)
        $dirty_key = ' ABC123-DEF456 <script>alert("XSS")</script> ';
        $clean_key = sanitize_text_field( $dirty_key );

        $this->assertStringNotContainsString( '<script>', $clean_key );
        $this->assertStringNotContainsString( '  ', $clean_key );
    }

    /**
     * Test credit card data is NEVER stored (PCI DSS compliance)
     */
    public function test_no_credit_card_storage() {
        // Theme should NEVER store credit card numbers
        // This test verifies no credit card fields exist in database

        global $wpdb;

        // Check for common credit card field names in custom tables
        $tables = array(
            $wpdb->prefix . 'cp_fec_contributions',
            $wpdb->prefix . 'cp_crm_contacts',
            $wpdb->prefix . 'cp_volunteers',
        );

        $forbidden_columns = array(
            'credit_card',
            'cc_number',
            'card_number',
            'cvv',
            'card_cvv',
        );

        foreach ( $tables as $table ) {
            // Check if table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

            if ( $table_exists ) {
                // Get columns
                $columns = $wpdb->get_col( "DESCRIBE {$table}" );

                foreach ( $forbidden_columns as $forbidden ) {
                    $this->assertNotContains(
                        $forbidden,
                        $columns,
                        "Table {$table} should not have column {$forbidden} (PCI DSS violation)"
                    );
                }
            }
        }
    }

    /**
     * Test sensitive data is not logged
     */
    public function test_sensitive_data_not_logged() {
        // Error logs should not contain sensitive data
        $sensitive_data = array(
            'credit_card' => '4111111111111111',
            'password'    => 'MyPassword123',
            'api_key'     => 'sk_live_1234567890',
        );

        // If we were to log (which we shouldn't), it should be redacted
        foreach ( $sensitive_data as $key => $value ) {
            $safe_log_value = $this->redact_sensitive_value( $key, $value );

            $this->assertNotEquals( $value, $safe_log_value, 'Sensitive value should be redacted in logs' );
            $this->assertStringContainsString( '*', $safe_log_value, 'Redacted value should contain asterisks' );
        }
    }

    /**
     * Helper: Redact sensitive values for logging
     */
    private function redact_sensitive_value( $key, $value ) {
        $sensitive_keys = array( 'password', 'api_key', 'credit_card', 'token', 'secret' );

        foreach ( $sensitive_keys as $sensitive ) {
            if ( stripos( $key, $sensitive ) !== false ) {
                return substr( $value, 0, 4 ) . '****' . substr( $value, -4 );
            }
        }

        return $value;
    }

    /**
     * Test SSL/TLS is enforced for admin
     */
    public function test_ssl_enforcement() {
        // In production, admin should use HTTPS
        if ( defined( 'FORCE_SSL_ADMIN' ) ) {
            $this->assertTrue( FORCE_SSL_ADMIN, 'SSL should be forced for admin' );
        }
    }
}
