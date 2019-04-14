<?php

use WP_STATISTICS\Helper;

/**
 * Class WP_Statistics_Frontend
 */
class WP_Statistics_Frontend {

	public function __construct() {
		global $WP_Statistics;

		//Enable Shortcode in Widget
		add_filter( 'widget_text', 'do_shortcode' );

		// Add the honey trap code in the footer.
		add_action( 'wp_footer', array( $this, 'add_honeypot' ) );

		// Enqueue scripts & styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		//Get Visitor information and Save To Database
		add_action( 'wp', array( $this, 'init' ) );

		//Add inline Rest Request
		add_action( 'wp_head', array( $this, 'add_inline_rest_js' ) );

		//Add Html Comment in head
		if ( ! $WP_Statistics->option->get( 'use_cache_plugin' ) ) {
			add_action( 'wp_head', array( $this, 'html_comment' ) );
		}

		// Check to show hits in posts/pages
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
			echo '<script>var WP_Statistics_http = new XMLHttpRequest();WP_Statistics_http.open(\'POST\', \'' . add_query_arg( array( '_' => time() ), path_join( get_rest_url(), WP_Statistics_Rest::route . '/' . WP_Statistics_Rest::func ) ) . '\', true);WP_Statistics_http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");WP_Statistics_http.send("' . WP_Statistics_Rest::_POST . '=" + JSON.stringify(' . self::set_default_params() . '));</script>' . "\n";
		}
	}

	/*
	 * Set Default Params Rest Api
	 */
	static public function set_default_params() {
		global $WP_Statistics;

		/*
		 * Load Rest Api JavaScript
		 */
		$params = array();

		//Set Url WP-Rest API
		$params['base'] = rtrim( get_rest_url(), "/" );
		$params['api']  = rtrim( rest_get_url_prefix(), "/" );

		//Set UserAgent [browser|platform|version]
		$params    = wp_parse_args( $params, \WP_STATISTICS\UserAgent::getUserAgent() );

		//Set Referred
		$params['referred'] = \WP_STATISTICS\Referred::get();

		//Set IP
		$params['ip'] = \WP_STATISTICS\IP::getIP();

		//Set Hash Ip
		$params['hash_ip'] = \WP_STATISTICS\IP::getHashIP();

		//exclude
		$check_exclude            = new WP_Statistics_Hits();
		$params['exclude']        = $check_exclude->exclusion_match;
		$params['exclude_reason'] = $check_exclude->exclusion_reason;

		//User Agent String
		$params['ua'] = \WP_STATISTICS\UserAgent::getHttpUserAgent();

		//track all page
		$params['track_all'] = ( Helper::is_track_all_page() === true ? 1 : 0 );

		//timestamp
		$params['timestamp'] = \WP_STATISTICS\Timezone::getCurrentTimestamp();

		//Set Page Type
		$get_page_type               = Helper::get_page_type();
		$params['current_page_type'] = $get_page_type['type'];
		$params['current_page_id']   = $get_page_type['id'];
		$params['search_query']      = ( isset( $get_page_type['search_query'] ) ? $get_page_type['search_query'] : '' );

		//page url
		$params['page_uri'] = wp_statistics_get_uri();

		//Get User id
		$params['user_id'] = $WP_Statistics->user->ID;

		//Fixed entity decode Html
		foreach ( (array) $params as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}
			$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		return json_encode( $params, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Shutdown Action
	 */
	public function init() {
		global $WP_Statistics;

		// If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.
		// This seems to happen sometimes with WP Cron calls.
		if ( ! is_object( $WP_Statistics ) ) {
			return;
		}

		//Disable if User Active cache Plugin
		if ( ! $WP_Statistics->option->get( 'use_cache_plugin' ) ) {

			$h = new WP_Statistics_GEO_IP_Hits;

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

	/**
	 * @param $content
	 *
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
