<?php

class WC_Bonster_Shipping_Config {
	public static $scheduler = array(
		'maxShippingByRoute' => 5,
		'replenishmentTime' => 1, // in hours
		'colorCurrentEvent' => 'purple',
		'scheduleCourier' => array(
			'start' => '05:00',
			'end' => '20:00'
		),
		'scheduleStore' => array(
			'start' => '06:00',
			'end' => '19:00'
		)
	);
}