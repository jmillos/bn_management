<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if WooCommerce is active.
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

final class WC_Bonster_Shipping {
	public static $loadCDN = false;
	public static $cssAssets;
	public static $jsAssets;
	public $version = "0.0.0";

	public function __construct(){
		require_once('includes/config.php');
		require_once('includes/dbintegration.php');

		add_action( 'plugins_loaded', array($this, 'plugins_loaded') );
	}

	public function plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	public static function plugin_path() {
		return plugin_dir_path(__FILE__);
	}

	public function plugins_loaded(){
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );

		add_action( 'add_meta_boxes_shop_order', array( $this, 'add_box' ) );

		//Ajax Response
		// add_action( 'wp_ajax_nopriv_wc_bonster_shipping_assign', array($this, 'wc_bonster_search_compositions') );
		add_action( 'wp_ajax_wc_bonster_shipping_assign', array($this, 'shipping_assign') );
		add_action( 'wp_ajax_wc_bonster_shipping_assign_manual', array($this, 'shipping_assign_manual') );
		add_action( 'wp_ajax_wc_bonster_delete_shipping', array($this, 'delete_shipping') );
		add_action( 'wp_ajax_wc_bonster_shipping_events', array($this, 'get_shipping_events') );
		add_action( 'wp_ajax_wc_bonster_get_couriers', array($this, 'get_couriers') );

		// add_filter('woocommerce_billing_fields', array($this, 'woocommerce_billing_fields'));
		// add_filter('woocommerce_shipping_fields', array($this, 'woocommerce_shipping_fields'));
		// add_action('woocommerce_checkout_process', array($this, 'woocommerce_checkout_process'));
		// add_filter('woocommerce_ship_to_different_address_checked', array($this, 'woocommerce_ship_to_different_address_checked'));
		// add_action('woocommerce_checkout_update_order_meta', array($this, 'woocommerce_checkout_update_order_meta'));
		// add_filter('woocommerce_form_field_neighborhood', array($this, 'field_select_neighborhood'), 99, 4);
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		if ( ! $screen = get_current_screen() ) {
			return;
		}

