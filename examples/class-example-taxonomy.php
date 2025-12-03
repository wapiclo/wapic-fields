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
        if (! isset($_GET['taxonomy']) || $_GET['taxonomy'] !== $this->id) {
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

        Field::add_control(array(
            'id'    => '_sample_text',
            'type'  => 'text',
            'label' => 'Text',
            'value' => $term_id ? get_term_meta($term_id, '_sample_text', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_number',
            'type'  => 'number',
            'label' => 'Number',
            'value' => $term_id ? get_term_meta($term_id, '_sample_number', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_email',
            'type'  => 'email',
            'label' => 'Email',
            'value' => $term_id ? get_term_meta($term_id, '_sample_email', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_textarea',
            'type'  => 'textarea',
            'label' => 'Textarea',
            'value' => $term_id ? get_term_meta($term_id, '_sample_textarea', true) : '',
        ));

        Field::add_control(array(
            'id'          => '_sample_image',
            'type'        => 'image',
            'label'       => 'Image',
            'description' => 'Upload an image',
            'value'       => $term_id ? get_term_meta($term_id, '_sample_image', true) : '',
        ));

        Field::add_control(array(
            'id'    => '_sample_color',
            'type'  => 'color',
            'label' => 'Color',
            'value' => $term_id ? get_term_meta($term_id, '_sample_color', true) : '',
        ));
    }

    /**
     * Save custom taxonomy fields
     */
    public function save_field($term_id, $tt_id) {

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

                $value      = $_POST[$key];
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
