<?php

declare(strict_types=1);

namespace Wapic_Fields;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for all fields.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
abstract class Field {

    /**
     * Field ID attribute.
     *
     * @var string
     */
    protected string $id;

    /**
     * Field name attribute.
     *
     * @var string
     */
    protected string $name;

    /**
     * Field type.
     *
     * @var string
     */
    protected string $type;

    /**
     * Current field value.
     *
     * @var mixed
     */
    protected mixed $value;

    /**
     * Default field value.
     *
     * @var mixed
     */
    protected mixed $default;

    /**
     * Field label text.
     *
     * @var string
     */
    protected string $label;

    /**
     * Field description text.
     *
     * @var string
     */
    protected string $description;

    /**
     * Field options.
     *
     * @var array
     */
    protected array $options;

    /**
     * Additional CSS class(es).
     *
     * @var string
     */
    protected string $class;

    /**
     * Whether the field is required.
     *
     * @var bool
     */
    protected bool $required;

    /**
     * Field display conditions.
     *
     * @var array
     */
    protected array $condition;

    /**
     * Additional HTML attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Field style (e.g., 'table').
     *
     * @var string
     */
    protected string $style;

    /**
     * Constructor.
     *
     * @param array $args Field arguments.
     */
    public function __construct(array $args = []) {
        $defaults = [
            'id'          => '',
            'name'        => '',
            'type'        => 'text',
            'value'       => '',
            'default'     => '',
            'label'       => '',
            'description' => '',
            'options'     => [],
            'class'       => '',
            'required'    => false,
            'condition'   => [],
            'attributes'  => [],
            'style'       => '',
        ];

        $args = wp_parse_args($args, $defaults);

        $this->id          = (string) $args['id'];
        $this->name        = $args['name'] ?: $this->id;
        $this->type        = (string) $args['type'];
        $this->default     = $args['default'];
        $this->value       = $args['value'];
        $this->label       = (string) $args['label'];
        $this->description = (string) $args['description'];
        $this->options     = (array) $args['options'];
        $this->class       = (string) $args['class'];
        $this->required    = (bool) $args['required'];
        $this->condition   = (array) $args['condition'];
        $this->attributes  = (array) $args['attributes'];
        $this->style       = (string) $args['style'];

        $this->handle_default_value();
    }

    /**
     * Handle default value logic.
     */
    protected function handle_default_value(): void {
        $is_edit = isset($_GET['action']) || isset($_GET['tag_ID']) || isset($_GET['page']) || isset($_GET['user_id']) || isset($_GET['wp_http_referer']);
        $allow_empty = ! $is_edit;

        $invalid = $this->value === null || $this->value === false || ($allow_empty && $this->value === '');

        if ($invalid) {
            $this->value = $this->default;
        }
    }

