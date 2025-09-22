<?php

namespace Wapic_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Example_Meta extends \Wapic_Fields\Field {

	private $id = 'wapic-fields-metabox';

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Display admin notices for meta box
	 */
	public function notices() {
		// Only show on post edit screen
		if ( get_current_screen()->base !== 'post' && get_current_screen()->base !== 'edit' ) {
			return;
		}

		// Get any stored messages
		$messages = get_transient( "{$this->id}_metabox_messages" );

		if ( $messages && is_array( $messages ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			foreach ( $messages as $message ) {
				echo '<p>' . esc_html( $message ) . '</p>';
			}
			echo '</div>';
			// Clear the transient
			delete_transient( "{$this->id}_metabox_messages" );
		}
	}

	/**
	 * Register the metabox
	 */
	public function register() {
		add_meta_box(
			$this->id, // ID Metabox
			'Wapic Fields Example', // Title Metabox
			array( $this, 'render' ), // Render the metabox field
			'post', // Post type
			'normal', // Position
			'default' // Priority
		);
	}

	/**
	 * Render the fields
	 */
	public function render( $post ) {
		wp_nonce_field( "{$this->id}_metabox_save", "{$this->id}_metabox_nonce" );

		$this->start_controls_panel(
			array(
				'title' => 'Wapic Fields Example',
				'id'    => $this->id,
				'type'  => 'metabox',
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

		$this->group_controls_general( $post );

		$this->end_controls_group();

		// Conditional Tab
		$this->start_controls_group(
			array(
				'id' => 'conditional',
			)
		);

		$this->group_controls_conditional( $post );

		$this->end_controls_group();

		// Advanced Tab
		$this->start_controls_group(
			array(
				'id' => 'advanced',
			)
		);

		$this->group_controls_advanced( $post );

		$this->end_controls_group();

		// End Tabs
		$this->end_controls_section();

		$this->end_controls_panel(
			array(
				'type' => 'metabox',
			)
		);
	}

	/**
	 * Render General Tab Fields
	 */
	private function group_controls_general( $post ) {
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
				'value' => get_post_meta( $post->ID, '_sample_text', true ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_email',
				'type'  => 'email',
				'label' => 'Email',
				'value' => get_post_meta( $post->ID, '_sample_email', true ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_phone',
				'type'  => 'phone',
				'label' => 'Phone',
				'value' => get_post_meta( $post->ID, '_sample_phone', true ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_number',
				'type'  => 'number',
				'label' => 'Number',
				'value' => get_post_meta( $post->ID, '_sample_number', true ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_url',
				'type'  => 'url',
				'label' => 'URL',
				'value' => get_post_meta( $post->ID, '_sample_url', true ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_textarea',
				'type'  => 'textarea',
				'label' => 'Textarea',
				'value' => get_post_meta( $post->ID, '_sample_textarea', true ),
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
				'value'   => get_post_meta( $post->ID, '_sample_select', true ),
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
				'value'   => get_post_meta( $post->ID, '_sample_checkbox', true ),
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
				'value'   => get_post_meta( $post->ID, '_sample_radio', true ),
			)
		);
	}

	/**
	 * Render Conditional Tab Fields
	 */
	private function group_controls_conditional( $post ) {

		$this->add_control(
			array(
				'id'          => '_sample_text_required',
				'type'        => 'text',
				'label'       => 'Regular Text Required',
				'description' => 'Regular text field.',
				'value'       => get_post_meta( $post->ID, '_sample_text_required', true ),
				'required'    => true,
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_price',
				'type'  => 'number',
				'label' => 'Price',
				'class' => 'regular-price',
				'value' => get_post_meta( $post->ID, '_sample_price', true ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_sale_price',
				'type'        => 'number',
				'label'       => 'Sale Price',
				'class'       => 'sale-price',
				'description' => 'Sale price must be less than regular price',
				'value'       => get_post_meta( $post->ID, '_sample_sale_price', true ),
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_toggle_conditional',
				'type'  => 'toggle',
				'label' => 'Toggle Conditional',
				'value' => get_post_meta( $post->ID, '_sample_toggle_conditional', true ),
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
				'value'       => get_post_meta( $post->ID, '_sample_select_conditional', true ),
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
	private function group_controls_advanced( $post ) {

		$this->add_control(
			array(
				'id'          => '_sample_image',
				'type'        => 'image',
				'label'       => 'Image',
				'description' => 'Image field.',
				'value'       => get_post_meta( $post->ID, '_sample_image', true ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_gallery',
				'type'        => 'gallery',
				'label'       => 'Gallery',
				'description' => 'Gallery field.',
				'value'       => get_post_meta( $post->ID, '_sample_gallery', true ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_file',
				'type'        => 'file',
				'label'       => 'File',
				'description' => 'File field.',
				'value'       => get_post_meta( $post->ID, '_sample_file', true ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_toggle',
				'type'        => 'toggle',
				'label'       => 'Toggle',
				'description' => 'Toggle field.',
				'value'       => get_post_meta( $post->ID, '_sample_toggle', true ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_date',
				'type'        => 'date',
				'label'       => 'Date Picker',
				'description' => 'Date Picker field.',
				'value'       => get_post_meta( $post->ID, '_sample_date', true ),
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_color',
				'type'        => 'color',
				'label'       => 'Color Picker',
				'description' => 'Color Picker field.',
				'value'       => get_post_meta( $post->ID, '_sample_color', true ),
			)
		);

		$this->add_control(
			array(
				'id'         => '_sample_select2',
				'type'       => 'select2',
				'label'      => 'Select Options',
				'value'      => get_post_meta( $post->ID, '_sample_select2', true ),
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
				'value'       => get_post_meta( $post->ID, '_sample_editor', true ),
			)
		);
	}

	/**
	 * Save metabox data with validation
	 */
	public function save( $post_id ) {

		// Autosave check
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Permission check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Nonce check
		if ( ! isset( $_POST[ "{$this->id}_metabox_nonce" ] ) || ! wp_verify_nonce( $_POST[ "{$this->id}_metabox_nonce" ], "{$this->id}_metabox_save" ) ) {
			return;
		}

		// Get fields id
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

		// Field validation
		// Store sanitized value in database
		foreach ( $fields as $key => $type ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value      = $_POST[ $key ];
				$validation = $this->validate_value( $type, $value );

				if ( ! empty( $validation ) ) {
					$error_message[] = $validation;
				} else {
					$sanitized = $this->sanitize_value( $type, $value );
					update_post_meta( $post_id, $key, $sanitized );
				}
			} elseif ( $type === 'toggle' ) {
					update_post_meta( $post_id, $key, 'no' );
			} elseif ( $type === 'checkbox' ) {
				update_post_meta( $post_id, $key, array() );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}

		// Set error message
		if ( ! empty( $error_message ) ) {
			set_transient( "{$this->id}_metabox_messages", $error_message, 30 );
		}
	}
}
// Initialize the meta box
new Example_Meta();
