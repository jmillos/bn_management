<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// require_once(WC_Bonster_Shipping::plugin_path() . 'includes/route.php');

class WC_Bonster_Courier {
	private $id;
	private $displayName;
	private $priority;
	private $routes;
	private $maxShippingByRoute;

	public function __construct($id, $displayName, $priority = null){
		$this->id = $id;
		$this->displayName = $displayName;

		// $this->maxShippingByRoute = WC_Bonster_Shipping_Config::$scheduler['maxShippingByRoute'];

		if($priority === null)
			$this->priority = get_user_meta($id, "_bn_priority", true);
		else
			$this->priority = $priority;
	}

	public function addRoute($zoneName, $route){
		$this->routes[$zoneName] = $route;
	}

	public function getId(){
		return $this->id;
	}

	public function getDisplayName(){
		return $this->displayName;
	}

	public function getRoute($zoneName){
		if( isset($this->routes[$zoneName]) )
			return $this->routes[$zoneName];

		return false;
	}

	public function getPriority(){
		return $this->priority;
	}

	public function addShippingOrderToRoute($zoneName, $shippingOrder){
		if($route = $this->getRoute($zoneName)){
			if(!$this->hasRoutePrev($route)){
				return $route->addShippingOrder($shippingOrder);				
			}
		}

		return false;
	}

	public function hasRoutePrev($route){
		$startTime = $route->getStartTime();
		$endTime = $startTime;
		$startTime = date( "Y-m-d H:00:00", strtotime($startTime)- (WC_Bonster_Shipping_Config::$scheduler['replenishmentTime']*3600) );
		$couriers = WC_DBIntegration::getCouriersAssigned($startTime, $endTime);

		if(isset($couriers[$this->id])){
			return true;
		}

		// echo "$startTime, $endTime<pre>", print_r($couriers);die;
		return false;
	}

	/*public function isAvailableForShipping(){
		return $this->countShippingOrders() < $this->maxShippingByRoute;
	}

	public function countShippingOrders(){
		$cnt = 0;
		foreach ($this->routes as $key => $route) {
			$cnt += $route->countShippingOrders();
		}

		return $cnt;
	}*/
}