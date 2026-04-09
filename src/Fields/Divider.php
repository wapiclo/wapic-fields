<?php

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Field;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Divider field.
 *
 * @package Wapic_Fields
 */
class Divider extends Field {

    /**
     * Render the divider element.
     *
     * @param string $required_attr
     */
    protected function render_input(string $required_attr): void {
        echo '<hr class="wcf-field-divider" />';
    }
}
