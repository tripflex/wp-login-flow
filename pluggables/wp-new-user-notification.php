<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Prevent Wordpress from sending default notification, instead use our custom one
if ( ! function_exists( 'wp_new_user_notification' ) ) :

	/**
	 * @param        $user_id
	 * @param string $plaintext_pass
	 *
	 * @return bool
	 */
	function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {

		global $wpdb, $wp_hasher;

		$user = new WP_User( $user_id );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, FALSE );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, TRUE );
		}

		$hashed = $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

		// Set option needs to be activated
		WP_Login_Flow::set_activated( $user_id, FALSE );

		$message = __( 'Thank you for registering your account:' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'In order to set your password and access the site, please visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( WP_Login_Flow::get_wplogin() . "?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		if ( is_multisite() ) {
			$blogname = $GLOBALS[ 'current_site' ]->site_name;
		} else
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
		{
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		// New User Admin Notification
		$admin_message = sprintf( __( 'New user registration on %s:' ), get_option( 'blogname' ) ) . "\r\n\r\n";
		$admin_message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$admin_message .= sprintf( __( 'E-mail: %s' ), $user_email ) . "\r\n";

		@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), get_option( 'blogname' ) ), $admin_message );

		$title = sprintf( __( '[%s] Account Activation' ), $blogname );
		if ( ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

endif;
