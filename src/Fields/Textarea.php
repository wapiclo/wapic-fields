<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Textarea field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Textarea extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        printf(
            '<textarea id="%s" name="%s" class="wcf-field-textarea wcf-field__input" %s %s>%s</textarea>',
            esc_attr($this->id),
            esc_attr($this->name),
            $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $this->get_attributes_string(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_textarea((string) $this->value)
        );
    }
}
