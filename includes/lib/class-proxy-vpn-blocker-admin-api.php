<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class proxy_vpn_blocker_Admin_API {
	/**
	 * Generate HTML for displaying fields.
	 */
	public function display_field( $data = array(), $post = false, $echo = true ) {

		// Get field info.
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name.
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data.
		$data = '';
		if ( $post ) {

			// Get saved field data.
			$option_name .= $field['id'];
			$option       = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field.
			if ( isset( $option ) ) {
				$data = $option;
			}
		} else {

			// Get saved option.
			$option_name .= $field['id'];
			$option       = get_option( $option_name );

			// Get data to display in field.
			if ( isset( $option ) ) {
				$data = $option;
			}
		}

		// Show default data if no option saved and default is supplied.
		if ( false === $data && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( false === $data ) {
			$data = '';
		}

		$html = '';

		switch ( $field['type'] ) {

			case 'text':
				$html .= '<input class="pvb" id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
				break;

			case 'apikey':
				$html .= '<input class="pvb" id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
				break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>' . "\n";
				break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' === $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<label class="switch">' . "\n";
				$html .= '<input autocomplete="off" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '" ' . $checked . '>' . "\n";
				$html .= '<div class="slider round">' . "\n";
				$html .= '<span class="slideron">ON</span>' . "\n";
				$html .= '<span class="slideroff">OFF</span>' . "\n";
				$html .= '</div>' . "\n";
				$html .= '</label>';
				break;

			case 'textslider':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="1" max="60" step="1" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue" id="sliderrangeoutput"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'textslider-riskscore-proxy':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange2" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="1" max="99" step="1" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue2" id="sliderrangeoutput2"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'textslider-riskscore-vpn':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange3" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="1" max="99" step="1" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue2" id="sliderrangeoutput3"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'textslider-good-ip-cache-time':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange4" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="10" max="240" step="10" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue4" id="sliderrangeoutput4"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( in_array( $k, (array) $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k === $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k === $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_country_multi':
				$html .= '<select class="chosen-select form-control" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple autocomplete="off">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_page_single':
				$html .= '<select class="chosen-select form-control" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '"  autocomplete="off" multiple>';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_pages_multi':
				$html .= '<select class="chosen-select form-control" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple autocomplete="off">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'editor':
				wp_editor(
					$data,
					$option_name,
					array(
						'textarea_name' => $option_name,
					)
				);
				break;

			case 'hidden_key_field':
					$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="hidden" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( uniqid() ) . '" />' . "\n";
				break;
		}

		switch ( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
			case 'country_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
				break;

			default:
				$html .= '<p class="description">' . $field['description'] . '</p>' . "\n";
				break;
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;

	}

	/**
	 * Validate form field
	 *
	 * @param  string $data Submitted value
	 * @param  string $type Type of field to validate
	 * @return string       Validated value
	 */
	public function validate_field( $data = '', $type = 'text' ) {

		switch ( $type ) {
			case 'text':
				$data = esc_attr( $data );
				break;
		}

		return $data;
	}
	public function validate_field_checkbox( $data = '', $type = 'checkbox' ) {

		switch ( $type ) {
			case 'checkbox':
				$data = esc_attr( $data );
				break;
		}

		return $data;
	}
	public function validate_field_apikey( $data = '', $type = 'apikey' ) {

		switch ( $type ) {
			case 'apikey':
				$data = esc_attr( $data );
				break;
		}

	}
}
