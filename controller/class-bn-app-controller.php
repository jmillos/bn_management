<?php

class BN_App_Controller {
	public function __construct(){
		add_action( 'save_post_' . $this->post_type, array($this, 'after_save_post'), 20, 3);
	}

    public function before_save_new_post($cleanPost){
        global $post_type, $post_id;

        if($post_type == $this->post_type && empty($cleanPost['post_title'])){
            $cleanPost['post_title'] = $this->model->getSuffix() . $post_id;
        }

        return $cleanPost;
    }

    public function change_default_title( $title, $post ){
        global $post_type, $post_id;

        if ( $this->post_type == $post_type ){
            $title = $this->model->getSuffix() . $post->ID;
        }
        return $title;
    }
}