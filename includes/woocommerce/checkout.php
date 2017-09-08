<?php

add_action('woocommerce_after_order_notes', 'bn_default_values_fields', 10);
add_filter('woocommerce_billing_fields', 'bn_billing_fields');
add_filter('woocommerce_shipping_fields', 'bn_shipping_fields');
add_filter('woocommerce_ship_to_different_address_checked', 'bn_ship_to_different_address_checked');
add_action('woocommerce_checkout_process', 'bn_checkout_process');
add_action('woocommerce_checkout_update_order_meta', 'bn_checkout_update_order_meta');
add_filter('woocommerce_form_field_neighborhood', 'bn_field_select_neighborhood', 99, 4);
add_filter('woocommerce_form_field_datetime', 'bn_field_select_datetime', 99, 4);

function bn_billing_fields($fields){
	// echo "<pre>", print_r($fields);die;
	$newFields = (is_array($fields)) ? $fields : array();
	unset($newFields['billing_country'], $newFields['billing_city'], $newFields['billing_company'], $newFields['billing_state'], $newFields['billing_address_1'], $newFields['billing_address_2'], $newFields['billing_postcode']);

	$newFields['billing_cellphone'] = array(
	    'type' => 'text',
	    'class' => array(''),
	    'required' => true,
	    'label' => __( 'Celular' ),
	    'placeholder' => __('Número Celular.', 'placeholder')
    );

    $newFields['billing_city'] = $fields['billing_city'];

	$newFields['billing_last_name']['clear'] = 0;
	$newFields['billing_email']['clear'] = 1;
	$newFields['billing_phone']['clear'] = 0;

	return $newFields;
}

function bn_shipping_fields($fields){
	$fieldPostcode = $fields['shipping_postcode']; //retrieve post code for change order
	unset($fields['shipping_postcode'], $fields['shipping_country'], $fields['shipping_company'], $fields['shipping_state']);
	wp_dequeue_script('wc_bonster_bundle_front');
	wp_enqueue_script( 'wc_bonster_shipping_bundle', Bonster_Management::plugin_url() . '/assets-ng/js/bundle.js', array('jquery'), Bonster_Management::$version, true );
	$params = array(
		'element_ngapp_angularjs' => 'body',
	);
	wp_localize_script( 'wc_bonster_shipping_bundle', 'wc_bonster_admin_meta_boxes', $params );

	// echo "<pre>", print_r($fields);die;

	/*** Custom Fields ***/
	$fields['shipping_neighborhood'] = array(
	    'type' => 'neighborhood',
	    'class' => array(''),
	    'required' => true,
	    'label' => __('Barrio'),
	    'placeholder' => __('Seleccione un barrio'),
    );
	$fields['shipping_postcode'] = $fieldPostcode;

	$fields['shipping_postcode']['clear'] = 0;
	$fields['shipping_city']['clear'] = 1;
	$fields['shipping_address_2']['label'] = "Detalles de la dirección";

	$fields['shipping_date'] = array(
	    'type' => 'datetime',
	    // 'class' => array(''),
	    // 'required' => true,
	    'label' => "Fecha y hora de envío",
	    // 'placeholder' => _x('', 'placeholder', 'woocommerce')
    );

	$fields['shipping_cellphone'] = array(
	    'type' => 'text',
	    'class' => array(''),
	    'required' => true,
	    'label' => __( 'Celular' ),
	    'placeholder' => __('Número Celular.', 'placeholder')
    );

	$fields['shipping_phone'] = array(
	    'type' => 'text',
	    'class' => array(''),
	    'required' => true,
	    'label' => __( 'Teléfono' ),
	    'placeholder' => __('Número de Teléfono.', 'placeholder')
    );

	return $fields;
}

