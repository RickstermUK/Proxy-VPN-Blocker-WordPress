<?php
/**
 * Proxy & VPN Blocker
 *
 * @package           Proxy & VPN Blocker
 * @author            RickstermUK
 * @copyright         2017 - 2021 Proxy & VPN Blocker
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Proxy & VPN Blocker
 * Plugin URI: https://pvb.ricksterm.net
 * description: Proxy & VPN Blocker. This plugin will prevent Proxies and VPN's accessing your site's login page or making comments on pages & posts using the Proxycheck.io API
 * Version: 1.8.4
 * Author: RickstermUK
 * Author URI: https://profiles.wordpress.org/rickstermuk
 * License: GPLv2
 * Text Domain:       proxy-vpn-blocker
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

$version     = '1.8.4';
$update_date = 'September 7th 2021';

if ( version_compare( get_option( 'proxy_vpn_blocker_version' ), $version, '<' ) ) {
	update_option( 'proxy_vpn_blocker_version', $version );
	update_option( 'proxy_vpn_blocker_last_update', $update_date );
}

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Load plugin class & function files.
require_once 'includes/class-proxy-vpn-blocker.php';
require_once 'includes/class-proxy-vpn-blocker-settings.php';
require_once 'includes/custom-form-handlers.php';
if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
	require_once 'includes/user-ip.php';
}
// Load plugin libraries.
require_once 'includes/lib/class-proxy-vpn-blocker-admin-api.php';

/**
 * Returns the main instance of Proxy_VPN_Blocker to prevent the need to use globals.
 *
 * @return object Proxy_VPN_Blocker
 */
function proxy_vpn_blocker() {
	global $version;
	$instance = Proxy_VPN_Blocker::instance( __FILE__, $version );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Proxy_VPN_Blocker_Settings::instance( $instance );
	}

	return $instance;
}

proxy_vpn_blocker();

/**
 * Display message if disablepvb.txt file exists
 */
function disable_pvb_file_exists() {
	if ( is_file( ABSPATH . 'disablepvb.txt' ) ) {
		echo '<div class="notice notice-warning">';
		echo '<p>' . esc_html_e( 'Proxy & VPN Blocker is currently not protecting your site, disablepvb.txt exists in your WordPress root directory, please delete it!', 'proxy-vpn-blocker' ) . '</p>';
		echo '</div>';
	}
}
add_action( 'admin_notices', 'disable_pvb_file_exists' );

/**
 * Display message if cloudflare detected but not enabled in Proxy & VPN Blocker
 */
function pvb_cloudflare_not_enabled() {
	if ( get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) === '' && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && get_option( 'pvb_proxycheckio_master_activation' ) === 'on' ) {
		echo '<div class="notice notice-warning pvb-cloudflare-notice is-dismissible">';
		echo '<p>' . esc_html_e( 'Proxy & VPN Blocker has detected that you are probably using Cloudflare but have not enabled the Cloudflare option in Proxy & VPN Blocker settings, please enable this or we <i>*may not</i> be able to check visitors real IP addresses!', 'proxy-vpn-blocker' ) . '</p>';
		echo '<p>' . esc_html_e( '*If your web server supports Cloudflare natively then Proxy & VPN Blocker will get the correct visitor IP anyway, but you should still enable the Cloudflare option.', 'proxy-vpn-blocker' ) . '</p>';
		echo '</div>';
	}
}
add_action( 'admin_notices', 'pvb_cloudflare_not_enabled' );

/**
 * Proxy & VPN Blocker Block/Deny to ease repetitiveness.
 */
