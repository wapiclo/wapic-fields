<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Gallery field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Gallery extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('media');

        $ids_str = '';

        if (is_array($this->value)) {
            $ids_str = implode(',', array_filter(array_map('intval', $this->value)));
        } elseif (! empty($this->value)) {
            $ids_str = implode(',', array_filter(array_map('intval', explode(',', (string) $this->value))));
        }

        $ids   = $ids_str ? array_map('intval', explode(',', $ids_str)) : [];
        $label = $ids_str ? __('Edit Gallery', 'wapic-fields') : __('Add Gallery', 'wapic-fields');

        echo '<div id="' . esc_attr($this->id) . '_preview" class="wcf-field-gallery-preview">';
        if (! empty($ids)) {
            foreach ($ids as $img_id) {
                $img_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                if ($img_url) {
                    echo '<span class="wcf-field-gallery-thumb" data-id="' . esc_attr((string) $img_id) . '">';
                    echo '<img src="' . esc_url($img_url) . '" alt="' . esc_attr__('Gallery image', 'wapic-fields') . '">';
                    echo '<a href="#" class="wcf-field-remove-gallery-thumb" title="' . esc_attr__('Remove image', 'wapic-fields') . '">×</a>';
                    echo '</span>';
                }
            }
        }
        echo '</div>';

        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" %s class="wcf-gallery-ids">',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr($ids_str),
            $required_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );

        echo '<div class="wcf-gallery-actions">';
        echo '<button type="button" class="button wcf-field-gallery-upload" data-target="' . esc_attr($this->id) . '">' . esc_html($label) . '</button>';
        echo '</div>';
    }
}
