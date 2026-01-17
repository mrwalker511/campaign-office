<?php
/**
 * CRM Import/Export Class
 *
 * Handles CSV import and export for voter files, including support for
 * L2 Political and TargetSmart data formats. Includes field mapping,
 * validation, duplicate detection, and batch processing for large files.
 *
 * @package CampaignPress
 * @subpackage CRM
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRM Import/Export Class
 *
 * @since 1.0.0
 */
class CampaignPress_CRM_Import_Export {

	/**
	 * Contacts instance
	 *
	 * @var CampaignPress_CRM_Contacts
	 */
	private $contacts;

	/**
	 * Segments instance
	 *
	 * @var CampaignPress_CRM_Segments
	 */
	private $segments;

	/**
	 * Batch size for processing
	 *
	 * @var int
	 */
	private $batch_size = 100;

	/**
	 * Supported file formats
	 *
	 * @var array
	 */
	private $supported_formats = array(
		'l2',
		'targetsmart',
		'ngpvan',
		'generic',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->contacts = new CampaignPress_CRM_Contacts();
		$this->segments = new CampaignPress_CRM_Segments();
	}

	/**
	 * Import contacts from CSV file
	 *
	 * @since 1.0.0
	 * @param string $file_path Path to CSV file
	 * @param array  $args Import arguments
	 * @return array|WP_Error Import results or WP_Error on failure
	 */
	public function import_csv( $file_path, $args = array() ) {
		// Validate file exists
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'CSV file not found.', 'campaignpress' ) );
		}

		// Parse arguments
		$defaults = array(
			'format'              => 'generic',
			'field_mapping'       => array(),
			'update_existing'     => false,
			'skip_duplicates'     => true,
			'tag_imported'        => true,
			'tag_id'              => null,
			'create_segment'      => false,
			'segment_name'        => '',
			'has_header'          => true,
			'delimiter'           => ',',
			'enclosure'           => '"',
			'batch_callback'      => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// Validate format
		if ( ! in_array( $args['format'], $this->supported_formats, true ) ) {
			return new WP_Error( 'invalid_format', __( 'Invalid import format.', 'campaignpress' ) );
		}

		// Initialize counters
		$results = array(
			'total_rows'     => 0,
			'imported'       => 0,
			'updated'        => 0,
			'skipped'        => 0,
			'errors'         => array(),
			'processing_time' => 0,
		);

		$start_time = microtime( true );

		// Open file
		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to open CSV file.', 'campaignpress' ) );
		}

		// Get field mapping
		$field_mapping = ! empty( $args['field_mapping'] ) ? $args['field_mapping'] : $this->get_default_field_mapping( $args['format'] );

		// Read header row
		$headers = array();
		if ( $args['has_header'] ) {
			$headers = fgetcsv( $handle, 0, $args['delimiter'], $args['enclosure'] );
			if ( false === $headers ) {
				fclose( $handle );
				return new WP_Error( 'file_error', __( 'Unable to read CSV headers.', 'campaignpress' ) );
			}
		}

		// Create import tag if requested
		$import_tag_id = null;
		if ( $args['tag_imported'] ) {
			if ( ! empty( $args['tag_id'] ) ) {
				$import_tag_id = $args['tag_id'];
			} else {
				$tag_name = 'Import ' . date( 'Y-m-d H:i:s' );
				$tag_result = $this->segments->create_tag( array(
					'name'     => $tag_name,
					'tag_type' => 'import',
					'color'    => '#9b59b6',
				) );
				if ( ! is_wp_error( $tag_result ) ) {
					$import_tag_id = $tag_result;
				}
			}
		}

		// Create segment if requested
		$segment_id = null;
		if ( $args['create_segment'] && ! empty( $args['segment_name'] ) ) {
			$segment_result = $this->segments->create_segment( array(
				'name'         => $args['segment_name'],
				'segment_type' => 'static',
				'description'  => sprintf( __( 'Imported from CSV on %s', 'campaignpress' ), date( 'Y-m-d H:i:s' ) ),
			) );
			if ( ! is_wp_error( $segment_result ) ) {
				$segment_id = $segment_result;
			}
		}

		// Process rows in batches
		$batch = array();
		$row_number = $args['has_header'] ? 1 : 0;

		while ( ( $row = fgetcsv( $handle, 0, $args['delimiter'], $args['enclosure'] ) ) !== false ) {
			$row_number++;
			$results['total_rows']++;

			// Map row to contact data
			$contact_data = $this->map_row_to_contact( $row, $headers, $field_mapping );

			// Validate contact data
			if ( empty( $contact_data['email'] ) && empty( $contact_data['first_name'] ) && empty( $contact_data['last_name'] ) ) {
				$results['skipped']++;
				$results['errors'][] = sprintf( __( 'Row %d: Missing required fields', 'campaignpress' ), $row_number );
				continue;
			}

			// Add to batch
			$batch[] = array(
				'row_number' => $row_number,
				'data'       => $contact_data,
			);

			// Process batch when it reaches batch size
			if ( count( $batch ) >= $this->batch_size ) {
				$batch_results = $this->process_import_batch( $batch, $args, $import_tag_id, $segment_id );
				$results = $this->merge_results( $results, $batch_results );

				// Call batch callback if provided
				if ( is_callable( $args['batch_callback'] ) ) {
					call_user_func( $args['batch_callback'], $results );
				}

				$batch = array();
			}
		}

		// Process remaining rows
		if ( ! empty( $batch ) ) {
			$batch_results = $this->process_import_batch( $batch, $args, $import_tag_id, $segment_id );
			$results = $this->merge_results( $results, $batch_results );
		}

		fclose( $handle );

		// Calculate processing time
		$results['processing_time'] = round( microtime( true ) - $start_time, 2 );

		// Log import
		do_action( 'cp_crm_import_completed', $results, $args );

		return $results;
	}

	/**
	 * Process a batch of import rows
	 *
	 * @since 1.0.0
	 * @param array $batch Batch of rows to process
	 * @param array $args Import arguments
	 * @param int   $import_tag_id Import tag ID
	 * @param int   $segment_id Segment ID
	 * @return array Batch results
	 */
	private function process_import_batch( $batch, $args, $import_tag_id = null, $segment_id = null ) {
		$results = array(
			'imported' => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		foreach ( $batch as $item ) {
			$row_number = $item['row_number'];
			$contact_data = $item['data'];

			// Check for existing contact
			$existing_contact = null;
			if ( ! empty( $contact_data['email'] ) ) {
				$existing_contact = $this->contacts->get_contact_by_email( $contact_data['email'] );
			} elseif ( ! empty( $contact_data['voter_id'] ) ) {
				$existing_contact = $this->contacts->get_contact_by_voter_id( $contact_data['voter_id'] );
			}

			// Handle existing contact
			if ( $existing_contact ) {
				if ( $args['update_existing'] ) {
					$result = $this->contacts->update_contact( $existing_contact->id, $contact_data );
					if ( is_wp_error( $result ) ) {
						$results['skipped']++;
						$results['errors'][] = sprintf( __( 'Row %d: %s', 'campaignpress' ), $row_number, $result->get_error_message() );
					} else {
						$results['updated']++;
						$contact_id = $existing_contact->id;

						// Add tag and segment
						if ( $import_tag_id ) {
							$this->segments->add_tag_to_contact( $contact_id, $import_tag_id );
						}
						if ( $segment_id ) {
							$this->segments->add_contact_to_segment( $segment_id, $contact_id );
						}
					}
				} else {
					$results['skipped']++;
				}
			} else {
				// Create new contact
				$result = $this->contacts->create_contact( $contact_data );
				if ( is_wp_error( $result ) ) {
					$results['skipped']++;
					$results['errors'][] = sprintf( __( 'Row %d: %s', 'campaignpress' ), $row_number, $result->get_error_message() );
				} else {
					$results['imported']++;
					$contact_id = $result;

					// Add tag and segment
					if ( $import_tag_id ) {
						$this->segments->add_tag_to_contact( $contact_id, $import_tag_id );
					}
					if ( $segment_id ) {
						$this->segments->add_contact_to_segment( $segment_id, $contact_id );
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Map CSV row to contact data
	 *
	 * @since 1.0.0
	 * @param array $row CSV row data
	 * @param array $headers CSV headers
	 * @param array $field_mapping Field mapping
	 * @return array Contact data
	 */
	private function map_row_to_contact( $row, $headers, $field_mapping ) {
		$contact_data = array();

		foreach ( $field_mapping as $csv_field => $contact_field ) {
			// Get value from row
			$value = null;

			if ( ! empty( $headers ) ) {
				// Use header-based mapping
				$header_index = array_search( $csv_field, $headers, true );
				if ( false !== $header_index && isset( $row[ $header_index ] ) ) {
					$value = $row[ $header_index ];
				}
			} else {
				// Use numeric index mapping
				if ( is_numeric( $csv_field ) && isset( $row[ $csv_field ] ) ) {
					$value = $row[ $csv_field ];
				}
			}

			// Set contact field if value exists
			if ( null !== $value && '' !== $value ) {
				$contact_data[ $contact_field ] = trim( $value );
			}
		}

		return $contact_data;
	}

	/**
	 * Get default field mapping for format
	 *
	 * @since 1.0.0
	 * @param string $format Import format
	 * @return array Field mapping
	 */
	private function get_default_field_mapping( $format ) {
		$mappings = array(
			'l2' => array(
				'LALVOTERID'       => 'voter_id',
				'Voters_FirstName' => 'first_name',
				'Voters_LastName'  => 'last_name',
				'Residence_Addresses_AddressLine' => 'address_line1',
				'Residence_Addresses_City' => 'city',
				'Residence_Addresses_State' => 'state',
				'Residence_Addresses_Zip' => 'zip_code',
				'Voters_BirthDate' => 'date_of_birth',
				'Voters_Age'       => 'age',
				'Voters_Gender'    => 'gender',
				'Parties_Description' => 'party_affiliation',
				'VoterReg_Status'  => 'voter_registration_status',
				'CongressionalDistrict' => 'congressional_district',
				'StateHouse'       => 'state_house_district',
				'StateSenate'      => 'state_senate_district',
				'County'           => 'county',
				'Precinct'         => 'precinct',
				'Voters_Phone'     => 'phone',
				'Residence_Addresses_Latitude' => 'latitude',
				'Residence_Addresses_Longitude' => 'longitude',
			),
			'targetsmart' => array(
				'vb_voterbase_id'  => 'voter_id',
				'vb_vf_first_name' => 'first_name',
				'vb_vf_last_name'  => 'last_name',
				'vb_vf_reg_address_line_1' => 'address_line1',
				'vb_vf_reg_city'   => 'city',
				'vb_vf_reg_state'  => 'state',
				'vb_vf_reg_zip'    => 'zip_code',
				'vb_vf_county_name' => 'county',
				'vb_vf_age'        => 'age',
				'vb_vf_gender'     => 'gender',
				'vb_vf_party_name' => 'party_affiliation',
				'vb_vf_registration_status' => 'voter_registration_status',
				'vb_vf_congressional_district' => 'congressional_district',
				'vb_vf_state_house_district' => 'state_house_district',
				'vb_vf_state_senate_district' => 'state_senate_district',
				'vb_vf_precinct_name' => 'precinct',
				'vb_phone'         => 'phone',
				'vb_email'         => 'email',
				'vb_tsmart_latitude' => 'latitude',
				'vb_tsmart_longitude' => 'longitude',
				'vb_tsmart_partisan_score' => 'partisan_score',
			),
			'ngpvan' => array(
				'VanID'            => 'voter_id',
				'FirstName'        => 'first_name',
				'LastName'         => 'last_name',
				'Email'            => 'email',
				'Phone'            => 'phone',
				'Address'          => 'address_line1',
				'City'             => 'city',
				'State'            => 'state',
				'Zip'              => 'zip_code',
				'DateOfBirth'      => 'date_of_birth',
				'Sex'              => 'gender',
			),
			'generic' => array(
				'first_name'       => 'first_name',
				'last_name'        => 'last_name',
				'email'            => 'email',
				'phone'            => 'phone',
				'address'          => 'address_line1',
				'address_line_1'   => 'address_line1',
				'address_line_2'   => 'address_line2',
				'city'             => 'city',
				'state'            => 'state',
				'zip'              => 'zip_code',
				'zip_code'         => 'zip_code',
				'age'              => 'age',
				'gender'           => 'gender',
				'party'            => 'party_affiliation',
				'voter_id'         => 'voter_id',
			),
		);

		return isset( $mappings[ $format ] ) ? $mappings[ $format ] : $mappings['generic'];
	}

	/**
	 * Merge batch results into main results
	 *
	 * @since 1.0.0
	 * @param array $main_results Main results array
	 * @param array $batch_results Batch results array
	 * @return array Merged results
	 */
	private function merge_results( $main_results, $batch_results ) {
		$main_results['imported'] += $batch_results['imported'];
		$main_results['updated']  += $batch_results['updated'];
		$main_results['skipped']  += $batch_results['skipped'];
		$main_results['errors']    = array_merge( $main_results['errors'], $batch_results['errors'] );

		return $main_results;
	}

	/**
	 * Export contacts to CSV
	 *
	 * @since 1.0.0
	 * @param array $args Export arguments
	 * @return string|WP_Error File path on success, WP_Error on failure
	 */
	public function export_csv( $args = array() ) {
		$defaults = array(
			'format'        => 'generic',
			'contact_ids'   => array(),
			'segment_id'    => null,
			'tag_id'        => null,
			'filters'       => array(),
			'fields'        => array(),
			'include_custom_fields' => false,
			'file_name'     => 'contacts-export-' . date( 'Y-m-d-His' ) . '.csv',
		);

		$args = wp_parse_args( $args, $defaults );

		// Get contacts to export
		$contacts = $this->get_contacts_for_export( $args );

		if ( empty( $contacts ) ) {
			return new WP_Error( 'no_contacts', __( 'No contacts to export.', 'campaignpress' ) );
		}

		// Get fields to export
		$export_fields = ! empty( $args['fields'] ) ? $args['fields'] : $this->get_default_export_fields( $args['format'] );

		// Create temporary file
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/crm-exports/' . $args['file_name'];

		// Create directory if doesn't exist
		if ( ! file_exists( dirname( $file_path ) ) ) {
			wp_mkdir_p( dirname( $file_path ) );
		}

		// Open file for writing
		$handle = fopen( $file_path, 'w' );
		if ( false === $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to create export file.', 'campaignpress' ) );
		}

		// Write header row
		fputcsv( $handle, array_keys( $export_fields ) );

		// Write contact rows
		foreach ( $contacts as $contact ) {
			$row = array();
			foreach ( $export_fields as $header => $field ) {
				$row[] = isset( $contact->$field ) ? $contact->$field : '';
			}
			fputcsv( $handle, $row );
		}

		fclose( $handle );

		// Log export
		do_action( 'cp_crm_export_completed', $file_path, count( $contacts ), $args );

		return $file_path;
	}

	/**
	 * Get contacts for export based on criteria
	 *
	 * @since 1.0.0
	 * @param array $args Export arguments
	 * @return array Array of contacts
	 */
	private function get_contacts_for_export( $args ) {
		$contacts = array();

		// Export specific contact IDs
		if ( ! empty( $args['contact_ids'] ) ) {
			foreach ( $args['contact_ids'] as $contact_id ) {
				$contact = $this->contacts->get_contact( $contact_id );
				if ( $contact ) {
					$contacts[] = $contact;
				}
			}
		}
		// Export by segment
		elseif ( ! empty( $args['segment_id'] ) ) {
			$result = $this->segments->get_segment_contacts( $args['segment_id'], array( 'per_page' => 999999 ) );
			$contacts = $result['contacts'];
		}
		// Export by tag
		elseif ( ! empty( $args['tag_id'] ) ) {
			$result = $this->contacts->get_contacts( array(
				'tags'     => array( $args['tag_id'] ),
				'per_page' => 999999,
			) );
			$contacts = $result['contacts'];
		}
		// Export by filters
		else {
			$filters = ! empty( $args['filters'] ) ? $args['filters'] : array();
			$filters['per_page'] = 999999;
			$result = $this->contacts->get_contacts( $filters );
			$contacts = $result['contacts'];
		}

		return $contacts;
	}

	/**
	 * Get default export fields
	 *
	 * @since 1.0.0
	 * @param string $format Export format
	 * @return array Export fields mapping
	 */
	private function get_default_export_fields( $format ) {
		$fields = array(
			'First Name'       => 'first_name',
			'Last Name'        => 'last_name',
			'Email'            => 'email',
			'Phone'            => 'phone',
			'Mobile Phone'     => 'mobile_phone',
			'Address Line 1'   => 'address_line1',
			'Address Line 2'   => 'address_line2',
			'City'             => 'city',
			'State'            => 'state',
			'Zip Code'         => 'zip_code',
			'County'           => 'county',
			'Congressional District' => 'congressional_district',
			'State House District' => 'state_house_district',
			'State Senate District' => 'state_senate_district',
			'Precinct'         => 'precinct',
			'Date of Birth'    => 'date_of_birth',
			'Age'              => 'age',
			'Gender'           => 'gender',
			'Party Affiliation' => 'party_affiliation',
			'Voter Registration Status' => 'voter_registration_status',
			'Voter ID'         => 'voter_id',
			'Engagement Score' => 'engagement_score',
			'Last Contact Date' => 'last_contact_date',
			'Contact Count'    => 'contact_count',
			'Is Volunteer'     => 'is_volunteer',
			'Is Donor'         => 'is_donor',
			'Is Likely Supporter' => 'is_likely_supporter',
			'Support Level'    => 'support_level',
		);

		return apply_filters( 'cp_crm_export_fields', $fields, $format );
	}

	/**
	 * Parse CSV headers
	 *
	 * @since 1.0.0
	 * @param string $file_path Path to CSV file
	 * @return array|WP_Error Array of headers or WP_Error on failure
	 */
	public function parse_csv_headers( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'CSV file not found.', 'campaignpress' ) );
		}

		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to open CSV file.', 'campaignpress' ) );
		}

		$headers = fgetcsv( $handle );
		fclose( $handle );

		if ( false === $headers ) {
			return new WP_Error( 'file_error', __( 'Unable to read CSV headers.', 'campaignpress' ) );
		}

		return $headers;
	}

	/**
	 * Detect CSV format
	 *
	 * @since 1.0.0
	 * @param array $headers CSV headers
	 * @return string Detected format
	 */
	public function detect_csv_format( $headers ) {
		// L2 Political format detection
		$l2_fields = array( 'LALVOTERID', 'Voters_FirstName', 'Voters_LastName' );
		if ( $this->headers_contain_fields( $headers, $l2_fields ) ) {
			return 'l2';
		}

		// TargetSmart format detection
		$targetsmart_fields = array( 'vb_voterbase_id', 'vb_vf_first_name', 'vb_vf_last_name' );
		if ( $this->headers_contain_fields( $headers, $targetsmart_fields ) ) {
			return 'targetsmart';
		}

		// NGP VAN format detection
		$ngpvan_fields = array( 'VanID', 'FirstName', 'LastName' );
		if ( $this->headers_contain_fields( $headers, $ngpvan_fields ) ) {
			return 'ngpvan';
		}

		// Default to generic
		return 'generic';
	}

	/**
	 * Check if headers contain specific fields
	 *
	 * @since 1.0.0
	 * @param array $headers CSV headers
	 * @param array $fields Fields to check for
	 * @return bool True if all fields found
	 */
	private function headers_contain_fields( $headers, $fields ) {
		$found = 0;
		foreach ( $fields as $field ) {
			if ( in_array( $field, $headers, true ) ) {
				$found++;
			}
		}
		return $found === count( $fields );
	}

	/**
	 * Generate field mapping suggestions
	 *
	 * @since 1.0.0
	 * @param array  $headers CSV headers
	 * @param string $format Import format
	 * @return array Suggested field mapping
	 */
	public function suggest_field_mapping( $headers, $format = 'generic' ) {
		$default_mapping = $this->get_default_field_mapping( $format );
		$suggested_mapping = array();

		foreach ( $headers as $header ) {
			// Try exact match first
			if ( isset( $default_mapping[ $header ] ) ) {
				$suggested_mapping[ $header ] = $default_mapping[ $header ];
				continue;
			}

			// Try fuzzy matching
			$header_lower = strtolower( $header );
			foreach ( $default_mapping as $csv_field => $contact_field ) {
				if ( stripos( $header, $csv_field ) !== false || stripos( $csv_field, $header ) !== false ) {
					$suggested_mapping[ $header ] = $contact_field;
					break;
				}
			}

			// Check for common variations
			if ( ! isset( $suggested_mapping[ $header ] ) ) {
				$variations = array(
					'fname' => 'first_name',
					'firstname' => 'first_name',
					'lname' => 'last_name',
					'lastname' => 'last_name',
					'emailaddress' => 'email',
					'phonenumber' => 'phone',
					'street' => 'address_line1',
					'postal' => 'zip_code',
					'zipcode' => 'zip_code',
				);

				$header_clean = str_replace( array( ' ', '_', '-' ), '', $header_lower );
				if ( isset( $variations[ $header_clean ] ) ) {
					$suggested_mapping[ $header ] = $variations[ $header_clean ];
				}
			}
		}

		return $suggested_mapping;
	}

	/**
	 * Validate import file
	 *
	 * @since 1.0.0
	 * @param string $file_path Path to CSV file
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function validate_import_file( $file_path ) {
		// Check file exists
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'File not found.', 'campaignpress' ) );
		}

		// Check file size (max 50MB)
		$file_size = filesize( $file_path );
		if ( $file_size > 50 * 1024 * 1024 ) {
			return new WP_Error( 'file_too_large', __( 'File size exceeds 50MB limit.', 'campaignpress' ) );
		}

		// Check file extension
		$file_extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
		if ( ! in_array( $file_extension, array( 'csv', 'txt' ), true ) ) {
			return new WP_Error( 'invalid_extension', __( 'File must be a CSV file.', 'campaignpress' ) );
		}

		// Try to open file
		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to open file.', 'campaignpress' ) );
		}

		// Check if file is empty
		$first_line = fgetcsv( $handle );
		fclose( $handle );

		if ( false === $first_line || empty( $first_line ) ) {
			return new WP_Error( 'empty_file', __( 'File is empty.', 'campaignpress' ) );
		}

		return true;
	}

	/**
	 * Get import preview
	 *
	 * @since 1.0.0
	 * @param string $file_path Path to CSV file
	 * @param int    $rows Number of rows to preview
	 * @return array|WP_Error Preview data or WP_Error on failure
	 */
	public function get_import_preview( $file_path, $rows = 10 ) {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'CSV file not found.', 'campaignpress' ) );
		}

		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to open CSV file.', 'campaignpress' ) );
		}

		$preview = array(
			'headers' => array(),
			'rows'    => array(),
			'format'  => 'generic',
		);

		// Read headers
		$preview['headers'] = fgetcsv( $handle );
		if ( false === $preview['headers'] ) {
			fclose( $handle );
			return new WP_Error( 'file_error', __( 'Unable to read CSV headers.', 'campaignpress' ) );
		}

		// Detect format
		$preview['format'] = $this->detect_csv_format( $preview['headers'] );

		// Read preview rows
		$row_count = 0;
		while ( $row_count < $rows && ( $row = fgetcsv( $handle ) ) !== false ) {
			$preview['rows'][] = $row;
			$row_count++;
		}

		fclose( $handle );

		return $preview;
	}

	/**
	 * Get supported formats
	 *
	 * @since 1.0.0
	 * @return array Supported formats with descriptions
	 */
	public function get_supported_formats() {
		return array(
			'l2' => array(
				'name'        => 'L2 Political',
				'description' => 'Standard L2 Political voter file format',
			),
			'targetsmart' => array(
				'name'        => 'TargetSmart',
				'description' => 'TargetSmart voter file format',
			),
			'ngpvan' => array(
				'name'        => 'NGP VAN',
				'description' => 'NGP VAN export format',
			),
			'generic' => array(
				'name'        => 'Generic CSV',
				'description' => 'Generic CSV with standard field names',
			),
		);
	}

	/**
	 * Set batch size
	 *
	 * @since 1.0.0
	 * @param int $size Batch size
	 */
	public function set_batch_size( $size ) {
		$this->batch_size = absint( $size );
	}

	/**
	 * Get batch size
	 *
	 * @since 1.0.0
	 * @return int Batch size
	 */
	public function get_batch_size() {
		return $this->batch_size;
	}
}
