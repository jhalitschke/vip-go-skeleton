<?php
// empty here
?>
	<p>Native Advertising Zählpixel</p>
<?php
if ( function_exists( '\Newspack_Sponsors\get_all_sponsors' ) ) {
	$sponsors = \Newspack_Sponsors\get_all_sponsors( get_the_ID(), 'native' );

	if ( ! $sponsors ) {
		echo '<p class="is-error"><span class="dashicons dashicons-warning"></span>'
			 . __( 'Counter-pixel only allowed on sponsored content.' ) . '</p>';
	}
}