function pvb_block_deny() {
	$proxycheck_denied = get_option( 'pvb_proxycheckio_denied_access_field' );
	if ( ! empty( get_option( 'pvb_proxycheckio_custom_blocked_page' ) ) ) {
		$redirect_to = array_values( get_option( 'pvb_proxycheckio_custom_blocked_page' ) );
		nocache_headers();
		wp_safe_redirect( $redirect_to[0] );
		exit;
	} else {
		if ( 'on' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) {
			if ( ! empty( get_option( 'pvb_proxycheckio_opt_redirect_url' ) ) ) {
				nocache_headers();
				// phpcs:ignore
				wp_redirect( get_option( 'pvb_proxycheckio_opt_redirect_url' ), 302 );
				exit;
			} else {
				define( 'DONOTCACHEPAGE', true ); // Do not cache this page.
				// phpcs:ignore
				wp_die( '<p>' . $proxycheck_denied . '</p>', $proxycheck_denied, array( 'back_link' => true ) );
			}
		} else {
			define( 'DONOTCACHEPAGE', true ); // Do not cache this page.
			// phpcs:ignore
			wp_die( '<p>' . $proxycheck_denied . '</p>', $proxycheck_denied, array( 'back_link' => true ) );
		}
	}
}

/**
 * Proxy & VPN Blocker General check for (pages, posts, login etc).
 */
function pvb_general_check() {
	// phpcs:disable
	if ( 'on' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$visitor_ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} else {
		$visitor_ip_address = $_SERVER['REMOTE_ADDR'];
	}
	// phpcs:enable
	if ( ! empty( $visitor_ip_address ) ) {
		require_once 'proxycheckio-api-call.php';
		$countries = get_option( 'pvb_proxycheckio_blocked_countries_field' );
		if ( ! empty( $countries ) && is_array( $countries ) ) {
			$perform_country_check = 1;
		} else {
			$perform_country_check = 0;
		}
		$proxycheck_answer = proxycheck_function( $visitor_ip_address, $perform_country_check );
		if ( 'yes' === $proxycheck_answer[0] ) {
			// Check if Risk Score Checking is on.
			if ( 'on' === get_option( 'pvb_proxycheckio_risk_select_box' ) ) {
				// Check if proxycheck answer array key 4 is set and is NOT type VPN or RULE.
				if ( 'VPN' !== $proxycheck_answer[4] ) {
					// Check if proxycheck answer array key 4 for risk score and compare it to the set proxy risk score.
					if ( $proxycheck_answer[3] >= get_option( 'pvb_proxycheckio_max_riskscore_proxy' ) ) {
						pvb_block_deny();
					}
				} elseif ( 'VPN' === $proxycheck_answer[4] ) {
					// Check if proxycheck answer array key 4 for risk score and compare it to the set VPN risk score.
					if ( $proxycheck_answer[3] >= get_option( 'pvb_proxycheckio_max_riskscore_vpn' ) ) {
						pvb_block_deny();
					}
				}
			} else {
				// Do this if risk score checking is off.
				pvb_block_deny();
			}
		} elseif ( 1 === $perform_country_check ) {
			if ( '' === get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) {
				// Block Countries in Country Block List. Allow all others.
				if ( 'null' !== $proxycheck_answer[1] && 'null' !== $proxycheck_answer[2] ) {
					if ( in_array( $proxycheck_answer[1], $countries, true ) || in_array( $proxycheck_answer[2], $countries, true ) ) {
						pvb_block_deny();
					} else {
						set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
					}
				}
			}
			if ( 'on' === get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) {
				// Allow Countries through if listed if this is to be treated as a whitelist. Block all other countries.
				if ( 'null' !== $proxycheck_answer[1] && 'null' !== $proxycheck_answer[2] ) {
					if ( in_array( $proxycheck_answer[1], $countries, true ) || in_array( $proxycheck_answer[2], $countries, true ) ) {
						set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
					} else {
						pvb_block_deny();
					}
				}
			}
		} else {
			// No proxy has been detected so set a transient to cache this result as known good IP.
			set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
		}
	}
}

/**
 * Proxy & VPN Blocker Standard Script
 */
function pvb_standard_script() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		// phpcs:ignore
		$request_uri = $_SERVER['REQUEST_URI'];
		if ( stripos( $request_uri, 'wp-cron.php' ) === false && stripos( $request_uri, 'admin-ajax.php' ) === false && current_user_can( 'administrator' ) === false ) {
			pvb_general_check();
		}
	}
}

