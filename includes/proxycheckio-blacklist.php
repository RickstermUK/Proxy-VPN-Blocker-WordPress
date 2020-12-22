<?php
/**
 * The proxycheck.io Blacklist Editor.
 *
 * @package Proxy & VPN Blocker
 */

$allowed_html = array(
	'div'    => array(
		'class' => array(),
		'id'    => array(),
		'style' => array(),
	),
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'script' => array(
		'type' => array(),
	),
	'form'   => array(
		'class'  => array(),
		'id'     => array(),
		'action' => array(),
		'method' => array(),
		'target' => array(),
	),
	'input'  => array(
		'class' => array(),
		'id'    => array(),
		'name'  => array(),
		'type'  => array(),
		'title' => array(),
		'value' => array(),
	),
	'button' => array(
		'class'   => array(),
		'id'      => array(),
		'type'    => array(),
		'onclick' => array(),
		'name'    => array(),
		'style'   => array(),
	),
	'table'  => array(
		'class' => array(),
		'id'    => array(),
	),
	'thead'  => array(),
	'tr'     => array(),
	'th'     => array(),
	'td'     => array(),
	'tbody'  => array(),
	'strong' => array(),
	'h1'     => array(),
	'h2'     => array(),
	'h3'     => array(),
	'p'      => array(),
);

$get_api_key  = get_option( 'pvb_proxycheckio_API_Key_field' );
$add_ip_nonce = wp_create_nonce( 'add-ip-blacklist' );

if ( ! empty( $get_api_key ) ) {
	// Requesting current blacklist.
	$request_args      = array(
		'timeout'     => '5',
		'blocking'    => true,
		'httpversion' => '1.1',
	);
	$request_blacklist = wp_remote_get( 'https://proxycheck.io/dashboard/blacklist/list/?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ), $request_args );
	$current_blacklist = json_decode( wp_remote_retrieve_body( $request_blacklist ) );

	if ( isset( $current_blacklist->status ) && 'denied' === $current_blacklist->status ) {
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics">' . "\n";
		$html .= '<h1>' . __( 'proxycheck.io Blacklist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
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
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_ipblacklist">' . "\n";
		$html .= '<h1>' . __( 'proxycheck.io Blacklist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '<div class="pvboptionswrap">' . "\n";
		$html .= '<h2>' . __( 'View and Edit Your proxycheck.io Blacklist', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '<p>' . __( 'The Blacklist feature allows you to specify a list of IP Addresses, IP Ranges or ASN numbers which will always be detected as Proxies or VPN\'s when checked using your API Key. You can write \'#comments\' next to your ip/range/ASN, only valid Addresses, Ranges and ASN\'s will be lifted from the box so you need not worry about how you format your entries. ' ) . '<p>' . "\n";
		$html .= '<p>' . __( 'Please note your Blacklist is checked after your Whitelist but before all other checks are performed.' ) . '<p>' . "\n";
		// Adding to blacklist.
		$html .= '<div id="add-list-wrapper">' . "\n";
		$html .= '<form id="add-list-form" action="' . admin_url( 'admin-post.php?' ) . '" method="POST" >' . "\n";
		$html .= '<input type="hidden" name="action" value="blacklist_add">' . "\n";
		$html .= '<input id="add-list-text" placeholder="IP Address, Range or ASN #optional tag" type="text" name="add" value="" required>' . "\n";
		$html .= wp_nonce_field( 'add-ip-blacklist', 'nonce_add_ip_blacklist' ) . "\n";
		$html .= '<button id="add-list-button" type="submit" name="submit" value="submit" ><span>Add to list</span></button>' . "\n";
		$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<form action="' . admin_url( 'admin-post.php' ) . '" method="POST" >' . "\n";
		$html .= wp_nonce_field( 'remove-ip-blacklist', 'nonce_remove_ip_blacklist' ) . "\n";
		$html .= '<input type="hidden" name="action" value="blacklist_remove">' . "\n";
		// Display current blacklist.
		$html .= '<table class="statsfancy">' . "\n";
		$html .= '<thead>' . "\n";
		$html .= '<tr>' . "\n";
		$html .= '<th>Blacklisted IP, Range or ASN</th>' . "\n";
		$html .= '<th>Delete Entry?</th>' . "\n";
		$html .= '</tr>' . "\n";
		$html .= '</thead>' . "\n";
		$html .= '<tbody>' . "\n";
		// phpcs:disable
		if ( isset( $current_blacklist->Raw ) ) {
			foreach ( $current_blacklist->Raw as $ip_address ) {
				if ( empty( $ip_address ) ) {
					continue;
				}
				$html .=  '<tr>' . "\n";
				$html .=  '<td>' . $ip_address . '</td>' . "\n";
				$html .=  '<td><button type="submit" class="entrydelete" name="remove" value="' . $ip_address . '">X</button></td>' . "\n";
				$html .=  '</tr>' . "\n";
			}
		} else {
			$html .=  '<tr><td> Your Blacklist is currently empty! </td></tr>';
		}
		// phpcs:enable
		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";
		$html .= '</form>' . "\n";
		$html .= '</div>';
		echo wp_kses( $html, $allowed_html );
	}
} else {
	$html  = '<div class="wrap" id="' . $this->parent->_token . '_ipblacklist">' . "\n";
	$html .= '<h1>' . __( 'proxycheck.io Blacklist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
	$html .= '<div class="pvberror">' . "\n";
	$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
	$html .= '<div class="pvberrorinside">' . "\n";
	$html .= '<h2>' . __( 'Please set a <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> API Key to see this page!', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
	$html .= '<h3>' . __( 'This page will display and allow you to edit your proxycheck.io blacklist.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '<h3>' . __( 'If you need an API Key they are free for up to 1000 daily queries, paid plans are available with more.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>';
	echo wp_kses( $html, $allowed_html );
}
