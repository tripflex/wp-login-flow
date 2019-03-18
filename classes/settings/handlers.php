<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Settings_Handlers extends WP_Login_Flow_Settings_Fields {

	/**
	 * Handle Button Field Type
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $input
	 *
	 * @return bool
	 */
	public function button_handler( $input ) {

		if ( empty( $_POST[ 'button_submit' ] ) || ( $this->process_count > 0 ) ) return $input;

		$action = filter_input( INPUT_POST, 'button_submit', FILTER_SANITIZE_STRING );

		switch ( $action ) {

			case 'reset_default':

				$this->remove_all();
				$this->add_updated_alert( __( 'All field configurations removed!' ) );
				break;

		}

		$this->process_count ++;

		return FALSE;

	}

	/**
	 * Generate backup download
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function download_backup(){

		check_ajax_referer( "wp_login_flow_dl_backup", "wp_login_flow_dl_backup" );

		$option_keys = array_column_recursive( WP_Login_Flow_Settings::get_settings(), 'name' );
		if ( ! is_array( $option_keys ) ) return FALSE;

		$option_config = array();

		foreach ( $option_keys as $option ) {
			$option_config[ $option ] = get_option( $option );
		}

		$option_json = json_encode( $option_config );
		ob_clean();
		echo $option_json;
		/**
		 * Generate the JSON file and trigger download
		 * Taken from wp-admin/includes/export.php
		 */
		$filename = 'wp-login-flow.' . date( 'Y-m-d' ) . '.json';
		header( 'Content-Description: File Transfer' );
		header( "Content-Disposition: attachment; filename=$filename" );
		header( "Content-Type: text/json; charset=" . get_option( 'blog_charset' ), TRUE );
		die();
	}

	/**
	 * Remove all settings options
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function remove_all() {

		$option_keys = array_column_recursive( WP_Login_Flow_Settings::get_settings(), 'name' );
		if( ! is_array( $option_keys ) ) return false;

		foreach( $option_keys as $option ){

			delete_option( $option );

		}

	}

	/**
	 * Add updated alert
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $message
	 */
	function add_updated_alert( $message ) {

		add_settings_error( $this->settings_group, esc_attr( 'settings_updated' ), $message, 'updated' );

	}

	/**
	 * Add error alert
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $message
	 */
	function add_error_alert( $message ) {

		add_settings_error( $this->settings_group, esc_attr( 'settings_error' ), $message, 'error' );

	}

	/**
	 * Handle submit button
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $input
	 *
	 * @return bool
	 */
	public function submit_handler( $input ) {

		if ( empty( $input ) || ! empty( $_POST[ 'button_submit' ] ) ) return FALSE;
		return $input;

	}

	public function repeatable_handler( $input ) {

		if ( empty( $input ) || ! empty( $_POST[ 'button_submit' ] ) ) return FALSE;

//		if ( is_array( $input ) ) {
//			$input = maybe_serialize( $input );
//		}

		return $input;

	}

	/**
	 * Handle permalinks being disabled or enabled
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	static function permalinks_disabled(){
		$permalink = get_option( 'permalink_structure' );
		if( empty( $permalink ) ) return true;

		return false;
	}

}