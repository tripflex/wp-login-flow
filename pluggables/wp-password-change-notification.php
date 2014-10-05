<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wp_password_change_notification' ) ) :
	/**
	 * Notify the blog admin of a user changing password, normally via email.
	 *
	 * @since 2.7.0
	 *
	 * @param object $user User Object
	 */
	/**
	 * @param object $user
	 */
	function wp_password_change_notification( &$user ) {

		// Check is password reset was triggered by user activating account and setting password
		if ( ! WP_Login_Flow::is_activated( $user->ID ) ) {
			WP_Login_Flow::set_activated( $user->ID );
			login_header( __( 'Password Saved' ), '<p class="message reset-pass">' . WP_Login_Flow::get_locale_password_set() . '<br>You can now <a href="' . esc_url( wp_login_url() ) . '">' . __( 'Log in' ) . '</a></p>' );
			login_footer();
			exit;
		}

		// send a copy of password change notification to the admin
		// but check to see if it's the admin whose password we're changing, and skip this
		if ( 0 !== strcasecmp( $user->user_email, get_option( 'admin_email' ) ) ) {
			$message = sprintf( __( 'Password Lost and Changed for user: %s' ), $user->user_login ) . "\r\n";
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] Password Lost/Changed' ), $blogname ), $message );
		}
	}

endif;