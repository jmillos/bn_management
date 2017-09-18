<?php

class PT_Expense {
	/**
	 * The single instance of the class.
	 *
	 * @var PT_Expense
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	private $post_type = 'bn_expense';
	private $suffix_metadata = '_bn_expense_';

	public function __construct(){
		add_action('save_post', array($this, 'save_postdata'), 10, 3);
		add_action( 'init', array($this, 'post_type') );
		add_action( 'admin_print_scripts-post-new.php', array($this, 'admin_script'));
		add_action( 'admin_print_scripts-post.php', array($this, 'admin_script'));

		// add_filter( 'manage_edit-'.$this->post_type.'_columns', array($this, 'edit_columns') );  
		// add_action( 'manage_'.$this->post_type.'_posts_custom_column', array($this, 'custom_columns') );
		add_filter('post_row_actions', array($this, 'post_row_actions'));
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );

		add_filter( 'manage_edit-'.$this->post_type.'_columns', array($this, 'edit_columns') );
		add_action( 'manage_'.$this->post_type.'_posts_custom_column', array($this, 'custom_columns') );		
		add_action( 'pre_get_posts', array($this, 'pre_get_posts') );
		add_filter( 'manage_edit-'.$this->post_type.'_sortable_columns', array($this, 'sortable_columns') );
		add_action( 'restrict_manage_posts', array( $this, 'filter_posts_department') );
		add_action( 'restrict_manage_posts', array( $this, 'filter_posts_supplier') );
		add_filter( 'request', array( $this, 'filter_posts_query_by_department' ) );
		add_filter( 'request', array( $this, 'filter_posts_query_by_supplier' ) );

		//Ajax Response
		// add_action( 'wp_ajax_bn_search_expenses', array($this, 'search_expenses') );	
	}

	public function admin_script(){
		global $post_type;

	    if( $this->post_type == $post_type ){
		    // Bonster Assets
	    	// wp_enqueue_style( 'cdn_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css' );
		    /*wp_enqueue_script( 'management_bonster_bundle', Bonster_Management::$adminBundleJsUrl, array('wc-admin-meta-boxes'), Bonster_Management::$version, true );

		    $params = array(
	    		'ajax_url' => admin_url( 'admin-ajax.php' ),
				'bonster_nonce' => wp_create_nonce( 'bonster-management' ),
			);
		    wp_localize_script( 'management_bonster_bundle', 'wc_bonster_admin_meta_boxes', $params );*/
		}
	}

	public function add_meta_boxes(){
		global $post_type;

		if ($this->post_type == $post_type) {
			echo '
				<style>
					#minor-publishing {
						display:none;
					}
				</style>
			';

			add_meta_box( 'main_fields', __('Campos'), array($this, 'metabox_main_fields'), $this->post_type, 'normal', 'high' );
		}
	}

	public function metabox_main_fields(){
		global $post, $post_id;
		$customFields = get_post_custom( $post_id );
		unset($customFields['_edit_lock'], $customFields['_edit_last']);
		$data = array();
		foreach ($customFields as $key => $value) {
			$key = str_replace($this->suffix_metadata, '', $key);
			$data[$key] = isset($value[0]) ? $value[0]:null;
		}

		echo '<div 
				class="expenseMainMetaboxComponent"
				data="' . ( is_array($data) && count($data) > 0 ? esc_attr(json_encode($data)):'{}' ) . '"></div>';
	}

	public function post_row_actions($actions){
		global $post;
		if ($post->post_type == $this->post_type) {
			unset( $actions['view'] );
		}

        return $actions;
	}

	public function search_expenses() {
		$args = array(
			'numberposts' => 5,
			'post_type'   => $this->post_type,
			's' => sanitize_text_field( $_GET['term'])
		);
		$suppliers = get_posts( $args );
	    echo json_encode($suppliers);
	    die();
	}

	/* update meta_boxes
	================================================== */
	public function save_postdata( $post_id, $post, $update ) {
		global $post_type;

		if( $this->post_type != $post_type && !current_user_can( 'edit_post', $post_id ) ){
			return $post_id;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dataExpense'])) {
			return false;
		}

		$requestData = json_decode(stripslashes($_POST['dataExpense']), true);
		foreach ($requestData as $key => $value) {
			update_post_meta( $post_id, $this->suffix_metadata.$key, $value );
			if($key === 'supplier'){
				$val = json_decode($value, true);
				update_post_meta( $post_id, $this->suffix_metadata.'supplier_id', $val['value'] );				
			}
		}

		$j = json_decode($requestData['supplier']);
		update_post_meta( $post_id, $this->suffix_metadata.'supplier_id', $j->value );
	}

	/* Post types
	================================================== */
	public function post_type() {
		$labels = array(
			'name' => __( 'Gastos'),
			'singular_name' => __( 'Gasto' ),
			'add_new' => __('Añadir nuevo'),
			'add_new_item' => __('Añadir nuevo Gasto'),
			'edit_item' => __('Editar Gasto'),
			'new_item' => __('Nuevo Gasto'),
			'view_item' => __('Ver Gasto'),
			'search_items' => __('Buscar'),
			'not_found' =>  __('No se encontraron Gastos'),
			'not_found_in_trash' => __('No se encontraron Gastos en la Papelera'), 
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
			'show_in_nav_menus' => true,
		    // 'show_in_menu' => 'edit.php?post_type=bn_purchase_order',
			'menu_position' => 57,
			'menu_icon' => 'dashicons-carrot',
			'rewrite' => array('slug' => __( $this->post_type )),
			'supports' => array('title', 'excerpt', 'author', 'comments'), //,'editor'
            'show_in_rest'       => true,
      		'rest_base'          => 'bn_expenses',
      		'rest_controller_class' => 'WP_REST_Posts_Controller',
		  );
		  
		  register_post_type(__( $this->post_type ), $args);
	}

	/**
	 * Add bulk filter for orders by payment method
	 *
	 * @since 1.0.0
	 */
	public function filter_posts_department() {
		global $typenow;

		if ( $this->post_type === $typenow ) {

			$args = array('post_type' => 'bn_department', 'post_parent' => 0, 'numberposts' => -1);
			$items = get_posts( $args );

			wp_dropdown_pages(array(
				'depth' => 0,
				'post_type' => 'bn_department',
				'name' => '_expense_department',
				'show_option_none' => __('Todos los departamentos'),
			));

			/* ?>
			<select name="_expense_department" id="dropdown_expense_department">
				<option value="">
					<?php esc_html_e( 'Todos los departamentos' ); ?>
				</option>

				<?php foreach ( $items as $key => $item ) : ?>
				<option value="<?php echo esc_attr( $item->ID ); ?>" <?php echo esc_attr( isset( $_GET['_expense_department'] ) ? selected( $item->ID, $_GET['_expense_department'], false ) : '' ); ?>>
					<?php echo esc_html( $item->post_title ); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<?php */
		}
	}

	/**
	 * Add bulk filter for orders by payment method
	 *
	 * @since 1.0.0
	 */
	public function filter_posts_supplier() {
		global $typenow;

		if ( $this->post_type === $typenow ) {

			// get couriers with role 'mensajero'
			$args = array('post_type' => 'bn_supplier', 'numberposts' => -1);
			$items = get_posts( $args );

			?>
			<select name="_expense_supplier" id="dropdown_expense_supplier">
				<option value="">
					<?php esc_html_e( 'Todos los proveedores' ); ?>
				</option>

				<?php foreach ( $items as $key => $item ) : ?>
				<option value="<?php echo esc_attr( $item->ID ); ?>" <?php echo esc_attr( isset( $_GET['_expense_supplier'] ) ? selected( $item->ID, $_GET['_expense_supplier'], false ) : '' ); ?>>
					<?php echo esc_html( $item->post_title ); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<?php
		}
	}


	/**
	 * Process bulk filter order payment method
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_posts_query_by_department( $vars ) {
		global $typenow;

		if ( $this->post_type === $typenow && isset( $_GET['_expense_department'] ) && !empty($_GET['_expense_department']) ) {
			$filterValue = $_GET['_expense_department'];
			echo wp_get_post_parent_id($filterValue);die;

			$vars['meta_key']   = wp_get_post_parent_id($filterValue) === 0 ? '_bn_expense_department':'_bn_expense_subdepartment';
			$vars['meta_value'] = wc_clean( $filterValue );
		}

		return $vars;
	}


	/**
	 * Process bulk filter order payment method
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_posts_query_by_supplier( $vars ) {
		global $typenow;

		if ( $this->post_type === $typenow && isset( $_GET['_expense_supplier'] ) && !empty($_GET['_expense_supplier']) ) {
			$vars['meta_key']   = '_bn_expense_supplier_id';
			$vars['meta_value'] = wc_clean( $_GET['_expense_supplier'] );
		}

		return $vars;
	}

	public function edit_columns($columns){
	    $columns = array(
	        "cb" => "<input type=\"checkbox\" />",
	        "title" => __( 'Gasto' ),
	        "department" => __( 'Departamento' ),
	        "subdepartment" => __( 'Subdepartamento' ),
	        "supplier" => __( 'Proveedor' ),
	        "value" => __( 'Valor' ),
	        "date" => __( 'Fecha' ),
	    );

	    return $columns;
	}

	public function custom_columns($column){
	    global $post;

	    switch ($column){
	        case "department":
	            $id = get_post_meta( $post->ID, '_bn_expense_department', true );
	            $p = get_post($id);
	            echo $p->post_title;
	        break;

	        case "subdepartment":
	            $id = get_post_meta( $post->ID, '_bn_expense_subdepartment', true );
	            $p = get_post($id);
	            echo $p->post_title;
	        break;
	        
			case "supplier":
				$data = get_post_meta( $post->ID, '_bn_expense_supplier', true );
		        $j = $data ? json_decode($data):null;
				if( isset($j->value) ){
		            $p = get_post( (int)$j->value );
		            echo $p->post_title;
				} 
			break;

			case "value":
				$val = get_post_meta( $post->ID, '_bn_expense_value', true );
				echo $val ? '$ '.number_format($val):'$ 0';
			break;

	        /*case "date":
	            $date = get_post_meta( $post->ID, '_bn_expense_date', true );
	            if($date)
	                echo date('M-d h:ia', strtotime($date));
	        break;*/
	    }
	}

	public function sortable_columns($columns){
		$columns['department'] = 'department';
		$columns['subdepartment'] = 'subdepartment';
		$columns['supplier'] = 'supplier';

 		return $columns;
	}

	public function pre_get_posts($query){
		global $post_type;

		if ( !is_admin() || $this->post_type !== $post_type )
		    return;

		$orderby = $query->get('orderby');

		switch ($orderby) {
			case 'department':
				$query->set( 'meta_key', '_bn_expense_department' );
				$query->set( 'orderby', 'meta_value' );
				break;

			case 'subdepartment':
				$query->set( 'meta_key', '_bn_expense_subdepartment' );
				$query->set( 'orderby', 'meta_value' );
				break;
				
			case 'supplier':
				$query->set( 'meta_key', '_bn_expense_supplier' );
				$query->set( 'orderby', 'meta_value' );
				break;
		}
	}

	/**
	 * Main PT_Expense Instance.
	 *
	 * Ensures only one instance of PT_Expense is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return PT_Expense - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

PT_Expense::instance();
