<?php

require_once __DIR__ . '/supplier.php';
require_once __DIR__ . '/../utils/duplicate-post.php';

class PT_Purchase_Order {
	private $post_type = 'bn_purchase_order';
	private $optionNameConsecutive = 'bn_purchase_order_consecutive';

	/**
	 * The single instance of the class.
	 *
	 * @var PT_Purchase_Order
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	public function __construct(){
		add_action('save_post', array($this, 'save_postdata'), 10, 3 );
		add_action( 'init', array($this, 'post_type') );
		add_action( 'init', array($this, 'register_post_status'), 10 );
		add_filter( 'wp_insert_post_data', array($this, 'before_save_new_post'), 10, 2 );
		add_filter( 'enter_title_here', array($this, 'change_default_title') );

		add_filter('post_row_actions', array($this, 'remove_bulk_actions'));
		// add_action( 'transition_post_status', array($this, 'transitions_post_status'), 10, 3 );

		add_filter( 'manage_edit-'.$this->post_type.'_columns', array($this, 'edit_columns') );
		add_action( 'manage_'.$this->post_type.'_posts_custom_column', array($this, 'custom_columns') );
		add_action( 'pre_get_posts', array($this, 'pre_get_posts') );
		add_filter( 'manage_edit-'.$this->post_type.'_sortable_columns', array($this, 'set_sortable_columns') );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'admin_print_scripts-post-new.php', array($this, 'admin_script'));
		add_action( 'admin_print_scripts-post.php', array($this, 'admin_script'));
		add_action( 'admin_enqueue_scripts', array($this, 'admin_script'));		
	}

	public function before_save_new_post($cleanPost){
		global $post_type;

		if($post_type == $this->post_type && empty($cleanPost['post_title'])){
			$cleanPost['post_title'] = $this->get_consecutive();
		}

		return $cleanPost;
	}

	public function change_default_title( $title ){
		global $post_type;

	    if ( $this->post_type == $post_type ){
	        $title = $this->get_consecutive();
	    }
	    return $title;
	}

	public function remove_bulk_actions($actions){
		global $post_type;
		if ($post_type == $this->post_type) {
			unset( $actions['inline hide-if-no-js'], $actions['view'], $actions['trash'] );
		}

        return $actions;
	}

	public function get_consecutive(){
		global $wpdb;

		$exclude_states   = get_post_stati( array(
			'show_in_admin_all_list' => false,
		) );

		$countPosts = intval( $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( 1 )
			FROM $wpdb->posts
			WHERE post_type = %s
			AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
		", $this->post_type ) ) );
		/*$nextConsecutive = (int)$countPosts->publish + (int)$countPosts->future + (int)$countPosts->draft + (int)$countPosts->pending
								+ (int)$countPosts->private + (int)$countPosts->trash + (int)$countPosts->received + (int)$countPosts->void + (int)$countPosts->inherit;*/
		// echo "<pre>";var_dump($countPosts);die;

