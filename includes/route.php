<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Bonster_Route {
	private $startTime;
	private $endTime;
	private $zone;
	private $shippingOrders = array();
	private $maxShippingByRoute;

	public function __construct($zone, $startTime, $endTime){
		$this->zone = $zone;
		$this->startTime = $startTime;
		$this->endTime = $endTime;

		$this->maxShippingByRoute = WC_Bonster_Shipping_Config::$scheduler['maxShippingByRoute'];

		// $this->setZone($postcode);
	}

	public function setZone($postcode){
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * 
											FROM {$wpdb->prefix}woocommerce_shipping_zone_locations as lc
												INNER JOIN {$wpdb->prefix}woocommerce_shipping_zones as zn ON (zn.zone_id = lc.zone_id)
											WHERE lc.location_code = '$postcode'" );
		$zone = is_array($results) && count($results) > 0 ? $results[0]:null;

		$this->zone = $zone;
	}

	public function addShippingOrder($shippingOrder){
		if( count($this->shippingOrders) < $this->maxShippingByRoute ){
			$this->shippingOrders[] = $shippingOrder;
			return true;
		}

		return false;
	}

	public function countShippingOrders(){
		return count($this->shippingOrders);
	}

	public function getStartTime(){
		return $this->startTime;
	}

	public function getEndTime(){
		return $this->endTime;
	}
}