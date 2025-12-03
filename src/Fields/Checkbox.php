<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Checkbox field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Checkbox extends Field {

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

        $current_values = [];
        if ((empty($this->value) || $this->value === null || $this->value === false) && $this->default !== null) {
            $current_values = is_array($this->default) ? $this->default : [$this->default];
        } else {
            $current_values = is_array($this->value) ? $this->value : [$this->value];
        }

        foreach ($this->options as $key => $label) {
            $checked = in_array($key, $current_values);
            printf(
                '<label class="wcf-field-checkbox" for="%s"><input id="%s" type="checkbox" name="%s[]" value="%s" %s %s> %s</label>',
                esc_attr("{$this->name}_{$key}"),
                esc_attr("{$this->name}_{$key}"),
                esc_attr($this->name),
                esc_attr((string) $key),
                checked($checked, true, false),
                $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                esc_html((string) $label)
            );
        }
        echo '</fieldset>';
    }

    /**
     * Override render to handle fieldset structure if needed, but base render handles label separately.
     * Actually, base render shows label before input. For checkbox/radio, we might want to hide the main label if we use fieldset/legend.
     * The original code had a check: if ($this->label && $this->type !== 'toggle' && $this->type !== 'checkbox' && $this->type !== 'radio' ...)
     * So the base class already handles skipping the label for checkbox/radio.
     */
}
