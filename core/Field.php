<?php

/**
 * Wapic Fields - Field Management
 *
 * @package    Wapic_Fields
 * @subpackage Core
 * @since      1.2.0
 * @author     Wapic Team
 * @license    GPL-2.0+
 * @link       https://wapiclo.com/
 */

namespace Wapic_Fields;

use Wapic_Fields\Assets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the creation and rendering of form fields in the WordPress admin.
 *
 * This class provides methods to create various types of form fields with support for
 * validation, conditions, and custom attributes. It's designed to be flexible
 * and extensible for different types of form fields.
 *
 * @since 1.2.0
 */
class Field {

	/**
	 * Field ID attribute.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $id;

	/**
	 * Field name attribute.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $name;

	/**
	 * Field type (text, textarea, select, etc.).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $type;

	/**
	 * Current field value.
	 *
	 * @since 1.0.0
	 * @var mixed
	 */
	private $value;

	/**
	 * Default field value.
	 *
	 * @since 1.0.0
	 * @var mixed
	 */
	private $default;

	/**
	 * Field label text.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $label;

	/**
	 * Field description text.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $description;

	/**
	 * Field options (for select, radio, checkbox, etc.).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $options;

	/**
	 * Additional CSS class(es) for the field.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $class;

	/**
	 * Whether the field is required.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private $required;

	/**
	 * Field display conditions.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $condition;

	/**
	 * Additional HTML attributes for the field.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $attributes;

	/**
	 * Adds a form control with the specified arguments.
	 *
	 * This is the main method for rendering form fields. It handles the rendering
	 * of different field types and applies all necessary attributes and conditions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. An array of field arguments.
	 *
	 *     @type string $id           Field ID attribute.
	 *     @type string $name         Field name attribute.
	 *     @type string $type         Field type (text, textarea, select, etc.).
	 *     @type mixed  $value        Current field value.
	 *     @type mixed  $default      Default field value.
	 *     @type string $label        Field label text.
	 *     @type string $description  Field description text.
	 *     @type array  $options      Field options (for select, radio, etc.).
	 *     @type string $class        Additional CSS class(es) for the field.
	 *     @type bool   $required     Whether the field is required.
	 *     @type array  $condition    Field display conditions.
	 *     @type array  $attributes   Additional HTML attributes for the field.
	 * }
	 * @return void
	 */
	public function add_control( $args = array() ) {
		$defaults = array(
			'id'          => '',
			'name'        => '',
			'type'        => 'text',
			'value'       => '',
			'default'     => '',
			'label'       => '',
			'description' => '',
			'options'     => array(),
			'class'       => '',
			'required'    => false,
			'condition'   => array(),
			'attributes'  => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$this->id          = $args['id'];
		$this->name        = $args['name'] ?: $args['id'];
		$this->type        = $args['type'];
		$this->default     = $args['default'];
		$this->value       = $args['value'];
		$this->label       = $args['label'];
		$this->description = $args['description'];
		$this->options     = $args['options'];
		$this->class       = $args['class'];
		$this->required    = $args['required'];
		$this->condition   = $args['condition'];
		$this->attributes  = $args['attributes'];

		// Handle default value
		// If it's an edit action, use the default value if the value is null or false
		// If it's a new action, use the default value if the value is null, false, or empty string
		$is_edit = isset( $_GET['action'] ) || isset( $_GET['tag_ID'] ) || isset( $_GET['page'] ) || isset( $_GET['user_id'] ) || isset( $_GET['wp_http_referer'] );

		$allow_empty = ! $is_edit; // Saat add new, izinkan string kosong

		$invalid = $args['value'] === null || $args['value'] === false || ( $allow_empty && $args['value'] === '' );

		$this->value = $invalid ? $args['default'] : $args['value'];

		// Add wrapper class
		$wrapper_class = 'wcf-field wcf-field-type-' . esc_attr( $this->type );
		if ( ! empty( $this->class ) ) {
			$wrapper_class .= ' ' . esc_attr( $this->class );
		}

		// Add error class if the field has an error
		if (
			isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' &&
			! empty( $this->required ) && empty( $this->value ) && $this->value !== '0'
		) {
			$wrapper_class .= ' has-field-error';
		}

		$data_cond = '';
		if ( ! empty( $this->condition ) ) {
			if ( is_array( $this->condition ) && isset( $this->condition['field'], $this->condition['value'] ) ) {
				$wrapper_class .= ' wcf-field-conditional';
				$operator       = isset( $this->condition['operator'] ) ? $this->condition['operator'] : '==';
				$data_cond      = 'data-condition-field="' . esc_attr( $this->condition['field'] ) . '" ';
				$data_cond     .= 'data-condition-operator="' . esc_attr( $operator ) . '" ';
				$data_cond     .= 'data-condition-value="' . esc_attr( $this->condition['value'] ) . '"';
			}
		}

		// jika ini add taxonomy
		if ( isset( $_GET['taxonomy'] ) ) {
			if ( isset( $_GET['tag_ID'] ) ) {
				$required = ! empty( $this->required ) ? '<span class="required">*</span>' : '';
				echo '<tr class="form-field term-group-wrap">';
				echo '<th scope="row"><label for="' . esc_attr( $this->id ) . '">' . esc_html( $this->label ) . $required . '</label></th>';
				echo '<td>';
			} else {
				echo '<div class="form-field term-group">';
			}
		}

		echo '<div class="' . $wrapper_class . '" ' . $data_cond . '>';

		if ( $this->label && $this->type !== 'toggle' && $this->type !== 'checkbox' && $this->type !== 'radio' && ! isset( $_GET['tag_ID'] ) ) {
			$required = ! empty( $this->required ) ? '<span class="required">*</span>' : '';
			echo '<label class="wcf-field__label" for="' . esc_attr( $this->id ) . '"><strong>' . esc_html( $this->label ) . $required . '</strong></label>';
		}

		$required = ! empty( $this->required ) ? 'data-required="true"' : '';

		// Render field berdasarkan tipe
		$method = 'control_' . $this->type;
		if ( method_exists( $this, $method ) ) {
			$this->$method( $required );
		} else {
			$this->control_text( $required );
		}

		// Tempat untuk menampilkan notifikasi error
		if ( $this->type !== 'html' ) {
			echo '<p id="' . esc_attr( $this->id ) . '_error" class="wcf-field-error" style="display:none;"></p>';
		}

		if ( $this->description ) {
			echo '<p class="wcf-field__description">' . esc_html( $this->description ) . '</p>';
		}
		echo '</div>';

		// jika ini add taxonomy
		if ( isset( $_GET['taxonomy'] ) ) {

			if ( isset( $_GET['tag_ID'] ) ) {
				echo '</td>';
				echo '</tr>';
			} else {
				echo '</div>';
			}
		}
	}

	/**
	 * Renders an HTML content field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $required HTML required attribute if the field is required.
	 * @return void
	 */
	private function control_html( $required ) {
		echo wp_kses_post( $this->value );
	}

	/**
	 * Renders a color picker field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $required HTML required attribute if the field is required.
	 * @return void
	 */
	private function control_color( $required ) {
		// Mark color picker as required
		Assets::get_instance()->require_asset( 'colorpicker' );

		$class = 'wcf-field-color wcf-field__input';

		printf(
			'<input type="text" id="%s" name="%s" value="%s" class="%s" %s data-default-color="%s" />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			esc_attr( $class ),
			$required,
			esc_attr( $this->default ?? '' )
		);
	}

	/**
	 * Renders a rich text editor field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $required HTML required attribute if the field is required.
	 * @return void
	 */
	private function control_editor( $required ) {
		// Mark that editor is required
		Assets::get_instance()->require_asset( 'editor' );

		$editor_id = esc_attr( $this->id );
		$settings  = array(
			'textarea_name' => $this->name,
			'textarea_rows' => 6,
			'editor_class'  => 'wcf-field-editor wcf-field__input',
		);

		echo '<div class="wcf-field-editor-wrap">';
		if ( function_exists( 'wp_editor' ) ) {
			ob_start();
			wp_editor( $this->value, $editor_id, $settings );
			echo ob_get_clean();
		} else {
			printf(
				'<textarea id="%s" name="%s" rows="6" class="wcf-field-editor wcf-field__input" %s>%s</textarea>',
				$editor_id,
				esc_attr( $this->name ),
				$required,
				esc_textarea( $this->value )
			);
		}
		echo '</div>';
	}

	/**
	 * Generates HTML attributes string from the attributes array.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return string HTML attributes string.
	 */
	private function get_attributes_string() {
		$attrs = '';
		if ( ! empty( $this->attributes ) && is_array( $this->attributes ) ) {
			foreach ( $this->attributes as $key => $value ) {
				$attrs .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}
		return $attrs;
	}

	/**
	 * Renders a text input field.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $required HTML required attribute if the field is required.
	 * @return void
	 */
	private function control_text( $required ) {
		$attributes = $this->get_attributes_string();

		printf(
			'<input type="text" id="%s" name="%s" value="%s" class="wcf-field-text wcf-field__input" %s %s />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required,
			$attributes
		);
	}

	private function control_email( $required ) {
		$attributes = $this->get_attributes_string();
		printf(
			'<input type="email" id="%s" name="%s" value="%s" class="wcf-field-email wcf-field__input" %s %s />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required,
			$attributes
		);
	}

	private function control_phone( $required ) {
		$attributes = $this->get_attributes_string();
		printf(
			'<input type="tel" id="%s" name="%s" value="%s" class="wcf-field-phone wcf-field__input" %s %s />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required,
			$attributes
		);
	}

	private function control_number( $required ) {
		$attributes = $this->get_attributes_string();
		printf(
			'<input type="number" id="%s" name="%s" value="%s" class="wcf-field-number wcf-field__input" min="1" %s %s />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required,
			$attributes
		);
	}

	private function control_url( $required ) {
		$attributes = $this->get_attributes_string();
		printf(
			'<input type="url" id="%s" name="%s" value="%s" class="wcf-field-url wcf-field__input" %s %s />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required,
			$attributes
		);
	}

	private function control_textarea( $required ) {
		$attributes = $this->get_attributes_string();

		printf(
			'<textarea id="%s" name="%s" class="wcf-field-textarea wcf-field__input" %s %s>%s</textarea>',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			$required,
			$attributes,
			esc_textarea( $this->value )
		);
	}

	private function control_select( $required ) {
		$attributes = $this->get_attributes_string();

		echo '<select id="' . esc_attr( $this->id ) . '" name="' . esc_attr( $this->name ) . '" class="wcf-field-select wcf-field__input" ' . $required . ' ' . $attributes . '>';
		foreach ( $this->options as $value => $label ) {
			$selected = selected( $this->value, $value, false );
			echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}

	private function control_select2( $required ) {
		// Mark that select2 is needed
		Assets::get_instance()->require_asset( 'select2' );

		$attributes  = $this->get_attributes_string();
		$is_multiple = ! empty( $this->attributes['multiple'] );
		$name_attr   = $is_multiple ? $this->name . '[]' : $this->name;
		$placeholder = ! empty( $this->attributes['placeholder'] ) ? $this->attributes['placeholder'] : __( 'Select an option', 'wapic-fields' );
		$allow_clear = ! empty( $this->attributes['allow_clear'] ) ? 'true' : 'false';
		$width       = ! empty( $this->attributes['width'] ) ? $this->attributes['width'] : '100%';

		// Ensure selected_values is always an array
		$selected_values = array();
		if ( $is_multiple ) {
			if ( is_array( $this->value ) ) {
				$selected_values = $this->value;
			} elseif ( is_string( $this->value ) && ! empty( $this->value ) ) {
				$selected_values = array_map( 'trim', explode( ',', $this->value ) );
			}
		} else {
			$selected_values = $this->value ? (array) $this->value : array();
		}

		// Add required class for validation if needed
		$required_class = strpos( $required, 'required' ) !== false ? 'wcf-required' : '';

		// Output the select element
		echo '<select id="' . esc_attr( $this->id ) . '" 
                     name="' . esc_attr( $name_attr ) . '"
                     class="wcf-field-select2 wcf-field__input ' . esc_attr( $required_class ) . '" 
                     data-placeholder="' . esc_attr( $placeholder ) . '"
                     data-allow-clear="' . esc_attr( $allow_clear ) . '"
                     data-width="' . esc_attr( $width ) . '"',
		$is_multiple ? ' multiple="multiple"' : '',
		' ' . $required . ' ' . $attributes . '>';

		// Add placeholder option for single select
		if ( ! empty( $placeholder ) && ! $is_multiple ) {
			echo '<option value="">' . esc_html( $placeholder ) . '</option>';
		}

		// Add options
		foreach ( $this->options as $value => $label ) {
			$selected = in_array( $value, $selected_values ) ? 'selected="selected"' : '';
			echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';

		// Add script to handle select2 initialization
		echo '<script>
        jQuery(document).ready(function($) {
            $("#' . esc_js( $this->id ) . '").select2({
                width: "' . esc_js( $width ) . '",
                placeholder: "' . esc_js( $placeholder ) . '",
                allowClear: ' . $allow_clear . ',
                ' . ( $is_multiple ? 'closeOnSelect: false,' : '' ) . '
                templateResult: function(data) {
                    if (!data.id) { return data.text; }
                    return data.text;
                },
                templateSelection: function(data) {
                    if (!data.id) { return data.text; }
                    return data.text;
                }
            });
        });
        </script>';

		// Add hidden field to store the value as JSON for proper form submission
		echo '<input type="hidden" name="' . esc_attr( $this->name . '_is_array' ) . '" value="1">';
	}

	private function control_checkbox( $required ) {
		echo '<fieldset>';
		if ( $this->label ) {
			echo '<legend class="wcf-field__label"><strong>' . esc_html( $this->label ) . '</strong></legend>';
		}

		$current_values = array();
		if ( ( empty( $this->value ) || $this->value === null || $this->value === false ) && $this->default !== null ) {
			$current_values = is_array( $this->default ) ? $this->default : array( $this->default );
		} else {
			$current_values = is_array( $this->value ) ? $this->value : array( $this->value );
		}

		foreach ( $this->options as $key => $label ) {
			$checked = in_array( $key, $current_values );
			printf(
				'<label class="wcf-field-checkbox" for="%s"><input id="%s" type="checkbox" name="%s[]" value="%s" %s %s> %s</label>',
				"{$this->name}_{$key}",
				"{$this->name}_{$key}",
				esc_attr( $this->name ),
				esc_attr( $key ),
				checked( $checked, true, false ),
				$required,
				esc_html( $label )
			);
		}
		echo '</fieldset>';
	}

	private function control_radio( $required ) {
		echo '<fieldset>';
		if ( $this->label ) {
			echo '<legend class="wcf-field__label"><strong>' . esc_html( $this->label ) . '</strong></legend>';
		}

		foreach ( $this->options as $key => $label ) {
			printf(
				'<label class="wcf-field-radio" for="%s"><input id="%s" type="radio" name="%s" value="%s" %s %s> %s</label>',
				"{$this->name}_{$key}",
				"{$this->name}_{$key}",
				esc_attr( $this->name ),
				esc_attr( $key ),
				checked( $this->value, $key, false ),
				$required,
				esc_html( $label )
			);
		}
		echo '</fieldset>';
	}

	private function control_toggle( $required ) {
		$checked = ( $this->value === 'yes' || $this->value === true || $this->value === '1' || $this->value === 1 ) ? 'checked' : '';
		echo '<div class="wcf-field-toggle">';

		printf(
			'<label class="wcf-field-toggle-switch" for="%s">',
			esc_attr( $this->id )
		);

		printf(
			'<input type="hidden" name="%s" value="no">' .
				'<input type="checkbox" id="%s" name="%s" value="yes" %s %s>',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			$checked,
			$required
		);

		echo '<span class="slider"></span>';
		echo '</label>';

		echo '<strong class="wcf-field-toggle-label">' . esc_html( $this->label ) . '</strong>';

		echo '</div>';
	}

	private function control_image( $required ) {
		// Mark that media uploader is needed
		Assets::get_instance()->require_asset( 'media' );

		$img_url = $this->value ? wp_get_attachment_image_url( $this->value, 'large' ) : '';
		$label   = $img_url ? 'Edit Image' : 'Add Image';
		echo '<div id="' . esc_attr( $this->id ) . '_preview" class="wcf-field-image-preview">';
		if ( $img_url ) {
			echo '<span class="wcf-field-image-thumb">';
			echo '<img src="' . esc_url( $img_url ) . '">';
			echo '<a href="#" class="wcf-field-remove-image" title="' . esc_attr__( 'Remove image', 'wapic-fields' ) . '">×</a>';
			echo '</span>';
		}
		echo '</div>';
		printf(
			'<input type="hidden" id="%s" name="%s" value="%s" %s>',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required
		);
		echo '<button type="button" class="button wcf-field-image-upload" data-target="' . esc_attr( $this->id ) . '">' . esc_html( $label ) . '</button>';
	}

	private function control_gallery( $required ) {
		// Mark that media uploader is needed
		Assets::get_instance()->require_asset( 'media' );

		// Always store as comma-separated string in options
		$ids_str = '';

		if ( is_array( $this->value ) ) {
			// Convert array to comma-separated string
			$ids_str = implode( ',', array_filter( array_map( 'intval', $this->value ) ) );
		} elseif ( ! empty( $this->value ) ) {
			// Already a string, ensure it's clean
			$ids_str = implode( ',', array_filter( array_map( 'intval', explode( ',', $this->value ) ) ) );
		}

		// Convert back to array for display
		$ids   = $ids_str ? array_map( 'intval', explode( ',', $ids_str ) ) : array();
		$label = $ids_str ? 'Edit Gallery' : 'Add Gallery';

		// Display gallery preview
		echo '<div id="' . esc_attr( $this->id ) . '_preview" class="wcf-field-gallery-preview">';
		if ( ! empty( $ids ) ) {
			foreach ( $ids as $img_id ) {
				$img_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
				if ( $img_url ) {
					echo '<span class="wcf-field-gallery-thumb" data-id="' . esc_attr( $img_id ) . '">';
					echo '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr__( 'Gallery image', 'wapic-fields' ) . '">';
					echo '<a href="#" class="wcf-field-remove-gallery-thumb" title="' . esc_attr__( 'Remove image', 'wapic-fields' ) . '">×</a>';
					echo '</span>';
				}
			}
		}
		echo '</div>';

		// Hidden input that stores the comma-separated string
		printf(
			'<input type="hidden" id="%s" name="%s" value="%s" %s class="wcf-gallery-ids">',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $ids_str ),
			$required
		);

		// Add/Edit Gallery button
		echo '<div class="wcf-gallery-actions">';
		echo '<button type="button" class="button wcf-field-gallery-upload" data-target="' . esc_attr( $this->id ) . '">' . esc_html( $label ) . '</button>';
		echo '</div>';
	}

	private function control_date( $required ) {
		// Tandai bahwa datepicker diperlukan
		Assets::get_instance()->require_asset( 'datepicker' );

		printf(
			'<input type="text" id="%s" name="%s" value="%s" class="wcf-field-date wcf-field__input" autocomplete="off" %s />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required
		);
	}

	private function control_media_upload( $required ) {
		// Mark that media uploader is needed
		Assets::get_instance()->require_asset( 'media' );

		$img_url = $this->value ? wp_get_attachment_url( $this->value ) : '';

		echo '<div class="wcf-media-upload-wrapper">';

		// Preview container
		echo '<div class="wcf-media-upload-preview">';
		if ( $img_url ) {
			echo '<div class="wcf-media-thumb">';
			echo '<img src="' . esc_url( $img_url ) . '" alt="" />';
			echo '<a href="#" class="wcf-media-remove" title="' . esc_attr__( 'Remove', 'wapic-fields' ) . '">×</a>';
			echo '</div>';
		}
		echo '</div>';

		// URL input
		printf(
			'<input type="text" class="wcf-field__input wcf-media-url" id="%s_url" value="%s" placeholder="' . esc_attr__( 'Media URL', 'wapic-fields' ) . '" readonly />',
			esc_attr( $this->id ),
			esc_url( $img_url )
		);

		// Hidden input for attachment ID
		printf(
			'<input type="hidden" id="%s" name="%s" value="%s" %s class="wcf-media-id" />',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			$required
		);

		// Upload button
		echo '<button type="button" class="button wcf-media-upload-button" data-target="' . esc_attr( $this->id ) . '">' . esc_html__( 'Select/Upload Media', 'wapic-fields' ) . '</button>';

		echo '</div>'; // .wcf-media-upload-wrapper
	}

	private function control_file( $required ) {
		// Mark that media uploader is needed
		Assets::get_instance()->require_asset( 'media' );

		// Main container
		echo '<div class="wcf-file-upload-wrapper">';

		// Use default value if value is not set

		// URL input - this is now the main input that stores the URL directly
		printf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="wcf-field-file-url wcf-field__input" placeholder="' . esc_attr__( 'File URL', 'wapic-fields' ) . '" data-validate="url" %4$s/>',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_url( $this->value ),
			$required ? 'required' : ''
		);

		// Upload button - will need to be updated in JavaScript to work with direct URLs
		echo '<button type="button" class="button wcf-file-upload-button" data-target="' . esc_attr( $this->id ) . '">' . esc_html__( 'Select File', 'wapic-fields' ) . '</button>';
		echo '</div>';
	}

	public function validate_value( $type, $value ) {
		if ( empty( $value ) ) {
			return $value; // Let empty values pass, use required attribute in form if needed
		}

		$error = '';

		switch ( $type ) {
			case 'email':
				if ( ! is_email( $value ) ) {
					$error = __( 'Please enter a valid email address', 'wapic-fields' );
				}
				break;

			case 'number':
			case 'price':
				if ( ! is_numeric( $value ) ) {
					$error = __( 'Please enter a valid number', 'wapic-fields' );
				}
				break;

			case 'phone':
				if ( ! preg_match( '/^[0-9+\-\s()]+$/', $value ) ) {
					$error = __( 'Please enter a valid phone number', 'wapic-fields' );
				}
				break;

			case 'url':
				if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
					$error = __( 'Please enter a valid URL', 'wapic-fields' );
				}
				break;
		}

		return $error;
	}

	// Sanitasi nilai
	public function sanitize_value( $type, $value ) {
		$errors = array();

		switch ( $type ) {
			case 'select2':
				if ( empty( $value ) ) {
					return array();
				}
				if ( is_array( $value ) ) {
					// If it's an array (from multiple select), sanitize each value
					return array_filter( array_map( 'sanitize_text_field', $value ) );
				}
				// If it's a string, it might be comma-separated values
				if ( strpos( $value, ',' ) !== false ) {
					$values = array_map( 'trim', explode( ',', $value ) );
					return array_filter( array_map( 'sanitize_text_field', $values ) );
				}
				// For single value, return as array with one element
				$sanitized = sanitize_text_field( $value );
				return $sanitized !== '' ? array( $sanitized ) : array();
			case 'url':
				return esc_url_raw( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'select':
			case 'radio':
			case 'text':
				return sanitize_text_field( $value );
			case 'checkbox':
				return array_map( 'sanitize_text_field', (array) $value );
			case 'image':
				return intval( $value );
			case 'gallery':
				if ( is_array( $value ) ) {
					$filtered = array_filter( array_map( 'intval', $value ) );
					return implode( ',', $filtered );
				} else {
					$filtered = array_filter( array_map( 'intval', explode( ',', $value ) ) );
					return implode( ',', $filtered );
				}
			case 'toggle':
				return ( $value === 'yes' || $value === true || $value === '1' || $value === 1 ) ? 'yes' : 'no';
			case 'number':
				// Sanitasi angka, jika bukan angka return null
				return is_numeric( $value ) ? $value + 0 : null;
			case 'email':
				return sanitize_email( $value );
			case 'phone':
				return sanitize_text_field( $value );
			case 'date':
				// Validasi format tanggal yyyy-mm-dd
				if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
					return sanitize_text_field( $value );
				}
				return '';
			default:
				return $value;
		}
	}

	// Panel render
	public function start_controls_panel( $args = array() ) {

		$args = wp_parse_args(
			$args,
			array(
				'title' => __( 'Panel', 'wapic-fields' ),
				'id'    => 'custom-panel',
				'type'  => 'setting',
			)
		);

		if ( $args['type'] === 'setting' ) {
			echo '<div class="wcf">';
			echo '<div class="wcf-page-header"><h1 class="wcf-page-header__title">' . $args['title'] . '</h1></div>';
			echo '<form id="option" method="post" action="options.php">';
			settings_fields( $args['id'] );
			do_settings_sections( $args['id'] );
			settings_errors( $args['id'] );

			echo '<div class="wcf-container">';
			echo '<div class="wcf-panel is-style-compact">';
		}

		if ( $args['type'] === 'metabox' ) {
			echo '<div class="wcf-panel is-style-compact">';
		}
	}

	public function end_controls_panel( $args = array() ) {

		$args = wp_parse_args(
			$args,
			array(
				'type' => 'setting',
			)
		);

		if ( $args['type'] === 'setting' ) {
			echo '</div>';

			// Submit panel
			echo '<div class="wcf-panel is-style-outline" style="flex:0 0 278px;">';
			echo '<div class="wcf-panel__header"><h2 class="wcf-panel__title">Action</h2></div>';
			submit_button();
			echo '</div>';

			echo '</div>';
			echo '</form>';
			echo '</div>';
		}

		if ( $args['type'] === 'metabox' ) {
			echo '</div>';
		}
	}

	// Section render
	public function start_controls_section( $tabs ) {
		echo '<div class="wcf-tabs">';
		echo '<ul class="wcf-tabs-nav">';
		foreach ( $tabs as $tab => $label ) {
			echo '<li><a href="#tab-' . esc_attr( $tab ) . '"><strong>' . esc_html( $label ) . '</strong></a></li>';
		}
		echo '</ul>';
	}

	public function end_controls_section() {
		echo '</div>';
	}

	// Group render
	public function start_controls_group( $args ) {
		echo '<div class="wcf-tab-content" id="tab-' . esc_attr( $args['id'] ) . '">';
	}

	public function end_controls_group() {
		echo '</div>';
	}
}
