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

			$wp_login = strstr( $location, 'wp-login.php' );

			$site_url = get_site_url();
			$full_url = get_site_url( null, $wp_login );

			if( $status === 302 && $this->get_action() === "rp" && isset( $_GET[ 'key' ] ) && isset( $_GET[ 'login' ] ) ){

				// Non-permalink password reset URL used
				if( $wp_login === 'wp-login.php?action=rp' ){
					$redirect = $this->get_url( 'lost_pw', $location, 'rp' );
					$cookie_path = $this->get_url( 'lost_pw', $location );
				}

				if( $this->get_step() === 'activate' ){
					$redirect = $this->get_url( 'activate', $location, 'password' );
					$cookie_path = $this->get_url( 'activate', $location );
				}

				$value      = sprintf( '%s:%s', wp_unslash( $_GET[ 'login' ] ), wp_unslash( $_GET[ 'key' ] ) );
				$cookie_path = str_replace( $site_url, '', $cookie_path );
				setcookie( 'wp-resetpass-' . COOKIEHASH, $value, 0, $cookie_path, COOKIE_DOMAIN, is_ssl(), TRUE );

				return $redirect;

			}

			// No need to process if location to redirect is not wp-login.php
			if ( strpos( $location, 'wp-login.php' ) === FALSE ) return $location;

			//$is_activation = $this->check_activation_redirect();
			//if ( $is_activation ) return $is_activation;


			$path     = str_replace( $site_url, '', $location );

			$location = $this->site_url( $location, $path, NULL, NULL );

			return $location;
		}

		/**
		 * Filter the site URL.
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

			switch( $args ){

				case "?action=lostpassword":
					return $this->get_url( 'lost_pw', $url );
					break;

				case "?action=register":
					return $this->get_url( 'register', $url );
					break;

				case "?checkemail=confirm":
					return $this->get_url( 'lost_pw', $url, 'confirm' );
					break;

				case "?registration=disabled":
					return $this->get_url( 'register', $url, 'disabled' );
					break;

				case "?loggedout=true":
					return $this->get_url( 'loggedout', $url );
					break;
			}

			switch( $args ){

				case "?action=resetpass":
					if ( $this->get_step() === 'setpw' ) return $this->get_url( 'activate', $url, 'password' );
					return $this->get_url( 'lost_pw', $url, 'resetpass' );
					break;

				case "?action=lostpassword&error=expiredkey":
					if ( $this->get_step() === 'setpw' ) return $this->get_url( 'activate', $url, 'expired' );
					return $this->get_url( 'lost_pw', $url, 'expired' );
					break;

				case "?action=lostpassword&error=invalidkey":
					if ( $this->get_step() === 'setpw' ) return $this->get_url( 'activate', $url, 'invalid' );
					return $this->get_url( 'lost_pw', $url, 'invalid' );
					break;

				case "?action=rp":
					if ( $this->get_step() === 'activate' ) return $this->get_url( 'activate', $url, 'password' );
					return $this->get_url( 'lost_pw', $url, 'rp' );
					break;

				case "?action=activation&step=pending":
					return $this->get_url( 'activate', $url, 'pending' );
					break;

				case "?action=rp&step=setpw":
					return $this->get_url( 'activate', $url, 'password' );
					break;

				case "":
					return $this->get_url( 'login', $url );
					break;

				default:
					return $url;
			}

			return $url;

		}

		/**
		 * Handle activation redirect to set password
		 *
		 * wp-login.php by default sets a cookie, removes the query args, and then redirects
		 * to wp-login.php?action=rp.  We have to check if cookie is set, and if the request_uri
		 * matches that, and then we can set the same cookie under our new path, and redirect to
		 * our activate password URL.
		 *
		 *
		 * @since @@version
		 *
		 */
		function check_activation_redirect() {

			// First check if this is actuall wp-login.php
			if ( esc_url( $_SERVER[ 'SCRIPT_NAME' ] ) !== '/wp-login.php' ) return;

			// Check for activation/lost password cookie and non-permalink URL
			if ( $this->get_step() === 'activate' ) {

				$redirect = $this->get_url( 'activate', site_url( 'wp-login.php?action=rp' ), 'password' );

				//$cookie_has_colon = 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ? TRUE : FALSE;

				//$value = sprintf( '%s:%s', wp_unslash( $_GET[ 'login' ] ), wp_unslash( $_GET[ 'key' ] ) );

				$rp_cookie        = 'wp-resetpass-' . COOKIEHASH;
				$value            = wp_unslash( $_COOKIE[ $rp_cookie ] );

				// Set new cookie under our new path, this is required
				setcookie( $rp_cookie, $value, 0, '/', COOKIE_DOMAIN, is_ssl(), TRUE );

				return $redirect;
			}

		}

		function set_activate_cookie() {

			$rp_path          = 'wp-login.php?action=rp';
			$rp_cookie        = 'wp-resetpass-' . COOKIEHASH;
			$cookie_has_colon = 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ? TRUE : FALSE;
			$value            = wp_unslash( $_COOKIE[ $rp_cookie ] );

			// Set new cookie under our new path, this is required
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), TRUE );

		}

		/**
		 * Get URL
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param      $name
		 * @param null $original_url
		 * @param null $extra_rewrite
		 *
		 * @return bool|null|string
		 */
		function get_url( $name, $original_url = NULL, $extra_rewrite = NULL ) {

			$enabled = get_option( "wplf_rewrite_{$name}" );
			$slug    = get_option( "wplf_rewrite_{$name}_slug" );

			if ( ! $original_url ) {
				$url_response = FALSE;
			} else {
				$url_response = $original_url;
			};
			// If rewrite not enabled or no slug value return original URL or FALSE
			if ( ! $enabled || ! $slug ) return $url_response;

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
			if ( $login ) {
				add_rewrite_rule( $login . '/?', 'wp-login.php', 'top' );
			}

			/** @var self $lost_pw */
			if ( $lost_pw ) {
				add_rewrite_rule( $lost_pw . '/rp/?', 'wp-login.php?action=rp', 'top' );
				add_rewrite_rule( $lost_pw . '/resetpass/?', 'wp-login.php?action=resetpass', 'top' );
				add_rewrite_rule( $lost_pw . '/confirm/?', 'wp-login.php?checkemail=confirm', 'top' );
				add_rewrite_rule( $lost_pw . '/expired/?', 'wp-login.php?action=lostpassword&error=expiredkey', 'top' );
				add_rewrite_rule( $lost_pw . '/invalid/?', 'wp-login.php?action=lostpassword&error=invalidkey', 'top' );
				add_rewrite_rule( $lost_pw . '/?', 'wp-login.php?action=lostpassword', 'top' );
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
			if ( $loggedout ) {

				add_rewrite_rule( $loggedout . '/?', 'wp-login.php?loggedout=true', 'top' );

			}
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
		 *
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
			if ( ! $this->get_url( 'login' ) ) return $url . $redirect;

			return $this->get_url( 'login' ) . $redirect;
		}

		/**
		 * Lost Password URL
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
			if ( ! $this->get_url( 'lost_pw' ) ) return $url . $redirect;

			return $this->get_url( 'lost_pw' ) . $redirect;
		}

		/**
		 * Register URL
		 *
		 *
		 * @since 1.0.0
		 *
		 * @param $url
		 *
		 * @return bool|null|string
		 */
		function register_url( $url ) {

			if ( ! $this->get_url( 'register' ) ) return $url;

			return $this->get_url( 'register' );
		}

		function get_step() {

			if ( empty( $this->step ) ) $this->step = filter_input( INPUT_GET, 'step', FILTER_SANITIZE_URL );

			return $this->step;

		}

		function get_action() {

			if ( empty( $this->action ) ) $this->action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_URL );

			return $this->action;

		}

	}