<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Select2 field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Select2 extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('select2');

        $attributes  = $this->get_attributes_string();
        $is_multiple = ! empty($this->attributes['multiple']);
        $name_attr   = $is_multiple ? $this->name . '[]' : $this->name;
        $placeholder = $this->attributes['placeholder'] ?? __('Select an option', 'wapic-fields');
        $allow_clear = ! empty($this->attributes['allow_clear']) ? 'true' : 'false';
        $width       = $this->attributes['width'] ?? '100%';

        $selected_values = [];
        if ($is_multiple) {
            if (is_array($this->value)) {
                $selected_values = array_map('strval', $this->value);
            } elseif (is_string($this->value) && $this->value !== '') {
                $selected_values = array_map('strval', array_filter(array_map('trim', explode(',', $this->value))));
            }
        } else {
            $selected_values = $this->value !== '' ? [strval($this->value)] : [];
        }

        $required_class = $this->required ? 'wcf-required' : '';

        echo '<select id="' . esc_attr($this->id) . '" 
                 name="' . esc_attr($name_attr) . '"
                 class="wcf-field-select2 wcf-field__input ' . esc_attr($required_class) . '" 
                 data-placeholder="' . esc_attr($placeholder) . '"
                 data-allow-clear="' . esc_attr($allow_clear) . '"
                 data-width="' . esc_attr($width) . '" ' .
            ($is_multiple ? 'multiple="multiple"' : '') . ' ' .
            $required_attr . ' ' . $attributes . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if (! $is_multiple && $placeholder) {
            echo '<option value="">' . esc_html($placeholder) . '</option>';
        }

        foreach ($this->options as $opt_value => $label) {
            $opt_value_str = (string) $opt_value;
            $selected = in_array($opt_value_str, $selected_values, true) ? 'selected' : '';
            echo '<option value="' . esc_attr((string) $opt_value) . '" ' . $selected . '>' . esc_html((string) $label) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        echo '</select>';

        // Initialization is handled by the enqueued select2 script (assets/js/select2.min.js),
        // which reads the data-placeholder / data-allow-clear / data-width attributes above.
        // Avoid emitting a per-field inline <script> so the field works under a strict CSP.
    }
}
