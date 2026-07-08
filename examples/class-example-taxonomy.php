<?php

/**
 * CustomTaxonomy
 *
 * Example of adding custom fields to taxonomy (Category)
 * with validation, sanitization, notices, and consistent structure.
 */

namespace Wapic_Fields\Example;

if (! defined('ABSPATH')) {
    exit;
}

use Wapic_Fields\Field;

class Example_Taxonomy {

    /**
     * Taxonomy slug
     * @var string
     */
    private $id = 'category';

    public function __construct() {

        // Display fields
        add_action('category_add_form_fields', array($this, 'add_field'));
        add_action('category_edit_form_fields', array($this, 'edit_field'));

        // Save fields
        add_action('created_category', array($this, 'save_field'), 10, 2);
        add_action('edited_category', array($this, 'save_field'), 10, 2);

        // Display notices if validation errors occur
        add_action('admin_notices', array($this, 'notices'));
    }

    /**
     * Display taxonomy admin notices
     */
    public function notices() {

        // Tampilkan hanya di halaman taxonomy ini
        if (filter_input(INPUT_GET, 'taxonomy') !== $this->id) {
            return;
        }

        $messages = get_transient("{$this->id}_taxonomy_messages");

        if (! $messages || ! is_array($messages)) {
            return;
        }

        echo '<div class="notice notice-error is-dismissible">';
        foreach ($messages as $message) {
            echo '<p>' . esc_html($message) . '</p>';
        }
        echo '</div>';

        delete_transient("{$this->id}_taxonomy_messages");
    }

    /**
     * Add term screen (Add New Category)
     */
    public function add_field($taxonomy) {
        $this->render_fields();
    }

    /**
     * Edit term screen (Edit Category)
     */
    public function edit_field($term) {
        $this->render_fields($term->term_id);
    }

    /**
     * Render all form fields for taxonomy
     * Reusable for Add + Edit screens
     */
    private function render_fields($term_id = 0) {
        wp_nonce_field("{$this->id}_taxonomy_save", "{$this->id}_taxonomy_nonce");

        Field::add_control(array(
            'id'    => '_sample_text',
            'type'  => 'text',
            'label' => esc_html__('Text', 'wapic-fields'),
            'value' => $term_id ? get_term_meta($term_id, '_sample_text', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_number',
            'type'  => 'number',
            'label' => esc_html__('Number', 'wapic-fields'),
            'value' => $term_id ? get_term_meta($term_id, '_sample_number', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_email',
            'type'  => 'email',
            'label' => esc_html__('Email', 'wapic-fields'),
            'value' => $term_id ? get_term_meta($term_id, '_sample_email', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_textarea',
            'type'  => 'textarea',
            'label' => esc_html__('Textarea', 'wapic-fields'),
            'value' => $term_id ? get_term_meta($term_id, '_sample_textarea', true) : '',
        ));

        Field::add_control(array(
            'id'          => '_sample_image',
            'type'        => 'image',
            'label'       => esc_html__('Image', 'wapic-fields'),
            'description' => esc_html__('Upload an image', 'wapic-fields'),
            'value'       => $term_id ? get_term_meta($term_id, '_sample_image', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_color',
            'type'  => 'color',
            'label' => esc_html__('Color', 'wapic-fields'),
            'value' => $term_id ? get_term_meta($term_id, '_sample_color', true) : '',
        ));
    }

    /**
     * Save custom taxonomy fields
     */
    public function save_field($term_id, $tt_id) {

        if (! current_user_can('manage_categories')) {
            return;
        }

        $nonce = isset($_POST["{$this->id}_taxonomy_nonce"])
            ? sanitize_text_field(wp_unslash($_POST["{$this->id}_taxonomy_nonce"]))
            : '';

        if (! wp_verify_nonce($nonce, "{$this->id}_taxonomy_save")) {
            return;
        }

        // Field definitions
        $fields = array(
            '_sample_text'     => 'text',
            '_sample_textarea' => 'textarea',
            '_sample_image'    => 'image',
            '_sample_color'    => 'color',
            '_sample_number'   => 'number',
            '_sample_email'    => 'email',
        );

        $errors = array();

        foreach ($fields as $key => $type) {

            // Field exists → validate & save
            if (isset($_POST[$key])) {

                $value      = wp_unslash($_POST[$key]); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by Field::sanitize_value() below.
                $validation = Field::validate_value($type, $value);

                if (! empty($validation)) {
                    $errors[] = $validation;
                } else {
                    $sanitized = Field::sanitize_value($type, $value);
                    update_term_meta($term_id, $key, $sanitized);
                }
            } else {
                // Field removed → delete meta
                delete_term_meta($term_id, $key);
            }
        }

        // Save errors into transient once
        if (! empty($errors)) {
            set_transient("{$this->id}_taxonomy_messages", $errors, 30);
        }
    }
}
// Init class
new Example_Taxonomy();
