<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Radio field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Radio extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        echo '<fieldset>';
        if ($this->label) {
            echo '<legend class="wcf-field__label"><strong>' . esc_html($this->label) . '</strong></legend>';
        }

        foreach ($this->options as $key => $label) {
            printf(
                '<label class="wcf-field-radio" for="%s"><input id="%s" type="radio" name="%s" value="%s" %s %s> %s</label>',
                esc_attr("{$this->name}_{$key}"),
                esc_attr("{$this->name}_{$key}"),
                esc_attr($this->name),
                esc_attr((string) $key),
                checked($this->value, $key, false),
                $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                esc_html((string) $label)
            );
        }
        echo '</fieldset>';
    }
}
