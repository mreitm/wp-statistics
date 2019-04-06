<?php

namespace WP_STATISTICS;

class AdminBar {

	public function __construct() {
		global $WP_Statistics;

		//Show Wordpress Admin Bar
		if ( $WP_Statistics->option->get( 'menu_bar' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 20 );
		}
	}

	/**
	 * Adds the admin bar menu if the user has selected it.
	 */
	public function admin_bar() {
		global $wp_admin_bar;

		if ( is_admin_bar_showing() && ( wp_statistics_check_access_user() ) ) {

			/**
			 * List Of Admin Bar Wordpress
			 *
			 * --- Array Arg ---
			 * Key : ID of Admin bar
			 */
			$admin_bar_list = array(
				'wp-statistic-menu'                   => array(
					'title' => '<span class="ab-icon"></span>',
					'href'  => \WP_Statistics_Admin_Pages::admin_url( 'overview' )
				),
				'wp-statistics-menu-useronline'       => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Online User', 'wp-statistics' ) . ": " . wp_statistics_useronline(),
					'href'   => \WP_Statistics_Admin_Pages::admin_url( 'online' )
				),
				'wp-statistics-menu-todayvisitor'     => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Today\'s Visitors', 'wp-statistics' ) . ": " . wp_statistics_visitor( 'today' ),
				),
				'wp-statistics-menu-todayvisit'       => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Today\'s Visits', 'wp-statistics' ) . ": " . wp_statistics_visit( 'today' )
				),
				'wp-statistics-menu-yesterdayvisitor' => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Yesterday\'s Visitors', 'wp-statistics' ) . ": " . wp_statistics_visitor( 'yesterday' ),
				),
				'wp-statistics-menu-yesterdayvisit'   => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Yesterday\'s Visits', 'wp-statistics' ) . ": " . wp_statistics_visit( 'yesterday' )
				),
				'wp-statistics-menu-viewstats'        => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'View Stats', 'wp-statistics' ),
					'href'   => \WP_Statistics_Admin_Pages::admin_url( 'overview' )
				)
			);
			foreach ( $admin_bar_list as $id => $v_admin_bar ) {
				$wp_admin_bar->add_menu( array_merge( array( 'id' => $id ), $v_admin_bar ) );
			}
		}
	}
}