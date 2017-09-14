<?php
/*
* Plugin Name: Bonster Management
* Plugin URI: http://bonster.com.co
* Description: Manage Bonster store.
* Version: 1.0.0
* Author: Bonster SAS
* Author URI: http://bonster.com.co
* Developer: jgarcia
* Developer URI: https://www.facebook.com/jmillos13
*
* Text Domain: bonster-management
* Domain Path: /languages/
*
* Requires at least: 3.8
* Tested up to: 4.6
*
* Copyright: © 2016 Bonster SAS.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ini_set('display_errors', 2);
setlocale(LC_ALL, 'es_ES');

require(dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'loader.php');
require_once('includes/woocommerce/bn-payment-gateways.php');
require_once('includes/woocommerce/shop-order.php');
require_once('includes/woocommerce/checkout.php');

require_once('wc-bonster-shipping.php');
// require_once('bn-stock-control.php');
require_once('includes/post-types/purchase-order.php');
require_once('includes/post-types/expense.php');
require_once('includes/post-types/neighborhoods.php');
require_once('includes/post-types/department.php');

final class Bonster_Management {

	public static $version = "0.0.1";

	public static $adminBundleJsUrl = null;

	/**
	 * The single instance of the class.
	 *
	 * @var Bonster_Management
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	public function __construct(){
		self::$adminBundleJsUrl = "http://localhost:8080/bundle.js";

		add_action( 'plugins_loaded', array($this, 'plugins_loaded') );

		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		add_action('acf/init', array($this, 'acf_init'));

		add_action( 'wp_enqueue_scripts', array($this, 'front_script') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
	}

	public static function plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	public static function plugin_path() {
		return plugin_dir_path(__FILE__);
	}

	public function plugins_loaded(){

	}

	public function acf_init(){
		if( function_exists('acf_add_options_page') ) {
			
			$option_page = acf_add_options_page(array(
				'page_title' 	=> 'Configuración Bonster',
				'menu_title' 	=> 'Configuración',
				'menu_slug' 	=> 'bonster-settings',
				'capability' 	=> 'manage_options',
				'parent_slug'	=> 'bn_config',
				'position' 		=> 1,
				'redirect' 		=> false,
			));
			
		}
	}

	public function front_script(){
		wp_enqueue_style('pickadate.js-default', plugins_url('/assets/vendors/pickadate/themes/default.css', __FILE__), array(), self::$version);
		wp_enqueue_style('pickadate.js-default.date', plugins_url('/assets/vendors/pickadate/themes/default.date.css', __FILE__), array(), self::$version);
		wp_enqueue_style('pickadate.js-default.time', plugins_url('/assets/vendors/pickadate/themes/default.time.css', __FILE__), array(), self::$version);
		wp_enqueue_script( 'pickadate.js-picker', plugins_url('/assets/vendors/pickadate/picker.js', __FILE__), array('jquery'), self::$version, false );
		wp_enqueue_script( 'pickadate.js-date', plugins_url('/assets/vendors/pickadate/picker.date.js', __FILE__), array('jquery'), self::$version, false );
		wp_enqueue_script( 'pickadate.js-time', plugins_url('/assets/vendors/pickadate/picker.time.js', __FILE__), array('jquery'), self::$version, false );
		wp_enqueue_script( 'pickadate.js-translation', plugins_url('/assets/vendors/pickadate/translations/es_ES.js', __FILE__), array('jquery'), self::$version, false );
	}

	/**
	 * Register plugin menus
	 *
	 * @return void
	 */
	public function admin_menu() {
		// top level WP Migration menu
		add_menu_page(
			'Bonster Configuración',
			'Bonster',
			'read',
			'bn_config',
			'',
			'',
			'57'
		);

		// sublevel Export menu
		/*add_submenu_page(
			'site-migration-export',
			__( 'Export', AI1WM_PLUGIN_NAME ),
			__( 'Export', AI1WM_PLUGIN_NAME ),
			'export',
			'site-migration-export',
			'Ai1wm_Export_Controller::index'
		);

		// sublevel Import menu
		add_submenu_page(
			'site-migration-export',
			__( 'Import', AI1WM_PLUGIN_NAME ),
			__( 'Import', AI1WM_PLUGIN_NAME ),
			'import',
			'site-migration-import',
			'Ai1wm_Import_Controller::index'
		);

		// sublevel Backups menu
		add_submenu_page(
			'site-migration-export',
			__( 'Backups', AI1WM_PLUGIN_NAME ),
			__( 'Backups', AI1WM_PLUGIN_NAME ),
			'import',
			'site-migration-backups',
			'Ai1wm_Backups_Controller::index'
		);*/
	}

	public static function admin_script(){
		global $post_id;
		
		wp_register_script( 'management_bonster_bundle', self::$adminBundleJsUrl, array('jquery', 'wc-admin-meta-boxes'), self::$version, true );
		wp_enqueue_script( 'management_bonster_bundle' );

		wp_localize_script( 'management_bonster_bundle', 'wpApiSettings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
		
		$params = array(
	    	'ajax_url' => admin_url( 'admin-ajax.php' ),
			'bonster_nonce' => wp_create_nonce( 'bonster-management' ),
			'order_id' => $post_id,
		);
		wp_localize_script( 'management_bonster_bundle', 'wc_bonster_admin_meta_boxes', $params );
	}

	/**
	 * Main Bonster_Management Instance.
	 *
	 * Ensures only one instance of Bonster_Management is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return Bonster_Management - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

/**
 * Main instance of WooCommerce.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 * @since  2.1
 * @return WooCommerce
 */
function B_M() {
	return Bonster_Management::instance();
}

// Global for backwards compatibility.
$GLOBALS['Bonster_Management'] = B_M();