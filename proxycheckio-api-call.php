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
	} else {
		$pvb_transient_exploded[0] = 1;
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
		// Check if the IP we're testing is a proxy server.
		if ( 'yes' === $decoded_json->$visitor_ip->proxy ) {
			// A proxy has been detected "1", return true and don't IP cache this.
			if ( isset( $decoded_json->$visitor_ip->risk ) ) {
				if ( 1 === $asn_check ) {
					$array = array(
						'yes', // detected.
						$decoded_json->$visitor_ip->country,
						$decoded_json->$visitor_ip->continent,
						$decoded_json->$visitor_ip->risk,
						$decoded_json->$visitor_ip->type,
					);
				} else {
					$array = array(
						'yes', // detected.
						'null',
						'null',
						$decoded_json->$visitor_ip->risk,
						$decoded_json->$visitor_ip->type,
					);
				}
				return $array;
			} else {
				if ( 1 === $asn_check ) {
					$array = array(
						'yes', // detected.
						$decoded_json->$visitor_ip->country,
						$decoded_json->$visitor_ip->continent,
						'null',
						$decoded_json->$visitor_ip->type,
					);
				} else {
					$array = array(
						'yes', // detected.
						'null',
						'null',
						$decoded_json->$visitor_ip->risk,
						$decoded_json->$visitor_ip->type,
					);
				}
				return $array;
			}
		} else {
			// A proxy has not been detected but still check if country or continent is blocked, return true and don't cache this as a good IP.
			if ( 1 === $asn_check ) {
				$array = array(
					'no', // undetected.
					$decoded_json->$visitor_ip->country,
					$decoded_json->$visitor_ip->continent,
					'null',
					'null',
				);
			} else {
				$array = array(
					'no', // undetected.
					'null',
					'null',
					'null',
					'null',
				);
			}
			return $array;
		}
	} else {
		if ( 1 === $pvb_transient_exploded[0] ) {
			$array = array(
				'yes', // detected.
				'null',
				'null',
				'null',
				'null',
			);
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
}
