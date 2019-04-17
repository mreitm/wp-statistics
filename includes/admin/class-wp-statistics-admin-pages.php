<?php

use WP_STATISTICS\GeoIP;

/**
 * Class WP_Statistics_Admin_Pages
 */
class WP_Statistics_Admin_Pages {

	//Transient For Show Notice Setting
	public static $setting_notice = '_show_notice_wp_statistics';


	public function __construct() {
		add_action( 'admin_init', array( $this, 'save_setting' ) );
	}

	/**
	 * Load Overview Page
	 */
	static function overview() {

		// Right side "wide" widgets
		if ( WP_STATISTICS\Option::get( 'visits' ) ) {
			add_meta_box(
				'wps_hits_postbox',
				__( 'Hit Statistics', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'hits' )
			);
		}

		if ( WP_STATISTICS\Option::get( 'visitors' ) ) {
			add_meta_box(
				'wps_top_visitors_postbox',
				__( 'Top Visitors', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'top.visitors' )
			);
			add_meta_box(
				'wps_search_postbox',
				__( 'Search Engine Referrals', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'search' )
			);
			add_meta_box(
				'wps_words_postbox',
				__( 'Latest Search Words', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'words' )
			);
			add_meta_box(
				'wps_recent_postbox',
				__( 'Recent Visitors', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'recent' )
			);

			if ( WP_STATISTICS\Option::get( 'geoip' ) ) {
				add_meta_box(
					'wps_map_postbox',
					__( 'Today\'s Visitors Map', 'wp-statistics' ),
					'wp_statistics_generate_overview_postbox_contents',
					\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
					'normal',
					null,
					array( 'widget' => 'map' )
				);
			}
		}

		if ( WP_STATISTICS\Option::get( 'pages' ) ) {
			add_meta_box(
				'wps_pages_postbox',
				__( 'Top 10 Pages', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'pages' )
			);
		}

		// Left side "thin" widgets.
		if ( WP_STATISTICS\Option::get( 'visitors' ) ) {
			add_meta_box(
				'wps_summary_postbox',
				__( 'Summary', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'side',
				null,
				array( 'widget' => 'summary' )
			);
			add_meta_box(
				'wps_browsers_postbox',
				__( 'Browsers', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'side',
				null,
				array( 'widget' => 'browsers' )
			);
			add_meta_box(
				'wps_referring_postbox',
				__( 'Top Referring Sites', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
				'side',
				null,
				array( 'widget' => 'referring' )
			);

			if ( WP_STATISTICS\Option::get( 'geoip' ) ) {
				add_meta_box(
					'wps_countries_postbox',
					__( 'Top 10 Countries', 'wp-statistics' ),
					'wp_statistics_generate_overview_postbox_contents',
					\WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ),
					'side',
					null,
					array( 'widget' => 'countries' )
				);
			}
		}

		//Left Show User online table
		if ( WP_STATISTICS\Option::get( 'useronline' ) ) {
			add_meta_box( 'wps_users_online_postbox', __( 'Online Users', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', \WP_STATISTICS\Admin_Menus::get_action_menu_slug( 'overview' ), 'side', null, array( 'widget' => 'users_online' ) );
		}
	}

	/**
	 * Check in admin page
	 *
	 * @param $page | For Get List @see \WP_STATISTICS\WP_Statistics::$page
	 * @return bool
	 */
	public static function in_page( $page ) {
		global $pagenow;

		//Check is custom page
		if ( $pagenow == "admin.php" and isset( $_REQUEST['page'] ) and $_REQUEST['page'] == \WP_STATISTICS\Admin_Menus::get_page_slug( $page ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Show Page title
	 * @param string $title
	 */
	public static function show_page_title( $title = '' ) {

		//Check if $title not Set
		if ( empty( $title ) and function_exists( 'get_admin_page_title' ) ) {
			$title = get_admin_page_title();
		}

		//show Page title
		echo '<img src="' . plugins_url( 'wp-statistics/assets/images/' ) . '/title-logo.png" class="wps_page_title"><h2 class="wps_title">' . $title . '</h2>';

		//do_action after wp_statistics
		do_action( 'wp_statistics_after_title' );
	}

	/**
	 * Get Admin Url
	 *
	 * @param null $page
	 * @param array $arg
	 * @area is_admin
	 * @return string
	 */
	public static function admin_url( $page = null, $arg = array() ) {

		//Check If Pages is in Wp-statistics
		if ( array_key_exists( $page, \WP_STATISTICS\Admin_Menus::get_admin_page_list() ) ) {
			$page = \WP_STATISTICS\Admin_Menus::get_page_slug( $page );
		}

		return add_query_arg( array_merge( array( 'page' => $page ), $arg ), admin_url( 'admin.php' ) );
	}

	/**
	 * Show MetaBox button Refresh/Direct Button Link in Top of Meta Box
	 *
	 * @param string $export
	 * @return string
	 */
	public static function meta_box_button( $export = 'all' ) {

		//Prepare button
		$refresh = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '<span class="screen-reader-text">' . __( 'Reload', 'wp-statistics' ) . '</span></button>';
		$more    = '<button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . wp_statistics_icons( 'dashicons-external' ) . '<span class="screen-reader-text">' . __( 'More Details', 'wp-statistics' ) . '</span></button>';

		//Export
		if ( $export == 'all' ) {
			return $refresh . $more;
		} else {
			return $$export;
		}
	}

	/**
	 * Show Loading Meta Box
	 */
	public static function loading_meta_box() {
		$loading = '<div class="wps_loading_box"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'loading.svg" alt="' . __( 'Reloading...', 'wp-statistics' ) . '"></div>';
		return $loading;
	}

	/**
	 * Plugins
	 */
	static function plugins() {
		// Activate or deactivate the selected plugin
		if ( isset( $_GET['action'] ) ) {
			if ( $_GET['action'] == 'activate' ) {
				$result = activate_plugin( $_GET['plugin'] . '/' . $_GET['plugin'] . '.php' );
				if ( is_wp_error( $result ) ) {
					wp_statistics_admin_notice_result( 'error', $result->get_error_message() );
				} else {
					wp_statistics_admin_notice_result( 'success', __( 'Add-On activated.', 'wp-statistics' ) );
				}
			}
			if ( $_GET['action'] == 'deactivate' ) {
				$result = deactivate_plugins( $_GET['plugin'] . '/' . $_GET['plugin'] . '.php' );
				if ( is_wp_error( $result ) ) {
					wp_statistics_admin_notice_result( 'error', $result->get_error_message() );
				} else {
					wp_statistics_admin_notice_result( 'success', __( 'Add-On deactivated.', 'wp-statistics' ) );
				}
			}
		}

		$response      = wp_remote_get( 'https://wp-statistics.com/wp-json/plugin/addons' );
		$response_code = wp_remote_retrieve_response_code( $response );
		$error         = null;
		$plugins       = array();

		// Check response
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} else {
			if ( $response_code == '200' ) {
				$plugins = json_decode( $response['body'] );
			} else {
				$error = $response['body'];
			}
		}

		include WP_STATISTICS_DIR . 'includes/admin/templates/plugins.php';
	}

	/**
	 * Donate
	 */
	static function donate() {
		echo "<script>window.location.href='http://wp-statistics.com/donate';</script>";
	}

	/**
	 * Loads the optimization page code.
	 */
	static function optimization() {
		global $wpdb, $WP_Statistics;

		// Check the current user has the rights to be here.
		if ( ! current_user_can( wp_statistics_validate_capability( WP_STATISTICS\Option::get( 'manage_capability', 'manage_options' ) ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Get the row count for each of the tables, we'll use this later on in the wps_optimization.php file.
		$list_table = WP_STATISTICS\DB::table( 'all' );
		$result     = array();
		foreach ( $list_table as $tbl_key => $tbl_name ) {
			$result[ $tbl_name ] = $wpdb->get_var( "SELECT COUNT(*) FROM `$tbl_name`" );
		}

		include WP_STATISTICS_DIR . "includes/admin/templates/optimization.php";
	}


	public function save_setting() {
		global $wpdb;

		if ( array_key_exists( 'wp-statistics-nonce', $_POST ) ) {
			if ( wp_verify_nonce( $_POST['wp-statistics-nonce'], 'update-options' ) ) {

				$wp_statistics_options = \WP_STATISTICS\Option::getOptions();

				// General Option
				$selist                       = WP_STATISTICS\SearchEngine::getList( true );
				$permalink                    = get_option( 'permalink_structure' );
				$disable_strip_uri_parameters = false;

				if ( $permalink == '' || strpos( $permalink, '?' ) !== false ) {
					$disable_strip_uri_parameters = true;
				}
				foreach ( $selist as $se ) {
					$se_post = 'wps_disable_se_' . $se['tag'];

					if ( array_key_exists( $se_post, $_POST ) ) {
						$value = $_POST[ $se_post ];
					} else {
						$value = '';
					}
					$new_option                           = str_replace( "wps_", "", $se_post );
					$wp_statistics_options[ $new_option ] = $value;
				}

				$wps_option_list = array(
					'wps_useronline',
					'wps_visits',
					'wps_visitors',
					'wps_visitors_log',
					'wps_pages',
					'wps_track_all_pages',
					'wps_use_cache_plugin',
					'wps_disable_column',
					'wps_hit_post_metabox',
					'wps_show_hits',
					'wps_display_hits_position',
					'wps_check_online',
					'wps_menu_bar',
					'wps_coefficient',
					'wps_chart_totals',
					'wps_hide_notices',
					'wps_all_online',
					'wps_strip_uri_parameters',
					'wps_addsearchwords',
				);

				// We need to check the permalink format for the strip_uri_parameters option, if the permalink is the default or contains uri parameters, we can't strip them.
				if ( $disable_strip_uri_parameters ) {
					$_POST['wps_strip_uri_parameters'] = '';
				}

				foreach ( $wps_option_list as $option ) {
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$new_option                           = str_replace( "wps_", "", $option );
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Access Level
				$wps_option_list = array_merge( $wps_option_list, array( 'wps_read_capability', 'wps_manage_capability' ) );
				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );

					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Exclusion List
				foreach ( \WP_STATISTICS\User::get_role_list() as $role ) {
					$role_post = 'wps_exclude_' . str_replace( " ", "_", strtolower( $role ) );

					if ( array_key_exists( $role_post, $_POST ) ) {
						$value = $_POST[ $role_post ];
					} else {
						$value = '';
					}

					$new_option                           = str_replace( "wps_", "", $role_post );
					$wp_statistics_options[ $new_option ] = $value;
				}

				if ( array_key_exists( 'wps_create_honeypot', $_POST ) ) {
					$my_post = array(
						'post_type'    => 'page',
						'post_title'   => __( 'WP Statistics Honey Pot Page', 'wp-statistics' ) .
						                  ' [' .
						                  \WP_STATISTICS\TimeZone::getCurrentDate() .
						                  ']',
						'post_content' => __( 'This is the Honey Pot for WP Statistics to use, do not delete.', 'wp-statistics' ),
						'post_status'  => 'publish',
						'post_author'  => 1,
					);

					$_POST['wps_honeypot_postid'] = wp_insert_post( $my_post );
				}

				$wps_option_list = array_merge(
					$wps_option_list,
					array(
						'wps_record_exclusions',
						'wps_robotlist',
						'wps_exclude_ip',
						'wps_exclude_loginpage',
						'wps_exclude_adminpage',
						'wps_force_robot_update',
						'wps_excluded_countries',
						'wps_included_countries',
						'wps_excluded_hosts',
						'wps_robot_threshold',
						'wps_use_honeypot',
						'wps_honeypot_postid',
						'wps_exclude_feeds',
						'wps_excluded_urls',
						'wps_exclude_404s',
						'wps_corrupt_browser_info',
						'wps_exclude_ajax',
					)
				);

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );

					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare External
				$wps_option_list = array(
					'wps_geoip',
					'wps_update_geoip',
					'wps_schedule_geoip',
					'wps_geoip_city',
					'wps_auto_pop',
					'wps_private_country_code',
					'wps_referrerspam',
					'wps_schedule_referrerspam'
				);

				// For country codes we always use upper case, otherwise default to 000 which is 'unknown'.
				if ( array_key_exists( 'wps_private_country_code', $_POST ) ) {
					$_POST['wps_private_country_code'] = trim( strtoupper( $_POST['wps_private_country_code'] ) );
				} else {
					$_POST['wps_private_country_code'] = \WP_STATISTICS\GeoIP::$private_country;
				}

				if ( $_POST['wps_private_country_code'] == '' ) {
					$_POST['wps_private_country_code'] = \WP_STATISTICS\GeoIP::$private_country;
				}

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Maintenance
				$wps_option_list = array(
					'wps_schedule_dbmaint',
					'wps_schedule_dbmaint_days',
					'wps_schedule_dbmaint_visitor',
					'wps_schedule_dbmaint_visitor_hits',
				);

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Notification

				// We need to handle a change in the report schedule manually, so check to see it has been set.
				if ( array_key_exists( 'wps_time_report', $_POST ) ) {
					// If the report has been changed, we need to update the schedule.
					if ( WP_STATISTICS\Option::get( 'time_report' ) != $_POST['wps_time_report'] ) {
						// Remove the old schedule if it exists.
						if ( wp_next_scheduled( 'report_hook' ) ) {
							wp_unschedule_event( wp_next_scheduled( 'report_hook' ), 'report_hook' );
						}

						// Setup the new schedule, we could just let this fall through and let the code in schedule.php deal with it
						// but that would require an extra page load to start the schedule so do it here instead.
						wp_schedule_event( time(), $_POST['wps_time_report'], 'report_hook' );
					}
				}

				$wps_option_list = array(
					"wps_stats_report",
					"wps_time_report",
					"wps_send_report",
					"wps_content_report",
					"wps_email_list",
					"wps_geoip_report",
					"wps_prune_report",
					"wps_upgrade_report",
					"wps_admin_notices",
				);

				foreach ( $wps_option_list as $option ) {
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}

					// WordPress escapes form data no matter what the setting of magic quotes is in PHP (http://www.theblog.ca/wordpress-addslashes-magic-quotes).
					$value = stripslashes( $value );

					$new_option                           = str_replace( "wps_", "", $option );
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare OverView Display
				$wps_option_list = array( 'wps_disable_map', 'wps_disable_dashboard', 'wps_disable_editor' );

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( 'wps_', '', $option );

					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}

				// Prepare Privacy
				$wps_option_list = array(
					'wps_anonymize_ips',
					'wps_hash_ips',
					'wps_store_ua',
					'wps_all_online',
				);

				// If the IP hash's are enabled, disable storing the complete user agent.
				if ( array_key_exists( 'wps_hash_ips', $_POST ) ) {
					$_POST['wps_store_ua'] = '';
				}

				foreach ( $wps_option_list as $option ) {
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$new_option                           = str_replace( "wps_", "", $option );
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Removal
				if ( array_key_exists( 'wps_remove_plugin', $_POST ) ) {
					if ( is_super_admin() ) {
						update_option( 'wp_statistics_removal', 'true' );

						// We need to reload the page after we reset the options but it's too late to do it through a HTTP redirect so do a
						// JavaScript redirect instead.
						echo '<script type="text/javascript">window.location.href="' . admin_url() . 'plugins.php";</script>';
					}
				}

				if ( array_key_exists( 'wps_reset_plugin', $_POST ) ) {

					$default_options   = \WP_STATISTICS\Option::defaultOption();
					$excluded_defaults = array( 'force_robot_update', 'robot_list' );
					$again_options     = array();

					// Handle multi site implementations
					if ( is_multisite() ) {

						// Loop through each of the sites.
						$sites = WP_STATISTICS\Helper::get_wp_sites_list();
						foreach ( $sites as $blog_id ) {

							switch_to_blog( $blog_id );

							// Delete the wp_statistics option.
							update_option( \WP_STATISTICS\Option::$opt_name, array() );

							// Delete the user options.
							$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

							// Set some intelligent defaults.
							foreach ( $default_options as $key => $value ) {
								if ( ! in_array( $key, $excluded_defaults ) ) {
									$again_options[ $key ] = $value;
								}
							}

							// Disable Show Welcome Page Again
							$again_options['first_show_welcome_page'] = true;
							$again_options['show_welcome_page']       = false;

							update_option( \WP_STATISTICS\Option::$opt_name, $again_options );
						}

						restore_current_blog();
					} else {

						// Delete the wp_statistics option.
						update_option( \WP_STATISTICS\Option::$opt_name, array() );

						// Delete the user options.
						$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

						// Set some intelligent defaults.
						foreach ( $default_options as $key => $value ) {
							if ( ! in_array( $key, $excluded_defaults ) ) {
								$again_options[ $key ] = $value;
							}
						}

						// Disable Show Welcome Page Again
						$again_options['first_show_welcome_page'] = true;
						$again_options['show_welcome_page']       = false;

						update_option( \WP_STATISTICS\Option::$opt_name, $again_options );
					}

					// We need to reload the page after we reset the options but it's too late to do it through a HTTP redirect so do a
					// JavaScript redirect instead.
					wp_redirect( WP_Statistics_Admin_Pages::admin_url( 'settings' ) );
					exit;
				}

				\WP_STATISTICS\Option::save_options( $wp_statistics_options );
				wp_redirect( WP_Statistics_Admin_Pages::admin_url( 'settings' ) );
				exit;

			}
		}

	}

	/**
	 * This function displays the HTML for the settings page.
	 */
	static function settings() {

		// Check the current user has the rights to be here.
		if ( ! current_user_can( wp_statistics_validate_capability( WP_STATISTICS\Option::get( 'read_capability', 'manage_options' ) ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Check admin notices.
		if ( WP_STATISTICS\Option::get( 'admin_notices' ) == true ) {
			WP_STATISTICS\Option::update( 'disable_donation_nag', false );
			WP_STATISTICS\Option::update( 'disable_suggestion_nag', false );
		}

		// Check User Access To Save Setting
		$wps_admin = false;
		if ( current_user_can( wp_statistics_validate_capability( WP_STATISTICS\Option::get( 'manage_capability', 'manage_options' ) ) ) ) {
			$wps_admin = true;
		}
		if ( $wps_admin === false ) {
			$wps_admin = 0;
		}
		$selist                       = WP_STATISTICS\SearchEngine::getList( true );
		$permalink                    = get_option( 'permalink_structure' );
		$disable_strip_uri_parameters = false;
		if ( $permalink == '' || strpos( $permalink, '?' ) !== false ) {
			$disable_strip_uri_parameters = true;
		}
		$wp_statistics_options = \WP_STATISTICS\Option::getOptions();

		include WP_STATISTICS_DIR . "includes/admin/templates/settings.php";


		//TODO Push To Setting Page Admin_init Hook
		if ( WP_STATISTICS\Option::get( 'geoip' ) and isset( $_POST['update_geoip'] ) and isset( $_POST['geoip_name'] ) ) {

			//Check Geo ip Exist in Database
			if ( isset( GeoIP::$library[ $_POST['geoip_name'] ] ) ) {
				$result = WP_Statistics_Updates::download_geoip( $_POST['geoip_name'], "update" );

				if ( isset( $result['status'] ) and $result['status'] === false ) {
					add_filter( "wp_statistics_redirect_setting", function ( $redirect ) {
						$redirect = true;
						return $redirect;
					} );
				} else {
					echo $result['notice'];
				}
			}

		}

		//Enabled Geo ip Country Or City And download
		foreach ( array( "geoip" => "country", "geoip_city" => "city" ) as $geo_opt => $geo_name ) {
			if ( ! isset( $_POST['update_geoip'] ) and isset( $_POST[ 'wps_' . $geo_opt ] ) ) {

				//Check File Not Exist
				$upload_dir = wp_upload_dir();
				$file       = $upload_dir['basedir'] . '/wp-statistics/' . GeoIP::$library[ $geo_name ]['file'] . '.mmdb';
				if ( ! file_exists( $file ) ) {
					$result = WP_Statistics_Updates::download_geoip( $geo_name );
					if ( isset( $result['status'] ) and $result['status'] === false ) {
						add_filter( "wp_statistics_redirect_setting", function ( $redirect ) {
							$redirect = true;
							return $redirect;
						} );
					} else {
						echo $result['notice'];
					}
				}
			}
		}

		//Redirect Set Setting
		self::wp_statistics_redirect_setting();
	}

	/**
	 * Set Transient Notice
	 *
	 * @param $text
	 * @param string $type
	 */
	public static function set_admin_notice( $text, $type = 'error' ) {
		$get = get_transient( WP_Statistics_Admin_Pages::$setting_notice );
		if ( $get != false ) {
			$results = $get;
		}
		delete_transient( WP_Statistics_Admin_Pages::$setting_notice );
		$results[] = array( "text" => $text, "type" => $type );
		set_transient( WP_Statistics_Admin_Pages::$setting_notice, $results, 1 * HOUR_IN_SECONDS );
	}

	/**
	 * Notification Setting
	 */
	public static function wp_statistics_notice_setting() {
		global $pagenow, $WP_Statistics;

		//Show Notice By Plugin
		$get = get_transient( WP_Statistics_Admin_Pages::$setting_notice );
		if ( $get != false ) {
			foreach ( $get as $item ) {
				wp_statistics_admin_notice_result( $item['type'], $item['text'] );
			}
			delete_transient( WP_Statistics_Admin_Pages::$setting_notice );
		}

		//Check referring Spam Update
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == \WP_STATISTICS\Admin_Menus::get_page_slug( 'settings' ) and isset( $_GET['update-referrerspam'] ) ) {

			// Update referrer spam
			$update_spam = WP_Statistics_Updates::download_referrerspam();
			if ( $update_spam === true ) {
				wp_statistics_admin_notice_result( 'success', __( 'Updated Matomo Referrer Spam.', 'wp-statistics' ) );
			} else {
				wp_statistics_admin_notice_result( 'error', __( 'error in get referrer spam list. please try again.', 'wp-statistics' ) );
			}
		}
	}

	/**
	 * Redirect Jquery
	 * @param bool $redirect
	 */
	public static function wp_statistics_redirect_setting( $redirect = false ) {
		$redirect = apply_filters( 'wp_statistics_redirect_setting', $redirect );
		if ( $redirect === true ) {
			echo '<script>window.location.replace("' . ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . '");</script>';
		}
	}

	/**
	 * @param string $log_type Log Type
	 */
	static function log( $log_type = "" ) {
		global $wpdb, $WP_Statistics, $plugin_page;

		switch ( $plugin_page ) {
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'browser' ):
				$log_type = 'all-browsers';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'countries' ):
				$log_type = 'top-countries';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'exclusions' ):
				$log_type = 'exclusions';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'hits' ):
				$log_type = 'hit-statistics';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'online' ):
				$log_type = 'online';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'pages' ):
				$log_type = 'top-pages';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'categories' ):
				$log_type = 'categories';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'tags' ):
				$log_type = 'tags';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'authors' ):
				$log_type = 'authors';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'referrers' ):
				$log_type = 'top-referring-site';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'searches' ):
				$log_type = 'search-statistics';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'words' ):
				$log_type = 'last-all-search';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'top-visitors' ):
				$log_type = 'top-visitors';

				break;
			case \WP_STATISTICS\Admin_Menus::get_page_slug( 'visitors' ):
				$log_type = 'last-all-visitor';

				break;
			default:
				$log_type = "";
		}

		// We allow for a get style variable to be passed to define which function to use.
		if ( $log_type == "" && array_key_exists( 'type', $_GET ) ) {
			$log_type = $_GET['type'];
		}

		// Verify the user has the rights to see the statistics.
		if ( ! current_user_can(
			wp_statistics_validate_capability(
				WP_STATISTICS\Option::get(
					'read_capability',
					'manage_option'
				)
			)
		)
		) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// We want to make sure the tables actually exist before we blindly start access them.
		$result = $wpdb->query(
			"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visit' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_exclusions' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_pages' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_useronline' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_search'"
		);

		if ( $result != 7 ) {

			$get_bloginfo_url = WP_Statistics_Admin_Pages::admin_url( 'optimization', array( 'tab' => 'database' ) );
			$missing_tables   = array();

			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_visitor';
			}
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visit'" );
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_visit';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_exclusions'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_exclusions';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_historical';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_useronline'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_useronline';
			}
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_pages'" );
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_pages';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_search'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_search';
			}

			wp_die(
				'<div class="error"><p>' . sprintf(
					__(
						'The following plugin table(s) do not exist in the database, please re-run the %s install routine %s:',
						'wp-statistics'
					),
					'<a href="' . $get_bloginfo_url . '">',
					'</a>'
				) . implode( ', ', $missing_tables ) . '</p></div>'
			);
		}

		// Load the postbox script that provides the widget style boxes.
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );

		// Load the css we use for the statistics pages.
		wp_enqueue_style( 'wpstatistics-log-css', WP_STATISTICS_URL . 'assets/css/log.css', true, WP_STATISTICS_VERSION );
		wp_enqueue_style( 'wpstatistics-pagination-css', WP_STATISTICS_URL . 'assets/css/pagination.css', true, WP_STATISTICS_VERSION );

		// The different pages have different files to load.
		switch ( $log_type ) {
			case 'all-browsers':
			case 'top-countries':
			case 'hit-statistics':
			case 'search-statistics':
			case 'exclusions':
			case 'online':
			case 'top-visitors':
			case 'categories':
			case 'tags':
			case 'authors':
				include WP_STATISTICS_DIR . 'includes/log/' . $log_type . '.php';
				break;
			case 'last-all-search':
				include WP_STATISTICS_DIR . 'includes/log/last-search.php';

				break;
			case 'last-all-visitor':
				include WP_STATISTICS_DIR . 'includes/log/last-visitor.php';

				break;
			case 'top-referring-site':
				include WP_STATISTICS_DIR . 'includes/log/top-referring.php';

				break;
			case 'top-pages':
				// If we've been given a page id or uri to get statistics for, load the page stats, otherwise load the page stats overview page.
				if ( array_key_exists( 'page-id', $_GET ) || array_key_exists( 'page-uri', $_GET ) || array_key_exists( 'prepage', $_GET ) ) {
					include WP_STATISTICS_DIR . 'includes/log/page-statistics.php';
				} else {
					include WP_STATISTICS_DIR . 'includes/log/top-pages.php';
				}

				break;
			default:
				if ( get_current_screen()->parent_base == \WP_STATISTICS\Admin_Menus::get_page_slug( 'overview' ) ) {

					wp_enqueue_style( 'wpstatistics-jqvmap-css', WP_STATISTICS_URL . 'assets/jqvmap/jqvmap.css', true, '1.5.1' );
					wp_enqueue_script( 'wpstatistics-jquery-vmap', WP_STATISTICS_URL . 'assets/jqvmap/jquery.vmap.js', true, '1.5.1' );
					wp_enqueue_script( 'wpstatistics-jquery-vmap-world', WP_STATISTICS_URL . 'assets/jqvmap/maps/jquery.vmap.world.js', true, '1.5.1' );

					// Load our custom widgets handling javascript.
					wp_enqueue_script( 'wp_statistics_log', WP_STATISTICS_URL . 'assets/js/log.js' );

					include WP_STATISTICS_DIR . 'includes/log/log.php';
				}

				break;
		}
	}

}