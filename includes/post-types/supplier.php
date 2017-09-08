<?php

class PT_Supplier {
	/**
	 * The single instance of the class.
	 *
	 * @var PT_Supplier
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	private $post_type = 'bn_supplier';

	public function __construct(){
		// add_action('save_post', array($this, 'save_postdata'));
		add_action( 'init', array($this, 'post_type') );
		// add_filter( 'manage_edit-bn_neighborhoods_columns', array($this, 'edit_columns') );  
		// add_action( 'manage_bn_neighborhoods_posts_custom_column', array($this, 'custom_columns') );
		// add_action( 'admin_print_scripts-post-new.php', array($this, 'composition_admin_script'));
		// add_action( 'admin_print_scripts-post.php', array($this, 'composition_admin_script'));
		// add_action("manage_posts_custom_column",  array($this, 'custom_column'));

		add_filter('post_row_actions', array($this, 'post_row_actions'));
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );

		//Ajax Response
		add_action( 'wp_ajax_bn_search_suppliers', array($this, 'search_suppliers') );	
	}

	public function post_row_actions($actions){
		global $post;
		if ($post->post_type == $this->post_type) {
			unset( $actions['view'] );
		}

        return $actions;
	}

	public function add_meta_boxes(){
		global $post;
		if ($post->post_type == $this->post_type) {
			echo '
				<style>
					#minor-publishing {
						display:none;
					}
				</style>
			';
		}
	}

	public function search_suppliers() {
		$args = array(
			'numberposts' => 5,
			'post_type'   => $this->post_type,
			's' => sanitize_text_field( $_GET['term'])
		);
		$suppliers = get_posts( $args );
	    echo json_encode($suppliers);
	    die();
	}

	/* Post types
	================================================== */
	public function post_type() {
		$labels = array(
			'name' => __( 'Proveedores'),
			'singular_name' => __( 'Proveedor' ),
			'add_new' => __('Añadir nuevo'),
			'add_new_item' => __('Añadir nuevo Proveedor'),
			'edit_item' => __('Editar Proveedor'),
			'new_item' => __('Nuevo Proveedor'),
			'view_item' => __('Ver Proveedor'),
			'search_items' => __('Buscar'),
			'not_found' =>  __('No se encontraron Proveedores'),
			'not_found_in_trash' => __('No se encontraron Proveedores en la Papelera'), 
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
		    'show_in_menu' => 'edit.php?post_type=bn_purchase_order',
			'menu_position' => 100,
			// 'menu_icon' => 'dashicons-admin-multisite',
			'rewrite' => array('slug' => __( $this->post_type )),
			'supports' => array('title', 'excerpt') //,'editor'
		  );
		  
		  register_post_type(__( $this->post_type ), $args);
	}

	/**
	 * Main PT_Supplier Instance.
	 *
	 * Ensures only one instance of PT_Supplier is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return PT_Supplier - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

PT_Supplier::instance();