		return '#OC' . ($countPosts + 1);
	}

	public function register_post_status(){
		global $post_type;

	    if( $this->post_type == $post_type ){
			register_post_status( 'received', array(
				'label'                     => __( 'Recibida' ),
				'public'                    => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Recibida <span class="count">(%s)</span>', 'Recibidas <span class="count">(%s)</span>' )
		    ));

		    register_post_status( 'void', array(
				'label'                     => __( 'Devuelta' ),
				'public'                    => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Devuelta <span class="count">(%s)</span>', 'Devueltas <span class="count">(%s)</span>' )
		    ));
		}
	}

	public function add_meta_boxes(){
		global $post_type;

	    if( $this->post_type == $post_type ){
			remove_meta_box( 'submitdiv', $this->post_type, 'side' );

			add_meta_box( 'purchase-order-detail', __('Orden'), array($this, 'purchase_order_detail'), $this->post_type, 'normal', 'high' );
			add_meta_box( 'submitdiv', __('Acciones'), array($this, 'purchase_order_actions'), $this->post_type, 'normal', 'high' );
	    }
	}

	public function admin_script(){
		global $post_type, $post_id;

	    if( $this->post_type == $post_type ){
		    // Bonster Assets
	    	// wp_enqueue_style( 'cdn_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css' );
		    /*wp_enqueue_script( 'management_bonster_bundle', Bonster_Management::$adminBundleJsUrl, array('wc-admin-meta-boxes'), Bonster_Management::$version, true );
		    $params = array(
	    		'ajax_url' => admin_url( 'admin-ajax.php' ),
				'search_products_nonce' => wp_create_nonce( 'search-products' ),
				'bonster_nonce' => wp_create_nonce( 'bonster-management' ),
				'order_id' => $post_id,
			);
			wp_localize_script( 'management_bonster_bundle', 'wc_bonster_admin_meta_boxes', $params );*/

		    // add_meta_box( 'purchase-order-meta-boxes', __('Orden'), array($this, 'purchase_order_detail'), $this->post_type, 'normal', 'high' );
		}
	}

	public function purchase_order_detail(){
		global $post;
		// echo "<pre>", print_r($post);die;
		$purchaseOrderData = maybe_unserialize( get_post_meta( $post->ID, '_bn_purchase_order_data', true ) );
		echo '<div
				class="purchaseOrderComponent"
				data="' . ( is_array($purchaseOrderData) ? esc_attr(json_encode($purchaseOrderData)):'{}' ) . '"
				orderStatus="'.$post->post_status.'"
				isNewOrder="'.(empty($post->post_title) ? 'true':'false').'"></div>';
	}

	public function purchase_order_actions(){
		global $post;

		switch ($post->post_status) {
			case 'publish':
				$valBtn = 'Recibir todo';
				break;

			default:
				# code...
				break;
		}

		$selectStatus = $voidAction = $publishAction = '';
		if( !in_array($post->post_status, array('received', 'void')) ){
			$selectStatus = '
				<div class="misc-pub-section" style="display: flex; justify-content: space-between; align-items: center;">
					<label for="post_status">Estado:</label>
					<select name="post_status" id="post_status">
						<option ' . ($post->post_status == 'publish' ? 'selected="selected"':'') . ' value="publish">Publicada</option>
						<option ' . ($post->post_status == 'received' ? 'selected="selected"':'') . ' value="received">Recibida toda la orden</option>
						<option ' . ($post->post_status == 'draft' ? 'selected="selected"':'') . ' value="draft">Borrador</option>
					</select>
				</div>
			';

			$publishAction = '<div id="publishing-action">
								<input name="save" type="submit" class="button button-primary button-large" id="publish" value="Guardar" />
							</div>';
		}elseif($post->post_status == 'received'){
			$voidAction = '<div id="void-action">
								<input type="hidden" name="post_status" id="post_status" />
								<input name="void" type="submit" class="button button-default button-large" id="publish" value="Vaciar" onclick="document.getElementById(\'post_status\').value = \'void\'" />
							</div>';
		}

		echo '
			<div class="submitbox">
				'.do_action( 'post_submitbox_misc_actions', $post ).'
				<div class="misc-publishing-actions">
					'.$selectStatus.'
				</div>
				<div id="major-publishing-actions">
					'.$voidAction.'
					'.$publishAction.'

					<div class="clear"></div>
				</div>
			</div>
		'; // <a class="submitdelete deletion" href="'.esc_url( get_delete_post_link( $post->ID ) ).'">Mover a la papelera</a>
	}

	public function update_stock($added = true) {
		global $post;
        $purchaseOrderData = maybe_unserialize( get_post_meta( $post->ID, '_bn_purchase_order_data', true ) );
       	// echo "<pre>", print_r($purchaseOrderData);die;
       	
        foreach ($purchaseOrderData['items'] as $key => $item) {
        	$p = json_decode($item['name']);
            $product = wc_get_product( $p->value );
	        $currentStock = $product->get_stock_quantity();
	        $qtyAdded = (double) $item['qty'];
	        $newStock = $added === true ? $currentStock + $qtyAdded:$currentStock - $qtyAdded;
       		// echo "<pre>", ($product->get_stock_quantity());
	        wc_update_product_stock($product->id, $newStock);
        }	    
	}

	/* update meta_boxes
	================================================== */
	public function save_postdata( $post_id, $post, $update ) {
		// echo "<pre>", print_r($_POST);die;
		global $post, $post_type;

		if( $this->post_type != $post_type && !current_user_can( 'edit_post', $post_id ) ){
			return $post_id;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dataPurchaseOrder'])) {
			return false;
		}

		/*if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
			return $post_id;
		} */

		$requestData = json_decode(stripslashes($_POST['dataPurchaseOrder']), true);

		if(isset($requestData['supplier'])){
			$val = json_decode($requestData['supplier'], true);
			update_post_meta( $post_id, '_bn_purchase_order_supplier', $val['value'] );
		}

		if(isset($requestData['reference'])){
			update_post_meta( $post_id, '_bn_purchase_order_reference', $requestData['reference'] );
		}

		if(isset($requestData['stock_due'])){
			update_post_meta( $post_id, '_bn_purchase_order_stock_due', $requestData['stock_due']);
		}

		if(isset($requestData['total_order'])){
			update_post_meta( $post_id, '_bn_purchase_order_total_qty', $requestData['total_order']['totalQty'] );
			update_post_meta( $post_id, '_bn_purchase_order_subtotal', $requestData['total_order']['subtotal'] );
			update_post_meta( $post_id, '_bn_purchase_order_total_tax_detail', $requestData['total_order']['tax'] );
			update_post_meta( $post_id, '_bn_purchase_order_total_tax', $requestData['total_order']['totalTax'] );
			update_post_meta( $post_id, '_bn_purchase_order_total_cost', $requestData['total_order']['totalCost'] );
		}

		if( is_array($requestData) )
			update_post_meta( $post_id, '_bn_purchase_order_data', $requestData );

       	// echo "<pre>", print_r($_POST);die;
		if ( $_POST['post_status'] == 'received' ) {
			$this->update_stock();
			update_post_meta( $post_id, '_bn_purchase_order_received_at', date('Y-m-d h:i:s'));
		}

		if(isset($_POST['void']) && $post->post_status == 'received'){
			$this->update_stock(false);
		}

		// WC_Admin_Meta_Boxes::add_error( "$error JJ" );
	}

	/**
	 * Main PT_Purchase_Order Instance.
	 *
	 * Ensures only one instance of PT_Purchase_Order is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return PT_Purchase_Order - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/* Composition post type
	================================================== */
	public function post_type() {
		$labels = array(
			'name' => __( 'Ordenes de compra'),
			'singular_name' => __( 'Orden de compra' ),
			'add_new' => __('Añadir nueva', 'composition'),
			'add_new_item' => __('Añadir nueva Orden de compra'),
			'edit_item' => __('Edit Orden de compra'),
			'new_item' => __('New Orden de compra'),
			'view_item' => __('View Orden de compra'),
			'search_items' => __('Search Orden de compra Items'),
			'not_found' =>  __('No se encontraron Ordenes de compra'),
			'not_found_in_trash' => __('No se encontraron Ordenes de compra en la Papelera'),
			'parent_item_colon' => ''
		  );

		  $args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
	        'has_archive' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
		    'show_ui' => true,
		    // 'show_in_menu' => 'edit.php?post_type=shop_order',
			'menu_position' => 56,
			'menu_icon' => 'dashicons-clipboard',
			'rewrite' => array('slug' => __( $this->post_type )),
			'supports' => array('title', 'thumbnail', 'comments', 'revisions') //,'editor'
		  );

		  register_post_type(__( $this->post_type ), $args);
	}

	/* Composition edit
	================================================== */
	public function edit_columns($columns){
	    $columns = array(
	        "cb" => "<input type=\"checkbox\" />",
	        "title" => __( 'Orden #' ),
	        "supplier" => __( 'Proveedor' ),
	        "status" => __( 'Estado' ),
	        "qty" => __( 'Cantidad' ),
	        "total_cost" => __( 'Costo Total' ),
	        "stock_due" => __( 'Fecha de espera estimada' ),
	        "received_at" => __( 'Recibido en' ),
	        "modified_at" => __( 'Modificado en' ),
	    );

	    return $columns;
	}

	public function set_sortable_columns($columns){
		$columns['stock_due'] = 'stock_due';
		$columns['received_at'] = 'received_at';
		$columns['modified_at'] = 'modified_at';

 		return $columns;
	}

	public function pre_get_posts($query){
		global $post_type;

		if ( !is_admin() || $this->post_type !== $post_type )
		    return;

		$orderby = $query->get('orderby');

		switch ($orderby) {
			case 'stock_due':
				$query->set( 'meta_key', '_bn_purchase_order_stock_due' );
				$query->set( 'orderby', 'meta_value' );
				break;

			case 'received_at':
				$query->set( 'meta_key', '_bn_purchase_order_received_at' );
				$query->set( 'orderby', 'meta_value' );
				break;
				
			case 'modified_at':
				$query->set( 'orderby', 'post_modified' );
				break;
			
			default:
				$query->set( 'orderby', 'post_modified' );
				break;
		}
	}

	/* Composition custom column
	================================================== */
	public function custom_columns($column){
	    global $post, $post_type;

	    if ( !is_admin() || $this->post_type !== $post_type )
		    return;

	    switch ($column){
			case "supplier":
				$data = maybe_unserialize( get_post_meta( $post->ID, '_bn_purchase_order_data', true ) );
				if (isset($data['supplier'])) {
					$supplier = json_decode($data['supplier'], true);
					echo $supplier['text'];
				}
			break;

			case "status":
				// $status = get_post_status_object( get_post_status( $post->ID) );
				switch ($post->post_status) {
					case 'publish':
						$status = 'Publicada';
						$badget = 'success';
						break;

					case 'received':
						$status = 'Recibida';
						$badget = 'primary';
						break;

					case 'void':
						$status = 'Devuelta';
						$badget = 'danger';
						break;
					
					default:
						$status = 'Borrador';
						$badget = 'default';
						break;
				}
				echo '<span class="bn-badge badge badge-'.$badget.'">' . $status . '</span>';
			break;

			case "qty":
				$qty = get_post_meta( $post->ID, '_bn_purchase_order_total_qty', true );
				echo $qty ? $qty:0;
			break;

			case "total_cost":
				$cost = get_post_meta( $post->ID, '_bn_purchase_order_total_cost', true );
				echo $cost ? '$ '.number_format($cost):'$ 0';
			break;

			case "stock_due":
				$date = get_post_meta( $post->ID, '_bn_purchase_order_stock_due', true );
				if($date)
					echo date('M d Y', strtotime($date));
			break;

			case "received_at":
				$date = get_post_meta( $post->ID, '_bn_purchase_order_received_at', true );
				if($date)
					echo date('M d Y', strtotime($date));
			break;			

			case "modified_at":
				$date = $post->post_modified;
				if($date)
					echo date('M d Y h:i:s', strtotime($date));
			break;			
	    }
	}
}

PT_Purchase_Order::instance();
