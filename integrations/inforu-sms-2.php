<?php
namespace ElementorInforuSms\Integrations;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Inforu_Sms2 extends Inforu_Sms {

	public function get_name() {
		return 'inforu-sms2';
	}

	public function get_label() {
		return 'Inforu SMS 2';
	}

	protected function get_control_id( $control_id ) {
		return $control_id . '_2';
	}
}
