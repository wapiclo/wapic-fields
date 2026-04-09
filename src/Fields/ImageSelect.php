<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Image Select field.
 *
 * @package Wapic_Fields
 * @since 1.4.0
 */
class ImageSelect extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('image_select');

        $layout = $this->attributes['layout'] ?? 'row'; // row, grid
        
        echo '<div class="wcf-image-select wcf-image-select--' . esc_attr($layout) . '">';
        
        foreach ($this->options as $key => $option) {
            $image = is_array($option) ? ($option['image'] ?? '') : $option;
            $label = is_array($option) ? ($option['label'] ?? '') : '';

            $input_id = esc_attr("{$this->id}_{$key}");
            $is_selected = (string) $this->value === (string) $key;
            
            printf(
                '<div class="wcf-image-select__item %s">
                    <input type="radio" id="%s" name="%s" value="%s" %s %s class="wcf-image-select__input">
                    <label for="%s" class="wcf-image-select__label">
                        <div class="wcf-image-select__image-wrapper">
                            <img src="%s" alt="%s" class="wcf-image-select__image">
                            <span class="wcf-image-select__check">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.6666 5L7.49992 14.1667L3.33325 10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        %s
                    </label>
                </div>',
                $is_selected ? 'is-selected' : '',
                $input_id,
                esc_attr($this->name),
                esc_attr((string) $key),
                checked((string)$this->value, (string)$key, false),
                $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                $input_id,
                esc_url($image),
                esc_attr($label),
                $label ? '<span class="wcf-image-select__text">' . esc_html($label) . '</span>' : ''
            );
        }
        
        echo '</div>';
    }
}
