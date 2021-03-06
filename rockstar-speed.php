<?php

include 'inc/class.cdn.php';
include 'inc/class.minify_html.php';

class Rockstar_Speed {
	function __construct() {
		new Rockstar_Speed_Cdn();

		$minifier_html = new Rockstar_Speed_Minify_HTML();
		if ( ! is_admin() && ( ! defined('WP_DEBUG') || ! WP_DEBUG ) ) {
			$minifier_html->minify_page();
		}

		if( apply_filters( 'rockstarspeed_remove_src_version', false ) ) {
			add_filter( 'script_loader_src', array( &$this, '_remove_version' ), 15 );
			add_filter( 'style_loader_src', array( &$this, '_remove_version' ), 15 );
		}
	}

	function _remove_version( $src ) {
		if( strpos( $src, '?ver=' ) )
			$src = remove_query_arg( 'ver', $src );

		return $src;
	}
}

new Rockstar_Speed;
