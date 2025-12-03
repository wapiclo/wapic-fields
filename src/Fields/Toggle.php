<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Toggle field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Toggle extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        $checked = ($this->value === 'yes' || $this->value === true || $this->value === '1' || $this->value === 1) ? 'checked' : '';
        echo '<div class="wcf-field-toggle">';

        printf(
            '<label class="wcf-field-toggle-switch" for="%s">',
            esc_attr($this->id)
        );

        printf(
            '<input type="hidden" name="%s" value="no">' .
                '<input type="checkbox" id="%s" name="%s" value="yes" %s %s>',
            esc_attr($this->name),
            esc_attr($this->id),
            esc_attr($this->name),
            $checked, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $required_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );

        echo '<span class="slider"></span>';
        echo '</label>';

        echo '<strong class="wcf-field-toggle-label">' . esc_html($this->label) . '</strong>';

        echo '</div>';
    }
}
