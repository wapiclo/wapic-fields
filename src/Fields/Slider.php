<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Slider field.
 *
 * @package Wapic_Fields
 * @since 1.4.0
 */
class Slider extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('slider');
        
        $min  = $this->attributes['min'] ?? 0;
        $max  = $this->attributes['max'] ?? 100;
        $step = $this->attributes['step'] ?? 1;

        printf(
            '<div class="wcf-slider-container">
                <div class="wcf-slider-wrapper">
                    <input type="range" id="%s" name="%s" value="%s" min="%s" max="%s" step="%s" class="wcf-slider wcf-field__input" %s %s />
                    <span class="wcf-slider-value">%s</span>
                </div>
            </div>',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr((string) $this->value),
            esc_attr((string) $min),
            esc_attr((string) $max),
            esc_attr((string) $step),
            $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $this->get_attributes_string(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_html((string) $this->value)
        );
    }
}
