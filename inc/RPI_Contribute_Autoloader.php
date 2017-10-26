<?php
/**
 * Class RPI_Contribute_Autoloader
 *
 * Autoloader for the plugin
 *
 */

class RPI_Contribute_Autoloader {
	/**
	 * Registers autoloader function to spl_autoload
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @action  rpi_contribute_autoload_register
	 * @return  void
	 */
	public static function register(){
		spl_autoload_register( 'RPI_Contribute_Autoloader::load' );
		do_action( 'rpi_contribute_autoload_register' );
	}

	/**
	 * Unregisters autoloader function with spl_autoload
	 *
	 * @ince    0.1
	 * @access  public
	 * @static
	 * @action  rpi_contribute_autoload_unregister
	 * @return  void
	 */
	public static function unregister(){
		spl_autoload_unregister( 'RPI_Contribute_Autoloader::load' );
		do_action( 'rpi_contribute_autoload_unregister' );
	}

	/**
	 * Autoloading function
	 *
	 * @since   0.1
	 * @param   string  $classname
	 * @access  public
	 * @static
	 * @return  void
	 */
	public static function load( $classname ){
		$file =  dirname( __FILE__ ) . DIRECTORY_SEPARATOR . ucfirst( $classname ) . '.php';
		if( file_exists( $file ) ) require_once $file;
	}
}
