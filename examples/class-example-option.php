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
			esc_html__('Wapic Fields Options Example', 'wapic-fields'),
			esc_html__('Wapic Fields', 'wapic-fields'),
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
				'title' => esc_html__('Wapic Fields Options Example', 'wapic-fields'),
				'id'    => $this->id,
				'type'  => 'setting',
			)
		);

		// Start Tabs
		Field::start_controls_section(
			array(
				'general'     => esc_html__('General', 'wapic-fields'),
				'conditional' => esc_html__('Conditional', 'wapic-fields'),
				'advanced'    => esc_html__('Advanced', 'wapic-fields'),
			),
			array(
				'layout' => 'top',
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
				'value' => '<p>' . esc_html__('Change the mobile browser address bar color using these settings.', 'wapic-fields') . '</p>',
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_text',
				'type'  => 'text',
				'label' => esc_html__('Text Field', 'wapic-fields'),
				'value' => get_option('_sample_text'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_email',
				'type'  => 'email',
				'label' => esc_html__('Email Address', 'wapic-fields'),
				'value' => get_option('_sample_email'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_phone',
				'type'  => 'phone',
				'label' => esc_html__('Phone Number', 'wapic-fields'),
				'value' => get_option('_sample_phone'),
			)
		);

		Field::add_control(
			array(
				'id'         => '_sample_number',
				'type'       => 'number',
				'label'      => esc_html__('Number Field', 'wapic-fields'),
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
				'label' => esc_html__('Website URL', 'wapic-fields'),
				'value' => get_option('_sample_url'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_textarea',
				'type'  => 'textarea',
				'label' => esc_html__('Textarea', 'wapic-fields'),
				'value' => get_option('_sample_textarea'),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_select',
				'type'    => 'select',
				'label'   => esc_html__('Dropdown Select', 'wapic-fields'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-fields'),
					'option_2' => esc_html__('Option 2', 'wapic-fields'),
					'option_3' => esc_html__('Option 3', 'wapic-fields'),
				),
				'value'   => get_option('_sample_select'),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_checkbox',
				'type'    => 'checkbox',
				'label'   => esc_html__('Checkbox Options', 'wapic-fields'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-fields'),
					'option_2' => esc_html__('Option 2', 'wapic-fields'),
					'option_3' => esc_html__('Option 3', 'wapic-fields'),
				),
				'value' => get_option('_sample_checkbox'),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_radio',
				'type'    => 'radio',
				'label'   => esc_html__('Radio Options', 'wapic-fields'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-fields'),
					'option_2' => esc_html__('Option 2', 'wapic-fields'),
					'option_3' => esc_html__('Option 3', 'wapic-fields'),
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
				'label'       => esc_html__('Required Text Field', 'wapic-fields'),
				'description' => esc_html__('This field must not be empty.', 'wapic-fields'),
				'value'       => get_option('_sample_text_required'),
				'required'    => true,
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_price',
				'type'  => 'number',
				'label' => esc_html__('Regular Price', 'wapic-fields'),
				'class' => 'regular-price',
				'value' => get_option('_sample_price'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_sale_price',
				'type'        => 'number',
				'label'       => esc_html__('Sale Price', 'wapic-fields'),
				'class'       => 'sale-price',
				'description' => esc_html__('Sale price must be lower than the regular price.', 'wapic-fields'),
				'value'       => get_option('_sample_sale_price'),
			)
		);

		Field::add_control(array(
			'type'  => 'heading',
			'label' => esc_html__('Cascading Logic Example (A -> B -> C)', 'wapic-fields'),
		));

		Field::add_control(array(
			'id'    => '_sample_input_a',
			'type'  => 'toggle',
			'label' => esc_html__('Input A (Active B)', 'wapic-fields'),
			'value' => get_option('_sample_input_a', 'no'),
		));

		Field::add_control(
			array(
				'id'    => '_sample_input_b',
				'type'  => 'toggle',
				'label' => esc_html__('Input B (Visible if A is ON, Active C)', 'wapic-fields'),
				'value' => get_option('_sample_input_b', 'no'),
				'condition' => array(
					'field' => '_sample_input_a',
					'value' => 'yes',
				),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_input_c',
				'type'  => 'text',
				'label' => esc_html__('Input C (Visible if B is ON)', 'wapic-fields'),
				'value' => get_option('_sample_input_c'),
				'description' => esc_html__('If you turn off Input A, both B and C will hide.', 'wapic-fields'),
				'condition' => array(
					'field' => '_sample_input_b',
					'value' => 'yes',
				),
			)
		);

		Field::add_control(array(
			'type' => 'separator',
		));

		Field::add_control(array(
			'type'  => 'heading',
			'label' => esc_html__('Multi-Condition Logic (AND / OR)', 'wapic-fields'),
		));

		Field::add_control(
			array(
				'id'      => '_sample_mc_type',
				'type'    => 'select',
				'label'   => esc_html__('Select Type', 'wapic-fields'),
				'options' => array(
					'text'  => esc_html__('Text', 'wapic-fields'),
					'image' => esc_html__('Image', 'wapic-fields'),
					'both'  => esc_html__('Both', 'wapic-fields'),
				),
				'value'   => get_option('_sample_mc_type', 'text'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_mc_advanced',
				'type'  => 'toggle',
				'label' => esc_html__('Show Advanced', 'wapic-fields'),
				'value' => get_option('_sample_mc_advanced', 'no'),
			)
		);

		// AND Example
		Field::add_control(
			array(
				'id'    => '_sample_mc_and_field',
				'type'  => 'text',
				'label' => esc_html__('Field AND Relation', 'wapic-fields'),
				'description' => esc_html__('Visible only if Type is "Both" AND Advanced is "ON"', 'wapic-fields'),
				'value' => get_option('_sample_mc_and_field'),
				'condition' => array(
					'relation' => 'AND',
					array(
						'field' => '_sample_mc_type',
						'value' => 'both',
					),
					array(
						'field' => '_sample_mc_advanced',
						'value' => 'yes',
					),
				),
			)
		);

		// OR Example
		Field::add_control(
			array(
				'id'    => '_sample_mc_or_field',
				'type'  => 'text',
				'label' => esc_html__('Field OR Relation', 'wapic-fields'),
				'description' => esc_html__('Visible if Type is "Image" OR Type is "Both"', 'wapic-fields'),
				'value' => get_option('_sample_mc_or_field'),
				'condition' => array(
					'relation' => 'OR',
					array(
						'field' => '_sample_mc_type',
						'value' => 'image',
					),
					array(
						'field' => '_sample_mc_type',
						'value' => 'both',
					),
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
				'label'       => esc_html__('Image Upload', 'wapic-fields'),
				'description' => esc_html__('Upload a single image.', 'wapic-fields'),
				'value'       => get_option('_sample_image'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_gallery',
				'type'        => 'gallery',
				'label'       => esc_html__('Image Gallery', 'wapic-fields'),
				'description' => esc_html__('Upload multiple images.', 'wapic-fields'),
				'value'       => get_option('_sample_gallery'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_file',
				'type'        => 'file',
				'label'       => esc_html__('File Upload', 'wapic-fields'),
				'description' => esc_html__('Upload any file type.', 'wapic-fields'),
				'value'       => get_option('_sample_file'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_toggle',
				'type'        => 'toggle',
				'label'       => esc_html__('Toggle Switch', 'wapic-fields'),
				'description' => esc_html__('Simple on/off switch.', 'wapic-fields'),
				'value'       => get_option('_sample_toggle'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_color',
				'type'        => 'color',
				'label'       => esc_html__('Color Picker', 'wapic-fields'),
				'description' => esc_html__('Pick a color.', 'wapic-fields'),
				'value'       => get_option('_sample_color'),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_date',
				'type'        => 'date',
				'label'       => esc_html__('Date Picker', 'wapic-fields'),
				'description' => esc_html__('Select a date.', 'wapic-fields'),
				'value'       => get_option('_sample_date'),
			)
		);

		Field::add_control(
			array(
				'id'         => '_sample_select2',
				'type'       => 'select2',
				'label'      => esc_html__('Multi Select', 'wapic-fields'),
				'value'      => get_option('_sample_select2'),
				'options'    => array(
					'option1'  => esc_html__('Option 1', 'wapic-fields'),
					'option2'  => esc_html__('Option 2', 'wapic-fields'),
					'option3'  => esc_html__('Option 3', 'wapic-fields'),
					'option4'  => esc_html__('Option 4', 'wapic-fields'),
					'option5'  => esc_html__('Option 5', 'wapic-fields'),
					'option6'  => esc_html__('Option 6', 'wapic-fields'),
					'option7'  => esc_html__('Option 7', 'wapic-fields'),
					'option8'  => esc_html__('Option 8', 'wapic-fields'),
					'option9'  => esc_html__('Option 9', 'wapic-fields'),
					'option10' => esc_html__('Option 10', 'wapic-fields'),
				),
				'attributes' => array(
					'multiple'    => true,
					'placeholder' => esc_html__('Select one or more options...', 'wapic-fields'),
					'allow_clear' => true,
				),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_editor',
				'type'        => 'editor',
				'label'       => esc_html__('Content Editor', 'wapic-fields'),
				'description' => esc_html__('Rich text editor with full WP editor tools.', 'wapic-fields'),
				'value'       => get_option('_sample_editor'),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_slider',
				'type'  => 'slider',
				'label' => esc_html__('Slider Number', 'wapic-fields'),
				'description' => esc_html__('Drag to select a numeric value.', 'wapic-fields'),
				'value' => get_option('_sample_slider', 50),
				'attributes' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 5,
				),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_image_select_row',
				'type'    => 'image_select',
				'label'   => esc_html__('Image Select', 'wapic-fields'),
				'description' => esc_html__('Horizontal layout for visual options.', 'wapic-fields'),
				'options' => array(
					'option_1' => array(
						'label' => 'Standard',
						'image' => 'https://placehold.co/300x300',
					),
					'option_2' => array(
						'label' => 'Compact',
						'image' => 'https://placehold.co/300x300',
					),
					'option_3' => array(
						'label' => 'List',
						'image' => 'https://placehold.co/300x300',
					),
				),
				'value' => get_option('_sample_image_select_row'),
				'attributes' => array(
					'layout' => 'row',
				),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_code',
				'type'        => 'code_editor',
				'label'       => esc_html__('Code Editor (HTML)', 'wapic-fields'),
				'description' => esc_html__('Edit HTML with syntax highlighting.', 'wapic-fields'),
				'value'       => get_option('_sample_code', '<p>Hello World</p>'),
				'attributes'  => array(
					'language' => 'text/html',
				),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_code_css',
				'type'        => 'code_editor',
				'label'       => esc_html__('Code Editor (CSS)', 'wapic-fields'),
				'description' => esc_html__('Edit CSS with syntax highlighting.', 'wapic-fields'),
				'value'       => get_option('_sample_code_css', 'body { background: #fff; }'),
				'attributes'  => array(
					'language' => 'text/css',
				),
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
			'_sample_input_a'            => 'toggle',
			'_sample_input_b'            => 'toggle',
			'_sample_input_c'            => 'text',
			'_sample_mc_type'            => 'select',
			'_sample_mc_advanced'        => 'toggle',
			'_sample_mc_and_field'       => 'text',
			'_sample_mc_or_field'        => 'text',
			'_sample_image'              => 'image',
			'_sample_gallery'            => 'gallery',
			'_sample_file'               => 'file',
			'_sample_toggle'             => 'toggle',
			'_sample_date'               => 'date',
			'_sample_color'              => 'color',
			'_sample_select2'            => 'select2',
			'_sample_editor'             => 'editor',
			'_sample_slider'             => 'slider',
			'_sample_image_select_row'   => 'image_select',
			'_sample_code'               => 'code_editor',
			'_sample_code_css'           => 'code_editor',
		);

		// Per-field attributes (e.g. min/max) so numeric values are clamped
		// server-side to the same constraints declared at render time.
		$field_attributes = array(
			'_sample_number' => array('min' => 0, 'max' => 100),
			'_sample_slider' => array('min' => 0, 'max' => 100),
		);

		foreach ($fields as $field_name => $field_type) {
			$attributes = isset($field_attributes[$field_name]) ? $field_attributes[$field_name] : array();

			register_setting(
				$this->id,
				$field_name,
				array(
					'sanitize_callback' => function ($value, $option = '') use ($field_type, $field_name, $attributes) {

						if (isset($_POST[$field_name . '_is_hidden']) && $_POST[$field_name . '_is_hidden'] === '1') {
							return Field::sanitize_value($field_type, $value, $attributes);
						}

						$validation = Field::validate_value($field_type, $value);

						if (! empty($validation)) {
							add_settings_error($this->id, 'validation_error', $validation, 'error');
							return get_option($field_name, '');
						}

						return Field::sanitize_value($field_type, $value, $attributes);
					},
				)
			);
		}
	}
}
new Example_Option();
