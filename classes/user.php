<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User extends WP_Login_Flow {

	protected $status_option = 'wplf_activated';
	protected $auth;
	protected $activation;

	function __construct() {

		$this->activation = new WP_Login_Flow_User_Activation();
		$this->auth = new WP_Login_Flow_User_Auth();

	}

	function activation(){
		if( ! $this->activation ) $this->activation = new WP_Login_Flow_User_Activation();
		return $this->activation;
	}

	function auth() {
		if ( ! $this->auth ) $this->auth = new WP_Login_Flow_User_Auth();
		return $this->auth;
	}

}