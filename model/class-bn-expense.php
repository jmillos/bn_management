<?php

class BN_Expense extends BN_App {
	protected $post_type = 'bn_expense';
	protected $suffix_meta = '_bn_expense_';

	protected $register_meta_fields = [
		'department' => [
			'description' => 'Department section of the expense',
			'type'        => 'integer'
		],
		'subdepartment' => [
			'description' => 'Subdepartment section of the expense',
			'type'        => 'integer'
		],
		'reference' => [
			'description' => 'Reference of the expense',
			'type'        => 'string'
		],
	];

	public function rest_api_post( $data, $post, $context ) {
		$ret = array(
			'id'				=> $data->data['id'],
			'title'    	 		=> $data->data['title']['rendered'],
			'department' 		=> $data->data['department'],
			'subdepartment' 	=> $data->data['subdepartment'],
			'date' 				=> $data->data['date'],
		);

		return $ret;
	}
}

$GLOBALS['BN_Expense'] = new BN_Expense();
