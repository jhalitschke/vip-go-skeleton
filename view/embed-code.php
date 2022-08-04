<?php
require_once(KALTURA_ROOT . "/view/advertising.php");

KalturaHelpers::protectView( $this );
if (!is_array($this->attrs)) {
	$this->attrs = array();
}

try { //note: get width and height from kmodel
// get the embed options from the attributes
$embedOptions = KalturaHelpers::getEmbedOptions( $this->attrs );

$wid              = $embedOptions['wid'] ? $embedOptions['wid'] : '_' . KalturaHelpers::getOption( 'kaltura_partner_id' );
$uiconfId         = $embedOptions['uiconfid'];
$entryId          = $embedOptions['entryId'];
$mode          	  = $embedOptions['mode'];
$width            = $embedOptions['width'];
$height           = $embedOptions['height'];
$isResponsive     = !empty($embedOptions['responsive']) && $embedOptions['responsive'];
$hoveringControls = !empty($embedOptions['hoveringControls']) && $embedOptions['hoveringControls'];
$randId           = $embedOptions['randId'];
$divId            = 'kaltura_wrapper_' . $randId;
$thumbnailDivId   = 'kaltura_thumbnail_' . $randId;
$playerId         = 'kaltura_player_' . $randId;
$flashVars        = $embedOptions['flashVars'];
$isPlaylist       = $embedOptions['isPlaylist'];
$serverUrl		  = KalturaHelpers::getServerUrl();
$serverCdn		  = KalturaHelpers::getCdnUrl();
$allowedPlayers   = KalturaHelpers::getAllowedPlayers();
$playerOptions    = $allowedPlayers[$embedOptions['uiconfid']];
$partnerID = KalturaHelpers::getOption( 'kaltura_partner_id' );
$kmodel = KalturaModel::getInstance();
$entry = $kmodel->getEntry( $entryId );
$ratio = $entry->width / $entry->height;
} catch (Exception $e) {}


$feature_image="";
if ($mode == "feature-video") {
	$feature_image = get_the_post_thumbnail_url( get_the_ID() , 'post-thumbnail' );
	$player_pos = "opener";
	if ($feature_image == "") $feature_image = $serverCdn."/p/".esc_attr( $partnerID )."/thumbnail/entry_id/".esc_attr($entryId)."/width/1000/height/0";
} else {
	$feature_image = $serverCdn."/p/".esc_attr( $partnerID )."/thumbnail/entry_id/".esc_attr( $entryId )."/width/640/height/0";
	$player_pos = "inline";
}

//overwrite the thumbnail, description, titel
if (is_object($entry)){
	if ($mode == "feature-video"){
		$entry->thumbnailUrl = $feature_image;
		$video_desc = get_post_meta( get_the_ID(), '_fd_featured_video_caption', true );
		if ($video_desc !== "") {
			$entry->description = $video_desc;
		};
		$video_title = get_post_meta( get_the_ID(), '_fd_featured_video_title', true );
		if ($video_title !== ""){
			$entry->name = $video_title;
		};
	};
	add_filter( 'push_json', 'KalturaHelpers::push_json_ld_to_footer', 10, 1);
	apply_filters( 'push_json', $entry);
}

$autoplay = $mode == "feature-video" ? "true" : "false";
$ad_config = fdwp_get_ad_config();
$ad_config["loknewsid"] = get_the_ID();

$articel_url = urlencode(get_permalink());
$is_mobile = false;
if (function_exists('wp_is_mobile')){
	if (wp_is_mobile()) {
		$is_mobile = true;
	}
} else {
	if (is_mobile()) {
		$is_mobile = true;
	}
}

if (amp_is_request() || fdwp_amp_is_request() || $is_mobile){
	$width_ad            = "373";
	$height_ad           = "210";
}else{
	$width_ad            = "1000";
	$height_ad           = "562";
}

$player_version = "";
if ( strpos( $playerOptions->tags, 'html5studio' ) !== false) {
	$player_version = "v2";	//html5studio -> OLD Player via iFrame
	$scriptSrc = $serverUrl . '/p/' . esc_attr($partnerID) . '/sp/' . esc_attr($partnerID) . '00/embedIframeJs/uiconf_id/' . esc_attr((int)$uiconfId) . '/partner_id/' . esc_attr($partnerID);
	$scriptCdn = $serverCdn . '/p/' . esc_attr($partnerID) . '/sp/' . esc_attr($partnerID) . '00/embedIframeJs/uiconf_id/' . esc_attr((int)$uiconfId) . '/partner_id/' . esc_attr($partnerID);
}elseif ( strpos( $playerOptions->tags, 'kalturaPlayerJs' ) !== false) { //kalturaPlayerJs -> New Player via Embed
	$player_version = "v7";
	$scriptSrc = $serverUrl . '/p/' . esc_attr($partnerID) . '/embedPlaykitJs/uiconf_id/' . (int)$uiconfId;
	$scriptCdn = $serverCdn . '/p/' . esc_attr($partnerID) . '/embedPlaykitJs/uiconf_id/' . (int)$uiconfId;
}

