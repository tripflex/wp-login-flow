<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_User
 *
 * @since @@version
 *
 */
class WP_Login_Flow_User extends WP_Login_Flow {

	/**
	 * @var \WP_Login_Flow_User_Auth
	 */
	protected $auth;
	/**
	 * @var \WP_Login_Flow_User_Activation
	 */
	protected $activation;
	/**
	 * @var \WP_Login_Flow_User_List_Table
	 */
	protected $list_table;

	/**
	 * WP_Login_Flow_User constructor.
	 */
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

	/**
	 *
	 *
	 *
	 * @return \WP_Login_Flow_User_Activation
	 * @since @@version
	 *
	 */
	function activation(){
		if( ! $this->activation ) $this->activation = new WP_Login_Flow_User_Activation();
		return $this->activation;
	}

	/**
	 *
	 *
	 *
	 * @return \WP_Login_Flow_User_Auth
	 * @since @@version
	 *
	 */
	function auth() {
		if ( ! $this->auth ) $this->auth = new WP_Login_Flow_User_Auth();
		return $this->auth;
	}

}