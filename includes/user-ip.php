<?php
/**
 * Proxy & VPN Blocker User Registration and last login IP logging.
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Get a users IP and other information when they register. Save this as user meta.
 *
 * @since 1.8.3
 * @param type $user_id from user_register hook.
 */
function pvb_user_register_ip_save( $user_id ) {
	if ( 'on' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$visitor_ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} else {
		$visitor_ip_address = $_SERVER['REMOTE_ADDR'];
	}
	if ( isset( $visitor_ip_address ) ) {
		add_user_meta( $user_id, 'registration_ip', $visitor_ip_address );
	}
}
add_action( 'user_register', 'pvb_user_register_ip_save', 10, 1 );

/**
 * Get a users IP and other information when they first log in. Save this as user meta.
 * Updates this information with each log in.
 *
 * @since 1.8.3
 * @param type $user_login from wp_login hook.
 * @param type $user from wp_login hook.
 */
function pvb_user_login( $user_login, $user ) {
	if ( 'on' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$visitor_ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} else {
		$visitor_ip_address = $_SERVER['REMOTE_ADDR'];
	}
	if ( isset( $visitor_ip_address ) ) {
		$last_login_ip = '';
		if ( '' !== get_user_meta( $user->ID, 'last_login_ip', true ) ) {
			$last_login_ip = get_user_meta( $user->ID, 'last_login_ip', true );
		}
		if ( $last_login_ip !== $visitor_ip_address ) {
			update_user_meta( $user->ID, 'last_login_ip', $visitor_ip_address );
		}
	}
}
add_action( 'wp_login', 'pvb_user_login', 10, 2 );

/**
 * Add column to user table for displaying IP information
 *
 * @since 1.8.3
 * @param type $column from manage_users_columns filter.
 */
function new_modify_user_table( $column ) {
	$column['user_ip'] = 'User IP Address';
	return $column;
}
add_filter( 'manage_users_columns', 'new_modify_user_table' );

/**
 * Display and format user IP information within the column created previously.
 *
 * @since 1.8.3
 * @param type $val from manage_users_custom_column hook.
 * @param type $column_name from manage_users_columns set above.
 * @param type $user_id from manage_users_custom_column hook.
 */
function new_modify_user_table_row( $val, $column_name, $user_id ) {
	if ( 'user_ip' === $column_name ) {
		if ( '' !== get_user_meta( $user_id, 'last_login_ip', true ) ) {
			$val = '<p class="last-login-ip"><strong>Last Login IP:</strong> <a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'last_login_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'last_login_ip', true ) . '" >' . get_user_meta( $user_id, 'last_login_ip', true ) . '</a></p>';
		} else {
			$val = '<p class="last-login-ip"><strong>Last Login IP:</strong> User Hasn\'t Logged In</p>';
		}
		if ( '' !== get_user_meta( $user_id, 'signup_ip', true ) && 'none' !== get_user_meta( $user_id, 'signup_ip', true ) && '' === get_user_meta( $user_id, 'registration_ip', true ) ) {
			$val .= '<p class="pvb-registration-ip"><strong>Registration IP:</strong> <a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'signup_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'signup_ip', true ) . '">' . get_user_meta( $user_id, 'signup_ip', true ) . '</a></p>';
		} elseif ( '' !== get_user_meta( $user_id, 'registration_ip', true ) ) {
			$val .= '<p class="pvb-registration-ip"><strong>Registration IP:</strong> <a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'registration_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'registration_ip', true ) . '">' . get_user_meta( $user_id, 'registration_ip', true ) . '</a></p>';
		} else {
			$val .= '<p class="pvb-registration-ip"><strong>Registration IP:</strong> Not Recorded</p>';
		}
	}
	return $val;
}
add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );


/**
 * Show the IP on a profile to admins only
 *
 * @since 1.8.3
 * @param type $profileuser from edit_user_profile and show_user_profile hooks.
 */
function edit_user_profile( $profileuser ) {
	if ( current_user_can( 'manage_options' ) ) {
		$user_id     = $profileuser->ID;
		$pvb_ip_info = '<h3>Proxy & VPN Blocker User IP Information</h3>';
		if ( '' !== get_user_meta( $user_id, 'signup_ip', true ) && 'none' !== get_user_meta( $user_id, 'signup_ip', true ) && '' === get_user_meta( $user_id, 'registration_ip', true ) ) {
			$pvb_ip_info .= '<p><strong>Registration IP Address:</strong> <a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'signup_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'signup_ip', true ) . '">' . get_user_meta( $user_id, 'signup_ip', true ) . '</a></p>';
		} elseif ( '' !== get_user_meta( $user_id, 'registration_ip', true ) ) {
			$pvb_ip_info .= '<p><strong>Registration IP Address:</strong> <a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'registration_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'registration_ip', true ) . '">' . get_user_meta( $user_id, 'registration_ip', true ) . '</a></p>';
		} else {
			$pvb_ip_info .= '<p><strong>Registration IP Address:</strong> Not Recorded</p>';
		}
		if ( '' !== get_user_meta( $user_id, 'last_login_ip', true ) ) {
			$pvb_ip_info .= '<p><strong>Last Login IP Address:</strong> <a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'last_login_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'last_login_ip', true ) . '">' . get_user_meta( $user_id, 'last_login_ip', true ) . '</a></p>';
		} else {
			$pvb_ip_info .= '<p><strong>Last Login IP Address:</strong> User Hasn\'t Logged In</p>';
		}
	}
	return $pvb_ip_info;
}
add_action( 'edit_user_profile', 'edit_user_profile', 10, 1 );
add_action( 'show_user_profile', 'edit_user_profile', 10, 1 );
