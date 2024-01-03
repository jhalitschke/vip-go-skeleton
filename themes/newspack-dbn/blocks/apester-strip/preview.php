<h3>Apester-Strip</h3>
<?php
$channel_token = trim( carbon_get_theme_option( 'apester_channel_token' ) );

if ( $channel_token !== '' ) {
	echo 'Channel-Token: ' . $channel_token;
} else {
	echo 'Please set Apester Channel-Token in Theme Options/Inline Ads';
}
