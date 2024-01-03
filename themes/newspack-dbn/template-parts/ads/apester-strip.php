<?php
/**
 * FDL-920
 *
 * Apester Strip for channel pages
 *
 */

if ( empty( $ad_config ) ) {
	$ad_config = fdwp_get_ad_config();
}

if ( $ad_config['ads_disabled'] ) {
	return;
}

if ( carbon_get_theme_option( 'apester_channel_enabled' ) ) {

	$channel_token = trim( carbon_get_theme_option( 'apester_channel_token' ) );

	if ( $channel_token !== '' ) {
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript - scripts need user consent from CMP
		printf( '<div class="apester-strip" is-mobile-only="false" data-channel-tokens="%s" item-shape="round"
     item-size="medium" strip-background="transparent" thumbnails-stroke-color="rgb(264, 46, 61)"
     header-font-family="Lato" header-provider="system" header-font-size="18" header-font-color="rgba(0,0,0,1)"
     header-font-weight="400" header-ltr="true" top-border-width="0" top-border-color="rgba(0, 0, 0, 1)"
     bottom-border-width="0" bottom-border-color="rgba(0, 0, 0, 1)" data-fast-strip="true"></div>
	 %s
	 <script type="text/plain"
			 nomodule
			 async
			 data-cmp-src="https://sdk.apester.com/web-sdk.core.legacy.min.js"
			 class="cmplazyload"
			 data-cmp-vendor="354"></script>',
			esc_attr( $channel_token ),
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - static template content
			load_template_part( 'template-parts/ads/helpers/apester-module-loader.php', 'apester-loader' ) );

		global $apester_strip_inserted;
		$apester_strip_inserted = $apester_strip_inserted ?? true;

	}
}
