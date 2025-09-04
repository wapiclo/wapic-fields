<?php
namespace Wapic_Fields;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Assets
 * 
 * Class untuk mengelola aset admin field
 */
class Assets {
    private static $instance = null;
    private $required_assets = [
        'select2' => false,
        'colorpicker' => false,
        'datepicker' => false,
        'media' => false,
        'editor' => false
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function require_asset($asset) {
        if (isset($this->required_assets[$asset])) {
            $this->required_assets[$asset] = true;
        }
    }

    public function enqueue_assets() {

        // Load JavaScript libraries
        if ($this->required_assets['select2']) {
            wp_enqueue_style('select2', WAPIC_FIELDS_ASSETS . 'assets/select2/select2.min.css');
            wp_enqueue_script('select2', WAPIC_FIELDS_ASSETS . 'assets/select2/select2.min.js', ['jquery'], null, true);
            wp_enqueue_script('wapic-field-select2', WAPIC_FIELDS_ASSETS . 'assets/js/select2.min.js', [], '1.0.0', true);
        }

        if ($this->required_assets['colorpicker']) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('wapic-field-colorpicker', WAPIC_FIELDS_ASSETS . 'assets/js/colorpicker.min.js', [], '1.0.0', true);
        }

        if ($this->required_assets['datepicker']) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('wapic-field-datepicker', WAPIC_FIELDS_ASSETS . 'assets/js/datepicker.min.js', [], '1.0.0', true);
        }

        if ($this->required_assets['media']) {
            wp_enqueue_media();
            wp_enqueue_script('wapic-field-media-upload', WAPIC_FIELDS_ASSETS . 'assets/js/media-upload.min.js', [], '1.0.0', true);
        }

        // Enqueue the main admin script as a module
        $this->enqueue_admin_script();
    }

    /**
     * Enqueue the main admin script as a module
     */
    private function enqueue_admin_script() {
        // Register the script with type='module'
        wp_enqueue_style('wapic-field', WAPIC_FIELDS_ASSETS . 'assets/css/styles.min.css', [], '1.0.0');
        wp_enqueue_script('wapic-field-tab', WAPIC_FIELDS_ASSETS . 'assets/js/tab.min.js', [], '1.0.0', true);
        wp_enqueue_script('wapic-field-validation', WAPIC_FIELDS_ASSETS . 'assets/js/validation.min.js', [], '1.0.0', true);
        wp_enqueue_script('wapic-field-conditional', WAPIC_FIELDS_ASSETS . 'assets/js/conditional.min.js', [], '1.0.0', true);

        // Localize script with necessary data
        wp_localize_script('wapic-field-validation', 'wapic_field', [
            'validation' => [
                'requiredMessage' => __('This field is required', 'wapic-fields-text'),
                'validEmail' => __('Please enter a valid email address', 'wapic-fields-text'),
                'validUrl' => __('Please enter a valid URL', 'wapic-fields-text'),
                'validNumber' => __('Please enter a valid number', 'wapic-fields-text'),
                'minNumber' => __('Value must be at least %s', 'wapic-fields-text'),
                'maxNumber' => __('Value must be at most %s', 'wapic-fields-text'),
                'compareRegularPrice' => __('Regular price must be greater than sale price', 'wapic-fields-text'),
                'compareSalePrice' => __('Sale price must be less than regular price', 'wapic-fields-text'),
                'submitFailed' => __( 'Oops! Form submission failed due to validation issues. Please review the highlighted fields:', 'wapic-fields-text' ),
            ]
        ]);
    }
}