<div class="wrap wps-wrap">
	<?php use WP_STATISTICS\Admin_Helper;

	Admin_Helper::show_page_title( __( 'Extensions for WP-Statistics', 'wp-statistics' ) ); ?>

    <p><p><?php _e( 'These extensions add functionality to your WP-Statistics.', 'wp-statistics' ); ?></p><br/></p>
    <?php include( WP_STATISTICS_DIR . "includes/admin/templates/add-ons.php" ); ?>
</div>