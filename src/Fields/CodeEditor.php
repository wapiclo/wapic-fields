<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;
use Wapic_Fields\Assets;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Code Editor field.
 *
 * @package Wapic_Fields
 * @since 1.4.0
 */
class CodeEditor extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        Assets::get_instance()->require_asset('code_editor');

        printf(
            '<div class="wcf-code-editor-outer"><textarea id="%s" name="%s" class="wcf-code-editor wcf-field__input" %s %s>%s</textarea></div>',
            esc_attr($this->id),
            esc_attr($this->name),
            $required_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $this->get_attributes_string(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_textarea((string) $this->value)
        );
    }
}
