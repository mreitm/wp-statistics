<?php

/**
 * Main bootstrap class for WP Statistics
 *
 * @package WP Statistics
 */
class WP_Statistics {
	/**
	 * Holds various class instances
	 *
	 * @since 2.5.7
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * IP address of visitor
	 *
	 * @var bool|string
	 */
	public $ip = false;
	/**
	 * Hash of visitors IP address
	 *
	 * @var bool|string
	 */
	public $ip_hash = false;
	/**
	 * Agent of visitor browser
	 *
	 * @var string
	 */
	public $agent;
	/**
	 * a coefficient to record number of visits
	 *
	 * @var int
	 */
	public $coefficient = 1;

	/**
	 * Menu Slugs
	 *
	 * @var array
	 */
	public $menu_slugs = array();
	/**
	 * is current request
	 *
	 * @var bool
	 */
	public $is_ajax_logger_request = false;
	/**
	 * Result of queries
	 *
	 * @var
	 */
	private $result;
	/**
	 * Historical data
	 *
	 * @var array
	 */
	private $historical = array();

	/**
	 * Country Codes
	 *
	 * @var bool|string
	 */
	private $country_codes = false;
	/**
	 * Referrer
	 *
	 * @var bool
	 */
	private $referrer = false;
	/**
	 * Installed Version
	 *
	 * @var string
	 */
	public static $installed_version;
	/**
	 * Registry for plugin settings
	 *
	 * @var array
	 */
	public static $reg = array();
	/**
	 * Pages slugs
	 *
	 * @var array
	 */
	public static $page = array();
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
		/*
		 * Check PHP Support
		 */
		if ( ! $this->require_php_version() ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return;
		}
		/*
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
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param string $prop
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}
		return $this->{$prop};
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
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';

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
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-welcome.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-network-admin.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-purge.php';

			//Admin Notice
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-notice.php';

			//TinyMCE Editor
			require_once WP_STATISTICS_DIR . 'includes/admin/TinyMCE/class-wp-statistics-tinymce.php';

			//Admin Bar
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-adminbar.php';

		}

		// Front Class.
		if ( ! is_admin() ) {
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

		# Get User Detail
		$GLOBALS['WP_Statistics']->user = new \WP_STATISTICS\User();

		# Set Options
		$GLOBALS['WP_Statistics']->option = new \WP_STATISTICS\Option();

		# User IP


		//Load Rest Api
		$this->init_rest_api();

		//Get user Ip
		$this->get_IP();

		// Check if the has IP is enabled.
		if ( $GLOBALS['WP_Statistics']->option->get( 'hash_ips' ) == true ) {
			$this->ip_hash = $this->get_hash_string();
		}

		//Set Pages
		$this->set_pages();

		//Reset User Online Count
		add_action( 'wp_loaded', array( $this, 'reset_user_online' ) );

		//Get Current User Agent
		$this->agent = $this->get_UserAgent();

		//Set constant
		$GLOBALS['WP_Statistics'] = $this;
		//$GLOBALS['WP_Statistics']->timezone = $this->container['timezone']; //TODO Remove At last

		//$GLOBALS['WP_Statistics'] = array_merge($this->container, $this);
		new \WP_STATISTICS\AdminBar();
	}

	/**
	 * Check the REST API
	 */
	public function init_rest_api() {
		$this->restapi = new WP_Statistics_Rest();
	}

	/**
	 * Set Coefficient
	 */
	public function set_coefficient() {
		// Set the default co-efficient.
		$this->coefficient = $GLOBALS['WP_Statistics']->option->get( 'coefficient', 1 );
		// Double check the co-efficient setting to make sure it's not been set to 0.
		if ( $this->coefficient <= 0 ) {
			$this->coefficient = 1;
		}
	}


	/**
	 * Set pages slugs
	 */
	public function set_pages() {
		if ( ! isset( WP_Statistics::$page['overview'] ) ) {

			/**
			 * List Of Admin Page Slug WP-statistics
			 *
			 * -- Array Arg ---
			 * key   : page key for using another methods
			 * value : Admin Page Slug
			 */
			$list = array(
				'overview'     => 'overview',
				'browser'      => 'browsers',
				'countries'    => 'countries',
				'exclusions'   => 'exclusions',
				'hits'         => 'hits',
				'online'       => 'online',
				'pages'        => 'pages',
				'categories'   => 'categories',
				'authors'      => 'authors',
				'tags'         => 'tags',
				'referrers'    => 'referrers',
				'searches'     => 'searches',
				'words'        => 'words',
				'top-visitors' => 'top_visitors',
				'visitors'     => 'visitors',
				'optimization' => 'optimization',
				'settings'     => 'settings',
				'plugins'      => 'plugins',
				'donate'       => 'donate',
			);
			foreach ( $list as $page_key => $page_slug ) {
				WP_Statistics::$page[ $page_key ] = 'wps_' . $page_slug . '_page';
			}
		}
	}

