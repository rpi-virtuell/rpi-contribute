<?php

/**
 * Class RPI_Contibute_Installation
 *
 * Contains some helper code for plugin installation
 *
 */

class RPI_Contribute_Installation {
	/**
	 * Check some thinks on plugin activation
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @return  void
	 */
	public static function on_activate() {

		// check WordPress version
		if ( ! version_compare( $GLOBALS[ 'wp_version' ], '4.0', '>=' ) ) {
			deactivate_plugins( RPI_Contribute::$plugin_filename );
			die(
			wp_sprintf(
				'<strong>%s:</strong> ' .
				__( 'This plugin requires WordPress 4.0 or newer to work', RPI_Contribute::get_textdomain() )
				, RPI_Contribute::get_plugin_data( 'Name' )
			)
			);
		}


		// check php version
		if ( version_compare( PHP_VERSION, '5.2.0', '<' ) ) {
			deactivate_plugins( RPI_Contribute::$plugin_filename );
			die(
			wp_sprintf(
				'<strong>%1s:</strong> ' .
				__( 'This plugin requires PHP 5.2 or newer to work. Your current PHP version is %1s, please update.', RPI_Contribute::get_textdomain() )
				, RPI_Contribute::get_plugin_data( 'Name' ), PHP_VERSION
			)
			);
		}
		// check multisite
		if ( is_multisite() && ! is_network_admin() ) {
			deactivate_plugins( RPI_Contribute::$plugin_filename );

			wp_die(
				'<strong>'.__('Sorry Admin!',RPI_Contribute::get_textdomain()).'</strong> ' .
				__( 'In a multisite context you may only activate this plugin network wide. ', RPI_Contribute::get_textdomain() )

			);
		}

	}

	/**
	 * Clean up after deactivation
	 *
	 * Clean up after deactivation the plugin
	 * Refresh rewriterules
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @return  void
	 */
	public static function on_deactivation() {
	}

	/**
	 * Clean up after uninstall
	 *
	 * Clean up after uninstall the plugin.
	 * Delete options and other stuff.
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @return  void
	 *
	 */
	public static function on_uninstall() {

	}

}