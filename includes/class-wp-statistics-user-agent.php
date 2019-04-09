<?php

namespace WP_STATISTICS;

class UserAgent {
	/**
	 * Get User Agent
	 *
	 * @return mixed
	 */
	public static function getHttpUserAgent() {
		if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			$UserAgent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$UserAgent = false;
		}

		return apply_filters( 'wp_statistics_user_http_agent', $UserAgent );
	}

	/**
	 * Calls the user agent parsing code.
	 *
	 * @return array|\string[]
	 */
	public static function getUserAgent() {

		//Check If Rest Request
		//TODO Remove At Last
		if ( \WP_Statistics_Rest::is_rest() ) {
			return array(
				'browser'  => \WP_Statistics_Rest::params( 'browser' ),
				'platform' => \WP_Statistics_Rest::params( 'platform' ),
				'version'  => \WP_Statistics_Rest::params( 'version' )
			);
		}

		// Get Http User Agent
		$user_agent = ( self::getHttpUserAgent() === false ? '' : self::getHttpUserAgent() );

		// Get WhichBrowser Browser
		$result = new \WhichBrowser\Parser( $user_agent );
		$agent  = array(
			'browser'  => ( isset( $result->browser->name ) ) ? $result->browser->name : _x( 'Unknown', 'Browser', 'wp-statistics' ),
			'platform' => ( isset( $result->os->name ) ) ? $result->os->name : _x( 'Unknown', 'Platform', 'wp-statistics' ),
			'version'  => ( isset( $result->os->version->value ) ) ? $result->os->version->value : _x( 'Unknown', 'Version', 'wp-statistics' ),
		);

		return apply_filters( 'wp_statistics_user_agent', $agent );
	}


}