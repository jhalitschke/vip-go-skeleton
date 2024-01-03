<?php
/**
 * Zähl-Pixel Block for Sponsored Content (Native Ads)
 *
 */

if ( function_exists( '\Newspack_Sponsors\get_all_sponsors' ) ) {
	$sponsors = \Newspack_Sponsors\get_all_sponsors( get_the_ID(), 'native' );

	if ( $sponsors ) {
		// get JS code and check, if contains script-tags (inserted by Administrators)
		$javascript = html_entity_decode( block_value( 'javascript-pixel' ) );
		$javascript = preg_replace( '/<script\b[^>]*>([\s\S]*?)<\/script>/m', '$1', $javascript );

		if ( ! amp_is_request() ) {
			?>
			<script>
				<?php
				echo $javascript;
				?>
			</script>
			<?php
		} else {
			echo block_field( 'amp-pixel' );
		}
	} else {
		echo '<!-- counter-pixel only allowed on sponsored content -->';
	}
}