if ( amp_is_request() || fdwp_amp_is_request()) { //amp rendering
	$single = is_single();
	$ad_url = "https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/39216077/".esc_attr($ad_config["site_name"])."/".esc_attr($ad_config["zone"])."/preroll&impl=s&correlator=\'correlation\'&gdfp_req=1&env=vp&ad_rule=0&output=vast&unviewed_position_start=1&url=".$articel_url."&description_url=".$articel_url."&cust_params=ttID%3D".esc_attr( $entryId )."%26artikeltyp%3D".esc_attr($ad_config["type"])."%26pos%3Dpreroll%26loknewsid%3D".esc_attr($ad_config["loknewsid"])."%26lokseite%3D".esc_attr($ad_config["lokseite"])."%26loktitel%3D".esc_attr($ad_config["loktitel"])."%26lokverz1%3D".esc_attr($ad_config["lokverz1"])."%26lokverz2%3D".esc_attr($ad_config["lokverz2"])."%26lokverzn%3D".esc_attr($ad_config["lokverz3"])."%26videotags%3D%26environment%3D".esc_attr($ad_config["environment"])."%26playerHeight%3D".esc_attr($height_ad)."%26playerWidth%3D".esc_attr($width_ad)."%26playerPos%3D".esc_attr($player_pos)."%26playlist%3Dfalse%26playlistId%3D%26paid%3Dfalse";
	$scriptCdn = $serverCdn . '/p/' . esc_attr($partnerID) . '/sp/' . esc_attr($partnerID) . '00/embedIframeJs/uiconf_id/23464665/partner_id/' . esc_attr($partnerID);
	$player_version="v2";
	?>
	<script id="random-number-script" type="text/plain" target="amp-script">
		const correlation = parseInt(Math.random() * (1000000000));
	</script>
	<amp-iframe data-block-on-consent
		layout="responsive"
		width="16"
		height="9"
		sandbox="allow-scripts allow-same-origin allow-popups"
		frameborder="0"
		<?php if ($player_version =="v2"){?>
		src="<?php echo esc_url($scriptCdn); ?>?iframeembed=true&playerId=kaltura_player_fwid5&entry_id=<?php echo esc_attr($entryId);?>&flashvars[autoPlay]=<?php echo esc_attr($autoplay);?>&flashvars[mobileAutoPlay]=<?php echo esc_attr($autoplay);?>&flashvars[doubleClick.disableCompanionAds]=true&flashvars[doubleClick.adTagUrl]=<?php echo urlencode($ad_url);?>">
		<?php } elseif ($player_version == "v7"){?>
		src="<?php echo esc_url($scriptCdn); ?>?iframeembed=true&entry_id=<?php echo esc_attr($entryId);?>&flashvars[autoPlay]=<?php echo $autoplay;?>&flashvars[mobileAutoPlay]=<?php echo $autoplay;?>&config[plugins.ima]={'ima':'adTagUrl':'<?php echo urlencode($ad_url);?>'}">
		<?php }?>
		<amp-img
			layout="fill"
			src="<?php echo esc_url($feature_image);?>" placeholder>
		</amp-img>
	</amp-iframe>

	<?php
} else { //normal rendering

	global $scripts_set;
	?>

	<div style="width: 100%;display: inline-block;position: relative;" id="<?php echo esc_attr( $playerId ); ?>_wrapper">
		<div style="margin-top: <?php echo esc_attr($height) ?>%;"></div>

		<div id="<?php echo esc_attr( $playerId ); ?>" style="position: absolute; inset: 0px; overflow: hidden;background-image:url('<?php echo esc_url($feature_image);?>');background-size: cover;">
		</div>

		<?php if ($scripts_set!=true){?>
			<div id="<?php echo esc_attr($playerId); ?>_close" class="player-fixed--close"></div>
			<script type="text/javascript" id="<?php echo esc_attr( $playerId ); ?>_script" src="<?php echo esc_url( $scriptCdn ); ?>"></script>
		<?php };?>
		<script type="text/javascript">
			<?php
				if ($scripts_set!=true) echo "var players=[];";
				if ($mode == "feature-video"){
					echo "players.push(['".esc_attr($playerId)."',".esc_attr($autoplay).",'".esc_attr( $entryId)."','".esc_attr($player_pos)."']);".PHP_EOL;
				}else {
					echo "players.push(['".esc_attr($playerId)."',".esc_attr($autoplay).",'".esc_attr( $entryId)."','".esc_attr($player_pos)."']);".PHP_EOL;
				};
			?>
		</script>
		<?php if ($scripts_set!=true) {?>
			<script type="text/javascript">
				function initPlayers(hb_targets,ads) {
					players.forEach(function(player,index){
						var correlation = parseInt(Math.random() * (10000000000));
						if (ads == true) {
										var ad_call = '//pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/39216077/<?php echo esc_js($ad_config["site_name"]);?>/<?php echo esc_js($ad_config["zone"]);?>/preroll&impl=s&correlator='+correlation+'&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&ad_rule=0&url=<?php echo esc_url($articel_url);?>&description_url=<?php echo esc_url($articel_url);?>&cust_params=ttID%3D'+players[index][2]+'%26artikeltyp%3D<?php echo esc_attr($ad_config["type"]);?>%26pos%3Dpreroll%26loknewsid%3D<?php echo esc_attr($ad_config["loknewsid"]);?>%26lokseite%3D<?php echo esc_attr($ad_config["lokseite"]);?>%26loktitel%3D<?php echo esc_attr($ad_config["loktitel"]);?>%26lokverz1%3D<?php echo esc_attr($ad_config["lokverz1"]);?>%26lokverz2%3D<?php echo esc_attr($ad_config["lokverz2"]);?>%26lokverzn%3D<?php echo esc_attr($ad_config["lokverz3"]);?>%26videotags%3D%26environment%3D<?php echo esc_attr($ad_config["environment"]);?>%26playerHeight%3D<?php echo esc_attr($height_ad);?>%26playerWidth%3D<?php echo esc_attr($width_ad);?>%26playerPos%3D'+(players[index][3])+'%26playlist%3Dfalse%26playlistId%3D%26paid%3Dfalse';
										var ad_plugin = true;
									}else {
										var ad_call = '';
										var ad_plugin = false;
									};
						try {
							<?php if ($player_version == "v7"){ ?>
								var kalturaPlayer_object = {
									targetId: players[index][0],
									provider: {
										partnerId: <?php echo esc_js( $partnerID ); ?>,
										uiConfId: <?php echo esc_js( (int)$uiconfId ); ?>
									},
									plugins: {
										ima: {
											adTagUrl: ad_call,
											vpaidMode: 'ENABLED'
										}
									},
									player: {
										playback:{
											autoplay: players[index][1],
											preload: "auto",
											muted:true
										}
									}

								};
								if (ads === false) {
									delete kalturaPlayer_object.plugins.ima;
								};
								var kalturaPlayer= KalturaPlayer.setup(kalturaPlayer_object);
								if (kalturaPlayer.loadMedia({entryId: players[index][2]})){
									jQuery('.post-thumbnail figcaption').hide();
									jQuery('.post-thumbnail .video-wrapper figcaption').show();
								};
							<?php }elseif ($player_version === "v2"){?>
								if (typeof kWidget != 'undefined') {

									kWidget.embed({
									'targetId': players[index][0],
									'wid': '_<?php echo esc_js( $partnerID ); ?>',
									'uiconf_id': '<?php echo esc_js( (int)$uiconfId ); ?>',
									'entry_id': players[index][2],
									'flashvars':{
										'autoPlay': players[index][1],
										'doubleClick': {
											'plugin': ad_plugin,
											'adTagUrl': ad_call,
											'leadWithFlash': false,
											'disableCompanionAds': true,
											'htmlCompanions': ''
										}
									}
									});
									jQuery('.post-thumbnail figcaption').hide();
									jQuery('.post-thumbnail .video-wrapper figcaption').show();
								}
							<?php }?>
						} catch (e) {
							console.error(e.message)
						}
				});
			};
			<?php echo get_headerbidding_scripts();?>
			jQuery( document ).ready( function( ) {
				videoWrapper = jQuery('.video-wrapper');
				videoFrame = jQuery('.video-frame');
				videoHeight = videoWrapper.outerHeight();
					jQuery('figure.post-thumbnail').isInViewport(function (status) {
					if (status === 'entered') {
						jQuery(this).removeClass('player-fixed');
						videoWrapper.height('auto');
					}

					if (status === 'leaved') {
						jQuery(this).addClass('player-fixed');
						videoWrapper.height(videoHeight);
					}
					}, 0);
				});
				jQuery('#<?php echo esc_attr($playerId); ?>_close').on("click",function(){
				jQuery('#<?php echo esc_attr($playerId); ?>_wrapper').parents('figure').removeClass('player-fixed');
				});
			</script>
		<?php $scripts_set=true; }?>

		<?php if ($scripts_set!=true) {?>
			<script type="text/javascript">

			</script>
		<?php }?>


	</div>
	<?php
}
