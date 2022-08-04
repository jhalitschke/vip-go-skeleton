<?php
require_once(KALTURA_ROOT . "/view/advertising.php");

KalturaHelpers::protectView( $this );
if (!is_array($this->attrs)) {
	$this->attrs = array();
}
	
// get the embed options from the attributes
$embedOptions = KalturaHelpers::getEmbedOptions( $this->attrs );
//error_log( print_r( array('INFO' => 'EMBEDOPTIONS', 'embedOptions' => $embedOptions), true));

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
$serverUrl		  = str_replace( array('https://','http://') ,'', KalturaHelpers::getServerUrl() );
$serverCdn		  = str_replace( array('https://','http://') ,'', KalturaHelpers::getCdnUrl() );
$allowedPlayers   = KalturaHelpers::getAllowedPlayers();
$playerOptions    = $allowedPlayers[$embedOptions['uiconfid']];


try { //note: get width and height from kmodel
	$kmodel = KalturaModel::getInstance();
	$entry = $kmodel->getEntry( $entryId );
	$ratio = $entry->width / $entry->height;
} catch (Exception $e) {}


$feature_image="";
if ($mode == "feature-video") {
	$feature_image = get_the_post_thumbnail_url( get_the_ID() , 'post-thumbnail' ); 
	$player_pos = "opener";
	if ($feature_image == "") $feature_image = "https://".$serverCdn."/p/".esc_attr( KalturaHelpers::getOption( 'kaltura_partner_id' ) )."/thumbnail/entry_id/".esc_attr($entryId)."/width/1000/height/0";
} else { 
	$feature_image = "https://".$serverCdn."/p/".esc_attr( KalturaHelpers::getOption( 'kaltura_partner_id' ) )."/thumbnail/entry_id/".esc_attr($entryId)."/width/640/height/0";
	$player_pos = "inline";
}

$autoplay = $mode == "feature-video" ? "true" : "false";
$ad_config = fdwp_get_ad_config();
add_filter( 'push_json', 'KalturaHelpers::push_json_ld_to_footer', 10, 1);
apply_filters( 'push_json', $entry);

$articel_url = urlencode(urlencode(esc_url(get_permalink())));
$player_version = "";
if ( strpos( $playerOptions->tags, 'html5studio' ) !== false) { 	
	$player_version = "v2";	//html5studio -> OLD Player via iFrame
	$scriptSrc = KalturaHelpers::getServerUrl() . '/p/' . KalturaHelpers::getOption( 'kaltura_partner_id' ) . '/sp/' . KalturaHelpers::getOption( 'kaltura_partner_id' ) . '00/embedIframeJs/uiconf_id/' . (int)$embedOptions['uiconfid'] . '/partner_id/' . KalturaHelpers::getOption( 'kaltura_partner_id' );
	$scriptCdn = KalturaHelpers::getCdnUrl() . '/p/' . KalturaHelpers::getOption( 'kaltura_partner_id' ) . '/sp/' . KalturaHelpers::getOption( 'kaltura_partner_id' ) . '00/embedIframeJs/uiconf_id/' . (int)$embedOptions['uiconfid'] . '/partner_id/' . KalturaHelpers::getOption( 'kaltura_partner_id' );
}elseif ( strpos( $playerOptions->tags, 'kalturaPlayerJs' ) !== false) { //kalturaPlayerJs -> New Player via Embed
	$player_version = "v7";
	$scriptSrc = KalturaHelpers::getServerUrl() . '/p/' . KalturaHelpers::getOption( 'kaltura_partner_id' ) . '/embedPlaykitJs/uiconf_id/' . (int)$embedOptions['uiconfid'];
	$scriptCdn = KalturaHelpers::getCdnUrl() . '/p/' . KalturaHelpers::getOption( 'kaltura_partner_id' ) . '/embedPlaykitJs/uiconf_id/' . (int)$embedOptions['uiconfid'];
}

