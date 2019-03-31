<?php

namespace WP_STATISTICS;

class AdminNotice {
	/**
	 * Array of notices - name => callback.
	 *
	 * @var array
	 */
	private static $core_notices = array(

	);

	/**
	 * AdminNotice constructor.
	 */
	public function __construct() {

		//Check Core Notice
		foreach ( self::$core_notices as $notice ) {
			if ( self::$notice() === true ) {
				add_action( 'admin_notices', array( $this, $notice . "_notice" ), 10, 2 );
			}
		}
	}



}

new AdminNotice();