/**
 * PVB on select pages integration
 */
function pvb_select_pages_integrate() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		// phpcs:ignore
		$request_uri   = $_SERVER['REQUEST_URI'];
		$blocked_pages = get_option( 'pvb_blocked_pages_array' );
		if ( stripos( $request_uri, 'wp-cron.php' ) === false && stripos( $request_uri, 'admin-ajax.php' ) === false && current_user_can( 'administrator' ) === false ) {
			if ( is_array( $blocked_pages ) ) {
				foreach ( $blocked_pages as $blocked_page ) {
					if ( stripos( $request_uri, $blocked_page ) !== false ) {
						pvb_general_check();
					}
				}
			}
		}
	}
}

/**
 * PVB on ALL pages integration.
 */
function pvb_all_pages_integration() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		// phpcs:ignore
		$request_uri   = $_SERVER['REQUEST_URI'];
		// phpcs:ignore
		$full_url    = esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		if ( stripos( $request_uri, 'wp-cron.php' ) === false && stripos( $request_uri, 'admin-ajax.php' ) === false && current_user_can( 'administrator' ) === false ) {
			$custom_block_page = get_option( 'pvb_proxycheckio_custom_blocked_page' );
			if ( ! empty( $custom_block_page ) ) {
				if ( stripos( $full_url, $custom_block_page[0] ) === false ) {
					pvb_general_check();
				}
			} else {
				pvb_general_check();
			}
		}
	}
}

/**
 * PVB on select posts integration
 */
function pvb_select_posts_integrate() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		// phpcs:ignore
		$request_uri   = $_SERVER['REQUEST_URI'];
		$blocked_posts = get_option( 'pvb_blocked_posts_array' );
		if ( stripos( $request_uri, 'wp-cron.php' ) === false && stripos( $request_uri, 'admin-ajax.php' ) === false && current_user_can( 'administrator' ) === false ) {
			if ( is_array( $blocked_posts ) ) {
				foreach ( $blocked_posts as $blocked_post ) {
					if ( stripos( $request_uri, $blocked_post ) !== false ) {
						pvb_general_check();
					}
				}
			}
		}
	}
}

/**
 * Processes page/post ID's to permalinks for use later.
 * Cannot otherwise get permalinks from page/post ID's early enough to use when we need them.
 */
function process_permalinks() {
	if ( get_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_permalinks_' ) === false ) {
		if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
			foreach ( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) as $blocked_page ) {
				$formatted_page_permalink = str_replace( get_site_url(), '', get_permalink( $blocked_page ) );
				$permalink_pages_array[]  = $formatted_page_permalink;
			}
			update_option( 'pvb_blocked_pages_array', $permalink_pages_array );
		}
		if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) ) ) {
			foreach ( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) as $blocked_post ) {
				$formatted_post_permalink = str_replace( get_site_url(), '', get_permalink( $blocked_post ) );
				$permalink_posts_array[]  = $formatted_post_permalink;
			}
			update_option( 'pvb_blocked_posts_array', $permalink_posts_array );
		}
		set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_permalinks_', time() + 1800 . '-' . 0, 1 * HOUR_IN_SECONDS );
	}
}

/**
 * Activation switch to enable or disable querying.
 */
