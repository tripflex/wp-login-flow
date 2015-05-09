<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Mail extends WP_Login_Flow {

	protected $email;
	protected $name;

	function __construct() {

		add_filter( 'wp_mail_from', array( $this, 'email' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'name' ) );
	}

	/**
	 * Set custom email from email address
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $email
	 *
	 * @return mixed|void
	 */
	public function email( $email ) {

		$enable_custom_email = get_option( 'wplf_from_email_enable' );

		// Check if custom email is enabled, and set if is valid email
		if( ! empty( $enable_custom_email ) ){
			$custom_email = get_option( 'wplf_from_email' );
			if( is_email( $custom_email ) ) $email = $custom_email;
		}

		$this->email = $email;

		return $email;
	}

	/**
	 * Set custom email from name
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $name
	 *
	 * @return mixed|void
	 */
	public function name( $name ) {

		$enable_custom_name = get_option( 'wplf_from_name_enable' );

		// Check if custom name is enabled, and set if is valid name
		if ( ! empty( $enable_custom_name ) ) {
			$custom_name = get_option( 'wplf_from_name' );
			if ( $custom_name ) $name = $custom_name;
		}

		$this->name = $name;

		return $name;
	}

}