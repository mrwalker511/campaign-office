<?php
/**
 * Demo Content Generator
 *
 * Provides sample content for testing CampaignPress theme
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CampaignPress_Demo_Content {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_cp_import_demo', array($this, 'handle_import'));
        add_action('admin_post_cp_delete_demo', array($this, 'handle_delete'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_theme_page(
            __('CampaignPress Demo Content', 'campaign-office'),
            __('Demo Content', 'campaign-office'),
            'manage_options',
            'campaignpress-demo',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $demo_exists = get_option('campaignpress_demo_imported', false);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('CampaignPress Demo Content', 'campaign-office'); ?></h1>

            <?php if (!empty($_GET['imported']) && sanitize_key($_GET['imported']) === '1') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Demo content imported successfully!', 'campaign-office'); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['deleted']) && sanitize_key($_GET['deleted']) === '1') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Demo content deleted successfully!', 'campaign-office'); ?></p>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <h2><?php esc_html_e('Sample Campaign Content', 'campaign-office'); ?></h2>
                <p><?php esc_html_e('This will create sample content to help you see how CampaignPress works. Perfect for testing and demonstrations.', 'campaign-office'); ?></p>

                <h3><?php esc_html_e('Content to be created:', 'campaign-office'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php esc_html_e('6 Policy Issues (Healthcare, Education, Environment, Economy, Justice, Infrastructure)', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('4 Campaign Events (Town Hall, Fundraiser, Rally, Debate)', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('8 Endorsements (Officials, Organizations, Community Leaders)', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('5 Team Members (Campaign Manager, Finance Director, etc.)', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('4 Volunteer Opportunities (Canvassing, Phone Banking, etc.)', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Sample homepage with CampaignPress blocks', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('About page with candidate bio', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('3 Navigation Menus (Primary, Footer, Social)', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Complete Theme Options populated with sample campaign data', 'campaign-office'); ?></li>
                </ul>

                <?php if (!$demo_exists) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="cp_import_demo">
                        <?php wp_nonce_field('cp_import_demo', 'cp_demo_nonce'); ?>
                        <p>
                            <button type="submit" class="button button-primary button-hero">
                                <?php esc_html_e('Import Demo Content', 'campaign-office'); ?>
                            </button>
                        </p>
                        <p class="description">
                            <?php esc_html_e('This creates sample content. You can delete it anytime.', 'campaign-office'); ?>
                        </p>
                    </form>
                <?php else : ?>
                    <div class="notice notice-info inline">
                        <p><?php esc_html_e('Demo content is currently installed.', 'campaign-office'); ?></p>
                    </div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="cp-delete-demo-form">
                        <input type="hidden" name="action" value="cp_delete_demo">
                        <?php wp_nonce_field('cp_delete_demo', 'cp_demo_nonce'); ?>
                        <p>
                            <button type="submit" class="button button-secondary">
                                <?php esc_html_e('Delete Demo Content', 'campaign-office'); ?>
                            </button>
                        </p>
                    </form>
                    <script>
                    document.getElementById('cp-delete-demo-form').addEventListener('submit', function(e) {
                        if (!confirm('<?php echo esc_js(__('Are you sure you want to delete all demo content? This cannot be undone.', 'campaign-office')); ?>')) {
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
        if (!isset($_POST['cp_demo_nonce']) || !wp_verify_nonce($_POST['cp_demo_nonce'], 'cp_import_demo')) {
            wp_die(__('Security check failed', 'campaign-office'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'campaign-office'));
        }

        // Import content
        $demo_post_ids = array();

        // Import Issues
        $demo_post_ids['issues'] = $this->import_issues();

        // Import Events
        $demo_post_ids['events'] = $this->import_events();

        // Import Endorsements
        $demo_post_ids['endorsements'] = $this->import_endorsements();

        // Import Team Members
        $demo_post_ids['team'] = $this->import_team();

        // Import Volunteer Opportunities
        $demo_post_ids['volunteers'] = $this->import_volunteers();

        // Import Sample Pages (must be before menus)
        $demo_post_ids['pages'] = $this->import_pages();

        // Import Navigation Menus (uses page IDs)
        $demo_post_ids['menus'] = $this->import_menus($demo_post_ids['pages']);

        // Populate Theme Options
        $this->populate_theme_options();

        // Save demo post IDs for later deletion
        update_option('campaignpress_demo_post_ids', $demo_post_ids);
        update_option('campaignpress_demo_imported', true);

        // Redirect back
        wp_redirect(add_query_arg('imported', '1', wp_get_referer()));
        exit;
    }

    /**
     * Handle delete
     */
    public function handle_delete() {
        // Check nonce
        if (!isset($_POST['cp_demo_nonce']) || !wp_verify_nonce($_POST['cp_demo_nonce'], 'cp_delete_demo')) {
            wp_die(__('Security check failed', 'campaign-office'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'campaign-office'));
        }

        // Get demo post IDs
        $demo_post_ids = get_option('campaignpress_demo_post_ids', array());

        // Delete all demo posts
        foreach ($demo_post_ids as $post_type => $ids) {
            if ($post_type === 'menus' && is_array($ids)) {
                // Delete menus
                foreach ($ids as $menu_id) {
                    wp_delete_nav_menu($menu_id);
                }
            } elseif (is_array($ids)) {
                foreach ($ids as $post_id) {
                    wp_delete_post($post_id, true);
                }
            }
        }

        // Clear theme options (but don't delete them, just reset to defaults)
        $this->reset_theme_options();

        // Clean up options
        delete_option('campaignpress_demo_post_ids');
        delete_option('campaignpress_demo_imported');

        // Redirect back
        wp_redirect(add_query_arg('deleted', '1', wp_get_referer()));
        exit;
    }

    /**
     * Import sample issues
     */
    private function import_issues() {
        $issues = array(
            array(
                'title' => 'Universal Healthcare',
                'content' => '<p>Healthcare is a human right, not a privilege. We need to ensure that every person in our community has access to quality, affordable healthcare regardless of their income or employment status.</p>

<h3>Our Plan:</h3>
<ul>
    <li>Expand access to affordable health insurance</li>
    <li>Lower prescription drug costs through bulk negotiation</li>
    <li>Invest in community health centers</li>
    <li>Protect coverage for pre-existing conditions</li>
    <li>Support mental health and addiction services</li>
</ul>

<p>We spend more on healthcare than any other developed nation, yet millions still lack coverage. It\'s time to put people before profits and build a healthcare system that works for everyone.</p>',
                'category' => 'Healthcare',
            ),
            array(
                'title' => 'Quality Public Education',
                'content' => '<p>Every child deserves access to excellent public education. We must invest in our schools, support our teachers, and prepare our students for the jobs of tomorrow.</p>

<h3>Our Commitment:</h3>
<ul>
    <li>Increase teacher salaries to attract and retain the best educators</li>
    <li>Reduce class sizes for more personalized attention</li>
    <li>Modernize school facilities and technology</li>
    <li>Expand access to pre-K and early childhood education</li>
    <li>Make college and vocational training more affordable</li>
</ul>

<p>Education is the foundation of opportunity. By investing in our schools today, we\'re investing in our community\'s future.</p>',
                'category' => 'Education',
            ),
            array(
                'title' => 'Climate Action & Clean Energy',
                'content' => '<p>Climate change is the defining challenge of our generation. We must act now to protect our environment and transition to a clean energy economy.</p>

<h3>Our Action Plan:</h3>
<ul>
    <li>Achieve 100% renewable energy by 2035</li>
    <li>Create green jobs through clean energy investments</li>
    <li>Protect our air and water from pollution</li>
    <li>Preserve parks and natural spaces</li>
    <li>Support sustainable agriculture and local food systems</li>
</ul>

<p>We can fight climate change while creating good-paying jobs and building a sustainable economy for future generations.</p>',
                'category' => 'Environment',
            ),
            array(
                'title' => 'Economic Opportunity for All',
                'content' => '<p>Our economy should work for everyone, not just those at the top. We need to create good-paying jobs, support small businesses, and ensure economic security for all families.</p>

<h3>Our Economic Agenda:</h3>
<ul>
    <li>Raise the minimum wage to a living wage</li>
    <li>Support small businesses and local entrepreneurs</li>
    <li>Invest in infrastructure to create jobs</li>
    <li>Protect workers\' rights to organize</li>
    <li>Close tax loopholes for wealthy corporations</li>
</ul>

<p>When working families thrive, our entire community prospers. We\'ll build an economy that rewards hard work and provides opportunity for all.</p>',
                'category' => 'Economy',
            ),
            array(
                'title' => 'Criminal Justice Reform',
                'content' => '<p>Our justice system must be fair, equitable, and focused on rehabilitation rather than punishment. We need comprehensive reform to end mass incarceration and rebuild trust.</p>

<h3>Reform Priorities:</h3>
<ul>
    <li>End cash bail that criminalizes poverty</li>
    <li>Invest in community policing and accountability</li>
    <li>Expand mental health and addiction treatment</li>
    <li>Reform sentencing guidelines for non-violent offenses</li>
    <li>Support re-entry programs for formerly incarcerated individuals</li>
</ul>

<p>Justice means treating people with dignity, addressing root causes of crime, and giving everyone a second chance.</p>',
                'category' => 'Justice',
            ),
            array(
                'title' => 'Infrastructure & Transportation',
                'content' => '<p>Modern infrastructure is essential for economic growth and quality of life. We need to rebuild our roads, bridges, and public transit while investing in 21st-century solutions.</p>

<h3>Infrastructure Plan:</h3>
<ul>
    <li>Repair deteriorating roads and bridges</li>
    <li>Expand public transportation options</li>
    <li>Build out high-speed internet access</li>
    <li>Upgrade water and sewer systems</li>
    <li>Invest in electric vehicle infrastructure</li>
</ul>

<p>Smart infrastructure investments create jobs, improve safety, and connect our communities. It\'s time to build for the future.</p>',
                'category' => 'Infrastructure',
            ),
        );

        $post_ids = array();

        foreach ($issues as $issue) {
            $post_id = wp_insert_post(array(
                'post_title'   => $issue['title'],
                'post_content' => $issue['content'],
                'post_status'  => 'publish',
                'post_type'    => 'cp_issue',
                'post_author'  => get_current_user_id(),
            ));

            if (is_wp_error($post_id)) {
                error_log('CampaignPress demo content error: ' . $post_id->get_error_message());
                continue;
            }

            if (!$post_id) {
                error_log('CampaignPress: Failed to create demo issue - ' . $issue['title']);
                continue;
            }

            // Add to category
            $term = term_exists($issue['category'], 'issue_category');
            if (!$term) {
                $term = wp_insert_term($issue['category'], 'issue_category');
            }

            if (is_wp_error($term)) {
                error_log('CampaignPress: Failed to create issue category - ' . $issue['category'] . ': ' . $term->get_error_message());
            } elseif ($term) {
                wp_set_object_terms($post_id, $term['term_id'], 'issue_category');
            }

            $post_ids[] = $post_id;
        }

        return $post_ids;
    }

    /**
     * Import sample events
     */
    private function import_events() {
        $events = array(
            array(
                'title' => 'Community Town Hall - Healthcare Discussion',
                'content' => '<p>Join us for an important conversation about the future of healthcare in our community. We\'ll discuss our plan for universal healthcare access and answer your questions.</p>

<p>This is your opportunity to share your healthcare stories, concerns, and ideas. Together, we can build a system that works for everyone.</p>

<p>Light refreshments will be served. All are welcome!</p>',
                'date' => date('Y-m-d', strtotime('+14 days')),
                'time' => '18:00',
                'location' => 'Central Community Center',
                'address' => '123 Main Street',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62701',
                'rsvp_link' => '#',
                'type' => 'Town Hall',
            ),
            array(
                'title' => 'Grassroots Fundraiser BBQ',
                'content' => '<p>Help us fuel our grassroots campaign! Join us for a family-friendly BBQ fundraiser with great food, live music, and community spirit.</p>

<p>This campaign is powered by people, not special interests. Your support helps us reach voters, spread our message, and build a movement for change.</p>

<p>Suggested donation: $25 per person, but all contributions welcome. No one turned away!</p>',
                'date' => date('Y-m-d', strtotime('+21 days')),
                'time' => '14:00',
                'location' => 'Riverside Park',
                'address' => '456 Park Avenue',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62702',
                'rsvp_link' => '#',
                'type' => 'Fundraiser',
            ),
            array(
                'title' => 'Get Out The Vote Rally',
                'content' => '<p>The election is almost here! Join us for a high-energy rally to energize our volunteers and get out the vote.</p>

<p>We\'ll have special guests, inspiring speakers, and marching music. Then we\'ll head out together to knock doors and make calls.</p>

<p>This is it - the final push. Every conversation, every door, every vote counts. Let\'s finish strong together!</p>',
                'date' => date('Y-m-d', strtotime('+60 days')),
                'time' => '10:00',
                'location' => 'Campaign Headquarters',
                'address' => '789 Campaign Trail',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62703',
                'rsvp_link' => '#',
                'type' => 'Rally',
            ),
            array(
                'title' => 'Candidate Debate - Community Issues',
                'content' => '<p>Watch our candidate discuss the issues that matter most to our community in this important debate.</p>

<p>Topics will include education, healthcare, economic development, and public safety. The debate will be followed by a Q&A session with audience members.</p>

<p>This is your chance to see where the candidates stand and make an informed decision on Election Day.</p>',
                'date' => date('Y-m-d', strtotime('+45 days')),
                'time' => '19:00',
                'location' => 'Springfield High School Auditorium',
                'address' => '321 Education Way',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62704',
                'rsvp_link' => '#',
                'type' => 'Debate',
            ),
        );

        $post_ids = array();

        foreach ($events as $event) {
            $post_id = wp_insert_post(array(
                'post_title'   => $event['title'],
                'post_content' => $event['content'],
                'post_status'  => 'publish',
                'post_type'    => 'cp_event',
                'post_author'  => get_current_user_id(),
            ));

            if (is_wp_error($post_id)) {
                error_log('CampaignPress demo content error: ' . $post_id->get_error_message());
                continue;
            }

            if (!$post_id) {
                error_log('CampaignPress: Failed to create demo event - ' . $event['title']);
                continue;
            }

            // Add event metadata
            update_post_meta($post_id, '_cp_event_date', $event['date']);
            update_post_meta($post_id, '_cp_event_time', $event['time']);
            update_post_meta($post_id, '_cp_event_location', $event['location']);
            update_post_meta($post_id, '_cp_event_address', $event['address']);
            update_post_meta($post_id, '_cp_event_city', $event['city']);
            update_post_meta($post_id, '_cp_event_state', $event['state']);
            update_post_meta($post_id, '_cp_event_zip', $event['zip']);
            update_post_meta($post_id, '_cp_event_rsvp_link', $event['rsvp_link']);

            // Add event type
            $term = term_exists($event['type'], 'event_type');
            if (!$term) {
                $term = wp_insert_term($event['type'], 'event_type');
            }

            if (is_wp_error($term)) {
                error_log('CampaignPress: Failed to create event type - ' . $event['type'] . ': ' . $term->get_error_message());
            } elseif ($term) {
                wp_set_object_terms($post_id, $term['term_id'], 'event_type');
            }

            $post_ids[] = $post_id;
        }

        return $post_ids;
    }

    /**
     * Import sample endorsements
     */
    private function import_endorsements() {
        $endorsements = array(
            array(
                'title' => 'Mayor Jennifer Williams',
                'content' => '"I\'ve worked with this candidate on numerous community initiatives, and I can say without hesitation that they have the integrity, vision, and dedication we need in office. They listen to constituents, build consensus, and get results. I\'m proud to endorse their campaign."',
            ),
            array(
                'title' => 'Springfield Teachers Association',
                'content' => '"Our members have seen firsthand this candidate\'s commitment to public education. They understand that investing in schools means investing in our future. We\'re confident they will fight for our teachers, our students, and quality education for all."',
            ),
            array(
                'title' => 'Dr. Robert Chen - Community Health Director',
                'content' => '"As a healthcare professional, I\'m supporting this campaign because they understand that healthcare is a human right. Their plan to expand access and lower costs will save lives in our community. We need this kind of leadership."',
            ),
            array(
                'title' => 'Small Business Coalition of Springfield',
                'content' => '"This candidate gets it - they understand the challenges small businesses face and have real solutions to help us thrive. They\'re committed to supporting local entrepreneurs and creating an economy that works for everyone, not just big corporations."',
            ),
            array(
                'title' => 'Environmental Action League',
                'content' => '"Climate change demands bold action, and this candidate has the courage to fight for our planet. Their clean energy plan will create jobs, reduce pollution, and protect our environment for future generations. The time to act is now."',
            ),
            array(
                'title' => 'Police Chief (Ret.) Marcus Johnson',
                'content' => '"I\'ve dedicated my career to public safety, and I believe this candidate will make our community safer. They support both our officers and common-sense reforms. They understand that real safety comes from strong communities, good schools, and economic opportunity."',
            ),
            array(
                'title' => 'Young Democrats of Springfield',
                'content' => '"Our generation is ready for change, and this candidate represents the future we\'re fighting for. They\'re not afraid to tackle big challenges like student debt, climate change, and economic inequality. We\'re all in for this campaign!"',
            ),
            array(
                'title' => 'Rev. Sarah Martinez - Faith Leaders Coalition',
                'content' => '"This candidate embodies the values we cherish - compassion, justice, and service to others. They have shown a deep commitment to lifting up those in need and fighting for the marginalized. Our faith calls us to support leaders like this."',
            ),
        );

        $post_ids = array();

        foreach ($endorsements as $endorsement) {
            $post_id = wp_insert_post(array(
                'post_title'   => $endorsement['title'],
                'post_content' => $endorsement['content'],
                'post_status'  => 'publish',
                'post_type'    => 'cp_endorsement',
                'post_author'  => get_current_user_id(),
            ));

            if (is_wp_error($post_id)) {
                error_log('CampaignPress demo content error: ' . $post_id->get_error_message());
                continue;
            }

            if (!$post_id) {
                error_log('CampaignPress: Failed to create demo endorsement - ' . $endorsement['title']);
                continue;
            }

            $post_ids[] = $post_id;
        }

        return $post_ids;
    }

    /**
     * Import sample team members
     */
    private function import_team() {
        $team = array(
            array(
                'title' => 'Sarah Johnson - Campaign Manager',
                'content' => '<p>Sarah brings 15 years of campaign experience to our team. She\'s managed successful races from local school board to statewide initiatives. Sarah is a strategic thinker who knows how to build winning coalitions and mobilize grassroots support.</p>

<p>Before joining our campaign, Sarah worked as Political Director for the state party and managed three winning congressional campaigns. She holds a degree in Political Science from State University.</p>',
            ),
            array(
                'title' => 'Michael Torres - Finance Director',
                'content' => '<p>Michael oversees all fundraising operations and ensures our campaign runs efficiently. With a background in nonprofit development and political fundraising, he understands the importance of building a people-powered campaign.</p>

<p>Michael previously served as Development Director for several advocacy organizations and has raised millions for progressive causes. He believes in transparent, grassroots fundraising that puts people first.</p>',
            ),
            array(
                'title' => 'Emily Washington - Communications Director',
                'content' => '<p>Emily crafts our message and manages all media relations. She\'s an award-winning journalist turned political communicator who knows how to tell our story effectively across all platforms.</p>

<p>Previously a reporter for the Springfield Times and communications advisor to several elected officials, Emily brings both media savvy and a deep understanding of the issues facing our community.</p>',
            ),
            array(
                'title' => 'David Kim - Field Director',
                'content' => '<p>David leads our grassroots organizing efforts, training volunteers and building our ground game. He\'s a community organizer at heart who believes in the power of one-on-one conversations to build a movement.</p>

<p>David has organized successful voter registration drives, led canvassing operations, and built volunteer teams across the state. He knows that elections are won by talking to voters where they are.</p>',
            ),
            array(
                'title' => 'Maria Rodriguez - Policy Director',
                'content' => '<p>Maria develops our policy positions and ensures our platform addresses the real needs of our community. She brings deep expertise in public policy and a commitment to evidence-based solutions.</p>

<p>With a Ph.D. in Public Policy and experience working in both government and advocacy, Maria translates complex policy into practical solutions that improve people\'s lives.</p>',
            ),
        );

        $post_ids = array();

        foreach ($team as $member) {
            $post_id = wp_insert_post(array(
                'post_title'   => $member['title'],
                'post_content' => $member['content'],
                'post_status'  => 'publish',
                'post_type'    => 'cp_team',
                'post_author'  => get_current_user_id(),
            ));

            if (is_wp_error($post_id)) {
                error_log('CampaignPress demo content error: ' . $post_id->get_error_message());
                continue;
            }

            if (!$post_id) {
                error_log('CampaignPress: Failed to create demo team member - ' . $member['title']);
                continue;
            }

            $post_ids[] = $post_id;
        }

        return $post_ids;
    }

    /**
     * Import sample volunteer opportunities
     */
    private function import_volunteers() {
        $volunteers = array(
            array(
                'title' => 'Door-to-Door Canvassing',
                'content' => '<p>Join our canvassing team and talk to voters in your neighborhood! This is the most effective way to reach voters and share our message.</p>

<h3>What You\'ll Do:</h3>
<ul>
    <li>Walk through assigned neighborhoods</li>
    <li>Have friendly conversations with voters</li>
    <li>Share information about our candidate and campaign</li>
    <li>Collect valuable voter data</li>
</ul>

<h3>Time Commitment:</h3>
<p>Flexible shifts available: weekday evenings and weekend afternoons. We provide all training and materials. No experience necessary!</p>',
                'excerpt' => 'Talk to voters in your neighborhood and help build our grassroots movement. Training provided!',
            ),
            array(
                'title' => 'Phone Banking',
                'content' => '<p>Make calls from home (or our office) to reach voters across the district. Phone banking is a great way to get involved if you can\'t canvass in person.</p>

<h3>What You\'ll Do:</h3>
<ul>
    <li>Call voters from provided lists</li>
    <li>Answer questions about our candidate</li>
    <li>Identify supporters and persuade undecided voters</li>
    <li>Remind people to vote</li>
</ul>

<h3>Time Commitment:</h3>
<p>Virtual phone banks available daily. Shifts are typically 2-3 hours. We provide scripts and training!</p>',
                'excerpt' => 'Call voters from the comfort of your home. Flexible hours and full training provided.',
            ),
            array(
                'title' => 'Event Support & Logistics',
                'content' => '<p>Help make our campaign events successful! We need volunteers to assist with setup, registration, and logistics for rallies, town halls, and fundraisers.</p>

<h3>What You\'ll Do:</h3>
<ul>
    <li>Set up and break down event spaces</li>
    <li>Greet attendees and help with check-in</li>
    <li>Distribute campaign materials</li>
    <li>Assist with event coordination</li>
</ul>

<h3>Time Commitment:</h3>
<p>Events are typically on evenings and weekends. Shifts are usually 3-4 hours. Great for people who enjoy working with others!</p>',
                'excerpt' => 'Make our campaign events shine! Help with setup, registration, and event support.',
            ),
            array(
                'title' => 'Social Media & Digital Outreach',
                'content' => '<p>Use your digital skills to amplify our message online! Help us engage supporters, create content, and reach new audiences on social media.</p>

<h3>What You\'ll Do:</h3>
<ul>
    <li>Share campaign posts on social media</li>
    <li>Create graphics and content</li>
    <li>Engage with supporters online</li>
    <li>Help manage digital volunteer groups</li>
</ul>

<h3>Time Commitment:</h3>
<p>This is a flexible, remote opportunity. Work on your own schedule, anywhere you have internet access. Perfect for busy schedules!</p>',
                'excerpt' => 'Help spread our message online! Remote, flexible opportunity for digital-savvy volunteers.',
            ),
        );

        $post_ids = array();

        foreach ($volunteers as $opportunity) {
            $post_id = wp_insert_post(array(
                'post_title'   => $opportunity['title'],
                'post_content' => $opportunity['content'],
                'post_excerpt' => $opportunity['excerpt'],
                'post_status'  => 'publish',
                'post_type'    => 'cp_volunteer',
                'post_author'  => get_current_user_id(),
            ));

            if (is_wp_error($post_id)) {
                error_log('CampaignPress demo content error: ' . $post_id->get_error_message());
                continue;
            }

            if (!$post_id) {
                error_log('CampaignPress: Failed to create demo volunteer opportunity - ' . $opportunity['title']);
                continue;
            }

            $post_ids[] = $post_id;
        }

        return $post_ids;
    }

    /**
     * Import sample pages
     */
    private function import_pages() {
        $pages = array(
            array(
                'title' => 'Home',
                'slug' => 'home',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Fighting for Our Community\'s Future</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Join our grassroots movement to bring real change to our community. Together, we can build a future that works for everyone.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/issues/')) . '">Our Issues</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/events/')) . '">Upcoming Events</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading -->
