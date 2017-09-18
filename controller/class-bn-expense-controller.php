<?php

class BN_Expense_Controller extends BN_App_Controller {
	protected $post_type = 'bn_expense';

	protected $model;

	public function __construct(){
		parent::__construct();

		add_action( 'init', array($this, 'init'));
		add_filter( 'wp_insert_post_data', array($this, 'before_save_new_post'), 10, 2 );
		add_filter( 'enter_title_here', array($this, 'change_default_title'), 10, 2 );
	}

	public function init(){
		global $BN_Expense;
		$this->model = $BN_Expense;
	}

	public function after_save_post( $post_id, $post, $update ){
		global $post_type;
		if($post->post_status !== 'auto-draft')
			$this->model->update_consecutive();
	}
}

$GLOBALS['BN_Expense_Controller'] = new BN_Expense_Controller();

