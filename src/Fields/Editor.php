<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Editor field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Editor extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('editor');

        $editor_id = esc_attr($this->id);
        $settings  = [
            'textarea_name' => $this->name,
            'textarea_rows' => 6,
            'editor_class'  => 'wcf-field-editor wcf-field__input',
        ];

        echo '<div class="wcf-field-editor-wrap">';
        if (function_exists('wp_editor')) {
            ob_start();
            wp_editor((string) $this->value, $editor_id, $settings);
            echo ob_get_clean();
        } else {
            printf(
                '<textarea id="%s" name="%s" rows="6" class="wcf-field-editor wcf-field__input" %s>%s</textarea>',
                $editor_id,
                esc_attr($this->name),
                $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                esc_textarea((string) $this->value)
            );
        }
        echo '</div>';
    }
}
