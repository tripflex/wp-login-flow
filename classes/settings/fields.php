<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Login_Flow_Settings_Fields
 *
 * @since 3.0.0
 *
 */
class WP_Login_Flow_Settings_Fields {

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function checkbox_field( $args ) {
		$args = $this->parse_args( $args );
		$o       = $args[ 'option' ];
		$checked = checked( $args[ 'value' ], 1, FALSE );
		$disabled_field = ( isset( $o[ 'disabled' ] ) && $o[ 'disabled' ] ? "disabled=\"disabled\"" : "" );
		echo "<label><input id=\"{$o['name']}\" type=\"checkbox\" class=\"wplf-checkbox {$args['field_class']}\" name=\"{$o['name']}\" value=\"1\" {$args['attributes']} {$checked} {$disabled_field}/> {$o['cb_label']} </label>";
		if ( isset( $o['break'] ) ) {
			echo "<br />";
		}
		$this->sub_fields( $o );
		$this->description( $o );
		$this->check_permalinks( $o );
	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function default_header( $args ) {

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function button_field( $args ) {

		$o = $args[ 'option' ];

		echo "<button id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"wplf-button button {$args['field_class']}\" {$args['attributes']}>{$o['caption']}</button>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function backup_field( $args ) {

		$o   = $args[ 'option' ];
		$url = admin_url( 'admin-ajax.php' );
		echo "<form method=\"POST\" action=\"{$url}\">";
		echo "<input type=\"hidden\" name=\"action\" value=\"wp_login_flow_dl_backup\" />";
		wp_nonce_field( 'wp_login_flow_dl_backup', 'wp_login_flow_dl_backup' );
		echo "<button id=\"{$o['name']}\" name=\"wp_login_flow_dl_backup\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$args['field_class']}\" {$args['attributes']}>{$o['caption']}</button>";
		echo "</form>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function link_field( $args ) {

		$o = $args[ 'option' ];

		echo "<a id=\"{$o['name']}\" href=\"{$o['href']}\" class=\"wplf-link {$args['field_class']}\" {$args['attributes']}>{$o['caption']}</a>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function select_field( $args ) {

		$o = $args[ 'option' ];

		echo "<select id=\"{$o['name']}\" class=\"wplf-select {$args['field_class']}\" name=\"{$o['name']}\" {$args['attributes']}>";

		foreach ( $o[ 'options' ] as $key => $name ) {
			$value    = esc_attr( $key );
			$label    = esc_attr( $name );
			$selected = selected( $o[ 'value' ], $key, FALSE );

			echo "<option value=\"{$value}\" {$selected}> {$label} </option>";
		}

		echo "</select>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function userroles_field( $args ) {
		$args = $this->parse_args( $args );
		$o = $args['option'];

		$svalue = isset( $args['value'] ) ? $args['value'] : '';

		$all_roles = wp_roles()->roles;

		echo "<select id=\"{$o['name']}\" class=\"wplf-select wplf-user-roles {$args['field_class']}\" name=\"{$o['name']}\" {$args['attributes']}>";

		foreach ( $all_roles as $role => $details ) {
			$name = translate_user_role( $details['name'] );
			$value    = esc_attr( $role );
			$label    = esc_attr( $name );
			$selected = selected( $svalue, $role, FALSE );

			echo "<option value=\"{$value}\" {$selected}> {$label} </option>";
		}

		echo "</select>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function textarea_field( $args ) {

		$o = $args[ 'option' ];

		echo "<textarea cols=\"50\" rows=\"3\" id=\"{$o['name']}\" class=\"wplf-textarea {$args['field_class']}\" name=\"{$o['name']}\" {$args['attributes']}>";
		if( isset( $args['value'] ) ) echo esc_textarea($args[ 'value' ]);
		echo "</textarea>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 3.0.0
	 *
	 */
	function parse_args( $args ){

		$args['field_class'] = isset( $args['field_class'] ) ? $args['field_class'] : '';
		$args['value'] = isset( $args['value'] ) ? $args['value'] : '';
		$args['placeholder'] = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		$args['attributes'] = isset( $args['attributes'] ) ? $args['attributes'] : '';
		$args['class'] = isset( $args['class'] ) ? $args['class'] : '';

		return $args;
	}

	/**
	 *
	 *
	 *
	 * @param $a
	 *
	 * @since 3.0.0
	 *
	 */
	function textbox_field( $a ) {

		$a = $this->parse_args( $a );
		$o = $a[ 'option' ];
		$append = isset( $o['append'] ) ? $o['append'] : '';
		$prepend = isset( $o['prepend'] ) ? $o['prepend'] : '';

		$disabled_field = ( isset( $o[ 'disabled' ] ) && $o[ 'disabled' ] ? "disabled=\"disabled\"" : "");
		echo "{$prepend}<input id=\"{$o['name']}\" type=\"text\" class=\"wplf-textbox {$a['field_class']}\" name=\"{$o['name']}\" value=\"{$a['value']}\" {$a['placeholder']} {$a['attributes']} {$disabled_field}/>{$append}";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $o
	 *
	 * @return bool|void
	 * @since 3.0.0
	 *
	 */
	function check_permalinks( $o ){

		if ( ! isset( $o[ 'fields' ] ) || empty( $o[ 'fields' ] ) || ! isset( $o['fields'][0] ) || empty( $o['fields'][0] ) ) return;
		$field = $o['fields'][0];
		// No need to check if this is not a rewrite field
		if ( strpos( $field['name'], 'wplf_rewrite_' ) === FALSE ) return false;

		$path = esc_attr( get_option( $field[ 'name' ], $field['std'] ) );
		$check_page = get_page_by_path( $path );
		if( ! $check_page ) return false;

		echo "<div class=\"wplf-rewrite-conflict\">" . __( 'This rewrite conflicts with the ' ) . "<a href=\"" . get_edit_post_link( $check_page->ID ) . "\" target=\"_blank\">{$check_page->post_title} {$check_page->post_type}</a>" . __( ' permalink!' ) . "<br /><small>" . __( 'Your rewrites for WP Login Flow should take precendence over any permalinks but this also means that page will not load correctly now!' ) . "</small><br/><small>" . __( "You should probably use a different permalink or rewrite." ) . "</small></div>";

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function spinner_field( $args ) {

		$o = $args[ 'option' ];
		$append = isset( $o['append'] ) ? $o['append'] : '';
		$prepend = isset( $o['prepend'] ) ? $o['prepend'] : '';

		echo "{$prepend}<input id=\"{$o['name']}\" type=\"number\" min=\"1\" max-length=\"3\" max=\"999\" step=\"1\" class=\"wplf-number {$args['field_class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" {$args['placeholder']} {$args['attributes']} />{$append}";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function colorpicker_field( $args ){

		wp_enqueue_style( 'wp-color-picker' );
		$o = $args[ 'option' ];
		echo "<input id=\"{$o['name']}\" type=\"text\" class=\"wplf-color-picker {$args['field_class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" {$args['placeholder']} {$args['attributes']} />";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function wpeditor_field( $args ){

		$o = $args[ 'option' ];

		$wp_args = array(
			'wpautop' => false,
			'drag_drop_upload' => true,
			'editor_height' => 425, // In pixels, takes precedence and has no default value
			'textarea_rows' => 20,  // Has no visible effect if editor_height is set, default is 20
			'classes' => 'wplf-wp-editor',
			'tinymce' => array(
				'height' => 425
			)
		);

		$default = get_option( 'default_post_edit_rows', 10 );
		if ( strpos( $o['name'], 'wplf_notice' ) === 0 ) $wp_args['editor_height'] = 400;

		$editor = apply_filters( 'login_flow_wp_editor_args', $wp_args );
		if ( ! isset( $o[ 'disabled' ] ) || ! $o[ 'disabled' ] ) wp_editor( $args[ 'value' ], $o[ 'name' ] ,$editor );
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $args
	 *
	 * @since 3.0.0
	 *
	 */
	function upload_field( $args ){

		wp_enqueue_media();
		$o = $args[ 'option' ];
?>
		<ul id="<?php echo $o[ 'name' ]; ?>-ul" class="attachments" style="<?php if ( empty( $args[ 'value' ] ) ) echo 'display:none;'; ?>">
			<li class="attachment selected" style="position: relative; left: 0px; top: 0px;">
				<div class="attachment-preview">
					<div class="thumbnail">
						<div class="centered">
							<a href="#" data-name="<?php echo $o['name']; ?>" data-title="<?php echo $o['modal_title']; ?>" data-button="<?php echo $o['modal_btn']; ?>" class="wplf-upload-show">
								<img id="<?php echo $o['name']; ?>-img" src="<?php if( isset( $args[ 'value' ] ) ) echo $args[ 'value' ]; ?>">
							</a>
						</div>
					</div>
					<a data-name="<?php echo $o[ 'name' ]; ?>" title="<?php _e('Remove'); ?>" href="#" class="wpjm-upload-remove check">
						<div class="media-modal-icon"></div>
					</a>
				</div>
			</li>
		</ul>
<?php
		echo "<input id=\"{$o['name']}\" type=\"hidden\" class=\"wplf-upload {$args['field_class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" />";
		echo "<a href=\"#\" data-name=\"{$o['name']}\" data-title=\"{$o['modal_title']}\" data-button=\"{$o['modal_btn']}\" class=\"wplf-upload-show button button-secondary\">" . __( 'Select Logo' ) . "</a>";
		$this->description( $o );

	}

	/**
	 *
	 *
	 *
	 * @param $o
	 *
	 * @return bool
	 * @since 3.0.0
	 *
	 */
	function description( $o ) {

		if ( ! empty( $o[ 'desc' ] ) ) echo "<div class=\"wplf-description description\">{$o['desc']}</div>";

		if ( ! empty( $o[ 'endpoints' ] ) ) {
			echo "<div class=\"wplf-additional-endpoints\"><strong>";
			_e( 'Endpoints:' );
			echo "</strong>";
			foreach( $o['endpoints'] as $endpoint ){
				echo "<code class=\"wplf-additional-endpoint\">/{$endpoint}</code>";
			}
			echo "</div>";
		}

		return false;
	}

	/**
	 *
	 *
	 *
	 * @param $a
	 *
	 * @since 3.0.0
	 *
	 */
	function repeatable_field( $a ){
		$o = $a['option'];
		$values = isset( $a['value'] ) && ! empty( $a['value'] ) ? maybe_unserialize( $a['value'] ) : array();
		$single_val = isset( $o['single_val'] ) && ! empty( $o['single_val'] ) ? "data-singleval=\"{$o['single_val']}\"" : '';
		?>
		<div class="wplf-repeatable-wrap">
			<div id="wplf-repeatable">
				<div class="wplf-repeatable-form" id="repeatable-<?php echo esc_attr( $a['option']['name'] ); ?>-form" data-group="<?php echo esc_attr( $a['option']['name'] ); ?>" <?php echo $single_val; ?>>
					<?php
						if( ! empty( $values ) ){
							foreach( $values as $index => $value ){
								$this->repeatable_field_template( $a, $value, $index );
							}
						}
					?>
				</div>
			</div>

			<?php $this->repeatable_field_template( $a ); ?>
		</div>

		<?php
	}

	/**
	 *
	 *
	 *
	 * @param      $a
	 * @param bool $value
	 * @param bool $index
	 *
	 * @since 3.0.0
	 *
	 */
	function repeatable_field_template( $a, $value = false, $index = false ){
		$o = $a['option'];
		$existing_output = ! empty( $value ) && $index !== false;

		if ( isset( $o['rfields'] ) && ! empty( $o['rfields'] ) ) {

			if( ! $existing_output ){
				echo "<div class=\"repeatable-add-group-row\"><button class=\"button repeatable-add-group-row-button\" type=\"button\" data-group=\"" . $o['name'] . "\" data-rowtemplate=\"group-" . $o['name'] . "-tmpl\">" . __( 'Add Another' ) . "</button></div>\r\n";
				echo "<script type=\"text/html\" id=\"group-" . $o['name'] . "-tmpl\">\r\n";
			}

			echo "	<table class=\"form-table rowGroup {$o['name']}-groupitems\" id=\"groupitems\" ref=\"items\">\r\n";
			?>
			<thead class="repeatable-fields-handle">
					<tr>
						<td>
							<div class="<?php echo esc_attr( $o['name'] ); ?>-repeatable-fields-handle">☰</div>
						</td>
					</tr>
				</thead>
			<?php
			echo "		<tbody>\r\n";
			$c = 0;
			foreach ( $o['rfields'] as $field => $settings ) {
				$c++;
				$type_func = $settings['type'] . '_field';
				if ( ! method_exists( $this, $type_func ) ) {
					continue;
				}
				$count = ! $existing_output ? "__count__" : "{$index}";

				//dump($settings);
				$id        = 'field_{{id}}_' . $field;
				$name      = "{$o['name']}[{$count}][{$field}]";
//				$label     = '{{label_' . $field . '}}';
				$label = $settings['label'];

				$row_style = ( isset( $settings['template_style'] ) ? '{{style_' . $field . '}}' : '' );
				$caption   = ( isset( $settings['caption'] ) ? $settings['caption'] : '' );
				echo "<tr class=\"repeatable-field repeatable-fields-{$o['name']}\" valign=\"top\" style=\"" . $row_style . "\" id=\"repeatable-fields-" . $id . "-tr\">\r\n";
				echo "<th scope=\"row\">\r\n";
				echo "<label for=\"" . $id . "\">" . $label . "</label>\r\n";
				echo "</th>\r\n";
				echo "<td class=\"repeatable-fields-{$id}-td\">\r\n";

				$gen_options = array(
					'name' => $name
				);

				$new_options = array_merge( $settings, $gen_options );
				$type_args = $existing_output ? array( 'value' => isset( $value[ $field ] ) ? $value[ $field ] : "", 'option' => $new_options ) : array( 'option' => $new_options );

				if( isset( $settings['required'] ) && ! empty( $settings['required'] ) ){
					$type_args['attributes'] = "required=\"required\"";
				}

				$this->$type_func( $type_args );

				if ( ! empty( $caption ) ) {
					echo "<p class=\"description\">" . $caption . "</p>\r\n";
				}
				echo "</td>\r\n";
				echo "</tr>\r\n";
				if ( $c === count( $o['rfields'] ) ) {
					echo "<tr class=\"repeatable-fields-{$o['name']}\" valign=\"top\" id=\"repeatable-fields-" . $o['name'] . "-remove-tr\">\r\n";
					echo "<td class=\"repeatable-fields-remove-td repeatable-fields-{$o['name']}-remove-td\">\r\n";
					echo "  <div data-group=\"{$o['name']}\" class=\"button button-primary right repeatable-fields-remove-group-row\">" . __( 'Remove' ) . '</div>';
					echo '</td></tr>';
				}
			}
			echo "		</tbody>\r\n";
			echo "	</table>\r\n";
			if ( ! $existing_output ) {
				echo "</script>";
			}
		}
	}

	/**
	 *
	 *
	 *
	 * @param $o
	 *
	 * @since 3.0.0
	 *
	 */
	function sub_fields( $o ) {

		if( ! isset( $o['fields'] ) || empty( $o[ 'fields' ] ) ) return;

		foreach( $o['fields'] as $field ){

			$type_func = $field[ 'type' ] . '_field';
			if ( ! method_exists( $this, $type_func ) ) continue;

			if( isset( $field['break'] ) ) echo "<br />";
			if( ! empty( $field['pre'] ) ) echo " {$field['pre']} ";
			$this->$type_func( $this->build_args( $field ), false );
			if( ! empty( $field['post'] ) ) echo " {$field['post']} ";

		}

	}

}