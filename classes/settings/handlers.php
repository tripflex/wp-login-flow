<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Settings_Handlers extends WP_Login_Flow_Settings_Fields {

	public function button_handler( $input ) {

		if ( empty( $_POST[ 'button_submit' ] ) || ( $this->process_count > 0 ) ) return $input;

		$action = filter_input( INPUT_POST, 'button_submit', FILTER_SANITIZE_STRING );

		switch ( $action ) {

			case 'remove_all':

				$this->fields()->remove_all_fields();
				$this->add_updated_alert( __( 'All custom posts removed!' ) );
				break;

			case 'purge_options':

				$purged = $this->fields()->cpt()->purge_options();

				if ( ! is_array( $purged ) ) {
					$this->add_error_alert( __( 'There are not any fields that need options purged.' ) );
					break;
				}

				$count         = $purged[ 'count' ];
				$purged_fields = $purged[ 'purged_fields' ];

				$this->add_updated_alert( __( 'Options were purged from' ) . " {$count} " . __( 'fields:' ) . '<br/>' . implode( ', ', $purged_fields ) );
				break;

		}

		$this->process_count ++;

		return FALSE;

	}

	function add_updated_alert( $message ) {

		add_settings_error( $this->settings_group, esc_attr( 'settings_updated' ), $message, 'updated' );

	}

	function add_error_alert( $message ) {

		add_settings_error( $this->settings_group, esc_attr( 'settings_error' ), $message, 'error' );

	}

	public function submit_handler( $input ) {

		if ( empty( $input ) || ! empty( $_POST[ 'button_submit' ] ) ) return FALSE;

		return $input;

	}

}