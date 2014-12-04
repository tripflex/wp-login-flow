<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Core {

	function __construct() {

		new WP_Login_Flow_Assets();
		new WP_Login_Flow_Login_Styles();
		new WP_Login_Flow_Mail();
		new WP_Login_Flow_Rewrite();
	}

}