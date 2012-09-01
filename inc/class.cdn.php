<?php

class Rockstar_Speed_Cdn {
	private $site_url;
	private $cdn_url;

	function __construct() {
		add_action( 'init', array( &$this, 'set_urls' ) );
	}

	function set_urls() {
		$this->site_url = preg_replace( "((https?)://)", "", site_url() );
		$this->cdn_url  = apply_filters( 'rockstarspeed_cdn_url', false );

		if( ! $this->cdn_url ) {
			add_action( 'template_redirect', array( &$this, 'activate_cdn' ) );
		}
	}

	function activate_cdn() {
		add_filter( 'script_loader_src', array( &$this, 'filter_js' ) );
		add_filter( 'style_loader_src', array( &$this, 'filter_css' ) );
		add_filter( 'theme_root_uri', array( &$this, 'filter_theme' ) );
	}

	function filter_js( $url ) {
		$url = $this->replace( $url );
		return $url;
	}

	function filter_css( $url ) {
		$url = $this->replace( $url );
		return $url;
	}

	function filter_theme( $url ) {
		$url = $this->replace( $url );
		return $url;
	}

	private function replace( $url ) {
		return str_replace( $this->site_url, $this->cdn_url, $url );
	}
}