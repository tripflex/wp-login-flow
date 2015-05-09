<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class WP_Login_Flow_Rewrite {

		private       $step;
		private       $action;
		private       $doing_redirect = FALSE;
		public static $prevent_rewrite;

		function __construct() {

			add_action( 'shutdown', array($this, 'check_for_updates') );
			add_filter( 'lostpassword_url', array($this, 'lostpassword_url'), 9999, 2 );
			add_filter( 'login_url', array($this, 'login_url'), 9999, 2 );
			add_filter( 'register_url', array($this, 'register_url'), 9999, 1 );
			add_filter( 'site_url', array($this, 'site_url'), 9999, 4 );
			add_filter( 'wp_redirect', array($this, 'site_url_redirect'), 9999, 2 );

		}

		/**
		 * Check redirect for lost pw
		 *
		 * Check if the redirect is for the set password page for lost password.
		 * This needs to be checked as the cookie will need to be set again if
		 * using permalinks/rewrites.
		 *
		 *
		 * @since @@version
		 *
		 * @param $location
		 *
		 * @return bool|null|string
		 */
		function check_redirect_lostpw( $location ){

			// Non-permalink password reset URL used
			$wp_login = strstr( $location, 'wp-login.php' );
			if ( $wp_login !== 'wp-login.php?action=rp' ) return false;

			$site_url    = get_site_url();
			$redirect    = self::get_url( 'lost_pw', $location, 'rp' );
			$cookie_path = self::get_url( 'lost_pw', $location );
			$this->set_newpass_cookie( $site_url, $cookie_path );

			return $redirect;
		}

		/**
		 * Check redirect for activate pw
		 *
		 * Check if the redirect is for the set password page for activating account.
		 * This needs to be checked as the cookie will need to be set again if using
		 * permalinks/rewrites.
		 *
		 *
		 * @since @@version
		 *
		 * @param $location
		 *
		 * @return bool|null|string
		 */
		function check_redirect_setpw( $location ){

			if ( $this->get_step() !== 'activate' ) return false;

			$site_url    = get_site_url();
			$redirect    = self::get_url( 'activate', $location, 'password' );
			$cookie_path = self::get_url( 'activate', $location );
			$this->set_newpass_cookie( $site_url, $cookie_path );

			return $redirect;
		}

		/**
		 * Set new cookie for setting password
		 *
		 * The core WordPress wp-login.php file sets a cookie based on specific path/location
		 * and if we're using permalinks/rewrites we need to set the cookie again under our
		 * new path/location.
		 *
		 * @since @@version
		 *
		 * @param $site_url
		 * @param $cookie_path
		 */
		function set_newpass_cookie( $site_url, $cookie_path ){

			$value       = sprintf( '%s:%s', wp_unslash( $_GET[ 'login' ] ), wp_unslash( $_GET[ 'key' ] ) );
			$cookie_path = str_replace( $site_url, '', $cookie_path );
			setcookie( 'wp-resetpass-' . COOKIEHASH, $value, 0, $cookie_path, COOKIE_DOMAIN, is_ssl(), TRUE );

		}

		/**
		 * Handle wp_redirect rewrites
		 *
		 * The core wp-login.php does not use permalinks/rewrites and as such it has
		 * hard-coded wp-login.php urls.  We use a filter on wp_redirect to redirect
		 * to a rewrite/permalink if enabled.
		 *
		 * @since 1.0.0
		 *
		 * @param $location
		 * @param $status
		 *
		 * @return string
		 */
		function site_url_redirect( $location, $status ) {

			// If status is 302 redirect, with rp action and key/login set we need to check
			// if cookie needs to be set for new path.  This must be above wp-login.php check
			// in case rewrites are used.
			if( $status === 302 && $this->get_action() === "rp" && isset( $_GET[ 'key' ] ) && isset( $_GET[ 'login' ] ) ){
				$redirect = $this->check_redirect_lostpw( $location );
				$redirect = $this->check_redirect_setpw( $location );
				if( $redirect ) return $redirect;
			}

			// No need to process if location to redirect is not wp-login.php
			if ( strpos( $location, 'wp-login.php' ) === FALSE ) return $location;

			$site_url = get_site_url();
			$path     = str_replace( $site_url, '', $location );
			$location = $this->site_url( $location, $path, NULL, NULL );

			return $location;
		}

		/**
		 * Filter the site URL
		 *
		 * This filter is hooked onto the site_url() function and will be called
		 * anytime that function is used in WordPress.  We have to use this to be
		 * able to filter out the non-permalink/rewrite URLs.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $url     The complete site URL including scheme and path.
		 * @param string      $path    Path relative to the site URL. Blank string if no path is specified.
		 * @param string|null $scheme  Scheme to give the site URL context. Accepts 'http', 'https', 'login',
		 *                             'login_post', 'admin', 'relative' or null.
		 * @param int|null    $blog_id Blog ID, or null for the current blog.
		 *
		 * @return string
		 */
		function site_url( $url, $path, $scheme, $blog_id ) {

			// No need to process if path is not wp-login.php
			if ( strpos( $url, 'wp-login.php' ) === FALSE ) return $url;

			$args = strstr( $path, '?' );

			// Basic wp-login.php argument URLs
			switch( $args ){

				case "?action=lostpassword":
					return self::get_url( 'lost_pw', $url );
					break;

				case "?action=register":
					return self::get_url( 'register', $url );
					break;

				case "?checkemail=confirm":
					return self::get_url( 'lost_pw', $url, 'confirm' );
					break;

				case "?registration=disabled":
					return self::get_url( 'register', $url, 'disabled' );
					break;

				case "?loggedout=true":
					return self::get_url( 'loggedout', $url );
					break;
			}

			// Custom and modified wp-login.php argument URLs
			switch( $args ){

				case "?action=resetpass":
					if ( $this->get_step() === 'setpw' ) return self::get_url( 'activate', $url, 'password' );
					return self::get_url( 'lost_pw', $url, 'resetpass' );
					break;

				case "?action=lostpassword&error=expiredkey":
					if ( $this->get_step() === 'setpw' ) return self::get_url( 'activate', $url, 'expired' );
					return self::get_url( 'lost_pw', $url, 'expired' );
					break;

				case "?action=lostpassword&error=invalidkey":
					if ( $this->get_step() === 'setpw' ) return self::get_url( 'activate', $url, 'invalid' );
					return self::get_url( 'lost_pw', $url, 'invalid' );
					break;

				case "?action=rp":
					if ( $this->get_step() === 'activate' ) return self::get_url( 'activate', $url, 'password' );
					return self::get_url( 'lost_pw', $url, 'rp' );
					break;

				case "?action=activation&step=pending":
					return self::get_url( 'activate', $url, 'pending' );
					break;

				case "?action=rp&step=setpw":
					return self::get_url( 'activate', $url, 'password' );
					break;

				case "":
					return self::get_url( 'login', $url );
					break;

				default:
					return $url;
			}

			return $url;

		}

		/**
		 * Get WP Login Flow Custom URL
		 *
		 * Internal WP Login Flow method used to create a custom permalink/rewrite URL
		 * or return the standard URL if rewrites are not enabled/set.
		 *
		 * @since 1.0.0
		 *
		 * @param           $name
		 * @param bool|null $original_url
		 * @param null      $extra_rewrite
		 *
		 * @return bool|null|string
		 */
		static function get_url( $name, $original_url = FALSE, $extra_rewrite = NULL ) {

			$enabled = get_option( "wplf_rewrite_{$name}" );
			$slug    = get_option( "wplf_rewrite_{$name}_slug" );

			// If rewrite not enabled or no slug value return original URL or FALSE
			if ( ! $enabled || ! $slug ) return $original_url;

			$build_url = home_url() . "/{$slug}";
			if ( $extra_rewrite ) $build_url .= "/{$extra_rewrite}";

			return $build_url;
		}

		/**
		 *
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param null $options
		 *
		 * @return bool
		 */
		function set_rewrite_rules( $options = NULL ) {

			if ( self::$prevent_rewrite ) return FALSE;

			// Set known variable variables to false to prevent PHP notices
			$login = $lost_pw = $activate = $register = $loggedout = FALSE;
			// Get all options and filter out only the rewrite ones
			$settings = WP_Login_Flow_Settings::get_settings( 'rewrites' );
			$options  = array_column_recursive( $settings, 'name' );
			$options  = array_filter( $options, array($this, 'filter_rewrite_options') );

			foreach ( $options as $option ) {
				$enabled  = get_option( $option );
				$value    = get_option( $option . '_slug' );
				$variable = str_replace( 'wplf_rewrite_', '', $option );
				if ( $enabled ) ${$variable} = $value; // wplf_rewrite_lost_pw is @var $lost_pw
			}

			/** @var self $login */
			if ( $login ) add_rewrite_rule( $login . '/?', 'wp-login.php', 'top' );

			/** @var self $lost_pw */
			if ( $lost_pw ) {
				add_rewrite_rule( $lost_pw . '/rp/?', 'wp-login.php?action=rp', 'top' );
				add_rewrite_rule( $lost_pw . '/resetpass/?', 'wp-login.php?action=resetpass', 'top' );
				add_rewrite_rule( $lost_pw . '/confirm/?', 'wp-login.php?checkemail=confirm', 'top' );
				add_rewrite_rule( $lost_pw . '/expired/?', 'wp-login.php?action=lostpassword&error=expiredkey', 'top' );
				add_rewrite_rule( $lost_pw . '/invalid/?', 'wp-login.php?action=lostpassword&error=invalidkey', 'top' );
				add_rewrite_rule( $lost_pw . '/?', 'wp-login.php?action=lostpassword', 'top' );
				add_rewrite_rule( $lost_pw . '/([^/]*)/([^/]*)/', 'wp-login.php?action=rp&key=$2&login=$1', 'top' );
			}

			/** @var self $activate */
			if ( $activate ) {
				add_rewrite_rule( $activate . '/pending/?', 'wp-login.php?action=activation&step=pending', 'top' );
				add_rewrite_rule( $activate . '/password/?', 'wp-login.php?action=rp&step=setpw', 'top' );
				add_rewrite_rule( $activate . '/invalid/?', 'wp-login.php?action=lostpassword&error=invalidkey&step=activate', 'top' );
				add_rewrite_rule( $activate . '/expired/?', 'wp-login.php?action=lostpassword&error=expiredkey&step=activate', 'top' );
				add_rewrite_rule( $activate . '/([^/]*)/([^/]*)/', 'wp-login.php?action=rp&key=$2&login=$1&step=activate', 'top' );
			}

			/** @var self $register */
			if ( $register ) {
				add_rewrite_rule( $register . '/disabled/?', 'wp-login.php?registration=disabled', 'top' );
				add_rewrite_rule( $register . '/?', 'wp-login.php?action=register', 'top' );
			}

			/** @var self $loggedout */
			if ( $loggedout ) add_rewrite_rule( $loggedout . '/?', 'wp-login.php?loggedout=true', 'top' );

		}

		/**
		 * Check for rewrite updates
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		function check_for_updates() {

			$option_page = ( isset( $_POST[ 'option_page' ] ) ? $_POST[ 'option_page' ] : NULL );
			$action      = ( isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : NULL );

			if ( $option_page === 'wp_login_flow' && $action === 'update' ) {
				$this->set_rewrite_rules();
				flush_rewrite_rules();
			}

		}

		/**
		 * Prevent Rewrites
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param $prevent
		 */
		static function prevent_rewrite( $prevent ) {

			self::$prevent_rewrite = $prevent;

		}

		/**
		 * Filter Rewrite Options
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param $option
		 *
		 * @return bool
		 */
		function filter_rewrite_options( $option ) {

			$rewrite = strpos( $option, 'wplf_rewrite' ) !== FALSE;
			$slug    = strpos( $option, '_slug' ) !== FALSE;
			if ( $rewrite && $slug ) $rewrite = FALSE;

			return $rewrite;
		}

		/**
		 * Filter for login_url()
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param $url
		 * @param $redirect
		 *
		 * @return string
		 */
		function login_url( $url, $redirect ) {

			if ( $redirect ) $redirect = "?redirect_to={$redirect}";
			if ( ! self::get_url( 'login' ) ) return $url . $redirect;

			return self::get_url( 'login' ) . $redirect;
		}

		/**
		 * Filter for lostpassword_url()
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param $url
		 * @param $redirect
		 *
		 * @return string
		 */
		function lostpassword_url( $url, $redirect ) {

			if ( $redirect ) $redirect = "?redirect_to={$redirect}";
			if ( ! self::get_url( 'lost_pw' ) ) return $url . $redirect;

			return self::get_url( 'lost_pw' ) . $redirect;
		}

		/**
		 * Filter for register_url()
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param $url
		 *
		 * @return bool|null|string
		 */
		function register_url( $url ) {

			if ( ! self::get_url( 'register' ) ) return $url;

			return self::get_url( 'register' );
		}

		/**
		 * Get current step
		 *
		 * Checks var step and returns if set, otherwise gets current
		 * step from $_GET['step']
		 *
		 * @since @@version
		 *
		 * @return mixed
		 */
		function get_step() {

			if ( empty( $this->step ) ) $this->step = filter_input( INPUT_GET, 'step', FILTER_SANITIZE_URL );

			return $this->step;

		}

		/**
		 * Get current action
		 *
		 * Checks var action and returns if set, otherwise gets current
		 * action from $_GET['action']
		 *
		 * @since @@version
		 *
		 * @return mixed
		 */
		function get_action() {

			if ( empty( $this->action ) ) $this->action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_URL );

			return $this->action;

		}

	}