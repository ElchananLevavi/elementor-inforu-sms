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
			$this->get_control_id('inforu_sms'),
			[
				'label' => $this->get_label(),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			$this->get_control_id('inforu_user'),
			[
				'label' => __( 'InforU Username', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXT,
                'render_type' => 'none',
			]
		);

		$widget->add_control(
			$this->get_control_id('inforu_api_token'),
			[
				'label' => __( 'InforU API Token', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXT,
                'render_type' => 'none',
			]
		);

        $widget->add_control(
			$this->get_control_id('sms_sender'),
			[
				'label' => __( 'From Name', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXT,
                'default' => get_bloginfo( 'name' ),
                'render_type' => 'none',
			]
		);

        $widget->add_control(
			$this->get_control_id('sms_text'),
			[
				'label' => __( 'Text', 'elementor-inforu-sms' ),
				'type' => Controls_Manager::TEXTAREA,
                'description' => __( 'To customize sent fields, copy the shortcode that appears inside each field and paste it above.', 'elementor-inforu-sms' ),
                'render_type' => 'none',
			]
		);

        $widget->add_control(
			$this->get_control_id('phone_number'),
			[
				'label' => __( 'Phone Number', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
                'description' => __( 'To get the number from the form, copy the shortcode that appears inside the phone field and paste it above. To send the message as notification to known phone number, just write the number here.', 'elementor-inforu-sms' ),
                'render_type' => 'none',
			]
		);
        
        $widget->add_control(
			$this->get_control_id( 'add_form_metadata' ),
			[
				'label' => __( 'Meta Data', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'label_block' => true,
				'separator' => 'before',
				'default' => [
					'date',
					'time',
					'page_url',
				],
				'options' => [
					'date' => __( 'Date', 'elementor-pro' ),
					'time' => __( 'Time', 'elementor-pro' ),
					'page_url' => __( 'Page URL', 'elementor-pro' ),
				],
				'render_type' => 'none',
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

		$fields = [
			'inforu_user' => '',
			'inforu_api_token' => '',
			'sms_sender' => get_bloginfo( 'name' ),
			'sms_text' => '',
			'phone_number' => '',
		];

        foreach ( $fields as $key => $default ) {
			$setting = trim( $settings[ $this->get_control_id( $key ) ] );
			$setting = $record->replace_setting_shortcodes( $setting );
			if ( ! empty( $setting ) ) {
				$fields[ $key ] = $setting;
			}
		}

        $form_meta = '';

		$form_metadata_settings = $settings[ $this->get_control_id( 'add_form_metadata' ) ];

		foreach ( $record->get( 'meta' ) as $id => $field ) {
			if ( in_array( $id, $form_metadata_settings ) ) {
				$form_meta .= " [" . $this-> field_formatted($field)  . "] ";
			}
		}

		if ( ! empty( $form_meta ) ) {
			$fields['sms_text'] .=  $form_meta;
		}

		$message_text = preg_replace( "/\r|\n/", "", $fields['sms_text']); // remove line breaks
        $xml = '';
		$xml .= '<Inforu>'.PHP_EOL;
		$xml .= ' <User>'.PHP_EOL;
		$xml .= ' <Username>'.$fields['inforu_user'].'</Username>'.PHP_EOL;
        $xml .= ' <ApiToken>'.$fields['inforu_api_token'].'</ApiToken>'.PHP_EOL; 
		$xml .= ' </User>'.PHP_EOL;
		$xml .= ' <Content Type="sms">'.PHP_EOL;
		$xml .= ' <Message>'.htmlspecialchars($message_text).'</Message>'.PHP_EOL;
		$xml .= ' </Content>'.PHP_EOL;
		$xml .= ' <Recipients>'.PHP_EOL;
		$xml .= ' <PhoneNumber>'.$fields['phone_number'].'</PhoneNumber>'.PHP_EOL;
		$xml .= ' </Recipients>'.PHP_EOL;
		$xml .= ' <Settings>'.PHP_EOL;
		$xml .= ' <Sender>'.$fields['sms_sender'].'</Sender>'.PHP_EOL;
		$xml .= ' </Settings>'.PHP_EOL;
		$xml .= '</Inforu>';

        $sms_sent = wp_remote_post( 'http://uapi.inforu.co.il/SendMessageXml.ashx', ['body'=>'InforuXML='.urlencode($xml)] );
       
	}

	// Allow overwrite the control_id with a prefix, @see inforu_sms2
	protected function get_control_id( $control_id ) {
		return $control_id;
	}

    private function field_formatted( $field ) {
		$formatted = '';
		if ( ! empty( $field['title'] ) ) {
			$formatted = sprintf( '%s: %s', $field['title'], $field['value'] );
		} elseif ( ! empty( $field['value'] ) ) {
			$formatted = sprintf( '%s', $field['value'] );
		}

		return $formatted;
	}
}
