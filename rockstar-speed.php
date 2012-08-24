<?php

include 'inc/class.cdn.php';
include 'inc/class.minify_html.php';

class Rockstar_Speed {
	function __construct() {
		new Rockstar_Speed_Cdn();

		$minifier_html = new Rockstar_Speed_Minify_HTML();
		if ( ! is_admin() ) {
			$minifier_html->minify_page();
		}

		add_filter( 'script_loader_src', array( &$this, '_remove_version' ), 15 );
		add_filter( 'style_loader_src', array( &$this, '_remove_version' ), 15 );
	}

	function _remove_version( $src ) {
		$parts = explode( '?', $src );
		return $parts[0];
	}

}
new Rockstar_Speed;
