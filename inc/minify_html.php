<?php

class Rockstar_Speed_Minify_HTML {
	protected $compress_css = true;
	protected $compress_js = true;
	protected $remove_comments = true;

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

	public function parseHTML( $html ) {
		$html = $this->minifyHTML( $html );

		return $html;
	}

	protected function minifyHTML( $html_orig ) {
		$pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
		
		preg_match_all( $pattern, $html_orig, $matches, PREG_SET_ORDER );
		
		$overriding = false;
		$raw_tag    = false;
		
		// Variable reused for output
		$html_new = '';
		
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
				$content = $this->removeWhiteSpace( $content );
			}
			
			$html .= $content;
		}

		if( ! empty( $html ) )
			return $html;

		return $this->removeWhiteSpace( $html_orig );
	}

	protected function removeWhiteSpace( $html )
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