<?php

require_once(__DIR__ . '/shop-order-filter-by-payment.php');
require_once(__DIR__ . '/shop-order-filter-by-courier.php');

add_filter( 'manage_edit-shop_order_columns', 'bn_shop_order_columns' );
add_action( 'manage_shop_order_posts_custom_column', 'bn_shop_order_custom_columns' );
add_action( 'pre_get_posts', 'bn_shop_order_pre_get_posts' );
add_filter( 'manage_edit-shop_order_sortable_columns', 'bn_shop_order_set_sortable_columns' );
add_filter( 'woocommerce_order_formatted_shipping_address', 'bn_filter_woocommerce_order_formatted_shipping_address', 10, 2 );
add_filter( 'woocommerce_shipping_address_map_url_parts', 'bn_filter_woocommerce_shipping_address_map_url_parts', 10, 2 );
add_action( 'add_meta_boxes_shop_order', 'bn_shop_order_add_box' );
add_action( 'save_post', 'bn_shop_order_save_postdata' );

function bn_shop_order_columns($columns){
	// echo "<pre>", var_dump($columns);die;
    $newColumns = (is_array($columns)) ? $columns : array();
    unset( 
    	$newColumns['customer_message'], 
    	$newColumns['order_notes'], 
    	$newColumns['order_date'],
    	$newColumns['order_total'],
    	$newColumns['order_actions']
    );

    //edit this for you column(s)
    //all of your columns will be added before the actions column
    $newColumns['bn_shipping_date'] = 'Fecha Entrega';
    // $newColumns['hour'] = 'Hora';
    $newColumns['bn_billing_cellphone'] = 'Celular';
    $newColumns['bn_shipping_courier'] = 'Mensajero';    
    //stop editing

    $newColumns['customer_message'] = $columns['customer_message'];
    $newColumns['order_notes'] = $columns['order_notes'];
    $newColumns['order_date'] = $columns['order_date'];
    $newColumns['order_total'] = $columns['order_total'];
    $newColumns['order_actions'] = $columns['order_actions'];
    return $newColumns;
}

function bn_shop_order_add_box(){
    add_meta_box( 'wc-bonster-payment-shipping', __( 'Mensajero de Pago' ), 'bn_shop_order_create_box_content', 'shop_order', 'side', 'low' );
}

function bn_shop_order_create_box_content(){
    global $post;

    $args = array(
        'role' => 'mensajero'
    );
    $users = get_users( $args );
    $selectedUser = get_post_meta($post->ID, '_bn_payment_courier', true);
    echo '<select name="payment_courier" style="width:100%;"><option></option>';
    foreach ($users as $key => $user) {
        echo '<option value="'.$user->ID.'"'. ($selectedUser == $user->ID ? ' selected="selected"':'') .'>' . $user->data->display_name . '</option>';
    }
    echo '</select>';
}

function bn_shop_order_save_postdata(){
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    global $post, $post_type;

    if( 'shop_order' != $post_type )
        return $post_id;

    if ( !current_user_can('edit_post', $post_id) )  
        return $post_id;

    update_post_meta($post->ID, '_bn_payment_courier', $_POST['payment_courier']);
}

function bn_filter_woocommerce_shipping_address_map_url_parts($address, $order){
    unset($address['address_2'], $address['state']);

    return $address;
}

// define the woocommerce_order_formatted_shipping_address callback 
function bn_filter_woocommerce_order_formatted_shipping_address( $address, $order ) {
	// echo "<pre>", var_dump($address, $order);
    // make filter magic happen here... 
    $neighborhood = get_post_meta($order->id, '_shipping_neighborhood', true);
    $address['city'] = $neighborhood . ' - ' . $address['city']; 
    return $address; 
}

function bn_shop_order_custom_columns($column){
    global $post;

    switch ($column){
        case "bn_shipping_date":
            $date = get_post_meta( $post->ID, '_shipping_date', true );
            if($date)
                echo date('M-d h:ia', strtotime($date));
        break;

        case "bn_billing_cellphone":
            $data = get_post_meta( $post->ID, '_billing_cellphone', true );
            echo $data;
        break;

        case "bn_shipping_courier":
            $userId = get_post_meta( $post->ID, '_bn_shipping_courier', true );
            $userIdPay = get_post_meta( $post->ID, '_bn_payment_courier', true );
            $userInfo = get_userdata($userId);
            $userInfoPay = get_userdata($userIdPay);
            if( isset($userInfo->display_name) )
                echo $userInfo->display_name . '<div class="payCourierComponent" data="{}"></div>';

            if( isset($userInfoPay->display_name) )
                echo '<div><small><b>Cobro:</b> '. $userInfoPay->display_name .'</small></div>';
        break;      
    }
}

function bn_shop_order_set_sortable_columns($columns){
    $columns['bn_shipping_date'] = 'bn_shipping_date';
    $columns['bn_shipping_courier'] = 'bn_shipping_courier';
    // $columns['modified_at'] = 'modified_at';

    return $columns;
}

function bn_shop_order_pre_get_posts($query){
    global $post_type;

    if ( !is_admin() || 'shop_order' !== $post_type )
        return;

    $orderby = $query->get('orderby');

    switch ($orderby) {
        case 'bn_shipping_date':
            $query->set( 'meta_key', '_shipping_date' );
            $query->set( 'orderby', 'meta_value' );
            break;

        case 'bn_shipping_courier':
            $query->set( 'meta_key', '_bn_shipping_courier' );
            $query->set( 'orderby', 'meta_value' );
            break;
        
        /*default:
            $query->set( 'meta_key', '_shipping_date' );
            $query->set( 'orderby', 'meta_value' );
            break;*/
    }
}