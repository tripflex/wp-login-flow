<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Login_Flow_Settings_Fields {

	function checkbox_field( $args ) {

		$o       = $args[ 'option' ];
		$checked = checked( $args[ 'value' ], 1, FALSE );

		echo "<label><input id=\"{$o['name']}\" type=\"checkbox\" class=\"{$args['class']}\" name=\"{$o['name']}\" value=\"1\"  {$args['attributes']} {$checked} /> {$o['cb_label']} </label>";
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
		echo esc_textarea( $o[ 'value' ] );
		echo "</textarea>";
		$this->description( $o );

	}

	function textbox_field( $args ) {

		$o = $args[ 'option' ];

		echo "<input id=\"{$o['name']}\" type=\"text\" class=\"{$args['class']}\" name=\"{$o['name']}\" value=\"{$args['value']}\" {$args['placeholder']} {$args['attributes']} />";
		$this->description( $o );

	}

	function description( $o ) {

		if ( ! empty( $o[ 'desc' ] ) ) echo "<p class=\"description\">{$o['desc']}</p>";

	}

}