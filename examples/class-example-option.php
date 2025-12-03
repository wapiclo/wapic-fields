<?php
/**
 * Example Option Page using Wapic Fields
 *
 * @version 1.1.0
 * @package Wapic_Fields
 */

namespace Wapic_Fields\Example;

if (! defined('ABSPATH')) {
	exit;
}

use Wapic_Fields\Field;

class Example_Option {

	private $id = 'wapic-field-option';

	public function __construct() {
		add_action('admin_menu', array($this, 'register'));
		add_action('admin_init', array($this, 'save'));
	}

	/**
	 * Register Admin Menu Page
	 * 
	 * Creates a settings page under WP Admin with Wapic Fields UI.
	 */
	public function register() {
		add_menu_page(
			esc_html__('Wapic Fields Options Example', 'wapic-field'),
			esc_html__('Wapic Fields', 'wapic-field'),
			'manage_options',
			$this->id,
			array($this, 'render'),
			'dashicons-admin-generic',
			9999
		);
	}

	/**
	 * Render the Settings Page with Tabs & Field Groups
	 */
	public function render() {

		Field::start_controls_panel(
			array(
				'title' => esc_html__('Wapic Fields Options Example', 'wapic-field'),
				'id'    => $this->id,
				'type'  => 'setting',
			)
		);

		// Start Tabs
		Field::start_controls_section(
			array(
				'general'     => esc_html__('General', 'wapic-field'),
				'conditional' => esc_html__('Conditional', 'wapic-field'),
				'advanced'    => esc_html__('Advanced', 'wapic-field'),
			)
		);

		/* -------------------------
		 * General Tab
		 * ------------------------- */
		Field::start_controls_group(array('id' => 'general'));
		$this->group_controls_general();
		Field::end_controls_group();

		/* -------------------------
		 * Conditional Tab
		 * ------------------------- */
		Field::start_controls_group(array('id' => 'conditional'));
		$this->group_controls_conditional();
		Field::end_controls_group();

		/* -------------------------
		 * Advanced Tab
		 * ------------------------- */
		Field::start_controls_group(array('id' => 'advanced'));
		$this->group_controls_advanced();
		Field::end_controls_group();

		// End Tabs
		Field::end_controls_section();

		Field::end_controls_panel(array('type' => 'setting'));
	}

