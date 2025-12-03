<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Color field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Color extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('colorpicker');

        $class = 'wcf-field-color wcf-field__input';

        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="%s" %s data-default-color="%s" />',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr((string) $this->value),
            esc_attr($class),
            $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_attr((string) ($this->default ?? ''))
        );
    }
}