function bn_field_select_neighborhood($n = null, $key = null, $args = null, $value = null) {
	// echo "<pre>"; var_dump($n); var_dump($key); var_dump($args); var_dump($value);
	$neighborhoods = PT_Neighborhoods::getList();
	// $args['label_class'] = $args['class'] = $args['input_class'] = array();
	$field = '<field-neighborhood class="form-row" bons-neighborhoods="'. ( is_array($neighborhoods) ? esc_attr(json_encode($neighborhoods)):'[]' ) .'" bons-args="'. ( is_array($args) ? esc_attr(json_encode($args)):'{}' ) .'"></field-neighborhood>';

	return $field;
}

function bn_field_select_datetime($n = null, $key = null, $args = null, $value = null) {
	// echo "<pre>"; var_dump($n); var_dump($key); var_dump($args); var_dump($value);
	$neighborhoods = PT_Neighborhoods::getList();
	// $args['label_class'] = $args['class'] = $args['input_class'] = array();
	$field = '		
		<div class="form-row form-row form-row-wide address-field validate-required">
			<label for="' . esc_attr( $key ) . '" class="' . implode( ' ', $args['label_class'] ) .'">
				'.$args['label'].' <abbr class="required" title="obligatorio">*</abbr>
			</label>
			<div class="row">
				<div class="col-md-6">
					<input type="text" class="input-text" name="'.esc_attr($key).'_day" id="'.esc_attr($key).'_day" placeholder="Día" />
				</div>
				<div class="col-md-6">
					<input type="text" class="input-text" name="'.esc_attr($key).'_time" id="'.esc_attr($key).'_time" placeholder="Hora" />
				</div>
			</div>
		</div>
		<script type="text/javascript">
			jQuery("#'.esc_attr($key).'_day").pickadate({
			  formatSubmit: "yyyy-mm-dd",
			  min: 1,
			  disable: [
			    7
			  ]
			});

			jQuery("#'.esc_attr($key).'_time").pickatime({
			  formatSubmit: "HH:i",
			  interval: 15,
			  min: [6,0],
			  max: [19,0]
			});
		</script>
	';

	return $field;
}

function bn_checkout_process() {
	// Comprobar si el campo ha sido completado, en caso contrario agregar un error.
	if (!$_POST['shipping_date_day_submit'] || !$_POST['shipping_date_time_submit']){
		wc_add_notice( __('<b>Día y Hora de envío</b> es un campo requerido.'), 'error' );
	}

	// if (!$_POST['shipping_address_2'])
		// wc_add_notice( __('Por favor describe la ubicación de envio, si es Apartamento, Unidad cerrada, Bodega, Empresa, Edificio, Casa, etc.'), 'error' );
}

function bn_default_values_fields( $checkout ) {
	// echo "<pre>", var_dump($checkout->checkout_fields['billing']['bn_billing_cellphone']);die;
	// You can use this for postcode, address, company, first name, last name and such. 
	// $fields['billing']['billing_city']['default'] = 'SomeCity';
	$checkout->checkout_fields['billing']['bn_billing_cellphone']['default'] = $checkout->get_value( 'bn_billing_cellphone' );

    // return $fields;
}

function bn_checkout_update_order_meta( $order_id, $posted ) {
	if ($_POST['shipping_date_day_submit'] && $_POST['shipping_date_time_submit']){
		$date = esc_attr($_POST['shipping_date_day_submit']) .' '.esc_attr($_POST['shipping_date_time_submit']);
		update_post_meta( $order_id, '_shipping_date', date('Y-m-d H:i:s', strtotime($date)) );
	}
	
	if ($_POST['shipping_neighborhood'])
		update_post_meta( $order_id, '_shipping_neighborhood', esc_attr($_POST['shipping_neighborhood']) );
	
	if ($_POST['billing_cellphone'])
		update_post_meta( $order_id, '_billing_cellphone', esc_attr($_POST['billing_cellphone']) );
	
	if ($_POST['shipping_cellphone'])
		update_post_meta( $order_id, '_shipping_cellphone', esc_attr($_POST['shipping_cellphone']) );
	
	if ($_POST['shipping_phone'])
		update_post_meta( $order_id, '_shipping_phone', esc_attr($_POST['shipping_phone']) );
}

function bn_ship_to_different_address_checked($var){
	$var = 1;

	return $var;
}