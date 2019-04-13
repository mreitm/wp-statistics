<?php
/**
 * WP-Statistics Setup
 *
 * @package WP-Statistics
 * @since   13.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main bootstrap class for WP Statistics
 *
 * @package WP Statistics
 */
final class WP_Statistics {
	/**
	 * Referrer
	 *
	 * @var bool
	 */
	private $referrer = false;

	/**
	 * Rest Api init
	 *
	 * @var array
	 */
	public $restapi;

	/**
	 * WP_Statistics constructor.
	 */
	public function __construct() {
		/**
		 * Check PHP Support
		 */
		if ( ! $this->require_php_version() ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return;
		}
		/**
		 * Plugin Loaded Action
		 */
		add_action( 'plugins_loaded', array( $this, 'plugin_setup' ) );
		/**
		 * Install And Upgrade plugin
		 */
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
		/**
		 * wp-statistics loaded
		 */
		do_action( 'wp_statistics_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 13.0
	 */
	public function __clone() {
		\WP_STATISTICS\Helper::doing_it_wrong( __CLASS__, esc_html__( 'Cloning is forbidden.', 'wp-statisitcs' ), '13.0' );
	}

	/**
	 * Constructors plugin Setup
	 */
	public function plugin_setup() {
		/**
		 * Load Text Domain
		 */
		add_action( 'init', array( $this, 'load_textdomain' ) );
		/**
		 * instantiate Plugin
		 */
		//TODO PUSH TO INCLUDE METHOD
		// third-party Libraries
		require_once WP_STATISTICS_DIR . 'includes/vendor/autoload.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-rest.php';
		/**
		 * Include Require File
		 */
		$this->includes();
		$this->instantiate();
		/*
		 * Load action
		 */
		//TODO:  ADDED NEW IN SAME FILE CLASS
		new WP_Statistics_Schedule;
		if ( is_admin() ) {
			new WP_Statistics_Admin;
		} else {
			new WP_Statistics_Frontend;
		}
		new WP_Statistics_Shortcode();
	}

	/**
	 * Includes plugin files
	 */
	public function includes() {

		// Utility classes.
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-agent.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geoip.php';

		// Hits Class
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-historical.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visit.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-referred.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-search-engine.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';


		//todo rest api
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-hits.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geo-ip-hits.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-frontend.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-schedule.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';


		if ( is_admin() ) {

			// Admin classes.
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-pages.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-ajax.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-dashboard.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-editor.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-export.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-uninstall.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-updates.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-welcome.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-purge.php';

			//Admin Menu
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-menus.php';

			//Admin Asset
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';

			//Admin Notice
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-notices.php';

			//TinyMCE Editor
			require_once WP_STATISTICS_DIR . 'includes/admin/TinyMCE/class-wp-statistics-tinymce.php';

			//Admin Bar
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-bar.php';
		}

		// Front Class.
		if ( ! is_admin() ) {
		}

		// WP-Cli
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-cli.php';
		}

		// Template functions.
		include WP_STATISTICS_DIR . 'includes/template-functions.php';
	}

	/**
	 * Loads the load plugin text domain code.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-statistics', false, WP_STATISTICS_DIR . 'languages' );
	}

	/**
	 * Check PHP Version
	 */
	public function require_php_version() {
		if ( ! version_compare( phpversion(), WP_STATISTICS_REQUIRE_PHP_VERSION, ">=" ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Show notice about PHP version
	 *
	 * @return void
	 */
	function php_version_notice() {

		$error = __( 'Your installed PHP Version is: ', 'wp-statistics' ) . PHP_VERSION . '. ';
		$error .= __( 'The <strong>WP-Statistics</strong> plugin requires PHP version <strong>', 'wp-statistics' ) . WP_STATISTICS_REQUIRE_PHP_VERSION . __( '</strong> or greater.', 'wp-statistics' );
		?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
        </div>
		<?php
	}

	/**
	 * Create tables on plugin activation
	 *
	 * @global object $wpdb
	 */
	public static function install() {
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
		$installer = new \WP_STATISTICS\Install();
		$installer->install();
	}

	/**
	 * Manage task on plugin deactivation
	 *
	 * @return void
	 */
	public static function uninstall() {
		delete_option( 'wp_statistics_removal' );
	}

	/**
	 * Instantiate the classes
	 *
	 * @return void
	 */
	public function instantiate() {
		//todo seperate all item to seperate class

		# Get Country Codes
		$GLOBALS['WP_Statistics']->country_codes = \WP_STATISTICS\Helper::get_country_codes();

		# Get User Detail
		$GLOBALS['WP_Statistics']->user = new \WP_STATISTICS\User();

		# Set Options
		$GLOBALS['WP_Statistics']->option = new \WP_STATISTICS\Option();

		# User IP
		$GLOBALS['WP_Statistics']->ip = \WP_STATISTICS\IP::getIP();

		# User Agent
		$GLOBALS['WP_Statistics']->agent = \WP_STATISTICS\UserAgent::getUserAgent();

		# User Online
		$GLOBALS['WP_Statistics']->users_online = new \WP_STATISTICS\UserOnline();

		# Visitor
		$GLOBALS['WP_Statistics']->visitor = new \WP_STATISTICS\Visitor();


		//Load Rest Api
		$this->init_rest_api();

		//Set constant
		$GLOBALS['WP_Statistics'] = $this;


		if ( \WP_STATISTICS\Helper::is_request( 'admin' ) ) {

			# Admin Menu
			$GLOBALS['WP_Statistics']->admin_menu = new \WP_STATISTICS\Admin_Menus;

			# Admin Asset
			new \WP_STATISTICS\Admin_Assets;

			# MultiSite Admin
			if ( is_multisite() ) {
				$GLOBALS['WP_Statistics']->admin_network = new \WP_STATISTICS\Network;
			}

			# Welcome Screen
			new \WP_STATISTICS\Welcome;

			# Admin Menu Bar
			$GLOBALS['WP_Statistics']->admin_bar = new \WP_STATISTICS\AdminBar;
		}

	}

	/**
	 * Check the REST API
	 */
	public function init_rest_api() {
		$this->restapi = new WP_Statistics_Rest();
	}


	/**
	 * return the referrer link for the current user.
	 *
	 * @param bool|false $default_referrer
	 *
	 * @return array|bool|string|void
	 */
	public function get_Referred( $default_referrer = false ) {

		//Check If Rest Request
		if ( $this->restapi->is_rest() ) {
			$this->referrer = $this->restapi->params( 'referred' );

			return $this->referrer;
		}

		if ( $this->referrer !== false ) {
			return $this->referrer;
		}

		$this->referrer = '';

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$this->referrer = $_SERVER['HTTP_REFERER'];
		}
		if ( $default_referrer ) {
			$this->referrer = $default_referrer;
		}

		$this->referrer = esc_sql( strip_tags( $this->referrer ) );

		if ( ! $this->referrer ) {
			$this->referrer = get_bloginfo( 'url' );
		}

		if ( $GLOBALS['WP_Statistics']->option->get( 'addsearchwords', false ) ) {
			// Check to see if this is a search engine referrer
			$SEInfo = $this->Search_Engine_Info( $this->referrer );

			if ( is_array( $SEInfo ) ) {
				// If we're a known SE, check the query string
				if ( $SEInfo['tag'] != '' ) {
					$result = $this->Search_Engine_QueryString( $this->referrer );

					// If there were no search words, let's add the page title
					if ( $result == '' || $result == 'No search query found!' ) {
						$result = wp_title( '', false );
						if ( $result != '' ) {
							$this->referrer = esc_url(
								add_query_arg(
									$SEInfo['querykey'],
									urlencode( '~"' . $result . '"' ),
									$this->referrer
								)
							);
						}
					}
				}
			}
		}

		return $this->referrer;
	}


	/**
	 * Checks to see if a search engine exists in the current list of search engines.
	 *
	 * @param      $search_engine_name
	 * @param null $search_engine
	 *
	 * @return int
	 */
	public function Check_Search_Engines( $search_engine_name, $search_engine = null ) {

		if ( strstr( $search_engine, $search_engine_name ) ) {
			return 1;
		}
	}

	/**
	 * Returns an array of information about a given search engine based on the url passed in.
	 * It is used in several places to get the SE icon or the sql query
	 * To select an individual SE from the database.
	 *
	 * @param bool|false $url
	 *
	 * @return array|bool
	 */
	public function Search_Engine_Info( $url = false ) {

		// If no URL was passed in, get the current referrer for the session.
		if ( ! $url ) {
			$url = isset( $_SERVER['HTTP_REFERER'] ) ? $this->get_Referred() : false;
		}

		// If there is no URL and no referrer, always return false.
		if ( $url == false ) {
			return false;
		}

		// Parse the URL in to it's component parts.
		$parts = parse_url( $url );

		// Get the list of search engines we currently support.
		$search_engines = wp_statistics_searchengine_list();

		// Loop through the SE list until we find which search engine matches.
		foreach ( $search_engines as $key => $value ) {
			$search_regex = wp_statistics_searchengine_regex( $key );

			preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

			if ( isset( $matches[1] ) ) {
				// Return the first matched SE.
				return $value;
			}
		}

		// If no SE matched, return some defaults.
		return array(
			'name'         => _x( 'Unknown', 'Search Engine', 'wp-statistics' ),
			'tag'          => '',
			'sqlpattern'   => '',
			'regexpattern' => '',
			'querykey'     => 'q',
			'image'        => 'unknown.png',
		);
	}

	/**
	 * Returns an array of information about a given search engine based on the url passed in.
	 * It is used in several places to get the SE icon or the sql query
	 * to select an individual SE from the database.
	 *
	 * @param bool|false $engine
	 *
	 * @return array|bool
	 */
	public function Search_Engine_Info_By_Engine( $engine = false ) {

		// If there is no URL and no referrer, always return false.
		if ( $engine == false ) {
			return false;
		}

		// Get the list of search engines we currently support.
		$search_engines = wp_statistics_searchengine_list();

		if ( array_key_exists( $engine, $search_engines ) ) {
			return $search_engines[ $engine ];
		}

		// If no SE matched, return some defaults.
		return array(
			'name'         => _x( 'Unknown', 'Search Engine', 'wp-statistics' ),
			'tag'          => '',
			'sqlpattern'   => '',
			'regexpattern' => '',
			'querykey'     => 'q',
			'image'        => 'unknown.png',
		);
	}

	/**
	 * Parses a URL from a referrer and return the search query words used.
	 *
	 * @param bool|false $url
	 * @return bool|string
	 */
	public function Search_Engine_QueryString( $url = false ) {

		// If no URL was passed in, get the current referrer for the session.
		if ( ! $url ) {
			$url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;
		}

		// If there is no URL and no referrer, always return false.
		if ( $url == false ) {
			return false;
		}

		// Parse the URL in to it's component parts.
		$parts = parse_url( $url );

		// Check to see if there is a query component in the URL (everything after the ?).  If there isn't one
		// set an empty array so we don't get errors later.
		if ( array_key_exists( 'query', $parts ) ) {
			parse_str( $parts['query'], $query );
		} else {
			$query = array();
		}

		// Get the list of search engines we currently support.
		$search_engines = wp_statistics_searchengine_list();

		// Loop through the SE list until we find which search engine matches.
		foreach ( $search_engines as $key => $value ) {
			$search_regex = wp_statistics_searchengine_regex( $key );

			preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

			if ( isset( $matches[1] ) ) {
				// Check to see if the query key the SE uses exists in the query part of the URL.
				if ( array_key_exists( $search_engines[ $key ]['querykey'], $query ) ) {
					$words = strip_tags( $query[ $search_engines[ $key ]['querykey'] ] );
				} else {
					$words = '';
				}

				// If no words were found, return a pleasant default.
				if ( $words == '' ) {
					$words = 'No search query found!';
				}

				return $words;
			}
		}

		// We should never actually get to this point, but let's make sure we return something
		// just in case something goes terribly wrong.
		return 'No search query found!';
	}


}
