<?php

function bn_post_duplicator_submitbox( $post ) {
	if( in_array($post->post_type, array('bn_purchase_order')) && $post->post_status !== 'auto-draft' ) {
		$post_type = get_post_type_object( $post->post_type );
		$nonce = wp_create_nonce( 'm4c_ajax_file_nonce' );
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {

				/**
				 * Duplicate post listener.
				 *
				 * Creates an ajax request that creates a new post, 
				 * duplicating all the data and custom meta.
				 *
				 * @since 2.12
				 */
				 
				jQuery( '.bn-duplicate-post' ).live( 'click', function( e ) {
					e.preventDefault();
					var $spinner = jQuery(this).next('.spinner');
					$spinner.css('visibility', 'visible');
				
					// Create the data to pass
					var data = {
						action: 'bn_duplicate_post',
						original_id: jQuery(this).data('postid'),
						security: jQuery(this).attr('rel')
					};
				
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.post( ajaxurl, data, function( response ) {
						var location = window.location.pathname + '?post=' + response + '&action=edit';						
						window.location.href = location;
					});
				});
			});
		</script>
		<div class="misc-pub-section misc-pub-duplicator" id="duplicator">
			<a style="<?php echo !in_array($post->post_status, array('received', 'void')) ? 'float: right;':''; ?>" class="bn-duplicate-post button" rel="<?php echo $nonce; ?>" href="#" data-postid="<?php echo $post->ID; ?>">
				<?php printf( __( 'Clonar %s' ), $post_type->labels->singular_name ); ?>
			</a>
			<span class="spinner" style="float:none;margin-top:2px;margin-left:4px;"></span>
		</div>
		<?php
	}
}
add_action( 'post_submitbox_misc_actions', 'bn_post_duplicator_submitbox' );

function bn_ajax_duplicate_post() {
	// Check the nonce
	check_ajax_referer( 'm4c_ajax_file_nonce', 'security' );
	
	// Get variables
	$original_id  = $_POST['original_id'];
	
	// Duplicate the post
	$duplicate_id = bn_duplicate_post( $original_id );

	echo $duplicate_id;

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_bn_duplicate_post', 'bn_ajax_duplicate_post' );

function bn_duplicate_post( $original_id, $args=array(), $do_action=true ) {
	// Get access to the database
	global $wpdb;
	
	// Get the post as an array
	$duplicate = get_post( $original_id, 'ARRAY_A' );

	$duplicate['post_title'] = bn_get_consecutive('#OC', $duplicate['post_type']);
	$duplicate['post_name'] = sanitize_title($duplicate['post_title']);
	$duplicate['post_status'] = 'draft';
	
	// Set the post date
	/*$timestamp = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date']) : current_time('timestamp',0);
	$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date_gmt']) : current_time('timestamp',1);
	
	if( $settings['time_offset'] ) {
		$offset = intval($settings['time_offset_seconds']+$settings['time_offset_minutes']*60+$settings['time_offset_hours']*3600+$settings['time_offset_days']*86400);
		if( $settings['time_offset_direction'] == 'newer' ) {
			$timestamp = intval($timestamp+$offset);
			$timestamp_gmt = intval($timestamp_gmt+$offset);
		} else {
			$timestamp = intval($timestamp-$offset);
			$timestamp_gmt = intval($timestamp_gmt-$offset);
		}
	}*/
	$duplicate['post_date'] = date('Y-m-d H:i:s');
	$duplicate['post_date_gmt'] = date('Y-m-d H:i:s');
	$duplicate['post_modified'] = date('Y-m-d H:i:s');
	$duplicate['post_modified_gmt'] = date('Y-m-d H:i:s');

	// Remove some of the keys
	unset( $duplicate['ID'] );
	unset( $duplicate['guid'] );
	unset( $duplicate['comment_count'] );

	// Insert the post into the database
	$duplicate_id = wp_insert_post( $duplicate );
	
	// Duplicate all the taxonomies/terms
	$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
	foreach( $taxonomies as $taxonomy ) {
		$terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
		wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
	}

  	// Duplicate all the custom fields
	$custom_fields = get_post_custom( $original_id );
	foreach ( $custom_fields as $key => $value ) {
		if (!in_array($key, array('_bn_purchase_order_received_at'))) {
			if( is_array($value) && count($value) > 0 ) {
				foreach( $value as $i=>$v ) {
					$result = $wpdb->insert( $wpdb->prefix.'postmeta', array(
						'post_id' => $duplicate_id,
						'meta_key' => $key,
						'meta_value' => $v
					));
				}
			}
		}
	}

  	// Add an action for others to do custom stuff
	/*if( $do_action ) {
		do_action( 'mtphr_post_duplicator_created', $original_id, $duplicate_id, $settings );
	}*/

	return $duplicate_id;
}

function bn_get_consecutive($prefix, $post_type){
	global $wpdb;

	$exclude_states   = get_post_stati( array(
		'show_in_admin_all_list' => false,
	) );
	
	$countPosts = intval( $wpdb->get_var( $wpdb->prepare( "
		SELECT COUNT( 1 )
		FROM $wpdb->posts
		WHERE post_type = %s
		AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
	", $post_type ) ) );

	return $prefix . ($countPosts + 1);
}