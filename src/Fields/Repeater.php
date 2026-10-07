<?php
/**
 * Repeater container and schema-aware sanitization.
 *
 * @package Wapic_Fields
 */

declare(strict_types=1);

namespace Wapic_Fields\Fields;

use Wapic_Fields\Assets;
use Wapic_Fields\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Repeating container which delegates child rendering to the field factory. */
class Repeater extends Field {

	/**
	 * Normalize row limits, treating max_rows = 0 as unlimited.
	 *
	 * @param array $args Repeater definition.
	 * @return array Minimum and maximum row count.
	 */
	public static function row_limits( array $args ): array {
		$min = max( 0, (int) ( $args['min_rows'] ?? 0 ) );
		$max = max( 0, (int) ( $args['max_rows'] ?? 0 ) );
		return array( $min, $max > 0 ? max( $min, $max ) : 0 );
	}

	/**
	 * Sanitize only declared child fields using the existing type sanitizers.
	 *
	 * @param mixed $value Submitted nested array (already unslashed by the caller).
	 * @param array $args Complete repeater definition, including fields.
	 * @return array Sanitized, sequential rows.
	 */
	public static function sanitize_rows( $value, array $args ): array {
		[$min, $max] = self::row_limits( $args );
		$rows        = array();
		foreach ( is_array( $value ) ? $value : array() as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$clean = array();
			foreach ( (array) ( $args['fields'] ?? array() ) as $field ) {
				if ( ! is_array( $field ) || empty( $field['id'] ) ) {
					continue;
				}
				$type = (string) ( $field['type'] ?? 'text' );
				if ( in_array( $type, array( 'html', 'heading', 'separator' ), true ) ) {
					continue;
				}
				$id          = (string) $field['id'];
				$empty_value = in_array( $type, array( 'checkbox', 'select2', 'repeater' ), true ) ? array() : '';
				$child_value = $row[ $id ] ?? ( $type === 'toggle' ? 'no' : $empty_value );
				// Reject malformed arrays for scalar types before calling their sanitizer.
				if ( is_array( $child_value ) && ! in_array( $type, array( 'repeater', 'checkbox', 'select2', 'gallery' ), true ) ) {
					$child_value = '';
				}
				$schema       = $type === 'repeater' ? $field : (array) ( $field['attributes'] ?? array() );
				$clean[ $id ] = Field::sanitize_value( $type, $child_value, $schema );
			}
			$rows[] = $clean;
			if ( $max > 0 && count( $rows ) >= $max ) {
				break;
			}
		}
		// Use sanitized blank rows to enforce min_rows consistently on the server.
		if ( count( $rows ) < $min ) {
			$blank_args = array_merge(
				$args,
				array(
					'min_rows' => 0,
					'max_rows' => 0,
				)
			);
			$blank      = self::sanitize_rows( array( array() ), $blank_args )[0];
			$rows       = array_pad( $rows, $min, $blank );
		}
		return $rows;
	}

	/**
	 * Sanitize this container with its own schema.
	 *
	 * @param mixed $value Submitted value.
	 * @return array
	 */
	public function sanitize( $value ) {
		return self::sanitize_rows( $value, $this->config );
	}

	/**
	 * Render saved rows and an inert template for dynamic rows.
	 *
	 * @param string $required_attr Required attribute from the parent renderer.
	 * @return void
	 */
	protected function render_input( string $required_attr ): void {
		Assets::get_instance()->require_asset( 'repeater' );
		[$min, $max] = self::row_limits( $this->config );
		$missing     = ! is_array( $this->value );
		$rows        = is_array( $this->value ) ? array_values( array_filter( $this->value, 'is_array' ) ) : array();
		$count       = $missing ? max( $min, (int) ( $this->config['default_rows'] ?? 0 ) ) : max( $min, count( $rows ) );
		if ( $max > 0 ) {
			$count = min( $count, $max );
			$rows  = array_slice( $rows, 0, $max );
		}
		$rows       = array_pad( $rows, $count, array() );
		$token      = '__' . wp_unique_id( 'wcf_row_' ) . '__';
		$attributes = array(
			'data-name-prefix'     => $this->name,
			'data-row-token'       => $token,
			'data-next-key'        => count( $rows ),
			'data-min-rows'        => $min,
			'data-max-rows'        => $max,
			'data-sortable'        => ( $this->config['sortable'] ?? true ) ? 'true' : 'false',
			'data-confirm-delete'  => ! empty( $this->config['confirm_delete'] ) ? 'true' : 'false',
			'data-confirm-message' => $this->config['confirm_delete_message'] ?? __( 'Are you sure you want to delete this row?', 'wapic-fields' ),
			'data-remove-label'    => __( 'Remove', 'wapic-fields' ),
			'data-cancel-label'    => __( 'Cancel', 'wapic-fields' ),
			'data-row-label'       => $this->config['row_label'] ?? '{{title}}',
			'data-fallback-label'  => $this->label !== '' ? $this->label : __( 'Row', 'wapic-fields' ),
		);
		echo '<div id="' . esc_attr( $this->id ) . '" class="wcf-repeater wcf-repeater--block"';
		foreach ( $attributes as $key => $value ) {
			echo ' ' . esc_attr( $key ) . '="' . esc_attr( (string) $value ) . '"';
		}
		echo '>';
		// A scalar sentinel submits an empty repeater when its last row is removed.
		echo '<input type="hidden" class="wcf-repeater-empty" name="' . esc_attr( $this->name ) . '" value="">';
		echo '<div class="wcf-repeater-rows">';
		foreach ( $rows as $index => $row ) {
			$this->render_row( (string) $index, $row, $index );
		}
		echo '</div><template class="wcf-repeater-template">';
		$this->render_row( $token, array(), 0 );
		echo '</template><button type="button" class="button wcf-repeater-add"' . ( $max > 0 && count( $rows ) >= $max ? ' disabled' : '' ) . '>';
		echo esc_html( $this->config['button_label'] ?? __( 'Add Row', 'wapic-fields' ) );
		echo '</button></div>';
	}

