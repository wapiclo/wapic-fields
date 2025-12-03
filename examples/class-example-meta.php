<?php
/**
 * Example Meta Box using Wapic Fields
 *
 * This file demonstrates how to create a custom meta box
 * with various field types using the Wapic Fields library.
 *
 * @package Wapic_Fields
 */
namespace Wapic_Fields\Example;

if (! defined('ABSPATH')) {
	exit;
}

use Wapic_Fields\Field;

class Example_Meta {

	private $id = 'wapic-fields-metabox';

	public function __construct() {
		add_action('add_meta_boxes', array($this, 'register'));
		add_action('save_post', array($this, 'save'));
		add_action('admin_notices', array($this, 'notices'));
	}

	/**
	 * Display admin notices for the metabox.
	 *
	 * Shows validation messages when saving fails.
	 */
	public function notices() {

		$current_screen = get_current_screen();

		if ($current_screen->base !== 'post' && $current_screen->base !== 'edit') {
			return;
		}

		$messages = get_transient("{$this->id}_metabox_messages");

		if ($messages && is_array($messages)) {
			echo '<div class="notice notice-error is-dismissible">';
			foreach ($messages as $message) {
				echo '<p>' . esc_html($message) . '</p>';
			}
			echo '</div>';
			delete_transient("{$this->id}_metabox_messages");
		}
	}

	/**
	 * Register the metabox for Posts.
	 */
	public function register() {
		add_meta_box(
			$this->id,
			esc_html__('Wapic Fields Example', 'wapic-fields'),
			array($this, 'render'),
			'post',
			'normal',
			'default'
		);
	}

	/**
	 * Render the metabox content and fields.
	 */
	public function render($post) {

		wp_nonce_field("{$this->id}_metabox_save", "{$this->id}_metabox_nonce");

		Field::start_controls_panel(
			array(
				'title' => esc_html__('Wapic Fields Example', 'wapic-fields'),
				'id'    => $this->id,
				'type'  => 'metabox',
			)
		);

		// Tabs
		Field::start_controls_section(
			array(
				'general'     => esc_html__('General', 'wapic-fields'),
				'conditional' => esc_html__('Conditional', 'wapic-fields'),
				'advanced'    => esc_html__('Advanced', 'wapic-fields'),
			)
		);

		// General Tab
		Field::start_controls_group(array('id' => 'general'));
		$this->group_controls_general($post);
		Field::end_controls_group();

		// Conditional Tab
		Field::start_controls_group(array('id' => 'conditional'));
		$this->group_controls_conditional($post);
		Field::end_controls_group();

		// Advanced Tab
		Field::start_controls_group(array('id' => 'advanced'));
		$this->group_controls_advanced($post);
		Field::end_controls_group();

		// End Tabs
		Field::end_controls_section();

		Field::end_controls_panel(array('type' => 'metabox'));
	}

