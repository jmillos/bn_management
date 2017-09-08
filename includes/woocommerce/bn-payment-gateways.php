<?php


class BN_Payment_Gateway {
    /**
     * The single instance of the class.
     *
     * @var BN_Payment_Gateway
     * @since 0.0.1
     */
    protected static $_instance = null;

    public function __construct(){
        add_action( 'woocommerce_loaded', array($this, 'woocommerce_loaded') );

        add_filter( 'woocommerce_cod_process_payment_order_status', 'cod_process_payment_order_status', 10, 2 );        
        add_filter( 'woocommerce_payment_gateways', array($this, 'add_gateway') );
    }

    public function add_gateway($methods){
        $methods[] = 'WC_Bancolombia';
        $methods[] = 'WC_Citibank';
        $methods[] = 'WC_Payu';
        $methods[] = 'WC_Baloto';
        $methods[] = 'WC_Efecty';
        $methods[] = 'WC_Gane';
        $methods[] = 'WC_WesternUnion';

        return $methods;
    }

    public function woocommerce_loaded(){
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            add_action( 'admin_notices', array($this, 'wc_cpg_fallback_notice') );
            return;
        }

        require_once __DIR__ . '/wc-payment-gateways/class-wc-bancolombia.php';
        require_once __DIR__ . '/wc-payment-gateways/class-wc-citibank.php';
        require_once __DIR__ . '/wc-payment-gateways/class-wc-payu.php';
        require_once __DIR__ . '/wc-payment-gateways/class-wc-baloto.php';
        require_once __DIR__ . '/wc-payment-gateways/class-wc-efecty.php';
        require_once __DIR__ . '/wc-payment-gateways/class-wc-gane.php';
        require_once __DIR__ . '/wc-payment-gateways/class-wc-western-union.php';        
    }

    public function wc_cpg_fallback_notice(){
        echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Custom Payment Gateways depends on the last version of %s to work!', 'wcCpg' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
    }

    public function cod_process_payment_order_status($status, $order){
        return 'on-hold';
    }

    /**
     * Main BN_Payment_Gateway Instance.
     *
     * Ensures only one instance of BN_Payment_Gateway is loaded or can be loaded.
     *
     * @since 0.0.1
     * @static
     * @see B_M()
     * @return BN_Payment_Gateway - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

BN_Payment_Gateway::instance();

/* WooCommerce fallback notice. */
// function woocommerce_cpg_fallback_notice() {
//     echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Custom Payment Gateways depends on the last version of %s to work!', 'wcCpg' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
// }

// /* Load functions. */
// function custom_payment_gateway_load() {
//     if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
//         add_action( 'admin_notices', 'woocommerce_cpg_fallback_notice' );
//         return;
//     }
   
//     function wc_Custom_add_gateway( $methods ) {
//         $methods[] = 'WC_Bancolombia';
//         $methods[] = 'WC_Citibank';
//         $methods[] = 'WC_Payu';
//         $methods[] = 'WC_Baloto';
//         $methods[] = 'WC_Efecty';
//         $methods[] = 'WC_Gane';
//         $methods[] = 'WC_WesternUnion';
//         return $methods;
//     }
// 	add_filter( 'woocommerce_payment_gateways', 'wc_Custom_add_gateway' );
	
//     // Include the WooCommerce Custom Payment Gateways classes.
//     require_once 'class-wc-bancolombia.php';
//     require_once 'class-wc-citibank.php';
//     require_once 'class-wc-payu.php';
//     require_once 'class-wc-baloto.php';
//     require_once 'class-wc-efecty.php';
//     require_once 'class-wc-gane.php';
//     require_once 'class-wc-western_union.php';
// }

// function cod_process_payment_order_status($status, $order){
//     return 'on-hold';
// }
// add_filter( 'woocommerce_cod_process_payment_order_status', 'cod_process_payment_order_status', 10, 2 );