	/**
	 * Render a row through the normal field factory with nested input names.
	 *
	 * @param string $key Stable row identity or template token.
	 * @param array  $values Row values.
	 * @param int    $index User-facing row position.
	 * @return void
	 */
	private function render_row( string $key, array $values, int $index ): void {
		$label_values = array();
		foreach ( (array) ( $this->config['fields'] ?? array() ) as $definition ) {
			if ( is_array( $definition ) && isset( $definition['id'], $values[ $definition['id'] ] ) ) {
				$label_values[ $definition['id'] ] = $values[ $definition['id'] ];
			}
		}
		$label = (string) ( $this->config['row_label'] ?? '{{title}}' );
		$label = preg_replace_callback(
			'/\{\{\s*([\w-]+)\s*\}\}/',
			static function ( $placeholder ) use ( $label_values ) {
				return isset( $label_values[ $placeholder[1] ] ) && is_scalar( $label_values[ $placeholder[1] ] ) ? (string) $label_values[ $placeholder[1] ] : '';
			},
			$label
		);
		$label = trim( (string) $label );
		echo '<div class="wcf-repeater-row is-collapsed" data-row-index="' . esc_attr( (string) $index ) . '"><div class="wcf-repeater-header">';
		if ( $this->config['sortable'] ?? true ) {
			echo '<button type="button" class="wcf-repeater-handle" aria-label="' . esc_attr__( 'Drag to reorder row', 'wapic-fields' ) . '"><span class="dashicons dashicons-menu" aria-hidden="true"></span></button>';
		}
		$fallback   = $this->label !== '' ? $this->label : __( 'Row', 'wapic-fields' );
		$heading    = $label !== '' ? $label : $fallback . ' - ' . ( $index + 1 );
		$content_id = $this->id . '-' . $key . '-content';
		echo '<button type="button" class="wcf-repeater-collapse" aria-expanded="false" aria-controls="' . esc_attr( $content_id ) . '" aria-label="' . esc_attr__( 'Expand row', 'wapic-fields' ) . '" data-collapse-label="' . esc_attr__( 'Collapse row', 'wapic-fields' ) . '" data-expand-label="' . esc_attr__( 'Expand row', 'wapic-fields' ) . '"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>';
		echo '<strong class="wcf-repeater-title">' . esc_html( $heading ) . '</strong>';
		echo '<span class="wcf-repeater-warning" hidden aria-label="' . esc_attr__( 'This row has validation errors', 'wapic-fields' ) . '"><span class="dashicons dashicons-warning" aria-hidden="true"></span></span>';
		echo '<button type="button" class="wcf-repeater-delete" aria-label="' . esc_attr__( 'Delete row', 'wapic-fields' ) . '"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button></div><div id="' . esc_attr( $content_id ) . '" class="wcf-repeater-content">';
		foreach ( (array) ( $this->config['fields'] ?? array() ) as $field ) {
			if ( ! is_array( $field ) || empty( $field['id'] ) ) {
				continue;
			}
			$id                       = (string) $field['id'];
			$field['_repeater_child'] = true;
			$field['_repeater_key']   = $id;
			$field['id']              = $this->id . '-' . $key . '-' . $id;
			$field['name']            = $this->name . '[' . $key . '][' . $id . ']';
			$field['value']           = array_key_exists( $id, $values ) ? $values[ $id ] : ( $field['default'] ?? '' );
			// Saved empty values must not be replaced by child defaults.
			$field['default'] = $field['value'];
			Field::add_control( $field );
		}
		echo '</div></div>';
	}
}
