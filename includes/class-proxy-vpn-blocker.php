<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class proxy_vpn_blocker {

	/**
	 * The single instance of proxy_vpn_blocker.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $_version;

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function __construct( $file = '', $version = '1.7.1' ) {
		$this->_version = $version;
		$this->_token   = 'proxy_vpn_blocker';

		// Load plugin environment variables.
		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'pvb_scripts_header_settings_function' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'pvb_scripts_header_information_function' ), 10, 1 );
		add_action( 'admin_footer', array( $this, 'pvb_scripts_footer_function' ), 10, 1 );

		// Load API for generic admin functions.
		if ( is_admin() ) {
			$this->admin = new proxy_vpn_blocker_Admin_API();
		}

		// Handle localisation.
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Handle updates.
		add_action( 'upgrader_process_complete', array( $this, 'install' ), 10, 2 );
	} // End __construct ()

	/**
	 * Load admin CSS.
	 */
	public function admin_enqueue_styles() {
		$current_screen = get_current_screen();
		$pages_array    = array( 'proxy_vpn_blocker_settings', 'proxy_vpn_blocker_blacklist', 'proxy_vpn_blocker_whitelist', 'proxy_vpn_blocker_statistics', 'proxy_vpn_blocker_advanced' );
		foreach ( $pages_array as $page ) {
			if ( strpos( $current_screen->base, $page ) != true ) {
				continue;
			} else {
				wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.min.css', array(), $this->_version );
				wp_enqueue_style( $this->_token . '-admin' );
				wp_register_style( $this->_token . '-chosen', esc_url( $this->assets_url ) . 'css/chosen.min.css', array(), $this->_version );
				wp_enqueue_style( $this->_token . '-chosen' );
			}
		}

	} // End admin_enqueue_styles ()

	/**
	 * Add scripts to PVB Settings Header
	 */
	public function pvb_scripts_header_settings_function() {
		$current_screen = get_current_screen();
		if ( strpos( $current_screen->base, 'proxy_vpn_blocker_settings' ) === false ) {
			return;
		} else {
			wp_register_script( $this->_token . '-settings-pvb-cookie-js', esc_url( $this->assets_url ) . 'js/cookie' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-cookie-js' );
		}
	}//end pvb_scripts_header_settings_function()

	/**
	 * Add scripts to PVB Settings Header Info
	 */
	public function pvb_scripts_header_information_function() {
		$current_screen = get_current_screen();
		if ( strpos( $current_screen->base, 'proxy_vpn_blocker_statistics' ) === false ) {
			return;
		} else {
			wp_register_script( $this->_token . '-settings-pvb-am4core-js', esc_url( $this->assets_url ) . 'js/amcharts/core.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4core-js' );
			wp_register_script( $this->_token . '-settings-pvb-am4charts-js', esc_url( $this->assets_url ) . 'js/amcharts/charts.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4charts-js' );
			wp_register_script( $this->_token . '-settings-pvb-am4charts-animated-js', esc_url( $this->assets_url ) . 'js/amcharts/theme/animated.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4charts-animated-js' );
		}
	}//end pvb_scripts_header_information_function()


	/**
	 * Add scripts to PVB Settings Footer
	 */
	public function pvb_scripts_footer_function() {
		$current_screen = get_current_screen();
		if ( strpos( $current_screen->base, 'proxy_vpn_blocker_settings' ) === false ) {
			return;
		} else {
			wp_register_script( $this->_token . '-settings-pvb-js', esc_url( $this->assets_url ) . 'js/settings' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-js' );
			wp_register_script( $this->_token . '-settings-pvb-chosen-js', esc_url( $this->assets_url ) . 'js/chosen.jquery' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-chosen-js' );
		}
	}//end pvb_scripts_footer_function()

	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'proxy-vpn-blocker', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		$domain = 'proxy-vpn-blocker';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} //End load_Plugin_Textdomain ()

	/**
	 * Main proxy_vpn_blocker Instance
	 *
	 * Ensures only one instance of proxy_vpn_blocker is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see proxy_vpn_blocker()
	 * @return Main proxy_vpn_blocker instance
	 */
	public static function instance( $file = '', $version = '1.7.1' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function install() {
		$this->log_version_number();
	} //End install()

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function log_version_number() {
		update_option( $this->_token . '_version', $this->_version );
	} //End _log_version_number()

}
