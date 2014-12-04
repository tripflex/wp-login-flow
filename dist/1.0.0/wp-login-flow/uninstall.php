<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if( get_option('wplf_uninstall_remove_options') ){

	$settings = new WP_Login_Flow_Settings_Handlers();
	$settings->remove_all();

}