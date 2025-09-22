<?php

namespace Wapic_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomTaxonomy extends \Wapic_Fields\Field {
	private $id = 'category';

	public function __construct() {
		// Add field
		add_action( 'category_add_form_fields', array( $this, 'add_field' ) );
		add_action( 'category_edit_form_fields', array( $this, 'edit_field' ) );

		// Save field
		add_action( 'created_category', array( $this, 'save_field' ), 10, 2 );
		add_action( 'edited_category', array( $this, 'save_field' ), 10, 2 );

		// Notices
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Display admin notices for taxonomy
	 */
	public function notices() {

		// Hanya tampil di halaman taxonomy category
		if ( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] !== $this->id ) {
			return;
		}
		// Get stored messages
		$messages = get_transient( "{$this->id}_taxonomy_messages" );

		if ( $messages && is_array( $messages ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			foreach ( $messages as $message ) {
				echo '<p>' . esc_html( $message ) . '</p>';
			}
			echo '</div>';

			// Clear the transient
			delete_transient( "{$this->id}_taxonomy_messages" );
		}
	}

	/**
	 * Field for Add Term screen
	 */
	public function add_field( $taxonomy ) {
		$this->render_field();
	}

	/**
	 * Field for Edit Term screen
	 */
	public function edit_field( $term ) {
		$this->render_field( $term->term_id );
	}

	/**
	 * Common controls
	 */
	private function render_field( $term_id = 0 ) {
		$this->add_control(
			array(
				'id'    => '_sample_text',
				'type'  => 'text',
				'label' => 'Text',
				'value' => $term_id ? get_term_meta( $term_id, '_sample_text', true ) : '',
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_number',
				'type'  => 'number',
				'label' => 'Number',
				'value' => $term_id ? get_term_meta( $term_id, '_sample_number', true ) : '',
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_email',
				'type'  => 'email',
				'label' => 'Email',
				'value' => $term_id ? get_term_meta( $term_id, '_sample_email', true ) : '',
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_textarea',
				'type'  => 'textarea',
				'label' => 'Textarea',
				'value' => $term_id ? get_term_meta( $term_id, '_sample_textarea', true ) : '',
			)
		);

		$this->add_control(
			array(
				'id'          => '_sample_image',
				'type'        => 'image',
				'label'       => 'Image',
				'description' => 'Upload an image',
				'value'       => $term_id ? get_term_meta( $term_id, '_sample_image', true ) : '',
			)
		);

		$this->add_control(
			array(
				'id'    => '_sample_color',
				'type'  => 'color',
				'label' => 'Color',
				'value' => $term_id ? get_term_meta( $term_id, '_sample_color', true ) : '',
			)
		);
	}

	/**
	 * Save field
	 */
	public function save_field( $term_id, $tt_id ) {
		$fields = array(
			'_sample_text'     => 'text',
			'_sample_textarea' => 'textarea',
			'_sample_image'    => 'image',
			'_sample_color'    => 'color',
			'_sample_number'   => 'number',
			'_sample_email'    => 'email',
		);

		$error_message = array();

		foreach ( $fields as $key => $type ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value      = $_POST[ $key ];
				$validation = $this->validate_value( $type, $value );

				if ( ! empty( $validation ) ) {
					$error_message[] = $validation;
				} else {
					$sanitized = $this->sanitize_value( $type, $value );
					update_term_meta( $term_id, $key, $sanitized );
				}
			} else {
				delete_term_meta( $term_id, $key );
			}
		}

		// Simpan semua error sekali saja
		if ( ! empty( $error_message ) ) {
			set_transient( "{$this->id}_taxonomy_messages", $error_message, 30 );
		}
	}
}

// Init class
new CustomTaxonomy();