		switch ( $screen->id ) {
			case 'dashboard' :
				// include( 'class-wc-admin-dashboard.php' );
			break;
			case 'options-permalink' :
				// include( 'class-wc-admin-permalink-settings.php' );
			break;
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				require_once( self::plugin_path() . 'includes/user.php' );
			break;
		}
	}

	/**
	 * Add the meta box on the single order page
	 */
	public function add_box() {
		global $post_id;

		/*wp_enqueue_script( 'management_bonster_bundle', Bonster_Management::$adminBundleJsUrl, array('jquery'), Bonster_Management::$version, true );
		$params = array(
	    	'ajax_url' => admin_url( 'admin-ajax.php' ),
			'bonster_nonce' => wp_create_nonce( 'bonster-management' ),
			'element_ngapp_angularjs' => '#wc-bonster-shipping',
			'order_id' => $post_id,
		);
		wp_localize_script( 'management_bonster_bundle', 'wc_bonster_admin_meta_boxes', $params );*/

		add_meta_box( 'wc-bonster-shipping', __( 'Gestión de Envio' ), array( $this, 'create_box_content' ), 'shop_order', 'side', 'low' );
	}

	/**
	 * Create the meta box content on the single order page
	 */
	public function create_box_content() {
		global $wpdb, $post_id;
		add_thickbox();

		$shippingDate = get_post_meta($post_id, '_shipping_date', true);
		$postcode = get_post_meta($post_id, '_shipping_postcode', true);
		$zone = WC_DBIntegration::findZone($postcode);
		$shippingCurrent = array(
			'id' => (string) $post_id,
			'shippingDate' => $shippingDate,
			'zoneName' => isset($zone->zone_name) ? $zone->zone_name:null
		);

		$courierId = get_post_meta($post_id, '_bn_shipping_courier', true);
		if (is_numeric($courierId)) {
			$user = get_userdata($courierId);
			$shippingCurrent['courierId'] = $courierId;
			$shippingCurrent['courierName'] = (isset($user->data->display_name) ? $user->data->display_name:'');
		}

		$shippingConfig = WC_Bonster_Shipping_Config::$scheduler;

		?>
		<!-- <div id="wc-bonster-shipping-calendar" style="visibility: hidden; width: 920px; height: 0;"> -->
			<!-- <quick-shipping-order
				bons-shipping-data="<?php echo isset($shippingData['courierName']) ? esc_attr(json_encode($shippingData)):'false' ?>"
				bons-shipping-current="<?php echo is_array($shippingCurrent) ? esc_attr(json_encode($shippingCurrent)):'{}' ?>"
				bons-shipping-config="<?php echo is_array($shippingConfig) ? esc_attr(json_encode($shippingConfig)):'{}' ?>"></quick-shipping-order> -->
				<div class="shippingScheduleComponent"
					current="<?php echo is_array($shippingCurrent) ? esc_attr(json_encode($shippingCurrent)):'{}' ?>"
					config="<?php echo is_array($shippingConfig) ? esc_attr(json_encode($shippingConfig)):'{}' ?>"></div>
		<!-- </div> -->
		<!-- <a href="#TB_inline&width=920&height=550&inlineId=wc-bonster-shipping-calendar" class="thickbox">Asignar Mensajero</a> -->
		<?php
	}

	public function get_shipping_events(){
		global $wpdb;

		$orderId = $_GET['order_id'];

		$startTime = $_GET['start'];
		$endTime = $_GET['end'];

		// echo "<pre>", $startTime," ", $endTime;

		$results = $wpdb->get_results(
			"SELECT
				    $wpdb->posts.ID, $wpdb->postmeta.meta_value as sdate, mt1.meta_value as courierId, us.ID as userId, us.display_name as userName, mt2.meta_value as postcode, zn.zone_name as zoneName
				FROM
				    $wpdb->posts
				        INNER JOIN
				    $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
				        INNER JOIN
				    $wpdb->postmeta AS mt1 ON ($wpdb->posts.ID = mt1.post_id)
				        INNER JOIN
				    $wpdb->postmeta AS mt2 ON ($wpdb->posts.ID = mt2.post_id)
				        INNER JOIN
				    $wpdb->users AS us ON (us.ID = mt1.meta_value)
				        INNER JOIN
				    {$wpdb->prefix}woocommerce_shipping_zone_locations as lc ON (lc.location_code = mt2.meta_value)
						INNER JOIN
					{$wpdb->prefix}woocommerce_shipping_zones as zn ON (zn.zone_id = lc.zone_id)
				WHERE
			        (($wpdb->postmeta.meta_key = '_shipping_date'
			        AND CAST($wpdb->postmeta.meta_value AS DATETIME) BETWEEN '$startTime' AND '$endTime')
			        AND mt1.meta_key = '_bn_shipping_courier' AND mt2.meta_key = '_shipping_postcode')
			        AND $wpdb->posts.post_type = 'shop_order'
			        AND (($wpdb->posts.post_status = 'wc-pending'
			        OR $wpdb->posts.post_status = 'wc-processing'
			        OR $wpdb->posts.post_status = 'wc-on-hold'
			        OR $wpdb->posts.post_status = 'wc-completed'
			        OR $wpdb->posts.post_status = 'wc-cancelled'
			        OR $wpdb->posts.post_status = 'wc-refunded'
			        OR $wpdb->posts.post_status = 'wc-failed'))
				GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_date DESC
				LIMIT 0 , 100"
		, OBJECT );

		$events = array();
		foreach ($results as $key => $item) {
			$zoneSuf = substr($item->zoneName, 0, 3);
			$event = array(
				'id' => $item->ID,
				'group' => $item->courierId, // 'resourceId'
				'start' => date( "Y-m-d H:i:s", strtotime($item->sdate) ),
				'end' => date( "Y-m-d H:i:s", (strtotime($item->sdate)+(3600/WC_Bonster_Shipping_Config::$scheduler['maxShippingByRoute'])) ),
				'title' => $zoneSuf . " #" . $item->ID,
				'className' => strtolower($zoneSuf)
			);
			if($orderId == $item->ID){
				$event['canMove'] = true;
				$event['className'] = 'current-order';
				$event['color'] = 'purple';
			}
			$events[] = $event;
		}
		echo json_encode( $events );die;
		// echo "<pre>"; print_r($results);die;
	}

	public function get_couriers(){
		require_once( self::plugin_path() . 'includes/user.php' );
		$users = WC_Bonster_User::getCouriers();
		$colors = array(
			'red', 'green', 'yellow', 'orange', 'blue'
		);

		$resources = array();
		foreach ($users as $key => $item) {
			$res = array('id' => $item->ID, 'title' => $item->display_name, 'eventColor' => (isset($colors[$key]) ? $colors[$key]:$colors[0]) );
			$resources[] = $res;
		}

		echo json_encode( $resources );die;
		// echo "<pre>", print_r($users);
	}

	public function shipping_assign(){
		$start = microtime(true);

		$orderId = $_GET['order_id'];
		$postcode = get_post_meta($orderId, '_shipping_postcode', true);
		$zone = WC_DBIntegration::findZone($postcode);
		$shippingDateUser = get_post_meta($orderId, '_shipping_date_user', true);

		if($shippingDateUser){
			$shippingDate = $shippingDateUser;
			update_post_meta($orderId, '_shipping_date', $shippingDate);
		}else{
			$shippingDate = get_post_meta($orderId, '_shipping_date', true);
		}
		$startTime = date( "Y-m-d H:00:00", strtotime($shippingDate) );
		$endTime = date( "Y-m-d H:00:00", (strtotime($shippingDate)+3600) );

		$couriers = WC_DBIntegration::getCouriersAssigned($startTime, $endTime);
		usort($couriers, array($this, 'sortCouriersByPriority'));

		$wasAssigned = false;
		$eventAssigned = array();
		$excludeIds = array();
		$currentShippingOrder = new WC_Bonster_Shipping_Order($orderId, $shippingDate);
		foreach($couriers as $key => $courier){
			if($wasAssigned === false){
				if( $courier->addShippingOrderToRoute($zone->zone_name, $currentShippingOrder) ){
					$eventAssigned = array(
						'id' => (string) $currentShippingOrder->getOrderId(),
						'courierId' => $courier->getId(),
						'courierName' => $courier->getDisplayName(),
						'shippingDate' => $currentShippingOrder->getShippingDate(),
						// 'shippingDateFormat' => $currentShippingOrder->getShippingDateFormat(),
					);
					update_post_meta($orderId, '_bn_shipping_courier', $courier->getId());
					$wasAssigned = true;
				}
			}
			$excludeIds[] = $courier->getId();
		}

		if($wasAssigned === false){
			$users = WC_Bonster_User::getCouriers($excludeIds);
			if( is_array($users) && count($users) > 0 ){
				$couriers[$users[0]->ID] = new WC_Bonster_Courier($users[0]->ID, $users[0]->display_name, $users[0]->priority);
				$route = new WC_Bonster_Route($zone->zone_name, $startTime, $endTime);
				$couriers[$users[0]->ID]->addRoute($zone->zone_name, $route);
				if($couriers[$users[0]->ID]->addShippingOrderToRoute($zone->zone_name, $currentShippingOrder)){
					$eventAssigned = array(
						'id' => $currentShippingOrder->getOrderId(),
						'courierId' => $couriers[$users[0]->ID]->getId(),
						'courierName' => $couriers[$users[0]->ID]->getDisplayName(),
						'shippingDate' => $currentShippingOrder->getShippingDate(),
					);
					update_post_meta($orderId, '_bn_shipping_courier', $users[0]->ID);
					$wasAssigned = true;
				}
			}
		}
		// echo "<pre>"; print_r($couriers);

		$time_elapsed_secs = microtime(true) - $start;
		// echo "<pre>". $time_elapsed_secs; //print_r($results);

		$response = array("success" => $wasAssigned, "eventAssigned" => $eventAssigned, "timeExecution" => $time_elapsed_secs);
		// $response = array("success" => false, "eventAssigned" => array(), "timeExecution" => $time_elapsed_secs);
		echo json_encode( $response );
	    die();
	}

	public function shipping_assign_manual(){
		$event = json_decode(file_get_contents('php://input'));
		$orderId = $event->id;
		$shippingDateUser = get_post_meta($orderId, '_shipping_date_user', true);
		if(!$shippingDateUser){
			$shippingDate = get_post_meta($orderId, '_shipping_date', true);
			update_post_meta($orderId, '_shipping_date_user', $shippingDate);
		}

		update_post_meta($orderId, '_bn_shipping_courier', $event->group);
		update_post_meta($orderId, '_shipping_date', $event->start);
		/*$eventAssigned = array(
			'id' => $orderId,
			'courierId' => $event->resourceId,
			'courierName' => $event->resourceName,
			'shippingDate' => $event->start,
			'shippingDateFormat' => date("l, j M/Y H:i", strtotime($event->start)),
		);*/
		$wasAssigned = true;
		$response = array("success" => $wasAssigned, "eventAssigned" => $event);
		echo json_encode( $response );
	    die();
	}

	public function delete_shipping(){
		$orderId = $_GET['order_id'];
		$success = delete_post_meta($orderId, '_bn_shipping_courier');

		$response = array("success" => $success, "id" => $orderId);
		echo json_encode( $response );
	    die();
	}

	public function sortCouriersByPriority($a, $b){
		if ($a->getPriority() == $b->getPriority()) {
	        return 0;
	    }
	    return ($a->getPriority() < $b->getPriority()) ? 1 : -1;
	}
}
$GLOBALS['WC_Bonster_Shipping'] = new WC_Bonster_Shipping();
