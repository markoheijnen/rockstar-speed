<?php

class Rockstar_Speed_Minify_HTML {
	public $use_gzip = true;
	public $compress_css = true;
	public $compress_js = true;
	public $remove_comments = true;

	function __construct() {
		
	}

	public function minify_page() {
		add_action( 'template_redirect', array( &$this, 'compression_start' ), -1 );
	} 

	function compression_start() {
		if ( ! is_feed() ) {
			ob_start( array( &$this, 'compression_finish' ) );
		}
	}

	function compression_finish( $html ) {
		return $this->parseHTML( $html );
	}

	public function parse_html( $html ) {
		$html = $this->minify_html( $html );

		return $html;
	}

	protected function minify_html( $html_orig ) {
		$matches = array();
		$pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';

		//This can go wrong when PREC is out-of-date
		@preg_match_all( $pattern, $html_orig, $matches, PREG_SET_ORDER );
		
		$overriding = false;
		$raw_tag    = false;
		
		// Variable reused for output
		$html = '';
		
		foreach( $matches as $token ) {
			$tag = ( isset( $token['tag'] ) ) ? strtolower( $token['tag'] ) : null;
			
			$content = $token[0];
			
			if( is_null( $tag ) )
			{
				if ( ! empty( $token['script'] ) )
				{
					$strip = $this->compress_js;
				}
				else if ( ! empty( $token['style'] ) )
				{
					$strip = $this->compress_css;
				}
				else if ( $this->remove_comments )
				{
					if ( ! $overriding && $raw_tag !== 'textarea' )
					{
						// Remove any HTML comments, except MSIE conditional comments
						$content = preg_replace( '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content );
					}
				}
			}
			else {
				// All tags except script, style and comments
				if ( $tag === 'pre' || $tag === 'textarea' ) {
					$raw_tag = $tag;
				}
				else if ( $tag === '/pre' || $tag === '/textarea' ) {
					$raw_tag = false;
				}
				else if ( $raw_tag || $overriding ) {
					$strip = false;
				}
				else {
					if( $tag !== '' ) {
						if( strpos( $tag, '/' ) === false ) {
							// Remove any empty attributes, except:
							// action, alt, content, src
							$content = preg_replace( '/(\s+)(\w++(?<!action|alt|content|src)=(""|\'\'))/i', '$1', $content );
						}
						
						// Remove any space before the end of a tag (including closing tags and self-closing tags)
						$content = preg_replace( '/\s+(\/?\>)/', '$1', $content );
					}
					else {
						// Content between opening and closing tags
						// Avoid multiple spaces by checking previous character in output HTML
						if( strrpos( $html,' ' ) === strlen( $html ) - 1 )
						{
							// Remove white space at the content beginning
							$content = preg_replace( '/^[\s\r\n]+/', '', $content );
						}
					}
					
					$strip = true;
				}
			}
			
			if ( $strip ) {
				$content = $this->remove_whitespace( $content );
			}
			
			$html .= $content;
		}

		if( empty( $html ) ) {
			$html = $html_orig;

			if( apply_filters( 'rockstarspeed_remove_whitespace_onerror', true ) ) {
				$html = $this->remove_whitespace( $html );
			}
		}

		if ( $this->use_gzip && substr_count( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) ) {
			header('Content-Encoding: gzip');
			$html = gzencode( $html, 1 );

			header('Content-Length: '.strlen($html));
		}

		return $html;
	}

	protected function remove_whitespace( $html )
	{
		$html = str_replace( "\t", ' ', $html );
		$html = str_replace( "\r", ' ', $html );
		$html = str_replace( "\n", ' ', $html );
		
		// This is over twice the speed of a RegExp
		while( strpos( $html, '  ' ) !== false )
		{
			$html = str_replace( '  ', ' ', $html );
		}
		
		return $html;
	}
}

?>