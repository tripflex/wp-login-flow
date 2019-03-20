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

	/**
	 * Add Login Flow to user submenu
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function submenu(){

		add_submenu_page(
			'users.php',
			__( 'Login Flow' ),
			__( 'Login Flow' ),
			'manage_options',
			'wp-login-flow',
			array( $this, 'output' )
		);

	}

	/**
	 * Output WP Login Flow Settings Page
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function output() {

		self::init_settings();
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
					foreach ( self::$settings as $key => $tab ) {
						$title = $tab["title"];
						echo "<a href=\"#settings-{$key}\" class=\"nav-tab\" data-tab=\"{$key}\">{$title}</a>";
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

								if ( array_key_exists( 'hide_if', $section ) && ! empty( $section['hide_if'] ) ) {
									continue;
								}

								echo "<h2 class=\"wp-ui-primary\">{$section['title']}</h2>";
								if( $skey === 'enable_rewrites' && parent::permalinks_disabled() ){
									echo "<h3 class=\"permalink-error\">" . sprintf( __( 'You <strong>must</strong> enable <a href="%1$s">permalinks</a> to use custom rewrites!' ), admin_url('options-permalink.php') ). "</h3>";
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

	/**
	 * Initialize Settings Fields
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	public static function init_settings() {

		self::$settings = apply_filters(
			'wp_login_flow_settings',
			array(
				'rewrites' => array(
					'title'  => __( 'Permalinks' ),
					'sections' => array(
						'enable_rewrites' => array(
							'title'  => __( 'Permalinks/Rewrites' ),
							'fields' => array(
								array(
									'name'       => 'wplf_rewrite_login',
									'std'        => '0',
									'label'      => __( 'Login' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '<strong>' . __( 'Default' ) . ':</strong> <pre>' . home_url() . '/wp-login.php</pre>',
									'disabled' => parent::permalinks_disabled(),
									'fields'     => array(
									    array(
											'name'       => 'wplf_rewrite_login_slug',
											'std'        => 'login',
											'pre'        => '<pre>' . home_url() . '/</pre>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
									        'disabled'   => parent::permalinks_disabled()
									    )
								    )
								),
								array(
									'name'       => 'wplf_rewrite_register',
									'std'        => '0',
									'label'      => __( 'Register' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '<strong>' . __( 'Default' ) . ':</strong> <pre>' . home_url() . '/wp-login.php?action=register</pre>',
									'disabled'   => parent::permalinks_disabled(),
									'endpoints' => array( 'disabled', 'checkemail' ),
									'fields'     => array(
										array(
											'name'       => 'wplf_rewrite_register_slug',
											'std'        => 'register',
											'pre'        => '<pre>' . home_url() . '/</pre>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled'   => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_activate',
									'std'        => '0',
									'label'      => __( 'Activate' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '<strong>' . __( 'Default' ) . ':</strong> <pre>' . home_url() . '/wp-login.php?action=rp&key=ACTIVATIONCODE&login=USERNAME</pre>',
									'disabled'   => parent::permalinks_disabled(),
									'fields'     => array(
										array(
											'name'       => 'wplf_rewrite_activate_slug',
											'std'        => 'activate',
											'pre'        => '<pre>' . home_url() . '/</pre>',
											'post'       => '<pre>/USERNAME/ACTIVATIONCODE</pre>',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled'   => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_lost_pw',
									'std'        => '0',
									'label'      => __( 'Lost Password' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => '<strong>' . __( 'Default' ) . ':</strong> <pre>' . home_url() . '/wp-login.php?action=lostpassword</pre>',
									'endpoints' => array( 'rp', 'resetpass', 'confirm', 'expired', 'invalid' ),
									'disabled' => parent::permalinks_disabled(),
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_lost_pw_slug',
											'std'        => 'lost-password',
											'pre'        => '<pre>' . home_url() . '/</pre>',
											'post'       => '',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled' => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_reset_pw',
									'std'        => '0',
									'label'      => __( 'Reset Password' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc' => '<strong>' . __( 'Default' ) . ':</strong> <pre>' . home_url() . '/wp-login.php?action=rp&key=RESETKEY&login=USERNAME</pre>',
									'disabled' => parent::permalinks_disabled(),
									'fields' => array(
										array(
											'name'       => 'wplf_rewrite_reset_pw_slug',
											'std'        => 'reset-password',
											'pre'        => '<pre>' . home_url() . '/</pre>',
											'post'       => '<pre>/USERNAME/RESETKEY</pre>',
											'type'       => 'textbox',
											'attributes' => array(),
											'disabled' => parent::permalinks_disabled()
										)
									)
								),
								array(
									'name'       => 'wplf_rewrite_loggedout',
									'std'        => '0',
									'label'      => __( 'Logged Out' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => '<strong>' . __( 'Default' ) . ':</strong> <pre>' . home_url() . '/wp-login.php?loggedout=true</pre>',
									'disabled'   => parent::permalinks_disabled(),
									'fields'     => array(
										array(
											'name'       => 'wplf_rewrite_loggedout_slug',
											'std'        => 'logout/complete',
											'pre'        => '<pre>' . home_url() . '/</pre>',
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
				'registration' => array(
					'title'  => __( 'Registration' ),
					'sections' => array(
						'require_activation' => array(
							'title'  => __( 'Account Setup' ),
							'fields' => array(
								array(
									'name'       => 'wplf_require_activation',
									'std'        => '1',
									'label'      => __( 'Require Activation' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Email link to set password in email when new users register. This is default method of registration in WordPress.' ),
								),
								array(
									'name'       => 'wplf_register_set_pw',
									'std'        => '0',
									'label'      => __( 'Register with Password' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Show password input fields on registration form, and do not require account activation (disables require activation above)' ),
								)
							)
						),
						'registration' => array(
							'title'  => __( 'Registration' ),
							'fields' => array(
								array(
									'name'       => 'wplf_auto_login',
									'std'        => '0',
									'label'      => __( 'Auto Login' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Auto login users after completing registration (regardless of account activation status).  Users will still be required to activate account (if enabled) to login again.' ),
								),
								array(
									'name'       => 'wplf_register_loader',
									'std'        => '0',
									'label'      => __( 'Loader' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Add a spinning loader and disable the register button after being clicked (and form validated)' ),
								)
							)
						),
						'registration_fields' => array(
							'title'  => __( 'Registration Fields' ),
							'fields' => array(
								array(
									'name'       => 'wplf_registration_email_as_un',
									'std'        => '0',
									'label'      => __( 'Email as Username' ),
									'cb_label'   => __( 'Enable' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'Hide the Username field, and use Email as the username.' ),
								),
								array(
									'name'       => 'wplf_registration_custom_fields',
									'std'        => '0',
									'label'      => __( 'Custom Fields' ),
									'type'       => 'repeatable',
									'attributes' => array(),
									'desc'       => __( 'Add any additional custom user meta fields you want on the register form.' ),
									'rfields' => array(
										'label'    => array(
											'label'       => __( 'Field Label' ),
											'type'        => 'textbox',
											'help' => __( 'Enter the label to show above the input field' ),
											'default'     => '',
											'placeholder' => '',
											'multiple'    => true,
											'required' => true
										),
										'meta_key'    => array(
											'label'       => __( 'Meta Key' ),
											'type'        => 'textbox',
											'default'     => '',
											'help'		=> __( 'Enter the exact user meta key to save this value to.  Example would be first_name, last_name, etc.' ),
											'placeholder' => '',
											'multiple'    => true,
											'required'    => true
										),
										'required' => array(
											'cb_label'          => __( 'Required' ),
											'label'        => __( 'Required' ),
											'type'           => 'checkbox',
											'class'          => '',
											'default'            => '0',
											'multiple'       => true,
											'template_style' => true
										)
									)
								)
							)
						)
					)
				),
				'redirects' => array(
					'title'  => __( 'Redirects' ),
					'sections' => array(
						'login_redirects' => array(
							'title'  => __( 'Login Redirects' ),
							'fields' => array(
								array(
									'name'        => 'wplf_default_login_redirect',
									'label'       => __( 'Default Login Redirect' ),
									'desc'        => __( 'Enter the endpoint for default redirect after a user logs in (if they don\'t match any other rules below)' ),
									'placeholder'         => '/my-account',
									'type'        => 'textbox',
									'field_class' => '',
									'attributes'  => array(),
								),
								array(
									'name'       => 'wplf_role_login_redirects',
									'std'        => '0',
									'label'      => __( 'Role Login Redirects' ),
									'type'       => 'repeatable',
									'attributes' => array(),
									'single_val' => 'role', // Signify this group has single val fields to check on page init
									'desc'       => __( 'Select any custom redirects to use for specific user roles' ),
									'rfields'    => array(
										'role'    => array(
											'label'       => __( 'Role' ),
											'type'        => 'userroles',
											'help'        => __( 'Enter the label to show above the input field' ),
											'default'     => '',
											'placeholder' => '',
											'multiple'    => true,
											'single_val'  => true, // Means values can't be selected more than once in repeatable
											'required'    => true
										),
										'redirect' => array(
											'label'       => __( 'Redirect' ),
											'type'        => 'textbox',
											'default'     => '',
											'placeholder' => '/some-endpoint',
											'help'        => __( 'Endpoint (on this site) to redirect to (do NOT include website URL)' ),
											'placeholder' => '',
											'multiple'    => true,
											'required'    => true
										)
									)
								),
								array(
									'name'       => 'wplf_redirect_to_login_redirects',
									'std'        => '0',
									'label'      => sprintf( __( '%s Precedence' ), 'redirect_to' ),
									'cb_label'   => sprintf( __( 'Yes, allow a POST or GET %s variable to take priority over above settings' ), '<code>redirect_to</code>' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'By enabling this setting, any redirect_to value set in GET or POST params will take priority over the above rules.  More than likely you will want to leave this disabled.' ),
								),
							)
						),
						'logout_redirects' => array(
							'title'  => __( 'Logout Redirects' ),
							'fields' => array(
								array(
									'name'        => 'wplf_default_logout_redirect',
									'label'       => __( 'Default Logout Redirect' ),
									'desc'        => __( 'Enter the endpoint for default redirect after a user logs out (if they don\'t match any other rules below)' ),
									'placeholder'         => '/my-account',
									'type'        => 'textbox',
									'field_class' => '',
									'attributes'  => array(),
								),
								array(
									'name'       => 'wplf_role_logout_redirects',
									'std'        => '0',
									'label'      => __( 'Role Logout Redirects' ),
									'type'       => 'repeatable',
									'attributes' => array(),
									'single_val' => 'role', // Signify this group has single val fields to check on page init
									'desc'       => __( 'Select any custom redirects to use for specific user roles' ),
									'rfields'    => array(
										'role'    => array(
											'label'       => __( 'Role' ),
											'type'        => 'userroles',
											'help'        => __( 'Enter the label to show above the input field' ),
											'default'     => '',
											'placeholder' => '',
											'multiple'    => true,
											'single_val'  => true, // Means values can't be selected more than once in repeatable
											'required'    => true
										),
										'redirect' => array(
											'label'       => __( 'Redirect' ),
											'type'        => 'textbox',
											'default'     => '',
											'placeholder' => '/some-endpoint',
											'help'        => __( 'Endpoint (on this site) to redirect to (do NOT include website URL)' ),
											'placeholder' => '',
											'multiple'    => true,
											'required'    => true
										)
									)
								),
								array(
									'name'       => 'wplf_redirect_to_logout_redirects',
									'std'        => '0',
									'label'      => sprintf( __( '%s Precedence' ), 'redirect_to' ),
									'cb_label'   => sprintf( __( 'Yes, allow a POST or GET %s variable to take priority over above settings' ), '<code>redirect_to</code>' ),
									'type'       => 'checkbox',
									'attributes' => array(),
									'desc'       => __( 'By enabling this setting, any redirect_to value set in GET or POST params will take priority over the above rules.  More than likely you will want to leave this disabled.' ),
								),
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
									'name'  => 'wplf_font_color',
									'label' => __( 'Font Color' ),
									'desc'  => __( 'Use a custom font color for wp-login.php page.' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'  => 'wplf_link_color',
									'label' => __( 'Link Color' ),
									'desc'  => __( 'Use a custom color for links on the wp-login.php page.' ),
									'type'  => 'colorpicker'
								),
								array(
									'name'  => 'wplf_link_hover_color',
									'label' => __( 'Link Hover Color' ),
									'desc'  => __( 'Use a custom color when hovering over links on the wp-login.php page.' ),
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
					'title'  => __( 'Email Templates' ),
					'sections' => array(
						'activation' => array(
							'title' => __( 'New User Activation Email Template' ),
							'hide_if' => get_option( 'wplf_register_set_pw', false ),
							'fields' => array(
								array(
									'name'       => 'wplf_activation_subject',
									'label'      => __( 'Email Subject' ),
									'desc'       => __( 'This will be used as the subject for the Activation email.  You can use any template tags available in message below.' ),
									'std'        => __( 'Account Activation Required' ),
									'type'       => 'textbox',
									'field_class'      => 'widefat',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_activation_message',
									'label'      => __( 'Email Message' ),
									'desc'       => __( 'This template will be used as the first email sent to the user to activate their account.<br /><strong>Available Template Tags:</strong> <code>%wp_activate_url%</code>, <code>%wp_activation_key%</code>, <code>%wp_user_name%</code>, <code>%wp_user_email%</code>, <code>%wp_site_url%</code>, <code>%wp_login_url%</code>' ),
									'std'        => __( 'Thank you for registering your account:' ) . '<br />%wp_site_url%<br />' . sprintf( __( 'Username: %s' ), '%wp_user_name%' ) . '<br /><br />' . __( 'In order to activate your account and set your password, please visit the following address:' ) . '<br /><a href="%wp_activate_url%">%wp_activate_url%</a>',
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						),
						'new_user' => array(
							'title' => __( 'New User Email Template' ),
							'hide_if' => get_option( 'wplf_require_activation', true ),
							'fields' => array(
								array(
									'name'       => 'wplf_new_user_subject',
									'label'      => __( 'Email Subject' ),
									'desc'       => __( 'This will be used as the subject for the new user registered email.  You can use any template tags available in message below.' ),
									'std'        => __( 'Your Account Information' ),
									'type'       => 'textbox',
									'field_class'      => 'widefat',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_new_user_message',
									'label'      => __( 'Email Message' ),
									'desc'       => __( 'This template will be used as the first email sent to the user after creating an account (with their own password).<br /><strong>Available Template Tags:</strong> <code>%wp_user_name%</code>, <code>%wp_user_email%</code>, <code>%wp_site_url%</code>, <code>%wp_login_url%</code>' ),
									'std'        => __( 'Thank you for registering your account:' ) . '<br />%wp_site_url%<br />' . sprintf( __( 'Username: %s' ), '%wp_user_name%' ) . '<br /><br />' . __( 'To login to your account, please visit the following address:' ) . '<br /><a href="%wp_login_url%">%wp_login_url%</a>',
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						),
						'lostpassword' => array(
							'title' => __( 'Lost Password Email Template' ),
							'fields' => array(
								array(
									'name'       => 'wplf_lostpassword_subject',
									'label'      => __( 'Email Subject' ),
									'desc'       => __( 'This will be used as the subject for the Lost Password email.  You can use any template tags available in message below.' ),
									'std'        => __( 'Password Reset' ),
									'type'       => 'textbox',
									'field_class'      => 'widefat',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_lostpassword_message',
									'label'      => __( 'Email Message' ),
									'desc'       => __( 'This template will be used whenever someone submits a lost/reset password request.<br /><strong>Available Template Tags:</strong> <code>%wp_reset_pw_url%</code>, <code>%wp_reset_pw_key%</code>, <code>%wp_user_name%</code>, <code>%wp_user_email%</code>, <code>%wp_site_url%</code>, <code>%wp_login_url%</code>' ),
									'std'        => __( 'Someone requested that the password be reset for the following account:') . '<br />%wp_site_url%<br />' . sprintf( __( 'Username: %s' ), '%wp_user_name%' ) . '<br /><br />' . __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . '<br />' . __( 'To reset your password, visit the following address:' ) . '<br /><a href="%wp_reset_pw_url%">%wp_reset_pw_url%</a>',
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
							)
						)
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
									'std'        => __( 'Thank you for registering.  Please check your email for your activation link.<br><br>If you do not receive the email please request a <a href="%wp_lost_pw_url%">password reset</a> to have the email sent again.' ),
									'desc'       => __( 'This notice will be shown to the user when they attempt to login but have not activated their account.<br /><strong>Available Template Tags:</strong> <code>%wp_lost_pw_url%</code>, <code>%wp_site_url%</code>, <code>%wp_login_url%</code>' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_notice_activation_pending',
									'label'      => __( 'Pending Activation Notice' ),
									'std'        => __( '<strong>ERROR</strong>: Your account is still pending activation, please check your email, or you can request a <a href="%wp_lost_pw_url%">password reset</a> for a new activation code.' ),
									'desc'       => __( 'This notice will be shown to the user when they attempt to login but have not activated their account.<br /><strong>Available Template Tags:</strong> <code>%wp_lost_pw_url%</code>, <code>%wp_site_url%</code>, <code>%wp_login_url%</code>' ),
									'type'       => 'wpeditor',
									'attributes' => array(),
								),
								array(
									'name'       => 'wplf_notice_activation_thankyou',
									'label'      => __( 'Successful Activation Notice' ),
									'std'        => '<p>' . __( 'Your account has been successfully activated!' ) . '</p><p>' . sprintf( __( 'You can now <a href="%s">Log In</a>'), '%wp_login_url%' ) . '</p>',
									'desc'       => __( 'This notice will be shown to the user once they activate and set the password for their account.<br /><strong>Available Template Tags:</strong> <code>%wp_lost_pw_url%</code>, <code>%wp_site_url%</code>, <code>%wp_login_url%</code>' ),
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
				),
				'settings' => array(
					'title'    => __( 'Settings' ),
					'sections' => array(
						'config' => array(
							'title'  => __( 'Configuration' ),
							'fields' => array(
								array(
									'name'       => 'wplf_uninstall_remove_options',
									'std'        => '0',
									'label'      => __( 'Remove on Uninstall' ),
									'cb_label'   => __( 'Enable' ),
									'desc'       => __( 'This will remove all configuration and options when you uninstall the plugin (disabled by default)' ),
									'type'       => 'checkbox',
									'attributes' => array()
								),
								array(
									'name'       => 'wplf_show_admin_bar_only_admins',
									'std'        => '0',
									'label'      => __( 'Admin Bar' ),
									'cb_label'   => __( 'Only show admin bar for administrators (users with manage_options capability)' ),
									'desc'       => __( 'By default, WordPress will show the admin bar for any kind of user when browsing the site.  Enable this setting to only show for Administrators.' ),
									'type'       => 'checkbox',
									'attributes' => array()
								),
								array(
									'name'       => 'wplf_reset_default',
									'field_class'  => 'button-primary',
									'action' => 'reset_default',
									'label'      => __( 'Reset to Defaults' ),
									'caption'   => __( 'Reset to Defaults' ),
									'desc'       => __( '<strong>CAUTION!</strong> This will remove ALL configuration values, and reset everything to default!' ),
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

	/**
	 * Return Settings Fields
	 *
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
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

				if( array_key_exists( 'hide_if', $section ) && ! empty( $section['hide_if'] ) ){
					continue;
				}

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

	/**
	 * Build arguments to pass to settings fields/handlers
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param      $option
	 * @param bool $register
	 *
	 * @return array
	 */
	function build_args( $option, $register = true ){

		$submit_handler = 'submit_handler';

		if ( method_exists( $this, "{$option['type']}_handler" ) ) $submit_handler = "{$option['type']}_handler";

		if ( isset( $option[ 'std' ] ) ) add_option( $option[ 'name' ], $option[ 'std' ] );

		if( $register ) register_setting( $this->settings_group, $option[ 'name' ], array( $this, $submit_handler ) );

		$placeholder = ( ! empty( $option[ 'placeholder' ] ) ) ? 'placeholder="' . $option[ 'placeholder' ] . '"' : '';
		$class       = ! empty( $option[ 'class' ] ) ? $option[ 'class' ] : '';
		$field_class = ! empty( $option[ 'field_class' ] ) ? $option[ 'field_class' ] : '';

		$non_escape_fields = array( 'wpeditor', 'repeatable' );
		$value       = in_array( $option['type'], $non_escape_fields ) ? get_option( $option['name'] ) : esc_attr( get_option( $option[ 'name' ] ) );

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
			'class'       => $class,
		    'field_class' => $field_class
		);

		return $field_args;

	}

}