<?php
/**
 * A PHP Function which checks if the IP Address specified is a Proxy Server utilising the API provided by https://proxycheck.io
 *
 * @package Proxy & VPN Blocker.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A PHP Function which checks if the IP Address specified is a Proxy Server utilising the API provided by https://proxycheck.io
 * This function is covered under a MIT License.
 *
 * @param type $visitor_ip defined in proxy-vpn-blocker-function.php as $visitor_ip_address.
 * @param type $asn_check defined in proxy-vpn-blocker-function.php as $perform_country_check.
 */
function proxycheck_function( $visitor_ip, $asn_check ) {

	$pvb_transient_exploded = explode( '-', get_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip ) );
	if ( false === $pvb_transient_exploded[0] ) {
		$pvb_transient_exploded[0] = 0;
	}

	if ( time() >= $pvb_transient_exploded[0] ) {
		// Current time has surpassed the time we set for expirary if it existed already.
		// That means we need to check this IP with the API.

		// Setup the correct querying string for the transport security selected.
		if ( 'on' === get_option( 'pvb_proxycheckio_TLS_select_box' ) ) {
			$transport_type_string = 'https://';
		} else {
			$transport_type_string = 'http://';
		}

		// Applying TAG options.
		if ( empty( get_option( 'pvb_proxycheckio_Custom_TAG_field' ) ) ) {
			$post_field = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} elseif ( ! empty( get_option( 'pvb_proxycheckio_Custom_TAG_field' ) ) ) {
			$post_field = get_option( 'pvb_proxycheckio_Custom_TAG_field' );
		} else {
			$post_field = '';
		}

		// Performing the API query to proxycheck.io/v2/ using WordPress HTTP API.
		$body = array(
			'tag' => $post_field,
		);

		global $wp_version;
		$args = array(
			'body'        => $body,
			'timeout'     => '5',
			'httpversion' => '1.1',
			'blocking'    => true,
			'user-agent'  => 'PVB/' . get_option( 'proxy_vpn_blocker_version' ) . '; WordPress/' . $wp_version . '; ' . home_url(),
			'headers'     => array(),
			'cookies'     => array(),
		);

		// Get checkbox value for VPN_Option.
		if ( 'on' === get_option( 'pvb_proxycheckio_VPN_select_box' ) ) {
			$vpn_option = 1;
		} else {
			$vpn_option = 0;
		}

		// Get checkbox value for Risk_Score.
		if ( 'on' === get_option( 'pvb_proxycheckio_risk_select_box' ) ) {
			$risk_option = 1;
		} else {
			$risk_option = 0;
		}

		// Perform the query.
		$response = wp_remote_post( $transport_type_string . 'proxycheck.io/v2/' . $visitor_ip . '?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ) . '&risk=' . $risk_option . '&vpn=' . $vpn_option . '&days=' . get_option( 'pvb_proxycheckio_Days_Selector' ) . '&asn=' . $asn_check, $args );

		// Decode the JSON from proxycheck.io API.
		$decoded_json = json_decode( wp_remote_retrieve_body( $response ) );

		// Check if the JSON response is valid.
		if ( ! isset( $decoded_json->$visitor_ip ) || isset( $decoded_json->status ) && 'denied' === $decoded_json->status || 'warning' === $decoded_json->status ) {
			if ( 'on' === get_option( 'pvb_proxycheckio_Admin_Alert_Denied_Email' ) && ! get_transient( 'pvb_admin_email_denied_timeout_' . $decoded_json->status ) ) {
				// Prepare an email to sent to admin.
				$to       = get_option( 'admin_email' );
				$subject  = 'Proxy & VPN Blocker: proxycheck.io API Status: ' . $decoded_json->status . ' on ' . home_url();
				$message  = 'This is a courtesy message to tell you that Proxy & VPN Blocker on "' . home_url() . '" received the following status message from proxycheck.io when attempting to make a query to the API: ' . "\n\n";
				$message .= 'Status: ' . $decoded_json->status . "\n";
				$message .= 'Message: ' . $decoded_json->message . "\n\n";
				$message .= 'As a result, Proxy & VPN Blocker is not currently protecting your website.' . "\n\n";
				$message .= 'You can disable these emails by turning off "proxycheck.io \'denied\' status emails" in your site\'s Proxy & VPN Blocker Settings.';
				wp_mail( $to, $subject, $message );

				// Set a transient so this doesn't happen too many times.
				set_transient( 'pvb_admin_email_denied_timeout_' . $decoded_json->status, 3 * HOUR_IN_SECONDS );
			}

			// If the request to proxycheck.io was denied or malformed allow the visitor.
			if ( 'denied' === $decoded_json->status || ! isset( $decoded_json->$visitor_ip ) ) {
				// Return.
				$array = array(
					'no', // Undetected.
					'null',
					'null',
					'null',
					'null',
				);
				return $array;
			}
		}

		// Check if the IP we're testing is a proxy server or not according to proxycheck.io.
		if ( 'yes' === $decoded_json->$visitor_ip->proxy ) {
			$array = array( 'yes' );
		} else {
			$array = array( 'no' );
		}

		// Country.
		if ( isset( $decoded_json->$visitor_ip->country ) ) {
			$array[] = $decoded_json->$visitor_ip->country;
		} else {
			$array[] = 'null';
		}

		// Continent.
		if ( isset( $decoded_json->$visitor_ip->continent ) ) {
			$array[] = $decoded_json->$visitor_ip->continent;
		} else {
			$array[] = 'null';
		}

		// Risk Score.
		if ( isset( $decoded_json->$visitor_ip->risk ) ) {
			$array[] = $decoded_json->$visitor_ip->risk;
		} else {
			$array[] = 'null';
		}

		// Proxy Type.
		if ( isset( $decoded_json->$visitor_ip->type ) ) {
			$array[] = $decoded_json->$visitor_ip->type;
		} else {
			$array[] = 'null';
		}

		return $array;

	} else {
		$array = array(
			'no', // undetected.
			'null',
			'null',
			'null',
			'null',
		);

		return $array;

	}
}
