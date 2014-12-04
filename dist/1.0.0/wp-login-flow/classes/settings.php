<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( WP_LOGIN_FLOW_PLUGIN_DIR . '/classes/settings/fields.php' );
require_once( WP_LOGIN_FLOW_PLUGIN_DIR . '/classes/settings/handlers.php' );

class WP_Login_Flow_Settings extends WP_Login_Flow_Settings_Handlers {

	protected static $settings;
	protected $settings_group;
	protected $process_count;
	protected $field_data;

	function __construct() {

		$this->settings_group = 'wp_login_flow';
		$this->process_count  = 0;

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'submenu' ) );
		add_action( 'wp_ajax_wp_login_flow_dl_backup', array( $this, 'download_backup' ) );

	}

	function submenu(){

		add_submenu_page(
			'users.php',
			__( 'Login Flow', 'wp-login-flow' ),
			__( 'Login Flow', 'wp-login-flow' ),
			'manage_options',
			'wp-login-flow',
			array( $this, 'output' )
		);

	}

	function output() {

		self::init_settings();
		settings_errors();
		?>
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h1><?php _e( 'WP Login Flow', 'wp-login-flow' ); ?></h1>
			<h2></h2>

			<form method="post" action="options.php">

				<?php settings_fields( $this->settings_group ); ?>

				<h2 id="wplf-nav-tabs" class="nav-tab-wrapper">
		<?php
					foreach ( self::$settings as $key => $tab ) {
						$title = $tab["title"];
						echo "<a href=\"#settings-{$key}\" class=\"nav-tab\">{$title}</a>";
					}
		?>
				</h2>
				<div id="wplf-all-settings">
		<?php
						foreach ( self::$settings as $key => $tab ):
		?>
						<div id="settings-<?php echo $key ?>" class="settings_panel">
							<div id="wplf-settings-inside">
		<?php
							foreach( $tab['sections'] as $skey => $section ) {
								echo "<h2 class=\"wp-ui-primary\">{$section['title']}</h2>";
								if( $skey === 'enable_rewrites' && parent::permalinks_disabled() ){
									echo "<h3 class=\"permalink-error\">" . sprintf( __( 'You <strong>must</strong> enable <a href="%1$s">permalinks</a> to use custom rewrites!', 'wp-login-flow' ), admin_url('options-permalink.php') ). "</h3>";
								}
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

	public static function init_settings() {

		self::$settings = apply_filters(
			'wp_login_flow_settings',
			array(
				'rewrites' => array(
					'title'  => __( 'Rewrites', 'wp-login-flow' ),
					'sections' => array(
						'require_activation' => array(
							'title'  => __( 'Email Activation', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'       => 'wplf_require_activation',
									'std'        => '0',
									'label'      => __( 'Require Activation', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'This will require new accounts to be verified by email before they are able to login.', 'wp-login-flow' ),
								)
							)
						),
						'enable_rewrites' => array(
							'title'  => __( 'Enable Rewrites', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'       => 'wplf_rewrite_login',
									'std'        => '0',
									'label'      => __( 'Login', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Default', 'wp-login-flow' ) . ': <code>' . home_url() . '/wp-login.php</code>',
									'disabled' => parent::permalinks_disabled(),
									'fields'     => array(
									    array(
											'name'       => 'wplf_rewrite_login_slug',
											'std'        => 'login',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
									        'disabled'   => parent::permalinks_disabled()
									    )
								    )
								),
								array(
									'name'       => 'wplf_rewrite_lost_pw',
									'std'        => '0',
									'label'      => __( 'Lost Password', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Default', 'wp-login-flow' ) . ': <code>' . home_url() . '/wp-login.php?action=lostpassword</code>',
									'disabled' => parent::permalinks_disabled(),
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_lost_pw_slug',
											'std'        => 'lost-password',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled' => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_register',
									'std'        => '0',
									'label'      => __( 'Register', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Default', 'wp-login-flow' ) . ': <code>' . home_url() . '/wp-login.php?action=register</code>',
									'disabled' => parent::permalinks_disabled(),
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_register_slug',
											'std'        => 'register',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled' => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_activate',
									'std'        => '0',
									'label'      => __( 'Activate', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Default', 'wp-login-flow' ) . ': <code>' . home_url() . '/wp-login.php?action=rp&key=SAMPLEACTIVATIONCODE&login=users@email.com</code>',
									'disabled' => parent::permalinks_disabled(),
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_activate_slug',
											'std'        => 'activate',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '<code>/users@email.com/SAMPLEACTIVATIONCODE</code>',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled' => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_loggedout',
									'std'        => '0',
									'label'      => __( 'Logged Out', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Default', 'wp-login-flow' ) . ': <code>' . home_url() . '/wp-login.php?loggedout=true</code>',
									'disabled'   => parent::permalinks_disabled(),
									'fields'     => array(
										array(
											'name'       => 'wplf_rewrite_loggedout_slug',
											'std'        => 'logout/complete',
											'pre'        => '<code>' . home_url() . '/</code>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled'   => parent::permalinks_disabled()
										)
									)
								),
							)
						)
					)
				),
				'custom_page' => array(
					'title'  => __( 'Customize Page', 'wp-login-flow' ),
					'sections' => array(
						'page' => array(
							'title' => __( 'Page Customizations', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'  => 'wplf_bg_color',
									'label' => __( 'Background Color', 'wp-login-flow' ),
									'desc'  => __( 'Use a custom background for the default wp-login.php page.', 'wp-login-flow' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'  => 'wplf_custom_css',
									'label' => __( 'Custom CSS', 'wp-login-flow' ),
									'desc'  => __( 'Add any custom CSS you want added to login page here.', 'wp-login-flow' ),
									'type'  => 'textarea'
								),
							)
						),
						'login_styles' => array(
							'title' => __( 'Logo Customizations', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'        => 'wplf_logo_url_title',
									'label'       => __( 'Logo URL Title', 'wp-login-flow' ),
									'placeholder' => __( 'My Website', 'wp-login-flow' ),
									'desc'        => __( 'Title attribute for the logo url link', 'wp-login-flow' ),
									'type'        => 'textbox'
								),
								array(
									'name'  => 'wplf_logo_url',
									'label' => __( 'Logo URL', 'wp-login-flow' ),
									'placeholder' => 'http://mydomain.com',
									'desc'  => __( 'Custom URL to use for the logo.', 'wp-login-flow' ),
									'type'  => 'textbox'
								),
								array(
									'name'    => 'wplf_logo',
									'label'   => __( 'Custom Logo', 'wp-login-flow' ),
									'modal_title'   => __( 'Custom Logo', 'wp-login-flow' ),
									'modal_btn'   => __( 'Set Custom Logo', 'wp-login-flow' ),
									'desc'    => __( 'Use a custom logo on the default wp-login.php page.', 'wp-login-flow' ),
									'type'    => 'upload'
								)
							)
						),
						'login_box' => array(
							'title' => __( 'Login Box', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'        => 'wplf_login_box_responsive',
									'label'       => __( 'Responsive Width', 'wp-login-flow' ),
									'cb_label' => __( 'Enable', 'wp-login-flow' ),
									'desc'        => __( 'Screen sizes above 1200px use default 50%, smaller screens use 90% width.', 'wp-login-flow' ),
									'type'        => 'checkbox'
								),
								array(
									'name'  => 'wplf_login_box_color',
									'label' => __( 'Font Color', 'wp-login-flow' ),
									'desc'  => __( 'Custom font color for Login Box', 'wp-login-flow' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'  => 'wplf_login_box_bg_color',
									'label' => __( 'Background Color', 'wp-login-flow' ),
									'desc'  => __( 'Custom background color for Login Box', 'wp-login-flow' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'       => 'wplf_login_box_border_radius_enable',
									'std'        => '0',
									'label'      => __( 'Border Radius', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => __( 'Set a custom border radius on the login box, will only work with modern browsers that support CSS3.', 'wp-login-flow' ),
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
					'title'  => __( 'Email', 'wp-login-flow' ),
					'sections' => array(
						'email_from' => array(
							'title' => __( 'Customize Email Options', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'       => 'wplf_from_name_enable',
									'std'        => '0',
									'label'      => __( 'From Name', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'desc'       => __( 'Use a custom name on emails from WordPress.', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '',
									'fields'     => array(
										array(
											'name'       => 'wplf_from_name',
											'std'        => '',
											'placeholder' => __( 'My Website', 'wp-login-flow' ),
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array()
										)
									),
								),
								array(
									'name'       => 'wplf_from_email_enable',
									'std'        => '0',
									'label'      => __( 'From E-Mail', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'desc'       => __( 'Use a custom e-mail on emails from WordPress.', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '',
									'fields'     => array(
										array(
											'name'       => 'wplf_from_email',
											'std'        => '',
											'placeholder' => __( 'support@mydomain.com', 'wp-login-flow' ),
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
					'title'  => __( 'Templates', 'wp-login-flow' ),
					'sections' => array(
						'activation' => array(
							'title' => __( 'User Activation Email Template', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'       => 'wplf_activation_subject',
									'label'      => __( 'Email Subject', 'wp-login-flow' ),
									'desc'       => __( 'This will be used as the subject for the Activation email.  You can use any template tags available in message below.', 'wp-login-flow' ),
									'std'        => 'Account Activation Required',
									'type'       => 'textbox',
									'class'      => 'widefat',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_activation_message',
									'label'      => __( 'Email Message', 'wp-login-flow' ),
									'desc'       => __( 'This template will be used as the first email sent to the user to activate their account.<br /><strong>Template Tags:</strong> <code>%wp_activate_url%</code> - Activation URL, <code>%wp_activation_key%</code> - Activation Key, <code>%wp_user_name%</code> - Username, <code>%wp_user_email%</code> - User Email, <code>%wp_site_url%</code> - Site URL', 'wp-login-flow' ),
									'std'        => 'Thank you for registering your account:<br />' . network_home_url( '/' ) . '<br />Username: %wp_user_name%<br /><br />In order to activate your account and set your password, please visit the following address:<br /><a href="%wp_activate_url%">%wp_activate_url%</a>',
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						)
					)
				),
				'notices' => array(
					'title'  => __( 'Notices', 'wp-login-flow' ),
					'sections' => array(
						'activation' => array(
							'title' => __( 'Activation Notices', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'       => 'wplf_notice_activation_required',
									'label'      => __( 'Account Requires Activation Notice', 'wp-login-flow' ),
									'std'        => 'Thank you for registering.  Please check your email for your activation link.<br><br>If you do not receive the email please request a <a href="%wp_lost_pw_url%">password reset</a> to have the email sent again.',
									'desc'       => __( 'This notice will be shown to the user when they attempt to login but have not activated their account.  Use <code>%wp_lost_pw_url%</code> for the lost password URL.', 'wp-login-flow' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_notice_activation_pending',
									'label'      => __( 'Pending Activation Notice', 'wp-login-flow' ),
									'std'        => '<strong>ERROR</strong>: Your account is still pending activation, please check your email, or you can request a <a href="%wp_lost_pw_url%">password reset</a> for a new activation code.',
									'desc'       => __( 'This notice will be shown to the user when they attempt to login but have not activated their account.  Use <code>%wp_lost_pw_url%</code> for the lost password URL.', 'wp-login-flow' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_notice_activation_thankyou',
									'label'      => __( 'Successful Activation Notice', 'wp-login-flow' ),
									'std'        => 'Your account has been successfully activated!',
									'desc'       => __( 'This notice will be shown to the user once they activate and set the password for their account.', 'wp-login-flow' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						),
					)
				),
			    'integrations' => array(
					'title' => __( 'Integrations', 'wp-login-flow' ),
					'sections' => array(
					    'jobify' => array(
						    'title' => __( 'Jobify', 'wp-login-flow' ),
							'fields' => array(
							    array(
								    'name'       => 'wplf_jobify_pw',
								    'std'        => '1',
								    'label'      => __( 'Jobify Password Field', 'wp-login-flow' ),
								    'cb_label'   => __( 'Remove', 'wp-login-flow' ),
								    'desc'       => __( 'Remove the password box from Jobify registration form.', 'wp-login-flow' ),
								    'type'       => 'checkbox',
								    'attributes' => array()
							    ),
					        )
					    )
				    )
				),
				'settings' => array(
					'title'    => __( 'Settings', 'wp-login-flow' ),
					'sections' => array(
						'config' => array(
							'title'  => __( 'Configuration', 'wp-login-flow' ),
							'fields' => array(
								array(
									'name'       => 'wplf_uninstall_remove_options',
									'std'        => '0',
									'label'      => __( 'Remove on Uninstall', 'wp-login-flow' ),
									'cb_label'   => __( 'Enable', 'wp-login-flow' ),
									'desc'       => __( 'This will remove all configuration and options when you uninstall the plugin (disabled by default)', 'wp-login-flow' ),
									'type'       => 'checkbox',
									'attributes' => array()
								),
								array(
									'name'       => 'wplf_reset_default',
									'class'  => 'button-primary',
									'action' => 'reset_default',
									'label'      => __( 'Reset to Defaults', 'wp-login-flow' ),
									'caption'   => __( 'Reset to Defaults', 'wp-login-flow' ),
									'desc'       => __( '<strong>CAUTION!</strong> This will remove ALL configuration values, and reset everything to default!', 'wp-login-flow' ),
									'type'       => 'button',
									'attributes' => array()
								),

							)
						)
					)
				)
			)
		);

	}

	public static function get_settings(){

		if( ! self::$settings ) self::init_settings();

		return self::$settings;

	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {

		self::init_settings();

		foreach ( self::$settings as $key => $tab ) {

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