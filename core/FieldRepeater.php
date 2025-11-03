<?php

namespace Wapic_Fields;

if (!defined('ABSPATH')) {
    exit;
}

class FieldRepeater {
    public static function render($ctx, $required) {
        $options = isset($ctx['options']) && is_array($ctx['options']) ? $ctx['options'] : array();
        $schema_fields = isset($options['fields']) && is_array($options['fields']) ? $options['fields'] : array();
        $min           = isset($options['min']) ? max(0, intval($options['min'])) : 0;
        $max           = isset($options['max']) ? max(0, intval($options['max'])) : 0;
        $title_field   = isset($options['title_field']) ? (string) $options['title_field'] : '';
        $add_label     = isset($options['add_button_label']) && $options['add_button_label'] !== '' ? $options['add_button_label'] : __('Add Row', 'wapic-fields');

        $value = isset($ctx['value']) ? $ctx['value'] : array();
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            }
        }
        if (!is_array($value)) {
            $value = array();
        }

        if ($min > 0 && count($value) < $min) {
            $value = array_pad($value, $min, array());
        }

        foreach ($schema_fields as $sf) {
            $t = isset($sf['type']) ? $sf['type'] : 'text';
            if ($t === 'select2') {
                Assets::get_instance()->require_asset('select2');
            } elseif ($t === 'color') {
                Assets::get_instance()->require_asset('colorpicker');
            } elseif ($t === 'date') {
                Assets::get_instance()->require_asset('datepicker');
            } elseif (in_array($t, array('image', 'media_upload', 'file', 'gallery'), true)) {
                Assets::get_instance()->require_asset('media');
            } elseif ($t === 'editor') {
                Assets::get_instance()->require_asset('editor');
            }
        }

        $wrapper_id = esc_attr(isset($ctx['id']) ? $ctx['id'] : 'repeater') . '_repeater';
        $name_base  = esc_attr(isset($ctx['name']) ? $ctx['name'] : 'repeater');

        echo '<div class="wcf-repeater" id="' . $wrapper_id . '" data-max="' . esc_attr($max) . '" data-title-field="' . esc_attr($title_field) . '">';
        // Hidden marker to ensure the field key exists on submit without colliding with row indexes
        echo '<input type="hidden" name="' . esc_attr($name_base) . '[_wcf_marker]" value="1" />';

        $index = 0;
        foreach ($value as $row) {
            self::render_row($ctx, $schema_fields, $name_base, $index, is_array($row) ? $row : array(), $required, false, $title_field);
            $index++;
        }

        self::render_row($ctx, $schema_fields, $name_base, '__INDEX__', array(), $required, true, $title_field);

        echo '<div class="wcf-repeater-actions">';
        echo '<button type="button" class="button wcf-repeater-add">' . esc_html($add_label) . '</button>';
        echo '</div>';

        echo '</div>';
    }

    private static function render_row($ctx, $fields, $name_base, $index, $row, $required, $is_template = false, $title_field = '') {
        $row_class = 'wcf-repeater-row' . ($is_template ? ' wcf-repeater-template' : '');
        $style     = $is_template ? ' style="display:none;"' : '';
        $disabled  = $is_template ? ' disabled="disabled"' : '';

        $title_value = '';
        if ($title_field !== '' && is_array($row) && isset($row[$title_field])) {
            $title_value = (string) $row[$title_field];
        }

        echo '<div class="' . esc_attr($row_class) . '"' . $style . ' data-index="' . esc_attr($index) . '">';

        echo '<div class="wcf-repeater-row-header">';
        echo '<span class="wcf-repeater-drag-handle" title="' . esc_attr__('Drag to reorder', 'wapic-fields') . '">⋮⋮</span>';
        echo '<span class="wcf-repeater-row-title">' . esc_html($title_value !== '' ? $title_value : __('Item', 'wapic-fields')) . '</span>';
        echo '<div class="wcf-repeater-row-controls">';
        echo '<button type="button" class="button button-secondary wcf-repeater-accordion-toggle" aria-expanded="false" aria-controls="' . esc_attr(isset($ctx['id']) ? $ctx['id'] : 'repeater') . '_' . esc_attr($index) . '_body" title="' . esc_attr__('Toggle', 'wapic-fields') . '">▼</button>';
        echo '<button type="button" class="button button-link-delete wcf-repeater-remove">' . esc_html__('Remove', 'wapic-fields') . '</button>';
        echo '</div>';
        echo '</div>';

        echo '<div class="wcf-repeater-row-body" id="' . esc_attr(isset($ctx['id']) ? $ctx['id'] : 'repeater') . '_' . esc_attr($index) . '_body">';

        foreach ($fields as $sf) {
            $fid   = isset($sf['id']) ? (string) $sf['id'] : '';
            if ($fid === '') {
                continue;
            }
            $flabel = isset($sf['label']) ? $sf['label'] : '';
            $ftype  = isset($sf['type']) ? $sf['type'] : 'text';
            $fopts  = isset($sf['options']) && is_array($sf['options']) ? $sf['options'] : array();
            $fattr  = isset($sf['attributes']) && is_array($sf['attributes']) ? $sf['attributes'] : array();
            $fdef   = array_key_exists('default', $sf) ? $sf['default'] : '';
            $fval   = (isset($row[$fid]) && $row[$fid] !== '') ? $row[$fid] : $fdef;

            $input_name = $name_base . '[' . $index . '][' . $fid . ']';
            $input_id   = esc_attr(isset($ctx['id']) ? $ctx['id'] : 'repeater') . '_' . $index . '_' . $fid;

            echo '<div class="wcf-repeater-field" data-subfield-id="' . esc_attr($fid) . '">';
            if ($flabel !== '') {
                echo '<label class="wcf-field__label" for="' . esc_attr($input_id) . '"><strong>' . esc_html($flabel) . '</strong></label>';
            }

            $attrs = '';
            foreach ($fattr as $ak => $av) {
                $attrs .= ' ' . esc_attr($ak) . '="' . esc_attr($av) . '"';
            }

            if ($ftype === 'textarea') {
                echo '<textarea id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" class="wcf-field__input"' . $attrs . $disabled . '>' . esc_textarea((string) $fval) . '</textarea>';
            } elseif ($ftype === 'number') {
                echo '<input type="number" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input"' . $attrs . ' ' . $required . $disabled . ' />';
            } elseif ($ftype === 'email') {
                echo '<input type="email" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input"' . $attrs . ' ' . $required . $disabled . ' />';
            } elseif ($ftype === 'url') {
                echo '<input type="url" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input"' . $attrs . ' ' . $required . $disabled . ' />';
            } elseif ($ftype === 'date') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field-date wcf-field__input" autocomplete="off"' . $attrs . ' ' . $required . $disabled . ' />';
            } elseif ($ftype === 'select') {
                echo '<select id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" class="wcf-field__input"' . $attrs . ' ' . $required . $disabled . '>';
                foreach ($fopts as $ov => $ol) {
                    $sel = selected((string) $fval, (string) $ov, false);
                    echo '<option value="' . esc_attr((string) $ov) . '" ' . $sel . '>' . esc_html((string) $ol) . '</option>';
                }
                echo '</select>';
            } else {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input"' . $attrs . ' ' . $required . $disabled . ' />';
            }

            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    public static function sanitize($value) {
        if (!is_array($value)) {
            $decoded = null;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
            }
            if (!is_array($decoded)) {
                return array();
            }
            $value = $decoded;
        }
        $rows = array();
        foreach ($value as $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean = array();
            foreach ($row as $k => $v) {
                if (is_scalar($v) || $v === null) {
                    $clean[(string) $k] = is_string($v) ? sanitize_text_field($v) : (string) $v;
                } elseif (is_array($v)) {
                    $clean[(string) $k] = array_map(function ($iv) {
                        return is_string($iv) ? sanitize_text_field($iv) : (string) $iv;
                    }, $v);
                }
            }
            if (!empty($clean)) {
                $rows[] = $clean;
            }
        }
        return $rows;
    }
}