<h2>Why I\'m Running</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our community faces real challenges - from healthcare costs to crumbling infrastructure, from underfunded schools to climate change. But I believe we have the power to solve these problems when we work together.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>I\'m running for office because I\'ve seen firsthand the difference that dedicated public service can make. As a community organizer, teacher, and parent, I understand the issues facing working families. And I have a proven track record of bringing people together to get things done.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Key Issues</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3>Universal Healthcare</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Healthcare is a human right. We need to ensure everyone has access to quality, affordable healthcare.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3>Quality Education</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every child deserves access to excellent public education with well-paid teachers and modern facilities.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3>Climate Action</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We must act now on climate change, transitioning to clean energy and creating green jobs.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading -->
<h2>Get Involved</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">This campaign is powered by people like you. Whether you can knock doors, make calls, or help at events - we need your help to win!</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://secure.actblue.com/donate/example">Donate</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://example.com/volunteer">Volunteer</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
                'is_front_page' => true,
            ),
            array(
                'title' => 'About',
                'slug' => 'about',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Meet the Candidate</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>A Lifelong Commitment to Service</strong></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>I was born and raised in Springfield, attending public schools and learning the value of hard work from my parents - a teacher and a factory worker. They taught me that everyone deserves a fair shot and that we all have a responsibility to give back to our community.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>After earning my degree from State University, I came home to Springfield to teach in the same public schools I attended. For the past 15 years, I\'ve worked with students, parents, and educators to improve our schools and expand opportunities for all children.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>As a community organizer, I\'ve fought for affordable housing, quality healthcare, and good-paying jobs. I\'ve brought together neighbors, businesses, and local leaders to solve problems and make our community stronger.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why I\'m Running</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>I\'m running for office because I believe our community deserves better. Better schools, better healthcare, better opportunities for everyone. I\'ve spent my career fighting for working families, and I\'m ready to take that fight to the next level.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>This campaign is about all of us - about building a future where everyone can thrive, where healthcare is a right, where quality education is guaranteed, and where good jobs are available to all who want to work.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>My Values</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><strong>Integrity:</strong> I believe in honesty, transparency, and always doing what\'s right - even when it\'s hard.</li>
<li><strong>Compassion:</strong> Everyone deserves to be treated with dignity and respect, regardless of their background.</li>
<li><strong>Community:</strong> We\'re stronger together. Real change comes from listening to people and working as a team.</li>
<li><strong>Justice:</strong> Our systems should be fair and equitable, providing opportunity for all.</li>
<li><strong>Sustainability:</strong> We must protect our environment and build an economy that lasts for generations.</li>
</ul>
<!-- /wp:list -->',
            ),
            array(
                'title' => 'Contact',
                'slug' => 'contact',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Get in Touch</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We\'d love to hear from you! Whether you have questions, want to volunteer, or just want to share your thoughts, please reach out.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Campaign Headquarters</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Address:</strong><br>123 Campaign Trail<br>Springfield, IL 62701</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Email:</strong> info@campaignexample.com</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Phone:</strong> (555) 123-4567</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Office Hours</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Connect on Social Media</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Follow us on Facebook, Twitter, and Instagram for the latest campaign updates, event announcements, and ways to get involved!</p>
<!-- /wp:paragraph -->',
            ),
            array(
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Privacy Policy</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><em>Last Updated: ' . date('F j, Y') . '</em></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Information We Collect</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>When you interact with our campaign website, we may collect certain information including your name, email address, phone number, and mailing address if you choose to provide it.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>How We Use Your Information</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We use the information you provide to:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Send you campaign updates and newsletters</li>
<li>Coordinate volunteer activities</li>
<li>Process donations</li>
<li>Respond to your questions and comments</li>
<li>Comply with federal and state campaign finance laws</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Information Sharing</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We do not sell, trade, or rent your personal information to third parties. We may share information as required by law or to comply with campaign finance regulations.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Contact Us</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you have questions about this Privacy Policy, please contact us at info@campaignexample.com</p>
<!-- /wp:paragraph -->',
            ),
            array(
                'title' => 'Press & Media',
                'slug' => 'press',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Press & Media</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Welcome to our press and media center. Here you\'ll find press releases, media kits, and contact information for media inquiries.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Media Contact</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Press Secretary:</strong> Sarah Communications<br><strong>Email:</strong> press@campaignexample.com<br><strong>Phone:</strong> (555) 123-4568</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Recent Press Releases</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Check back soon for our latest press releases and campaign announcements.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Media Kit</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Download our media kit including high-resolution photos, campaign logo, and candidate biography.</p>
<!-- /wp:paragraph -->',
            ),
            array(
                'title' => 'Get Involved',
                'slug' => 'get-involved',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Get Involved</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This campaign is powered by people like you. There are many ways to get involved and help us win!</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Volunteer</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Join our team of dedicated volunteers! We need help with:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Door-to-door canvassing</li>
<li>Phone banking</li>
<li>Event support</li>
<li>Social media outreach</li>
<li>Office work</li>
</ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/volunteer-opportunities/')) . '">View Volunteer Opportunities</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading -->
<h2>Donate</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Your financial support helps us reach more voters, run ads, and organize events. Every dollar makes a difference!</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://secure.actblue.com/donate/example">Donate Now</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading -->
<h2>Spread the Word</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Follow us on social media and share our posts with your friends and family. Word of mouth is one of our most powerful tools!</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Host an Event</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Want to host a house party, fundraiser, or meet-and-greet? Contact our campaign to learn how!</p>
<!-- /wp:paragraph -->',
            ),
            array(
                'title' => 'Issues',
                'slug' => 'issues',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Our Issues</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our campaign is built on a foundation of progressive values and real solutions to the challenges facing our community. Here are the key issues we\'re fighting for:</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Universal Healthcare</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Healthcare is a human right, not a privilege. We need a system that guarantees quality, affordable healthcare for everyone - regardless of employment status or pre-existing conditions. Our plan will lower prescription drug costs, expand access to mental health services, and ensure no one goes bankrupt due to medical bills.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Quality Public Education</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every child deserves access to excellent public education. We will fight to fully fund our schools, raise teacher salaries to competitive levels, reduce class sizes, and invest in modern facilities and technology. Education is the foundation of opportunity.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Climate Action & Clean Energy</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Climate change is the defining challenge of our time. We will transition to 100% clean energy, create thousands of green jobs, invest in public transportation, and ensure environmental justice for communities that have been disproportionately impacted by pollution.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Economic Opportunity for All</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We need an economy that works for everyone, not just those at the top. We will raise the minimum wage to a living wage, support small businesses, invest in workforce development, and ensure workers have the right to organize and bargain collectively.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Criminal Justice Reform</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our justice system must be fair and equitable. We will end mass incarceration, invest in community-based alternatives to imprisonment, eliminate cash bail, and ensure accountability in law enforcement while supporting community safety.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Infrastructure & Transportation</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We need to rebuild and modernize our infrastructure. We will fix our roads and bridges, expand public transportation, invest in broadband access for all communities, and ensure our infrastructure is resilient to climate change.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/get-involved/')) . '">Join the Fight</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
            ),
            array(
                'title' => 'Our Team',
                'slug' => 'team',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Meet Our Team</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our campaign is powered by dedicated professionals who share a commitment to progressive values and winning this election. Meet the team working tirelessly to bring change to our community.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Campaign Leadership</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We\'ve assembled a team of experienced political professionals, grassroots organizers, and community leaders who bring diverse perspectives and proven track records of success.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Campaign Manager</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Leading our overall strategy and operations, ensuring we reach voters effectively and build a winning coalition.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Field Director</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Organizing our grassroots volunteer network, coordinating canvassing efforts, and building relationships in communities across the district.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Communications Director</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Crafting our message, managing media relations, and ensuring our campaign communicates effectively across all platforms.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Finance Director</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Managing fundraising operations, ensuring compliance with campaign finance laws, and building our donor network.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Policy Director</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Developing detailed policy proposals, conducting research, and ensuring our platform addresses the real needs of our community.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Join Our Team</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Interested in joining our campaign team? We\'re always looking for talented, passionate individuals to help us win. <a href="' . esc_url(home_url('/contact/')) . '">Get in touch</a> to learn about opportunities.</p>
<!-- /wp:paragraph -->',
            ),
            array(
                'title' => 'Events',
                'slug' => 'events',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Upcoming Events</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Join us at one of our upcoming campaign events! Whether it\'s a town hall, rally, fundraiser, or volunteer event, we\'d love to see you there.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Town Halls & Community Events</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our town halls are your opportunity to hear directly from the candidate, ask questions, and share your concerns. We believe in accessible, transparent leadership - and that starts with listening to you.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Volunteer Events</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Get involved by joining us for canvassing, phone banking, or event support. No experience necessary - we\'ll train you and provide all the materials you need. Together, we can make a difference!</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Fundraisers</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our grassroots fundraising events are fun, casual gatherings where you can meet the candidate, connect with fellow supporters, and help fuel our campaign. From backyard BBQs to virtual events, there\'s something for everyone.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Stay Updated</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Don\'t miss an event! Sign up for our email list to receive notifications about upcoming events in your area.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/contact/')) . '">Get Updates</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
            ),
            array(
                'title' => 'Endorsements',
                'slug' => 'endorsements',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Endorsements</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We\'re honored to have the support of leaders, organizations, and community members who share our vision for a better future. Here\'s what they\'re saying about our campaign:</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Community Leaders</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Local elected officials, community organizers, and civic leaders who know firsthand the difference that dedicated public service can make.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>"I\'ve worked with this candidate on numerous community initiatives, and I can say without hesitation that they have the integrity, vision, and dedication we need in office."</p>
<!-- /wp:paragraph --><cite>Mayor Jennifer Williams</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2>Organizations</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Unions, advocacy groups, and community organizations that are committed to fighting for working families, quality education, healthcare access, and environmental justice.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>"Our members have seen firsthand this candidate\'s commitment to public education. We\'re confident they will fight for our teachers, our students, and quality education for all."</p>
<!-- /wp:paragraph --><cite>Springfield Teachers Association</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2>Professionals & Experts</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Healthcare professionals, educators, business leaders, and policy experts who understand the challenges we face and believe in our solutions.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>"As a healthcare professional, I\'m supporting this campaign because they understand that healthcare is a human right. Their plan to expand access and lower costs will save lives in our community."</p>
<!-- /wp:paragraph --><cite>Dr. Robert Chen - Community Health Director</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p><strong>Want to endorse our campaign?</strong> <a href="' . esc_url(home_url('/contact/')) . '">Contact us</a> to add your voice of support!</p>
<!-- /wp:paragraph -->',
            ),
            array(
                'title' => 'Volunteer Opportunities',
                'slug' => 'volunteer-opportunities',
                'content' => '<!-- wp:heading {"level":1} -->
<h1>Volunteer Opportunities</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ready to make a difference? Our campaign needs volunteers like you! Whether you have an hour a week or can commit to more, there are many ways to get involved and help us win.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Door-to-Door Canvassing</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Connect with voters in your neighborhood! Canvassing is one of the most effective ways to reach voters. We\'ll provide training, materials, and ongoing support. You\'ll meet great people and make a real impact.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Time Commitment:</strong> 2-4 hours per shift</li>
<li><strong>Skills Needed:</strong> Friendly, outgoing personality</li>
<li><strong>Training Provided:</strong> Yes - full training on messaging and safety</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Phone Banking</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Prefer to volunteer from home? Join our phone banking team! You\'ll call voters to share information about our campaign, answer questions, and help get out the vote. Perfect for evenings or weekends.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Time Commitment:</strong> 2-3 hours per shift</li>
<li><strong>Skills Needed:</strong> Good communication skills</li>
<li><strong>Training Provided:</strong> Yes - scripts and calling guide provided</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Event Support & Logistics</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Help us host amazing events! We need volunteers to help with setup, registration, refreshments, and more. Great for people who enjoy organizing and creating welcoming spaces.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Time Commitment:</strong> Varies by event (typically 3-5 hours)</li>
<li><strong>Skills Needed:</strong> Organized, detail-oriented</li>
<li><strong>Training Provided:</strong> Event-specific orientation</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Social Media & Digital Outreach</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Are you social media savvy? Help us spread our message online! Create content, engage with supporters, and help build our digital presence across platforms.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Time Commitment:</strong> Flexible - work on your own schedule</li>
<li><strong>Skills Needed:</strong> Social media experience, creativity</li>
<li><strong>Training Provided:</strong> Brand guidelines and content calendar</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Office & Administrative Support</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every campaign needs behind-the-scenes support! Help with data entry, mailings, research, and general office tasks at our campaign headquarters.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>Time Commitment:</strong> Flexible shifts available</li>
<li><strong>Skills Needed:</strong> Basic computer skills, reliable</li>
<li><strong>Training Provided:</strong> On-the-job training for all tasks</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Ready to Join Us?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Sign up today and we\'ll match you with opportunities that fit your interests, skills, and schedule. Together, we can win this!</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/contact/')) . '">Sign Up to Volunteer</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
            ),
        );

        $post_ids = array();
        $front_page_id = null;
        $blog_page_id = null;

        foreach ($pages as $page_data) {
            $page_args = array(
                'post_title'   => $page_data['title'],
                'post_content' => $page_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id(),
                'post_name'    => $page_data['slug'],
            );

            $post_id = wp_insert_post($page_args);

            if (is_wp_error($post_id)) {
                error_log('CampaignPress demo content error: ' . $post_id->get_error_message());
                continue;
            }

            if (!$post_id) {
                error_log('CampaignPress: Failed to create demo page - ' . $page_data['title']);
                continue;
            }

            // Store front page ID
            if (isset($page_data['is_front_page']) && $page_data['is_front_page']) {
                $front_page_id = $post_id;
            }

            $post_ids[$page_data['slug']] = $post_id;
        }

        // Set front page
        if ($front_page_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $front_page_id);

            // Create a blog page for posts
            $blog_page_id = wp_insert_post(array(
                'post_title'   => 'News & Updates',
                'post_content' => '<!-- wp:paragraph --><p>Stay up to date with the latest campaign news and announcements.</p><!-- /wp:paragraph -->',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id(),
                'post_name'    => 'news',
            ));

            if ($blog_page_id && !is_wp_error($blog_page_id)) {
                update_option('page_for_posts', $blog_page_id);
                $post_ids['news'] = $blog_page_id;
            }
        }

        return $post_ids;
    }

    /**
     * Import navigation menus
     *
     * @param array $page_ids Array of created page IDs
     */
    private function import_menus($page_ids = array()) {
        $menu_ids = array();

        // Create Primary Menu
        $primary_menu_id = wp_create_nav_menu('Demo Primary Menu');
        if (!is_wp_error($primary_menu_id)) {
            $menu_ids['primary'] = $primary_menu_id;

            $menu_position = 1;

            // Add Home page
            if (isset($page_ids['home'])) {
                wp_update_nav_menu_item($primary_menu_id, 0, array(
                    'menu-item-title' => 'Home',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['home'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Add About page
            if (isset($page_ids['about'])) {
                wp_update_nav_menu_item($primary_menu_id, 0, array(
                    'menu-item-title' => 'About',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['about'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Add Issues link
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title' => 'Issues',
                'menu-item-url' => get_post_type_archive_link('cp_issue'),
                'menu-item-status' => 'publish',
                'menu-item-position' => $menu_position++,
            ));

            // Add Events link
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title' => 'Events',
                'menu-item-url' => get_post_type_archive_link('cp_event'),
                'menu-item-status' => 'publish',
                'menu-item-position' => $menu_position++,
            ));

            // Add Team link
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title' => 'Team',
                'menu-item-url' => get_post_type_archive_link('cp_team'),
                'menu-item-status' => 'publish',
                'menu-item-position' => $menu_position++,
            ));

            // Add Get Involved page
            if (isset($page_ids['get-involved'])) {
                wp_update_nav_menu_item($primary_menu_id, 0, array(
                    'menu-item-title' => 'Get Involved',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['get-involved'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Add News page
            if (isset($page_ids['news'])) {
                wp_update_nav_menu_item($primary_menu_id, 0, array(
                    'menu-item-title' => 'News',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['news'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Add Contact page
            if (isset($page_ids['contact'])) {
                wp_update_nav_menu_item($primary_menu_id, 0, array(
                    'menu-item-title' => 'Contact',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['contact'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Assign to primary location
            $locations = get_theme_mod('nav_menu_locations');
            if (!is_array($locations)) {
                $locations = array();
            }
            $locations['primary'] = $primary_menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        // Create Footer Menu
        $footer_menu_id = wp_create_nav_menu('Demo Footer Menu');
        if (!is_wp_error($footer_menu_id)) {
            $menu_ids['footer'] = $footer_menu_id;

            $menu_position = 1;

            // Add Privacy Policy page
            if (isset($page_ids['privacy-policy'])) {
                wp_update_nav_menu_item($footer_menu_id, 0, array(
                    'menu-item-title' => 'Privacy Policy',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['privacy-policy'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Add Contact page
            if (isset($page_ids['contact'])) {
                wp_update_nav_menu_item($footer_menu_id, 0, array(
                    'menu-item-title' => 'Contact',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['contact'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Add Press page
            if (isset($page_ids['press'])) {
                wp_update_nav_menu_item($footer_menu_id, 0, array(
                    'menu-item-title' => 'Press',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page_ids['press'],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $menu_position++,
                ));
            }

            // Assign to footer location
            $locations = get_theme_mod('nav_menu_locations');
            if (!is_array($locations)) {
                $locations = array();
            }
            $locations['footer'] = $footer_menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        // Create Social Menu
        $social_menu_id = wp_create_nav_menu('Demo Social Menu');
        if (!is_wp_error($social_menu_id)) {
            $menu_ids['social'] = $social_menu_id;

            // Add social menu items
            wp_update_nav_menu_item($social_menu_id, 0, array(
                'menu-item-title' => 'Facebook',
                'menu-item-url' => 'https://facebook.com/campaignpress',
                'menu-item-status' => 'publish',
                'menu-item-position' => 1,
                'menu-item-attr-title' => 'Follow us on Facebook',
            ));

            wp_update_nav_menu_item($social_menu_id, 0, array(
                'menu-item-title' => 'Twitter',
                'menu-item-url' => 'https://twitter.com/campaignpress',
                'menu-item-status' => 'publish',
                'menu-item-position' => 2,
                'menu-item-attr-title' => 'Follow us on Twitter',
            ));

            wp_update_nav_menu_item($social_menu_id, 0, array(
                'menu-item-title' => 'Instagram',
                'menu-item-url' => 'https://instagram.com/campaignpress',
                'menu-item-status' => 'publish',
                'menu-item-position' => 3,
                'menu-item-attr-title' => 'Follow us on Instagram',
            ));

            // Assign to social location
            $locations = get_theme_mod('nav_menu_locations');
            if (!is_array($locations)) {
                $locations = array();
            }
            $locations['social'] = $social_menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        return $menu_ids;
    }

    /**
     * Populate theme options with demo data
     */
    private function populate_theme_options() {
        // General Options
        update_option('campaignpress_candidate_name', 'Alex Thompson');
        update_option('campaignpress_office_seeking', 'State Senate - District 12');
        update_option('campaignpress_campaign_tagline', 'Fighting for Our Community\'s Future');
        update_option('campaignpress_campaign_year', date('Y'));
        update_option('campaignpress_election_date', date('Y-m-d', strtotime('+90 days')));
        update_option('campaignpress_donation_url', 'https://secure.actblue.com/donate/example');
        update_option('campaignpress_volunteer_url', 'https://example.com/volunteer');

        // Design Options
        update_option('campaignpress_color_scheme', 'democrat-blue');
        update_option('campaignpress_primary_color', '#0066cc');
        update_option('campaignpress_secondary_color', '#333333');
        update_option('campaignpress_accent_color', '#ff6b35');
        update_option('campaignpress_homepage_layout', 'classic-candidate');
        update_option('campaignpress_layout', 'sidebar-right');
        update_option('campaignpress_logo_width', 200);
        update_option('campaignpress_enable_sticky_header', 1);

        // Typography Options
        update_option('campaignpress_heading_font', 'system-ui');
        update_option('campaignpress_body_font', 'system-ui');
        update_option('campaignpress_font_size_base', 16);

        // Social Media Options
        update_option('campaignpress_facebook_url', 'https://facebook.com/campaignpress');
        update_option('campaignpress_twitter_url', 'https://twitter.com/campaignpress');
        update_option('campaignpress_instagram_url', 'https://instagram.com/campaignpress');
        update_option('campaignpress_youtube_url', 'https://youtube.com/@campaignpress');
        update_option('campaignpress_linkedin_url', '');
        update_option('campaignpress_tiktok_url', '');

        // Footer Options
        update_option('campaignpress_show_footer_widgets', 1);
        update_option('campaignpress_footer_text', '<p><strong>Alex Thompson for State Senate</strong><br>Building a better future for our community.</p>');
        update_option('campaignpress_disclaimer_text', '<p><small>Paid for by Friends of Alex Thompson. Not authorized by any candidate or candidate\'s committee.</small></p>');

        // Advanced Options (leave blank for security)
        update_option('campaignpress_custom_css', '/* Add your custom CSS here */');
        update_option('campaignpress_google_analytics_id', '');
        update_option('campaignpress_facebook_pixel_id', '');
        update_option('campaignpress_enable_maintenance_mode', 0);

        // Customizer Options (for backwards compatibility)
        set_theme_mod('campaignpress_color_scheme', 'democrat-blue');
        set_theme_mod('campaignpress_primary_color', '#0066cc');
        set_theme_mod('campaignpress_secondary_color', '#333333');
        set_theme_mod('campaignpress_layout', 'sidebar-right');
        set_theme_mod('campaignpress_homepage_layout', 'classic-candidate');
        set_theme_mod('campaignpress_candidate_name', 'Alex Thompson');
        set_theme_mod('campaignpress_office_seeking', 'State Senate - District 12');
        set_theme_mod('campaignpress_campaign_tagline', 'Fighting for Our Community\'s Future');
        set_theme_mod('campaignpress_donation_url', 'https://secure.actblue.com/donate/example');
        set_theme_mod('campaignpress_facebook_url', 'https://facebook.com/campaignpress');
        set_theme_mod('campaignpress_twitter_url', 'https://twitter.com/campaignpress');
        set_theme_mod('campaignpress_instagram_url', 'https://instagram.com/campaignpress');
        set_theme_mod('campaignpress_youtube_url', 'https://youtube.com/@campaignpress');
    }

    /**
     * Reset theme options to defaults
     */
    private function reset_theme_options() {
        // General Options
        delete_option('campaignpress_candidate_name');
        delete_option('campaignpress_office_seeking');
        delete_option('campaignpress_campaign_tagline');
        delete_option('campaignpress_campaign_year');
        delete_option('campaignpress_election_date');
        delete_option('campaignpress_donation_url');
        delete_option('campaignpress_volunteer_url');

        // Design Options
        delete_option('campaignpress_color_scheme');
        delete_option('campaignpress_primary_color');
        delete_option('campaignpress_secondary_color');
        delete_option('campaignpress_accent_color');
        delete_option('campaignpress_homepage_layout');
        delete_option('campaignpress_layout');
        delete_option('campaignpress_logo_width');
        delete_option('campaignpress_enable_sticky_header');

        // Typography Options
        delete_option('campaignpress_heading_font');
        delete_option('campaignpress_body_font');
        delete_option('campaignpress_font_size_base');

        // Social Media Options
        delete_option('campaignpress_facebook_url');
        delete_option('campaignpress_twitter_url');
        delete_option('campaignpress_instagram_url');
        delete_option('campaignpress_youtube_url');
        delete_option('campaignpress_linkedin_url');
        delete_option('campaignpress_tiktok_url');

        // Footer Options
        delete_option('campaignpress_show_footer_widgets');
        delete_option('campaignpress_footer_text');
        delete_option('campaignpress_disclaimer_text');

        // Advanced Options
        delete_option('campaignpress_custom_css');
        delete_option('campaignpress_google_analytics_id');
        delete_option('campaignpress_facebook_pixel_id');
        delete_option('campaignpress_enable_maintenance_mode');

        // Customizer Options
        remove_theme_mod('campaignpress_color_scheme');
        remove_theme_mod('campaignpress_primary_color');
        remove_theme_mod('campaignpress_secondary_color');
        remove_theme_mod('campaignpress_layout');
        remove_theme_mod('campaignpress_homepage_layout');
        remove_theme_mod('campaignpress_candidate_name');
        remove_theme_mod('campaignpress_office_seeking');
        remove_theme_mod('campaignpress_campaign_tagline');
        remove_theme_mod('campaignpress_donation_url');
        remove_theme_mod('campaignpress_facebook_url');
        remove_theme_mod('campaignpress_twitter_url');
        remove_theme_mod('campaignpress_instagram_url');
        remove_theme_mod('campaignpress_youtube_url');

        // Remove menu locations
        set_theme_mod('nav_menu_locations', array());
    }
}

// Initialize
new CampaignPress_Demo_Content();
