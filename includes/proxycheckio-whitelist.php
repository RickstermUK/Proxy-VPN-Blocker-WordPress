<?php
/**
 * The proxycheck.io Whitelist Editor.
 *
 * @package Proxy & VPN Blocker
 */

$allowed_html = array(
	'div'     => array(
		'class' => array(),
		'id'    => array(),
		'style' => array(),
	),
	'a'       => array(
		'href'  => array(),
		'title' => array(),
	),
	'i'       => array(
		'class' => array(),
	),
	'script'  => array(
		'type' => array(),
	),
	'form'    => array(
		'class'  => array(),
		'id'     => array(),
		'action' => array(),
		'method' => array(),
		'target' => array(),
	),
	'input'   => array(
		'class'        => array(),
		'id'           => array(),
		'name'         => array(),
		'type'         => array(),
		'title'        => array(),
		'value'        => array(),
		'required'     => array(),
		'placeholder'  => array(),
		'autocomplete' => array(),
	),
	'button'  => array(
		'class'   => array(),
		'id'      => array(),
		'type'    => array(),
		'onclick' => array(),
		'name'    => array(),
		'style'   => array(),
		'value'   => array(),
	),
	'table'   => array(
		'class' => array(),
		'id'    => array(),
	),
	'section' => array(),
	'header'  => array(),
	'strong'  => array(),
	'h1'      => array(),
	'h2'      => array(
		'class' => array(),
	),
	'h3'      => array(),
	'p'       => array(),
);



$get_api_key  = get_option( 'pvb_proxycheckio_API_Key_field' );
$add_ip_nonce = wp_create_nonce( 'add-ip-whitelist' );