if ( amp_is_request() ) { //amp rendering
	$single = is_single();
	$correlation = rand(0,10000000000);
	$ad_url = "//pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/39216077/".esc_attr($ad_config["site_name"])."/".esc_attr($ad_config["zone"])."/preroll&impl=s&correlator=".$correlation."&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&ad_rule=0&url=".$articel_url."&description_url=".$articel_url."&cust_params=ttID%3D".esc_attr( $entryId )."%26artikeltyp%3D".esc_attr($ad_config["type"])."%26pos=preroll%26loknewsid%3D".esc_attr($ad_config["loknewsid"])."%26lokseite%3D".esc_attr($ad_config["lokseite"])."%26loktitel%3D".esc_attr($ad_config["loktitel"])."%26lokverz1%3D".esc_attr($ad_config["lokverz1"])."%26lokverz2%3D".esc_attr($ad_config["lokverz2"])."%26lokverzn%3D".esc_attr($ad_config["lokverz3"])."%26videotags%3D%26environment=".esc_attr($ad_config["environment"])."%26playerHeight%3D".$height."%26playerWidth%3D".$width."%26playerPos%3D".esc_attr($player_pos)."%26playlist%3Dfalse%26playlistId%3D%26paid=false";
	?>
	<amp-iframe
		layout="responsive"
		width="16"
		height="9"
		sandbox="allow-scripts allow-same-origin allow-popups"
		frameborder="0" 
		<?php if ($player_version =="v2"){?>
		src="<?php echo $scriptCdn; ?>?iframeembed=true&entry_id=<?php echo esc_attr($entryId);?>&flashvars[autoPlay]=<?php echo $autoplay;?>&flashvars[mobileAutoPlay]=<?php echo $autoplay;?>&flashvars[doubleClick.disableCompanionAds]=true&flashvars[doubleClick.adTagUrl]=<?php echo urlencode(urlencode($ad_url));?>">
		<?php } elseif ($player_version == "v7"){?>
		src="<?php echo $scriptCdn; ?>?iframeembed=true&entry_id=<?php echo esc_attr($entryId);?>&flashvars[autoPlay]=<?php echo $autoplay;?>&flashvars[mobileAutoPlay]=<?php echo $autoplay;?>&config[plugins]=%7B%2522ima%2522:%7B%2522adTagUrl%2522:%2522<?php echo urlencode(urlencode($ad_url));?>%2522%7D%7D">	 
		<?php }?>
		<amp-img 
			layout="fill" 
			src="<?php echo $feature_image;?>" placeholder>
		</amp-img>
	</amp-iframe> 
	<?php
} else { //normal rendering
	
	global $scripts_set; 
	?> 
	
	<div style="width: 100%;display: inline-block;position: relative;" id="<?php echo esc_attr( $playerId ); ?>_wrapper">
		<div style="margin-top: <?php echo esc_attr($height) ?>%;"></div>

		<div id="<?php echo esc_attr( $playerId ); ?>" style="position: absolute; inset: 0px; overflow: hidden;background-image:url('<?php echo $feature_image;?>');background-size: cover;">
		</div>
		
		<?php if ($scripts_set!=true){?>
			<div id="<?php echo esc_attr($playerId); ?>_close" class="player-fixed--close"></div>
			<script type="text/javascript" id="<?php echo esc_attr( $playerId ); ?>_script" src="<?php echo esc_url( $scriptCdn ); ?>"></script>
		<?php };?>
		<script type="text/javascript">
			<?php 
				if ($scripts_set!=true) echo "var players=[];";
				if ($mode == "feature-video"){
					echo "players.push(['".$playerId."',".$autoplay.",'".esc_attr( $entryId)."','".$player_pos."']);".PHP_EOL;
					//echo get_headerbidding_scripts($ad_config);
				}else {
					//echo "var players=[];";
					//echo "jQuery( document ).ready( function() {";
					//echo "var players=[];";
					echo "players.push(['".$playerId."',".$autoplay.",'".esc_attr( $entryId)."','".$player_pos."']);".PHP_EOL;	
					//echo "initPlayer();";	
					//echo "});";
						
				}
				if ($scripts_set!=true) echo get_headerbidding_scripts();
			?> 
		</script> 
		<?php if ($scripts_set!=true) {?>
			<script type="text/javascript">
				function initPlayers(hb_targets,ads) { 
					players.forEach(function(player,index){
						var correlation = parseInt(Math.random() * (10000000000));
						if (ads == true) {
										var ad_call = '//pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/39216077/<?php echo esc_js($ad_config["site_name"]);?>/<?php echo esc_attr($ad_config["zone"]);?>/preroll&impl=s&correlator='+correlation+'&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&ad_rule=0&url=<?php echo $articel_url;?>&description_url=<?php echo $articel_url;?>&cust_params=ttID%253D<?php echo esc_attr( $entryId ); ?>%2526artikeltyp%253D<?php echo esc_attr($ad_config["type"]);?>%2526pos=preroll%2526loknewsid%253D<?php echo esc_attr($ad_config["loknewsid"]);?>%2526lokseite%253D<?php echo esc_attr($ad_config["lokseite"]);?>%2526loktitel%253D<?php echo esc_attr($ad_config["loktitel"]);?>%2526lokverz1%253D<?php echo esc_attr($ad_config["lokverz1"]);?>%2526lokverz2%253D<?php echo esc_attr($ad_config["lokverz2"]);?>%2526lokverzn%253D<?php echo esc_attr($ad_config["lokverz3"]);?>%2526videotags%253D%2526environment=<?php echo esc_attr($ad_config["environment"]);?>%2526playerHeight%253D<?php echo $height;?>%2526playerWidth%253D<?php echo $width;?>%2526playerPos%253D'+(players[index][3])+'%2526playlist%253Dfalse%2526playlistId%253D%2526paid=false';
										var ad_plugin = true;
									}else {
										var ad_call = '';
										var ad_plugin = false;
									};
						try {
							/*if (typeof pbjs.rp !== "undefined" && typeof pbjs.rp.requestVideoBids === "function"){
								pbjs.rp.requestVideoBids({
									adSlotName: "/39216077/<?php echo esc_js($ad_config["site_name"]);?>/<?php echo esc_js($ad_config["zone"]);?>/preroll",
									adServer: "gam",
									playerSize: [640,480],
									callback: getBidResponse
								});
							};*/
							<?php if ($player_version == "v7"){ ?>
								var kalturaPlayer = KalturaPlayer.setup({
									targetId: players[index][0],
									provider: {
										partnerId: <?php echo esc_js( KalturaHelpers::getOption( 'kaltura_partner_id' ) ); ?>,
										uiConfId: <?php echo esc_js( (int)$embedOptions['uiconfid'] ); ?>,
										env:{
											serviceUrl: "<?php echo KalturaHelpers::getCdnUrl();?>/api_v3",
											statsServiceUrl: "<?php echo KalturaHelpers::getCdnUrl();?>",
											liveStatsServiceUrl: "<?php echo KalturaHelpers::getCdnUrl();?>",
											analyticsServiceUrl: "<?php echo KalturaHelpers::getCdnUrl();?>"
										}
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
											muted:false
										}
									}
									
								});
								if (ads == false) {
									delete KalturaPlayer.plugins.ima;
								};
								kalturaPlayer.loadMedia({entryId: players[index][2]});
							<?php }elseif ($player_version == "v2"){?>
								if (typeof kWidget != 'undefined') {
									
									kWidget.embed({
									'targetId': players[index][0],
									'wid': '_<?php echo esc_js( KalturaHelpers::getOption( 'kaltura_partner_id' ) ); ?>',
									'uiconf_id': '<?php echo esc_js( (int)$embedOptions['uiconfid'] ); ?>',
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
									
								}
							<?php }?>
						} catch (e) {
							console.error(e.message)
						}
				});
			}
			</script>
		<?php $scripts_set=true; }?>

		<?php if ($scripts_set!=true) {?>
			<script type="text/javascript">
			jQuery( document ).ready( function( ) {
				jQuery('figure.post-thumbnail').isInViewport(function (status) {
				if (status === 'entered') {
					jQuery(this).removeClass('player-fixed')
				}

				if (status === 'leaved') {
					jQuery(this).addClass('player-fixed')
				}
				}, 0);	
			}); 
			jQuery('#<?php echo esc_attr($playerId); ?>_close').on("click",function(){
				jQuery('#<?php echo esc_attr($playerId); ?>_wrapper').parent('figure').removeClass('player-fixed');
			});
			</script>
		<?php }?>

		
	</div>
	<?php		
}