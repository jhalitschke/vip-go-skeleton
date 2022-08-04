<?php
/*
Plugin Name: Kaltura Video
Plugin URI: http://www.kaltura.org/
Description: This is not just another video embed tool - it includes every functionality you might need for video and rich-media, including playing and uploading. Adopted on 2.8-rc4.
Version: 3.1.2
Author: Funke Digital, Christian Storm
Author URI: https://funkedigital.de
*/

define( 'KALTURA_PLUGIN_FILE', __FILE__ );
define( 'KALTURA_ROOT', dirname( __FILE__ ) );
define( 'KALTURA_PLUGIN_VERSION', '3.0' );

require_once( KALTURA_ROOT . '/lib/Kaltura/Autoloader.php' );

$kalturaAutoloader = new Kaltura_Autoloader( plugin_dir_path( __FILE__ ) );
$kalturaAutoloader->register();    

$kalturaPlugin = new Kaltura_AllInOneVideoPackPlugin();
$kalturaPlugin->init();