	/**
	 * Generate hash string
	 */
	public function get_hash_string() {
		// Check If Rest Request
		if ( $this->restapi->is_rest() ) {
			return $this->restapi->params( 'hash_ip' );
		}

		// Check the user agent has exist.
		if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			$key = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$key = 'Unknown';
		}

		return '#hash#' . sha1( $this->ip . $key );
	}

	/**
	 * geo ip Loader
	 *
	 * @param $pack
	 * @return bool|\GeoIp2\Database\Reader
	 */
	static function geoip_loader( $pack ) {

		$upload_dir = wp_upload_dir();
		$geoip      = $upload_dir['basedir'] . '/wp-statistics/' . WP_Statistics_Updates::$geoip[ $pack ]['file'] . '.mmdb';
		if ( file_exists( $geoip ) ) {
			try {
				$reader = new \GeoIp2\Database\Reader( $geoip );
			} catch ( \MaxMind\Db\Reader\InvalidDatabaseException $e ) {
				return false;
			}
		} else {
			return false;
		}

		return $reader;
	}

	/**
	 * During installation of WP Statistics some initial data needs to be loaded
	 * in to the database so errors are not displayed.
	 * This function will add some initial data if the tables are empty.
	 */
	public function Primary_Values() {
		global $wpdb;

		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_useronline" );

		if ( ! $this->result ) {

			$wpdb->insert(
				$wpdb->prefix . "statistics_useronline",
				array(
					'ip'        => $this->store_ip_to_db(),
					'timestamp' => \WP_STATISTICS\TimeZone::getCurrentDate( 'U' ),
					'date'      => \WP_STATISTICS\TimeZone::getCurrentDate(),
					'referred'  => $this->get_Referred(),
					'agent'     => $this->agent['browser'],
					'platform'  => $this->agent['platform'],
					'version'   => $this->agent['version'],
				)
			);
		}

		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_visit" );

		if ( ! $this->result ) {

			$wpdb->insert(
				$wpdb->prefix . "statistics_visit",
				array(
					'last_visit'   => $this->Current_Date(),
					'last_counter' => $this->Current_date( 'Y-m-d' ),
					'visit'        => 1,
				)
			);
		}

		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_visitor" );

		if ( ! $this->result ) {

			$wpdb->insert(
				$wpdb->prefix . "statistics_visitor",
				array(
					'last_counter' => $this->Current_date( 'Y-m-d' ),
					'referred'     => $this->get_Referred(),
					'agent'        => $this->agent['browser'],
					'platform'     => $this->agent['platform'],
					'version'      => $this->agent['version'],
					'ip'           => $this->store_ip_to_db(),
					'location'     => '000',
				)
			);
		}
	}

	/**
	 * During installation of WP Statistics some initial options need to be set.
	 * This function will save a set of default options for the plugin.
	 *
	 * @param null $option_name
	 *
	 * @return array
	 */
	public function Default_Options( $option_name = null ) {
		$options = array();

		if ( ! isset( $wps_robotarray ) ) {
			// Get the robots list, we'll use this for both upgrades and new installs.
			require_once WP_STATISTICS_DIR . 'includes/defines/robots-list.php';
		}

		$options['robotlist'] = trim( $wps_robotslist );

		// By default, on new installs, use the new search table.
		$options['search_converted'] = 1;

		// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
		$options['anonymize_ips']         = false;
		$options['geoip']                 = false;
		$options['useronline']            = true;
		$options['visits']                = true;
		$options['visitors']              = true;
		$options['pages']                 = true;
		$options['check_online']          = '120';
		$options['menu_bar']              = false;
		$options['coefficient']           = '1';
		$options['stats_report']          = false;
		$options['time_report']           = 'daily';
		$options['send_report']           = 'mail';
		$options['content_report']        = '';
		$options['update_geoip']          = true;
		$options['store_ua']              = false;
		$options['robotlist']             = $wps_robotslist;
		$options['exclude_administrator'] = true;
		$options['disable_se_clearch']    = true;
		$options['disable_se_qwant']      = true;
		$options['disable_se_baidu']      = true;
		$options['disable_se_ask']        = true;
		$options['map_type']              = 'jqvmap';

		$options['force_robot_update'] = true;

		if ( $option_name and isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		}

		return $options;
	}

	/**
	 * Processes a string that represents an IP address and returns
	 * either FALSE if it's invalid or a valid IP4 address.
	 *
	 * @param $ip
	 *
	 * @return bool|string
	 */
	private function get_ip_value( $ip ) {
		// Reject anything that's not a string.
		if ( ! is_string( $ip ) ) {
			return false;
		}

		// Trim off any spaces.
		$ip = trim( $ip );

		// Process IPv4 and v6 addresses separately.
		if ( $this->isValidIPv6( $ip ) ) {
			// Reject any IPv6 addresses if IPv6 is not compiled in to this version of PHP.
			if ( ! defined( 'AF_INET6' ) ) {
				return false;
			}
		} else {
			// Trim off any port values that exist.
			if ( strstr( $ip, ':' ) !== false ) {
				$temp = explode( ':', $ip );
				$ip   = $temp[0];
			}

			// Check to make sure the http header is actually an IP address and not some kind of SQL injection attack.
			$long = ip2long( $ip );

			// ip2long returns either -1 or FALSE if it is not a valid IP address depending on the PHP version, so check for both.
			if ( $long == - 1 || $long === false ) {
				return false;
			}
		}

		// If the ip address is blank, reject it.
		if ( $ip == '' ) {
			return false;
		}

		// We're got a real IP address, return it.
		return $ip;
	}

	/**
	 * Returns the current IP address of the remote client.
	 *
	 * @return bool|string
	 */
	public function get_IP() {

		//Check If Rest Api Request
		if ( $this->restapi->is_rest() ) {
			$this->ip = $this->restapi->params( 'ip' );

			return $this->ip;
		}

		// Check to see if we've already retrieved the IP address and if so return the last result.
		if ( $this->ip !== false ) {
			return $this->ip;
		}

		// Get User IP
		$whip = new \Vectorface\Whip\Whip( \Vectorface\Whip\Whip::PROXY_HEADERS | \Vectorface\Whip\Whip::REMOTE_ADDR );
		$whip->addCustomHeader( 'HTTP_CLIENT_IP' );
		$whip->addCustomHeader( 'HTTP_X_REAL_IP' );
		$user_ip = $whip->getValidIpAddress();
		if ( $user_ip != false ) {
			$this->ip = $user_ip;
		}

		// If no valid ip address has been found, use 127.0.0.1 (aka localhost).
		if ( false === $this->ip ) {
			$this->ip = '127.0.0.1';
		}

		return $this->ip;
	}

	/**
	 * Store User IP To Database
	 */
	public function store_ip_to_db() {

		//Get User ip
		$user_ip = $this->ip;

		// use 127.0.0.1 If no valid ip address has been found.
		if ( false === $user_ip ) {
			return '127.0.0.1';
		}

		// If the anonymize IP enabled for GDPR.
		if ( $GLOBALS['WP_Statistics']->option->get( 'anonymize_ips' ) == true ) {
			$user_ip = substr( $user_ip, 0, strrpos( $user_ip, '.' ) ) . '.0';
		}

		return $user_ip;
	}

	/**
	 * Validate an IPv6 IP address
	 *
	 * @param  string $ip
	 *
	 * @return boolean - true/false
	 */
	private function isValidIPv6( $ip ) {
		if ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Calls the user agent parsing code.
	 *
	 * @return array|\string[]
	 */
	public function get_UserAgent() {
		//Check If Rest Request
		if ( $this->restapi->is_rest() ) {
			return array(
				'browser'  => $this->restapi->params( 'browser' ),
				'platform' => $this->restapi->params( 'platform' ),
				'version'  => $this->restapi->params( 'version' )
			);
		}

		// Check function exist.
		if ( function_exists( 'getallheaders' ) ) {
			$user_agent = getallheaders();
		} elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$user_agent = '';
		}

		$result = new WhichBrowser\Parser( $user_agent );
		$agent  = array(
			'browser'  => ( isset( $result->browser->name ) ) ? $result->browser->name : _x( 'Unknown', 'Browser', 'wp-statistics' ),
			'platform' => ( isset( $result->os->name ) ) ? $result->os->name : _x( 'Unknown', 'Platform', 'wp-statistics' ),
			'version'  => ( isset( $result->os->version->value ) ) ? $result->os->version->value : _x( 'Unknown', 'Version', 'wp-statistics' ),
		);

		return $agent;
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

	/**
	 * Get historical data
	 *
	 * @param        $type
	 * @param string $id
	 *
	 * @return int|null|string
	 */
	public function Get_Historical_Data( $type, $id = '' ) {
		global $wpdb;

		$count = 0;
		switch ( $type ) {
			case 'visitors':
				if ( array_key_exists( 'visitors', $this->historical ) ) {
					return $this->historical['visitors'];
				} else {
					$result = $wpdb->get_var( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'visitors'" );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical['visitors'] = $count;
				}

				break;
			case 'visits':
				if ( array_key_exists( 'visits', $this->historical ) ) {
					return $this->historical['visits'];
				} else {
					$result = $wpdb->get_var( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'visits'" );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical['visits'] = $count;
				}

				break;
			case 'uri':
				if ( array_key_exists( $id, $this->historical ) ) {
					return $this->historical[ $id ];
				} else {
					$result = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'uri' AND uri = %s", $id ) );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical[ $id ] = $count;
				}

				break;
			case 'page':
				if ( array_key_exists( $id, $this->historical ) ) {
					return $this->historical[ $id ];
				} else {
					$result = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'uri' AND page_id = %d", $id ) );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical[ $id ] = $count;
				}

				break;
		}

		return $count;
	}

	/**
	 * Get country codes
	 *
	 * @return array|bool|string
	 */
	public function get_country_codes() {
		if ( $this->country_codes == false ) {
			$ISOCountryCode = array();
			require_once WP_STATISTICS_DIR . "includes/defines/country-codes.php";
			$this->country_codes = $ISOCountryCode;
		}

		return $this->country_codes;
	}


	/**
	 * Reset Online User Process By Option time
	 *
	 * @return string
	 */
	public function reset_user_online() {
		global $WP_Statistics, $wpdb;

		//Check User Online is Active in this Wordpress
		if ( $WP_Statistics->option->get( 'useronline' ) ) {

			//Get Not timestamp
			$now = \WP_STATISTICS\TimeZone::getCurrentDate( 'U' );

			// Set the default seconds a user needs to visit the site before they are considered offline.
			$reset_time = 120;

			// Get the user set value for seconds to check for users online.
			if ( $WP_Statistics->option->get( 'check_online' ) ) {
				$reset_time = $WP_Statistics->option->get( 'check_online' );
			}

			// We want to delete users that are over the number of seconds set by the admin.
			$time_diff = $now - $reset_time;

			//Last check Time
			$wps_run = get_option( "wp_statistics_check_useronline" );
			if ( isset( $wps_run ) and is_numeric( $wps_run ) ) {
				if ( ( $wps_run + $reset_time ) > $now ) {
					return;
				}
			}

			// Call the deletion query.
			$wpdb->query( "DELETE FROM `" . WP_STATISTICS\DB::table( 'useronline' ) . "` WHERE timestamp < {$time_diff}" );

			//Update Last run this Action
			update_option( "wp_statistics_check_useronline", $now );
		}
	}

	/**
	 * Get Number Days From install this plugin
	 * this method used for `ALL` Option in Time Range Pages
	 */
	public static function get_number_days_install_plugin() {
		global $wpdb, $WP_Statistics;

		//Create Empty default Option
		$first_day = '';

		//First Check Visitor Table , if not exist Web check Pages Table
		$list_tbl = array(
			'visitor' => array( 'order_by' => 'ID', 'column' => 'last_counter' ),
			'pages'   => array( 'order_by' => 'page_id', 'column' => 'date' ),
		);
		foreach ( $list_tbl as $tbl => $val ) {
			$first_day = $wpdb->get_var( "SELECT `" . $val['column'] . "` FROM `" . WP_STATISTICS\DB::table( $tbl ) . "` ORDER BY `" . $val['order_by'] . "` ASC LIMIT 1" );
			if ( ! empty( $first_day ) ) {
				break;
			}
		}

		//Calculate hit day if range is exist
		if ( empty( $first_day ) ) {
			$result = array(
				'days' => 1,
				'date' => current_time( 'timestamp' )
			);
		} else {
			$earlier = new \DateTime( $first_day );
			$later   = new \DateTime( \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) );
			$result  = array(
				'days'      => $later->diff( $earlier )->format( "%a" ),
				'timestamp' => strtotime( $first_day ),
				'first_day' => $first_day,
			);
		}

		return $result;
	}

}
