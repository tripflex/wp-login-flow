<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Template extends WP_Login_Flow {

	function __construct() {



	}

	function get_tags( $values ){

		$template_tags =  array(
			'wp_login_url' => wp_login_url(),
			'wp_site_url'  => get_site_url(),
			'wp_lost_pw_url' => wp_lostpassword_url()
		);

		$template_tags = array_merge( $template_tags, $values );
		return apply_filters( 'wp_login_flow_template_tags', $template_tags );

	}

	function replace_tags( $content, $values = array() ){

		foreach ( $this->get_tags( $values ) as $tag => $value ){

			$content = str_replace( "%$tag%", $value, $content );
		}

		return $content;
	}

}