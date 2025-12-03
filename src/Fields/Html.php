<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Html field.
 *
 * @package Wapic_Fields
 * @since 1.3.0
 */
class Html extends Field {

    /**
     * Render the input element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        echo wp_kses_post((string) $this->value);
    }
}
