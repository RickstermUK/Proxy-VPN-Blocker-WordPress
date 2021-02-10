<?php
/**
 * Generate API Key Statistics Page.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	'i'      => array(
		'class' => array(),
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
	'strong' => array(),
	'h1'     => array(),
	'h2'     => array(
		'class' => array(),
	),
	'h3'     => array(),
	'p'      => array(),
);

$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );
if ( ! empty( $get_api_key ) ) {
	// Build page HTML.
	$request_args  = array(
		'timeout'     => '10',
		'blocking'    => true,
		'httpversion' => '1.1',
	);
	$request_usage = wp_remote_get( 'https://proxycheck.io/dashboard/export/usage/?key=' . $get_api_key, $request_args );
	$api_key_usage = json_decode( wp_remote_retrieve_body( $request_usage ) );
	if ( isset( $api_key_usage->status ) && 'denied' === $api_key_usage->status ) {
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics">' . "\n";
		$html .= '<h2></h2>' . "\n";
		$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '<div class="pvberror">' . "\n";
		$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
		$html .= '<div class="pvberrorinside">' . "\n";
		$html .= '<h2>' . __( 'You must enable Dashboard API Access within your <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> Dashboard to access this part of Proxy & VPN Blocker', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>';
		echo wp_kses( $html, $allowed_html );
	} else {
		// Format and Display usage stats.
		$queries_today = $api_key_usage->{'Queries Today'};
		$daily_limit   = $api_key_usage->{'Daily Limit'};
		$queries_total = $api_key_usage->{'Queries Total'};
		$plan_tier     = $api_key_usage->{'Plan Tier'};
		$burst_tokens  = $api_key_usage->{'Burst Tokens Available'};
		$html          = '<div class="wrap" id="' . $this->parent->_token . '_statistics">' . "\n";
		$html         .= '<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
		$html         .= '<div class="pvbareawrap">' . "\n";
		$html         .= '<h1>' . __( 'Your proxycheck.io API Key Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html         .= '<div class="pvbapidaily">';
		$html         .= '<div class="pvbapikey">' . __( 'API Key: ', 'proxy-vpn-blocker' ) . $get_api_key . '</div>' . "\n";
		$html         .= '<div class="pvbapitier">' . __( 'Plan: ', 'proxy-vpn-blocker' ) . $plan_tier . __( ' | ', 'proxy-vpn-blocker' ) . number_format( $daily_limit ) . __( ' Daily Queries', 'proxy-vpn-blocker' ) . '</div>' . "\n";
		$html         .= '</div>';
		$html         .= '<div class="pvbapiusageday">';
		$usage_percent = ( $queries_today * 100 ) / $daily_limit;
		if ( $usage_percent > 100 ) {
			$usage_percent = 100;
		}
		$html .= 'API Key Usage Today: ' . number_format( $queries_today ) . '/' . number_format( $daily_limit ) . ' Queries - ' . round( $usage_percent, 2 ) . '% of Total.';
		$html .= '<div class="pvbpercentbar">';
		$html .= '<div class="pvbpercentbarinner" style="width:' . $usage_percent . '%">';
		$html .= '</div> </div>';
		$html .= 'Burst Tokens Available: ' . $burst_tokens . "\n";
		$html .= '</div>';
		$html .= '</div>' . "\n";
		$html .= '<div class="pvbareawrap">' . "\n";
		$html .= '<h1>' . __( 'API Key Queries: Past Month', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		echo wp_kses( $html, $allowed_html );
	}

		// Month stats graph.
		$html  = '<script type="text/javascript">
		am4core.ready(function() {
			// Themes begin
			function am4themes_myTheme(target) {
				if (target instanceof am4core.InterfaceColorSet) {
					target.setFor("secondaryButton", am4core.color("#5ba7cb"));
					target.setFor("secondaryButtonHover", am4core.color("#5ba7cb").lighten(-0.2));
					target.setFor("secondaryButtonDown", am4core.color("#5ba7cb").lighten(-0.2));
					target.setFor("secondaryButtonActive", am4core.color("#5ba7cb").lighten(-0.2));
					target.setFor("secondaryButtonText", am4core.color("#FFFFFF"));
					target.setFor("secondaryButtonStroke", am4core.color("#467B88"));
					target.setFor("text", am4core.color("#8C929A"));
					target.setFor("alternativeText", am4core.color("#8C929A"));
				}
			  }
			am4core.useTheme(am4themes_animated);
			am4core.useTheme(am4themes_myTheme);
			// Themes end
		
			// Create chart instance
			var chart = am4core.create("amchartAPImonth", am4charts.XYChart);
		
			// Increase contrast by taking evey second color
			chart.colors.step = 2;
		
			// Add data 
			chart.dataSource.url = "' . get_site_url() . '/wp-json/proxy-vpn-blocker-stats/v1/month-stats?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ) . '";
		
			// Create Category Axis
			var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
			categoryAxis.dataFields.category = "days";
			categoryAxis.title.text = "Date";

			// Create value axis
			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.title.text = "Queries";
			valueAxis.renderer.minLabelPosition = 0.01;

			// Create series
			var series1 = chart.series.push(new am4charts.LineSeries());
			series1.dataFields.valueY = "proxies";
			series1.dataFields.categoryX = "days";
			series1.name = "Proxies Detected";
			series1.strokeWidth = 2.5;
			series1.tensionX = 0.8;
			series1.tooltipText = " {name}: {valueY}";
			series1.bullets.push(new am4charts.CircleBullet());

			var series2 = chart.series.push(new am4charts.LineSeries());
			series2.dataFields.valueY = "vpns";
			series2.dataFields.categoryX = "days";
			series2.name = "VPN\'s Detected";
			series2.strokeWidth = 2.5;
			series2.tensionX = 0.8;
			series2.tooltipText = " {name}: {valueY}";
			series2.bullets.push(new am4charts.CircleBullet());
		
			var series3 = chart.series.push(new am4charts.LineSeries());
			series3.dataFields.valueY = "undetected";
			series3.dataFields.categoryX = "days";
			series3.name = "Undetected";
			series3.strokeWidth = 2.5;
			series3.tensionX = 0.8;
			series3.tooltipText = " {name}: {valueY}";
			series3.bullets.push(new am4charts.CircleBullet());
		
			var series4 = chart.series.push(new am4charts.LineSeries());
			series4.dataFields.valueY = "refused queries";
			series4.dataFields.categoryX = "days";
			series4.name = "Refused Queries";
			series4.strokeWidth = 2.5;
			series4.tensionX = 0.8;
			series4.tooltipText = " {name}: {valueY}";
			series4.bullets.push(new am4charts.CircleBullet());
		
			// Add chart cursor
			chart.cursor = new am4charts.XYCursor();
			chart.cursor.behavior = "zoomY";


			let hs1 = series1.segments.template.states.create("hover")
			hs1.properties.strokeWidth = 5;
			series1.segments.template.strokeWidth = 1;

			let hs2 = series2.segments.template.states.create("hover")
			hs2.properties.strokeWidth = 5;
			series2.segments.template.strokeWidth = 1;

			let hs3 = series3.segments.template.states.create("hover")
			hs3.properties.strokeWidth = 5;
			series3.segments.template.strokeWidth = 1;

			let hs4 = series4.segments.template.states.create("hover")
			hs4.properties.strokeWidth = 5;
			series4.segments.template.strokeWidth = 1;

			// Add legend
			chart.legend = new am4charts.Legend();

			chart.scrollbarX = new am4core.Scrollbar();
			chart.scrollbarX.parent = chart.bottomAxesContainer;
		}); // end am4core.ready()
		</script>
		';
		$html .= '<div id="amchartAPImonth" style="width: 100%; height: 450px;"></div>' . "\n";
		$html .= '<p>' . __( '*Statistics delayed by several minutes.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
		$html .= '</div>' . "\n";
		// Get recent detection stats.
		$html .= '<div id="log_outer">' . "\n";
		$html .= '	<div id="log_content"></div>' . "\n";
		$html .= '	<form id="log_query_form" action="https://proxycheck.io/dashboard/export/detections/pvb.pagination.v2.php" method="post" target="hiddenFrame">' . "\n";
		$html .= '		<input type="hidden" id="api_key" name="api_key" value="' . $get_api_key . '">' . "\n";
		$html .= '		<input type="hidden" id="page_number" name="page_number" value="0">' . "\n";
		$html .= '		<div class="fancy-bottom">' . "\n";
		$html .= '			<button class="pvbdefault" style="float: right;" onclick="decrementValue()" type="submit">View Newer Entries <i class="fas fa-angle-double-right"></i></button>' . "\n";
		$html .= '			<button class="pvbdefault"  onclick="incrementValue()" type="submit"><i class="fas fa-angle-double-left"></i> View Older Entries</button>' . "\n";
		$html .= '		</div>' . "\n";
		$html .= '	</form>' . "\n";
		$html .= '</div>' . "\n";
		echo wp_kses( $html, $allowed_html );
} else {
	$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics">' . "\n";
	$html .= '<div class="pvbareawrap">' . "\n";
	$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
	$html .= '<div class="pvberror">' . "\n";
	$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
	$html .= '<div class="pvberrorinside">' . "\n";
	$html .= '<h2>' . __( 'Please set a <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> API Key to see this page!', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
	$html .= '<h3>' . __( 'This page will display stats about your API Key queries and recent detections.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '<h3>' . __( 'If you need an API Key they are free for up to 1000 daily queries, paid plans are available with more.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>';
	echo wp_kses( $html, $allowed_html );
}

/**
 * Function for stats table.
 */