	/**
	 * General Fields
	 */
	private function group_controls_general($post) {

		Field::add_control(
			array(
				'type'  => 'html',
				'value' => '<p>' . esc_html__('This section contains basic example fields.', 'wapic-fields') . '</p>',
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_text',
				'type'  => 'text',
				'label' => esc_html__('Text Field', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_text', true),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_email',
				'type'  => 'email',
				'label' => esc_html__('Email Address', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_email', true),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_phone',
				'type'  => 'phone',
				'label' => esc_html__('Phone Number', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_phone', true),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_number',
				'type'  => 'number',
				'label' => esc_html__('Number', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_number', true),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_url',
				'type'  => 'url',
				'label' => esc_html__('URL', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_url', true),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_textarea',
				'type'  => 'textarea',
				'label' => esc_html__('Textarea', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_textarea', true),
			)
		);

		Field::add_control(
			array(
				'id'      => '_sample_select',
				'type'    => 'select',
				'label'   => esc_html__('Select Field', 'wapic-fields'),
				'options' => array(
					'option_1' => esc_html__('Option 1', 'wapic-fields'),
					'option_2' => esc_html__('Option 2', 'wapic-fields'),
					'option_3' => esc_html__('Option 3', 'wapic-fields'),
				),
				'value'   => get_post_meta($post->ID, '_sample_select', true),
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
				'value'   => get_post_meta($post->ID, '_sample_checkbox', true),
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
				'value'   => get_post_meta($post->ID, '_sample_radio', true),
			)
		);
	}

	/**
	 * Conditional Fields
	 */
	private function group_controls_conditional($post) {

		Field::add_control(
			array(
				'id'          => '_sample_text_required',
				'type'        => 'text',
				'label'       => esc_html__('Required Text Field', 'wapic-fields'),
				'description' => esc_html__('This field is required.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_text_required', true),
				'required'    => true,
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_price',
				'type'  => 'number',
				'label' => esc_html__('Regular Price', 'wapic-fields'),
				'class' => 'regular-price',
				'value' => get_post_meta($post->ID, '_sample_price', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_sale_price',
				'type'        => 'number',
				'label'       => esc_html__('Sale Price', 'wapic-fields'),
				'class'       => 'sale-price',
				'description' => esc_html__('Sale price must be lower than regular price.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_sale_price', true),
			)
		);

		Field::add_control(
			array(
				'id'    => '_sample_toggle_conditional',
				'type'  => 'toggle',
				'label' => esc_html__('Enable Conditional Field', 'wapic-fields'),
				'value' => get_post_meta($post->ID, '_sample_toggle_conditional', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_select_conditional',
				'type'        => 'select',
				'label'       => esc_html__('Conditional Select', 'wapic-fields'),
				'description' => esc_html__('This field is required when the toggle is enabled.', 'wapic-fields'),
				'options'     => array(
					'option_1' => esc_html__('Option 1', 'wapic-fields'),
					'option_2' => esc_html__('Option 2', 'wapic-fields'),
					'option_3' => esc_html__('Option 3', 'wapic-fields'),
				),
				'value'       => get_post_meta($post->ID, '_sample_select_conditional', true),
				'condition'   => array(
					'field' => '_sample_toggle_conditional',
					'value' => 'yes',
				),
			)
		);
	}

	/**
	 * Advanced Fields
	 */
	private function group_controls_advanced($post) {

		Field::add_control(
			array(
				'id'          => '_sample_image',
				'type'        => 'image',
				'label'       => esc_html__('Image Field', 'wapic-fields'),
				'description' => esc_html__('Upload or select an image.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_image', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_gallery',
				'type'        => 'gallery',
				'label'       => esc_html__('Gallery Field', 'wapic-fields'),
				'description' => esc_html__('Select multiple images for the gallery.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_gallery', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_file',
				'type'        => 'file',
				'label'       => esc_html__('File Upload', 'wapic-fields'),
				'description' => esc_html__('Upload a file.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_file', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_toggle',
				'type'        => 'toggle',
				'label'       => esc_html__('Toggle Field', 'wapic-fields'),
				'description' => esc_html__('Enable or disable the toggle.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_toggle', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_date',
				'type'        => 'date',
				'label'       => esc_html__('Date Picker', 'wapic-fields'),
				'description' => esc_html__('Select a date.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_date', true),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_color',
				'type'        => 'color',
				'label'       => esc_html__('Color Picker', 'wapic-fields'),
				'description' => esc_html__('Select a color.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_color', true),
			)
		);

		Field::add_control(
			array(
				'id'         => '_sample_select2',
				'type'       => 'select2',
				'label'      => esc_html__('Select2 Field', 'wapic-fields'),
				'value'      => get_post_meta($post->ID, '_sample_select2', true),
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
					'placeholder' => esc_html__('Select options...', 'wapic-fields'),
					'allow_clear' => true,
				),
			)
		);

		Field::add_control(
			array(
				'id'          => '_sample_editor',
				'type'        => 'editor',
				'label'       => esc_html__('Content Editor', 'wapic-fields'),
				'description' => esc_html__('WordPress rich text editor field.', 'wapic-fields'),
				'value'       => get_post_meta($post->ID, '_sample_editor', true),
			)
		);
	}

	/**
	 * Save meta box fields with sanitization and validation
	 */
	public function save($post_id) {

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (! current_user_can('edit_post', $post_id)) {
			return;
		}

		if (! isset($_POST["{$this->id}_metabox_nonce"]) ||
			! wp_verify_nonce($_POST["{$this->id}_metabox_nonce"], "{$this->id}_metabox_save")
		) {
			return;
		}

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

		$error_message = array();

		foreach ($fields as $key => $type) {

			if (isset($_POST[$key])) {

				$value      = $_POST[$key];
				$validation = Field::validate_value($type, $value);

				if (! empty($validation)) {
					$error_message[] = $validation;
				} else {
					$sanitized = Field::sanitize_value($type, $value);
					update_post_meta($post_id, $key, $sanitized);
				}

			} elseif ($type === 'toggle') {
				update_post_meta($post_id, $key, 'no');

			} elseif ($type === 'checkbox') {
				update_post_meta($post_id, $key, array());

			} else {
				delete_post_meta($post_id, $key);
			}
		}

		if (! empty($error_message)) {
			set_transient("{$this->id}_metabox_messages", $error_message, 30);
		}
	}
}

// Initialize the meta box
new Example_Meta();