	/**
	 * General Tab Fields
	 */
	private function group_controls_general() {

		Field::add_control(
			array(
				'type'  => 'html',
				'value' => '<p>' . esc_html__('Change the mobile browser address bar color using these settings.', 'wapic-field') . '</p>',
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_text',
				'type'  => 'text',
				'label' => esc_html__('Text Field', 'wapic-field'),
				'value' => get_option('_sample_text'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_email',
				'type'  => 'email',
				'label' => esc_html__('Email Address', 'wapic-field'),
				'value' => get_option('_sample_email'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_phone',
				'type'  => 'phone',
				'label' => esc_html__('Phone Number', 'wapic-field'),
				'value' => get_option('_sample_phone'),
			)
		);

		Field::add_control(
			array(
				'id'         => '_sample_number',
				'type'       => 'number',
				'label'      => esc_html__('Number Field', 'wapic-field'),
				'value'      => get_option('_sample_number'),
				'attributes' => array(
					'min' => 0,
					'max' => 100,
				),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_url',
				'type'  => 'url',
				'label' => esc_html__('Website URL', 'wapic-field'),
				'value' => get_option('_sample_url'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_textarea',
				'type'  => 'textarea',
				'label' => esc_html__('Textarea', 'wapic-field'),
				'value' => get_option('_sample_textarea'),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_select',
				'type'    => 'select',
				'label'   => esc_html__('Dropdown Select', 'wapic-field'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-field'),
					'option_2' => esc_html__('Option 2', 'wapic-field'),
					'option_3' => esc_html__('Option 3', 'wapic-field'),
				),
				'value'   => get_option('_sample_select'),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_checkbox',
				'type'    => 'checkbox',
				'label'   => esc_html__('Checkbox Options', 'wapic-field'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-field'),
					'option_2' => esc_html__('Option 2', 'wapic-field'),
					'option_3' => esc_html__('Option 3', 'wapic-field'),
				),
				'value' => get_option('_sample_checkbox'),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_radio',
				'type'    => 'radio',
				'label'   => esc_html__('Radio Options', 'wapic-field'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-field'),
					'option_2' => esc_html__('Option 2', 'wapic-field'),
					'option_3' => esc_html__('Option 3', 'wapic-field'),
				),
				'value' => get_option('_sample_radio'),
			)
		);
	}

	/**
	 * Conditional Tab Fields
	 */
	private function group_controls_conditional() {

		Field::add_control(
			array(
				'id'          => '_sample_text_required',
				'type'        => 'text',
				'label'       => esc_html__('Required Text Field', 'wapic-field'),
				'description' => esc_html__('This field must not be empty.', 'wapic-field'),
				'value'       => get_option('_sample_text_required'),
				'required'    => true,
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_price',
				'type'  => 'number',
				'label' => esc_html__('Regular Price', 'wapic-field'),
				'class' => 'regular-price',
				'value' => get_option('_sample_price'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_sale_price',
				'type'        => 'number',
				'label'       => esc_html__('Sale Price', 'wapic-field'),
				'class'       => 'sale-price',
				'description' => esc_html__('Sale price must be lower than the regular price.', 'wapic-field'),
				'value'       => get_option('_sample_sale_price'),
			)
		);
// 1. Toggle field
		Field::add_control(
			array(
				'id'    => '_sample_toggle_conditional',
				'type'  => 'toggle',
				'label' => esc_html__('Enable Conditional Fields', 'wapic-field'),
				'value' => get_option('_sample_toggle_conditional'),
			)
		);
// 2. URL field that appears conditionally
		Field::add_control(
			array(
				'id'          => '_sample_url_conditional',
				'type'        => 'url',
				'label'       => esc_html__('Conditional URL', 'wapic-field'),
				'description' => esc_html__('This field is required when the toggle is enabled.', 'wapic-field'),
				'value'       => get_option('_sample_url_conditional'),
				'condition'   => array(
					'field' => '_sample_toggle_conditional', // the controlling field
					'value' => 'yes', 				 // show this field only if toggle = "yes"
				),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_select_conditional',
				'type'        => 'select',
				'label'       => esc_html__('Conditional Select', 'wapic-field'),
				'description' => esc_html__('Visible only when the conditional toggle is enabled.', 'wapic-field'),
				'options'     => array(
					'option_1' => esc_html__('Option 1', 'wapic-field'),
					'option_2' => esc_html__('Option 2', 'wapic-field'),
					'option_3' => esc_html__('Option 3', 'wapic-field'),
				),
				'value'     => get_option('_sample_select_conditional'),
				'condition' => array(
					'field' => '_sample_toggle_conditional',
					'value' => 'yes',
				),
			)
		);
	}

	/**
	 * Advanced Tab Fields
	 */
	private function group_controls_advanced() {

		Field::add_control(
			array(
				'id'          => '_sample_image',
				'type'        => 'image',
				'label'       => esc_html__('Image Upload', 'wapic-field'),
				'description' => esc_html__('Upload a single image.', 'wapic-field'),
				'value'       => get_option('_sample_image'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_gallery',
				'type'        => 'gallery',
				'label'       => esc_html__('Image Gallery', 'wapic-field'),
				'description' => esc_html__('Upload multiple images.', 'wapic-field'),
				'value'       => get_option('_sample_gallery'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_file',
				'type'        => 'file',
				'label'       => esc_html__('File Upload', 'wapic-field'),
				'description' => esc_html__('Upload any file type.', 'wapic-field'),
				'value'       => get_option('_sample_file'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_toggle',
				'type'        => 'toggle',
				'label'       => esc_html__('Toggle Switch', 'wapic-field'),
				'description' => esc_html__('Simple on/off switch.', 'wapic-field'),
				'value'       => get_option('_sample_toggle'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_color',
				'type'        => 'color',
				'label'       => esc_html__('Color Picker', 'wapic-field'),
				'description' => esc_html__('Pick a color.', 'wapic-field'),
				'value'       => get_option('_sample_color'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_date',
				'type'        => 'date',
				'label'       => esc_html__('Date Picker', 'wapic-field'),
				'description' => esc_html__('Select a date.', 'wapic-field'),
				'value'       => get_option('_sample_date'),
			)
		);

		Field::add_control(
			array(
				'id'         => '_sample_select2',
				'type'       => 'select2',
				'label'      => esc_html__('Multi Select', 'wapic-field'),
				'value'      => get_option('_sample_select2'),
				'options'    => array(
					'option1'  => esc_html__('Option 1', 'wapic-field'),
					'option2'  => esc_html__('Option 2', 'wapic-field'),
					'option3'  => esc_html__('Option 3', 'wapic-field'),
					'option4'  => esc_html__('Option 4', 'wapic-field'),
					'option5'  => esc_html__('Option 5', 'wapic-field'),
					'option6'  => esc_html__('Option 6', 'wapic-field'),
					'option7'  => esc_html__('Option 7', 'wapic-field'),
					'option8'  => esc_html__('Option 8', 'wapic-field'),
					'option9'  => esc_html__('Option 9', 'wapic-field'),
					'option10' => esc_html__('Option 10', 'wapic-field'),
				),
				'attributes' => array(
					'multiple'    => true,
					'placeholder' => esc_html__('Select one or more options...', 'wapic-field'),
					'allow_clear' => true,
				),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_editor',
				'type'        => 'editor',
				'label'       => esc_html__('Content Editor', 'wapic-field'),
				'description' => esc_html__('Rich text editor with full WP editor tools.', 'wapic-field'),
				'value'       => get_option('_sample_editor'),
			)
		);
	}

	/**
	 * Sanitize and Save Options
	 */
	public function save() {

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

		foreach ($fields as $field_name => $field_type) {
			register_setting(
				$this->id,
				$field_name,
				array(
					'sanitize_callback' => function ($value, $option = '') use ($field_type, $field_name) {

						if (isset($_POST[$field_name . '_is_hidden']) && $_POST[$field_name . '_is_hidden'] === '1') {
							return Field::sanitize_value($field_type, $value);
						}

						$validation = Field::validate_value($field_type, $value);

						if (! empty($validation)) {
							add_settings_error($this->id, 'validation_error', $validation, 'error');
							return get_option($field_name, '');
						}

						return Field::sanitize_value($field_type, $value);
					},
				)
			);
		}
	}
}
new Example_Option();
