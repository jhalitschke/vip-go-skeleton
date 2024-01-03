<?php
/**
 * Template part for IVW/Infonline Tracking
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @link https://www.infonline.de/support/
 *
 * @package Newspack
 */

$ivw = fdwp_get_ivw_config();

if ( amp_is_request() ) {
	?>
	<amp-analytics type="infonline" id="infonline" data-block-on-consent>
		<script type="application/json">
			{ "vars":
				{ "st": "<?php echo esc_js( $ivw['kennung'] ); ?>",
				  "co": "",
				  "cp": "<?php echo esc_js( $ivw['code'] ); ?>"
			    },
				  "requests": {
				    "url": "<?php echo esc_js( $ivw['url'] ); ?>"
		          }
		    }

		</script>
	</amp-analytics>
	<?php
	if ( $ivw['data_domain'] ) {
		?>
		<amp-analytics type="infonline_base">
			<script type="application/json">
				{
					"vars": {
						"st": "<?php echo esc_js( $ivw['kennung'] ); ?>",
						"cp": "<?php echo esc_js( $ivw['code'] ); ?>",
						"dn": "<?php echo esc_js( $ivw['data_domain'] ); ?>"
					},
					"requests": {
						"url": "https://<?php echo esc_js( $ivw['data_domain'] ); ?>/iomb/latest/html/amp.html"
					}
				}

			</script>
		</amp-analytics>
		<?php
	}
} else {
	if ( ! $ivw['iomm_verified'] ) {
		?>
		<script type="text/javascript" src="https://script.ioam.de/iam.js" data-cmp-ab="1"></script>
		<!-- SZM VERSION="2.0" -->
		<script type="text/javascript">
			var iam_data = {
				"st": "<?php echo esc_js( $ivw['kennung'] ); ?>",
				"cp": "<?php echo esc_js( $ivw['code'] ); ?>",
				"sv": "<?php echo esc_js( $ivw['frabo'] ); ?>",
				"co": "",
				"sc": "yes"
			}
			iom.c(iam_data, 1);
		</script>
		<!--/SZM -->
		<?php
	}

	if ( $ivw['data_domain'] ) {

		if ( $ivw['iomm_verified'] ) {
			printf( '
			<script type="text/javascript">
			  IOMm("configure", { st: "%s", dn: "%s", mh:5 });
			</script>', $ivw['kennung'], $ivw['data_domain'] );
		} else {
			printf( '
			<script type="text/javascript">
			  IOMm("configure", { st: "%s", dn: "%s" });
			</script>', $ivw['kennung'], $ivw['data_domain'] );
		}

		printf( '
		<script type="text/javascript">
		  IOMm("pageview", { cp: "%s" });
		</script>', $ivw['code'] );

		if ( $ivw['qds'] ) {
			printf( '
			<script type="text/javascript">
			  IOMm("3p", "qds", "%s");
			</script>', $ivw['qds'] );
		}

	}
}