    /**
     * Render the field.
     */
    public function render(): void {
        $wrapper_class = 'wcf-field wcf-field-type-' . esc_attr($this->type);

        if (! empty($this->class)) {
            $wrapper_class .= ' ' . esc_attr($this->class);
        }

        if (
            isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' &&
            ! empty($this->required) && empty($this->value) && $this->value !== '0'
        ) {
            $wrapper_class .= ' has-field-error';
        }

        $data_cond = '';
        if (! empty($this->condition)) {
            if (isset($this->condition['field'], $this->condition['value'])) {
                $wrapper_class .= ' wcf-field-conditional';
                $operator       = $this->condition['operator'] ?? '==';
                $data_cond      = sprintf(
                    'data-condition-field="%s" data-condition-operator="%s" data-condition-value="%s"',
                    esc_attr($this->condition['field']),
                    esc_attr($operator),
                    esc_attr($this->condition['value'])
                );
            }
        }

        $is_term  = isset($_GET['taxonomy']) && isset($_GET['tag_ID']);
        $is_table = $this->style === 'table';

        if ($is_table || $is_term) {
            $required_mark = ! empty($this->required) ? '<span class="required">*</span>' : '';
            echo '<tr class="form-field term-group-wrap">';
            echo '<th scope="row"><label for="' . esc_attr($this->id) . '">' . esc_html($this->label) . $required_mark . '</label></th>';
            echo '<td>';
        } else {
            echo '<div class="form-field term-group">';
        }

        echo '<div class="' . $wrapper_class . '" ' . $data_cond . '>';

        if ($this->label && ! in_array($this->type, ['toggle', 'checkbox', 'radio'], true) && ! $is_term && ! $is_table) {
            $required_mark = ! empty($this->required) ? '<span class="required">*</span>' : '';
            echo '<label class="wcf-field__label" for="' . esc_attr($this->id) . '"><strong>' . esc_html($this->label) . $required_mark . '</strong></label>';
        }

        $required_attr = ! empty($this->required) ? 'data-required="true"' : '';

        $this->render_input($required_attr);

        if ($this->type !== 'html') {
            echo '<p id="' . esc_attr($this->id) . '_error" class="wcf-field-error" style="display:none;"></p>';
        }

        if ($this->description) {
            echo '<p class="wcf-field__description">' . esc_html($this->description) . '</p>';
        }
        echo '</div>';

        if ($is_term || $is_table) {
            echo '</td>';
            echo '</tr>';
        } else {
            echo '</div>';
        }
    }

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    abstract protected function render_input(string $required_attr): void;

