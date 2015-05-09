<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Emails_ResetPW extends WP_Login_Flow_Emails {

	function __construct() {

		add_filter( 'retrieve_password_title', array( $this, 'title' ), 9999, 1 );
		add_filter( 'retrieve_password_message', array( $this, 'message' ), 9999, 4 );

	}

	/**
	 * Lost Password Email Title
	 *
	 *
	 * @since @@version
	 *
	 * @param $title
	 *
	 * @return mixed|void
	 */
	function title( $title ){

		return get_option( 'wplf_lostpassword_subject', $title );

	}

	/**
	 * Lost Password Email Message
	 *
	 *
	 * @since @@version
	 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $message    Default mail message.
	 * @param string  $key        The activation key.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user_data  WP_User object.
	 *
	 * @return string
	 */
	function message( $message, $key, $user_login, $user_data ){

		if( ! get_option( 'wplf_lostpassword_message' ) ) return $message;

		$template = new WP_Login_Flow_Template();
		$template_data = array(
			'wp_user_name'      => $user_login,
			'wp_user_email'     => $user_data->user_email,
			'wp_reset_pw_key'   => $key,
			'wp_reset_pw_url'   => $this->reset_url( $key, $user_login )
		);

		$message = $template->generate( 'wplf_lostpassword_message', $message, $template_data );

		return $message;
	}

	/**
	 * Generate Password Reset URL
	 *
	 *
	 * @since @@version
	 *
	 * @param $key
	 * @param $user_login
	 *
	 * @return bool|null|string
	 */
	function reset_url( $key, $user_login ){

		$std = "wp-login.php?action=rp&key={$key}&login={$user_login}";
		return WP_Login_Flow_Rewrite::get_url( 'reset_pw', $std, "{$user_login}/{$key}" );

	}

}