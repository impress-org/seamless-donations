<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

function seamless_donations_forms_engine ( $form_array ) {

	// embed version number in generated form code (4.0.2 and beyond)
	$form_html      = "<!-- SD " . dgx_donate_get_version () . " form engine mode -->";
	$form_body_html = '';
	$form_before    = '';
	$form_after     = '';

	if( ! isset( $form_array['method'] ) ) {
		$form_array['method'] = 'post';
	}
	if( ! isset( $form_array['onsubmit'] ) ) {
		$form_array['onsubmit'] = '';
	}
	if( isset( $form_array['before'] ) ) {
		$form_before = $form_array['before'];
	}
	$form_size = count ( $form_array );

	$form_validator = "return SeamlessDonationsFormsEngineValidator();";
	$form_validator = apply_filters (
		'seamless_donations_forms_engine_replace_validator', $form_validator );

	// manage lack of javascript by showing all hidden fields
	$form_noscript = "<noscript>";
	$form_noscript .= "<h2>" . esc_html__ (
			'JavaScript is not enabled. You must enable JavaScript to use this form.', 'seamless-donations' ) .
	                  "</h2>";
	$form_noscript .= "</noscript>";
	$form_noscript = apply_filters (
		'seamless_donations_forms_engine_noscript_filter', $form_noscript );

	$form_html .= $form_noscript;

	$form_tag_html = '<div class="seamless-donations-forms-engine">'; // all forms inside that class div
	$form_tag_html .= $form_before . '<form ';
	$form_tag_html .= "onsubmit='" . $form_validator . "' "; // doing this in JavaScript

	for( $form_index = 0; $form_index < $form_size; ++ $form_index ) {
		$attribute_name = seamless_donations_name_of ( $form_array, $form_index );

		switch( $attribute_name ) {
			case 'method':
				$form_method = trim ( $form_array['method'] );
				$form_tag_html .= "method='" . $form_method . "' ";
				break;
			case 'action':
				$form_action = trim ( $form_array['action'] );
				$form_tag_html .= "action='" . $form_action . "' ";
				break;
			case 'id':
				$form_id = trim ( $form_array['id'] );
				$form_tag_html .= "id='" . $form_id . "' ";
				break;
			case 'name':
				$form_name = trim ( $form_array['name'] );
				$form_tag_html .= "name='" . $form_name . "' ";
				break;
			case 'after':
				$form_after = $form_array['after'];
				break;
			case 'elements':
				$form_elements = $form_array[ $attribute_name ];
				if( is_array ( $form_elements ) ) {
					$form_body_html .= seamless_donations_forms_engine_element_list ( $form_elements );
				}
				break;
			default:
				$form_section = $form_array[ $attribute_name ];
				if( is_array ( $form_section ) ) {
					$form_body_html .= seamless_donations_forms_engine_section ( $form_section ); // recurse
				}
				break;
		}
	}
	$form_tag_html .= '>';
	$form_tag_html .= '<div class="seamless-donations-forms-error-message" style="display:none"></div>';
	$form_html .= $form_tag_html . $form_body_html . '</form>' . $form_after . '</div>';

	return $form_html;
}