if ( 'on' === get_option( 'pvb_proxycheckio_master_activation' ) ) {
	/**
	 * WordPress Auth protection and comments protection.
	 */
	if ( 'on' === get_option( 'pvb_protect_login_authentication' ) ) {
		add_filter( 'authenticate', 'pvb_standard_script', 1 );
		add_filter( 'login_head', 'pvb_standard_script', 1 );
	}
	add_action( 'pre_comment_on_post', 'pvb_standard_script', 1 );
	add_action( 'wp_loaded', 'process_permalinks', 1 );

	/**
	 * Enable block on specified PAGES option
	 */
	if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
		add_action( 'plugins_loaded', 'pvb_select_pages_integrate', 1 );
		if ( 'on' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
			update_option( 'pvb_proxycheckio_all_pages_activation', '' );
		}
	}

	/**
	 * Enable block on specified POSTS option
	 */
	if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) ) ) {
		add_action( 'plugins_loaded', 'pvb_select_posts_integrate', 1 );
		if ( 'on' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
			update_option( 'pvb_proxycheckio_all_pages_activation', '' );
		}
	}

	/**
	 * Enable for all pages option
	 */
	if ( 'on' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
		add_action( 'plugins_loaded', 'pvb_all_pages_integration', 1 );
	}

	/**
	 * Disable the Whitelist option if whitelist is empty.
	 */
	if ( 'on' === get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) && empty( get_option( 'pvb_proxycheckio_blocked_countries_field' ) ) ) {
		update_option( 'pvb_proxycheckio_whitelist_countries_select_box', '' );
	}

	/**
	 * Disable the Custom Block Page option if Redirection of Blocked Visitors is enabled.
	 */
	if ( 'on' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) {
		update_option( 'pvb_proxycheckio_custom_blocked_page', '' );
	}
}

/**
 * Function to upgrade database.
 */
function upgrade_pvb_db() {
	$database_version = get_option( 'pvb_db_version' );
	$current_version  = '3.0.0';
	if ( $current_version !== $database_version ) {
		require_once 'pvb-db-upgrade.php';
	}
}
add_action( 'init', 'upgrade_pvb_db' );

/**
 * Creates endpoint for month stats in admin.
 */
function endpoint_monthstat_init() {
	// route url: domain.com/wp-json/$namespace/$route.
	$namespace = 'proxy-vpn-blocker-stats/v1';
	$route     = 'month-stats';

	register_rest_route(
		$namespace,
		$route,
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'pvb_load_monthstat',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'endpoint_monthstat_init' );

/**
 * Function to process the proxycheck.io stats json response into a format that amcharts can use
 *
 * @param type $request request recieved by rest route.
 */
function pvb_load_monthstat( $request ) {
	$key = $request['key'];
	// Check if the API key is in the request and if it is the current API key, otherwise throw an error.
	if ( ! empty( $key ) && ( get_option( 'pvb_proxycheckio_API_Key_field' ) === $key ) ) {
		$request_args = array(
			'timeout'     => '10',
			'blocking'    => true,
			'httpversion' => '1.1',
		);
		// Get the months data from the proxycheck dashboard API.
		$request1      = wp_remote_get( 'https://proxycheck.io/dashboard/export/queries/?json=1&key=' . $key, $request_args );
		$api_key_stats = json_decode( wp_remote_retrieve_body( $request1 ) );
		if ( isset( $api_key_stats->status ) && 'denied' !== $api_key_stats->status ) {
			exit();
		} else {
			$response_api_month = array();
			$count_day          = 0;
			// America/Denver Time zone is important so that the time is in sync with the API.
			$date    = new DateTime( null, new DateTimeZone( 'America/Denver' ) );
			$datefix = $date->add( new DateInterval( 'P1D' ) );
			foreach ( $api_key_stats as $key => $value ) {
					$data                    = array();
					$data['days']            = $datefix->modify( '-1 day' )->format( 'M jS' );
					$data['proxies']         = $value->proxies;
					$data['vpns']            = $value->vpns;
					$data['undetected']      = $value->undetected;
					$data['refused queries'] = $value->{'refused queries'};
					array_push( $response_api_month, $data );
			}
			// Reverse the order of the array so that the current day is on the left.
			$reverse_order = array_reverse( $response_api_month );
			// Return the reversed array as REST response.
			return new WP_REST_Response( $reverse_order, 200 );
		}
	} else {
		$error = 'Incorrect or no API key provided.';
		// Return an error - Key not set or invalid.
		return new WP_REST_Response( array( 'error' => $error ), 400 );
	}
}
