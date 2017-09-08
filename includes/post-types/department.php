<?php

class PT_Department {
	/**
	 * The single instance of the class.
	 *
	 * @var PT_Department
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	private $post_type = 'bn_department';

	public function __construct(){
		// add_action('save_post', array($this, 'save_postdata'));
		add_action( 'init', array($this, 'post_type') );

		// add_filter( 'manage_edit-'.$this->post_type.'_columns', array($this, 'edit_columns') );  
		// add_action( 'manage_'.$this->post_type.'_posts_custom_column', array($this, 'custom_columns') );
		add_filter('post_row_actions', array($this, 'post_row_actions'));
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );

		//Ajax Response
		add_action( 'wp_ajax_bn_search_departments', array($this, 'search_departments') );	
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
		}
	}

	public function search_departments() {
		$args = array(
			'numberposts' => 5,
			'post_type'   => $this->post_type,
			'post_parent' => isset($_GET['parent']) ? $_GET['parent']:0,
			// 'fields' => 'id=>parent',
			// 's' => sanitize_text_field( $_GET['term'])
		);
		$items = get_posts( $args );
	    echo json_encode($items);
	    die();
	}

	public function post_row_actions($actions){
		global $post;
		if ($post->post_type == $this->post_type) {
			unset( $actions['view'] );
		}

        return $actions;
	}

	/* Post types
	================================================== */
	public function post_type() {
		$labels = array(
			'name' => __( 'Departamentos'),
			'singular_name' => __( 'Departamento' ),
			'add_new' => __('Añadir nuevo'),
			'add_new_item' => __('Añadir nuevo Departamento'),
			'edit_item' => __('Editar Departamento'),
			'new_item' => __('Nuevo Departamento'),
			'view_item' => __('Ver Departamento'),
			'search_items' => __('Buscar'),
			'not_found' =>  __('No se encontraron Departamentos'),
			'not_found_in_trash' => __('No se encontraron Departamentos en la Papelera'), 
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
			'hierarchical' => true,
		    'show_ui' => true,
			'show_in_nav_menus' => true,
		    'show_in_menu' => 'bn_config',
			'menu_position' => 57,
			'menu_icon' => 'dashicons-clipboard',
			'rewrite' => array('slug' => __( $this->post_type )),
			'supports' => array('title', 'excerpt', 'page-attributes') //,'editor'
		  );
		  
		  register_post_type(__( $this->post_type ), $args);
	}

	/**
	 * Main PT_Department Instance.
	 *
	 * Ensures only one instance of PT_Department is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return PT_Department - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

PT_Department::instance();
