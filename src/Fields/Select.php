<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Select field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Select extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        echo '<select id="' . esc_attr($this->id) . '" name="' . esc_attr($this->name) . '" class="wcf-field-select wcf-field__input" ' . $required_attr . ' ' . $this->get_attributes_string() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        foreach ($this->options as $value => $label) {
            $selected = selected((string) $this->value, (string) $value, false);
            echo '<option value="' . esc_attr((string) $value) . '" ' . $selected . '>' . esc_html((string) $label) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        echo '</select>';
    }
}
