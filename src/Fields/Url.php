<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Url field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Url extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        printf(
            '<input type="url" id="%s" name="%s" value="%s" class="wcf-field-url wcf-field__input" %s %s />',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr((string) $this->value),
            $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $this->get_attributes_string() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }
}
