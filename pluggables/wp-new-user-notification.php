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
		$activation = new WP_Login_Flow_User_Activation();
		$activation->set( $user_id, 1 );

		$template_data = array( 'wp_user_name' => $user_login, 'wp_user_email' => $user_email, 'wp_activation_key' => $hashed,
		                        'wp_activate_url' => $activation->get_url( $hashed, $user_login ) );

		$template = new WP_Login_Flow_Template();
		$subject = get_option( 'wplf_activation_subject' );
		$message = get_option( 'wplf_activation_message' );
		$subject = $template->replace_tags( $subject, $template_data );
		$message = $template->replace_tags( $message, $template_data );

		// New User Activation Email
		if ( ! wp_mail( $user_email, wp_specialchars_decode( $subject ), $message ) ) return FALSE;

		return TRUE;

	}

endif;
