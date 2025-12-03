<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Image field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Image extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('media');

        $img_url = $this->value ? wp_get_attachment_image_url((int) $this->value, 'large') : '';
        $label   = $img_url ? __('Edit Image', 'wapic-fields') : __('Add Image', 'wapic-fields');

        echo '<div id="' . esc_attr($this->id) . '_preview" class="wcf-field-image-preview">';
        if ($img_url) {
            echo '<span class="wcf-field-image-thumb">';
            echo '<img src="' . esc_url($img_url) . '">';
            echo '<a href="#" class="wcf-field-remove-image" title="' . esc_attr__('Remove image', 'wapic-fields') . '">×</a>';
            echo '</span>';
        }
        echo '</div>';
        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" %s>',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr((string) $this->value),
            $required_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
        echo '<button type="button" class="button wcf-field-image-upload" data-target="' . esc_attr($this->id) . '">' . esc_html($label) . '</button>';
    }
}
