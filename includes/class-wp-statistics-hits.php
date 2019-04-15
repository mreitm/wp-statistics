<?php

namespace WP_STATISTICS;

class Hits {

	// Setup our public/private/protected variables.
	public $result = null;

	protected $location = '000';
	public $exclusion_match = false;
	public $exclusion_reason = '';
	private $current_page_id;
	private $current_page_type;
	public $current_visitor_id = 0;

	/**
	 * WP_Statistics_Hits constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct() {
		// location
		$this->location = GeoIP::getCountry();

		//Check Exclusion
		$exclusion              = Exclusion::check();
		$this->exclusion_match  = $exclusion['exclusion_match'];
		$this->exclusion_reason = $exclusion['exclusion_reason'];
	}

	// This function records visits to the site.
	public function Visits() {
		global $wpdb, $WP_Statistics;

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
		if ( ! $this->exclusion_match ) {

			// Check to see if we're a returning visitor.
			$this->result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}statistics_visit ORDER BY `{$wpdb->prefix}statistics_visit`.`ID` DESC" );

			// If we're a returning visitor, update the current record in the database, otherwise, create a new one.
			if ( $this->result->last_counter != \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) ) {

				// We'd normally use the WordPress insert function, but since we may run in to a race condition where another hit to the site has already created a new entry in the database
				// for this IP address we want to do an "INSERT ... ON DUPLICATE KEY" which WordPress doesn't support.
				$sqlstring = $wpdb->prepare(
					'INSERT INTO ' . $wpdb->prefix . 'statistics_visit (last_visit, last_counter, visit) VALUES ( %s, %s, %d) ON DUPLICATE KEY UPDATE visit = visit + ' . WP_STATISTICS\Visitor::getCoefficient(),
					\WP_STATISTICS\TimeZone::getCurrentDate(),
					\WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ),
					\WP_STATISTICS\Visitor::getCoefficient()
				);

				$wpdb->query( $sqlstring );
			} else {
				$sqlstring = $wpdb->prepare(
					'UPDATE ' . $wpdb->prefix . 'statistics_visit SET `visit` = `visit` + %d, `last_visit` = %s WHERE `last_counter` = %s',
					\WP_STATISTICS\Visitor::getCoefficient(),
					\WP_STATISTICS\TimeZone::getCurrentDate(),
					$this->result->last_counter
				);

				$wpdb->query( $sqlstring );
			}
		}
	}

	//Get current Page detail
	public function get_page_detail() {

		//if is Cache enable
		if ( WP_Statistics_Rest::is_rest() ) {
			$this->current_page_id   = WP_Statistics_Rest::params( 'current_page_id' );
			$this->current_page_type = WP_Statistics_Rest::params( 'current_page_type' );
		} else {
			//Get Page Type
			$get_page_type           = Helper::get_page_type();
			$this->current_page_id   = $get_page_type['id'];
			$this->current_page_type = $get_page_type['type'];
		}

	}

	// This function records unique visitors to the site.
	public function Visitors() {
		global $wpdb, $WP_Statistics;

		//Get Current Page detail
		$this->get_page_detail();

		//Check honeypot Page
		if ( $WP_Statistics->option->get( 'use_honeypot' ) && $WP_Statistics->option->get( 'honeypot_postid' ) > 0 && $WP_Statistics->option->get( 'honeypot_postid' ) == $this->current_page_id && $this->current_page_id > 0 ) {
			$this->exclusion_match  = true;
			$this->exclusion_reason = 'honeypot';
		}

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
		// The exception here is if we've matched a honey page, we want to lookup the user and flag them
		// as having been trapped in the honey pot for later exclusions.
		if ( $this->exclusion_reason == 'honeypot' || ! $this->exclusion_match ) {

			// Check to see if we already have an entry in the database.
			$check_ip_db = \WP_STATISTICS\IP::StoreIP();
			if ( \WP_STATISTICS\IP::getHashIP() != false ) {
				$check_ip_db = \WP_STATISTICS\IP::getHashIP();
			}

			//Check Exist This User in Current Day
			$this->result = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `last_counter` = '" . \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) . "' AND `ip` = '{$check_ip_db}'" );

			// Check to see if this is a visit to the honey pot page, flag it when we create the new entry.
			$honeypot = 0;
			if ( $this->exclusion_reason == 'honeypot' ) {
				$honeypot = 1;
			}

			// If we don't create a new one, otherwise update the old one.
			if ( ! $this->result ) {

				// If we've been told to store the entire user agent, do so.
				if ( $WP_Statistics->option->get( 'store_ua' ) == true ) {
					if ( WP_Statistics_Rest::is_rest() ) {
						$ua = WP_Statistics_Rest::params( 'ua' );
					} else {
						$ua = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' );
					}
				} else {
					$ua = '';
				}

				// Store the result.
				add_filter( 'query', 'wp_statistics_ignore_insert', 10 );
				$wpdb->insert(
					$wpdb->prefix . 'statistics_visitor',
					array(
						'last_counter' => \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ),
						'referred'     => \WP_STATISTICS\Referred::get(),
						'agent'        => $WP_Statistics->agent['browser'],
						'platform'     => $WP_Statistics->agent['platform'],
						'version'      => $WP_Statistics->agent['version'],
						'ip'           => \WP_STATISTICS\IP::getHashIP() ? \WP_STATISTICS\IP::getHashIP() : \WP_STATISTICS\IP::StoreIP(),
						'location'     => $this->location,
						'UAString'     => $ua,
						'hits'         => 1,
						'honeypot'     => $honeypot,
					),
					array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
				);
				$this->current_visitor_id = $wpdb->insert_id;
				remove_filter( 'query', 'wp_statistics_ignore_insert', 10 );

				// Now parse the referrer and store the results in the search table if the database has been converted.
				// Also make sure we actually inserted a row on the INSERT IGNORE above or we'll create duplicate entries.
				if ( $WP_Statistics->option->get( 'search_converted' ) && $wpdb->insert_id ) {

					$search_engines = WP_STATISTICS\SearchEngine::getList();
					if ( WP_Statistics_Rest::is_rest() ) {
						$referred = WP_Statistics_Rest::params( 'referred' );
					} else {
						$referred = \WP_STATISTICS\Referred::get();
					}

					// Parse the URL in to it's component parts.
					if ( wp_http_validate_url( $referred ) ) {
						$parts = parse_url( $referred );

						// Loop through the SE list until we find which search engine matches.
						foreach ( $search_engines as $key => $value ) {
							$search_regex = WP_STATISTICS\SearchEngine::regex( $key );

							preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

							if ( isset( $matches[1] ) ) {
								$data['last_counter'] = \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' );
								$data['engine']       = $key;
								$data['words']        = WP_STATISTICS\SearchEngine::getByQueryString( $referred );
								$data['host']         = $parts['host'];
								$data['visitor']      = $wpdb->insert_id;

								if ( $data['words'] == 'No search query found!' ) {
									$data['words'] = '';
								}

								$wpdb->insert( $wpdb->prefix . 'statistics_search', $data );
							}
						}
					}
				}
			} else {

				// Normally we've done all of our exclusion matching during the class creation, however for the robot threshold is calculated here to avoid another call the database.
				if ( $WP_Statistics->option->get( 'robot_threshold' ) > 0 && $this->result->hits + 1 > $WP_Statistics->option->get( 'robot_threshold' ) ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'robot_threshold';
				} else if ( $this->result->honeypot ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'honeypot';
				} else {

					//Get Current Visitors ID
					$this->current_visitor_id = $this->result->ID;

					$sqlstring = $wpdb->prepare(
						'UPDATE `' . $wpdb->prefix . 'statistics_visitor` SET `hits` = `hits` + %d, `honeypot` = %d WHERE `ID` = %d',
						1,
						$honeypot,
						$this->result->ID
					);

					$wpdb->query( $sqlstring );
				}
			}
		}

		if ( $this->exclusion_match ) {
			WP_STATISTICS\Exclusion::record( array( 'exclusion_match' => $this->exclusion_match, 'exclusion_reason' => $this->exclusion_reason ) );
		}
	}

	// This function records page hits.
	public function Pages() {
		global $wpdb, $WP_Statistics;

		// If we're a web crawler or referral from ourselves or an excluded address don't record the page hit.
		if ( ! $this->exclusion_match ) {

			// Don't track anything but actual pages and posts, unless we've been told to.
			$is_track_all = false;
			if ( WP_Statistics_Rest::is_rest() ) {
				if ( WP_Statistics_Rest::params( 'track_all' ) == 1 ) {
					$is_track_all = true;
				}
			} else {
				if ( Helper::is_track_all_page() ) {
					$is_track_all = true;
				}
			}

			if ( $is_track_all === true ) {

				// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
				$this->get_page_detail();

				// If we didn't find a page id, we don't have anything else to do.
				if ( $this->current_page_type == "unknown" ) {
					return;
				}

				// Get the current page URI.
				if ( WP_Statistics_Rest::is_rest() ) {
					$page_uri = WP_Statistics_Rest::params( 'page_uri' );
				} else {
					$page_uri = wp_statistics_get_uri();
				}

				//Get String Search Wordpress
				$is_search = false;
				if ( WP_Statistics_Rest::is_rest() ) {
					if ( WP_Statistics_Rest::params( 'search_query' ) != "" ) {
						$page_uri  = "?s=" . WP_Statistics_Rest::params( 'search_query' );
						$is_search = true;
					}
				} else {
					$get_page_type = Helper::get_page_type();
					if ( array_key_exists( "search_query", $get_page_type ) ) {
						$page_uri  = "?s=" . $get_page_type['search_query'];
						$is_search = true;
					}
				}

				if ( $WP_Statistics->option->get( 'strip_uri_parameters' ) and $is_search === false ) {
					$temp = explode( '?', $page_uri );
					if ( $temp !== false ) {
						$page_uri = $temp[0];
					}
				}

				// Limit the URI length to 255 characters, otherwise we may overrun the SQL field size.
				$page_uri = substr( $page_uri, 0, 255 );

				// If we have already been to this page today (a likely scenario), just update the count on the record.
				$exist = $wpdb->get_row( "SELECT `page_id` FROM {$wpdb->prefix}statistics_pages WHERE `date` = '" . \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) . "' " . ( $is_search === true ? "AND `uri` = '" . $page_uri . "'" : "" ) . "AND `type` = '{$this->current_page_type}' AND `id` = {$this->current_page_id}", ARRAY_A );
				if ( null !== $exist ) {
					$sql          = $wpdb->prepare( "UPDATE {$wpdb->prefix}statistics_pages SET `count` = `count` + 1 WHERE `date` = '" . \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) . "' " . ( $is_search === true ? "AND `uri` = '" . $page_uri . "'" : "" ) . "AND `type` = '{$this->current_page_type}' AND `id` = %d", $this->current_page_id );
					$this->result = $wpdb->query( $sql );
					$page_id      = $exist['page_id'];

				} else {
					add_filter( 'query', 'wp_statistics_ignore_insert', 10 );
					$wpdb->insert(
						$wpdb->prefix . 'statistics_pages',
						array(
							'uri'   => $page_uri,
							'date'  => \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ),
							'count' => 1,
							'id'    => $this->current_page_id,
							'type'  => $this->current_page_type
						)
					);
					$page_id = $wpdb->insert_id;
					remove_filter( 'query', 'wp_statistics_ignore_insert', 10 );
				}

				//Set Visitor Relationships
				if ( $WP_Statistics->option->get( 'visitors' ) == true and $WP_Statistics->option->get( 'visitors_log' ) == true and $this->current_visitor_id > 0 ) {
					$this->visitors_relationships( $page_id, $this->current_visitor_id );
				}

			}
		}
	}

	//Set Visitor Relationships
	public function visitors_relationships( $page_id, $visitor_id ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'statistics_visitor_relationships',
			array(
				'visitor_id' => $visitor_id,
				'page_id'    => $page_id,
				'date'       => current_time( 'mysql' )
			),
			array( '%d', '%d', '%s' )
		);
	}

	// This function checks to see if the current user (as defined by their IP address) has an entry in the database.
	// Note we set the $this->result variable so we don't have to re-execute the query when we do the user update.
	public function Is_user() {
		global $wpdb, $WP_Statistics;

		// Check to see if we already have an entry in the database.
		$check_ip_db = \WP_STATISTICS\IP::StoreIP();
		if ( \WP_STATISTICS\IP::getHashIP() != false ) {
			$check_ip_db = \WP_STATISTICS\IP::getHashIP();
		}

		//Check Exist
		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_useronline WHERE `ip` = '{$check_ip_db}'" );

		if ( $this->result ) {
			return true;
		}
	}

	// This function add/update/delete the online users in the database.
	public function Check_online() {
		global $WP_Statistics;

		// If we're a web crawler or referral from ourselves or an excluded address don't record the user as online, unless we've been told to anyway.
		if ( ! $this->exclusion_match || $WP_Statistics->option->get( 'all_online' ) ) {

			// If the current user exists in the database already,
			// Just update them, otherwise add them
			if ( $this->Is_user() ) {
				$this->Update_user();
			} else {
				$this->Add_user();
			}
		}

	}

	// This function adds a user to the database.
	public function Add_user() {
		global $wpdb, $WP_Statistics;

		//Check is User
		if ( ! $this->Is_user() ) {

			// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
			$this->get_page_detail();

			// Insert the user in to the database.
			$wpdb->insert(
				$wpdb->prefix . 'statistics_useronline',
				array(
					'ip'        => \WP_STATISTICS\IP::getHashIP() ? \WP_STATISTICS\IP::getHashIP() : \WP_STATISTICS\IP::StoreIP(),
					'timestamp' => \WP_STATISTICS\TimeZone::getCurrentTimestamp(),
					'created'   => \WP_STATISTICS\TimeZone::getCurrentTimestamp(),
					'date'      => \WP_STATISTICS\TimeZone::getCurrentDate(),
					'referred'  => \WP_STATISTICS\Referred::get(),
					'agent'     => $WP_Statistics->agent['browser'],
					'platform'  => $WP_Statistics->agent['platform'],
					'version'   => $WP_Statistics->agent['version'],
					'location'  => $this->location,
					'user_id'   => self::get_user_id(),
					'page_id'   => $this->current_page_id,
					'type'      => $this->current_page_type
				)
			);
		}
	}

	/**
	 * Get User ID
	 */
	public static function get_user_id() {

		//create Empty
		$user_id = 0;

		//if Rest Request
		if ( WP_Statistics_Rest::is_rest() ) {
			if ( WP_Statistics_Rest::params( 'user_id' ) != "" ) {
				$user_id = WP_Statistics_Rest::params( 'user_id' );
			}
		} else {
			if ( is_user_logged_in() ) {
				return get_current_user_id();
			}
		}

		return $user_id;
	}

	// This function updates a user in the database.
	public function Update_user() {
		global $wpdb, $WP_Statistics;

		// Make sure we found the user earlier when we called Is_user().
		if ( $this->result ) {

			// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
			$this->get_page_detail();

			// Update the database with the new information.
			$wpdb->update(
				$wpdb->prefix . 'statistics_useronline',
				array(
					'timestamp' => \WP_STATISTICS\TimeZone::getCurrentTimestamp(),
					'date'      => \WP_STATISTICS\TimeZone::getCurrentDate(),
					'referred'  => \WP_STATISTICS\Referred::get(),
					'user_id'   => self::get_user_id(),
					'page_id'   => $this->current_page_id,
					'type'      => $this->current_page_type
				),
				array( 'ip' => \WP_STATISTICS\IP::getHashIP() ? \WP_STATISTICS\IP::getHashIP() : \WP_STATISTICS\IP::StoreIP() )
			);
		}
	}

}