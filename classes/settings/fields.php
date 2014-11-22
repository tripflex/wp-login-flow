<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Settings_Fields {

	function checkbox_field( $args ) {

		$o       = $args[ 'option' ];
		$checked = checked( $args[ 'value' ], 1, FALSE );
		$disabled_field = ( isset( $o[ 'disabled' ] ) && $o[ 'disabled' ] ? "disabled=\"disabled\"" : "" );
		echo "<label><input id=\"{$o['name']}\" type=\"checkbox\" class=\"{$args['class']}\" name=\"{$o['name']}\" value=\"1\" {$args['attributes']} {$checked} {$disabled_field}/> {$o['cb_label']} </label>";
		$this->sub_fields( $o );
		$this->description( $o );

	}

	function default_header( $args ) {

	}

	function button_field( $args ) {

		$o = $args[ 'option' ];

		echo "<button id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$args['class']}\" {$args['attributes']}>{$o['caption']}</button>";
		$this->description( $o );

	}

	function link_field( $args ) {

		$o = $args[ 'option' ];

		echo "<a id=\"{$o['name']}\" href=\"{$o['href']}\" class=\"{$args['class']}\" {$args['attributes']}>{$o['caption']}</a>";
		$this->description( $o );

	}

	function select_field( $args ) {

		$o = $args[ 'option' ];

		echo "<select id=\"{$o['name']}\" class=\"{$args['class']}\" name=\"{$o['name']}\" {$args['attributes']}>";

		foreach ( $o[ 'options' ] as $key => $name ) {
			$value    = esc_attr( $key );
			$label    = esc_attr( $name );
			$selected = selected( $o[ 'value' ], $key, FALSE );

			echo "<option value=\"{$value}\" {$selected}> {$label} </option>";
		}

		echo "</select>";
		$this->description( $o );

	}

	function textarea_field( $args ) {

		$o = $args[ 'option' ];

		echo "<textarea cols=\"50\" rows=\"3\" id=\"{$o['name']}\" class=\"{$args['class']}\" name=\"{$o['name']}\" {$args['attributes']}>";
		if( isset( $o['value'] ) ) echo esc_textarea( $o[ 'value' ] );
		echo "</textarea>";
		$this->description( $o );

	}

	function textbox_field( $args ) {

		$o = $args[ 'option' ];
		$disabled_field = ( isset( $o[ 'disabled' ] ) && $o[ 'disabled' ] ? "disabled=\"disabled\"" : "");
		echo "<input id=\"{$o['name']}\" type=\"text\" class=\"{$args['class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" {$args['placeholder']} {$args['attributes']} {$disabled_field}/>";
		$this->description( $o );

	}

	function spinner_field( $args ) {

		$o = $args[ 'option' ];

		echo "<input id=\"{$o['name']}\" type=\"number\" min=\"1\" max-length=\"3\" max=\"999\" step=\"1\" class=\"{$args['class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" {$args['placeholder']} {$args['attributes']} />px";
		$this->description( $o );

	}

	function colorpicker_field( $args ){

		wp_enqueue_style( 'wp-color-picker' );
		$o = $args[ 'option' ];
		echo "<input id=\"{$o['name']}\" type=\"text\" class=\"wplf-color-picker {$args['class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" {$args['placeholder']} {$args['attributes']} />";
		$this->description( $o );

	}

	function wpeditor_field( $args ){

		$o = $args[ 'option' ];

		$editor = apply_filters( 'login_flow_wp_editor_args', array() );
		if ( ! isset( $o[ 'disabled' ] ) || ! $o[ 'disabled' ] ) wp_editor( $args[ 'value' ], $o[ 'name' ] ,$editor );
		$this->description( $o );

	}

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
		echo "<input id=\"{$o['name']}\" type=\"hidden\" class=\"wplf-upload {$args['class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" />";
		echo "<a href=\"#\" data-name=\"{$o['name']}\" data-title=\"{$o['modal_title']}\" data-button=\"{$o['modal_btn']}\" class=\"wplf-upload-show button button-secondary\">" . __( 'Select Logo' ) . "</a>";
		$this->description( $o );

	}

	function description( $o ) {

		if ( ! empty( $o[ 'desc' ] ) ) echo "<p class=\"description\">{$o['desc']}</p>";

	}

	function sub_fields( $o ) {

		if( ! isset( $o['fields'] ) || empty( $o[ 'fields' ] ) ) return;

		foreach( $o['fields'] as $field ){

			$type_func = $field[ 'type' ] . '_field';
			if ( ! method_exists( $this, $type_func ) ) continue;

			echo "<br />";
			if( ! empty( $field['pre'] ) ) echo $field['pre'];
			$this->$type_func( $this->build_args( $field ), false );
			if( ! empty( $field['post'] ) ) echo $field['post'];

		}

	}

}