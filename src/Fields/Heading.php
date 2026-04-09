<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Heading field.
 *
 * @package Wapic_Fields
 */
class Heading extends Field {

    /**
     * Render the heading element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        printf(
            '<div class="wcf-field-heading"><h3 class="wcf-field-heading__title">%s</h3></div>',
            esc_html($this->label)
        );
    }
}
