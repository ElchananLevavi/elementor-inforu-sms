<?php
namespace ElementorInforuSms\Integrations;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Inforu_Sms extends Action_Base {

	public function get_name() {
		return 'inforu-sms';
	}

	public function get_label() {
		return 'Inforu SMS';
	}

	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'inforu_sms',
			[
				'label' => __( 'Inforu SMS', 'elementor-inforu-sms' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'inforu_user',
			[
				'label' => __( 'InforU Username', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$widget->add_control(
			'inforu_api_token',
			[
				'label' => __( 'InforU API Token', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXT,
			]
		);

        $widget->add_control(
			'sms_sender',
			[
				'label' => __( 'From Name', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXT,
                'default' => get_bloginfo( 'name' ),
			]
		);

        $widget->add_control(
			'sms_text',
			[
				'label' => __( 'Text', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXTAREA,
                'description' => __( 'To customize sent fields, copy the shortcode that appears inside each field and paste it above.', 'elementor-inforu-sms' ),
			]
		);

        $widget->add_control(
			'phone_number',
			[
				'label' => __( 'Phone Number', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
                'description' => __( 'To get the number from the form, copy the shortcode that appears inside the phone field and paste it above. To send the message as notification to known phone number, just write the number here.', 'elementor-inforu-sms' ),
			]
		);
        
        $widget->end_controls_section();
	}

    
	public function on_export( $element ) {
		$controls_to_unset = [
			'inforu_user',
			'inforu_api_token',
			'sms_sender',
			'sms_text',
			'phone_number',
		];
        
		foreach ( $controls_to_unset as $base_id ) {
			$control_id = $this->get_control_id( $base_id );
			unset( $element['settings'][ $control_id ] );
		}

		return $element;
	}


	public function run( $record, $ajax_handler ) {
        error_log('start sendidng');
	
		$settings = $record->get( 'form_settings' );

		if ( empty( $settings['inforu_user'] ) ) {
			return;
		}

		if ( empty( $settings['inforu_api_token'] ) ) {
			return;
		}

		if ( empty( $settings['sms_text'] ) ) {
			return;
		}

		if ( empty( $settings['phone_number'] ) ) {
			return;
		}

        foreach ( $settings as $key => $setting ) {
			$setting = $record->replace_setting_shortcodes( $setting );
			if ( ! empty( $setting ) ) {
				$settings[ $key ] = $setting;
			}
		}

		$message_text = preg_replace( "/\r|\n/", "", $settings['sms_text']); // remove line breaks
        $xml = '';
		$xml .= '<Inforu>'.PHP_EOL;
		$xml .= ' <User>'.PHP_EOL;
		$xml .= ' <Username>'.$settings['inforu_user'].'</Username>'.PHP_EOL;
        $xml .= ' <ApiToken>'.$settings['inforu_api_token'].'</ApiToken>'.PHP_EOL; 
		$xml .= ' </User>'.PHP_EOL;
		$xml .= ' <Content Type="sms">'.PHP_EOL;
		$xml .= ' <Message>'.htmlspecialchars($message_text).'</Message>'.PHP_EOL;
		$xml .= ' </Content>'.PHP_EOL;
		$xml .= ' <Recipients>'.PHP_EOL;
		$xml .= ' <PhoneNumber>'.$settings['phone_number'].'</PhoneNumber>'.PHP_EOL;
		$xml .= ' </Recipients>'.PHP_EOL;
		$xml .= ' <Settings>'.PHP_EOL;
		$xml .= ' <Sender>'.$settings['sms_sender'].'</Sender>'.PHP_EOL;
		$xml .= ' </Settings>'.PHP_EOL;
		$xml .= '</Inforu>';

        $response = wp_remote_post( 'http://uapi.inforu.co.il/SendMessageXml.ashx', ['body'=>'InforuXML='.urlencode($xml)] );
    
	}

}
