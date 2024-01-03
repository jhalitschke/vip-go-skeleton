<?php
/**
 * Template part for displaying ads
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newspack
 */

if ( empty( $ad_config ) ) {
	$ad_config = fdwp_get_ad_config();
}

if ( $ad_config['ads_disabled'] ) {
	return;
}

if ( amp_is_request() ) {
	?>
	<div class="contentAd--amp">
		<div class="ad-marker">Anzeige</div>
		<amp-ad width="336" height="280" type="doubleclick"
				data-slot="/39216077/<?php echo $ad_config['site_name'] ?>/<?php echo $ad_config['zone'] ?>/<?php echo $args['ad_slot'] ?>"
				data-loading-strategy="0.0" data-block-on-consent
				data-multi-size="300x250,336x280"
				data-multi-size-validation="false"
				json='{"targeting":
                {
                    "pos":"<?php echo $args['ad_slot']; ?>",
                    "environment":"amp",
                    "artikeltyp":"news",
                    "loktitel":"<?php echo $ad_config['loktitel'] ?>",
                    "lokverz1":"<?php echo $ad_config['lokverz1'] ?>",
                    "lokverz2":"<?php echo $ad_config['lokverz2'] ?>",
                    "lokverz3":"<?php echo $ad_config['lokverz3'] ?>",
                    "lokseite":"<?php echo $ad_config['lokseite'] ?>",
                    "lokstadt":"<?php echo $ad_config['lokstadt'] ?>",
                    "loknewsid":"<?php echo get_the_ID() ?>",
                    "keyword": <?php echo json_encode( $ad_config['keywords'] ) ?>
                }
            }'
				rtc-config='{
            "urls": [
                "https://funke.amp.permutive.com/rtc?type=doubleclick"
            ],
            "vendors": {
                "prebidrubicon": {"REQUEST_ID": "<?php echo carbon_get_theme_option( 'ads_amp_rubicon_' . $args['ad_slot'] ) ?>"},
                "aps": {"PUB_ID": "3915", "PARAMS": {"amp": "1"}},
                "criteo": {"NETWORK_ID": 9091}
            },"timeoutMillis": 600
            }'>
		</amp-ad>
	</div>
	<?php
} else {
	?>
	<div class="contentAd--<?php echo $args['ad_slot'] . ' ' . $ad_config['environment']; ?>">
		<div id="<?php echo $args['ad_slot']; ?>" class="ad">
			<script>
				Spark.cmd.push(function () {
					Spark.definitionService.displaySlot('<?php echo $args['ad_slot']; ?>');
				});
			</script>
		</div>
	</div>
	<?php
}
?>
