<?php
/**
 * Server validation using the same field definitions as the renderer.
 *
 * @package Wapic_Fields
 */

declare(strict_types=1);

namespace Wapic_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Validate submitted field values independently of browser flags. */
class Validation {

	/**
	 * Validate a scope of sibling fields, including nested repeater scopes.
	 *
	 * @param array  $definitions Field schemas.
	 * @param array  $values Unslashed submitted values, keyed by logical field ID.
	 * @param string $prefix Internal nested field path.
	 * @return array Error messages keyed by field path.
	 */
	public static function fields( array $definitions, array $values, string $prefix = '' ): array {
		$schemas = array();
		foreach ( $definitions as $definition ) {
			if ( is_array( $definition ) && ! empty( $definition['id'] ) ) {
				$schemas[ (string) $definition['id'] ] = $definition;
			}
		}
		$errors = array();
		foreach ( $schemas as $id => $definition ) {
			if ( ! self::visible( $id, $schemas, $values ) ) {
				continue;
			}
			$type = (string) ( $definition['type'] ?? 'text' );
			if ( in_array( $type, array( 'html', 'heading', 'separator' ), true ) ) {
				continue;
			}
			$path       = '' === $prefix ? $id : $prefix . '[' . $id . ']';
			$label      = (string) ( $definition['label'] ?? $id );
			$value      = $values[ $id ] ?? null;
			$collection = in_array( $type, array( 'repeater', 'checkbox', 'select2', 'gallery' ), true );
			if ( ( is_array( $value ) && ! $collection ) || is_object( $value ) ) {
				$errors[ $path ] = $label . ': ' . __( 'Invalid field value.', 'wapic-fields' );
				continue;
			}
			if ( 'repeater' === $type ) {
				if ( null !== $value && '' !== $value && ! is_array( $value ) ) {
					$errors[ $path ] = $label . ': ' . __( 'Invalid repeater value.', 'wapic-fields' );
					continue;
				}
				$rows        = is_array( $value ) ? array_values( $value ) : array();
				[$min, $max] = Fields\Repeater::row_limits( $definition );
				if ( ! empty( $definition['required'] ) && ! count( $rows ) ) {
					$errors[ $path ] = $label . ': ' . __( 'This field is required', 'wapic-fields' );
				} elseif ( count( $rows ) < $min || ( $max > 0 && count( $rows ) > $max ) ) {
					$errors[ $path ] = $label . ': ' . __( 'The number of items is outside the allowed limits.', 'wapic-fields' );
				}
				foreach ( $rows as $index => $row ) {
					$row_path = $path . '[' . $index . ']';
					if ( ! is_array( $row ) ) {
						$errors[ $row_path ] = $label . ': ' . __( 'Invalid repeater item.', 'wapic-fields' );
						continue;
					}
					$children = self::fields( (array) ( $definition['fields'] ?? array() ), $row, $row_path );
					foreach ( $children as $child_path => $message ) {
						$errors[ $child_path ] = $label . ' / ' . ( $index + 1 ) . ' / ' . $message;
					}
				}
				continue;
			}
			if ( is_array( $value ) && count(
				array_filter(
					$value,
					static function ( $item ) {
						return ! is_scalar( $item ) && null !== $item;
					}
				)
			) ) {
				$errors[ $path ] = $label . ': ' . __( 'Invalid field value.', 'wapic-fields' );
				continue;
			}
			if ( ! empty( $definition['required'] ) && self::empty_value( $type, $value ) ) {
				$errors[ $path ] = $label . ': ' . __( 'This field is required', 'wapic-fields' );
				continue;
			}
			// Existing format validators expect scalar values.
			$message = is_array( $value ) ? '' : Field::validate_value( $type, $value );
			if ( '' !== $message ) {
				$errors[ $path ] = $label . ': ' . $message;
			}
		}
		return $errors;
	}

