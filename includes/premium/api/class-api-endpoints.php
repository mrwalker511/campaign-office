<?php
/**
 * API Endpoints Class
 *
 * Defines all REST API endpoints for CampaignPress including CRUD operations
 * for contacts, events, volunteers, donations, and field operations.
 *
 * @package CampaignPress
 * @subpackage API
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * API Endpoints Class
 *
 * @since 1.0.0
 */
class CampaignPress_API_Endpoints {

    /**
     * API namespace
     *
     * @var string
     */
    private $namespace = 'campaignpress/v1';

    /**
     * Database object
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Add Cache Headers to Response
     *
     * @since 2.0.0
     * @param WP_REST_Response $response Response object.
     * @param mixed            $data Data to generate ETag from.
     * @param int              $max_age Cache max age in seconds.
     * @return WP_REST_Response
     */
    private function add_cache_headers( $response, $data, $max_age = 300 ) {
        $etag = md5( wp_json_encode( $data ) );
        $response->header( 'ETag', '"' . $etag . '"' );
        $response->header( 'Cache-Control', 'public, max-age=' . $max_age );
        $response->header( 'Vary', 'Accept, Accept-Encoding' );

        if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) {
            $client_etag = trim( $_SERVER['HTTP_IF_NONE_MATCH'], '"' );
            if ( $client_etag === $etag ) {
                $response->set_status( 304 );
                $response->set_data( null );
            }
        }

