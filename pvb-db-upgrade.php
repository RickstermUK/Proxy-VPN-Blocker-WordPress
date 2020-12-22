<?php
/**
 * This file runs if older than current DB Version is detected.
 *
 * @package Proxy & VPN Blocker
 */

$database_version = get_option( 'pvb_db_version' );
// Upgrade DB to 1.1.1 if lower.
if ( $database_version < '1.1.1' ) {
	if ( get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) === '0' ) {
		update_option( 'pvb_proxycheckio_CLOUDFLARE_select_box', '' );
	}
	if ( get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) === '1' ) {
		update_option( 'pvb_proxycheckio_CLOUDFLARE_select_box', 'on' );
	}
	if ( get_option( 'pvb_proxycheckio_TAG_select_box' ) === '0' ) {
		update_option( 'pvb_proxycheckio_TAG_select_box', '' );
	}
	if ( get_option( 'pvb_proxycheckio_TAG_select_box' ) === '1' ) {
		update_option( 'pvb_proxycheckio_TAG_select_box', 'on' );
	}
	if ( get_option( 'pvb_proxycheckio_TLS_select_box' ) === '0' ) {
		update_option( 'pvb_proxycheckio_TLS_select_box', '' );
	}
	if ( get_option( 'pvb_proxycheckio_TLS_select_box' ) === '1' ) {
		update_option( 'pvb_proxycheckio_TLS_select_box', 'on' );
	}
	if ( get_option( 'pvb_proxycheckio_VPN_select_box' ) === '0' ) {
		update_option( 'pvb_proxycheckio_VPN_select_box', '' );
	}
	if ( get_option( 'pvb_proxycheckio_VPN_select_box' ) === '1' ) {
		update_option( 'pvb_proxycheckio_VPN_select_box', 'on' );
	}
	update_option( 'pvb_db_version', '1.1.1' );
}
// Upgrade DB to 2.0.1 if lower.
if ( $database_version >= '1.1.1' && $database_version < '2.0.1' ) {
	if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
		global $wpdb;
		foreach ( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) as $pvbpage ) {
			$url      = get_home_url();
			$new_path = $url . $pvbpage;

			$pvbpage_id = url_to_postid( $new_path );

			$select_pages[] = $pvbpage_id;
		}
		update_option( 'pvb_proxycheckio_blocked_select_pages_field', $select_pages );
	}
	if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) ) ) {
		global $wpdb;
		foreach ( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) as $pvbpost ) {
			$url      = get_home_url();
			$new_path = $url . $pvbpost;

			$pvbpost_id = url_to_postid( $new_path );

			$select_posts[] = $pvbpost_id;
		}
		update_option( 'pvb_proxycheckio_blocked_select_posts_field', $select_posts );
	}
	update_option( 'pvb_db_version', '2.0.1' );
}
