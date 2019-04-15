<?php

use WP_STATISTICS\Hits;

/**
 * Class WP_Statistics_Rest
 */
class WP_Statistics_Rest {

	// Set Default namespace
	const route = 'wpstatistics/v1';

	// Set Default Statistic Save method
	const func = 'hit';

	/**
	 * Setup an Wordpress REst Api action.
	 */
	public function __construct() {
		global $WP_Statistics;

		/*
		 * add Router Rest Api
		 */
		if ( isset( $WP_Statistics ) and $WP_Statistics->option->get( 'use_cache_plugin' ) ) {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}
	}

	/*
	 * Add Endpoint Route
	 */
	public function register_routes() {
		// Get Hit
		register_rest_route( self::route, '/' . self::func, array(
			'methods'  => 'POST',
			'callback' => array( $this, 'hit' ),
		) );
	}

	/*
	 * Wp Statistic Hit Save
	 */
	public function hit() {
		global $WP_Statistics;

		/*
		 * Check Is Test Service Request
		 */
		if ( isset( $_POST['rest-api-wp-statistics'] ) ) {

			return array( "rest-api-wp-statistics" => "OK" );
		}


		//Check Auth Key Request
		if ( ! isset( $_POST[ Hits::$rest_hits_key ] ) ) {
			return new \WP_Error( 'error', 'You have no right to access', array( 'status' => 403 ) );
		}

		// If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.
		// This seems to happen sometimes with WP Cron calls.
		if ( ! is_object( $WP_Statistics ) ) {
			return;
		}

		$h = new \WP_STATISTICS\Hits();

		// Call the online users tracking code.
		if ( $WP_Statistics->option->get( 'useronline' ) ) {
			$h->Check_online();
		}

		// Call the visitor tracking code.
		if ( $WP_Statistics->option->get( 'visitors' ) ) {
			$h->Visitors();
		}

		// Call the visit tracking code.
		if ( $WP_Statistics->option->get( 'visits' ) ) {
			$h->Visits();
		}

		// Call the page tracking code.
		if ( $WP_Statistics->option->get( 'pages' ) ) {
			$h->Pages();
		}
	}

}
