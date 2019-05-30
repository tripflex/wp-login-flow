<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_Template
 *
 * @since 3.0.0
 *
 */
class WP_Login_Flow_Template extends WP_Login_Flow {

	/**
	 * WP_Login_Flow_Template constructor.
	 */
	function __construct() {

	}

	/**
	 * Generate template data
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param null  $option
	 * @param null  $fallback
	 * @param array $values
	 *
	 * @return bool|mixed
	 */
	function generate( $option = NULL, $fallback = NULL, $values = array() ){

		if ( ! $option || ! $fallback ) return false;

		$option_value = get_option( $option );
		if ( ! $option_value ) $option_value = $fallback;
		$template = $this->replace_tags( $option_value, $values );

		return $template;

	}

	/**
	 * Set and filter available template tags
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $values
	 *
	 * @return mixed|void
	 */
	function get_tags( $values ){

		$template_tags =  array(
			'wp_login_url' => wp_login_url(),
			'wp_site_url'  => get_site_url(),
			'wp_lost_pw_url' => wp_lostpassword_url()
		);

		$template_tags = array_merge( $template_tags, $values );
		return apply_filters( 'wp_login_flow_template_tags', $template_tags );

	}

	/**
	 * Replace template tags with actual values
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param       $content
	 * @param array $values
	 *
	 * @return mixed
	 */
	function replace_tags( $content, $values = array() ){

		foreach ( $this->get_tags( $values ) as $tag => $value ){
			// Hack to prevent URL issues if wp editor prepends http:// in front of template tag for URL
			if( filter_var( $value, FILTER_VALIDATE_URL ) ) $content = str_replace( "http://%$tag%", $value, $content );
			$content = str_replace( "%$tag%", $value, $content );
		}

		return $content;
	}

}