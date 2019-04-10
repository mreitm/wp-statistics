<?php

/**
 * Class WP_Statistics_Network_Admin
 */
class WP_Statistics_Network_Admin {

	/**
	 * This function adds the primary menu to WordPress network.
	 */
	static function menu() {
		global $WP_Statistics;

		// Get the read/write capabilities required to view/manage the plugin as set by the user.
		$read_cap   = wp_statistics_validate_capability( $WP_Statistics->option->get( 'read_capability', 'manage_options' ) );
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->option->get( 'manage_capability', 'manage_options' ) );

		// Add the top level menu.
		add_menu_page( __( 'Statistics', 'wp-statistics' ), __( 'Statistics', 'wp-statistics' ), $read_cap, WP_STATISTICS_MAIN_FILE, 'WP_Statistics_Network_Admin::overview', 'dashicons-chart-pie' );

		// Add the sub items.
		add_submenu_page( WP_STATISTICS_MAIN_FILE, __( 'Overview', 'wp-statistics' ), __( 'Overview', 'wp-statistics' ), $read_cap, WP_STATISTICS_MAIN_FILE, 'WP_Statistics_Network_Admin::overview' );

		$count = 0;
		$sites = WP_STATISTICS\Helper::get_wp_sites_list();

		foreach ( $sites as $blog_id ) {
			$details = get_blog_details( $blog_id );
			add_submenu_page( WP_STATISTICS_MAIN_FILE, $details->blogname, $details->blogname, $manage_cap, 'wp_statistics_blogid_' . $blog_id, 'WP_Statistics_Network_Admin::goto_blog' );

			$count ++;
			if ( $count > 15 ) {
				break;
			}
		}
	}

	/**
	 * Network Overview
	 */
	static function overview() {
		global $WP_Statistics;
		?>
        <div id="wrap wps-wrap">
            <br/>
            <table class="widefat wp-list-table" style="width: auto;">
                <thead>
                <tr>
                    <th style='text-align: left'><?php _e( 'Site', 'wp-statistics' ); ?></th>
                    <th style='text-align: left'><?php _e( 'Options', 'wp-statistics' ); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				$i = 0;

				$options = array(
					__( 'Overview', 'wp-statistics' )           => \WP_STATISTICS\Menu::get_page_slug('overview'),
					__( 'Hits', 'wp-statistics' )               => \WP_STATISTICS\Menu::get_page_slug('hits'),
					__( 'Online', 'wp-statistics' )             => \WP_STATISTICS\Menu::get_page_slug('online'),
					__( 'Referrers', 'wp-statistics' )          => \WP_STATISTICS\Menu::get_page_slug('referrers'),
					__( 'Search Words', 'wp-statistics' )       => \WP_STATISTICS\Menu::get_page_slug('words'),
					__( 'Searches', 'wp-statistics' )           => \WP_STATISTICS\Menu::get_page_slug('searches'),
					__( 'Pages', 'wp-statistics' )              => \WP_STATISTICS\Menu::get_page_slug('pages'),
					__( 'Visitors', 'wp-statistics' )           => \WP_STATISTICS\Menu::get_page_slug('visitors'),
					__( 'Countries', 'wp-statistics' )          => \WP_STATISTICS\Menu::get_page_slug('countries'),
					__( 'Browsers', 'wp-statistics' )           => \WP_STATISTICS\Menu::get_page_slug('browser'),
					__( 'Top Visitors Today', 'wp-statistics' ) => \WP_STATISTICS\Menu::get_page_slug('top-visitors'),
					__( 'Exclusions', 'wp-statistics' )         => \WP_STATISTICS\Menu::get_page_slug('exclusions'),
					__( 'Optimization', 'wp-statistics' )       => \WP_STATISTICS\Menu::get_page_slug('optimization'),
					__( 'Settings', 'wp-statistics' )           => \WP_STATISTICS\Menu::get_page_slug('settings'),
				);

				$sites = WP_STATISTICS\Helper::get_wp_sites_list();

				foreach ( $sites as $blog_id ) {
					$details   = get_blog_details( $blog_id );
					$url       = get_admin_url( $blog_id, '/' ) . 'admin.php?page=';
					$alternate = '';

					if ( $i % 2 == 0 ) {
						$alternate = ' class="alternate"';
					}
					?>

                    <tr<?php echo $alternate; ?>>
                        <td style='text-align: left'>
							<?php echo $details->blogname; ?>
                        </td>
                        <td style='text-align: left'>
							<?php
							$options_len = count( $options );
							$j           = 0;

							foreach ( $options as $key => $value ) {
								echo '<a href="' . $url . $value . '">' . $key . '</a>';
								$j ++;
								if ( $j < $options_len ) {
									echo ' - ';
								}
							}
							?>
                        </td>
                    </tr>
					<?php
					$i ++;
				}
				?>
                </tbody>
            </table>
        </div>
		<?php
	}

	/**
	 * Goto Network Blog
	 */
	static function goto_blog() {
		global $plugin_page;

		$blog_id = str_replace( 'wp_statistics_blogid_', '', $plugin_page );
		// Get the admin url for the current site.
		$url = get_admin_url( $blog_id ) . '/admin.php?page=' . \WP_STATISTICS\Menu::get_page_slug('overview');
		echo "<script>window.location.href = '$url';</script>";
	}

}