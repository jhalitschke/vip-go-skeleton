<?php
/**
 * Advertising Block Preview
 *
 */

if ( block_rows( 'advertising' ) ):

	echo( '<h4>Advertising Block...</h4>' );

	while ( block_rows( 'advertising' ) ) :
		block_row( 'advertising' );

		printf( '<p>environment: %s</p><p>ad_slot: %s</p><p>ad_sizes: %s</p><p>poscount: %s</p><hr>',
				block_sub_value( 'environment' ),
				block_sub_value( 'ad_slot' ),
				block_sub_value( 'ad_sizes' ),
				block_sub_value( 'poscount' )
		);
	endwhile;
else:
	echo '<p>no block_rows</p>';
endif;

reset_block_rows( 'advertising' );



