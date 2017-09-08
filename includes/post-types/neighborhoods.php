<?php

class PT_Neighborhoods {
	/**
	 * The single instance of the class.
	 *
	 * @var PT_Neighborhoods
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	private $post_type = 'bn_neighborhoods';

	public function __construct(){
		// add_action('save_post', array($this, 'save_postdata'));
		add_action( 'init', array($this, 'post_type') );
		add_action( 'init', array($this, 'taxonomies'), 0 );
		add_filter( 'manage_edit-bn_neighborhoods_columns', array($this, 'edit_columns') );  
		add_action( 'manage_bn_neighborhoods_posts_custom_column', array($this, 'custom_columns') );
		// add_action( 'admin_print_scripts-post-new.php', array($this, 'composition_admin_script'));
		// add_action( 'admin_print_scripts-post.php', array($this, 'composition_admin_script'));
		// add_action("manage_posts_custom_column",  array($this, 'custom_column'));
	}

	/* Post types
	================================================== */
	public function post_type() {
		$labels = array(
			'name' => __( 'Barrios'),
			'singular_name' => __( 'Barrio' ),
			'add_new' => _x('Añadir nuevo', 'composition'),
			'add_new_item' => __('Añadir nuevo Barrio'),
			'edit_item' => __('Editar Barrio'),
			'new_item' => __('Nuevo Barrio'),
			'view_item' => __('Ver Barrio'),
			'search_items' => __('Buscar'),
			'not_found' =>  __('No se encontraron Barrios'),
			'not_found_in_trash' => __('No se encontraron Barrios en la Papelera'), 
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
			'map_meta_cap' => true,
		    'show_ui' => true,
			'show_in_nav_menus' => true,
		    'show_in_menu' => 'bn_config',
			'menu_position' => 58,
			'menu_icon' => 'dashicons-admin-multisite',
			'rewrite' => array('slug' => __( 'bn_neighborhoods' )),
			'supports' => array('title') //,'editor'
		  );
		  
		  register_post_type(__( $this->post_type ), $args);
	}

	/* Taxonomies
	================================================== */
	public function taxonomies(){
		// Postcodes
		register_taxonomy(
			'bn_neighborhoods_category',
			'bn_neighborhoods',
			array(
				'hierarchical' => true,
				'label' => 'Códigos Postales',
				'query_var' => true,
				'rewrite' => true
			)
		);
	    
		// Tags
		/*register_taxonomy(
			'composition_tags',
			'composition',
			array(
				'hierarchical' => false,
				'label' => 'Tags',
				'query_var' => true,
				'rewrite' => true
			)
		);*/
	}

	/* Composition edit
	================================================== */
	public function edit_columns($columns){  
	    $columns = array(  
	        "cb" => "<input type=\"checkbox\" />",  
	        "title" => __( 'Nombre' ),
	        "bn_neighborhoods_category" => __( 'Codigo Postal' ),
	        // "composition_tags" => __( 'Tags' ),
	    );   

	    return $columns;  
	}

	/* Composition custom column
	================================================== */
	public function custom_columns($column){  
	    global $post;  
        switch ($column){    
    		case "bn_neighborhoods_category":
	    		if ( ! $terms = get_the_terms( $post->ID, $column ) ) {
					echo '<span class="na">&ndash;</span>';
				} else {
					$termlist = array();
					foreach ( $terms as $term ) {
						$termlist[] = '<a href="' . admin_url( 'edit.php?' . $column . '=' . $term->slug . '&post_type=bn_neighborhoods' ) . ' ">' . $term->name . '</a>';
					}

					echo implode( ', ', $termlist );
				}
    			// echo get_the_term_list($post->ID, 'cards_msgs_category', '', ', ','');
    		break;
        }
	}

	public function custom_column($column){  
	    global $post;  
	    switch ($column){    
			case "bn_neighborhoods_category":
				echo get_the_term_list($post->ID, 'bn_neighborhoods_category', '', ', ','');
			break;
			/*case "composition_tags":
				echo get_the_term_list($post->ID, 'composition_tags', '', ', ','');
			break;*/
	    }
	}

	public static function getList(){
		$start = microtime(true);		
		$categories = get_categories( array('taxonomy' => 'bn_neighborhoods_category') );
		$cats = $posts = array();
		foreach ($categories as $key => $cat) {
			$args = array(
				'tax_query' => array(
                    array(
                        'taxonomy' => 'bn_neighborhoods_category',
                        'field' => 'term_id',
                        'terms' => $cat->term_id
                    )
                ),
				'post_type'      => 'bn_neighborhoods',
				'post_status'    => 'publish',
				// 'orderby' 	 	 => 'title',
				// 'posts_per_page' => 100
			);
			$list = get_posts( $args );
			foreach ($list as $key => $item) {
				$post = array(
					'id' => $item->ID,
					'label' => $item->post_title,
					'postcode' => $cat->cat_name,
				);
				$posts[] = $post;
			}
		}
		
		// echo "<pre>", print_r($posts);die;
		usort($posts, array('PT_Neighborhoods', 'sortPostsByTitle'));
		$time_elapsed_secs = microtime(true) - $start;
		// echo $time_elapsed_secs;

		return $posts;
	}

	public static function sortPostsByTitle($a, $b){
		if ($a['label'] == $b['label']) {
	        return 0;
	    }
	    return ($a['label'] < $b['label']) ? -1 : 1;
	}

	/**
	 * Main PT_Neighborhoods Instance.
	 *
	 * Ensures only one instance of PT_Neighborhoods is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see B_M()
	 * @return PT_Neighborhoods - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

PT_Neighborhoods::instance();
