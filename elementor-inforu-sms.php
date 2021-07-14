<?php
/**
 * Plugin Name:       Elementor Inforu SMS
 * Description:       Send an SMS to the user after Elementor form submission.
 * Plugin URI:  https://ha-ayal.co.il/
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            HaAyal studio
 * Author URI:  https://ha-ayal.co.il/
 * License:           GPL v2 or later
 * Text Domain:       elementor-inforu-sms
 * Domain Path:       /languages
 */

 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class Elementor_Inforu_Sms {

	/**
	 * Plugin Version
	 *
	 * @since 1.0
	 * @var string The plugin version.
	 */
	const VERSION = '1.0';

	const MINIMUM_ELEMENTOR_VERSION = '2.8.0';

	const MINIMUM_PHP_VERSION = '7.0';

	public function __construct() {

		// Load translation
		add_action( 'init', array( $this, 'i18n' ) );

		// Init Plugin
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 * Fired by `init` action hook.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function i18n() {
		load_plugin_textdomain( 'elementor-inforu-sms' );
	}

	/**
	 * Initialize the plugin
	 *
	 * Validates that Elementor is already loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed include the plugin class.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
			return;
		}

		// Once we get here, We have passed all validation checks so we can safely include our plugin
		require_once( 'plugin.php' );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'elementor-inforu-sms' ),
			'<strong>' . esc_html__( 'Elementor Inforu SMS', 'elementor-inforu-sms' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-inforu-sms' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-inforu-sms' ),
			'<strong>' . esc_html__( 'Elementor Inforu SMS', 'elementor-inforu-sms' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-inforu-sms' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-inforu-sms' ),
			'<strong>' . esc_html__( 'Elementor Inforu SMS', 'elementor-inforu-sms' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'elementor-inforu-sms' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}
}


new Elementor_Inforu_Sms();
