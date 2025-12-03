<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * File field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class File extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('media');

        echo '<div class="wcf-file-upload-wrapper">';

        printf(
            '<input type="text" id="%1$s" name="%2$s" value="%3$s" class="wcf-field-file-url wcf-field__input" placeholder="' . esc_attr__('File URL', 'wapic-fields') . '" data-validate="url" %4$s/>',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_url((string) $this->value),
            $this->required ? 'required' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );

        echo '<button type="button" class="button wcf-file-upload-button" data-target="' . esc_attr($this->id) . '">' . esc_html__('Select File', 'wapic-fields') . '</button>';
        echo '</div>';
    }
}
