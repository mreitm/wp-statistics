<?php

namespace WP_STATISTICS;

class Helper {
	/**
	 * Returns an array of site id's
	 *
	 * @return array
	 */
	public static function get_wp_sites_list() {
		$site_list = array();
		$sites     = get_sites();
		foreach ( $sites as $site ) {
			$site_list[] = $site->blog_id;
		}
		return $site_list;
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public static function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! self::is_rest_request();
		}
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * @return bool
	 */
	public static function is_rest_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		return ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );
	}

	/**
	 * Sanitizes the referrer
	 *
	 * @param     $referrer
	 * @param int $length
	 *
	 * @return string
	 */
	public static function html_sanitize_referrer( $referrer, $length = - 1 ) {
		$referrer = trim( $referrer );

		if ( 'data:' == strtolower( substr( $referrer, 0, 5 ) ) ) {
			$referrer = 'http://127.0.0.1';
		}

		if ( 'javascript:' == strtolower( substr( $referrer, 0, 11 ) ) ) {
			$referrer = 'http://127.0.0.1';
		}

		if ( $length > 0 ) {
			$referrer = substr( $referrer, 0, $length );
		}

		return htmlentities( $referrer, ENT_QUOTES );
	}

	/**
	 * Get referrer link
	 *
	 * @param  string $referrer
	 * @param string $title
	 * @param bool $is_blank
	 * @return string
	 */
	public static function get_referrer_link( $referrer, $title = '', $is_blank = false ) {
		$html_referrer = self::html_sanitize_referrer( $referrer );

		if ( substr( $html_referrer, 0, 7 ) !== 'http://' and substr( $html_referrer, 0, 8 ) !== 'https://' ) {
			// relative address, use '//' to adapt both http and https
			$html_nr_referrer = '//' . $html_referrer;
		} else {
			$html_nr_referrer = $html_referrer;
		}

		$base_url = parse_url( $html_nr_referrer );
		$title    = ( trim( $title ) == "" ? $html_nr_referrer : $title );
		return "<a href='{$html_nr_referrer}' title='{$title}'" . ( $is_blank === true ? ' target="_blank"' : '' ) . ">{$base_url['host']}</a>";
	}
}