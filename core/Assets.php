<?php
/**
 * Wapic Fields - Assets Management
 *
 * @package    Wapic_Fields
 * @subpackage Core
 * @since      1.0.0
 * @author     Wapic Team
 * @license    GPL-2.0+
 * @link       https://wapiclo.com/
 */

namespace Wapic_Fields;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the management of CSS and JavaScript assets for the Wapic Fields plugin.
 *
 * This class is responsible for loading and managing all the necessary assets (CSS, JS)
 * required by the Wapic Fields plugin. It implements a singleton pattern to ensure
 * only one instance of the class is created.
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Holds the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var Assets|null
	 */
	private static $instance = null;

	/**
	 * Tracks which assets need to be loaded.
	 *
	 * @since 1.0.0
	 * @var array<string, bool>
	 */
	private $required_assets = array(
		'select2'     => false,
		'colorpicker' => false,
		'datepicker'  => false,
		'media'       => false,
		'editor'      => false,
	);

	/**
	 * Gets the singleton instance of the Assets class.
	 *
	 * @since 1.0.0
	 *
	 * @return self The Assets class instance.
	 */
	public static function get_instance(): ?self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Marks an asset as required to be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @param string $asset The asset key to mark as required.
	 * @return void
	 */
	public function require_asset( $asset ) {
		if ( isset( $this->required_assets[ $asset ] ) ) {
			$this->required_assets[ $asset ] = true;
		}
	}

	/**
	 * Enqueues all required assets based on what's been marked as required.
	 *
	 * This method should be called on the appropriate WordPress hook (e.g., 'admin_enqueue_scripts').
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_assets() {
    
		// Load JavaScript libraries
		if ( $this->required_assets['select2'] ) {
			wp_enqueue_style( 'select2', WAPIC_FIELDS_ASSETS . 'assets/select2/select2.min.css' );
			wp_enqueue_script( 'select2', WAPIC_FIELDS_ASSETS . 'assets/select2/select2.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'wapic-field-select2', WAPIC_FIELDS_ASSETS . 'assets/js/select2.min.js', array(), WAPIC_FIELDS_VERSION, true );
		}

		if ( $this->required_assets['colorpicker'] ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'wapic-field-colorpicker', WAPIC_FIELDS_ASSETS . 'assets/js/colorpicker.min.js', array(), WAPIC_FIELDS_VERSION, true );	
		}

		if ( $this->required_assets['datepicker'] ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'wapic-field-datepicker', WAPIC_FIELDS_ASSETS . 'assets/js/datepicker.min.js', array(), WAPIC_FIELDS_VERSION, true );
		}

		if ( $this->required_assets['media'] ) {
			wp_enqueue_media();
			wp_enqueue_script( 'wapic-field-media-upload', WAPIC_FIELDS_ASSETS . 'assets/js/media-upload.min.js', array(), WAPIC_FIELDS_VERSION, true );
		}

		if ( $this->required_assets['editor'] ) {
			wp_enqueue_editor();
			// Panggil stylesheet bawaan editor
			wp_enqueue_style('wp-tinymce-skin',site_url('/wp-includes/js/tinymce/skins/lightgray/skin.min.css'),array(),null);
			wp_enqueue_style('wp-editor-core',site_url('/wp-includes/css/editor.min.css'),array(),get_bloginfo('version'));
		}

		// Enqueue the main admin script as a module
		$this->enqueue_admin_script();
	}

	/**
	 * Enqueues the main admin script and related assets.
	 *
	 * This method handles the registration and enqueuing of the main admin JavaScript file
	 * along with its dependencies. It also localizes the script with necessary data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function enqueue_admin_script() {
		// Register the script with type='module'
		wp_enqueue_style( 'wapic-field', WAPIC_FIELDS_ASSETS . 'assets/css/styles.min.css', array(), WAPIC_FIELDS_VERSION );
		wp_enqueue_script( 'wapic-field-tab', WAPIC_FIELDS_ASSETS . 'assets/js/tab.min.js', array(), WAPIC_FIELDS_VERSION, true );
		wp_enqueue_script( 'wapic-field-validation', WAPIC_FIELDS_ASSETS . 'assets/js/validation.min.js', array(), WAPIC_FIELDS_VERSION, true );
		wp_enqueue_script( 'wapic-field-conditional', WAPIC_FIELDS_ASSETS . 'assets/js/conditional.min.js', array(), WAPIC_FIELDS_VERSION, true );

		// Needed for repeater sortable feature
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Needed for image/gallery media frames
		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		// Repeater assets
		wp_enqueue_style( 'wapic-field-repeater', WAPIC_FIELDS_ASSETS . 'assets/css/repeater.min.css', array( 'wapic-field' ), WAPIC_FIELDS_VERSION );
		wp_enqueue_script( 'wapic-field-repeater', WAPIC_FIELDS_ASSETS . 'assets/js/repeater.min.js', array( 'jquery' ), WAPIC_FIELDS_VERSION, true );

		// Localize script with necessary data
		wp_localize_script(
			'wapic-field-validation',
			'wapic_field',
			array(
				'validation' => array(
					'requiredMessage'     => esc_html__( 'This field is required', 'wapic-fields' ),
					'validEmail'          => esc_html__( 'Please enter a valid email address', 'wapic-fields' ),
					'validUrl'            => esc_html__( 'Please enter a valid URL', 'wapic-fields' ),
					'validNumber'         => esc_html__( 'Please enter a valid number', 'wapic-fields' ),
					'minNumber'           => esc_html__( 'Value must be at least %s', 'wapic-fields' ),
					'maxNumber'           => esc_html__( 'Value must be at most %s', 'wapic-fields' ),
					'compareRegularPrice' => esc_html__( 'Regular price must be greater than sale price', 'wapic-fields' ),
					'compareSalePrice'    => esc_html__( 'Sale price must be less than regular price', 'wapic-fields' ),
					'submitFailed'        => esc_html__( 'Oops! Form submission failed due to validation issues. Please review the highlighted fields:', 'wapic-fields' ),
				),
			)
		);
	}
}
