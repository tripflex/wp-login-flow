<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Mail extends WP_Login_Flow {

	protected $email;
	protected $name;

	function __construct() {

		add_filter( 'wp_mail_from', array( $this, 'email' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'name' ) );
	}

	// Prevent auto login or other login forms from allowing user to login when pending activation

	public function email( $email ) {

		$this->email = $email;

		return $email;
	}

	public function name( $name ) {

		$this->name = $name;

		return $name;
	}

}