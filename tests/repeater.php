<?php
/**
 * Integration checks for repeater fields.
 *
 * Run with WP-CLI: wp --skip-plugins --skip-themes eval-file tests/repeater.php
 *
 * @package Wapic_Fields
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

use Wapic_Fields\Field;

$checks = 0;
$check  = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- CLI assertion, no HTML output.
	}
	++$checks;
};
$schema = array(
	'id'     => 'features',
	'type'   => 'repeater',
	'fields' => array(
		array(
			'id'   => 'title',
			'type' => 'text',
		),
		array(
			'id'         => 'price',
			'type'       => 'number',
			'attributes' => array(
				'min' => 0,
				'max' => 100,
			),
		),
		array(
			'id'   => 'content',
			'type' => 'editor',
		),
		array(
			'id'   => 'link',
			'type' => 'url',
		),
		array(
			'id'   => 'enabled',
			'type' => 'toggle',
		),
		array(
			'id'   => 'tags',
			'type' => 'select2',
		),
		array(
			'id'   => 'heading',
			'type' => 'heading',
		),
		array(
			'id'     => 'children',
			'type'   => 'repeater',
			'fields' => array(
				array(
					'id'   => 'title',
					'type' => 'text',
				),
			),
		),
	),
);
$input  = array(
	7     => array(
		'title'      => '<b>First</b>',
		'price'      => 200,
		'content'    => '<p>Safe</p><script>alert(1)</script>',
		'link'       => 'javascript:alert(1)',
		'enabled'    => 'yes',
		'tags'       => array( 'a', 'b' ),
		'unexpected' => 'discard',
		'children'   => array( array( 'title' => '<b>Child</b>' ) ),
	),
	12    => array(
		'title' => 'Second',
		'price' => -20,
	),
	'bad' => 'malformed',
);
$clean  = Field::sanitize_value( 'repeater', $input, $schema );
$check( count( $clean ) === 2 && array_keys( $clean ) === array( 0, 1 ), 'Rows must be sequential and malformed rows rejected.' );
$check( $clean[0]['title'] === 'First' && $clean[0]['price'] === 100 && $clean[1]['price'] === 0, 'Child sanitizers and numeric bounds must be applied.' );
$check( strpos( $clean[0]['content'], '<script>' ) === false && strpos( $clean[0]['content'], '<p>' ) !== false && $clean[0]['link'] === '', 'Editor HTML and URL must use their existing sanitizers.' );
$check( ! isset( $clean[0]['unexpected'] ) && ! isset( $clean[0]['heading'] ), 'Undeclared and presentation fields must not be stored.' );
$check( $clean[0]['children'][0]['title'] === 'Child' && $clean[0]['tags'] === array( 'a', 'b' ) && $clean[1]['enabled'] === 'no', 'Nested children, Select2, and omitted toggles must sanitize correctly.' );
$check( Field::sanitize_value( 'repeater', '', $schema ) === array(), 'Deleting the last row must save an empty array.' );
$check( count( Field::sanitize_value( 'repeater', $input, array_merge( $schema, array( 'max_rows' => 1 ) ) ) ) === 1, 'Server must enforce max_rows.' );
$check( count( Field::sanitize_value( 'repeater', array(), array_merge( $schema, array( 'min_rows' => 2 ) ) ) ) === 2, 'Server must enforce min_rows.' );
$check( Field::sanitize_value( 'text', '<b>Legacy</b>' ) === 'Legacy', 'Existing sanitizer calls must remain compatible.' );

// Register the existing WordPress option sanitizer and verify a real database round trip.
$option = '_wcf_repeater_test_' . wp_generate_uuid4();
register_setting(
	'wcf_repeater_tests',
	$option,
	array(
		'sanitize_callback' => static function ( $value ) use ( $schema ) {
			return Field::sanitize_value( 'repeater', $value, $schema );
		},
	)
);
try {
	update_option( $option, $input, false );
	$check( get_option( $option ) === $clean, 'Settings API must store the complete nested array as one option.' );
	update_option( $option, '' );
	$check( get_option( $option ) === array(), 'Empty repeater must survive save and reload.' );
} finally {
	delete_option( $option );
	unregister_setting( 'wcf_repeater_tests', $option );
}

ob_start();
Field::add_control(
	array_merge(
		$schema,
		array(
			'value'        => array(),
			'default_rows' => 3,
		)
	)
);
$html = ob_get_clean();
$check( strpos( $html, 'name="features[0][title]"' ) === false, 'Saved empty array must not recreate default_rows.' );
ob_start();
Field::add_control(
	array_merge(
		$schema,
		array(
			'value'        => false,
			'default_rows' => 2,
		)
	)
);
$html = ob_get_clean();
$check( strpos( $html, 'name="features[1][title]"' ) !== false && strpos( $html, '<template' ) !== false, 'Missing value must initialize default rows and an inert template.' );
ob_start();
Field::add_control(
	array_merge(
		$schema,
		array(
			'label' => 'Features (Block)',
			'value' => array( array() ),
		)
	)
);
$html = ob_get_clean();
$check( strpos( $html, '>Features (Block) - 1</strong>' ) !== false && strpos( $html, 'wcf-repeater-index' ) === false, 'Blank row header must append the row number after the repeater label.' );
ob_start();
Field::add_control(
	array_merge(
		$schema,
		array(
			'label' => 'Features (Block)',
			'value' => array( array( 'title' => 'Fast Performance' ) ),
		)
	)
);
$label_html = ob_get_clean();
$check( strpos( $label_html, '>Fast Performance</strong>' ) !== false && strpos( $label_html, 'Fast Performance - 1' ) === false, 'Field-derived headings must omit the row number.' );
$check( strpos( $html, 'aria-expanded="false"' ) !== false && strpos( $html, 'aria-controls="features-0-content"' ) !== false && strpos( $html, 'wcf-repeater-row is-collapsed' ) !== false, 'Rows must load collapsed with accessible controls.' );
$handle_position = strpos( $html, 'class="wcf-repeater-handle"' );
$arrow_position  = strpos( $html, 'class="wcf-repeater-collapse"' );
$title_position  = strpos( $html, 'class="wcf-repeater-title"' );
$warning_position = strpos( $html, 'class="wcf-repeater-warning"' );
$check( $handle_position < $arrow_position && $arrow_position < $title_position && $title_position < $warning_position, 'Header order must be drag handle, arrow, title, then warning marker.' );
WP_CLI::success( $checks . ' repeater integration checks passed.' );
