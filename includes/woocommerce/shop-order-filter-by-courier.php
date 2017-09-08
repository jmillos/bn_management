<?php

// fire it up!
add_action( 'plugins_loaded', 'wc_filter_orders_by_courier' );


/** 
 * Main plugin class
 *
 * @since 1.0.0
 */
class WC_Filter_Orders_By_Courier {
	const VERSION = '1.0.0';

	/** @var WC_Filter_Orders_By_Courier single instance of this plugin */
	protected static $instance;

	/**
	 * Main plugin class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			// add bulk order filter for exported / non-exported orders
			add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_payment_method') , 5 );
			add_filter( 'request',               array( $this, 'filter_orders_by_payment_method_query' ) );		
		}
	}


	/** Plugin methods ***************************************/


	/**
	 * Add bulk filter for orders by payment method
	 *
	 * @since 1.0.0
	 */
	public function filter_orders_by_payment_method() {
		global $typenow;

		if ( 'shop_order' === $typenow ) {

			// get couriers with role 'mensajero'
			$args = array('role' => 'mensajero', 'fields' => array('ID', 'display_name'));
			$users = get_users( $args );

			?>
			<select name="_shop_order_courier" id="dropdown_shop_order_courier">
				<option value="">
					<?php esc_html_e( 'Todos los mensajeros', 'wc-filter-orders-by-payment' ); ?>
				</option>

				<?php foreach ( $users as $key => $user ) : ?>
				<option value="<?php echo esc_attr( $user->ID ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_courier'] ) ? selected( $user->ID, $_GET['_shop_order_courier'], false ) : '' ); ?>>
					<?php echo esc_html( $user->display_name ); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<?php
		}
	}


	/**
	 * Process bulk filter order payment method
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_orders_by_payment_method_query( $vars ) {
		global $typenow;

		if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_courier'] ) ) {

			$vars['meta_key']   = '_bn_shipping_courier';
			$vars['meta_value'] = wc_clean( $_GET['_shop_order_courier'] );
		}

		return $vars;
	}


	/** Helper methods ***************************************/


	/**
	 * Main WC_Filter_Orders_By_Courier Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.0.0
	 * @see WC_Filter_Orders_By_Courier()
	 * @return WC_Filter_Orders_By_Courier
 	*/
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


}


/**
 * Returns the One True Instance of WC_Filter_Orders_By_Courier
 *
 * @since 1.0.0
 * @return WC_Filter_Orders_By_Courier
 */
function wc_filter_orders_by_courier() {
    return WC_Filter_Orders_By_Courier::instance();
}