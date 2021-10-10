<?php
/**
 * The proxycheck.io Blacklist Editor.
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
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_blacklist">' . "\n";
		$html .= '<h2></h2>' . "\n";
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
		$html .= '	<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
		$html .= '	<div class="pvbareawrap">' . "\n";
		$html .= '		<h1>' . __( 'proxycheck.io Blacklist Editor', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '		<h2>' . __( 'View and Edit Your proxycheck.io Blacklist', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '		<p>' . __( 'The Blacklist feature allows you to specify a list of IP Addresses, IP Ranges or ASN numbers which will always be detected as Proxies or VPN\'s when checked using your API Key. You can write \'#comments\' next to your ip/range/ASN, only valid Addresses, Ranges and ASN\'s will be lifted from the box so you need not worry about how you format your entries. ' ) . '<p>' . "\n";
		$html .= '		<p>' . __( 'Please note your Blacklist is checked after your Whitelist but before all other checks are performed.' ) . '<p>' . "\n";
		// Adding to blacklist.
		$html .= '		<div id="add-list-wrapper">' . "\n";
		$html .= '			<form id="add-list-form" action="' . admin_url( 'admin-post.php?' ) . '" method="POST" >' . "\n";
		$html .= '				<input type="hidden" name="action" value="blacklist_add">' . "\n";
		$html .= '				<input id="add-list-text" autocomplete="off" placeholder="IP Address, Range or ASN #optional tag" type="text" name="add" value="" required>' . "\n";
		$html .= wp_nonce_field( 'add-ip-blacklist', 'nonce_add_ip_blacklist' ) . "\n";
		$html .= '				<button id="add-list-button" type="submit" name="submit" value="submit" ><span><i class="pvb-fa-icon-plus"></i> Add to list</span></button>' . "\n";
		$html .= '			</form>' . "\n";
		$html .= '		</div>' . "\n";
		$html .= '	</div>' . "\n";
		if ( isset( $_GET['add-pvb-blacklist'] ) && 'yes' === $_GET['add-pvb-blacklist'] ) {
			$html .= '<div id="pvbshow" class="pvbsuccess"><i class="pvb-fa-icon-check-circle"></i> Successfully added to your proxycheck.io Blacklist</div>' . "\n";
		}
		if ( isset( $_GET['remove-pvb-blacklist'] ) && 'yes' === $_GET['remove-pvb-blacklist'] ) {
			$html .= '<div id="pvbshow" class="pvbsuccess"><i class="pvb-fa-icon-check-circle"></i> Successfully removed from your proxycheck.io Blacklist</div>' . "\n";
		}
		if ( isset( $_GET['add-pvb-blacklist'] ) && 'no' === $_GET['add-pvb-blacklist'] ) {
			$html .= '<div id="pvbshow" class="pvbfail"><i class="pvb-fa-icon-times-circle"></i> Failed adding to your proxycheck.io Blacklist</div>' . "\n";
		}
		if ( isset( $_GET['remove-pvb-blacklist'] ) && 'no' === $_GET['remove-pvb-blacklist'] ) {
			$html .= '<div id="pvbshow" class="pvbfail"><i class="pvb-fa-icon-times-circle"></i> Failed removing from your proxycheck.io Blacklist</div>' . "\n";
		}
		$html .= '	<form action="' . admin_url( 'admin-post.php' ) . '" method="POST" >' . "\n";
		$html .= wp_nonce_field( 'remove-ip-blacklist', 'nonce_remove_ip_blacklist' ) . "\n";
		$html .= '		<input type="hidden" name="action" value="blacklist_remove">' . "\n";
		// Display current blacklist.
		$html .= '		<div id="log_outer">' . "\n";
		$html .= '			<div class="stats-fancy">' . "\n";
		$html .= '				<section>' . "\n";
		$html .= '					<header>' . "\n";
		$html .= '						<div class="col left">Blacklisted IP, Range or ASN</div>' . "\n";
		$html .= '						<div class="col"></div>' . "\n";
		$html .= '					</header>' . "\n";
		// phpcs:disable
		if ( isset( $current_blacklist->Raw ) ) {
			foreach ( $current_blacklist->Raw as $ip_address ) {
				if ( empty( $ip_address ) ) {
					continue;
				}
				$html .=  '			<div class="row">' . "\n";
				$html .=  '				<div class="col left">' . $ip_address . '</div>' . "\n";
				$html .=  '				<div class="col"><button type="submit" class="entrydelete" name="remove" value="' . $ip_address . '"><i class="pvb-fa-icon-trash"></i> Delete Entry</button></div>' . "\n";
				$html .=  '			</div>' . "\n";
			}
		} else {
			$html .=  '				<div class="row">' . "\n";
			$html .=  '					<div class="col left">Your Blacklist is currently empty!</div>' . "\n";
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
