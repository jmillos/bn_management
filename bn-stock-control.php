<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BN_Stock_Control {

	/**
	 * The single instance of the class.
	 *
	 * @var BN_Stock_Control
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	public function __construct(){
		
	}

	/**
	 * Main BN_Stock_Control Instance.
	 *
	 * Ensures only one instance of BN_Stock_Control is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return BN_Stock_Control - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

// Global for backwards compatibility.
$GLOBALS['BN_Stock_Control'] = BN_Stock_Control::instance();

