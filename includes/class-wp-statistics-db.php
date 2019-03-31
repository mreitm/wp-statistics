<?php

namespace WP_STATISTICS;

class DB {
	/**
	 * List Of wp-statistics Mysql Table
	 *
	 * @var array
	 */
	public static $db_table = array(
		'useronline',
		'visit',
		'visitor',
		'exclusions',
		'pages',
		'search',
		'historical',
		'visitor_relationships'
	);

	/**
	 * Table List Wp-statistics
	 *
	 * @param string $export
	 * @param array $except
	 * @return array|null
	 */
	public static function table( $export = 'all', $except = array() ) {
		global $wpdb;

		//Create Empty Object
		$list = array();

		//List Of Table
		if ( is_string( $except ) ) {
			$except = array( $except );
		}
		$mysql_list_table = array_diff( self::$db_table, $except );
		foreach ( $mysql_list_table as $tbl ) {
			$table_name = $wpdb->prefix . 'statistics_' . $tbl;
			if ( $export == "all" ) {
				if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
					$list[ $tbl ] = $table_name;
				}
			} else {
				$list[ $tbl ] = $table_name;
			}
		}

		//Export Data
		if ( $export == 'all' ) {
			return $list;
		} else {
			if ( array_key_exists( $export, $list ) ) {
				return $list[ $export ];
			}
		}

		return null;
	}

}