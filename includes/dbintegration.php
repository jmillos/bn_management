<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( self::plugin_path() . 'includes/user.php' );
require_once(self::plugin_path() . 'includes/route.php');
require_once(self::plugin_path() . 'includes/shipping-order.php');
require_once(self::plugin_path() . 'includes/courier.php');

class WC_DBIntegration {
	public static function getCouriersAssigned($startTime, $endTime){
		global $wpdb;
		$results = $wpdb->get_results(
			"SELECT
				    $wpdb->posts.ID, $wpdb->postmeta.meta_value as sdate, mt1.meta_value as courierId, us.display_name as userName, mt2.meta_value as postcode, zn.zone_name as zoneName
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
				    1 = 1
				        AND (($wpdb->postmeta.meta_key = '_bn_shipping_date'
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

		$routes = $couriers = array();
		foreach ($results as $key => $item) {
			if( !isset($routes[$item->courierId][$item->zoneName]) ){
				$routes[$item->courierId][$item->zoneName] = new WC_Bonster_Route($item->zoneName, $startTime, $endTime);
			}
			if( !isset($couriers[$item->courierId]) ){
				$couriers[$item->courierId] = new WC_Bonster_Courier($item->courierId, $item->userName);
			}
			$couriers[$item->courierId]->addRoute($item->zoneName, $routes[$item->courierId][$item->zoneName]);
			$shippingOrder = new WC_Bonster_Shipping_Order($item->ID, $item->sdate);
			$couriers[$item->courierId]->getRoute($item->zoneName)->addShippingOrder($shippingOrder);
		}

		return $couriers;
	}

	public static function findZone($postcode){
		global $wpdb;
		$results = $wpdb->get_results( "SELECT *
											FROM {$wpdb->prefix}woocommerce_shipping_zone_locations as lc
												INNER JOIN {$wpdb->prefix}woocommerce_shipping_zones as zn ON (zn.zone_id = lc.zone_id)
											WHERE lc.location_code = '$postcode'" );
		$zone = is_array($results) && count($results) > 0 ? $results[0]:null;

		return $zone;
	}
}