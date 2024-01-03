<?php
/**
 * FDL-920
 *
 * Apester Umfrage - manuelle Integration durch einen Redakteur in einem Artikel
 *
 */

$media_id = trim( block_value( 'media-id' ) );
if ( ! amp_is_request() && strlen( $media_id > 0 ) ) {
	/*
		Note: the ES6 module gets loaded separately, because of missing implementation in consentmanager for type="module"
		<script type="module" src="https://sdk.apester.com/web-sdk.core.min.js"></script>
	*/
	if ( function_exists( 'load_template_part' ) ) {
		printf( '<div class="apester-media" data-media-id="%s"></div>
				   %s
				   <script type="text/plain"
				   nomodule
				   async
				   data-cmp-src="https://sdk.apester.com/web-sdk.core.legacy.min.js"
        		   class="cmplazyload"
        		   data-cmp-vendor="354"></script>',
			esc_attr( $media_id ),
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - static template content
			load_template_part( 'template-parts/ads/helpers/apester-module-loader.php', 'apester-loader' ) );
	}
}
