<?php
/**
 * Premium Demo Content Generator
 *
 * Provides sample content for testing CampaignPress Premium features including
 * CRM contacts, field operations data, and FEC compliance records.
 *
 * @package CampaignPress
 * @subpackage Premium
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CampaignPress_Premium_Demo_Content {

    /**
     * Instance
     *
     * @var CampaignPress_Premium_Demo_Content
     */
    private static $instance = null;

    /**
     * Sample first names
     *
     * @var array
     */
    private $first_names = array(
        'James', 'Mary', 'Robert', 'Patricia', 'John', 'Jennifer', 'Michael', 'Linda',
        'David', 'Elizabeth', 'William', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica',
        'Thomas', 'Sarah', 'Christopher', 'Karen', 'Charles', 'Lisa', 'Daniel', 'Nancy',
        'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley',
        'Steven', 'Kimberly', 'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle',
        'Kenneth', 'Dorothy', 'Kevin', 'Carol', 'Brian', 'Amanda', 'George', 'Melissa',
        'Timothy', 'Deborah', 'Ronald', 'Stephanie', 'Edward', 'Rebecca', 'Jason', 'Sharon',
        'Jeffrey', 'Laura', 'Ryan', 'Cynthia', 'Jacob', 'Kathleen', 'Gary', 'Amy',
        'Nicholas', 'Angela', 'Eric', 'Shirley', 'Jonathan', 'Anna', 'Stephen', 'Brenda'
    );

    /**
     * Sample last names
     *
     * @var array
     */
    private $last_names = array(
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
        'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill',
        'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell',
        'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz'
    );

    /**
     * Sample street names
     *
     * @var array
     */
    private $street_names = array(
        'Main Street', 'Oak Avenue', 'Maple Drive', 'Cedar Lane', 'Pine Street',
        'Elm Court', 'Washington Boulevard', 'Lincoln Avenue', 'Park Place',
        'Lake View Drive', 'Highland Road', 'Forest Way', 'River Road', 'Valley Drive',
        'Sunset Boulevard', 'Spring Street', 'Mill Road', 'Church Street', 'School Lane',
        'Market Street', 'High Street', 'Union Avenue', 'Center Street', 'North Road'
    );

    /**
     * Sample cities (for Springfield, IL area)
     *
     * @var array
     */
    private $cities = array(
        array('city' => 'Springfield', 'state' => 'IL', 'zip' => '62701'),
        array('city' => 'Springfield', 'state' => 'IL', 'zip' => '62702'),
        array('city' => 'Springfield', 'state' => 'IL', 'zip' => '62703'),
        array('city' => 'Springfield', 'state' => 'IL', 'zip' => '62704'),
        array('city' => 'Springfield', 'state' => 'IL', 'zip' => '62707'),
        array('city' => 'Chatham', 'state' => 'IL', 'zip' => '62629'),
        array('city' => 'Rochester', 'state' => 'IL', 'zip' => '62563'),
        array('city' => 'Sherman', 'state' => 'IL', 'zip' => '62684'),
        array('city' => 'Auburn', 'state' => 'IL', 'zip' => '62615'),
        array('city' => 'Riverton', 'state' => 'IL', 'zip' => '62561'),
    );

    /**
     * Interaction types
     *
     * @var array
     */
    private $interaction_types = array('call', 'text', 'email', 'door_knock', 'event', 'donation', 'volunteer');

    /**
     * Interaction outcomes
     *
     * @var array
     */
    private $interaction_outcomes = array(
        'call' => array('answered', 'voicemail', 'no_answer', 'busy', 'wrong_number'),
        'door_knock' => array('answered', 'not_home', 'refused', 'moved'),
        'email' => array('sent', 'opened', 'clicked', 'bounced'),
        'text' => array('delivered', 'replied', 'opted_out'),
        'event' => array('rsvp_yes', 'rsvp_no', 'attended', 'no_show'),
        'donation' => array('completed', 'pending', 'failed'),
        'volunteer' => array('signed_up', 'confirmed', 'completed', 'cancelled'),
    );

    /**
     * Party affiliations
     *
     * @var array
     */
    private $parties = array('Democrat', 'Republican', 'Independent', 'Unaffiliated', 'Green', 'Libertarian');

    /**
     * Voter statuses
     *
     * @var array
     */
    private $voter_statuses = array('Active', 'Active', 'Active', 'Inactive', 'Suspended');

    /**
     * Get singleton instance
     *
     * @return CampaignPress_Premium_Demo_Content
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 100);
        add_action('admin_post_cp_import_premium_demo', array($this, 'handle_import'));
        add_action('admin_post_cp_delete_premium_demo', array($this, 'handle_delete'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Only show if premium is active
        if (!class_exists('CampaignPress_Premium')) {
            return;
        }

        add_submenu_page(
            'campaignpress-pro',
            __('Premium Demo Content', 'campaignpress'),
            __('Demo Content', 'campaignpress'),
            'manage_options',
            'cp-premium-demo',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $demo_exists = get_option('campaignpress_premium_demo_imported', false);
        $crm_active = class_exists('CampaignPress_CRM_Init');
        $field_ops_active = class_exists('CP_Field_Operations_Init');
        $compliance_active = class_exists('CampaignPress_Compliance_Init');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('CampaignPress Premium Demo Content', 'campaignpress'); ?></h1>

            <?php if (!empty($_GET['imported']) && sanitize_key($_GET['imported']) === '1') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Premium demo content imported successfully!', 'campaignpress'); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['deleted']) && sanitize_key($_GET['deleted']) === '1') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Premium demo content deleted successfully!', 'campaignpress'); ?></p>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 900px;">
                <h2><?php esc_html_e('Premium Feature Demo Data', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('Generate realistic sample data to test and demonstrate CampaignPress Premium features.', 'campaignpress'); ?></p>

                <h3><?php esc_html_e('Content to be created:', 'campaignpress'); ?></h3>

                <table class="widefat" style="margin: 15px 0;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Module', 'campaignpress'); ?></th>
                            <th><?php esc_html_e('Demo Data', 'campaignpress'); ?></th>
                            <th><?php esc_html_e('Status', 'campaignpress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('CRM', 'campaignpress'); ?></strong></td>
                            <td>
                                <?php esc_html_e('500 sample contacts with voter data', 'campaignpress'); ?><br>
                                <?php esc_html_e('1,000+ interaction records', 'campaignpress'); ?><br>
                                <?php esc_html_e('15 tags (voter issues, demographics)', 'campaignpress'); ?><br>
                                <?php esc_html_e('5 smart segments', 'campaignpress'); ?><br>
                                <?php esc_html_e('Engagement scores calculated', 'campaignpress'); ?>
                            </td>
                            <td>
                                <?php if ($crm_active) : ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e('Active', 'campaignpress'); ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-warning" style="color: #ffb900;"></span> <?php esc_html_e('Module not active', 'campaignpress'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('Field Operations', 'campaignpress'); ?></strong></td>
                            <td>
                                <?php esc_html_e('10 canvassing turfs with boundaries', 'campaignpress'); ?><br>
                                <?php esc_html_e('5 phone banking campaigns', 'campaignpress'); ?><br>
                                <?php esc_html_e('3 GOTV target lists', 'campaignpress'); ?><br>
                                <?php esc_html_e('50 volunteer shift assignments', 'campaignpress'); ?>
                            </td>
                            <td>
                                <?php if ($field_ops_active) : ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e('Active', 'campaignpress'); ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-warning" style="color: #ffb900;"></span> <?php esc_html_e('Module not active', 'campaignpress'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php esc_html_e('FEC Compliance', 'campaignpress'); ?></strong></td>
                            <td>
                                <?php esc_html_e('100 sample contributions ($25 - $2,900)', 'campaignpress'); ?><br>
                                <?php esc_html_e('75 unique donors with employer data', 'campaignpress'); ?><br>
                                <?php esc_html_e('Quarterly report data', 'campaignpress'); ?><br>
                                <?php esc_html_e('Audit trail entries', 'campaignpress'); ?>
                            </td>
                            <td>
                                <?php if ($compliance_active) : ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e('Active', 'campaignpress'); ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-warning" style="color: #ffb900;"></span> <?php esc_html_e('Module not active', 'campaignpress'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="notice notice-info inline" style="margin: 15px 0;">
                    <p><strong><?php esc_html_e('Note:', 'campaignpress'); ?></strong> <?php esc_html_e('Only active modules will receive demo data. Enable modules in Features settings first.', 'campaignpress'); ?></p>
                </div>

                <?php if (!$demo_exists) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="cp_import_premium_demo">
                        <?php wp_nonce_field('cp_import_premium_demo', 'cp_premium_demo_nonce'); ?>
                        <p>
                            <button type="submit" class="button button-primary button-hero">
                                <?php esc_html_e('Generate Premium Demo Data', 'campaignpress'); ?>
                            </button>
                        </p>
                        <p class="description">
                            <?php esc_html_e('This creates sample data in premium module tables. You can delete it anytime.', 'campaignpress'); ?>
                        </p>
                    </form>
                <?php else : ?>
                    <div class="notice notice-success inline" style="margin: 15px 0;">
                        <p><?php esc_html_e('Premium demo data is currently installed.', 'campaignpress'); ?></p>
                    </div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="cp-delete-premium-demo-form">
                        <input type="hidden" name="action" value="cp_delete_premium_demo">
                        <?php wp_nonce_field('cp_delete_premium_demo', 'cp_premium_demo_nonce'); ?>
                        <p>
                            <button type="submit" class="button button-secondary">
                                <?php esc_html_e('Delete Premium Demo Data', 'campaignpress'); ?>
                            </button>
                        </p>
                    </form>
                    <script>
                    document.getElementById('cp-delete-premium-demo-form').addEventListener('submit', function(e) {
                        if (!confirm('<?php echo esc_js(__('Are you sure you want to delete all premium demo data? This cannot be undone.', 'campaignpress')); ?>')) {
                            e.preventDefault();
                        }
                    });
                    </script>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle import
     */
    public function handle_import() {
        // Check nonce
        if (!isset($_POST['cp_premium_demo_nonce']) || !wp_verify_nonce($_POST['cp_premium_demo_nonce'], 'cp_import_premium_demo')) {
            wp_die(__('Security check failed', 'campaignpress'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'campaignpress'));
        }

        $demo_ids = array();

        // Import CRM data if module is active
        if (class_exists('CampaignPress_CRM_Init')) {
            $demo_ids['crm'] = $this->import_crm_data();
        }

        // Import Field Operations data if module is active
        if (class_exists('CP_Field_Operations_Init')) {
            $demo_ids['field_ops'] = $this->import_field_ops_data();
        }

        // Import Compliance data if module is active
        if (class_exists('CampaignPress_Compliance_Init')) {
            $demo_ids['compliance'] = $this->import_compliance_data();
        }

        // Save demo IDs for later deletion
        update_option('campaignpress_premium_demo_ids', $demo_ids);
        update_option('campaignpress_premium_demo_imported', true);

        // Redirect back
        wp_redirect(add_query_arg('imported', '1', wp_get_referer()));
        exit;
    }

    /**
     * Handle delete
     */
    public function handle_delete() {
        // Check nonce
        if (!isset($_POST['cp_premium_demo_nonce']) || !wp_verify_nonce($_POST['cp_premium_demo_nonce'], 'cp_delete_premium_demo')) {
            wp_die(__('Security check failed', 'campaignpress'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'campaignpress'));
        }

        global $wpdb;

        $demo_ids = get_option('campaignpress_premium_demo_ids', array());

        // Delete CRM demo data
        if (!empty($demo_ids['crm'])) {
            $this->delete_crm_data($demo_ids['crm']);
        }

        // Delete Field Ops demo data
        if (!empty($demo_ids['field_ops'])) {
            $this->delete_field_ops_data($demo_ids['field_ops']);
        }

        // Delete Compliance demo data
        if (!empty($demo_ids['compliance'])) {
            $this->delete_compliance_data($demo_ids['compliance']);
        }

        // Clean up options
        delete_option('campaignpress_premium_demo_ids');
        delete_option('campaignpress_premium_demo_imported');

        // Redirect back
        wp_redirect(add_query_arg('deleted', '1', wp_get_referer()));
        exit;
    }

    /**
     * Import CRM demo data
     *
     * @return array Array of created IDs
     */
    private function import_crm_data() {
        global $wpdb;

        $contact_ids = array();
        $interaction_ids = array();
        $tag_ids = array();
        $segment_ids = array();

        $contacts_table = $wpdb->prefix . 'cp_contacts';
        $interactions_table = $wpdb->prefix . 'cp_interactions';
        $tags_table = $wpdb->prefix . 'cp_tags';
        $contact_tags_table = $wpdb->prefix . 'cp_contact_tags';
        $segments_table = $wpdb->prefix . 'cp_segments';

        // Create tags first
        $tags = array(
            // Issue-based tags
            array('name' => 'Healthcare Priority', 'slug' => 'healthcare-priority', 'type' => 'issue'),
            array('name' => 'Education Focus', 'slug' => 'education-focus', 'type' => 'issue'),
            array('name' => 'Climate Activist', 'slug' => 'climate-activist', 'type' => 'issue'),
            array('name' => 'Economic Concerns', 'slug' => 'economic-concerns', 'type' => 'issue'),
            array('name' => 'Infrastructure', 'slug' => 'infrastructure', 'type' => 'issue'),
            // Support level tags
            array('name' => 'Strong Support', 'slug' => 'strong-support', 'type' => 'support'),
            array('name' => 'Lean Support', 'slug' => 'lean-support', 'type' => 'support'),
            array('name' => 'Undecided', 'slug' => 'undecided', 'type' => 'support'),
            array('name' => 'Lean Oppose', 'slug' => 'lean-oppose', 'type' => 'support'),
            // Activity tags
            array('name' => 'Super Voter', 'slug' => 'super-voter', 'type' => 'activity'),
            array('name' => 'Previous Donor', 'slug' => 'previous-donor', 'type' => 'activity'),
            array('name' => 'Volunteer Interest', 'slug' => 'volunteer-interest', 'type' => 'activity'),
            array('name' => 'Event Attendee', 'slug' => 'event-attendee', 'type' => 'activity'),
            // Demographics
            array('name' => 'Young Voter (18-35)', 'slug' => 'young-voter', 'type' => 'demographic'),
            array('name' => 'Senior (65+)', 'slug' => 'senior', 'type' => 'demographic'),
        );

        foreach ($tags as $tag) {
            $wpdb->insert($tags_table, array(
                'tag_name' => $tag['name'],
                'tag_slug' => $tag['slug'],
                'tag_type' => $tag['type'],
                'created_at' => current_time('mysql'),
            ));
            $tag_ids[] = $wpdb->insert_id;
        }

        // Create 500 sample contacts
        for ($i = 0; $i < 500; $i++) {
            $first_name = $this->first_names[array_rand($this->first_names)];
            $last_name = $this->last_names[array_rand($this->last_names)];
            $city_data = $this->cities[array_rand($this->cities)];
            $street_num = rand(100, 9999);
            $street = $this->street_names[array_rand($this->street_names)];
            $party = $this->parties[array_rand($this->parties)];
            $voter_status = $this->voter_statuses[array_rand($this->voter_statuses)];
            $birth_year = rand(1945, 2005);
            $age = date('Y') - $birth_year;

            // Generate realistic voter ID
            $voter_id = sprintf('IL%08d', rand(10000000, 99999999));

            // Generate phone and email
            $phone = sprintf('217-%03d-%04d', rand(100, 999), rand(1000, 9999));
            $email = strtolower($first_name . '.' . $last_name . rand(1, 99) . '@example.com');

            // Calculate voting propensity (higher for older, registered voters)
            $voting_propensity = min(100, max(20, $age / 2 + rand(10, 50)));

            $wpdb->insert($contacts_table, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'address_line_1' => $street_num . ' ' . $street,
                'city' => $city_data['city'],
                'state' => $city_data['state'],
                'zip_code' => $city_data['zip'],
                'party_affiliation' => $party,
                'voter_id' => $voter_id,
                'voter_status' => $voter_status,
                'birth_year' => $birth_year,
                'voting_propensity' => $voting_propensity,
                'engagement_score' => rand(10, 95),
                'source' => 'demo_import',
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ));

            $contact_id = $wpdb->insert_id;
            $contact_ids[] = $contact_id;

            // Assign 1-4 random tags to each contact
            $num_tags = rand(1, 4);
            $assigned_tags = array_rand($tag_ids, min($num_tags, count($tag_ids)));
            if (!is_array($assigned_tags)) {
                $assigned_tags = array($assigned_tags);
            }

            foreach ($assigned_tags as $tag_index) {
                $wpdb->insert($contact_tags_table, array(
                    'contact_id' => $contact_id,
                    'tag_id' => $tag_ids[$tag_index],
                    'created_at' => current_time('mysql'),
                ));
            }

            // Create 1-5 interactions for each contact
            $num_interactions = rand(1, 5);
            for ($j = 0; $j < $num_interactions; $j++) {
                $type = $this->interaction_types[array_rand($this->interaction_types)];
                $outcomes = $this->interaction_outcomes[$type];
                $outcome = $outcomes[array_rand($outcomes)];

                // Random date in the last 90 days
                $days_ago = rand(0, 90);
                $interaction_date = date('Y-m-d H:i:s', strtotime("-{$days_ago} days"));

                $notes = $this->generate_interaction_note($type, $outcome, $first_name);

                $wpdb->insert($interactions_table, array(
                    'contact_id' => $contact_id,
                    'interaction_type' => $type,
                    'outcome' => $outcome,
                    'notes' => $notes,
                    'user_id' => get_current_user_id(),
                    'interaction_date' => $interaction_date,
                    'is_demo' => 1,
                    'created_at' => current_time('mysql'),
                ));

                $interaction_ids[] = $wpdb->insert_id;
            }
        }

        // Create smart segments
        $segments = array(
            array(
                'name' => 'High-Value Supporters',
                'description' => 'Contacts with strong support and high engagement scores',
                'type' => 'dynamic',
                'criteria' => json_encode(array(
                    'tags' => array('strong-support'),
                    'engagement_score_min' => 70,
                )),
            ),
            array(
                'name' => 'Persuadable Voters',
                'description' => 'Undecided voters with moderate engagement',
                'type' => 'dynamic',
                'criteria' => json_encode(array(
                    'tags' => array('undecided'),
                    'voting_propensity_min' => 50,
                )),
            ),
            array(
                'name' => 'Volunteer Prospects',
                'description' => 'Supporters interested in volunteering',
                'type' => 'dynamic',
                'criteria' => json_encode(array(
                    'tags' => array('volunteer-interest', 'strong-support'),
                )),
            ),
            array(
                'name' => 'Young Democrat Voters',
                'description' => 'Democratic voters under 35',
                'type' => 'dynamic',
                'criteria' => json_encode(array(
                    'party' => 'Democrat',
                    'age_max' => 35,
                )),
            ),
            array(
                'name' => 'GOTV Priority List',
                'description' => 'High-propensity supporters for GOTV',
                'type' => 'dynamic',
                'criteria' => json_encode(array(
                    'tags' => array('strong-support', 'lean-support'),
                    'voting_propensity_min' => 60,
                )),
            ),
        );

        foreach ($segments as $segment) {
            $wpdb->insert($segments_table, array(
                'segment_name' => $segment['name'],
                'segment_description' => $segment['description'],
                'segment_type' => $segment['type'],
                'criteria' => $segment['criteria'],
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ));
            $segment_ids[] = $wpdb->insert_id;
        }

        return array(
            'contacts' => $contact_ids,
            'interactions' => $interaction_ids,
            'tags' => $tag_ids,
            'segments' => $segment_ids,
        );
    }

    /**
     * Generate a realistic interaction note
     *
     * @param string $type Interaction type
     * @param string $outcome Interaction outcome
     * @param string $name Contact first name
     * @return string
     */
    private function generate_interaction_note($type, $outcome, $name) {
        $notes = array(
            'call' => array(
                'answered' => array(
                    "{$name} is supportive of our healthcare position. Asked about volunteer opportunities.",
                    "Discussed education policy. {$name} has children in public schools and is very engaged.",
                    "Quick conversation - {$name} confirmed they're planning to vote for our candidate.",
                    "{$name} had questions about our climate policy. Sent follow-up email with details.",
                    "Great conversation! {$name} wants to host a house party.",
                ),
                'voicemail' => array(
                    "Left voicemail with event invitation details.",
                    "Left message asking for callback.",
                    "Voicemail box full, will try again.",
                ),
                'no_answer' => array(
                    "No answer, will retry tomorrow.",
                    "No answer after 5 rings.",
                ),
            ),
            'door_knock' => array(
                'answered' => array(
                    "{$name} is a strong supporter! Took yard sign and bumper sticker.",
                    "Undecided but engaged in conversation. Main concern is economy.",
                    "Brief conversation at door. Confirmed voter registration.",
                    "Great conversation about local issues. {$name} mentioned healthcare costs.",
                ),
                'not_home' => array(
                    "Not home, left door hanger.",
                    "Neighbor said they work late. Will try evening canvass.",
                ),
                'refused' => array(
                    "Politely declined to speak.",
                    "Not interested, marked for removal.",
                ),
            ),
            'email' => array(
                'opened' => array(
                    "Opened campaign newsletter.",
                    "Opened event invitation.",
                ),
                'clicked' => array(
                    "Clicked donation link but didn't complete.",
                    "Clicked through to volunteer signup.",
                ),
            ),
            'event' => array(
                'attended' => array(
                    "Attended town hall. Asked question about education funding.",
                    "Came to fundraiser BBQ with family.",
                    "Active participant at rally. Very enthusiastic!",
                ),
                'no_show' => array(
                    "RSVP'd but didn't attend. Follow up call scheduled.",
                ),
            ),
            'donation' => array(
                'completed' => array(
                    "Monthly recurring donor.",
                    "First-time donor, very excited about campaign.",
                    "Increased donation from last quarter.",
                ),
            ),
            'volunteer' => array(
                'completed' => array(
                    "Excellent canvasser! Completed full turf assignment.",
                    "Made 50 calls during phone bank shift.",
                    "Helped with event setup and stayed to clean up.",
                ),
            ),
        );

        if (isset($notes[$type][$outcome])) {
            $options = $notes[$type][$outcome];
            return $options[array_rand($options)];
        }

        return "Interaction recorded.";
    }

    /**
     * Import Field Operations demo data
     *
     * @return array Array of created IDs
     */
    private function import_field_ops_data() {
        global $wpdb;

        $turf_ids = array();
        $campaign_ids = array();
        $shift_ids = array();

        // Tables
        $turfs_table = $wpdb->prefix . 'cp_canvass_turfs';
        $phone_campaigns_table = $wpdb->prefix . 'cp_phone_campaigns';
        $gotv_lists_table = $wpdb->prefix . 'cp_gotv_lists';
        $shifts_table = $wpdb->prefix . 'cp_volunteer_shifts';

        // Check if tables exist before inserting
        $turfs_exists = $wpdb->get_var("SHOW TABLES LIKE '{$turfs_table}'") === $turfs_table;

        if ($turfs_exists) {
            // Create canvassing turfs
            $turfs = array(
                array('name' => 'Downtown Core', 'doors' => 245, 'priority' => 'high'),
                array('name' => 'University Area', 'doors' => 180, 'priority' => 'high'),
                array('name' => 'West Side Residential', 'doors' => 320, 'priority' => 'medium'),
                array('name' => 'East Springfield', 'doors' => 275, 'priority' => 'medium'),
                array('name' => 'North End', 'doors' => 198, 'priority' => 'medium'),
                array('name' => 'South Side', 'doors' => 342, 'priority' => 'high'),
                array('name' => 'Lakefront District', 'doors' => 156, 'priority' => 'low'),
                array('name' => 'Industrial Park Area', 'doors' => 89, 'priority' => 'low'),
                array('name' => 'Chatham Suburbs', 'doors' => 267, 'priority' => 'medium'),
                array('name' => 'Rochester Township', 'doors' => 134, 'priority' => 'low'),
            );

            foreach ($turfs as $turf) {
                $wpdb->insert($turfs_table, array(
                    'turf_name' => $turf['name'],
                    'total_doors' => $turf['doors'],
                    'doors_knocked' => rand(0, (int)($turf['doors'] * 0.6)),
                    'priority' => $turf['priority'],
                    'status' => rand(0, 1) ? 'active' : 'pending',
                    'is_demo' => 1,
                    'created_at' => current_time('mysql'),
                ));
                $turf_ids[] = $wpdb->insert_id;
            }
        }

        // Check for phone campaigns table
        $phone_exists = $wpdb->get_var("SHOW TABLES LIKE '{$phone_campaigns_table}'") === $phone_campaigns_table;

        if ($phone_exists) {
            // Create phone banking campaigns
            $phone_campaigns = array(
                array('name' => 'Voter ID Calls - Week 1', 'calls' => 2500, 'completed' => 1847),
                array('name' => 'Fundraising Appeal - Q4', 'calls' => 1000, 'completed' => 756),
                array('name' => 'Event RSVP Calls', 'calls' => 500, 'completed' => 423),
                array('name' => 'Volunteer Recruitment', 'calls' => 800, 'completed' => 512),
                array('name' => 'GOTV Reminder Calls', 'calls' => 3000, 'completed' => 0),
            );

            foreach ($phone_campaigns as $campaign) {
                $wpdb->insert($phone_campaigns_table, array(
                    'campaign_name' => $campaign['name'],
                    'total_calls' => $campaign['calls'],
                    'completed_calls' => $campaign['completed'],
                    'status' => $campaign['completed'] > 0 ? 'in_progress' : 'pending',
                    'is_demo' => 1,
                    'created_at' => current_time('mysql'),
                ));
                $campaign_ids[] = $wpdb->insert_id;
            }
        }

        // Check for GOTV table
        $gotv_exists = $wpdb->get_var("SHOW TABLES LIKE '{$gotv_lists_table}'") === $gotv_lists_table;

        if ($gotv_exists) {
            // Create GOTV lists
            $gotv_lists = array(
                array('name' => 'Super Voters - Must Contact', 'contacts' => 450),
                array('name' => 'Sporadic Voters - Persuasion', 'contacts' => 890),
                array('name' => 'New Registrants 2024', 'contacts' => 234),
            );

            foreach ($gotv_lists as $list) {
                $wpdb->insert($gotv_lists_table, array(
                    'list_name' => $list['name'],
                    'contact_count' => $list['contacts'],
                    'contacted' => rand(0, (int)($list['contacts'] * 0.3)),
                    'status' => 'active',
                    'is_demo' => 1,
                    'created_at' => current_time('mysql'),
                ));
            }
        }

        // Check for shifts table
        $shifts_exists = $wpdb->get_var("SHOW TABLES LIKE '{$shifts_table}'") === $shifts_table;

        if ($shifts_exists) {
            // Create volunteer shifts
            $shift_types = array('canvassing', 'phone_bank', 'event_support', 'office_help');
            $shift_times = array('09:00-12:00', '12:00-15:00', '15:00-18:00', '18:00-21:00');

            for ($i = 0; $i < 50; $i++) {
                $days_offset = rand(-7, 21);
                $shift_date = date('Y-m-d', strtotime("+{$days_offset} days"));
                $shift_type = $shift_types[array_rand($shift_types)];
                $shift_time = $shift_times[array_rand($shift_times)];

                $wpdb->insert($shifts_table, array(
                    'shift_date' => $shift_date,
                    'shift_time' => $shift_time,
                    'shift_type' => $shift_type,
                    'slots_total' => rand(3, 10),
                    'slots_filled' => rand(0, 8),
                    'location' => 'Campaign HQ - 123 Campaign Trail',
                    'is_demo' => 1,
                    'created_at' => current_time('mysql'),
                ));
                $shift_ids[] = $wpdb->insert_id;
            }
        }

        return array(
            'turfs' => $turf_ids,
            'campaigns' => $campaign_ids,
            'shifts' => $shift_ids,
        );
    }

    /**
     * Import Compliance demo data
     *
     * @return array Array of created IDs
     */
    private function import_compliance_data() {
        global $wpdb;

        $contribution_ids = array();
        $donor_ids = array();

        $contributions_table = $wpdb->prefix . 'cp_fec_contributions';
        $donors_table = $wpdb->prefix . 'cp_fec_donors';

        // Check if tables exist
        $contributions_exists = $wpdb->get_var("SHOW TABLES LIKE '{$contributions_table}'") === $contributions_table;
        $donors_exists = $wpdb->get_var("SHOW TABLES LIKE '{$donors_table}'") === $donors_table;

        if (!$contributions_exists || !$donors_exists) {
            return array('contributions' => array(), 'donors' => array());
        }

        // Sample employers and occupations
        $employers = array(
            array('employer' => 'Springfield Memorial Hospital', 'occupation' => 'Nurse'),
            array('employer' => 'State Farm Insurance', 'occupation' => 'Insurance Agent'),
            array('employer' => 'University of Illinois', 'occupation' => 'Professor'),
            array('employer' => 'Springfield Public Schools', 'occupation' => 'Teacher'),
            array('employer' => 'Self-Employed', 'occupation' => 'Attorney'),
            array('employer' => 'Self-Employed', 'occupation' => 'Consultant'),
            array('employer' => 'Retired', 'occupation' => 'Retired'),
            array('employer' => 'ABC Manufacturing', 'occupation' => 'Engineer'),
            array('employer' => 'City of Springfield', 'occupation' => 'Administrator'),
            array('employer' => 'Tech Solutions Inc', 'occupation' => 'Software Developer'),
        );

        // Contribution amounts (realistic distribution)
        $amounts = array(25, 25, 50, 50, 50, 100, 100, 100, 100, 250, 250, 500, 500, 1000, 2900);

        // Create 75 unique donors
        for ($i = 0; $i < 75; $i++) {
            $first_name = $this->first_names[array_rand($this->first_names)];
            $last_name = $this->last_names[array_rand($this->last_names)];
            $city_data = $this->cities[array_rand($this->cities)];
            $emp_data = $employers[array_rand($employers)];
            $street_num = rand(100, 9999);
            $street = $this->street_names[array_rand($this->street_names)];

            $wpdb->insert($donors_table, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'address' => $street_num . ' ' . $street,
                'city' => $city_data['city'],
                'state' => $city_data['state'],
                'zip' => $city_data['zip'],
                'employer' => $emp_data['employer'],
                'occupation' => $emp_data['occupation'],
                'total_contributions' => 0, // Will update after contributions
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
            ));

            $donor_ids[] = $wpdb->insert_id;
        }

        // Create 100 contributions across donors
        for ($i = 0; $i < 100; $i++) {
            $donor_id = $donor_ids[array_rand($donor_ids)];
            $amount = $amounts[array_rand($amounts)];

            // Random date in the last 180 days
            $days_ago = rand(0, 180);
            $contribution_date = date('Y-m-d', strtotime("-{$days_ago} days"));

            // Payment methods
            $methods = array('credit_card', 'credit_card', 'credit_card', 'check', 'actblue', 'actblue');
            $method = $methods[array_rand($methods)];

            $wpdb->insert($contributions_table, array(
                'donor_id' => $donor_id,
                'amount' => $amount,
                'contribution_date' => $contribution_date,
                'payment_method' => $method,
                'is_itemized' => $amount >= 200 ? 1 : 0,
                'receipt_number' => 'RCP-' . date('Ymd', strtotime($contribution_date)) . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status' => 'completed',
                'is_demo' => 1,
                'created_at' => current_time('mysql'),
            ));

            $contribution_ids[] = $wpdb->insert_id;

            // Update donor total
            $wpdb->query($wpdb->prepare(
                "UPDATE {$donors_table} SET total_contributions = total_contributions + %f WHERE id = %d",
                $amount,
                $donor_id
            ));
        }

        return array(
            'contributions' => $contribution_ids,
            'donors' => $donor_ids,
        );
    }

    /**
     * Delete CRM demo data
     *
     * @param array $ids Array of demo IDs
     */
    private function delete_crm_data($ids) {
        global $wpdb;

        // Delete in reverse order due to foreign keys
        if (!empty($ids['segments'])) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}cp_segments WHERE is_demo = 1");
        }

        if (!empty($ids['interactions'])) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}cp_interactions WHERE is_demo = 1");
        }

        if (!empty($ids['contacts'])) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}cp_contact_tags WHERE contact_id IN (SELECT id FROM {$wpdb->prefix}cp_contacts WHERE is_demo = 1)");
            $wpdb->query("DELETE FROM {$wpdb->prefix}cp_contacts WHERE is_demo = 1");
        }

        if (!empty($ids['tags'])) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}cp_tags WHERE id IN (" . implode(',', array_map('intval', $ids['tags'])) . ")");
        }
    }

    /**
     * Delete Field Ops demo data
     *
     * @param array $ids Array of demo IDs
     */
    private function delete_field_ops_data($ids) {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}cp_canvass_turfs WHERE is_demo = 1");
        $wpdb->query("DELETE FROM {$wpdb->prefix}cp_phone_campaigns WHERE is_demo = 1");
        $wpdb->query("DELETE FROM {$wpdb->prefix}cp_gotv_lists WHERE is_demo = 1");
        $wpdb->query("DELETE FROM {$wpdb->prefix}cp_volunteer_shifts WHERE is_demo = 1");
    }

    /**
     * Delete Compliance demo data
     *
     * @param array $ids Array of demo IDs
     */
    private function delete_compliance_data($ids) {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}cp_fec_contributions WHERE is_demo = 1");
        $wpdb->query("DELETE FROM {$wpdb->prefix}cp_fec_donors WHERE is_demo = 1");
    }
}

// Initialize
CampaignPress_Premium_Demo_Content::get_instance();
