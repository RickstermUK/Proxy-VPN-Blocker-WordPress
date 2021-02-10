<?php
/**
 * Proxy & VPN Blocker Plugin Options
 *
 * @package  Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Proxy & VPN Settings Class.
 */
class Proxy_VPN_Blocker_Settings {
	/**
	 * The single instance of proxy_vpn_blocker_Settings.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0
	 */
	public $parent = null;

	/**
	 * Prefix for Proxy & VPN Blocker Settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0
	 */
	public $settings = array();
	public function __construct( $parent ) {
		$this->parent = $parent;
		$this->base   = 'pvb_';
		// Initialise settings.
		add_action( 'init', array( $this, 'init_settings' ), 11 );
		// Register Proxy & VPN Blocker Settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Add settings page to menu.
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		// Add settings link to plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}
	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function add_menu_item() {
		$this->assets_url = esc_url( trailingslashit( plugins_url( 'proxy-vpn-blocker/assets/' ) ) );
		add_menu_page( 'Proxy & VPN Blocker', 'PVB Settings', 'manage_options', $this->parent->_token . '_settings', array( $this, 'settings_page' ), esc_url( $this->assets_url ) . 'img/pvb.svg' );
		add_submenu_page( $this->parent->_token . '_settings', 'Blacklist Editor', 'Blacklist Editor', 'manage_options', $this->parent->_token . '_blacklist', array( $this, 'ipblacklist_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Whitelist Editor', 'Whitelist Editor', 'manage_options', $this->parent->_token . '_whitelist', array( $this, 'ipwhitelist_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Statistics', 'API Key Statistics', 'manage_options', $this->parent->_token . '_statistics', array( $this, 'statistics_page' ) );
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 * @return array        Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=proxy_vpn_blocker_settings">' . __( 'Settings', 'proxy-vpn-blocker' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {
		// Generates a list of PAGES that can be used as a custom page.
		$custom_page_array = array();
		$get_pages         = get_pages( 'hide_empty=0' );
		foreach ( $get_pages as $page ) {
			$url                                = get_home_url();
			$wp_path_slug                       = $url . '/' . $page->post_name . '/';
			$custom_page_array[ $wp_path_slug ] = $page->post_title;
		}

		// Generates a list of PAGES that you can block on.
		$pages_array = array();
		$get_pages   = get_pages( 'hide_empty=0' );
		foreach ( $get_pages as $page ) {
			$pages_array[ $page->ID ] = $page->post_title;
		}

		// Generates a list of POSTS that you can block on.
		$posts_array = array();
		$post_args   = array(
			'numberposts' => -1,
			'post_status' => 'publish',
		);
		$get_posts   = get_posts( $post_args );
		foreach ( $get_posts as $post ) {
			$posts_array[ $post->ID ] = $post->post_title;
		}

		$settings['Standard']                 = array(
			'title'       => __( 'Main', 'proxy-vpn-blocker' ),
			'description' => __( 'Please configure these settings.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_master_activation',
					'label'       => __( 'Enable Querying', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\'  to enable querying the API. If set to \'off\' proxycheck.io will not be protecting this site.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'proxycheckio_API_Key_field',
					'label'       => __( 'proxycheck.io API key', 'proxy-vpn-blocker' ),
					'description' => __( 'Your proxycheck.io API Key.', 'proxy-vpn-blocker' ),
					'type'        => 'apikey',
					'default'     => '',
					'placeholder' => __( 'Get your API key at proxycheck.io', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_CLOUDFLARE_select_box',
					'label'       => __( 'Site uses Cloudflare', 'proxy-vpn-blocker' ),
					'description' => __( 'It\'s important to set this to \'on\' if you are using Cloudflare. We wont be able to check visitor IP\'s if this is not set correctly.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_TLS_select_box',
					'label'       => __( 'Use TLS', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to secure queries made to the proxycheck.io API - this may slow down API response.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_VPN_select_box',
					'label'       => __( 'Also Detect VPN\'s', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to also detect VPN\'s in addition to Proxies.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
			),
		);
		$settings['RestrictPagePost']         = array(
			'title'       => __( 'Restrict Pages/Posts', 'proxy-vpn-blocker' ),
			'description' => __( 'Here you can select specific pages & posts that you don\'t want Proxies & VPN\'s to access.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_blocked_select_pages_field',
					'label'       => __( 'Restrict on Specific Pages', 'proxy-vpn-blocker' ),
					'description' => __( 'You can block on specific pages by adding them in this list. Try typing to search...<p class="note"><i class="fas fa-exclamation-circle"></i> Selecting a page here WILL turn off \'Block on All Pages\' option.</p><p class="note"><i class="fas fa-exclamation-circle"></i> Using this with a Cache Plugin may not work unless you set your Cache Plugin to not cache these pages.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'select_pages_multi',
					'options'     => $pages_array,
					'placeholder' => __( 'Select a list of pages...', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_blocked_select_posts_field',
					'label'       => __( 'Restrict on Specific Posts', 'proxy-vpn-blocker' ),
					'description' => __( 'You can block on specific postss by adding them in this list. Try typing to search...<p class="note"><i class="fas fa-exclamation-circle"></i> Selecting a post here WILL turn off \'Block on All Pages\' option.</p><p class="note"><i class="fas fa-exclamation-circle"></i> Using this with a Cache Plugin may not work unless you set your Cache Plugin to not cache these posts.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'select_pages_multi',
					'options'     => $posts_array,
					'placeholder' => __( 'Select a list of posts...', 'proxy-vpn-blocker' ),
				),
			),
		);
		$settings['BlockedVisitorAction']     = array(
			'title'       => __( 'Block Action', 'proxy-vpn-blocker' ),
			'description' => __( 'Here you can configure the action you want Proxy & VPN Blocker to take upon positive detection of Proxy/VPN (Countries/Continents).', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_denied_access_field',
					'label'       => __( 'Access Denied Message', 'proxy-vpn-blocker' ),
					'description' => __( 'You can enter a custom Access Denied message here.', 'proxy-vpn-blocker' ),
					'type'        => 'text',
					'default'     => 'Proxy or VPN detected - Please disable to access this website!',
					'placeholder' => __( 'Custom Access Denied Message', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_custom_blocked_page',
					'label'       => __( 'Custom Blocked Page', 'proxy-vpn-blocker' ),
					'description' => __( 'You can select a page to use as the blocked page from this list. <p class="note"><i class="fas fa-exclamation-circle"></i> You cannot select a page here that already exists in your "Restrict on Specific Pages" List.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'select_page_single',
					'options'     => $custom_page_array,
					'placeholder' => __( 'Select a specific page...', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_redirect_bad_visitor',
					'label'       => __( 'Redirect to URL', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to enable redirection of detected bad visitors to a set URL in the below box <p class="note"><i class="fas fa-exclamation-circle"></i> Turning this on will disable a set custom block page above, you cannot use both.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_opt_redirect_url',
					'label'       => __( 'URL to redirect blocked visitor to.', 'proxy-vpn-blocker' ),
					'description' => __( 'You can enter a custom redirect URL here.', 'proxy-vpn-blocker' ),
					'type'        => 'text',
					'default'     => 'https://wordpress.org',
					'placeholder' => __( 'https://wordpress.org', 'proxy-vpn-blocker' ),
				),
			),
		);
		$settings['RiskScore']                = array(
			'title'       => __( 'Risk Scores', 'proxy-vpn-blocker' ),
			'description' => __( 'You can optionally opt to use IP Risk Score Checking.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_risk_select_box',
					'label'       => __( 'Risk Score Checking', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to enable the proxycheck.io Risk Score feature. <p class="note"><i class="fas fa-exclamation-circle"></i> When using this feature your proxycheck.io positive detection log may not reflect what has actually been blocked by this plugin because they would still be positively detected, but the action will be taken by Proxy & VPN Blocker based on the IP Risk Score.</p><p class="note"><i class="fas fa-exclamation-circle"></i>  IP\'s allowed through with the risk score feature are not cached as Known Good IP\'s.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_max_riskscore_proxy',
					'label'       => __( 'Risk Score - Proxies', 'proxy-vpn-blocker' ),
					'description' => __( 'If Risk Score checking is enabled, Any Proxies with a Risk Score equal to or higher than the value set here will be blocked and if the risk score is lower they will be allowed. - Default value is 33', 'proxy-vpn-blocker' ),
					'type'        => 'textslider-riskscore-proxy',
					'default'     => '33',
					'placeholder' => __( '33', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_max_riskscore_vpn',
					'label'       => __( 'Risk Score - VPN\'s', 'proxy-vpn-blocker' ),
					'description' => __( 'If detecting VPN\'s and Risk Score checking is enabled, any VPN with a Risk Score equal to or higher than the value set here will be blocked and if the risk score is lower they will be allowed. - Default value is 66', 'proxy-vpn-blocker' ),
					'type'        => 'textslider-riskscore-vpn',
					'default'     => '66',
					'placeholder' => __( '66', 'proxy-vpn-blocker' ),
				),
			),
		);
		$settings['BlockCountriesContinents'] = array(
			'title'       => __( 'Restrict Country/Continent', 'proxy-vpn-blocker' ),
			'description' => __( 'By Default this is Blacklist of Countries/Continents thet you do not want to access protected parts of this site, You can opt to make this a Country/Continent Whitelist if you only want to allow a select few countries. IP\'s detected as Proxies/VPN\'s from Whitelisted Countries will still be blocked.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_blocked_countries_field',
					'label'       => __( 'Country/Continent', 'proxy-vpn-blocker' ),
					'description' => __( 'You can block specific Countries & Continents by adding them in this list. You can opt to make this a Whitelist below and then only the selected Countries/Continents will be allowed through. <p class="note"><i class="fas fa-exclamation-circle"></i> This is not affected by IP Risk Score Checking options.</p><p class="note"><i class="fas fa-exclamation-circle"></i> IP\'s that are not detected as bad by the proxycheck.io API but are blocked due to your settings here will not show up in your detections log. If you require this information then it is recommended that you use the Rules Feature of proxycheck.io instead of this.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'select_country_multi',
					'options'     => array(
						'Africa'                           => 'Africa',
						'Antarctica'                       => 'Antarctica',
						'Asia'                             => 'Asia',
						'Europe'                           => 'Europe',
						'North America'                    => 'North America',
						'Oceania'                          => 'Oceania',
						'South America'                    => 'South America',
						'Afghanistan'                      => 'Afghanistan',
						'Albania'                          => 'Albania',
						'Algeria'                          => 'Algeria',
						'American Samoa'                   => 'American Samoa',
						'Andorra'                          => 'Andorra',
						'Angola'                           => 'Angola',
						'Anguilla'                         => 'Anguilla',
						'Antarctica'                       => 'Antarctica',
						'Antigua and Barbuda'              => 'Antigua and Barbuda',
						'Argentina'                        => 'Argentina',
						'Armenia'                          => 'Armenia',
						'Aruba'                            => 'Aruba',
						'Australia'                        => 'Australia',
						'Austria'                          => 'Austria',
						'Azerbaijan'                       => 'Azerbaijan',
						'Bahamas'                          => 'Bahamas',
						'Bahrain'                          => 'Bahrain',
						'Bangladesh'                       => 'Bangladesh',
						'Barbados'                         => 'Barbados',
						'Belarus'                          => 'Belarus',
						'Belgium'                          => 'Belgium',
						'Belize'                           => 'Belize',
						'Benin'                            => 'Benin',
						'Bermuda'                          => 'Bermuda',
						'Bhutan'                           => 'Bhutan',
						'Bolivia'                          => 'Bolivia',
						'Bonaire'                          => 'Bonaire',
						'Bosnia and Herzegovina'           => 'Bosnia and Herzegovina',
						'Botswana'                         => 'Botswana',
						'Bouvet Island'                    => 'Bouvet Island',
						'Brazil'                           => 'Brazil',
						'British Indian Ocean Territory'   => 'British Indian Ocean Territory',
						'British Virgin Islands'           => 'British Virgin Islands',
						'Brunei'                           => 'Brunei',
						'Bulgaria'                         => 'Bulgaria',
						'Burkina Faso'                     => 'Burkina Faso',
						'Burundi'                          => 'Burundi',
						'Cabo Verde'                       => 'Cabo Verde',
						'Cambodia'                         => 'Cambodia',
						'Cameroon'                         => 'Cameroon',
						'Canada'                           => 'Canada',
						'Cayman Islands'                   => 'Cayman Islands',
						'Central African Republic'         => 'Central African Republic',
						'Chad'                             => 'Chad',
						'Chile'                            => 'Chile',
						'China'                            => 'China',
						'Christmas Island'                 => 'Christmas Island',
						'Cocos [Keeling] Islands'          => 'Cocos [Keeling] Islands',
						'Colombia'                         => 'Colombia',
						'Comoros'                          => 'Comoros',
						'Congo'                            => 'Congo',
						'Cook Islands'                     => 'Cook Islands',
						'Costa Rica'                       => 'Costa Rica',
						'Croatia'                          => 'Croatia',
						'Cuba'                             => 'Cuba',
						'Curaçao'                          => 'Curaçao',
						'Cyprus'                           => 'Cyprus',
						'Czechia'                          => 'Czechia',
						'Democratic Republic of Timor-Leste' => 'Democratic Republic of Timor-Leste',
						'Denmark'                          => 'Denmark',
						'Djibouti'                         => 'Djibouti',
						'Dominica'                         => 'Dominica',
						'Dominican Republic'               => 'Dominican Republic',
						'Ecuador'                          => 'Ecuador',
						'Egypt'                            => 'Egypt',
						'El Salvador'                      => 'El Salvador',
						'Equatorial Guinea'                => 'Equatorial Guinea',
						'Eritrea'                          => 'Eritrea',
						'Estonia'                          => 'Estonia',
						'Ethiopia'                         => 'Ethiopia',
						'Falkland Islands'                 => 'Falkland Islands',
						'Faroe Islands'                    => 'Faroe Islands',
						'Federated States of Micronesia'   => 'Federated States of Micronesia',
						'Fiji'                             => 'Fiji',
						'Finland'                          => 'Finland',
						'France'                           => 'France',
						'French Guiana'                    => 'French Guiana',
						'French Polynesia'                 => 'French Polynesia',
						'French Southern Territories'      => 'French Southern Territories',
						'Gabon'                            => 'Gabon',
						'Gambia'                           => 'Gambia',
						'Georgia'                          => 'Georgia',
						'Germany'                          => 'Germany',
						'Ghana'                            => 'Ghana',
						'Gibraltar'                        => 'Gibraltar',
						'Greece'                           => 'Greece',
						'Greenland'                        => 'Greenland',
						'Grenada'                          => 'Grenada',
						'Guadeloupe'                       => 'Guadeloupe',
						'Guam'                             => 'Guam',
						'Guatemala'                        => 'Guatemala',
						'Guernsey'                         => 'Guernsey',
						'Guinea'                           => 'Guinea',
						'Guinea-Bissau'                    => 'Guinea-Bissau',
						'Guyana'                           => 'Guyana',
						'Haiti'                            => 'Haiti',
						'Hashemite Kingdom of Jordan'      => 'Hashemite Kingdom of Jordan',
						'Heard Island and McDonald Islands' => 'Heard Island and McDonald Islands',
						'Honduras'                         => 'Honduras',
						'Hong Kong'                        => 'Hong Kong',
						'Hungary'                          => 'Hungary',
						'Iceland'                          => 'Iceland',
						'India'                            => 'India',
						'Indonesia'                        => 'Indonesia',
						'Iran'                             => 'Iran',
						'Iraq'                             => 'Iraq',
						'Ireland'                          => 'Ireland',
						'Isle of Man'                      => 'Isle of Man',
						'Israel'                           => 'Israel',
						'Italy'                            => 'Italy',
						'Ivory Coast'                      => 'Ivory Coast',
						'Jamaica'                          => 'Jamaica',
						'Japan'                            => 'Japan',
						'Jersey'                           => 'Jersey',
						'Kazakhstan'                       => 'Kazakhstan',
						'Kenya'                            => 'Kenya',
						'Kiribati'                         => 'Kiribati',
						'Kosovo'                           => 'Kosovo',
						'Kuwait'                           => 'Kuwait',
						'Kyrgyzstan'                       => 'Kyrgyzstan',
						'Laos'                             => 'Laos',
						'Latvia'                           => 'Latvia',
						'Lebanon'                          => 'Lebanon',
						'Lesotho'                          => 'Lesotho',
						'Liberia'                          => 'Liberia',
						'Libya'                            => 'Libya',
						'Liechtenstein'                    => 'Liechtenstein',
						'Luxembourg'                       => 'Luxembourg',
						'Macao'                            => 'Macao',
						'Macedonia'                        => 'Macedonia',
						'Madagascar'                       => 'Madagascar',
						'Malawi'                           => 'Malawi',
						'Malaysia'                         => 'Malaysia',
						'Maldives'                         => 'Maldives',
						'Mali'                             => 'Mali',
						'Malta'                            => 'Malta',
						'Marshall Islands'                 => 'Marshall Islands',
						'Martinique'                       => 'Martinique',
						'Mauritania'                       => 'Mauritania',
						'Mauritius'                        => 'Mauritius',
						'Mayotte'                          => 'Mayotte',
						'Mexico'                           => 'Mexico',
						'Monaco'                           => 'Monaco',
						'Mongolia'                         => 'Mongolia',
						'Montenegro'                       => 'Montenegro',
						'Montserrat'                       => 'Montserrat',
						'Morocco'                          => 'Morocco',
						'Mozambique'                       => 'Mozambique',
						'Myanmar [Burma]'                  => 'Myanmar [Burma]',
						'Namibia'                          => 'Namibia',
						'Nauru'                            => 'Nauru',
						'Nepal'                            => 'Nepal',
						'Netherlands'                      => 'Netherlands',
						'New Caledonia'                    => 'New Caledonia',
						'New Zealand'                      => 'New Zealand',
						'Nicaragua'                        => 'Nicaragua',
						'Niger'                            => 'Niger',
						'Nigeria'                          => 'Nigeria',
						'Niue'                             => 'Niue',
						'Norfolk Island'                   => 'Norfolk Island',
						'North Korea'                      => 'North Korea',
						'Northern Mariana Islands'         => 'Northern Mariana Islands',
						'Norway'                           => 'Norway',
						'Oman'                             => 'Oman',
						'Pakistan'                         => 'Pakistan',
						'Palau'                            => 'Palau',
						'Palestine'                        => 'Palestine',
						'Panama'                           => 'Panama',
						'Papua New Guinea'                 => 'Papua New Guinea',
						'Paraguay'                         => 'Paraguay',
						'Peru'                             => 'Peru',
						'Philippines'                      => 'Philippines',
						'Pitcairn Islands'                 => 'Pitcairn Islands',
						'Poland'                           => 'Poland',
						'Portugal'                         => 'Portugal',
						'Puerto Rico'                      => 'Puerto Rico',
						'Qatar'                            => 'Qatar',
						'Republic of Korea'                => 'Republic of Korea',
						'Republic of Lithuania'            => 'Republic of Lithuania',
						'Republic of Moldova'              => 'Republic of Moldova',
						'Republic of the Congo'            => 'Republic of the Congo',
						'Romania'                          => 'Romania',
						'Russia'                           => 'Russia',
						'Rwanda'                           => 'Rwanda',
						'Réunion'                          => 'Réunion',
						'Saint Helena'                     => 'Saint Helena',
						'Saint Lucia'                      => 'Saint Lucia',
						'Saint Martin'                     => 'Saint Martin',
						'Saint Pierre and Miquelon'        => 'Saint Pierre and Miquelon',
						'Saint Vincent and the Grenadines' => 'Saint Vincent and the Grenadines',
						'Saint-Barthélemy'                 => 'Saint-Barthélemy',
						'Samoa'                            => 'Samoa',
						'San Marino'                       => 'San Marino',
						'Saudi Arabia'                     => 'Saudi Arabia',
						'Senegal'                          => 'Senegal',
						'Serbia'                           => 'Serbia',
						'Seychelles'                       => 'Seychelles',
						'Sierra Leone'                     => 'Sierra Leone',
						'Singapore'                        => 'Singapore',
						'Sint Maarten'                     => 'Sint Maarten',
						'Slovakia'                         => 'Slovakia',
						'Slovenia'                         => 'Slovenia',
						'Solomon Islands'                  => 'Solomon Islands',
						'Somalia'                          => 'Somalia',
						'South Africa'                     => 'South Africa',
						'South Georgia and the South Sandwich Islands' => 'South Georgia and the South Sandwich Islands',
						'South Sudan'                      => 'South Sudan',
						'Spain'                            => 'Spain',
						'Sri Lanka'                        => 'Sri Lanka',
						'St Kitts and Nevis'               => 'St Kitts and Nevis',
						'Sudan'                            => 'Sudan',
						'Suriname'                         => 'Suriname',
						'Svalbard and Jan Mayen'           => 'Svalbard and Jan Mayen',
						'Swaziland'                        => 'Swaziland',
						'Sweden'                           => 'Sweden',
						'Switzerland'                      => 'Switzerland',
						'Syria'                            => 'Syria',
						'São Tomé and Príncipe'            => 'São Tomé and Príncipe',
						'Taiwan'                           => 'Taiwan',
						'Tajikistan'                       => 'Tajikistan',
						'Tanzania'                         => 'Tanzania',
						'Thailand'                         => 'Thailand',
						'Togo'                             => 'Togo',
						'Tokelau'                          => 'Tokelau',
						'Tonga'                            => 'Tonga',
						'Trinidad and Tobago'              => 'Trinidad and Tobago',
						'Tunisia'                          => 'Tunisia',
						'Turkey'                           => 'Turkey',
						'Turkmenistan'                     => 'Turkmenistan',
						'Turks and Caicos Islands'         => 'Turks and Caicos Islands',
						'Tuvalu'                           => 'Tuvalu',
						'U.S. Minor Outlying Islands'      => 'U.S. Minor Outlying Islands',
						'U.S. Virgin Islands'              => 'U.S. Virgin Islands',
						'Uganda'                           => 'Uganda',
						'Ukraine'                          => 'Ukraine',
						'United Arab Emirates'             => 'United Arab Emirates',
						'United Kingdom'                   => 'United Kingdom',
						'United States'                    => 'United States',
						'Uruguay'                          => 'Uruguay',
						'Uzbekistan'                       => 'Uzbekistan',
						'Vanuatu'                          => 'Vanuatu',
						'Vatican City'                     => 'Vatican City',
						'Venezuela'                        => 'Venezuela',
						'Vietnam'                          => 'Vietnam',
						'Wallis and Futuna'                => 'Wallis and Futuna',
						'Western Sahara'                   => 'Western Sahara',
						'Yemen'                            => 'Yemen',
						'Zambia'                           => 'Zambia',
						'Zimbabwe'                         => 'Zimbabwe',
						'Åland'                            => 'Åland',
					),
					'placeholder' => __( 'Select or search...', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_whitelist_countries_select_box',
					'label'       => __( 'Treat Country/Continent List as a Whitelist', 'proxy-vpn-blocker' ),
					'description' => __( 'If this is turned \'on\' then the Countries/Continents selected above will be Whitelisted instead of Blacklisted, all other countries will be blocked. <p class="warning"><i class="fas fa-exclamation-triangle"></i> This Could Be Your Own Country/Continent! You would have to add your own Country or Continent or you WILL get blocked from logging in. Please see the FAQ for instructions on how to fix this if it happens!</p><p class="note"><i class="fas fa-exclamation-circle"></i> Bad IP\'s from whitelisted Countries/Continents will still be blocked!</p>', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
			),
		);
		$settings['Advanced']                 = array(
			'title'       => __( 'Advanced', 'proxy-vpn-blocker' ),
			'description' => __( 'These are Advanced Settings that are not generally recommended, or have been added on request. ', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_Custom_TAG_field',
					'label'       => __( 'Custom Tag', 'proxy-vpn-blocker' ),
					'description' => __( 'By default the tag used is siteurl.com/path/to/page-accessed, however you can supply your own descriptive tag. return to default by leaving this empty. <p class="note"><i class="fas fa-exclamation-circle"></i> You can enter \'0\' in this box to disable tagging completely if you want queries to be private.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => __( 'Custom Tag', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_good_ip_cache_time',
					'label'       => __( 'Known Good IP Cache', 'proxy-vpn-blocker' ),
					'description' => __( 'Known Good IP\'s are cached after the first time they are checked to save on queries to the proxycheck.io API, you can set this to between ten and 240 mins (4hrs) - Default cache time is 30 minutes.', 'proxy-vpn-blocker' ),
					'type'        => 'textslider-good-ip-cache-time',
					'default'     => '30',
					'placeholder' => __( '30', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_Days_Selector',
					'label'       => __( 'Last Detected Within', 'proxy-vpn-blocker' ),
					'description' => __( 'You can set this from 1 to 60 days depending on how strict you want the detection to be. 1 day would be very liberal, 60 days would be very strict.', 'proxy-vpn-blocker' ),
					'type'        => 'textslider',
					'default'     => '7',
					'placeholder' => __( '7', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_all_pages_activation',
					'label'       => __( 'Block on entire site', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to block Proxies/VPN\'s on every page of your website. This is at the expense of higher query usage and is NOT generally recommended.<p class="note"><i class="fas fa-exclamation-circle"></i> This will not work if you are using a caching plugin. This will also not turn on if you have any pages and/or posts selected on the "Restrict Posts/Pages" Tab. Please see FAQ.</p>', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_Admin_Alert_Denied_Email',
					'label'       => 'proxycheck.io \'denied\' status emails',
					'description' => __( 'If proxycheck.io returns a \'denied\' status message when a query is made, PVB will send you an email containing the details. To avoid too many emails being sent, this will only happen again if 3hrs have passed and there is still a \'denied\' status message.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_current_key',
					'label'       => 'Unique Settings Key',
					'description' => 'Each time the settings are saved, A unique ID is also saved, this ensures that previously cached "Known Good" IP\'s are re-checked under the new settings, instead of waiting until the cache for that IP expires.',
					'placeholder' => '',
					'type'        => 'hidden_key_field',
				),
			),
		);
		$settings = apply_filters( 'plugin_settings_fields', $settings );
		return $settings;
	}

	/**
	 * Register Proxy & VPN Blocker Settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {
			foreach ( $this->settings as $section => $data ) {
				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field.
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page.
					add_settings_field(
						$field['id'],
						$field['label'],
						array( $this->parent->admin, 'display_field' ),
						$this->parent->_token . '_settings',
						$section,
						array(
							'field'  => $field,
							'prefix' => $this->base,
						)
					);
				}
			}
		}
	}
	/**
	 * Settings Sections.
	 *
	 * @param  string $section Settings Section.
	 */
	public function settings_section( $section ) {
		echo '<h3> ' . $this->settings[ $section['id'] ]['description'] . '</h3>' . "\n";
	}

	/**
	 * Custom function for settings page.
	 *
	 * @param  string $page Settings Page.
	 */
	public function pvb_do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div id="pvbshow" class="pvbsuccess">Settings Updated</div>' . "\n";
		}
		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}
		echo '<div id="pvb-settings-tabs">' . "\n";
		echo '	<ul class="nav-tab-wrapper">' . "\n";
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			echo '	<li class="tabz" data-tab="tab-' . $section['id'] . '">' . "\n";
			echo '		<a class="tab-text">' . $section['title'] . '</a>' . "\n";
			echo '	</li>' . "\n";
		}
		echo '	</ul>' . "\n";
		echo '<div class="tabs-content">' . "\n";
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '	<div class="pvboptionswrap" id="tab-' . $section['id'] . '">' . "\n";
			call_user_func( $section['callback'], $section );
			echo '<div class="settings-form-wrapper">' . "\n";
			$this->pvb_do_settings_fields( $page, $section['id'] );
			echo '	</div>' . "\n";
			echo '		<input name="Submit" type="submit" class="pvbdefault" value="' . esc_attr( __( 'Save Settings', 'proxy-vpn-blocker' ) ) . '" />' . "\n";
			echo '	</div>' . "\n";
		}
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	}

	/**
	 * Custom Settings Fields.
	 *
	 * @param  string $page Settings Page.
	 * @param  string $section Settings Section.
	 */
	public function pvb_do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields ) ||
			! isset( $wp_settings_fields[ $page ] ) ||
			! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}
		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			echo '<div class="pvb_settingssection_container">' . "\n";
			if ( ! empty( $field[ 'args' ]['label_for']) ) {
				echo '<div class="pvb_settingsform_row">' . "\n";
				echo '	<div class="pvb_settingsform_left box">' . "\n";
				echo '		<p><label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label><br />' . "\n";
				echo '	</div>' . "\n";
				echo '	<div class="pvb_settingsform_right">' . "\n";
				echo '	</div>' . "\n";
				echo '</div>' . "\n";
			} else {
				echo '<div class="pvb_settingsform_row">' . "\n";
				echo '	<div class="pvb_settingsform_left box">' . "\n";
				echo '		<h3>' . $field['title'] . '</h3>' . "\n";
				echo '	</div>' . "\n";
				echo '	<div class="pvb_settingsform_right">' . "\n";
					call_user_func( $field['callback'], $field['args'] ) . "\n";
				echo '	</div>' . "\n";
				echo '</div>' . "\n";
			}
			echo '</div>' . "\n";
		}
	}

	/**
	 * Load settings page content
	 *
	 * @return void
	 */
	public function settings_page() {
		/**
		 * Safety measure to prevent redirect loop if custom block page is defined in list of blocked pages
		 *
		 * @Since 1.4.0
		 */
		if ( ! empty( get_option( 'pvb_proxycheckio_custom_blocked_page' ) ) && ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
			foreach ( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) as $select_page ) {
				$custom_page = get_option( 'pvb_proxycheckio_custom_blocked_page' );
				$permalink   = get_permalink( $select_page );
				if ( ! empty( $custom_page ) ) {
					if ( stripos( $custom_page[0], $permalink ) !== false ) {
						update_option( 'pvb_proxycheckio_custom_blocked_page', '' );
					}
				}
			}
		}
		// Build page HTML.
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
		$html .= '<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
		if ( ! isset( $_COOKIE['pvb-hide-donate-div'] ) ) {
			$html .= '<div class="pvbdonationsoffer" id="pvbdonationhide">' . "\n";
			$html .= '	<div class="pvbdonationtext">' . "\n";
			$html .= '		<h3>Proxy & VPN Blocker Donation Offer:</h3>' . "\n";
			$html .= '		<p>Get a proxycheck.io starter or pro plan via Proxy & VPN Blocker\'s website at a 20% promotional discount. This is a donation perk and 100% of the money paid for your plan goes to the Plugin developer, while you get to enjoy the full benefits of a proxycheck.io paid plan.</p>' . "\n";
			$html .= '		<p>Please check out Proxy & VPN Blocker\'s exclusive page which lists the discounted plan prices. We accept Paypal and various Crypto Currencies.</p>' . "\n";
			$html .= '		<p>It is important to note that Proxy & VPN Blocker is not made by proxycheck.io and support isn\'t provided by them with configuration, or the functionality of this Plugin.</p>' . "\n";
			$html .= '		<p>Thank you for your support!</p>' . "\n";
			$html .= '	</div>' . "\n";
			$html .= '	<div class="pvbdonationright">' . "\n";
			$html .= '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fas fa-times-circle"></i></button>' . "\n";
			$html .= '		<div class="pvbdonationbutton">' . "\n";
			$html .= '			<a href="https://pvb.ricksterm.net/plan-donate/" target="_blank"><button class="pvbdonate">Check Plan Pricing</button></a>' . "\n";
			$html .= '		</div>' . "\n";
			$html .= '	</div>' . "\n";
			$html .= '</div>' . "\n";
		}
		if ( empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
			if ( ! isset( $_COOKIE['pvb-hide-info-div'] ) ) {
				$html .= '<div class="pvbinfowrap">' . "\n";
				$html .= '	<div class="pvbinfowrapleft">' . "\n";
				$html .= '		<div class="pvbinfowraplogoinside">' . "\n";
				$html .= '		</div>' . "\n";
				$html .= '	</div>' . "\n";
				$html .= '	<div class="pvbinfowrapright">' . "\n";
				$html .= '		<button class="pvbinfodismiss" id="pvbinfoclosebutton" title="close"><i class="fas fa-times-circle"></i></button>' . "\n";
				$html .= '		<div class="pvbinfowraptext">' . "\n";
				$html .= '			<h1>' . __( 'Welcome to Proxy &amp; VPN Blocker', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
				$html .= '			<p>' . __( 'Without an API Key you don\'t have access to statistics and most features of <a href="https://proxycheck.io" target="_blank">proxycheck.io</a>. You are also limited to 100 daily queries.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
				$html .= '			<p>' . __( 'It is suggested that you sign up with proxycheck.io for your free API Key which has 1,000 daily queries and full access to all features. Paid higher query tiers are also available and are recommended for large sites.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
				$html .= '			<p>' . __( 'Please enter your proxycheck.io API key under \'Main\' to enable full functionality of Proxy &amp; VPN Blocker.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
				$html .= '		</div>' . "\n";
				$html .= '	</div>' . "\n";
				$html .= '</div>' . "\n";
			}
		}
		$html .= '<nav>' . "\n";
		$html .= '	<input type="checkbox" id="checkbox" />' . "\n";
		$html .= '	<label for="checkbox">' . "\n";
		$html .= '  	<ul class="menu first">' . "\n";
		$html .= '			<li><a href="https://pvb.ricksterm.net" target="_blank"><i class="fas fa-external-link-alt"></i> PVB Website</a></li>' . "\n";
		$html .= '			<li><a href="https://wordpress.org/support/plugin/proxy-vpn-blocker/" target="_blank"><i class="fab fa-wordpress"></i> Support & Issues</a></li>' . "\n";
		$html .= '			<li><a href="https://pvb.ricksterm.net/installationandconfiguration/" target="_blank"><i class="far fa-question-circle"></i> Configuration Guide</a></li>' . "\n";
		$html .= '			<li><a href="https://pvb.ricksterm.net/faq/" target="_blank"><i class="far fa-file-alt"></i> FAQ</a></li>' . "\n";
		$html .= '			<li id="donate"><a href="https://pvb.ricksterm.net/donate/" target="_blank"><i class="fas fa-donate"></i> Donate</a></li>' . "\n";
		$html .= ' 	 	</ul>' . "\n";
		$html .= '	  <span class="toggle"><i class="fas fa-bars"></i></span>' . "\n";
		$html .= '	</label>' . "\n";
		$html .= '</nav>' . "\n";

		$html .= '<form method="post" id="pvb-options-form" class="pvb" action="options.php" enctype="multipart/form-data">' . "\n";
			// Get settings fields.
			ob_start();
			settings_fields( $this->parent->_token . '_settings' );
			$this->pvb_do_settings_sections( $this->parent->_token . '_settings' );
		$html .= ob_get_clean();
		$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";
		echo $html;
	}

	/**
	 * Load Information and Statistics page content.
	 *
	 * @return void
	 */
	public function statistics_page() {
		include_once 'proxycheckio-apikey-statistics.php';
	}

	/**
	 * Load IP Blacklist page.
	 *
	 * @return void
	 */
	public function ipblacklist_page() {
		include_once 'proxycheckio-blacklist.php';
	}

	/**
	 * Load IP Whitelist page.
	 *
	 * @return void
	 */
	public function ipwhitelist_page() {
		include_once 'proxycheckio-whitelist.php';
	}

	/**
	 * Main proxy_vpn_blocker_Settings Instance.
	 *
	 * Ensures only one instance of proxy_vpn_blocker_Settings is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see proxy_vpn_blocker()
	 * @return Main proxy_vpn_blocker_Settings instance
	 */
	public static function instance( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.' ), $this->parent->_version );
	} // End __clone()
	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.' ), $this->parent->_version );
	} // End __wakeup()
}
