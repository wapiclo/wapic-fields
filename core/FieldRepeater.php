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

        // Do not auto-pad to min here; UI should enforce min via JS to avoid phantom empty rows

        // Remove submission marker if present
        if (isset($value['_wcf_marker'])) {
            unset($value['_wcf_marker']);
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
        foreach ($value as $k => $row) {
            if (!is_numeric($k)) {
                continue; // ignore non-index keys
            }
            if (!is_array($row)) {
                continue; // skip non-array artifacts
            }
            // Skip empty rows: deep check for any non-empty scalar
            $has_nonempty = false;
            foreach ($row as $rv) {
                if (is_array($rv)) {
                    foreach ($rv as $iv) {
                        $sv = is_string($iv) ? trim($iv) : (is_null($iv) ? '' : (string)$iv);
                        if ($sv !== '') { $has_nonempty = true; break; }
                    }
                } else {
                    $sv = is_string($rv) ? trim($rv) : (is_null($rv) ? '' : (string)$rv);
                    if ($sv !== '') { $has_nonempty = true; }
                }
                if ($has_nonempty) break;
            }
            if (! $has_nonempty) continue;
            self::render_row($ctx, $schema_fields, $name_base, $index, $row, $required, false, $title_field);
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

        echo '<div class="wcf-repeater-row-header wcf-repeater-drag-handle">';
        echo '<span class="wcf-repeater-drag-handle-toggle" title="' . esc_attr__('Drag to reorder', 'wapic-fields') . '">⋮⋮</span>';
        echo '<span class="wcf-repeater-row-title">' . esc_html($title_value !== '' ? $title_value : __('Item', 'wapic-fields')) . '</span>';
        echo '<div class="wcf-repeater-row-controls">';
        echo '<button type="button" class="button button-secondary wcf-repeater-accordion-toggle" aria-expanded="false" aria-controls="' . esc_attr(isset($ctx['id']) ? $ctx['id'] : 'repeater') . '_' . esc_attr($index) . '_body" title="' . esc_attr__('Toggle', 'wapic-fields') . '">▼</button>';
        echo '<button type="button" class="button wcf-repeater-remove" title="' . esc_attr__('Remove item', 'wapic-fields') . '"><span class="dashicons dashicons-trash"></span><span class="screen-reader-text">' . esc_html__('Remove', 'wapic-fields') . '</span></button>';
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
            $is_req = ! empty($sf['required']);
            $req_attr = '';
            $data_req = ($is_req && ! $is_template) ? ' data-required="true"' : '';
            $req_class = ($is_req && ! $is_template) ? ' wcf-required' : '';

            $input_name = $name_base . '[' . $index . '][' . $fid . ']';
            $input_id   = esc_attr(isset($ctx['id']) ? $ctx['id'] : 'repeater') . '_' . $index . '_' . $fid;

            $wrapper_required_attr = $is_req ? ' data-wcf-required="1"' : '';
            echo '<div class="wcf-field wcf-field-type-' . esc_attr($ftype) . '" data-subfield-id="' . esc_attr($fid) . '"' . $wrapper_required_attr . '>';
            $has_legend = ($ftype === 'checkbox' || $ftype === 'radio');
            if ($has_legend) {
                echo '<fieldset>';
                if ($flabel !== '') {
                    echo '<legend class="wcf-field__label"><strong>' . esc_html($flabel) . '</strong></legend>';
                }
            } else {
                if ($flabel !== '') {
                    echo '<label class="wcf-field__label" for="' . esc_attr($input_id) . '"><strong>' . esc_html($flabel) . '</strong></label>';
                }
            }

            $attrs = '';
            foreach ($fattr as $ak => $av) {
                $attrs .= ' ' . esc_attr($ak) . '="' . esc_attr($av) . '"';
            }

            if ($ftype === 'textarea') {
                echo '<textarea id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" class="wcf-field__input' . $req_class . '"' . $attrs . $data_req . $disabled . '>' . esc_textarea((string) $fval) . '</textarea>';
            } elseif ($ftype === 'number') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input wcf-field-number' . $req_class . '"' . $attrs . $data_req . $disabled . ' />';
            } elseif ($ftype === 'email') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input wcf-field-email' . $req_class . '"' . $attrs . $data_req . $disabled . ' />';
            } elseif ($ftype === 'url') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input wcf-field-url' . $req_class . '"' . $attrs . $data_req . $disabled . ' />';
            } elseif ($ftype === 'date') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field-date wcf-field__input' . $req_class . '" autocomplete="off"' . $attrs . $data_req . $disabled . ' />';
            } elseif ($ftype === 'select') {
                echo '<select id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" class="wcf-field__input' . $req_class . '"' . $attrs . $data_req . $disabled . '>';
                foreach ($fopts as $ov => $ol) {
                    $sel = selected((string) $fval, (string) $ov, false);
                    echo '<option value="' . esc_attr((string) $ov) . '" ' . $sel . '>' . esc_html((string) $ol) . '</option>';
                }
                echo '</select>';
            } elseif ($ftype === 'select2') {
                $is_multiple = ! empty($fattr['multiple']);
                $name_attr   = $is_multiple ? $input_name . '[]' : $input_name;
                $placeholder = ! empty($fattr['placeholder']) ? $fattr['placeholder'] : __('Select an option', 'wapic-fields');
                $allow_clear = ! empty($fattr['allow_clear']) ? 'true' : 'false';
                $width       = ! empty($fattr['width']) ? $fattr['width'] : '100%';
                echo '<select id="' . esc_attr($input_id) . '" name="' . esc_attr($name_attr) . '" class="wcf-field-select2 wcf-field__input' . $req_class . '" data-placeholder="' . esc_attr($placeholder) . '" data-allow-clear="' . esc_attr($allow_clear) . '" data-width="' . esc_attr($width) . '" ' . ($is_multiple ? 'multiple="multiple"' : '') . $data_req . ' ' . $disabled . '>';
                if (! $is_multiple && $placeholder) {
                    echo '<option value="">' . esc_html($placeholder) . '</option>';
                }
                foreach ($fopts as $ov => $ol) {
                    $selected = '';
                    if ($is_multiple && is_array($fval)) {
                        $selected = in_array((string)$ov, array_map('strval', $fval), true) ? 'selected' : '';
                    } else {
                        $selected = selected((string)$fval, (string)$ov, false);
                    }
                    echo '<option value="' . esc_attr((string) $ov) . '" ' . $selected . '>' . esc_html((string) $ol) . '</option>';
                }
                echo '</select>';
            } elseif ($ftype === 'color') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string)$fval) . '" class="wcf-field-color wcf-field__input' . $req_class . '"' . $attrs . $data_req . $disabled . ' />';
            } elseif ($ftype === 'checkbox') {
                if (!empty($fopts)) {
                    $current_values = array();
                    if (is_array($fval)) {
                        $current_values = array_map('strval', $fval);
                    } elseif ($fval !== '' && $fval !== null) {
                        $current_values = array((string)$fval);
                    }
                    foreach ($fopts as $ov => $ol) {
                        $ov_str = (string)$ov;
                        $cid = $input_id . '_' . sanitize_key($ov_str);
                        $checked = in_array($ov_str, $current_values, true) ? 'checked' : '';
                        echo '<label class="wcf-field-checkbox" for="' . esc_attr($cid) . '"><input id="' . esc_attr($cid) . '" type="checkbox" name="' . esc_attr($input_name) . '[]" value="' . esc_attr($ov_str) . '" class="' . $req_class . '"' . $data_req . ' ' . $disabled . ' ' . $checked . ' /> ' . esc_html((string)$ol) . '</label>';
                    }
                } else {
                    $checked = !empty($fval) && ($fval === 'yes' || $fval === '1' || $fval === 1 || $fval === true) ? 'checked' : '';
                    echo '<label class="wcf-field-checkbox" for="' . esc_attr($input_id) . '"><input type="checkbox" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="yes" class="' . $req_class . '"' . $data_req . ' ' . $disabled . ' ' . $checked . ' /> ' . esc_html__('Yes', 'wapic-fields') . '</label>';
                }
            } elseif ($ftype === 'toggle') {
                $checked = ($fval === 'yes' || $fval === true || $fval === '1' || $fval === 1) ? 'checked' : '';
                echo '<div class="wcf-field-toggle">';
                echo '<label class="wcf-field-toggle-switch" for="' . esc_attr($input_id) . '">';
                // Only checkbox; no hidden "no" to prevent phantom non-empty rows
                echo '<input type="checkbox" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="yes" ' . $checked . $data_req . ' ' . $disabled . ' class="' . trim('wcf-field__input wcf-field-toggle' . $req_class) . '">';
                echo '<span class="slider"></span>';
                echo '</label>';
                echo '</div>';
            } elseif ($ftype === 'radio') {
                foreach ($fopts as $ov => $ol) {
                    $checked = checked((string)$fval, (string)$ov, false);
                    $rid = $input_id . '_' . sanitize_key((string)$ov);
                    echo '<label class="wcf-field-radio" for="' . esc_attr($rid) . '"><input type="radio" id="' . esc_attr($rid) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string)$ov) . '" class="' . $req_class . '"' . $data_req . ' ' . $disabled . ' ' . $checked . ' /> ' . esc_html((string)$ol) . '</label> ';
                }
            } elseif ($ftype === 'file') {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string)$fval) . '" class="wcf-field__input wcf-field-file-url' . $req_class . '" placeholder="' . esc_attr__('File URL', 'wapic-fields') . '"' . $attrs . $data_req . $disabled . ' />';
            } elseif ($ftype === 'image') {
                // store attachment ID, show preview like core Field::control_image
                $img_url = $fval ? wp_get_attachment_image_url(intval($fval), 'large') : '';
                $label   = $img_url ? __('Edit Image', 'wapic-fields') : __('Add Image', 'wapic-fields');
                echo '<div id="' . esc_attr($input_id) . '_preview" class="wcf-field-image-preview">';
                if ($img_url) {
                    echo '<span class="wcf-field-image-thumb">';
                    echo '<img src="' . esc_url($img_url) . '">';
                    echo '<a href="#" class="wcf-field-remove-image" title="' . esc_attr__('Remove image', 'wapic-fields') . '">×</a>';
                    echo '</span>';
                }
                echo '</div>';
                echo '<input type="hidden" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string)$fval) . '"' . $data_req . ' ' . $disabled . ' class="wcf-media-id">';
                echo '<button type="button" class="button wcf-field-image-upload" data-target="' . esc_attr($input_id) . '">' . esc_html($label) . '</button>';
            } elseif ($ftype === 'gallery') {
                // value as comma-separated IDs, preview thumbs
                $ids_str = '';
                if (is_array($fval)) {
                    $ids_str = implode(',', array_filter(array_map('intval', $fval)));
                } elseif (!empty($fval)) {
                    $ids_str = implode(',', array_filter(array_map('intval', explode(',', (string)$fval))));
                }
                $ids = $ids_str ? array_map('intval', explode(',', $ids_str)) : array();
                $label = $ids_str ? __('Edit Gallery', 'wapic-fields') : __('Add Gallery', 'wapic-fields');
                echo '<div id="' . esc_attr($input_id) . '_preview" class="wcf-field-gallery-preview">';
                if (!empty($ids)) {
                    foreach ($ids as $img_id) {
                        $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                        if ($thumb) {
                            echo '<span class="wcf-field-gallery-thumb" data-id="' . esc_attr($img_id) . '">';
                            echo '<img src="' . esc_url($thumb) . '" alt="' . esc_attr__('Gallery image', 'wapic-fields') . '">';
                            echo '<a href="#" class="wcf-field-remove-gallery-thumb" title="' . esc_attr__('Remove image', 'wapic-fields') . '">×</a>';
                            echo '</span>';
                        }
                    }
                }
                echo '</div>';
                echo '<input type="hidden" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr($ids_str) . '"' . $data_req . ' ' . $disabled . ' class="wcf-gallery-ids">';
                echo '<div class="wcf-gallery-actions">';
                echo '<button type="button" class="button wcf-field-gallery-upload" data-target="' . esc_attr($input_id) . '">' . esc_html($label) . '</button>';
                echo '</div>';
            } elseif ($ftype === 'editor') {
                if (function_exists('wp_editor') && ! $is_template) {
                    ob_start();
                    wp_editor(
                        (string) $fval,
                        $input_id,
                        array(
                            'textarea_name' => $input_name,
                            'textarea_rows' => 6,
                            'editor_class'  => 'wcf-field-editor wcf-field__input' . $req_class,
                        )
                    );
                    echo ob_get_clean();
                } else {
                    echo '<textarea id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" class="wcf-field-editor wcf-field__input' . $req_class . '"' . $attrs . $data_req . $disabled . '>' . esc_textarea((string) $fval) . '</textarea>';
                }
            } else {
                echo '<input type="text" id="' . esc_attr($input_id) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr((string) $fval) . '" class="wcf-field__input' . $req_class . '"' . $attrs . $data_req . $disabled . ' />';
            }

            if ($has_legend) {
                echo '</fieldset>';
            }
            // error message holder for validation script
            echo '<p class="wcf-field-error" id="' . esc_attr($input_id) . '_error" style="display:none"></p>';
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
                $key = (string) $k;
                if (is_array($v)) {
                    // sanitize array and drop empty elements
                    $san = array_values(array_filter(array_map(function ($iv) {
                        $sv = is_string($iv) ? sanitize_text_field($iv) : (is_null($iv) ? '' : (string)$iv);
                        return $sv;
                    }, $v), function ($sv) {
                        return $sv !== '' && $sv !== null;
                    }));
                    if (!empty($san)) {
                        $clean[$key] = $san;
                    }
                } else {
                    $sv = is_string($v) ? sanitize_text_field($v) : (is_null($v) ? '' : (string)$v);
                    if ($sv !== '') {
                        $clean[$key] = $sv;
                    }
                }
            }
            // Only push row if it has at least one non-empty value
            if (!empty($clean)) {
                $rows[] = $clean;
            }
        }
        return $rows;
    }
}
