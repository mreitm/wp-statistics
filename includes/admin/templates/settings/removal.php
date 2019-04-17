<table class="form-table">
    <tbody>
    <tr valign="top">
        <th scope="row" colspan="2"><h3><?php _e( 'WP Statisitcs Removal', 'wp-statistics' ); ?></h3></th>
    </tr>

    <tr valign="top">
        <th scope="row" colspan="2">
			<?php _e(
				'Uninstalling WP Statistics will not remove the data and settings, you can use this option to remove the WP Statistics data from your install before uninstalling the plugin.',
				'wp-statistics'
			); ?>
            <br>
            <br>
			<?php _e(
				'Once you submit this form the settings will be deleted during the page load, however WP Statistics will still show up in your Admin menu until another page load is executed.',
				'wp-statistics'
			); ?>
        </th>
    </tr>

    <tr valign="top">
        <th scope="row">
            <label for="reset-plugin"><?php _e( 'Reset options:', 'wp-statistics' ); ?></label>
        </th>

        <td>
            <input id="reset-plugin" type="checkbox" name="wps_reset_plugin">
            <label for="reset-plugin"><?php _e( 'Reset', 'wp-statistics' ); ?></label>

            <p class="description"><?php _e(
					'Reset the plugin options to the defaults. This will remove all user and global settings but will keep all other data. This action cannot be undone. Note: For multisite installs this will reset all sites to the defaults.',
					'wp-statistics'
				); ?></p>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row">
            <label for="remove-plugin"><?php _e( 'Remove data and settings:', 'wp-statistics' ); ?></label>
        </th>

        <td>
            <input id="remove-plugin" type="checkbox" name="wps_remove_plugin">
            <label for="remove-plugin"><?php _e( 'Remove', 'wp-statistics' ); ?></label>

            <p class="description"><?php _e(
					'Remove data and settings, this action cannot be undone.',
					'wp-statistics'
				); ?></p>
        </td>
    </tr>

    </tbody>
</table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' ); ?>