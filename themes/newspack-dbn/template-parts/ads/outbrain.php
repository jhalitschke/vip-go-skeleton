<?php
/**
 * Template part for displaying Outbrain Article Widget*
 * context can be article, category, homepage
 * @see crb_attach_theme_options()
 *
 * @package Newspack
 */

if ( empty( $ad_config ) ) {
	$ad_config = fdwp_get_ad_config();
}

if ( $ad_config['ads_disabled'] ) {
	return;
}


// FDL-864
$script_id = '';
if ( carbon_get_theme_option( 'ads_outbrain_script_id' ) ) {
	$script_id = '?i=' . carbon_get_theme_option( 'ads_outbrain_script_id' );
}

global $wp;
$data_src = home_url( $wp->request );

if ( amp_is_request() ) {
	if ( carbon_get_theme_option( 'ads_outbrain_amp_' . $args['context'] . '_enabled' ) ) {
		?>
		<amp-embed
				width=100 height=100
				type="outbrain"
				layout="responsive"
				data-widgetIds="<?php echo esc_attr( carbon_get_theme_option( 'ads_outbrain_amp_' . $args['context'] . '_widget_id' ) ) ?>"
				data-htmlURL="<?php echo esc_url( $data_src ); ?>"
				data-ampURL="<?php echo esc_url( $data_src ); ?>?amp"
				data-additionalParams="&index=0&testMode=false&originTarget=*"
				data-block-on-consent>
		</amp-embed>
		<?php
	}
} else {
	if ( carbon_get_theme_option( 'ads_outbrain_'.$args['context'].'_enabled' ) ) {
		?>
		<div class="OUTBRAIN"
			 data-src="<?php echo esc_url( $data_src ); ?>"
			 data-widget-id="<?php echo esc_attr( carbon_get_theme_option( 'ads_outbrain_' . $args['context'] . '_widget_id' ) ) ?>"></div>
		<?php
		if ( carbon_get_theme_option( 'cmp_outbrain_enabled' ) ) {
			$script_args['type']        = 'text/plain';
			$script_args['class']       = 'class="cmplazyload"';
			$script_args['data-vendor'] = 'data-cmp-vendor="164"';
			$script_args['data-source'] = 'data-cmp-src';
		} else {
			$script_args['type']        = 'text/javascript';
			$script_args['class']       = '';
			$script_args['data-vendor'] = '';
			$script_args['data-source'] = 'src';
		}

		printf( '<script async type="%s" %s %s
				%s="//widgets.outbrain.com/outbrain.js%s"></script>',
				$script_args['type'],
				$script_args['class'],
				$script_args['data-vendor'],
				$script_args['data-source'],
				esc_url( $script_id )
		);
	}
}

?>