	/**
	 * Determine meaningful presence without treating numeric zero as empty.
	 *
	 * @param string $type Field type.
	 * @param mixed  $value Submitted value.
	 * @return bool
	 */
	private static function empty_value( string $type, $value ): bool {
		if ( null === $value || false === $value ) {
			return true;
		}
		if ( 'toggle' === $type ) {
			return Field::sanitize_value( 'toggle', $value ) !== 'yes';
		}
		$value = Field::sanitize_value( $type, $value );
		if ( 'image' === $type ) {
			return $value <= 0;
		}
		if ( 'gallery' === $type ) {
			return ! count(
				array_filter(
					explode( ',', (string) $value ),
					static function ( $id ) {
						return (int) $id > 0;
					}
				)
			);
		}
		if ( is_array( $value ) ) {
			return ! count(
				array_filter(
					$value,
					static function ( $item ) {
						return trim( (string) $item ) !== '';
					}
				)
			);
		}
		if ( 'editor' === $type && ! preg_match( '/<(img|video|audio|iframe)\b/i', (string) $value ) ) {
			$value = html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' );
			$value = str_replace( "\xc2\xa0", ' ', $value );
		}
		return trim( (string) $value ) === '';
	}

	/**
	 * Resolve conditional visibility within this scope, including cascades.
	 *
	 * @param string $id Logical field ID.
	 * @param array  $schemas Sibling definitions keyed by ID.
	 * @param array  $values Submitted sibling values.
	 * @param array  $trail Dependency traversal, for cycle detection.
	 * @return bool
	 */
	private static function visible( string $id, array $schemas, array $values, array $trail = array() ): bool {
		if ( ! isset( $schemas[ $id ] ) || isset( $trail[ $id ] ) ) {
			return false;
		}
		$trail[ $id ] = true;
		$condition    = (array) ( $schemas[ $id ]['condition'] ?? array() );
		if ( ! $condition ) {
			return true;
		}
		$conditions = isset( $condition['field'] ) || isset( $condition['id'] ) ? array( $condition ) : $condition;
		$relation   = strtoupper( (string) ( $condition['relation'] ?? 'AND' ) );
		$matches    = array();
		foreach ( $conditions as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$dependency = (string) ( $rule['field'] ?? $rule['id'] ?? '' );
			if ( ! isset( $schemas[ $dependency ] ) ) {
				$matches[] = false;
				continue;
			}
			$current = self::visible( $dependency, $schemas, $values, $trail ) ? ( $values[ $dependency ] ?? '' ) : '';
			if ( 'toggle' === ( $schemas[ $dependency ]['type'] ?? '' ) && '' !== $current ) {
				$current = Field::sanitize_value( 'toggle', $current ) === 'yes' ? 'yes' : '';
			}
			$matches[] = self::compare( $current, $rule['value'] ?? '', (string) ( $rule['operator'] ?? $rule['compare'] ?? '==' ) );
		}
		return ! $matches || ( 'OR' === $relation ? in_array( true, $matches, true ) : ! in_array( false, $matches, true ) );
	}

	/**
	 * Compare values using the operators supported by the browser engine.
	 *
	 * @param mixed  $current Dependency value.
	 * @param mixed  $expected Expected condition value.
	 * @param string $operator Comparison operator.
	 * @return bool
	 */
	private static function compare( $current, $expected, string $operator ): bool {
		$actual = is_array( $current ) ? implode( ',', array_filter( $current, 'is_scalar' ) ) : ( is_scalar( $current ) ? (string) $current : '' );
		$target = is_scalar( $expected ) ? (string) $expected : '';
		switch ( $operator ) {
			case '!=':
			case '<>':
				return $actual !== $target;
			case '>':
				return is_numeric( $actual ) && is_numeric( $target ) && (float) $actual > (float) $target;
			case '<':
				return is_numeric( $actual ) && is_numeric( $target ) && (float) $actual < (float) $target;
			case '>=':
				return is_numeric( $actual ) && is_numeric( $target ) && (float) $actual >= (float) $target;
			case '<=':
				return is_numeric( $actual ) && is_numeric( $target ) && (float) $actual <= (float) $target;
			case 'IN':
			case 'NOT IN':
				$choices = is_array( $expected ) ? array_map( 'strval', array_filter( $expected, 'is_scalar' ) ) : array_map( 'trim', explode( ',', $target ) );
				$match   = in_array( $actual, $choices, true );
				return 'IN' === $operator ? $match : ! $match;
			default:
				return $actual === $target;
		}
	}
}
