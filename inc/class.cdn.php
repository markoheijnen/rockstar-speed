<?php

class Rockstar_Speed_Cdn {
	private $site_domain;
	private $cdn_domain;
	private $extensions;

	public function __construct() {
		add_action( 'init', array( $this, 'set_urls' ) );
	}

	public function set_urls() {
		$this->site_domain = preg_replace( "((https?)://)", "", site_url() );
		$this->cdn_domain  = apply_filters( 'rockstarspeed_cdn_domain', false );
		$this->extensions  = apply_filters( 'rockstarspeed_cdn_extensions', array( 'jpe?g', 'gif', 'png', 'css', 'bmp', 'js', 'ico' ) );

		if( $this->cdn_domain && ! is_admin() )
			$this->activate_cdn();
	}

	private function activate_cdn() {
		add_filter( 'script_loader_src', array( $this, 'str_replace' ) );
		add_filter( 'style_loader_src', array( $this, 'str_replace' ) );
		add_filter( 'theme_root_uri', array( $this, 'str_replace' ) );

		add_filter( 'the_content', array( $this, 'preg_replace' ) );
	}

	public function str_replace( $url ) {
		return str_replace( $this->site_domain, $this->cdn_domain, $url );
	}

	public function preg_replace( $content ) {
		return preg_replace( "#=([\"'])(https?://{$this->site_domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#", '=$1http://' . $this->cdn_domain . '/$3.$4$5$1', $content );
	}

}