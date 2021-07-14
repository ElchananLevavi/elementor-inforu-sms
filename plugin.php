<?php
namespace ElementorInforuSms;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Module;
use ElementorInforuSms\Integrations\Inforu_Sms;


/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.0
 */
class Plugin {

	/**
	 * Instance
	 *
	 * @since 1.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function register_form_integrations() {
		require __DIR__ . '/integrations/inforu-sms.php';

		/** @var Module $forms */
		$forms = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' );
		$forms->add_form_action( 'inforu_sms', new Inforu_Sms() );
	}


	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
        $this->add_actions();
        add_action( 'elementor_pro/init', [ $this, 'register_form_integrations' ] );
	}

    /**
	 * Add Actions
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function add_actions() {

	}

}

// Instantiate Plugin Class
Plugin::instance();
