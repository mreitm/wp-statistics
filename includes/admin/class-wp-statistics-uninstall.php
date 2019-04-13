<?php

/**
 * Class WP_Statistics_Uninstall
 */
class WP_Statistics_Uninstall {

	/**
	 * WP_Statistics_Uninstall constructor.
	 */
	function __construct() {
		if ( is_admin() ) {

			// Handle multi site implementations
			if ( is_multisite() ) {

				// Loop through each of the sites.
				$sites = WP_STATISTICS\Helper::get_wp_sites_list();
				foreach ( $sites as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->wp_statistics_site_removal();
				}
				restore_current_blog();
			} else {
				$this->wp_statistics_site_removal();

			}
			// Make sure we don't try and remove the data more than once.
			update_option( 'wp_statistics_removal', 'done' );
		}

	}

	/**
	 * Removes database options, user meta keys & tables
	 */
	public function wp_statistics_site_removal() {
		global $wpdb;

		// Delete the options from the WordPress options table.
		delete_option( 'wp_statistics' );
		delete_option( 'wp_statistics_db_version' );
		delete_option( 'wp_statistics_plugin_version' );
		delete_option( 'wp_statistics_referrals_detail' );

		// Delete the transients.
		delete_transient( 'wps_top_referring' );
		delete_transient( 'wps_excluded_hostname_to_ip_cache' );

		// Delete the user options.
		$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

		// Drop the tables
		foreach ( \WP_STATISTICS\DB::table() as $tbl ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$tbl}" );
		}
	}
}
