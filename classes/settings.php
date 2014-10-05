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

	}

	function output() {

		$this->init_settings();
		?>
		<div class="wrap">

			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e( 'Field Editor Settings' ); ?></h2>

			<form method="post" action="options.php">

				<?php
				settings_errors();
				settings_fields( $this->settings_group );
				?>

				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $this->settings as $key => $section ) {
						echo '<a href="#settings-' . sanitize_title( $key ) . '" class="nav-tab">' . esc_html( $section[ 0 ] ) . '</a>';
					}
					?>
				</h2>
				<div id="wplf-all-settings">
					<?php
					foreach ( $this->settings as $key => $section ) {
						echo "<div id=\"settings-{$key}\" class=\"settings_panel\">";
						do_settings_sections( "wplf_{$key}_section" );
						echo "</div>";
					}
					submit_button();
					?>
				</div>
			</form>

		</div>

		<script type="text/javascript">
			jQuery( '.nav-tab-wrapper a' ).click(
				function () {
					jQuery( '.settings_panel' ).hide();
					jQuery( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
					jQuery( jQuery( this ).attr( 'href' ) ).show();
					jQuery( this ).addClass( 'nav-tab-active' );
					return false;
				}
			);

			jQuery( '.nav-tab-wrapper a:first' ).click();
		</script>
	<?php
	}

	function init_settings() {

		$this->settings = apply_filters(
			'wp_login_flow_settings',
			array(
				'general'   => array(
					__( 'General' ),
					array(
						array(
							'name'  => 'wplf_about',
							'label' => '',
							'type'  => 'about'
						)
					)
				),
				'rewrites' => array(
					__( 'Rewrites' ),
					array(
						array(
							'name'  => 'wplf_rewrite',
							'label' => '',
							'type'  => 'rewrite'
						)
					)
				),
				'styles'  => array(
					__( 'Styles' ),
					array(
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
					),
				),
				'email'   => array(
					__( 'Email' ),
					array(
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
					),
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

		foreach ( $this->settings as $key => $section ) {

			$section_header = "default_header";

			if ( method_exists( $this, "{$key}_header" ) ) $section_header = "{$key}_header";

			add_settings_section( "wplf_{$key}_section", $section[ 0 ], array( $this, $section_header ), "wplf_{$key}_section" );

			foreach ( $section[ 1 ] as $option ) {

				$submit_handler = 'submit_handler';

				if ( method_exists( $this, "{$option['type']}_handler" ) ) $submit_handler = "{$option['type']}_handler";

				if ( isset( $option[ 'std' ] ) ) add_option( $option[ 'name' ], $option[ 'std' ] );

				register_setting( $this->settings_group, $option[ 'name' ], array( $this, $submit_handler ) );

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

				add_settings_field(
					$option[ 'name' ],
					$option[ 'label' ],
					array( $this, "{$option['type']}_field" ),
					"wplf_{$key}_section",
					"wplf_{$key}_section",
					$field_args
				);

			}
		}
	}

}