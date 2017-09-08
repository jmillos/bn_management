<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Bonster_User {
	private static $role = "mensajero";

	public function __construct(){
		add_action( 'show_user_profile', array( $this, 'create_box_content' ) );
		add_action( 'edit_user_profile', array( $this, 'create_box_content' ) );

		add_action( 'personal_options_update', array($this, 'save_extra_fields') );
		add_action( 'edit_user_profile_update', array($this, 'save_extra_fields') );
	}

	public function create_box_content( $user ) { 
		/*if ( ! current_user_can( 'mensajero' ) ) {
			return;
		}*/
		if (!in_array(self::$role, $user->roles)) {
			return;
		} ?>
		<h3>Configuración de Mensajero</h3>

		<table class="form-table">
			<tr>
				<th><label for="_bn_priority">Prioridad</label></th>
				<td>
					<input type="text" name="_bn_priority" id="_bn_priority" value="<?php echo esc_attr( get_the_author_meta( '_bn_priority', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Ingrese la prioridad del mensajero para asignarle pedidos con mas concurrencia.</span>
				</td>
			</tr>
		</table><?php
	}

	public function save_extra_fields( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
		update_usermeta( $user_id, '_bn_priority', $_POST['_bn_priority'] );
	}

	public static function getCouriers($excludeIds = array()){
		$args = array('role' => self::$role, 'fields' => array('ID', 'display_name'));
		$args['exclude'] = $excludeIds;
		$users = get_users( $args );
		foreach ($users as $key => &$user) {
			$user->priority = get_user_meta($user->ID, "_bn_priority", true);
		}

		usort($users, array('WC_Bonster_User', 'sortCouriersByPriority'));

		return $users;
	}

	public static function sortCouriersByPriority($a, $b){
		if ($a->priority == $b->priority) {
	        return 0;
	    }
	    return ($a->priority < $b->priority) ? 1 : -1;
	}
}
$GLOBALS['WC_Bonster_User'] = new WC_Bonster_User();