if ( ! empty( $get_api_key ) ) {
	// Requesting current blacklist.
	$request_args      = array(
		'timeout'     => '5',
		'blocking'    => true,
		'httpversion' => '1.1',
	);
	$request_whitelist = wp_remote_get( 'https://proxycheck.io/dashboard/whitelist/list/?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ), $request_args );
	$current_whitelist = json_decode( wp_remote_retrieve_body( $request_whitelist ) );

	if ( isset( $current_whitelist->status ) && 'denied' === $current_whitelist->status ) {
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_whitelist">' . "\n";
		$html .= '<h2></h2>' . "\n";
		$html .= '<h1>' . __( 'proxycheck.io Whitelist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '<div class="pvberror">' . "\n";
		$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
		$html .= '<div class="pvberrorinside">' . "\n";
		$html .= '<h2>' . __( 'You must enable Dashboard API Access within your <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> Dashboard to access this part of Proxy & VPN Blocker', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>';
		echo wp_kses( $html, $allowed_html );
	} else {
		// Build page HTML.
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_ipwhitelist">' . "\n";
		$html .= '	<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
		$html .= '	<div class="pvbareawrap">' . "\n";
		$html .= '		<h1>' . __( 'proxycheck.io Whitelist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '		<h2>' . __( 'View and Edit Your proxycheck.io Whitelist', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '		<p>' . __( 'The whitelist feature allows you to specify a list of IP Addresses, IP Ranges or ASN numbers which will not be detected as Proxy Servers or VPN\'s when checked using your API Key. You can write anything in the box below including \'#comments\' next to your ip/range/ASN, only valid Addresses, Ranges and ASN\'s will be lifted from the box so you need not worry about how you format your entries. ' ) . '<p>' . "\n";
		$html .= '		<p>' . __( 'Please note your Whitelist is always checked before any other checks are performed including before your Blacklist. ' ) . '<p>' . "\n";
		// Adding to whitelist.
		$html .= '		<div id="add-list-wrapper">' . "\n";
		$html .= '			<form id="add-list-form" action="' . admin_url( 'admin-post.php' ) . '" method="POST" >' . "\n";
		$html .= '				<input type="hidden" name="action" value="whitelist_add">' . "\n";
		$html .= '				<input id="add-list-text" autocomplete="off" placeholder="IP Address, Range or ASN #optional tag" type="text" name="add" value="" required>' . "\n";
		$html .= wp_nonce_field( 'add-ip-whitelist', 'nonce_add_ip_whitelist' ) . "\n";
		$html .= '				<button id="add-list-button" type="submit" name="submit" value="submit" ><span><i class="pvb-fa-icon-plus"></i> Add to list</span></button>' . "\n";
		$html .= '			</form>' . "\n";
		$html .= '		</div>' . "\n";
		$html .= '	</div>';
		// phpcs:disable
		if ( isset( $_GET['add-pvb-whitelist'] ) && 'yes' === $_GET['add-pvb-whitelist'] ) {
			$html .= '<div id="pvbshow" class="pvbsuccess"><i class="pvb-fa-icon-check-circle"></i> Successfully added to your proxycheck.io Whitelist</div>' . "\n";
		}
		if ( isset( $_GET['remove-pvb-whitelist'] ) && 'yes' === $_GET['remove-pvb-whitelist'] ) {
			$html .= '<div id="pvbshow" class="pvbsuccess"><i class="pvb-fa-icon-check-circle"></i> Successfully removed from your proxycheck.io Whitelist</div>' . "\n";
		}
		if ( isset( $_GET['add-pvb-whitelist'] ) && 'no' === $_GET['add-pvb-whitelist'] ) {
			$html .= '<div id="pvbshow" class="pvbfail"><i class="pvb-fa-icon-times-circle"></i> Failed adding to your proxycheck.io Whitelist</div>' . "\n";
		}
		if ( isset( $_GET['remove-pvb-whitelist'] ) && 'no' === $_GET['remove-pvb-whitelist'] ) {
			$html .= '<div id="pvbshow" class="pvbfail"><i class="pvb-fa-icon-times-circle"></i> Failed removing from your proxycheck.io Whitelist</div>' . "\n";
		}
		// phpcs:enable
		$html .= '	<form action="' . admin_url( 'admin-post.php' ) . '" method="POST" >' . "\n";
		$html .= wp_nonce_field( 'remove-ip-whitelist', 'nonce_remove_ip_whitelist' ) . "\n";
		$html .= '		<input type="hidden" name="action" value="whitelist_remove">' . "\n";
		// Display current whitelist.
		$html .= '		<div id="log_outer">' . "\n";
		$html .= '			<div class="stats-fancy">' . "\n";
		$html .= '				<section>' . "\n";
		$html .= '					<header>' . "\n";
		$html .= '						<div class="col left">Whitelisted IP, Range or ASN</div>' . "\n";
		$html .= '						<div class="col"></div>' . "\n";
		$html .= '					</header>' . "\n";
		// phpcs:disable
		if ( isset( $current_whitelist->Raw ) ) {
			foreach ( $current_whitelist->Raw as $ip_address ) {
				$html .= '			<div class="row">' . "\n";
				$html .= '				<div class="col left">' . $ip_address . '</div>' . "\n";
				$html .= '				<div class="col"><button type="submit" class="entrydelete" name="remove" value="' . $ip_address . '"><i class="pvb-fa-icon-trash"></i> Delete Entry</button></div>' . "\n";
				$html .= '			</div>' . "\n";
			}
		} else {
			$html .=  '				<div class="row">' . "\n";
			$html .=  '					<div class="col left">Your Whitelist is currently empty!</div>' . "\n";
			$html .=  '					<div class="col"></div>';
			$html .=  '				</div>' . "\n";
		}
		// phpcs:enable
		$html .= '				</section>' . "\n";
		$html .= '			</div>';
		$html .= '			<div class="fancy-bottom">';
		$html .= '			</div>';
		$html .= '		</div>';
		$html .= '	</form>' . "\n";
		echo wp_kses( $html, $allowed_html );
	}
} else {
	$html  = '<div class="wrap" id="' . $this->parent->_token . '_ipwhitelist">' . "\n";
	$html .= '<h1>' . __( 'proxycheck.io Whitelist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
	$html .= '<div class="pvberror">' . "\n";
	$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
	$html .= '<div class="pvberrorinside">' . "\n";
	$html .= '<h2>' . __( 'Please set a <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> API Key to see this page!', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
	$html .= '<h3>' . __( 'This page will display and allow you to edit your proxycheck.io whitelist.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '<h3>' . __( 'If you need an API Key, they are free for up to 1000 daily queries, paid plans are available with more.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>';
	echo wp_kses( $html, $allowed_html );
}
