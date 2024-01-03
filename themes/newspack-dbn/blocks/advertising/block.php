<?php
/**
 * Advertising Block
 *
 */

if ( ! is_admin() ) {
	if ( function_exists( 'fdwp_get_ad_config' ) ) {
		try {
			$ad_config = fdwp_get_ad_config();
		} catch ( Error $exception ) {
			error_log( $exception );
		}
	}

	$args          = []; // args holding the params for the template
	$display_block = false; // block row will be only displayed, if ad env & row value match

	if ( block_rows( 'advertising' ) ):

		while ( block_rows( 'advertising' ) ) :
			block_row( 'advertising' );
			if ( $ad_config['environment'] === block_sub_value( 'environment' ) ) :
				$args['ad_slot']   = block_sub_value( 'ad_slot' );
				$args['ad_size']   = block_sub_value( 'ad_sizes' );
				$args['poscount']  = block_sub_value( 'poscount' );
				$args['blockmode'] = true; // prevents ad-inline class
				$display_block     = true;
			endif;
		endwhile;

	endif;

	reset_block_rows( 'advertising' );

	if ( function_exists( 'load_template_part' ) && $display_block ) {
		$ad_slot = load_template_part( 'template-parts/ads/ad-inline', 'ad-block' . $args['ad_slot'], $args );
		printf( '<article class="entry">%s</article>', $ad_slot );
	}
}