        return $response;
    }

    /**
     * Add Pagination Link Headers
     *
     * @since 2.0.0
     * @param WP_REST_Response $response Response object.
     * @param int              $page Current page.
     * @param int              $total_pages Total pages.
     * @param string           $base_url Base URL.
     * @return WP_REST_Response
     */
    private function add_pagination_links( $response, $page, $total_pages, $base_url ) {
        $links = array();

        if ( $page > 1 ) {
            $links[] = '<' . add_query_arg( 'page', $page - 1, $base_url ) . '>; rel="prev"';
            $links[] = '<' . add_query_arg( 'page', 1, $base_url ) . '>; rel="first"';
        }

        if ( $page < $total_pages ) {
            $links[] = '<' . add_query_arg( 'page', $page + 1, $base_url ) . '>; rel="next"';
            $links[] = '<' . add_query_arg( 'page', $total_pages, $base_url ) . '>; rel="last"';
        }

        if ( ! empty( $links ) ) {
            $response->header( 'Link', implode( ', ', $links ) );
        }

        return $response;
    }

    /**
     * Register Routes
     *
     * @since 1.0.0
     */
    public function register_routes() {
        // Contacts endpoints
        register_rest_route( $this->namespace, '/contacts', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_contacts' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_collection_params(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_contact' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_contact_schema(),
            ),
        ) );

        register_rest_route( $this->namespace, '/contacts/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_contact' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_contact' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_contact_schema(),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_contact' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        // Events endpoints
        register_rest_route( $this->namespace, '/events', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_events' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_collection_params(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_event' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_event_schema(),
            ),
        ) );

        register_rest_route( $this->namespace, '/events/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_event' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_event' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_event_schema(),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_event' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        // Volunteers endpoints
        register_rest_route( $this->namespace, '/volunteers', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_volunteers' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_collection_params(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_volunteer' ),
                'permission_callback' => array( $this, 'check_permission' ),
                'args'                => $this->get_volunteer_schema(),
            ),
        ) );

        register_rest_route( $this->namespace, '/volunteers/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_volunteer' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_volunteer' ),
                'permission_callback' => array( $this, 'check_permission' ),
            ),
        ) );

        // Donations webhook endpoint
        register_rest_route( $this->namespace, '/donations', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'record_donation' ),
            'permission_callback' => array( $this, 'check_webhook_permission' ),
            'args'                => $this->get_donation_schema(),
        ) );

        // Analytics endpoint
        register_rest_route( $this->namespace, '/analytics', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_analytics' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'type' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'enum'              => array( 'dashboard', 'fundraising', 'volunteers', 'events', 'engagement' ),
                    'default'           => 'dashboard',
                ),
                'days' => array(
                    'required'          => false,
                    'type'              => 'integer',
                    'default'           => 30,
                ),
            ),
        ) );

        // Field Operations - Canvassing
        register_rest_route( $this->namespace, '/field-ops/canvassing', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'submit_canvassing_result' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => $this->get_canvassing_schema(),
        ) );

        // Field Operations - Phone Banking
        register_rest_route( $this->namespace, '/field-ops/phone-banking', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'submit_phone_banking_result' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => $this->get_phone_banking_schema(),
        ) );

        // Third-party integration endpoints
        register_rest_route( $this->namespace, '/integrations/nationbuilder/sync', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'sync_nationbuilder' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/ngp-van/import', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'import_ngp_van' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/action-network/sync', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'sync_action_network' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Batch operations endpoint
        register_rest_route( $this->namespace, '/batch', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'batch_operations' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'requests' => array(
                    'required'          => true,
                    'type'              => 'array',
                    'validate_callback' => array( $this, 'validate_batch_requests' ),
                ),
            ),
        ) );
    }

    // ========================================================================
    // PERMISSION CALLBACKS
    // ========================================================================

    /**
     * Check Permission
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return bool True if user has permission.
     */
    public function check_permission( $request ) {
        // Check if API is enabled
        if ( ! get_option( 'campaignpress_api_enabled', 1 ) ) {
            return new WP_Error(
                'api_disabled',
                __( 'API is currently disabled.', 'campaign-office' ),
                array( 'status' => 503 )
            );
        }

        // Check if authentication is required
        $require_auth = get_option( 'campaignpress_api_require_authentication', 1 );

        if ( $require_auth && ! is_user_logged_in() ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Authentication required.', 'campaign-office' ),
                array( 'status' => 401 )
            );
        }

        // Check user capabilities for write operations
        if ( in_array( $request->get_method(), array( 'POST', 'PUT', 'DELETE' ) ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return new WP_Error(
                    'rest_forbidden',
                    __( 'You do not have permission to perform this action.', 'campaign-office' ),
                    array( 'status' => 403 )
                );
            }
        }

        return true;
    }

    /**
     * Check Webhook Permission
     *
     * Special permission check for webhook endpoints.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return bool True if webhook is authorized.
     */
    public function check_webhook_permission( $request ) {
        // Check webhook signature if present
        $signature = $request->get_header( 'X-Webhook-Signature' );
        if ( $signature ) {
            return $this->verify_webhook_signature( $signature, $request->get_body() );
        }

        // Fall back to standard permission check
        return $this->check_permission( $request );
    }

    /**
     * Verify Webhook Signature
     *
     * @since 1.0.0
     * @param string $signature Signature from header.
     * @param string $payload Request payload.
     * @return bool True if signature is valid.
     */
    private function verify_webhook_signature( $signature, $payload ) {
        $secret = get_option( 'campaignpress_webhook_secret', '' );
        if ( empty( $secret ) ) {
            return false;
        }

        $expected_signature = hash_hmac( 'sha256', $payload, $secret );
        return hash_equals( $expected_signature, $signature );
    }

    // ========================================================================
    // CONTACTS ENDPOINTS
    // ========================================================================

    /**
     * Get Contacts
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_contacts( $request ) {
        $page = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search = $request->get_param( 'search' );
        $orderby = $request->get_param( 'orderby' );
        $order = $request->get_param( 'order' );

        $cache_key = 'contacts_' . md5( wp_json_encode( array( $page, $per_page, $search, $orderby, $order ) ) );
        $cached_data = wp_cache_get( $cache_key, 'campaignpress_api' );

        if ( false !== $cached_data ) {
            $response = rest_ensure_response( $cached_data['contacts'] );
            $response->header( 'X-WP-Total', $cached_data['total'] );
            $response->header( 'X-WP-TotalPages', $cached_data['total_pages'] );
            $response->header( 'X-Cache', 'HIT' );

            $base_url = rest_url( $this->namespace . '/contacts' );
            $this->add_pagination_links( $response, $page, $cached_data['total_pages'], $base_url );
            $this->add_cache_headers( $response, $cached_data['contacts'], 300 );

            return $response;
        }

        $table_name = $this->wpdb->prefix . 'campaignpress_contacts';

        $sql = "SELECT * FROM {$table_name} WHERE 1=1";

        if ( ! empty( $search ) ) {
            $search = '%' . $this->wpdb->esc_like( $search ) . '%';
            $sql .= $this->wpdb->prepare(
                " AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)",
                $search,
                $search,
                $search
            );
        }

        $allowed_orderby = array( 'id', 'first_name', 'last_name', 'email', 'created_at' );
        if ( in_array( $orderby, $allowed_orderby ) ) {
            $sql .= sprintf( " ORDER BY %s %s", esc_sql( $orderby ), esc_sql( $order ) );
        } else {
            $sql .= " ORDER BY id DESC";
        }

        $offset = ( $page - 1 ) * $per_page;
        $sql .= $this->wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );

        $contacts = $this->wpdb->get_results( $sql );

        $count_sql = "SELECT COUNT(*) FROM {$table_name} WHERE 1=1";
        if ( ! empty( $request->get_param( 'search' ) ) ) {
            $search = '%' . $this->wpdb->esc_like( $request->get_param( 'search' ) ) . '%';
            $count_sql .= $this->wpdb->prepare(
                " AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)",
                $search,
                $search,
                $search
            );
        }
        $total = $this->wpdb->get_var( $count_sql );
        $total_pages = ceil( $total / $per_page );

        wp_cache_set(
            $cache_key,
            array(
                'contacts'    => $contacts,
                'total'       => $total,
                'total_pages' => $total_pages,
            ),
            'campaignpress_api',
            5 * MINUTE_IN_SECONDS
        );

        $response = rest_ensure_response( $contacts );
        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', $total_pages );
        $response->header( 'X-Cache', 'MISS' );

        $base_url = rest_url( $this->namespace . '/contacts' );
        $this->add_pagination_links( $response, $page, $total_pages, $base_url );
        $this->add_cache_headers( $response, $contacts, 300 );

        return $response;
    }

    /**
     * Get Contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_contact( $request ) {
        $id = $request->get_param( 'id' );
        $table_name = $this->wpdb->prefix . 'campaignpress_contacts';

        $contact = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $id
            )
        );

        if ( ! $contact ) {
            return new WP_Error(
                'contact_not_found',
                __( 'Contact not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        return rest_ensure_response( $contact );
    }

    /**
     * Create Contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function create_contact( $request ) {
        $table_name = $this->wpdb->prefix . 'campaignpress_contacts';

        $data = array(
            'first_name'       => sanitize_text_field( $request->get_param( 'first_name' ) ),
            'last_name'        => sanitize_text_field( $request->get_param( 'last_name' ) ),
            'email'            => sanitize_email( $request->get_param( 'email' ) ),
            'phone'            => sanitize_text_field( $request->get_param( 'phone' ) ),
            'address'          => sanitize_text_field( $request->get_param( 'address' ) ),
            'city'             => sanitize_text_field( $request->get_param( 'city' ) ),
            'state'            => sanitize_text_field( $request->get_param( 'state' ) ),
            'zip'              => sanitize_text_field( $request->get_param( 'zip' ) ),
            'tags'             => sanitize_text_field( $request->get_param( 'tags' ) ),
            'source'           => sanitize_text_field( $request->get_param( 'source' ) ),
            'engagement_score' => intval( $request->get_param( 'engagement_score' ) ),
        );

        $inserted = $this->wpdb->insert( $table_name, $data );

        if ( ! $inserted ) {
            return new WP_Error(
                'contact_creation_failed',
                __( 'Failed to create contact.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $contact_id = $this->wpdb->insert_id;
        $contact = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $contact_id )
        );

        wp_cache_flush_group( 'campaignpress_api' );

        do_action( 'campaignpress_contact_created', $contact );

        return rest_ensure_response( $contact );
    }

    /**
     * Update Contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function update_contact( $request ) {
        $id = $request->get_param( 'id' );
        $table_name = $this->wpdb->prefix . 'campaignpress_contacts';

        // Check if contact exists
        $contact = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
        );

        if ( ! $contact ) {
            return new WP_Error(
                'contact_not_found',
                __( 'Contact not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        $data = array();
        $allowed_fields = array( 'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'tags', 'source', 'engagement_score' );

        foreach ( $allowed_fields as $field ) {
            if ( $request->has_param( $field ) ) {
                if ( $field === 'email' ) {
                    $data[ $field ] = sanitize_email( $request->get_param( $field ) );
                } elseif ( $field === 'engagement_score' ) {
                    $data[ $field ] = intval( $request->get_param( $field ) );
                } else {
                    $data[ $field ] = sanitize_text_field( $request->get_param( $field ) );
                }
            }
        }

        $updated = $this->wpdb->update(
            $table_name,
            $data,
            array( 'id' => $id )
        );

        if ( $updated === false ) {
            return new WP_Error(
                'contact_update_failed',
                __( 'Failed to update contact.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $contact = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
        );

        wp_cache_flush_group( 'campaignpress_api' );

        do_action( 'campaignpress_contact_updated', $contact );

        return rest_ensure_response( $contact );
    }

    /**
     * Delete Contact
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function delete_contact( $request ) {
        $id = $request->get_param( 'id' );
        $table_name = $this->wpdb->prefix . 'campaignpress_contacts';

        // Check if contact exists
        $contact = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
        );

        if ( ! $contact ) {
            return new WP_Error(
                'contact_not_found',
                __( 'Contact not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        $deleted = $this->wpdb->delete(
            $table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        if ( ! $deleted ) {
            return new WP_Error(
                'contact_deletion_failed',
                __( 'Failed to delete contact.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        wp_cache_flush_group( 'campaignpress_api' );

        do_action( 'campaignpress_contact_deleted', $contact );

        return rest_ensure_response( array(
            'deleted' => true,
            'id'      => $id,
        ) );
    }

    // ========================================================================
    // EVENTS ENDPOINTS
    // ========================================================================

    /**
     * Get Events
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_events( $request ) {
        $page = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $orderby = $request->get_param( 'orderby' );
        $order = $request->get_param( 'order' );

        $args = array(
            'post_type'      => 'campaign_event',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => $orderby,
            'order'          => $order,
        );

        $query = new WP_Query( $args );

        $events = array();
        foreach ( $query->posts as $post ) {
            $events[] = array(
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'description' => $post->post_content,
                'date'        => get_post_meta( $post->ID, '_campaign_event_date', true ),
                'location'    => get_post_meta( $post->ID, '_campaign_event_location', true ),
                'capacity'    => get_post_meta( $post->ID, '_campaign_event_capacity', true ),
                'rsvp_count'  => get_post_meta( $post->ID, '_campaign_event_rsvp_count', true ),
            );
        }

        $response = rest_ensure_response( $events );
        $response->header( 'X-WP-Total', $query->found_posts );
        $response->header( 'X-WP-TotalPages', $query->max_num_pages );

        return $response;
    }

    /**
     * Get Event
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_event( $request ) {
        $id = $request->get_param( 'id' );
        $post = get_post( $id );

        if ( ! $post || $post->post_type !== 'campaign_event' ) {
            return new WP_Error(
                'event_not_found',
                __( 'Event not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        $event = array(
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'description' => $post->post_content,
            'date'        => get_post_meta( $post->ID, '_campaign_event_date', true ),
            'location'    => get_post_meta( $post->ID, '_campaign_event_location', true ),
            'capacity'    => get_post_meta( $post->ID, '_campaign_event_capacity', true ),
            'rsvp_count'  => get_post_meta( $post->ID, '_campaign_event_rsvp_count', true ),
        );

        return rest_ensure_response( $event );
    }

    /**
     * Create Event
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function create_event( $request ) {
        $post_data = array(
            'post_type'    => 'campaign_event',
            'post_title'   => sanitize_text_field( $request->get_param( 'title' ) ),
            'post_content' => wp_kses_post( $request->get_param( 'description' ) ),
            'post_status'  => 'publish',
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error(
                'event_creation_failed',
                __( 'Failed to create event.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        // Update meta fields
        update_post_meta( $post_id, '_campaign_event_date', sanitize_text_field( $request->get_param( 'date' ) ) );
        update_post_meta( $post_id, '_campaign_event_location', sanitize_text_field( $request->get_param( 'location' ) ) );
        update_post_meta( $post_id, '_campaign_event_capacity', intval( $request->get_param( 'capacity' ) ) );
        update_post_meta( $post_id, '_campaign_event_rsvp_count', 0 );

        $post = get_post( $post_id );
        $event = array(
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'description' => $post->post_content,
            'date'        => get_post_meta( $post->ID, '_campaign_event_date', true ),
            'location'    => get_post_meta( $post->ID, '_campaign_event_location', true ),
            'capacity'    => get_post_meta( $post->ID, '_campaign_event_capacity', true ),
            'rsvp_count'  => get_post_meta( $post->ID, '_campaign_event_rsvp_count', true ),
        );

        // Trigger webhook
        do_action( 'campaignpress_event_created', $event );

        return rest_ensure_response( $event );
    }

    /**
     * Update Event
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function update_event( $request ) {
        $id = $request->get_param( 'id' );
        $post = get_post( $id );

        if ( ! $post || $post->post_type !== 'campaign_event' ) {
            return new WP_Error(
                'event_not_found',
                __( 'Event not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        $post_data = array(
            'ID' => $id,
        );

        if ( $request->has_param( 'title' ) ) {
            $post_data['post_title'] = sanitize_text_field( $request->get_param( 'title' ) );
        }

        if ( $request->has_param( 'description' ) ) {
            $post_data['post_content'] = wp_kses_post( $request->get_param( 'description' ) );
        }

        wp_update_post( $post_data );

        // Update meta fields
        if ( $request->has_param( 'date' ) ) {
            update_post_meta( $id, '_campaign_event_date', sanitize_text_field( $request->get_param( 'date' ) ) );
        }
        if ( $request->has_param( 'location' ) ) {
            update_post_meta( $id, '_campaign_event_location', sanitize_text_field( $request->get_param( 'location' ) ) );
        }
        if ( $request->has_param( 'capacity' ) ) {
            update_post_meta( $id, '_campaign_event_capacity', intval( $request->get_param( 'capacity' ) ) );
        }

        $post = get_post( $id );
        $event = array(
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'description' => $post->post_content,
            'date'        => get_post_meta( $post->ID, '_campaign_event_date', true ),
            'location'    => get_post_meta( $post->ID, '_campaign_event_location', true ),
            'capacity'    => get_post_meta( $post->ID, '_campaign_event_capacity', true ),
            'rsvp_count'  => get_post_meta( $post->ID, '_campaign_event_rsvp_count', true ),
        );

        // Trigger webhook
        do_action( 'campaignpress_event_updated', $event );

        return rest_ensure_response( $event );
    }

    /**
     * Delete Event
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function delete_event( $request ) {
        $id = $request->get_param( 'id' );
        $post = get_post( $id );

        if ( ! $post || $post->post_type !== 'campaign_event' ) {
            return new WP_Error(
                'event_not_found',
                __( 'Event not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        $deleted = wp_delete_post( $id, true );

        if ( ! $deleted ) {
            return new WP_Error(
                'event_deletion_failed',
                __( 'Failed to delete event.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        // Trigger webhook
        do_action( 'campaignpress_event_deleted', array( 'id' => $id ) );

        return rest_ensure_response( array(
            'deleted' => true,
            'id'      => $id,
        ) );
    }

    // ========================================================================
    // VOLUNTEERS ENDPOINTS
    // ========================================================================

    /**
     * Get Volunteers
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_volunteers( $request ) {
        $page = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $orderby = $request->get_param( 'orderby' );
        $order = $request->get_param( 'order' );

        $table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

        $sql = "SELECT * FROM {$table_name}";

        // Ordering
        $allowed_orderby = array( 'id', 'signup_date' );
        if ( in_array( $orderby, $allowed_orderby ) ) {
            $sql .= sprintf( " ORDER BY %s %s", esc_sql( $orderby ), esc_sql( $order ) );
        } else {
            $sql .= " ORDER BY id DESC";
        }

        // Pagination
        $offset = ( $page - 1 ) * $per_page;
        $sql .= $this->wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );

        $volunteers = $this->wpdb->get_results( $sql );

        $total = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

        $response = rest_ensure_response( $volunteers );
        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

        return $response;
    }

    /**
     * Get Volunteer
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_volunteer( $request ) {
        $id = $request->get_param( 'id' );
        $table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

        $volunteer = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $id
            )
        );

        if ( ! $volunteer ) {
            return new WP_Error(
                'volunteer_not_found',
                __( 'Volunteer not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        return rest_ensure_response( $volunteer );
    }

    /**
     * Create Volunteer
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function create_volunteer( $request ) {
        $table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

        $data = array(
            'contact_id'   => intval( $request->get_param( 'contact_id' ) ),
            'skills'       => sanitize_text_field( $request->get_param( 'skills' ) ),
            'availability' => sanitize_text_field( $request->get_param( 'availability' ) ),
            'source'       => sanitize_text_field( $request->get_param( 'source' ) ),
            'signup_date'  => current_time( 'mysql' ),
        );

        $inserted = $this->wpdb->insert( $table_name, $data );

        if ( ! $inserted ) {
            return new WP_Error(
                'volunteer_creation_failed',
                __( 'Failed to create volunteer.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $volunteer_id = $this->wpdb->insert_id;
        $volunteer = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $volunteer_id )
        );

        // Trigger webhook
        do_action( 'campaignpress_volunteer_created', $volunteer );

        return rest_ensure_response( $volunteer );
    }

    /**
     * Update Volunteer
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function update_volunteer( $request ) {
        $id = $request->get_param( 'id' );
        $table_name = $this->wpdb->prefix . 'campaignpress_volunteers';

        $volunteer = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
        );

        if ( ! $volunteer ) {
            return new WP_Error(
                'volunteer_not_found',
                __( 'Volunteer not found.', 'campaign-office' ),
                array( 'status' => 404 )
            );
        }

        $data = array();
        $allowed_fields = array( 'skills', 'availability', 'status' );

        foreach ( $allowed_fields as $field ) {
            if ( $request->has_param( $field ) ) {
                $data[ $field ] = sanitize_text_field( $request->get_param( $field ) );
            }
        }

        $updated = $this->wpdb->update(
            $table_name,
            $data,
            array( 'id' => $id )
        );

        if ( $updated === false ) {
            return new WP_Error(
                'volunteer_update_failed',
                __( 'Failed to update volunteer.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $volunteer = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id )
        );

        // Trigger webhook
        do_action( 'campaignpress_volunteer_updated', $volunteer );

        return rest_ensure_response( $volunteer );
    }

    // ========================================================================
    // DONATIONS ENDPOINT (Webhook)
    // ========================================================================

    /**
     * Record Donation
     *
     * Webhook endpoint for recording donations from third-party services.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function record_donation( $request ) {
        $table_name = $this->wpdb->prefix . 'campaignpress_donations';

        $data = array(
            'contact_id'    => intval( $request->get_param( 'contact_id' ) ),
            'amount'        => floatval( $request->get_param( 'amount' ) ),
            'currency'      => sanitize_text_field( $request->get_param( 'currency' ) ),
            'source'        => sanitize_text_field( $request->get_param( 'source' ) ),
            'transaction_id' => sanitize_text_field( $request->get_param( 'transaction_id' ) ),
            'status'        => 'completed',
            'donation_date' => current_time( 'mysql' ),
        );

        $inserted = $this->wpdb->insert( $table_name, $data );

        if ( ! $inserted ) {
            return new WP_Error(
                'donation_recording_failed',
                __( 'Failed to record donation.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $donation_id = $this->wpdb->insert_id;
        $donation = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $donation_id )
        );

        // Trigger webhook
        do_action( 'campaignpress_donation_received', $donation );

        return rest_ensure_response( $donation );
    }

    // ========================================================================
    // ANALYTICS ENDPOINT
    // ========================================================================

    /**
     * Get Analytics
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function get_analytics( $request ) {
        $type = $request->get_param( 'type' );
        $days = $request->get_param( 'days' );

        $analytics = isset( $GLOBALS['campaignpress_analytics'] ) ? $GLOBALS['campaignpress_analytics'] : null;

        if ( ! $analytics ) {
            return new WP_Error(
                'analytics_unavailable',
                __( 'Analytics module not available.', 'campaign-office' ),
                array( 'status' => 503 )
            );
        }

        $data = array();

        switch ( $type ) {
            case 'dashboard':
                $data = $analytics->get_dashboard_data( $days );
                break;
            case 'fundraising':
                $data = $analytics->get_fundraising_analytics( $days );
                break;
            case 'volunteers':
                $data = $analytics->get_volunteer_analytics( $days );
                break;
            case 'events':
                $data = $analytics->get_event_analytics( $days );
                break;
            case 'engagement':
                $data = $analytics->get_engagement_analytics( $days );
                break;
        }

        return rest_ensure_response( $data );
    }

    // ========================================================================
    // FIELD OPERATIONS ENDPOINTS
    // ========================================================================

    /**
     * Submit Canvassing Result
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function submit_canvassing_result( $request ) {
        $table_name = $this->wpdb->prefix . 'campaignpress_canvassing_results';

        $data = array(
            'volunteer_id' => intval( $request->get_param( 'volunteer_id' ) ),
            'address'      => sanitize_text_field( $request->get_param( 'address' ) ),
            'response'     => sanitize_text_field( $request->get_param( 'response' ) ),
            'notes'        => sanitize_textarea_field( $request->get_param( 'notes' ) ),
            'canvass_date' => current_time( 'mysql' ),
        );

        $inserted = $this->wpdb->insert( $table_name, $data );

        if ( ! $inserted ) {
            return new WP_Error(
                'canvassing_submission_failed',
                __( 'Failed to submit canvassing result.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $result_id = $this->wpdb->insert_id;
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $result_id )
        );

        return rest_ensure_response( $result );
    }

    /**
     * Submit Phone Banking Result
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function submit_phone_banking_result( $request ) {
        $table_name = $this->wpdb->prefix . 'campaignpress_phone_banking';

        $data = array(
            'volunteer_id' => intval( $request->get_param( 'volunteer_id' ) ),
            'contact_id'   => intval( $request->get_param( 'contact_id' ) ),
            'call_result'  => sanitize_text_field( $request->get_param( 'call_result' ) ),
            'notes'        => sanitize_textarea_field( $request->get_param( 'notes' ) ),
            'call_date'    => current_time( 'mysql' ),
        );

        $inserted = $this->wpdb->insert( $table_name, $data );

        if ( ! $inserted ) {
            return new WP_Error(
                'phone_banking_submission_failed',
                __( 'Failed to submit phone banking result.', 'campaign-office' ),
                array( 'status' => 500 )
            );
        }

        $result_id = $this->wpdb->insert_id;
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $result_id )
        );

        return rest_ensure_response( $result );
    }

    // ========================================================================
    // THIRD-PARTY INTEGRATIONS
    // ========================================================================

    /**
     * Sync with NationBuilder
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function sync_nationbuilder( $request ) {
        // Placeholder for NationBuilder integration
        // Would implement actual sync logic here

        return rest_ensure_response( array(
            'status'  => 'success',
            'message' => 'NationBuilder sync initiated',
        ) );
    }

    /**
     * Import from NGP VAN
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function import_ngp_van( $request ) {
        // Placeholder for NGP VAN integration
        // Would implement actual import logic here

        return rest_ensure_response( array(
            'status'  => 'success',
            'message' => 'NGP VAN import initiated',
        ) );
    }

    /**
     * Sync with Action Network
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function sync_action_network( $request ) {
        // Placeholder for Action Network integration
        // Would implement actual sync logic here

        return rest_ensure_response( array(
            'status'  => 'success',
            'message' => 'Action Network sync initiated',
        ) );
    }

    // ========================================================================
    // BATCH OPERATIONS
    // ========================================================================

    /**
     * Validate Batch Requests
     *
     * @since 2.0.0
     * @param array $requests Batch requests.
     * @return bool True if valid.
     */
    public function validate_batch_requests( $requests ) {
        if ( ! is_array( $requests ) ) {
            return false;
        }

        if ( count( $requests ) > 50 ) {
            return new WP_Error(
                'batch_limit_exceeded',
                __( 'Maximum 50 requests per batch allowed.', 'campaign-office' ),
                array( 'status' => 400 )
            );
        }

        foreach ( $requests as $request ) {
            if ( ! isset( $request['method'] ) || ! isset( $request['path'] ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Batch Operations
     *
     * @since 2.0.0
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function batch_operations( $request ) {
        $requests = $request->get_param( 'requests' );
        $responses = array();

        foreach ( $requests as $index => $batch_request ) {
            $method = strtoupper( $batch_request['method'] );
            $path = $batch_request['path'];
            $body = isset( $batch_request['body'] ) ? $batch_request['body'] : array();

            $internal_request = new WP_REST_Request( $method, $path );
            if ( ! empty( $body ) ) {
                $internal_request->set_body_params( $body );
            }

            $response = rest_do_request( $internal_request );

            $responses[ $index ] = array(
                'status' => $response->get_status(),
                'data'   => $response->get_data(),
            );

            if ( is_wp_error( $response ) ) {
                $responses[ $index ]['error'] = array(
                    'code'    => $response->get_error_code(),
                    'message' => $response->get_error_message(),
                );
            }
        }

        return rest_ensure_response( array(
            'responses' => $responses,
        ) );
    }

    // ========================================================================
    // SCHEMA DEFINITIONS
    // ========================================================================

    /**
     * Get Collection Parameters
     *
     * @since 1.0.0
     * @return array Collection parameters.
     */
    private function get_collection_params() {
        return array(
            'page' => array(
                'required'          => false,
                'type'              => 'integer',
                'default'           => 1,
                'minimum'           => 1,
            ),
            'per_page' => array(
                'required'          => false,
                'type'              => 'integer',
                'default'           => 20,
                'minimum'           => 1,
                'maximum'           => 100,
            ),
            'search' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'orderby' => array(
                'required'          => false,
                'type'              => 'string',
                'default'           => 'id',
            ),
            'order' => array(
                'required'          => false,
                'type'              => 'string',
                'default'           => 'DESC',
                'enum'              => array( 'ASC', 'DESC' ),
            ),
        );
    }

    /**
     * Get Contact Schema
     *
     * @since 1.0.0
     * @return array Contact schema.
     */
    private function get_contact_schema() {
        return array(
            'first_name' => array(
                'required'          => true,
                'type'              => 'string',
            ),
            'last_name' => array(
                'required'          => true,
                'type'              => 'string',
            ),
            'email' => array(
                'required'          => true,
                'type'              => 'string',
                'format'            => 'email',
            ),
            'phone' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'address' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'city' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'state' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'zip' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'tags' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'source' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'engagement_score' => array(
                'required'          => false,
                'type'              => 'integer',
                'minimum'           => 0,
                'maximum'           => 100,
            ),
        );
    }

    /**
     * Get Event Schema
     *
     * @since 1.0.0
     * @return array Event schema.
     */
    private function get_event_schema() {
        return array(
            'title' => array(
                'required'          => true,
                'type'              => 'string',
            ),
            'description' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'date' => array(
                'required'          => true,
                'type'              => 'string',
                'format'            => 'date-time',
            ),
            'location' => array(
                'required'          => true,
                'type'              => 'string',
            ),
            'capacity' => array(
                'required'          => false,
                'type'              => 'integer',
                'minimum'           => 1,
            ),
        );
    }

    /**
     * Get Volunteer Schema
     *
     * @since 1.0.0
     * @return array Volunteer schema.
     */
    private function get_volunteer_schema() {
        return array(
            'contact_id' => array(
                'required'          => true,
                'type'              => 'integer',
            ),
            'skills' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'availability' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'source' => array(
                'required'          => false,
                'type'              => 'string',
            ),
        );
    }

    /**
     * Get Donation Schema
     *
     * @since 1.0.0
     * @return array Donation schema.
     */
    private function get_donation_schema() {
        return array(
            'contact_id' => array(
                'required'          => true,
                'type'              => 'integer',
            ),
            'amount' => array(
                'required'          => true,
                'type'              => 'number',
                'minimum'           => 0.01,
            ),
            'currency' => array(
                'required'          => false,
                'type'              => 'string',
                'default'           => 'USD',
            ),
            'source' => array(
                'required'          => false,
                'type'              => 'string',
            ),
            'transaction_id' => array(
                'required'          => false,
                'type'              => 'string',
            ),
        );
    }

    /**
     * Get Canvassing Schema
     *
     * @since 1.0.0
     * @return array Canvassing schema.
     */
    private function get_canvassing_schema() {
        return array(
            'volunteer_id' => array(
                'required'          => true,
                'type'              => 'integer',
            ),
            'address' => array(
                'required'          => true,
                'type'              => 'string',
            ),
            'response' => array(
                'required'          => true,
                'type'              => 'string',
                'enum'              => array( 'positive', 'negative', 'neutral', 'not_home' ),
            ),
            'notes' => array(
                'required'          => false,
                'type'              => 'string',
            ),
        );
    }

    /**
     * Get Phone Banking Schema
     *
     * @since 1.0.0
     * @return array Phone banking schema.
     */
    private function get_phone_banking_schema() {
        return array(
            'volunteer_id' => array(
                'required'          => true,
                'type'              => 'integer',
            ),
            'contact_id' => array(
                'required'          => true,
                'type'              => 'integer',
            ),
            'call_result' => array(
                'required'          => true,
                'type'              => 'string',
                'enum'              => array( 'answered', 'voicemail', 'no_answer', 'wrong_number' ),
            ),
            'notes' => array(
                'required'          => false,
                'type'              => 'string',
            ),
        );
    }
}
