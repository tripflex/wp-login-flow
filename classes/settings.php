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
		ob_start();
		?>
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e( 'WP Login Flow' ); ?></h2>

			<form method="post" action="options.php">

		<?php
				settings_errors();
				settings_fields( $this->settings_group );
		?>
				<h2 class="nav-tab-wrapper">
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
		<?php
								foreach( $tab['sections'] as $skey => $section ) {
									do_settings_sections( "wplf_{$key}_{$skey}_section" );
								}
		?>
						</div>
		<?php
						endforeach;
						submit_button();
		?>
				</div>
			</form>
		</div>

		<script type="text/javascript">
			jQuery( ".nav-tab-wrapper a" ).click( function () {
				jQuery( '.settings_panel' ).hide();
				jQuery( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
				jQuery( jQuery( this ).attr( 'href' ) ).show();
				jQuery( this ).addClass( 'nav-tab-active' );
				return false;
			});
			jQuery( '.nav-tab-wrapper a:first' ).click();
		</script>
	<?php
		ob_end_flush();
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
				'styles' => array(
					'title'  => __( 'Styles' ),
					'sections' => array(
						'login_styles' => array(
							'title' => __( 'Login Styles' ),
							'fields' => array(
								array(
									'name'    => 'wplf_backup',
									'caption' => __( 'Create Backup!' ),
									'class'   => 'button-primary',
									'action'  => 'create_backup',
									'label'   => __( 'Generate Backup' ),
									'desc'    => __( 'Generate and download a backup of all fields.' ),
									'type'    => 'backup'
								),
								array(
									'name'    => 'wplf_import',
									'caption' => __( 'Import Backup!' ),
									'class'   => 'button button-primary',
									'href'    => get_admin_url() . 'import.php?import=wordpress',
									'label'   => __( 'Import Backup' ),
									'desc'    => __( 'Import a previously generated backup for custom fields.  This uses the default WordPress import feature, if you do not see a file upload after clicking this button, make sure to import using WordPress importer.' ),
									'type'    => 'link'
								)
							)
						)
					)

				),
				'email' => array(
					'title'  => __( 'Email' ),
					'sections' => array(
						'email_from' => array(
							'title' => __( 'Email From' ),
							'fields' => array(
								array(
									'name'       => 'wplf_enable_bug_reporter',
									'std'        => '1',
									'label'      => __( 'Enable Bug Reporter' ),
									'cb_label'   => __( 'Enable' ),
									'desc'       => __( 'Enable the bug report icon in the top right corner to submit bug reports' ),
									'type'       => 'checkbox',
									'attributes' => array()
								),
								array(
									'name'    => 'wplf_remove_all',
									'caption' => __( 'I understand, remove all data!' ),
									'class'   => 'button-primary',
									'action'  => 'remove_all',
									'label'   => __( 'Remove All' ),
									'desc'    => __( 'This will remove all custom and customized field data!' ),
									'type'    => 'button'
								),
								array(
									'name'    => 'wplf_purge_options',
									'caption' => __( 'Purge Options!' ),
									'class'   => 'button-primary',
									'action'  => 'purge_options',
									'label'   => __( 'Purge Options' ),
									'desc'    => __( 'Older versions of this plugin saved option values for fields that do not require them. You can purge those values by clicking this button.' ),
									'type'    => 'button'
								),
								array(
									'name'  => 'wplf_field_dump',
									'std'   => '0',
									'label' => __( 'Field Data' ),
									'type'  => 'debug_dump'
								)
							)
						)
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

				add_settings_section( "wplf_{$key}_{$skey}_section", $section[ 'title' ], array( $this, $section_header ), "wplf_{$key}_{$skey}_section" );

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