<?php

/**
 * Plugin Name:      RPI Contribute
 * Plugin URI:       https://github.com/rpi-virtuell/rpi-contribute
 * Description:      Connect a wordpress instance to rpi materialpool to contribute content
 * Author:           Frank Neumann-Staude
 * Version:          0.1.0
 * Licence:          GPLv3
 * Author URI:       http://staude.net
 * Text Domain:      rpi-contribute
 * Domain Path:      /languages
 * GitHub Plugin URI: https://github.com/rpi-virtuell/rpi-contribute
 * GitHub Branch:     master
 */

class RPI_Contribute {
	/**
	 * Plugin version
	 *
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $version = "0.1.0";

	/**
	 * Singleton object holder
	 *
	 * @var     mixed
	 * @since   0.1
	 * @access  private
	 */
	static private $instance = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_name = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $textdomain = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_base_name = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_url = NULL;

	/**
	 * @var     mixed
	 * @since   2.8
	 * @access  public
	 */
	static public $plugin_dir = NULL;

	/**
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_filename = __FILE__;

	/**
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_version = '';

	/**
	 * @var     string
	 * @since   0.2.0
	 * @access  public
	 */
	static public $notice = '';

	/**
	 * Plugin constructor.
	 *
	 * @since   0.1
	 * @access  public
	 * @uses    plugin_basename
	 * @action  rpi_contribute_init
	 */
	public function __construct () {
		// set the textdomain variable
		self::$textdomain = self::get_textdomain();

		// The Plugins Name
		self::$plugin_name = $this->get_plugin_header( 'Name' );

		// The Plugins Basename
		self::$plugin_base_name = plugin_basename( __FILE__ );

		// The Plugins Version
		self::$plugin_version = $this->get_plugin_header( 'Version' );

		// url to plugins root
		self::$plugin_url = plugins_url('/',__FILE__);

		// plugins root
		self::$plugin_dir = plugin_dir_path(__FILE__);

		// Load the textdomain
		$this->load_plugin_textdomain();

		// Add Filter & Actions

		add_action( 'admin_init',                   array( 'RPI_Contribute_Options', 'register_settings' ) );
		add_action( 'admin_menu',                   array( 'RPI_Contribute_Options', 'options_menu' ) );

		add_filter( 'http_request_args',            array( 'RPI_Contribute_API','set_http_request_args'), 999,2);
		add_action( 'edit_user_profile',            array( 'RPI_Contribute_Options', 'edit_user_profile' ) );
		add_action( 'show_user_profile',            array( 'RPI_Contribute_Options', 'edit_user_profile' ) );
		add_action( 'personal_options_update',      array( 'RPI_Contribute_Options', 'save_profile_fields' ) );
		add_action( 'add_meta_boxes',               array( 'RPI_Contribute_Posts', 'add_metaboxes' ) );

		do_action( 'rpi_contribute_init' );

	}

	/**
	 * Creates an Instance of this Class
	 *
	 * @since   0.1
	 * @access  public
	 * @return  RW_Remote_Auth_Client
	 */
	public static function get_instance() {

		if ( NULL === self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Load the localization
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	load_plugin_textdomain, plugin_basename
	 * @filters rw_remote_auth_client_translationpath path to translations files
	 * @return	void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( self::get_textdomain(), false, apply_filters ( 'rpi_contribution_translationpath', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
	}

	/**
	 * Get a value of the plugin header
	 *
	 * @since   0.1
	 * @access	protected
	 * @param	string $value
	 * @uses	get_plugin_data, ABSPATH
	 * @return	string The plugin header value
	 */
	protected function get_plugin_header( $value = 'TextDomain' ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php');
		}

		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_value = $plugin_data[ $value ];

		return $plugin_value;
	}

	/**
	 * get the textdomain
	 *
	 * @since   0.1
	 * @static
	 * @access	public
	 * @return	string textdomain
	 */
	public static function get_textdomain() {
		if( is_null( self::$textdomain ) )
			self::$textdomain = self::get_plugin_data( 'TextDomain' );

		return self::$textdomain;
	}

	/**
	 * get the textdomain path
	 *
	 * @since   0.1
	 * @static
	 * @access	public
	 * @return	string Domain Path
	 */
	public static function get_textdomain_path() {
		return self::get_plugin_data( 'DomainPath' );
	}

	/**
	 * return plugin comment data
	 *
	 * @since   0.1
	 * @uses    get_plugin_data
	 * @access  public
	 * @param   $value string, default = 'Version'
	 *		Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
	 * @return  string
	 */
	public static function get_plugin_data( $value = 'Version' ) {

		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		$plugin_data  = get_plugin_data ( __FILE__ );
		$plugin_value = $plugin_data[ $value ];

		return $plugin_value;
	}


	/**
	 * creates an admin notification on admin pages
	 *
	 * @since   0.2.0
	 * @uses     _notice_admin
	 * @access  public
	 * @param label         $value string,  default = 'info'
	 *        error, warning, success, info
	 * @param message       $value string
	 * @param $dismissible  $value bool,  default = false
	 *
	 */
	public static function notice_admin($label=info, $message, $dismissible=false ) {
		$notice = array(
			'label'             =>  $label
		,   'message'           =>  $message
		,   'is-dismissible'    =>  (bool)$dismissible

		);
		self::_notice_admin($notice);
	}

	/**
	 * creates an admin notification on admin pages
	 *
	 * @since   0.2.0
	 * @uses     _notice_admin
	 * @access  private
	 * @param $value array
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	 */

	static function _notice_admin($notice) {

		self::$notice = $notice;

		add_action( 'admin_notices',function(){

			$note = RW_Remote_Auth_Client::$notice;
			$note['IsDismissible'] =
				(isset($note['is-dismissible']) && $note['is-dismissible'] == true) ?
					' is-dismissible':'';
			?>
			<div class="notice notice-<?php echo $note['label']?><?php echo $note['IsDismissible']?>">
				<p><?php echo __( $note['message'] ,RPI_Contribute::get_textdomain() ); ?></p>
			</div>
			<?php
		});

	}
}


if ( class_exists( 'RPI_Contribute' ) ) {

	add_action( 'plugins_loaded', array( 'RPI_Contribute', 'get_instance' ) );

	require_once 'inc/RPI_Contribute_Autoloader.php';
	RPI_Contribute_Autoloader::register();

	register_activation_hook( __FILE__, array( 'RPI_Contribute_Installation', 'on_activate' ) );
	register_uninstall_hook(  __FILE__,	array( 'RPI_Contribute_Installation', 'on_uninstall' ) );
	register_deactivation_hook( __FILE__, array( 'RPI_Contribute_Installation', 'on_deactivation' ) );
}
