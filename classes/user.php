<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_User extends WP_Login_Flow {

	protected $auth;
	protected $activation;
	protected $list_table;

	function __construct() {

		$this->activation = new WP_Login_Flow_User_Activation();
		$this->auth = new WP_Login_Flow_User_Auth();
		$this->list_table = new WP_Login_Flow_User_List_Table();
		add_filter( 'gettext', array($this, 'change_user_strings'), 1 );
	}

	/**
	 * Change any required strings related to users
	 *
	 * Use WordPress built in translation to handle changing wording of any required
	 * user strings.
	 *
	 * @since 2.0.0
	 *
	 * @param $text
	 *
	 * @return string|void
	 */
	function change_user_strings( $text ) {

		if ( $text === 'Send Password?' ) return __( 'Require Activation?' );
		if ( $text === 'Send this password to the new user by email.' ) return __( 'Send an email to new user with link to activate and set password.' );

		return $text;

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