function seamless_donations_forms_engine_section ( $form_array, $form_html = '' ) {

	$form_size       = count ( $form_array );
	$section_wrapper = 'div';
	$div_html        = '';
	$section_html    = '';
	$section_before  = '';
	$section_after   = '';

	// prep hide elements
	if( isset( $form_array['cloak'] ) ) {
		$section_hide = trim ( $form_array['cloak'] );
		if( isset( $form_array['class'] ) ) {
			$form_array['class'] = trim ( $form_array['class'] ) . ' ' . $section_hide;
		} else {
			$form_array['class'] = $section_hide;
		}
		if( isset( $form_array['style'] ) ) {
			$section_style = trim ( $form_array['style'] );
			if( substr ( $section_style, - 1 ) != ';' ) {
				$section_style .= ';'; // add a semi-colon to the end of the style attr
			}
			$section_style .= ' display:none';
			$form_array['style'] = $section_style;
		} else {
			$form_array['style'] = 'display:none';
		}
	}

	for( $section_index = 0; $section_index < $form_size; ++ $section_index ) {
		$attribute_name = seamless_donations_name_of ( $form_array, $section_index );

		// process attributes
		switch( $attribute_name ) {
			case 'class':
				$section_class = trim ( $form_array['class'] );
				$div_html .= "class='" . $section_class . "' ";
				break;
			case 'id':
				$section_id = trim ( $form_array['id'] );
				$div_html .= "id='" . $section_id . "' ";
				break;
			case 'style':
				$section_style = trim ( $form_array['style'] );
				$div_html .= "style='" . $section_style . "' ";
				break;
			case 'before':
				$section_before = $form_array['before'];
				break;
			case 'after':
				$section_after = $form_array['after'];
				break;
			case 'wrapper':
				$section_wrapper = trim ( $form_array['wrapper'] );
				if( $section_wrapper != 'div' and $section_wrapper != 'span' ) {
					$section_wrapper = 'div';
				}
				break;
			case 'elements':
				$section_elements = $form_array[ $attribute_name ];
				if( is_array ( $section_elements ) ) {
					$section_html .= seamless_donations_forms_engine_element_list ( $section_elements );
				}
				break;
			default:
				$section_section = $form_array[ $attribute_name ];
				if( is_array ( $section_section ) ) {
					$section_html .= seamless_donations_forms_engine_section ( $section_section ); // recurse
				}
				break;
		}
	}

	$open_section_html = '<' . $section_wrapper . ' ' . $div_html . '>' . $section_before;
	$section_html      = $open_section_html . $section_html . $section_after . '</' . $section_wrapper . '>';

	return $form_html . $section_html;
}

