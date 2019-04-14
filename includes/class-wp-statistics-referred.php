<?php

namespace WP_STATISTICS;

use WP_Statistics_Rest;

class Referred {
	/**
	 * Get referer URL
	 *
	 * @return string
	 */
	public static function getRefererURL() {
		return ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	}

	/**
	 * Return the referrer link for the current user.
	 *
	 * @return array|bool|string
	 */
	public static function get() {
		global $WP_Statistics;

		//Check If Rest Api Request //TODO Remove At Last
		if ( WP_Statistics_Rest::is_rest() ) {
			return WP_Statistics_Rest::params( 'referred' );
		}

		// Get Default
		$referred = self::getRefererURL();

		// Sanitize Referer Url
		$referred = esc_sql( strip_tags( $referred ) );

		// If Referer is Empty then use same WebSite Url
		if ( empty( $referred ) ) {
			$referred = get_bloginfo( 'url' );
		}

		// Check Search Engine
		if ( $WP_Statistics->option->get( 'addsearchwords', false ) ) {

			// Check to see if this is a search engine referrer
			$SEInfo = SearchEngine::getByUrl( $referred );
			if ( is_array( $SEInfo ) ) {

				// If we're a known SE, check the query string
				if ( $SEInfo['tag'] != '' ) {
					$result = SearchEngine::getByQueryString( $referred );

					// If there were no search words, let's add the page title
					if ( $result == '' || $result == 'No search query found!' ) {
						$result = wp_title( '', false );
						if ( $result != '' ) {
							$referred = esc_url( add_query_arg( $SEInfo['querykey'], urlencode( '~"' . $result . '"' ), $referred ) );
						}
					}
				}
			}
		}

		return apply_filters( 'wp_statistics_user_referer', $referred );
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

		// Sanitize Link
		$html_referrer = self::html_sanitize_referrer( $referrer );

		// Check Url Protocol
		if ( ! Helper::check_url_scheme( $html_referrer ) ) {
			$html_referrer = '//' . $html_referrer;
		}

		// Parse Url
		$base_url = @parse_url( $html_referrer );

		// Get Page title
		$title = ( trim( $title ) == "" ? $html_referrer : $title );

		// Get Html Link
		return "<a href='{$html_referrer}' title='{$title}'" . ( $is_blank === true ? ' target="_blank"' : '' ) . ">{$base_url['host']}</a>";
	}

	/**
	 * Sanitizes the referrer
	 *
	 * @param     $referrer
	 * @param int $length
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
}