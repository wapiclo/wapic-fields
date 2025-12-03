<?php
/**
 * Example: Store all settings inside ONE database row as ARRAY
 * - Efficient (only 1 db row)
 * - Fast to load
 * - Easy to import/export
 */

namespace Wapic_Fields\Example;

if ( ! defined('ABSPATH')) exit;

use Wapic_Fields\Field;

class Example_Option_Array {

	private $id          = 'wapic-field-array';
	private $option_name = '_sample_wapic_field_settings'; // stores all settings as array

	public function __construct() {
		add_action('admin_menu', array($this, 'register'));
		add_action('admin_init', array($this, 'save'));
	}

	/**
	 * Register admin page
	 */
	public function register() {
		add_menu_page(
			esc_html__('Wapic Fields — Array Storage', 'wapic-field'),
			esc_html__('Wapic Array', 'wapic-field'),
			'manage_options',
			$this->id,
			array($this, 'render'),
			'dashicons-admin-generic',
			9997
		);
	}

	/**
	 * Get a field value from array
	 */
	public function get_option($key, $default = null) {
		$options = get_option($this->option_name, array());
		return isset($options[$key]) ? $options[$key] : $default;
	}

	/**
	 * Render all fields
	 */
	public function render() {

		Field::start_controls_panel(array(
			'title' => esc_html__('Wapic Fields — Array Storage Example', 'wapic-field'),
			'id'    => $this->id,
			'type'  => 'setting',
		));

		Field::add_control(array(
			'type'  => 'html',
			'value' => '<p><strong>' . esc_html__('All fields below are stored in a single database row.', 'wapic-field') . '</strong></p>',
		));

		/* Text Fields */
		Field::add_control(array(
			'id'    => 'site_title',
			'type'  => 'text',
			'label' => esc_html__('Site Title', 'wapic-field'),
			'value' => $this->get_option('site_title', ''),
		));

		Field::add_control(array(
			'id'    => 'site_email',
			'type'  => 'email',
			'label' => esc_html__('Site Email', 'wapic-field'),
			'value' => $this->get_option('site_email', ''),
		));

		Field::add_control(array(
			'id'    => 'site_phone',
			'type'  => 'phone',
			'label' => esc_html__('Site Phone', 'wapic-field'),
			'value' => $this->get_option('site_phone', ''),
		));

		/* Select */
		Field::add_control(array(
			'id'      => 'site_status',
			'type'    => 'select',
			'label'   => esc_html__('Site Status', 'wapic-field'),
			'options' => array(
				'active'      => esc_html__('Active', 'wapic-field'),
				'maintenance' => esc_html__('Maintenance', 'wapic-field'),
				'offline'     => esc_html__('Offline', 'wapic-field'),
			),
			'value' => $this->get_option('site_status', 'active'),
		));

		/* Toggle */
		 Field::add_control(array(
			'id'    => 'enable_comments',
			'type'  => 'toggle',
			'label' => esc_html__('Enable Comments', 'wapic-field'),
			'value' => $this->get_option('enable_comments', 'no'),
		));

		/* Checkbox */
		Field::add_control(array(
			'id'      => 'allowed_features',
			'type'    => 'checkbox',
			'label'   => esc_html__('Allowed Features', 'wapic-field'),
			'options' => array(
				'api'       => esc_html__('API Access', 'wapic-field'),
				'analytics' => esc_html__('Analytics', 'wapic-field'),
				'cache'     => esc_html__('Cache', 'wapic-field'),
				'cdn'       => esc_html__('CDN', 'wapic-field'),
			),
			'value' => $this->get_option('allowed_features', array()),
		));

		/* Image */
		Field::add_control(array(
			'id'          => 'site_logo',
			'type'        => 'image',
			'label'       => esc_html__('Site Logo', 'wapic-field'),
			'description' => esc_html__('Upload or select a site logo.', 'wapic-field'),
			'value'       => $this->get_option('site_logo', ''),
		));

		/* Color Picker */
		Field::add_control(array(
			'id'          => 'brand_color',
			'type'        => 'color',
			'label'       => esc_html__('Brand Color', 'wapic-field'),
			'description' => esc_html__('Primary brand color.', 'wapic-field'),
			'value'       => $this->get_option('brand_color', '#0073aa'),
		));

		/* Select2 */
		Field::add_control(array(
			'id'       => 'social_networks',
			'type'     => 'select2',
			'label'    => esc_html__('Social Networks', 'wapic-field'),
			'options'  => array(
				'facebook'  => esc_html__('Facebook', 'wapic-field'),
				'twitter'   => esc_html__('Twitter', 'wapic-field'),
				'instagram' => esc_html__('Instagram', 'wapic-field'),
				'linkedin'  => esc_html__('LinkedIn', 'wapic-field'),
				'youtube'   => esc_html__('YouTube', 'wapic-field'),
				'tiktok'    => esc_html__('TikTok', 'wapic-field'),
			),
			'attributes' => array(
				'multiple'    => true,
				'placeholder' => esc_html__('Select social networks...', 'wapic-field'),
				'allow_clear' => true,
			),
			'value' => $this->get_option('social_networks', array()),
		));

		/* Custom CSS */
		Field::add_control(array(
			'id'          => 'custom_css',
			'type'        => 'textarea',
			'label'       => esc_html__('Custom CSS', 'wapic-field'),
			'description' => esc_html__('Add custom CSS.', 'wapic-field'),
			'value'       => $this->get_option('custom_css', ''),
		));

		Field::end_controls_panel(array('type' => 'setting'));
	}

	/**
	 * Register setting (array storage)
	 */
	public function save() {

		register_setting(
			$this->id,
			$this->option_name,
			array(
				'type'              => 'array',
				'sanitize_callback' => array($this, 'sanitize_options'),
			)
		);
	}

	/**
	 * Sanitize & save all fields inside a single array
	 */
	public function sanitize_options($input) {

		$output = get_option($this->option_name, array());

		$fields = array(
			'site_title'        => 'text',
			'site_email'        => 'email',
			'site_phone'        => 'phone',
			'site_status'       => 'select',
			'enable_comments'   => 'toggle',
			'allowed_features'  => 'checkbox',
			'site_logo'         => 'image',
			'brand_color'       => 'color',
			'social_networks'   => 'select2',
			'custom_css'        => 'textarea',
		);

		foreach ($fields as $field => $type) {

			// Toggle
			if ($type === 'toggle') {
				$output[$field] = (isset($_POST[$field]) && $_POST[$field] === 'yes') ? 'yes' : 'no';
				continue;
			}

			// Checkbox (array)
			if ($type === 'checkbox') {
				$output[$field] = isset($_POST[$field]) ? (array) $_POST[$field] : array();
				continue;
			}

			// Other fields
			if (isset($_POST[$field])) {

				$value = $_POST[$field];

				// Skip validation if hidden
				if (isset($_POST[$field . '_is_hidden']) && $_POST[$field . '_is_hidden'] === '1') {
					$output[$field] = Field::sanitize_value($type, $value);
					continue;
				}

				// Validate
				$validation = Field::validate_value($type, $value);

				if (empty($validation)) {
					$output[$field] = Field::sanitize_value($type, $value);
				} else {
					add_settings_error(
						$this->id,
						'validation_error',
						sprintf('%s: %s', ucwords(str_replace('_', ' ', $field)), $validation),
						'error'
					);
				}
			}
		}

		return $output;
	}
}

new Example_Option_Array();