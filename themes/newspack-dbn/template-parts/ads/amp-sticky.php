<?php
/**
 * Template part for sticky AMP footer ad
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

?>
<amp-sticky-ad layout="nodisplay">
	<amp-ad width="320" height="100" type="doubleclick"
			data-slot="/39216077/<?php echo $ad_config['site_name'] ?>/<?php echo $ad_config['zone'] ?>/<?php echo $args['ad_slot'] ?>"
			data-loading-strategy="0.0"
			data-multi-size="320x75,320x50,300x100,300x75,300x50"
			data-multi-size-validation="false"
			json='{"targeting":
              {
                  "pos":"<?php echo $args['ad_slot']; ?>",
                  "environment":"amp",
                  "artikeltyp":"news",
                  "loktitel":"<?php echo $ad_config['site_code'] ?>",
                  ""lokverz1":"<?php echo $ad_config['lokverz1'] ?>",
                  "lokverz2":"<?php echo $ad_config['lokverz2'] ?>?>",
                  "lokverz3":"<?php echo $ad_config['lokverz3'] ?>",
                  "loknewsid":"<?php echo get_the_ID() ?>",
                  "lokseite":"<?php echo $ad_config['lokseite'] ?>",
                  "lokstadt":"<?php echo $ad_config['lokstadt'] ?>",
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
        }'></amp-ad>
</amp-sticky-ad>
