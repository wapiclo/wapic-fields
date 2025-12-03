<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Date field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Date extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('datepicker');

        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="wcf-field-date wcf-field__input" autocomplete="off" %s />',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr((string) $this->value),
            $required_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }
}
