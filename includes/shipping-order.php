<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// require_once(WC_Bonster_Shipping::plugin_path() . 'includes/courier.php');

class WC_Bonster_Shipping_Order {
	private $orderId;
	private $shippingDate;
	private $courier;
	private $route;

	public function __construct($orderId, $shippingDate){
		$this->orderId = $orderId;
		$this->shippingDate = $shippingDate;

		// $this->setCourier($courier);
	}

	public function setCourier($courier){
		$this->courier = $courier;
	}

	public function setRoute($route){
		$this->route = $route;
	}

	public function getOrderId(){
		return $this->orderId;
	}

	public function getShippingDate(){
		return $this->shippingDate;
	}

	public function getShippingDateFormat(){
		return date("l, j M/Y", strtotime($this->shippingDate));
	}
}