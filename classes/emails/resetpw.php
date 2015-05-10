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
	 * @since 2.0.0
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
	 * @since 2.0.0
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

		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$title = get_option( 'wplf_lostpassword_subject', sprintf( __( '[%s] Password Reset' ), $blogname ) );

		$template = new WP_Login_Flow_Template();
		$template_data = array(
			'wp_user_name'      => $user_login,
			'wp_user_email'     => $user_data->user_email,
			'wp_reset_pw_key'   => $key,
			'wp_reset_pw_url'   => $this->reset_url( $key, $user_login )
		);

		$message = $template->generate( 'wplf_lostpassword_message', $message, $template_data );

		if ( $message && ! wp_mail( $user_data->user_email, wp_specialchars_decode( $title ), $message, 'Content-type: text/html' ) )
			wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function.' ) );

		// return false to prevent email from being sent twice
		return false;
	}

	/**
	 * Generate Password Reset URL
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $key
	 * @param $user_login
	 *
	 * @return bool|null|string
	 */
	function reset_url( $key, $user_login ){

		$std = "wp-login.php?action=rp&key={$key}&login={$user_login}";
		return WP_Login_Flow_Rewrite::get_url( 'reset_pw', $std, "{$user_login}/{$key}/", true );

	}

}