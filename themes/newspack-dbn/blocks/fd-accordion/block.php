<?php
/**
 * FD Accordion Block
 *
 * FDL-1295-Accordion-Block
 */

if ( block_rows( 'fd-accordion-repeater' ) ) {
	echo '<div class="fd-accordion-block">';
	while ( block_rows( 'fd-accordion-repeater' ) ) {
		block_row( 'fd-accordion-repeater' );

		if ( block_sub_value( 'fd-accordion-body' ) ) {
			$is_active        = block_sub_value( 'fd-accordion-active' ) ? ' active' : '';
			$is_active_height = block_sub_value( 'fd-accordion-active' ) ? ' style="max-height: 3000px"' : ''; // 3000px is is just an magic number to make sure the accordion is open when the page loads.

			echo '<div class="fd-accordion-item">';
			echo '<' . esc_attr__( block_sub_value( 'fd-accordion-header-style' ) ) . ' class="fd-accordion-heading' . esc_attr__( $is_active ) . '" >';
			echo '<button class="fd-accordion-button" type="button">';
			echo wp_kses_post( block_sub_value( 'fd-accordion-header' ) );
			echo '</button>';
			echo '</' . esc_attr__( block_sub_value( 'fd-accordion-header-style' ) ) . '>';

			echo '<div class="fd-accordion-body"' . esc_attr__( $is_active_height ) . '>';
			echo wp_kses_post( block_sub_value( 'fd-accordion-body' ) );
			echo '</div>';
			echo '</div>';
		}
	}
	echo '</div>';
}
reset_block_rows( 'fd-accordion-repeater' );
