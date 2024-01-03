<?php
/**
 * Fontawesome Icon List
 *
 */


echo '<'.block_value('h-tag').'>'.block_value('headline').'</'.block_value('h-tag').'>';
if ( block_rows( 'list-rows' ) ):
	echo '<ul class="icon-list">';

	while ( block_rows( 'list-rows' ) ) :
		block_row( 'list-rows' );
		$faType = (block_sub_value('icon') == 'fa-usb') ? 'fab' : 'fa';
		echo '<li>
				<i class="'.$faType." ".block_sub_value('icon').'" aria-hidden="true"></i>'.
		     block_sub_value( 'text' ).
		     '</li>';
	endwhile;

	echo '</ul>';
endif;

reset_block_rows( 'list-rows' );

