<?php

/**
 * WC Efecty Gateway Class.
 * Built the efecty method.
 */
class WC_Efecty extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct() {
        $this->id             = 'efecty';
        $this->icon           = apply_filters( 'woocommerce_'.$this->id.'_icon', '' );
        $this->has_fields     = false;
        $this->method_title   = __( 'Efecty' );

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Get settings
        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->instructions       = $this->get_option( 'instructions', $this->description );
        $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
        $this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /* Initialise Gateway Settings Form Fields. */
    public function init_form_fields() {
    	$shipping_methods = array();

        if ( is_admin() ) {
            foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
                $shipping_methods[ $method->id ] = $method->get_method_title();
            }
        }
			
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Efecty' ),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __( 'Title' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.' ),
                'desc_tip' => true,
                'default' => __( 'Efecty' )
            ),
            'description' => array(
                'title' => __( 'Description' ),
                'type' => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.' ),
                'default' => __( 'Descriptions for Efecty.' )
            ),
			'instructions' => array(
				'title' => __( 'Instructions' ),
				'type' => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page.' ),
				'default' => __( 'Instructions for Efecty.' )
			),
			'enable_for_methods' => array(
				'title' 		=> __( 'Enable for shipping methods' ),
				'type' 			=> 'multiselect',
				'class'			=> 'chosen_select',
				'css'			=> 'width: 450px;',
				'default' 		=> '',
				'description' 	=> __( 'If Efecty is only available for certain methods, set it up here. Leave blank to enable for all methods.' ),
				'options'		=> $shipping_methods,
				'desc_tip'      => true,
			)
        );
    }


    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        // Mark as on-hold
        $order->update_status('on-hold', __( 'Your order wont be shipped until the funds have cleared in our account.', 'woocommerce' ));

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result'    => 'success',
            'redirect'  => $this->get_return_url( $order )
        );
    }


    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) );
        }
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
    }
}