function pagination_javascript() {
	$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );
	?>
	<script type="text/javascript">
						jQuery(document).ready(function($) {
							$('#log_content').load("https://proxycheck.io/dashboard/export/detections/pvb.pagination.v2.php?api_key=<?php echo $get_api_key; ?>");
						});
						jQuery('#log_query_form').submit(function(e) { // catch the form's submit event
							e.preventDefault();
							jQuery.ajax({ // create an AJAX call...
								data: jQuery(this).serialize(), // get the form data
								type: jQuery(this).attr('method'), // GET or POST
								url: jQuery(this).attr('action'), // the file to call
								success: function(response) { // on success..
									jQuery('#log_content').html(response); // update the DIV
								}
							}
						);
						return false; // cancel original event to prevent form submitting
						});
						function incrementValue() {
							var value = parseInt(document.getElementById('page_number').value, 10);
							value = isNaN(value) ? 0 : value;
							value++;
							document.getElementById('page_number').value = value;
						}
						function decrementValue() {
							var value = parseInt(document.getElementById('page_number').value, 10);
							value = isNaN(value) ? 0 : value;
							value--;
							if (value < 0) {
								value = 0;
							}
							document.getElementById('page_number').value = value;
						}
					</script> 
					<?php
}
add_action( 'admin_footer', 'pagination_javascript' );
