<?php

namespace Wapic_Fields;

if (! defined('ABSPATH')) {
    exit;
}

class CustomOptionRepeater extends \Wapic_Fields\Field {

    private $id = 'wapic-field-repeater';

    public function __construct() {
        add_action('admin_menu', array($this, 'register'));
        add_action('admin_init', array($this, 'save'));
    }

    public function register() {
        add_menu_page(
            'Wapic Fields Repeater',                // Page title
            'Wapic Fields Repeater',                // Menu title
            'manage_options',                // Capability
            $this->id,                 // Menu slug
            array($this, 'render'),  // Callback
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
                'title' => 'Wapic Fields Repeater',
                'id'    => $this->id,
                'type'  => 'setting',
            )
        );


        $this->add_control([
            'id' => '_sample_repeater',
            'type' => 'repeater',
            'label' => 'FORM YANG DIREPEADT',
            'value' => get_option('_sample_repeater'),
            'options' => [
                'fields' => [
                    ['id' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                    ['id' => 'qty', 'label' => 'Qty', 'type' => 'number', 'attributes' => ['min' => 0]],
                    ['id' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['a' => 'Type A', 'b' => 'Type B']],
                    ['id' => 'tag', 'label' => 'Tags (Select2)', 'type' => 'select2', 'options' => ['red' => 'Red', 'green' => 'Green', 'blue' => 'Blue'], 'attributes' => ['multiple' => true, 'placeholder' => 'Choose tags', 'allow_clear' => true]],
                    ['id' => 'agree', 'label' => 'Agree', 'type' => 'checkbox', 'options' => ['red' => 'Red', 'green' => 'Green', 'blue' => 'Blue']],
                    ['id' => 'choice', 'label' => 'Choice', 'type' => 'radio', 'options' => ['x' => 'Option X', 'y' => 'Option Y']],
                    ['id' => 'due', 'label' => 'Due Date', 'type' => 'date'],
                    ['id' => 'fileurl', 'label' => 'File URL', 'type' => 'file'],
                    ['id' => 'editor', 'label' => 'Editor', 'type' => 'editor'],
                    ['id' => 'colorpicker', 'label' => 'Color', 'type' => 'color'],
                    ['id' => 'toggle', 'label' => 'Toggle', 'type' => 'toggle'],
                    ['id' => 'gallery', 'label' => 'Gallery', 'type' => 'gallery'],
                    ['id' => 'image', 'label' => 'Image', 'type' => 'image'],
                ],
                'title_field' => 'title',
                'min' => 0,
                'max' => 0,
                'add_button_label' => 'Add New Item',
            ],
        ]);

        $this->end_controls_panel(
            array(
                'type' => 'setting',
            )
        );
    }

    public function save() {
        // First, ensure the options exist with autoload = 'no'
        $fields = array(
            '_sample_repeater'             => 'repeater',
        );

        foreach ($fields as $field_name => $field_type) {
            register_setting(
                $this->id,
                $field_name,
                array(
                    'sanitize_callback' => function ($value, $option = '') use ($field_type, $field_name) {
                        // Skip validation if the input is hidden
                        if (isset($_POST[$field_name . '_is_hidden']) && $_POST[$field_name . '_is_hidden'] === '1') {
                            return $this->sanitize_value($field_type, $value);
                        }

                        // Get validated value
                        $validation = $this->validate_value($field_type, $value);

                        if (! empty($validation)) {
                            add_settings_error($this->id, 'validation_error', $validation, 'error');
                            // Return the old value to prevent saving invalid data
                            return get_option($field_name, '');
                        } else {
                            return $this->sanitize_value($field_type, $value);
                        }
                    },
                )
            );
        }
    }
}
// Init
new CustomOptionRepeater();