function seamless_donations_forms_engine_element_list ( $form_array, $form_html = '' ) {

	// process form elements without outer wrapping
	$form_size            = count ( $form_array ); // so the count only happens once
	$supported_form_types = array( 'text', 'checkbox', 'radio', 'hidden', 'submit', 'image', 'select', 'static' );
	$input_tag_types      = array( 'text', 'checkbox', 'radio', 'hidden', 'submit', 'image' );

	if( get_option ( 'dgx_donate_labels_for_input' ) == true ) {
		$generate_input_labels = true;
	} else {
		$generate_input_labels = false;
	}

	for( $element_index = 0; $element_index < $form_size; ++ $element_index ) {

		$element_html        = '';
		$element_type        = '';  // preload the element attributes
		$element_id          = '';
		$element_placeholder = '';
		$element_value       = '';
		$element_class       = '';
		$element_style       = '';
		$element_size        = '1';
		$element_wrapper     = 'div';
		$element_hide        = '';
		$element_reveal      = '';
		$element_conceal     = '';
		$element_check       = '';
		$element_uncheck     = '';
		$element_before      = '';
		$element_after       = '';
		$element_prompt      = '';
		$element_source      = '';
		$element_select      = false;
		$element_validation  = '';
		$element_options     = array();

		$element_name = seamless_donations_name_of ( $form_array, $element_index );

		// prep hide elements
		if( isset( $form_array[ $element_name ]['cloak'] ) ) {
			$element_hide = trim ( $form_array[ $element_name ]['cloak'] );
			if( isset( $form_array[ $element_name ]['class'] ) ) {
				$form_array[ $element_name ]['class']
					= trim ( $form_array[ $element_name ]['class'] ) . ' ' . $element_hide;
			} else {
				$form_array[ $element_name ]['class'] = $element_hide;
			}
			if( isset( $form_array[ $element_name ]['style'] ) ) {
				$element_style = trim ( $form_array[ $element_name ]['style'] );
				if( substr ( $element_style, - 1 ) != ';' ) {
					$element_style .= ';'; // add a semi-colon to the end of the style attr
				}
				$element_style .= ' display:none';
				$form_array[ $element_name ]['style'] = $element_style;
			} else {
				$form_array[ $element_name ]['style'] = 'display:none';
			}
		}

		for( $attribute_index = 0; $attribute_index < count ( $form_array[ $element_name ] ); ++ $attribute_index ) {

			$attribute_name = seamless_donations_name_of ( $form_array[ $element_name ], $attribute_index );

			// don't really need this switch code, but much easer to maintain and manage
			// when each element is clearly seen and testable
			switch( $attribute_name ) {
				case 'type':
					$element_type = trim ( $form_array[ $element_name ]['type'] );
					break;
				case 'id':
					$element_id = trim ( $form_array[ $element_name ]['id'] );
					break;
				case 'value':
					$element_value = trim ( $form_array[ $element_name ]['value'] );
					break;
				case 'class':
					$element_class = trim ( $form_array[ $element_name ]['class'] );
					break;
				case 'style':
					$element_style = trim ( $form_array[ $element_name ]['style'] );
					break;
				case 'size':
					$element_size = trim ( $form_array[ $element_name ]['size'] );
					break;
				case 'before':
					$element_before = trim ( $form_array[ $element_name ]['before'] );
					break;
				case 'after':
					$element_after = trim ( $form_array[ $element_name ]['after'] );
					break;
				case 'prompt':
					$element_prompt = trim ( $form_array[ $element_name ]['prompt'] );
					break;
				case 'select':
					$element_select = $form_array[ $element_name ]['select'];
					break;
				case 'check':
					$element_check = $form_array[ $element_name ]['check'];
					break;
				case 'uncheck':
					$element_uncheck = $form_array[ $element_name ]['uncheck'];
					break;
				case 'source':
					$element_source = trim ( $form_array[ $element_name ]['source'] );
					break;
				case 'placeholder':
					$element_placeholder = trim ( $form_array[ $element_name ]['placeholder'] );
					break;
				case 'wrapper':
					$element_wrapper = strtolower ( ( $form_array[ $element_name ]['wrapper'] ) );
					if( $element_wrapper != 'div' and $element_wrapper != 'span' ) {
						$element_wrapper = 'div';
					}
					break;
				case 'reveal':
					if( ! is_array ( $form_array[ $element_name ]['reveal'] ) ) {
						$element_reveal = trim ( $form_array[ $element_name ]['reveal'] );
					} else {
						$element_reveal = $form_array[ $element_name ]['reveal'];
					}
					break;
				case 'conceal':
					$element_conceal = trim ( $form_array[ $element_name ]['conceal'] );
					if( $element_conceal != '' ) {
						$conceal_array = explode ( ' ', $element_conceal );
						for( $conceal_index = 0; $conceal_index < count ( $conceal_array ); ++ $conceal_index ) {
							$conceal_array[ $conceal_index ] = '.' . $conceal_array[ $conceal_index ];
						}
						$element_conceal = implode ( ', ', $conceal_array );
					}
					break;
				case 'validation':
					$element_validation = trim ( $form_array[ $element_name ]['validation'] );
					break;
				case 'options':
					if( is_array ( $form_array[ $element_name ]['options'] ) ) {
						$element_options = $form_array[ $element_name ]['options'];
					}
					break;
			}
		}

		// write element HTML code
		if( $element_type != '' and in_array ( $element_type, $supported_form_types ) ) {
			// process the element div html code
			$div_span_html = "<$element_wrapper"; // div or span
			$div_span_html .= " id='" . $element_name . "' ";
			if( $element_class != '' ) {
				$div_span_html .= "class='" . $element_class . "' ";    // CLASS
			}
			if( $element_style != '' ) {
				$div_span_html .= "style='" . $element_style . "'";                     // STYLE
			}
			$div_span_html .= ">";
			$element_html .= $div_span_html;

			// now include the error div or span
			$div_span_html = "<$element_wrapper"; // div or span
			$div_span_html .= " id='" . $element_name . "-error-message' ";
			$div_span_html .= " style='display:none'";
			$div_span_html .= " class='seamless-donations-error-message-field'";
			$div_span_html .= "></$element_wrapper>";
			$element_html .= $div_span_html;

			if( in_array ( $element_type, $input_tag_types ) ) {
				// process input tag

				if( ( $generate_input_labels == true ) and ( $element_type == 'text' ) ) {
					// set up the label tag
					$element_html .= "<label for='" . sanitize_text_field ( $element_name ) . "'>";
					$element_html .= esc_html__ ( $element_before, 'seamless-donations' );
					$element_html .= " </label>";
				} else {
					$element_html .= $element_before;  // BEFORE
				}
				$element_html .= "<input type='" . $element_type . "' ";                    // INPUT
				// process the name and radio group
				if( isset( $form_array[ $element_name ]['group'] ) ) {
					$element_group = $form_array[ $element_name ]['group'];                 // GROUP
					$element_html .= "name='" . sanitize_text_field ( $element_group ) . "' ";
				} else {
					$element_html .= "name='" . sanitize_text_field ( $element_name ) . "' ";   // NAME
				}
				if( $element_select !== false ) {                                             // SELECT
					$element_html .= 'checked ';
				}
				if( $element_type != 'checkbox' ) {
					$element_html .= "value='" . $element_value . "' ";                         // VALUE
				}
				if( $element_type == 'text' and $element_size != '' ) {                     // TEXT
					$element_html .= "size='" . $element_size . "' ";                       // SIZE
				}
				if( $element_type == 'image' and $element_source != '' ) {                    // IMAGE
					$element_html .= "src='" . $element_source . "' ";
				}
				if( $element_id != '' ) {
					$element_html .= "id='" . $element_id . "' ";                           // ID
				}
				if( $element_placeholder != '' ) {
					$element_html .= "placeholder='" . $element_placeholder . "' ";         // placeholder
				}
				if( $element_reveal != '' ) {
					$element_html .= "data-reveal='."; // jQuery will look for classes with this name
					$element_html .= $element_reveal;
					$element_html .= "' ";
				}
				if( $element_conceal != '' ) {
					$element_html .= "data-conceal='"; // jQuery will look for classes with this name
					$element_html .= $element_conceal;
					$element_html .= "' ";
				}
				if( $element_check != '' ) {
					$element_html .= "data-check='"; // jQuery will look for element names with this name
					$element_html .= $element_check;
					$element_html .= "' ";
				}
				if( $element_uncheck != '' ) {
					$element_html .= "data-uncheck='"; // jQuery will look for element names with this name
					$element_html .= $element_uncheck;
					$element_html .= "' ";
				}
				if( $element_validation != '' ) {
					$element_html .= "data-validate='"; // this is an HTML5 attribute that makes jQuery easier
					$element_html .= strtolower ( trim ( str_replace ( ' ', '', $element_validation ) ) );
					$element_html .= "' ";
				}
				$element_html .= '/>';
				if( $element_prompt != '' ) {                                               // PROMPT
					$element_html .= $element_prompt;
				}
				$element_html .= $element_after;                                            // AFTER
			}
			if( $element_type == 'static' ) {                                               // STATIC
				$element_html .= $element_before;
				$element_html .= $element_value;
				$element_html .= $element_after;
			}
			if( $element_type == 'select' ) {                                               // SELECT
				// process select tag

				if( count ( $element_options ) > 0 ) {

					// only build the select tag if there are options provided

					if( $generate_input_labels == true ) {
						// set up the label tag
						$element_html .= "<label for='" . sanitize_text_field ( $element_name ) . "'>";
						$element_html .= esc_html__ ( $element_before, 'seamless-donations' );
						$element_html .= " </label>";
					} else {
						$element_html .= $element_before;  // BEFORE
					}

					$element_html .= "<select ";
					$element_html .= "name='" . sanitize_text_field ( $element_name ) . "' ";
					if( $element_size != '' ) {
						$element_html .= "size='" . $element_size . "' ";
					}
					if( $element_id != '' ) {
						$element_html .= "id='" . $element_id . "' ";
					}
					if( $element_placeholder != '' ) {
						$element_html .= "placeholder='" . $element_placeholder . "' ";
					}
					if( $element_class != '' ) {
						$element_html .= "class='" . $element_class . "' ";
					}
					if( $element_style != '' ) {
						$element_html .= "style='" . $element_style . "' ";
					}
					if( $element_conceal != '' ) {
						$element_html .= "data-conceal='" . $element_conceal . "' ";
					}
					$element_html = trim ( $element_html ) . '>';
					if( isset( $element_reveal ) and is_array ( $element_reveal ) ) {
						foreach( $element_reveal as $element_reveal_key => $element_reveal_item ) {
							$reveal_array = explode ( ' ', $element_reveal_item );
							for(
								$reveal_element_index = 0; $reveal_element_index < count ( $reveal_array );
								++ $reveal_element_index ) {
								$reveal_array[ $reveal_element_index ] = '.' . $reveal_array[ $reveal_element_index ];
							}
							$element_reveal[ $element_reveal_key ] = implode ( ', ', $reveal_array );
						}
					}
					for( $options_index = 0; $options_index < count ( $element_options ); ++ $options_index ) {
						$option_value = seamless_donations_name_of ( $element_options, $options_index );
						$element_html .= "<option value='" . $option_value . "'";
						if( $element_value != '' ) {
							// in a select, the value of the select element determines what's initially chose
							if( $element_value == $option_value ) {
								$element_html .= " selected='selected'";
							}
							if( isset( $element_reveal[ $option_value ] ) ) {
								$element_html .= " data-reveal='" . $element_reveal[ $option_value ] . "'";
							}
						}
						$element_html .= ">";
						$element_html .= $element_options[ $option_value ];
						$element_html .= "</option>";
					}
					$element_html .= "</select>";
					$element_html .= $element_after;
				}
			}
			// close the element div
			$element_html .= "</$element_wrapper>";
		}
		$form_html .= $element_html;
	}

	return $form_html;
}

