<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( WP_LOGIN_FLOW_PLUGIN_DIR . '/classes/settings/fields.php' );
require_once( WP_LOGIN_FLOW_PLUGIN_DIR . '/classes/settings/handlers.php' );

class WP_Login_Flow_Settings extends WP_Login_Flow_Settings_Handlers {

	protected $settings;
	protected $settings_group;
	protected $process_count;
	protected $field_data;

	function __construct() {

		$this->settings_group = 'wp_login_flow';
		$this->process_count  = 0;

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'submenu' ) );

	}

	function submenu(){

		add_submenu_page(
			'users.php',
			__( 'WP Login Flow' ),
			__( 'WP Login Flow' ),
			'manage_options',
			'wp-login-flow',
			array( $this, 'output' )
		);

	}

	function output() {

		$this->init_settings();
		settings_errors();
		?>
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h1><?php _e( 'WP Login Flow' ); ?></h1>
			<h2></h2>

			<form method="post" action="options.php">

				<?php settings_fields( $this->settings_group ); ?>

				<h2 id="wplf-nav-tabs" class="nav-tab-wrapper">
		<?php
					foreach ( $this->settings as $key => $tab ) {
						$title = $tab["title"];
						echo "<a href=\"#settings-{$key}\" class=\"nav-tab\">{$title}</a>";
					}
		?>
				</h2>
				<div id="wplf-all-settings">
		<?php
						foreach ( $this->settings as $key => $tab ):
		?>
						<div id="settings-<?php echo $key ?>" class="settings_panel">
							<div id="wplf-settings-inside">
		<?php
							foreach( $tab['sections'] as $skey => $section ) {
								echo "<h2 class=\"wp-ui-primary\">{$section['title']}</h2>";
								do_settings_sections( "wplf_{$key}_{$skey}_section" );
							}
		?>
							</div>
						</div>
		<?php
						endforeach;
						submit_button();
		?>
				</div>
			</form>
		</div>

	<?php

	}

	function init_settings() {

		$this->settings = apply_filters(
			'wp_login_flow_settings',
			array(
				'rewrites' => array(
					'title'  => __( 'Rewrites' ),
					'sections' => array(
						'enable_rewrites' => array(
							'title'  => __( 'Enable Rewrites' ),
							'fields' => array(
								array(
									'name'       => 'wplf_rewrite_login',
									'std'        => '1',
									'label'      => __( 'Login' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Default' ) . ': <code>' . home_url() . '/wp-login.php</code>',
									'fields'     => array(
									    array(
											'name'       => 'wplf_rewrite_login_slug',
											'std'        => 'login',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array()
									    )
								    )
								),
								array(
									'name'       => 'wplf_rewrite_lost_pw',
									'std'        => '1',
									'label'      => __( 'Lost Password' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Default' ) . ': <code>' . home_url() . '/wp-login.php?action=lostpassword</code>',
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_lost_pw_slug',
											'std'        => 'lost-password',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_register',
									'std'        => '1',
									'label'      => __( 'Register' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Default' ) . ': <code>' . home_url() . '/wp-login.php</code>',
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_register_slug',
											'std'        => 'register',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_activate',
									'std'        => '1',
									'label'      => __( 'Activate' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Default' ) . ': <code>' . home_url() . '/wp-login.php?action=rp&key=SAMPLEACTIVATIONCODE&login=users@email.com</code>',
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_activate_slug',
											'std'        => 'activate',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '<code>/users@email.com/SAMPLEACTIVATIONCODE</code>',
											'type'       => 'textbox',
											'attributes' => array()
										)
									)
								)
							)
						)
					)
				),
				'custom_page' => array(
					'title'  => __( 'Customize Page' ),
					'sections' => array(
						'page' => array(
							'title' => __( 'Page Customizations' ),
							'fields' => array(
								array(
									'name'  => 'wplf_bg_color',
									'label' => __( 'Background Color' ),
									'desc'  => __( 'Use a custom background for the default wp-login.php page.' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'  => 'wplf_custom_css',
									'label' => __( 'Custom CSS' ),
									'desc'  => __( 'Add any custom CSS you want added to login page here.' ),
									'type'  => 'textarea'
								),
							)
						),
						'login_styles' => array(
							'title' => __( 'Logo Customizations' ),
							'fields' => array(
								array(
									'name'        => 'wplf_logo_url_title',
									'label'       => __( 'Logo URL Title' ),
									'placeholder' => __( 'My Website' ),
									'desc'        => __( 'Title attribute for the logo url link' ),
									'type'        => 'textbox'
								),
								array(
									'name'  => 'wplf_logo_url',
									'label' => __( 'Logo URL' ),
									'placeholder' => 'http://mydomain.com',
									'desc'  => __( 'Custom URL to use for the logo.' ),
									'type'  => 'textbox'
								),
								array(
									'name'    => 'wplf_logo',
									'label'   => __( 'Custom Logo' ),
									'modal_title'   => __( 'Custom Logo' ),
									'modal_btn'   => __( 'Set Custom Logo' ),
									'desc'    => __( 'Use a custom logo on the default wp-login.php page.' ),
									'type'    => 'upload'
								)
							)
						),
						'login_box' => array(
							'title' => __( 'Login Box' ),
							'fields' => array(
								array(
									'name'        => 'wplf_login_box_responsive',
									'label'       => __( 'Responsive Width' ),
									'cb_label' => __( 'Enable' ),
									'desc'        => __( 'Screen sizes above 1200px use default 50%, smaller screens use 90% width.' ),
									'type'        => 'checkbox'
								),
								array(
									'name'  => 'wplf_login_box_color',
									'label' => __( 'Font Color' ),
									'desc'  => __( 'Custom font color for Login Box' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'  => 'wplf_login_box_bg_color',
									'label' => __( 'Background Color' ),
									'desc'  => __( 'Custom background color for Login Box' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'       => 'wplf_login_box_border_radius_enable',
									'std'        => '0',
									'label'      => __( 'Border Radius' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Set a custom border radius on the login box, will only work with modern browsers that support CSS3.' ),
									'fields'     => array(
										array(
											'name'        => 'wplf_login_box_border_radius',
											'type'  => 'spinner'
										)
									),
								)
							)
						)
					)

				),
				'email' => array(
					'title'  => __( 'Email' ),
					'sections' => array(
						'email_from' => array(
							'title' => __( 'Customize Email Options' ),
							'fields' => array(
								array(
									'name'       => 'wplf_from_name_enable',
									'std'        => '0',
									'label'      => __( 'From Name' ),
									'cb_label'   => __( 'Enable' ),
									'desc'       => __( 'Use a custom name on emails from WordPress.' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '',
									'fields'     => array(
										array(
											'name'       => 'wplf_from_name',
											'std'        => '',
											'placeholder' => __( 'My Website' ),
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array()
										)
									),
								),
								array(
									'name'       => 'wplf_from_email_enable',
									'std'        => '0',
									'label'      => __( 'From E-Mail' ),
									'cb_label'   => __( 'Enable' ),
									'desc'       => __( 'Use a custom e-mail on emails from WordPress.' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '',
									'fields'     => array(
										array(
											'name'       => 'wplf_from_email',
											'std'        => '',
											'placeholder' => __( 'support@mydomain.com' ),
											'pre'        => '',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array()
										)
									),
								),
							)
						)
					)
				),
				'templates' => array(
					'title'  => __( 'Templates' ),
					'sections' => array(
						'activation' => array(
							'title' => __( 'Activation Email Template' ),
							'fields' => array(
								array(
									'name'       => 'wplf_activation_email',
									'label'      => __( 'Activation Email' ),
									'desc'       => __( 'Use a custom name on emails from WordPress.' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						),
						'lost_pw' => array(
							'title' => __( 'Lost Password Email Template' ),
							'fields' => array(
								array(
									'name'       => 'wplf_lost_pw_email',
									'label'      => __( 'Lost Password' ),
									'desc'       => __( 'Use a custom name on emails from WordPress.' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						),
					)
				),
				'notices' => array(
					'title'  => __( 'Notices' ),
					'sections' => array(
						'activation' => array(
							'title' => __( 'Activation Notices' ),
							'fields' => array(
								array(
									'name'       => 'wplf_notice_activation_required',
									'label'      => __( 'Account Requires Activation Notice' ),
									'std'        => 'Thank you for registering.  Please check your email for your activation link.<br><br>If you do not receive the email please request a <a href="{{wp_lost_pw_url}}">password reset</a> to have the email sent again.',
									'desc'       => __( 'This notice will be shown to the user when they attempt to login but have not activated their account.  Use <code>{{wp_lost_pw_url}}</code> for the lost password URL.' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_notice_activation_pending',
									'label'      => __( 'Pending Activation Notice' ),
									'std'        => '<strong>ERROR</strong>: Your account is still pending activation, please check your email, or you can request a <a href="{{wp_lost_pw_url}}">password reset</a> for a new activation code.',
									'desc'       => __( 'This notice will be shown to the user when they attempt to login but have not activated their account.  Use <code>{{wp_lost_pw_url}}</code> for the lost password URL.' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_notice_activation_thankyou',
									'label'      => __( 'Successful Activation Notice' ),
									'std'        => 'Your account has been successfully activated!',
									'desc'       => __( 'This notice will be shown to the user once they activate and set the password for their account.' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						),
					)
				),
			    'integrations' => array(
					'title' => __( 'Integrations' ),
					'sections' => array(
					    'jobify' => array(
						    'title' => __( 'Jobify' ),
							'fields' => array(
							    array(
								    'name'       => 'wplf_jobify_pw',
								    'std'        => '1',
								    'label'      => __( 'Jobify Password Field' ),
								    'cb_label'   => __( 'Remove' ),
								    'desc'       => __( 'Remove the password box from Jobify registration form.' ),
								    'type'       => 'checkbox',
								    'attributes' => array()
							    ),
					        )
					    )
				    )
				)
			)
		);
	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {

		$this->init_settings();

		foreach ( $this->settings as $key => $tab ) {

			foreach( $tab['sections'] as $skey => $section ) {

				$section_header = "default_header";
				if ( method_exists( $this, "{$key}_{$skey}_header" ) ) $section_header = "{$key}_{$skey}_header";

				add_settings_section( "wplf_{$key}_{$skey}_section", '', array( $this, $section_header ), "wplf_{$key}_{$skey}_section" );

				foreach ( $section[ 'fields' ] as $option ) {

					$field_args = $this->build_args( $option );

					if( isset( $option[ 'fields' ] ) && ! empty( $option[ 'fields' ] ) ){

						foreach( $option[ 'fields' ] as $sf ) $this->build_args( $sf );

					}

					add_settings_field(
						$option[ 'name' ],
						$option[ 'label' ],
						array( $this, "{$option['type']}_field" ),
						"wplf_{$key}_{$skey}_section",
						"wplf_{$key}_{$skey}_section",
						$field_args
					);

				}

			}
		}
	}

	function build_args( $option, $register = true ){

		$submit_handler = 'submit_handler';

		if ( method_exists( $this, "{$option['type']}_handler" ) ) $submit_handler = "{$option['type']}_handler";

		if ( isset( $option[ 'std' ] ) ) add_option( $option[ 'name' ], $option[ 'std' ] );

		if( $register ) register_setting( $this->settings_group, $option[ 'name' ], array( $this, $submit_handler ) );

		$placeholder = ( ! empty( $option[ 'placeholder' ] ) ) ? 'placeholder="' . $option[ 'placeholder' ] . '"' : '';
		$class       = ! empty( $option[ 'class' ] ) ? $option[ 'class' ] : '';

		$value       = esc_attr( get_option( $option[ 'name' ] ) );
		$non_escape_fields = array( 'wpeditor' );
		if( in_array( $option['type'], $non_escape_fields ) ) $value = get_option( $option['name'] );

		$attributes  = "";

		if ( ! empty( $option[ 'attributes' ] ) && is_array( $option[ 'attributes' ] ) ) {

			foreach ( $option[ 'attributes' ] as $attribute_name => $attribute_value ) {
				$attribute_name  = esc_attr( $attribute_name );
				$attribute_value = esc_attr( $attribute_value );
				$attributes .= "{$attribute_name}=\"{$attribute_value}\" ";
			}

		}

		$field_args = array(
			'option'      => $option,
			'placeholder' => $placeholder,
			'value'       => $value,
			'attributes'  => $attributes,
			'class'       => $class
		);

		return $field_args;

	}

}