<?php

class BN_Expense extends BN_App {
	protected $post_type = 'bn_expense';
	protected $suffix_meta = '_bn_expense_';

	protected $suffix = '#G';

	protected $consecutive_option = 'consecutive_expenses';

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
		'courier_id' => [
			'description' => 'Courier associated to expense',
			'type'        => 'integer'
		],
		'value' => [
			'description' => 'Value of the expense',
			'type'        => 'number'
		],
		'date' => [
			'description' => 'Date of the expense',
			'type'        => 'string',
			'format'	  => 'date-time'
		],
		'order_id' => [
			'description' => 'Shop order reference of the expense',
			'type'        => 'number'
		],
	];

    public function rest_pre_insert($prepared_post, $request){
    	return $prepared_post;
    }

	public function rest_insert_post($post, $request, $creating){
		parent::rest_insert_post($post, $request, $creating);

		if( $creating === true){
            $args = array(
                'ID' => $post->ID,
                'post_title' => $this->suffix . $post->ID,
            );
            wp_update_post( $args );
        }

		$orderId = $request->get_param('order_id');
		update_post_meta($orderId, '_bn_expense', $post->ID);
	}

	public function rest_api_post( $data, $post, $context ) {
		$ret = array(
			'id'				=> $data->data['id'],
			'title'    	 		=> $data->data['title']['rendered'],
			'department' 		=> $data->data['department'],
			'subdepartment' 	=> $data->data['subdepartment'],
			'reference' 		=> $data->data['reference'],
			'value' 			=> $data->data['value'],
			'link' 				=> get_edit_post_link($post->ID),
			'date' 				=> $data->data['date'],
		);

		return $ret;
	}
}

$GLOBALS['BN_Expense'] = new BN_Expense();
