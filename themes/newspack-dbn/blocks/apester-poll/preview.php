<h3>Apester Poll</h3>
<?php
$media_id = trim( block_value( 'media-id' ) );
if ( strlen( $media_id > 0 ) ) {
	echo 'Media ID: ' . esc_html( $media_id );
}
