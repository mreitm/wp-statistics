<?php

namespace WP_STATISTICS;

class UserOnline {
	/**
	 * Check Users Online Option name
	 *
	 * @var string
	 */
	public static $check_user_online_opt = 'wp_statistics_check_user_online';

	/**
	 * Default User Reset Time User Online
	 *
	 * @var int
	 */
	public static $reset_user_time = 120; # Second

	/**
	 * UserOnline constructor.
	 */
	public function __construct() {

		# Reset User Online Count
		add_action( 'wp_loaded', array( $this, 'reset_user_online' ) );
	}

	/**
	 * Check Active User Online System
	 *
	 * @return mixed
	 */
	public static function active_user_online() {
		/**
		 * Disable/Enable User Online for Custom request
		 *
		 * @example add_filter('wp_statistics_active_user_online', function(){ if( is_page() ) { return false; } });
		 */
		return ( has_filter( 'wp_statistics_active_user_online' ) ) ? apply_filters( 'wp_statistics_active_user_online', true ) : $GLOBALS['WP_Statistics']->option->get( 'useronline' );
	}

	/**
	 * Reset Online User Process By Option time
	 *
	 * @return string
	 */
	public function reset_user_online() {
		global $WP_Statistics, $wpdb;

		//Check User Online is Active in this Wordpress
		if ( self::active_user_online() ) {

			//Get Not timestamp
			$now = TimeZone::getCurrentDate( 'U' );

			// Set the default seconds a user needs to visit the site before they are considered offline.
			$reset_time = self::$reset_user_time;

			// Get the user set value for seconds to check for users online.
			if ( $WP_Statistics->option->get( 'check_online' ) ) {
				$reset_time = $WP_Statistics->option->get( 'check_online' );
			}

			// We want to delete users that are over the number of seconds set by the admin.
			$time_diff = $now - $reset_time;

			//Last check Time
			$wps_run = get_option( self::$check_user_online_opt );
			if ( isset( $wps_run ) and is_numeric( $wps_run ) ) {
				if ( ( $wps_run + $reset_time ) > $now ) {
					return;
				}
			}

			// Call the deletion query.
			$wpdb->query( "DELETE FROM `" . DB::table( 'useronline' ) . "` WHERE timestamp < {$time_diff}" );

			//Update Last run this Action
			update_option( self::$check_user_online_opt, $now );
		}
	}


}