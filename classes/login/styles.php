<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Login_Styles extends WP_Login_Flow_Login {

	function __construct() {

		add_action( 'login_enqueue_scripts', array( $this, 'login_css' ) );
		add_action( 'login_headerurl', array( $this, 'logo_url' ) );
		add_action( 'login_headertitle', array( $this, 'logo_title' ) );

	}

	function logo_url( $url ){

		$custom_url = get_option( 'wplf_logo_url' );
		if ( $custom_url ) return $custom_url;

		return $url;

	}

	function logo_title( $title ){

		$custom_title = get_option( 'wplf_logo_url_title' );
		if( $custom_title ) return $custom_title;

		return $title;

	}

	public function login_css() {

		$login_bg_color = get_option( 'wplf_bg_color' );
		$custom_css = get_option( 'wplf_custom_css' );

		$login_box_responsive = get_option( 'wplf_login_box_responsive' );
		$login_box_bg_color = get_option( 'wplf_login_box_bg_color' );
		$wplf_login_box_color = get_option( 'wplf_login_box_color' );
		$login_box_border_radius_enable = get_option( 'wplf_login_box_border_radius_enable' );
		$wplf_login_box_border_radius = get_option( 'wplf_login_box_border_radius' );

		$logo = get_option( 'wplf_logo' );

		?>

		<style type="text/css">
			<?php if ( ! empty( $login_box_responsive ) ): ?>
			@media (max-width: 1200px) {#login {width: 90% !important;}}
			@media (min-width: 1200px) {#login {width: 50% !important;}}
			<?php endif; ?>

			<?php if ( ! empty( $login_bg_color ) ): ?>
			body {background-color: <?php echo $login_bg_color; ?> !important;}
			<?php endif; ?>

			<?php
				$login_form_css = '.login #login form, .login #login form label {';

				// Login Box Background Color
				if ( ! empty( $login_box_bg_color ) ){
					$login_form_css .= 'background-color: ' . $login_box_bg_color . ' !important;';
				}

				// Login Box Border Radius
				if ( ! empty( $login_box_border_radius_enable ) && ! empty( $wplf_login_box_border_radius ) ){
					$login_form_css .= 'border-radius: ' . $wplf_login_box_border_radius . 'px !important;';
				}

				// Login Box Font Color
				if ( ! empty( $wplf_login_box_color ) ){
					$login_form_css .= 'color: ' . $wplf_login_box_color . ' !important;';
				}

				$login_form_css .= '}';

				echo sanitize_text_field( $login_form_css );
			?>

			<?php if($custom_css) echo sanitize_text_field( $custom_css );

			?>
			<?php
				if ( $logo ):
			?>
			body.login div#login h1 a {
				background-image: url('<?php echo $logo; ?>');
				background-size: contain;
			}

			<?php endif; ?>
		</style>
	<?php

	} // End login_css

}