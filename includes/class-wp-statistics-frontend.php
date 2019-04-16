<?php

namespace WP_STATISTICS;


class Frontend {

	public function __construct() {
		global $WP_Statistics;

		# Enable ShortCode in Widget
		add_filter( 'widget_text', 'do_shortcode' );

		# Add the honey trap code in the footer.
		add_action( 'wp_footer', array( $this, 'add_honeypot' ) );

		# Enqueue scripts & styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		# Get Visitor information and Save To Database
		add_action( 'wp', array( $this, 'record_hits' ) );

		# Add inline Rest Request
		add_action( 'wp_head', array( $this, 'add_inline_rest_js' ) );

		# Add Html Comment in head
		if ( ! $WP_Statistics->option->get( 'use_cache_plugin' ) ) {
			add_action( 'wp_head', array( $this, 'html_comment' ) );
		}

		# Check to show hits in posts/pages
		if ( $WP_Statistics->option->get( 'show_hits' ) ) {
			add_filter( 'the_content', array( $this, 'show_hits' ) );
		}
	}

	/*
	 * Create Comment support Wappalyzer
	 */
	public function html_comment() {
		echo '<!-- Analytics by WP-Statistics v' . WP_STATISTICS_VERSION . ' - ' . WP_STATISTICS_SITE . ' -->' . "\n";
	}

	/**
	 * Footer Action
	 */
	public function add_honeypot() {
		global $WP_Statistics;
		if ( $WP_Statistics->option->get( 'use_honeypot' ) && $WP_Statistics->option->get( 'honeypot_postid' ) > 0 ) {
			$post_url = get_permalink( $WP_Statistics->option->get( 'honeypot_postid' ) );
			echo '<a href="' . $post_url . '" style="display: none;">&nbsp;</a>';
		}
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts() {

		// Load our CSS to be used.
		if ( is_admin_bar_showing() ) {
			wp_enqueue_style( 'wpstatistics-css', WP_STATISTICS_URL . 'assets/css/frontend.css', true, WP_STATISTICS_VERSION );
		}
	}

	/*
	 * Inline Js
	 */
	public function add_inline_rest_js() {
		global $WP_Statistics;

		if ( $WP_Statistics->option->get( 'use_cache_plugin' ) ) {
			$this->html_comment();
			//TODO Solve Load Rest Class
			echo '<script>var WP_Statistics_http = new XMLHttpRequest();WP_Statistics_http.open(\'POST\', \'' . add_query_arg( array( '_' => time() ), path_join( get_rest_url(), WP_Statistics_Rest::route . '/' . WP_Statistics_Rest::func ) ) . '\', true);WP_Statistics_http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");WP_Statistics_http.send("' . Hits::$rest_hits_key . '=" + JSON.stringify(' . self::set_default_params() . '));</script>' . "\n";
		}
	}

	/*
	 * Set Default Params Rest Api
	 */
	static public function set_default_params() {
		global $WP_Statistics;

		// Create Empty Params Object
		$params = array();

		//Set Url WP-Rest API
		$params['base'] = rtrim( get_rest_url(), "/" );
		$params['api']  = rtrim( rest_get_url_prefix(), "/" );

		//Set UserAgent [browser|platform|version]
		$params = wp_parse_args( $params, UserAgent::getUserAgent() );

		//Set Referred
		$params['referred'] = Referred::get();

		//Set IP
		$params['ip'] = IP::getIP();

		//Set Hash Ip
		$params['hash_ip'] = IP::getHashIP();

		//exclude
		$exclude                  = Exclusion::check();
		$params['exclude']        = $exclude['exclusion_match'];
		$params['exclude_reason'] = $exclude['exclusion_reason'];

		//User Agent String
		$params['ua'] = UserAgent::getHttpUserAgent();

		//track all page
		$params['track_all'] = ( Pages::is_track_all_page() === true ? 1 : 0 );

		//timestamp
		$params['timestamp'] = Timezone::getCurrentTimestamp();

		//Set Page Type
		$get_page_type               = Pages::get_page_type();
		$params['current_page_type'] = $get_page_type['type'];
		$params['current_page_id']   = $get_page_type['id'];
		$params['search_query']      = ( isset( $get_page_type['search_query'] ) ? $get_page_type['search_query'] : '' );

		//page url
		$params['page_uri'] = Pages::get_page_uri();

		//Get User id
		$params['user_id'] = $WP_Statistics->user->ID;

		//return Json Data
		return Helper::standard_json_encode( $params );
	}

	/**
	 * Record WP-Statistics Hits in Frontend
	 *
	 * @throws \Exception
	 */
	public function record_hits() {
		global $WP_Statistics;

		// Disable if User Active cache Plugin
		if ( ! $WP_Statistics->option->get( 'use_cache_plugin' ) ) {
			Hits::record();
		}
	}

	/**
	 * Show Hits in After WordPress the_content
	 *
	 * @param $content
	 * @return string
	 */
	public function show_hits( $content ) {
		global $WP_Statistics;

		// Get post ID
		$post_id = get_the_ID();

		// Check post ID
		if ( ! $post_id ) {
			return $content;
		}

		// Get post hits
		$hits      = wp_statistics_pages( 'total', "", $post_id );
		$hits_html = '<p>' . sprintf( __( 'Hits: %s', 'wp-statistics' ), $hits ) . '</p>';

		// Check hits position
		if ( $WP_Statistics->option->get( 'display_hits_position' ) == 'before_content' ) {
			return $hits_html . $content;
		} elseif ( $WP_Statistics->option->get( 'display_hits_position' ) == 'after_content' ) {
			return $content . $hits_html;
		} else {
			return $content;
		}
	}
}