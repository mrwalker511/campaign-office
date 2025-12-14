<?php
/**
 * Analytics Dummy Data Generator
 * 
 * Creates realistic test data for the analytics module
 * 
 * @package CampaignPress
 * @subpackage Analytics
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate dummy analytics data
 */
function campaignpress_generate_analytics_dummy_data() {
    global $wpdb;
    
    // Table names
    $donations_table = $wpdb->prefix . 'campaignpress_donations';
    $contacts_table = $wpdb->prefix . 'campaignpress_contacts';
    $volunteers_table = $wpdb->prefix . 'campaignpress_volunteers';
    $activities_table = $wpdb->prefix . 'campaignpress_volunteer_activities';
    
    // Check if tables exist, create if not
    campaignpress_create_analytics_tables();
    
    // Clear existing dummy data
    $wpdb->query("DELETE FROM {$donations_table} WHERE contact_id BETWEEN 1000 AND 1200");
    $wpdb->query("DELETE FROM {$contacts_table} WHERE id BETWEEN 1000 AND 1200");
    $wpdb->query("DELETE FROM {$volunteers_table} WHERE contact_id BETWEEN 1000 AND 1200");
    $wpdb->query("DELETE FROM {$activities_table} WHERE volunteer_id BETWEEN 1000 AND 1200");
    
    // Generate contacts (100 contacts)
    $contact_ids = array();
    for ($i = 0; $i < 100; $i++) {
        $contact_id = 1000 + $i;
        $first_names = array('John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa', 'James', 'Mary');
        $last_names = array('Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez');
        
        $wpdb->insert($contacts_table, array(
            'id' => $contact_id,
            'first_name' => $first_names[array_rand($first_names)],
            'last_name' => $last_names[array_rand($last_names)],
            'email' => 'contact' . $contact_id . '@example.com',
            'phone' => '555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
            'city' => array('New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix')[array_rand(array('New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'))],
            'state' => array('NY', 'CA', 'IL', 'TX', 'AZ')[array_rand(array('NY', 'CA', 'IL', 'TX', 'AZ'))],
            'zip' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
            'engagement_score' => rand(0, 100),
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 365) . ' days')),
        ));
        
        $contact_ids[] = $contact_id;
    }
    
    // Generate donations (200 donations over last 90 days)
    for ($i = 0; $i < 200; $i++) {
        $days_ago = rand(0, 90);
        $amounts = array(25, 50, 75, 100, 150, 200, 250, 500, 1000, 2500);
        $sources = array('website', 'event', 'email', 'phone', 'mail', 'social_media');
        
        $wpdb->insert($donations_table, array(
            'contact_id' => $contact_ids[array_rand($contact_ids)],
            'amount' => $amounts[array_rand($amounts)],
            'donation_date' => date('Y-m-d H:i:s', strtotime("-{$days_ago} days")),
            'source' => $sources[array_rand($sources)],
            'status' => 'completed',
            'payment_method' => array('credit_card', 'paypal', 'check', 'cash')[array_rand(array('credit_card', 'paypal', 'check', 'cash'))],
            'recurring' => rand(0, 100) > 70 ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s', strtotime("-{$days_ago} days")),
        ));
    }
    
    // Generate volunteers (50 volunteers)
    $volunteer_ids = array();
    for ($i = 0; $i < 50; $i++) {
        $contact_id = $contact_ids[array_rand($contact_ids)];
        $volunteer_id = 1000 + $i;
        
        $wpdb->insert($volunteers_table, array(
            'id' => $volunteer_id,
            'contact_id' => $contact_id,
            'status' => array('active', 'active', 'active', 'inactive')[array_rand(array('active', 'active', 'active', 'inactive'))],
            'skills' => json_encode(array('canvassing', 'phone_banking', 'data_entry')),
            'availability' => json_encode(array('weekdays', 'weekends')),
            'total_hours' => rand(5, 100),
            'joined_date' => date('Y-m-d', strtotime('-' . rand(30, 180) . ' days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(30, 180) . ' days')),
        ));
        
        $volunteer_ids[] = $volunteer_id;
    }
    
    // Generate volunteer activities (300 activities)
    for ($i = 0; $i < 300; $i++) {
        $days_ago = rand(0, 90);
        $activity_types = array('canvassing', 'phone_banking', 'data_entry', 'event_setup', 'office_work');
        $hours = array(2, 3, 4, 5, 6, 8);
        
        $wpdb->insert($activities_table, array(
            'volunteer_id' => $volunteer_ids[array_rand($volunteer_ids)],
            'activity_type' => $activity_types[array_rand($activity_types)],
            'activity_date' => date('Y-m-d', strtotime("-{$days_ago} days")),
            'hours' => $hours[array_rand($hours)],
            'notes' => 'Completed ' . $activity_types[array_rand($activity_types)] . ' activity',
            'created_at' => date('Y-m-d H:i:s', strtotime("-{$days_ago} days")),
        ));
    }
    
    return array(
        'contacts' => count($contact_ids),
        'donations' => 200,
        'volunteers' => count($volunteer_ids),
        'activities' => 300,
    );
}

/**
 * Create analytics database tables if they don't exist
 */
function campaignpress_create_analytics_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Contacts table
    $contacts_table = $wpdb->prefix . 'campaignpress_contacts';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$contacts_table}'") != $contacts_table) {
        $sql = "CREATE TABLE {$contacts_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            address varchar(255) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(50) DEFAULT NULL,
            zip varchar(20) DEFAULT NULL,
            engagement_score int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY engagement_score (engagement_score)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Donations table
    $donations_table = $wpdb->prefix . 'campaignpress_donations';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$donations_table}'") != $donations_table) {
        $sql = "CREATE TABLE {$donations_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) NOT NULL,
            amount decimal(10,2) NOT NULL,
            donation_date datetime NOT NULL,
            source varchar(50) DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            payment_method varchar(50) DEFAULT NULL,
            recurring tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY donation_date (donation_date),
            KEY status (status),
            KEY source (source)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Volunteers table
    $volunteers_table = $wpdb->prefix . 'campaignpress_volunteers';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$volunteers_table}'") != $volunteers_table) {
        $sql = "CREATE TABLE {$volunteers_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) NOT NULL,
            status varchar(20) DEFAULT 'active',
            skills text DEFAULT NULL,
            availability text DEFAULT NULL,
            total_hours int(11) DEFAULT 0,
            joined_date date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Volunteer activities table
    $activities_table = $wpdb->prefix . 'campaignpress_volunteer_activities';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$activities_table}'") != $activities_table) {
        $sql = "CREATE TABLE {$activities_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            volunteer_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            activity_date date NOT NULL,
            hours decimal(5,2) DEFAULT 0,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY volunteer_id (volunteer_id),
            KEY activity_date (activity_date),
            KEY activity_type (activity_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

/**
 * AJAX handler to generate dummy data
 */
function campaignpress_ajax_generate_dummy_data() {
    check_ajax_referer('cp_premium_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
    }
    
    $result = campaignpress_generate_analytics_dummy_data();
    
    wp_send_json_success(array(
        'message' => 'Dummy data generated successfully!',
        'data' => $result,
    ));
}
add_action('wp_ajax_cp_generate_dummy_data', 'campaignpress_ajax_generate_dummy_data');

/**
 * Add admin menu item for data generation
 */
function campaignpress_add_dummy_data_menu() {
    add_submenu_page(
        'campaignpress-analytics',
        'Generate Test Data',
        'Test Data',
        'manage_options',
        'campaignpress-test-data',
        'campaignpress_render_test_data_page'
    );
}
add_action('admin_menu', 'campaignpress_add_dummy_data_menu', 100);

/**
 * Render test data page
 */
function campaignpress_render_test_data_page() {
    ?>
    <div class="wrap">
        <h1>Generate Analytics Test Data</h1>
        <p>Click the button below to generate dummy analytics data for testing purposes.</p>
        
        <div class="card" style="max-width: 600px;">
            <h2>What will be created:</h2>
            <ul>
                <li><strong>100 Contacts</strong> - Sample contact records</li>
                <li><strong>200 Donations</strong> - Donations over the last 90 days ($25-$2,500)</li>
                <li><strong>50 Volunteers</strong> - Active and inactive volunteers</li>
                <li><strong>300 Activities</strong> - Volunteer activities over 90 days</li>
            </ul>
            
            <p><button id="generate-dummy-data" class="button button-primary button-large">Generate Test Data</button></p>
            
            <div id="generation-result" style="margin-top: 20px;"></div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#generate-dummy-data').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Generating...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cp_generate_dummy_data',
                    nonce: '<?php echo wp_create_nonce('cp_premium_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#generation-result').html(
                            '<div class="notice notice-success"><p><strong>Success!</strong> ' + response.data.message + '</p>' +
                            '<ul>' +
                            '<li>Contacts: ' + response.data.data.contacts + '</li>' +
                            '<li>Donations: ' + response.data.data.donations + '</li>' +
                            '<li>Volunteers: ' + response.data.data.volunteers + '</li>' +
                            '<li>Activities: ' + response.data.data.activities + '</li>' +
                            '</ul></div>'
                        );
                    } else {
                        $('#generation-result').html(
                            '<div class="notice notice-error"><p>' + response.data.message + '</p></div>'
                        );
                    }
                    $btn.prop('disabled', false).text('Generate Test Data');
                },
                error: function() {
                    $('#generation-result').html(
                        '<div class="notice notice-error"><p>An error occurred while generating data.</p></div>'
                    );
                    $btn.prop('disabled', false).text('Generate Test Data');
                }
            });
        });
    });
    </script>
    <?php
}
