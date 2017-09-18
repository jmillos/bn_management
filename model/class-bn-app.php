<?php

class BN_App {
	public function __construct(){
        add_action( 'rest_api_init', array($this, 'register_meta_fields') );
        add_filter( 'rest_prepare_' . $this->post_type, array($this, 'rest_api_post'), 10, 3 );
        add_filter( 'rest_pre_insert_' . $this->post_type, array($this, 'rest_pre_insert'), 10, 2 );       
        add_action( 'rest_insert_' . $this->post_type, array($this, 'rest_insert_post'), 10, 3 );
    }

    public function rest_pre_insert($prepared_post, $request){

    }

    public function rest_insert_post($post, $request, $creating){
        if( $creating === true){
            $args = array(
                'ID' => $post->ID,
                'post_status' => 'publish'
            );
            wp_update_post( $args );
        }
    }

    // Register Meta fields needed for posts
    public function register_meta_fields() {
        if ( !empty($this->register_meta_fields) && isset($this->post_type) ) {
            foreach ($this->register_meta_fields as $field => $fieldOpts) {
                register_rest_field( $this->post_type, $field, array(
                    'get_callback' => array($this, 'get_meta'),
					'update_callback' => array($this, 'update_meta'),
                    'schema' => array(
                        'description' => __( isset($fieldOpts['description']) ? $fieldOpts['description'] : $field ),
                        'type'        => isset($fieldOpts['type']) ? $fieldOpts['type'] : 'string'
                    ),
                ));
            }
        }
    }

	public function get_meta($post, $field_name, $request) {
		return get_post_meta($post['id'], $this->suffix_meta . $field_name, true);
	}

	public function update_meta($value, $post, $field_name){
		return update_post_meta($post->ID, $this->suffix_meta . $field_name, strip_tags($value));
	}

    public function get_consecutive(){
        $consecutive = get_option($this->consecutive_option, 1);

        return $consecutive;
    }

    public function get_consecutive_format(){
        return $this->suffix . $this->get_consecutive();
    }

    public function update_consecutive(){
        $value = (int)$this->get_consecutive() + 1;

        return update_option($this->consecutive_option, $value);
    }

    public function getSuffix(){
        return $this->suffix;
    }
}
