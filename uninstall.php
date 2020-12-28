<?php
/**
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 *
 * @package Proxy & VPN Blocker
 */

// If plugin is not being uninstalled, exit (do nothing).
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete Options.
$options = array(
	'proxy_vpn_blocker_version',
	'proxy_vpn_blocker_last_update',
	'pvb_proxycheckio_master_activation',
	'pvb_proxycheckio_API_Key_field',
	'pvb_proxycheckio_CLOUDFLARE_select_box',
	'pvb_proxycheckio_VPN_select_box',
	'pvb_proxycheckio_TLS_select_box',
	'pvb_proxycheckio_TAG_select_box',
	'pvb_proxycheckio_Custom_TAG_field',
	'pvb_proxycheckio_denied_access_field',
	'pvb_proxycheckio_Days_Selector',
	'pvb_proxycheckio_all_pages_activation',
	'pvb_proxycheckio_custom_blocked_page',
	'pvb_proxycheckio_blocked_select_pages_field',
	'pvb_proxycheckio_anti_clickbomb',
	'pvb_proxycheckio_blocked_countries_field',
	'pvb_proxycheckio_blocked_select_posts_field',
	'pvb_proxycheckio_good_ip_cache_time',
	'pvb_proxycheckio_whitelist_countries_select_box',
	'pvb_proxycheckio_opt_redirect_url',
	'pvb_proxycheckio_redirect_bad_visitor',
	'pvb_proxycheckio_Admin_Alert_Denied_Email',
);
foreach ( $options as $option ) {
	if ( get_option( $option ) ) {
		delete_option( $option );
	}
}
