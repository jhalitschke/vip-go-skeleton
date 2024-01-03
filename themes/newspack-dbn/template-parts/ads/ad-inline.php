<?php
/**
 * Template part for displaying inline ads
 *
 * @see add_inline_ads()
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
	if ( $args['flying_carpet'] === true ) {
		?>
		<amp-fx-flying-carpet height="450px">
			<amp-ad width="336" height="600" layout="fixed" type="doubleclick"
					data-slot="/39216077/<?php echo $ad_config['site_name'] ?>/<?php echo $ad_config['zone'] ?>/<?php echo $args['ad_slot'] ?>"
					data-multi-size="300x250,300x600,320x480,336x280" data-multi-size-validation="false"
					json='{"targeting":
              {
                  "pos":"<?php echo $args['ad_slot']; ?>",
                  "environment":"amp",
                  "artikeltyp":"news",
                  "loktitel":"<?php echo $ad_config['loktitel'] ?>",
                  "lokverz1":"<?php echo $ad_config['lokverz1'] ?>",
                  "lokverz2":"<?php echo array_key_exists( 'lokverz2', $ad_config ) ? $ad_config['lokverz2'] : ''; // TODO correct? ?>",
                  "lokverz3":"<?php echo array_key_exists( 'lokverz3', $ad_config ) ? $ad_config['lokverz3'] : ''; // TODO correct? ?>",
                  "lokseite":"<?php echo array_key_exists( 'lokseite', $ad_config ) ? $ad_config['lokseite'] : ''; // TODO correct? ?>",
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
            "prebidrubicon": {"REQUEST_ID": "<?php echo $args['rubicon_id'] ?>"},
            "aps": {"PUB_ID": "3915", "PARAMS": {"amp": "1"}},
            "criteo": {"NETWORK_ID": 9091}
        },"timeoutMillis": 600
        }'></amp-ad>
		</amp-fx-flying-carpet>
		<?php
	} elseif ( $args['ob_ia'] === true ) {
		?>
		<amp-embed width="100" height="10" type="outbrain" layout="responsive"
				   data-widgetids="<?php echo $args['ob_widget_id']; ?>"
				   data-htmlURL="<?php echo get_permalink(); ?>"
				   data-ampURL="<?php echo get_permalink(); ?>?amp"
				   data-additionalparams="&index=0&testMode=false&originTarget=*"
				   data-block-on-consent class="i-amphtml-layout-responsive i-amphtml-layout-size-defined"
				   i-amphtml-layout="responsive">
			<i-amphtml-sizer style="display:block;padding-top:10%"></i-amphtml-sizer>
		</amp-embed>
		<?php
	} else {
		?>
		<div class="contentAd--inline-amp">
			<div class="ad-marker">Anzeige</div>
			<amp-ad width="336" height="280" type="doubleclick"
					data-slot="/39216077/<?php echo $ad_config['site_name'] ?>/<?php echo $ad_config['zone'] ?>/<?php echo $args['ad_slot'] ?>"
					data-loading-strategy="0.0" data-block-on-consent
					data-multi-size="300x250,336x280"
					data-multi-size-validation="false"
					json='{"targeting":
              {
                  "pos":"<?php echo $args['ad_slot']; ?>",
                  "poscount": "<?php echo $args['poscount']; ?>",
                  "environment":"amp",
                  "artikeltyp":"news",
                  "loktitel":"<?php echo $ad_config['loktitel'] ?>",
                  "lokverz1":"<?php echo $ad_config['lokverz1'] ?>",
                  "lokverz2":"<?php echo array_key_exists( 'lokverz2', $ad_config ) ? $ad_config['lokverz2'] : ''; // TODO correct? ?>",
                  "lokverz3":"<?php echo array_key_exists( 'lokverz3', $ad_config ) ? $ad_config['lokverz3'] : ''; // TODO correct? ?>",
                  "lokseite":"<?php echo array_key_exists( 'lokseite', $ad_config ) ? $ad_config['lokseite'] : ''; // TODO correct? ?>",
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
            "prebidrubicon": {"REQUEST_ID": "<?php echo $args['rubicon_id'] ?>"},
            "aps": {"PUB_ID": "3915", "PARAMS": {"amp": "1"}},
            "criteo": {"NETWORK_ID": 9091}
        },"timeoutMillis": 600
        }'>
			</amp-ad>
		</div>
		<?php
	}
} else {
	if ( array_key_exists( 'ob_ia', $args ) && $args['ob_ia'] ) {
		?>
		<div class="OUTBRAIN" data-src="<?php echo get_permalink(); ?>"
			 data-widget-id="<?php echo $args['ob_widget_id']; ?>"
			 data-ob-template="<?php echo $args['ob_template']; ?>"></div>
		<?php
	} elseif ( array_key_exists( 'opinary_ia', $args ) && $args['opinary_ia'] ) {
		?>
		<div id="opinary-automation-placeholder"></div>
		<?php
		fdwp_insert_opinary();
		?>
		<?php
	} elseif ( array_key_exists( 'taboola_ia', $args ) && $args['taboola_ia'] ) {
		get_template_part( 'template-parts/ads/taboola', 'taboola-ia', array( 'context' => 'ia' ) );
	} else {

		// ensure the static oustream position name 'po1', regardless the poscount logic
		if ( array_key_exists( 'outstream_ia', $args ) && $args['outstream_ia'] === true ) {
			$args['ad_slot'] = 'po1';
		}

		?>
		<div id="<?php echo $args['ad_slot'] . '-' . $args['poscount']; ?>"
			 class="ad <?php if ( array_key_exists( 'blockmode', $args ) && ! $args['blockmode'] ) { // TODO correct? ?> ad-inline <?php } ?> <?php echo $ad_config['environment']; ?>"></div>
		<script>
			Spark.cmd.push(function () {
				Spark.definitionService.defineSlot(
					{
						name: '<?php echo $args['ad_slot']; ?>',
						id: '<?php echo $args['ad_slot'] . '-' . $args['poscount']; ?>',
						sizes: <?php echo ( is_array( $args['ad_size'] ) ) ? json_encode( $args['ad_size'] ) : $args['ad_size']; ?>,
						targetings: {
							pos: '<?php echo $args['ad_slot']; ?>',
							poscount: '<?php echo $args['poscount']; ?>'
						}
					}
				);
				Spark.definitionService.displaySlot('<?php echo $args['ad_slot'] . '-' . $args['poscount']; ?>');
			});
		</script>
		<?php
	}
}
?>
