<?php

namespace WP_STATISTICS;

class UserAgent {
	/**
	 * Get User Agent
	 *
	 * @return mixed
	 */
	public static function getHttpUserAgent() {
		return apply_filters( 'wp_statistics_user_http_agent', ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' ) );
	}

	/**
	 * Calls the user agent parsing code.
	 *
	 * @return array|\string[]
	 */
	public static function getUserAgent() {

		// Get Http User Agent
		$user_agent = self::getHttpUserAgent();

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