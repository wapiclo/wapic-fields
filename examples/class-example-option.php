<?php

namespace Wapic_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomOption extends \Wapic_Fields\Field {

	private $id = 'wapic-field-option';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
	}

	public function register() {
		add_menu_page(
			'Wapic Fields Example',                // Page title
			'Wapic Fields',                // Menu title
			'manage_options',                // Capability
			$this->id,                 // Menu slug
			array( $this, 'render' ),  // Callback
			'dashicons-admin-generic',       // Icon
			9999                             // Position
		);
	}

	/**
	 * Render the fields
	 */
	public function render() {

		$this->start_controls_panel(
			array(
				'title' => 'Wapic Fields Example',
				'id'    => $this->id,
				'type'  => 'setting',
			)
		);

		// Start Tabs
		$this->start_controls_section(
			array(
				'general'     => 'General',
				'conditional' => 'Conditional',
				'advanced'    => 'Advanced',
			)
		);

		// General Tab
		$this->start_controls_group(
			array(
				'id' => 'general',
			)
		);

		$this->group_controls_general();

		$this->end_controls_group();

		// Conditional Tab
		$this->start_controls_group(
			array(
				'id' => 'conditional',
			)
		);

		$this->group_controls_conditional();

		$this->end_controls_group();

		// Advanced Tab
		$this->start_controls_group(
			array(
				'id' => 'advanced',
			)
		);

		$this->group_controls_advanced();

		$this->end_controls_group();

		// End Tabs
		$this->end_controls_section();

		$this->end_controls_panel(
			array(
				'type' => 'setting',
			)
		);
	}

	/**
	 * Render General Tab Fields
	 */
	private function group_controls_general() {
		$this->add_control(
			array(
				'type'  => 'html',
				'value' => '<p>Change the background color of address bar in mobile browser</p>',
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_text',
				'type'  => 'text',
				'label' => 'Regular Text',
				'value' => get_option( '_sample_text' ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_email',
				'type'  => 'email',
				'label' => 'Email',
				'value' => get_option( '_sample_email' ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_phone',
				'type'  => 'phone',
				'label' => 'Phone',
				'value' => get_option( '_sample_phone' ),
			)
		);

		$this->add_control(
			array(
				'id'         => '_sample_number',
				'type'       => 'number',
				'label'      => 'Number',
				'value'      => get_option( '_sample_number' ),
				'attributes' => array(
					'min' => 0,
					'max' => 100,
				),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_url',
				'type'  => 'url',
				'label' => 'URL',
				'value' => get_option( '_sample_url' ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_textarea',
				'type'  => 'textarea',
				'label' => 'Textarea',
				'value' => get_option( '_sample_textarea' ),
			)
		);

		$this->add_control(
			array(
				'id'      => '_sample_select',
				'type'    => 'select',
				'label'   => 'Select',
				'options' => array(
					'option_1' => 'Option 1',
					'option_2' => 'Option 2',
					'option_3' => 'Option 3',
				),
				'value'   => get_option( '_sample_select' ),
			)
		);

		$this->add_control(
			array(
				'id'      => '_sample_checkbox',
				'type'    => 'checkbox',
				'label'   => 'Checkbox',
				'options' => array(
					'option_1' => 'Option 1',
					'option_2' => 'Option 2',
					'option_3' => 'Option 3',
				),
				'value'   => get_option( '_sample_checkbox' ),
			)
		);

		$this->add_control(
			array(
				'id'      => '_sample_radio',
				'type'    => 'radio',
				'label'   => 'Radio',
				'options' => array(
					'option_1' => 'Option 1',
					'option_2' => 'Option 2',
					'option_3' => 'Option 3',
				),
				'value'   => get_option( '_sample_radio' ),
			)
		);
	}

	/**
	 * Render Conditional Tab Fields
	 */
	private function group_controls_conditional() {

		$this->add_control(
			array(
				'id'          => '_sample_text_required',
				'type'        => 'text',
				'label'       => 'Regular Text Required',
				'description' => 'Regular text field.',
				'value'       => get_option( '_sample_text_required' ),
				'required'    => true,
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_price',
				'type'  => 'number',
				'label' => 'Price',
				'class' => 'regular-price',
				'value' => get_option( '_sample_price' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_sale_price',
				'type'        => 'number',
				'label'       => 'Sale Price',
				'class'       => 'sale-price',
				'description' => 'Sale price must be less than regular price',
				'value'       => get_option( '_sample_sale_price' ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_toggle_conditional',
				'type'  => 'toggle',
				'label' => 'Toggle Conditional',
				'value' => get_option( '_sample_toggle_conditional' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_url_conditional',
				'type'        => 'url',
				'label'       => 'URL Conditional',
				'description' => 'URL field required when toggle conditional is on or value is "yes"',
				'value'       => get_option( '_sample_url_conditional' ),
				'condition'   => array(
					'field' => '_sample_toggle_conditional',
					'value' => 'yes',
				),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_select_conditional',
				'type'        => 'select',
				'label'       => 'Select Conditional',
				'description' => 'Select Conditional required when toggle conditional is on or value is "yes"',
				'options'     => array(
					'option_1' => 'Option 1',
					'option_2' => 'Option 2',
					'option_3' => 'Option 3',
				),
				'value'       => get_option( '_sample_select_conditional' ),
				'condition'   => array(
					'field' => '_sample_toggle_conditional',
					'value' => 'yes',
				),
			)
		);
	}

	/**
	 * Render Advanced Tab Fields
	 */
	private function group_controls_advanced() {

		$this->add_control(
			array(
				'id'          => '_sample_image',
				'type'        => 'image',
				'label'       => 'Image',
				'description' => 'Image field.',
				'value'       => get_option( '_sample_image' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_gallery',
				'type'        => 'gallery',
				'label'       => 'Gallery',
				'description' => 'Gallery field.',
				'value'       => get_option( '_sample_gallery' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_file',
				'type'        => 'file',
				'label'       => 'File',
				'description' => 'File field.',
				'value'       => get_option( '_sample_file' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_toggle',
				'type'        => 'toggle',
				'label'       => 'Toggle',
				'description' => 'Toggle field.',
				'value'       => get_option( '_sample_toggle' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_color',
				'type'        => 'color',
				'label'       => 'Color Picker',
				'description' => 'Color Picker field.',
				'value'       => get_option( '_sample_color' ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_date',
				'type'        => 'date',
				'label'       => 'Date Picker',
				'description' => 'Date Picker field.',
				'value'       => get_option( '_sample_date' ),
			)
		);

		$this->add_control(
			array(
				'id'         => '_sample_select2',
				'type'       => 'select2',
				'label'      => 'Select Options',
				'value'      => get_option( '_sample_select2' ),
				'options'    => array(
					'option1'  => 'Option 1',
					'option2'  => 'Option 2',
					'option3'  => 'Option 3',
					'option4'  => 'Option 4',
					'option5'  => 'Option 5',
					'option6'  => 'Option 6',
					'option7'  => 'Option 7',
					'option8'  => 'Option 8',
					'option9'  => 'Option 9',
					'option10' => 'Option 10',
				),
				'attributes' => array(
					'multiple'    => true,
					'placeholder' => 'Select options...',
					'allow_clear' => true,
				),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_editor',
				'type'        => 'editor',
				'label'       => 'WP Editor',
				'description' => 'WP Editor field.',
				'value'       => get_option( '_sample_editor' ),
			)
		);
	}

	public function save() {
		// First, ensure the options exist with autoload = 'no'
		$fields = array(
			'_sample_text'               => 'text',
			'_sample_email'              => 'email',
			'_sample_phone'              => 'phone',
			'_sample_number'             => 'number',
			'_sample_url'                => 'url',
			'_sample_textarea'           => 'textarea',
			'_sample_select'             => 'select',
			'_sample_checkbox'           => 'checkbox',
			'_sample_radio'              => 'radio',
			'_sample_text_required'      => 'text',
			'_sample_price'              => 'number',
			'_sample_sale_price'         => 'number',
			'_sample_toggle_conditional' => 'toggle',
			'_sample_select_conditional' => 'select',
			'_sample_image'              => 'image',
			'_sample_gallery'            => 'gallery',
			'_sample_file'               => 'file',
			'_sample_toggle'             => 'toggle',
			'_sample_date'               => 'date',
			'_sample_color'              => 'color',
			'_sample_select2'            => 'select2',
			'_sample_editor'             => 'editor',
		);

		foreach ( $fields as $field_name => $field_type ) {
			register_setting(
				$this->id,
				$field_name,
				array(
					'sanitize_callback' => function ( $value, $option = '' ) use ( $field_type, $field_name ) {
						// Skip validation if the input is hidden
						if ( isset( $_POST[ $field_name . '_is_hidden' ] ) && $_POST[ $field_name . '_is_hidden' ] === '1' ) {
							return $this->sanitize_value( $field_type, $value );
						}

						// Get validated value
						$validation = $this->validate_value( $field_type, $value );

						if ( ! empty( $validation ) ) {
							add_settings_error( $this->id, 'validation_error', $validation, 'error' );
							// Return the old value to prevent saving invalid data
							return get_option( $field_name, '' );
						} else {
							return $this->sanitize_value( $field_type, $value );
						}
					},
				)
			);
		}
	}
}
// Init
new CustomOption();