    /**
     * Get HTML attributes string.
     *
     * @return string
     */
    protected function get_attributes_string(): string {
        $attrs = '';
        foreach ($this->attributes as $key => $value) {
            $attrs .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
        return $attrs;
    }

    /**
     * Factory method to create field instances.
     *
     * @param array $args
     * @return void
     */
    public static function add_control(array $args = []): void {
        $type = $args['type'] ?? 'text';
        $class_name = 'Wapic_Fields\\Fields\\' . ucfirst($type);

        // Handle special cases or mapping if needed
        $map = [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'select' => 'Select',
            'select2' => 'Select2',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio',
            'toggle' => 'Toggle',
            'image' => 'Image',
            'gallery' => 'Gallery',
            'file' => 'File',
            'color' => 'Color',
            'date' => 'Date',
            'editor' => 'Editor',
            'repeater' => 'Repeater',
            'html' => 'Html',
            'email' => 'Email',
            'phone' => 'Phone',
            'number' => 'Number',
            'url' => 'Url',
        ];

        if (isset($map[$type])) {
            $class_name = 'Wapic_Fields\\Fields\\' . $map[$type];
        }

        if (class_exists($class_name)) {
            $field = new $class_name($args);
            $field->render();
        } else {
            // Fallback to text or error
            $field = new \Wapic_Fields\Fields\Text($args);
            $field->render();
        }
    }

    // Static methods for panel rendering (moved from original Field class)
    public static function start_controls_panel($args = array()) {
        $args = wp_parse_args(
            $args,
            array(
                'title' => __('Panel', 'wapic-fields'),
                'id'    => 'custom-panel',
                'type'  => 'setting',
            )
        );

        if ($args['type'] === 'setting') {
            echo '<div class="wcf">';
            echo '<div class="wcf-page-header"><h1 class="wcf-page-header__title">' . esc_html($args['title']) . '</h1></div>';
            echo '<form id="option" method="post" action="options.php">';
            settings_fields($args['id']);
            do_settings_sections($args['id']);
            settings_errors($args['id']);

            echo '<div class="wcf-container">';
            echo '<div class="wcf-panel is-style-compact">';
        }

        if ($args['type'] === 'metabox') {
            echo '<div class="wcf-panel is-style-compact">';
        }
    }

    public static function end_controls_panel($args = array()) {
        $args = wp_parse_args(
            $args,
            array(
                'type' => 'setting',
            )
        );

        if ($args['type'] === 'setting') {
            echo '</div>';

            // Submit panel
            echo '<div class="wcf-panel is-style-outline" style="flex:0 0 278px;">';
            echo '<div class="wcf-panel__header"><h2 class="wcf-panel__title">Action</h2></div>';
            submit_button();
            echo '</div>';

            echo '</div>';
            echo '</form>';
            echo '</div>';
        }

        if ($args['type'] === 'metabox') {
            echo '</div>';
        }
    }

    public static function start_controls_section($tabs) {
        echo '<div class="wcf-tabs">';
        echo '<ul class="wcf-tabs-nav">';
        foreach ($tabs as $tab => $label) {
            echo '<li><a href="#tab-' . esc_attr($tab) . '"><strong>' . esc_html($label) . '</strong></a></li>';
        }
        echo '</ul>';
    }

    public static function end_controls_section() {
        echo '</div>';
    }

    public static function start_controls_group($args) {
        echo '<div class="wcf-tab-content" id="tab-' . esc_attr($args['id']) . '">';
    }

    public static function end_controls_group() {
        echo '</div>';
    }

    /**
     * Validate field value.
     *
     * @param string $type
     * @param mixed  $value
     * @return string Error message or empty string.
     */
    public static function validate_value(string $type, mixed $value): string {
        if (empty($value)) {
            return ''; // Let empty values pass, use required attribute in form if needed
        }

        $error = '';

        switch ($type) {
            case 'email':
                if (! is_email($value)) {
                    $error = __('Please enter a valid email address', 'wapic-fields');
                }
                break;

            case 'number':
            case 'price':
                if (! is_numeric($value)) {
                    $error = __('Please enter a valid number', 'wapic-fields');
                }
                break;

            case 'phone':
                if (! preg_match('/^[0-9+\-\s()]+$/', (string) $value)) {
                    $error = __('Please enter a valid phone number', 'wapic-fields');
                }
                break;

            case 'url':
                if (! filter_var($value, FILTER_VALIDATE_URL)) {
                    $error = __('Please enter a valid URL', 'wapic-fields');
                }
                break;
        }

        return $error;
    }

    /**
     * Sanitize field value.
     *
     * @param string $type
     * @param mixed  $value
     * @return mixed
     */
    public static function sanitize_value(string $type, mixed $value): mixed {
        switch ($type) {
            case 'select2':
                if (empty($value)) {
                    return [];
                }
                if (is_array($value)) {
                    return array_filter(array_map('sanitize_text_field', $value));
                }
                if (strpos((string) $value, ',') !== false) {
                    $values = array_map('trim', explode(',', (string) $value));
                    return array_filter(array_map('sanitize_text_field', $values));
                }
                $sanitized = sanitize_text_field((string) $value);
                return $sanitized !== '' ? [$sanitized] : [];

            case 'url':
                return esc_url_raw((string) $value);

            case 'textarea':
                return sanitize_textarea_field((string) $value);

            case 'select':
            case 'radio':
            case 'text':
                return sanitize_text_field((string) $value);

            case 'checkbox':
                return array_map('sanitize_text_field', (array) $value);

            case 'image':
                return (int) $value;

            case 'gallery':
                if (is_array($value)) {
                    $filtered = array_filter(array_map('intval', $value));
                    return implode(',', $filtered);
                } else {
                    $filtered = array_filter(array_map('intval', explode(',', (string) $value)));
                    return implode(',', $filtered);
                }

            case 'toggle':
                return ($value === 'yes' || $value === true || $value === '1' || $value === 1) ? 'yes' : 'no';

            case 'number':
                return is_numeric($value) ? $value + 0 : null;

            case 'email':
                return sanitize_email((string) $value);

            case 'phone':
                return sanitize_text_field((string) $value);

            case 'date':
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
                    return sanitize_text_field((string) $value);
                }
                return '';

            default:
                return sanitize_text_field((string) $value);
        }
    }

    /**
     * Sanitize the field value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function sanitize(mixed $value): mixed {
        return sanitize_text_field((string) $value);
    }
}