// Shortcode
function seamless_form_test () {

	$form = array(
		'onsubmit'       => '',
		'method'         => 'post',
		'id'             => 'seamless-donations-form',
		'name'           => 'seamless-donations-form',
		'sectionZero'    => array(
			'elements' => array(
				'radio1' => array(
					'type'   => 'radio',
					'id'     => 'radio1',
					'group'  => 'radio',
					'prompt' => 'radio1',
				),
				'radio2' => array(
					'type'   => 'radio',
					'after'  => '&nbsp;forms R us',
					'id'     => 'radio2',
					'select' => '',
					'group'  => 'radio',
				),
			),
		),
		'sectionOne'     => array(
			'elements' => array(
				'mister_revealer'            => array(
					'type'   => 'checkbox',
					'id'     => 'dgx-donate-formtest',
					'prompt' => 'fun with forms',
					'reveal' => 'set1',
					'class'  => 'revealer-check',
				),
				'thing_to_show'              => array(
					'type'  => 'checkbox',
					'after' => '&nbsp;forms R us',
					'cloak' => 'set1',
					'id'    => 'dgx-donate-memorial',
				),
				'_dgx_donate_memorialf_gift' => array(
					'type'       => 'text',
					'after'      => ' forms be us',
					'id'         => 'dgx-donate-memorial-first',
					'cloak'      => 'set1',
					'size'       => '30',
					'validation' => 'required',
				),
				'email_address'              => array(
					'type'       => 'text',
					'before'     => ' enter email address',
					'id'         => 'email-address',
					'size'       => '30',
					'validation' => 'email',
				),
				'foobarblingman'             => array(
					'type'  => 'static',
					'cloak' => 'set1',
					'value' => '<p>Static text</p>',
				),
				'mylist'                     => array(
					'type'    => 'select',
					'id'      => 'puppy',
					'cloak'   => 'set1',
					'size'    => '1',
					'options' => array(
						'teddy' => "Theodore",
						'elly'  => "Eleanor",
						'fdr'   => "Franklin",
					),

				)
			),
			'style'    => 'background-color:green'
		),
		'sectionBlohard' => array(
			'elements' => array(
				'mister_revealerb'            => array(
					'type'   => 'checkbox',
					'id'     => 'dgx-donate-formtest',
					'prompt' => 'fun with forms',
					'reveal' => 'set1b',
					'class'  => 'revealer-check',
				),
				'thing_to_showb'              => array(
					'type'   => 'checkbox',
					'after'  => '&nbsp;forms R us',
					'reveal' => 'set2b',
					'id'     => 'dgx-donate-memorial',
				),
				'_dgx_donate_memorialf_giftb' => array(
					'type'       => 'text',
					'after'      => ' forms be us',
					'id'         => 'dgx-donate-memorial',
					'cloak'      => 'set1b',
					'size'       => '30',
					'validation' => 'currency,required',
				),
				'foobarblingmanb'             => array(
					'type'  => 'static',
					'cloak' => 'set2b',
					'value' => '<p>Static text</p>',
				),
				'mylistb'                     => array(
					'type'    => 'select',
					'id'      => 'puppy',
					'cloak'   => 'set2b',
					'size'    => '1',
					'options' => array(
						'teddy' => "Theodore",
						'elly'  => "Eleanor",
						'fdr'   => "Franklin",
					),

				)
			),
			'style'    => 'background-color:yellow'
		),
		'elements'       => array(
			'submitter' => array(
				'type'  => 'submit',
				'id'    => 'seamless-donations-form',
				'value' => 'Donate Now!',
				'class' => 'submit-button seamless-donations-form-submit',
			),
		)
	);

	echo "<h2>Form output:</h2>";
	echo seamless_donations_forms_engine ( $form );
}

add_shortcode ( 'seamless_formtest', 'seamless_form_test' );