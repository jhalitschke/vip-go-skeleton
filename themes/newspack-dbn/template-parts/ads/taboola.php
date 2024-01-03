<?php
/**
 * Template part for displaying Taboola Article Widget*
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

if ( ! function_exists( 'add_taboola_loader' ) ) {
	function add_taboola_loader( $page_type ) {
		global $taboola_loader_inserted;

		if ( $page_type === 'ia' ) {
			$page_type = 'article';
		}
		if ( ! isset( $taboola_loader_inserted ) || $taboola_loader_inserted === false ) {
			?>
			<script type="text/plain" class="cmplazyload" data-cmp-vendor="42">
				window._taboola = window._taboola || [];
				_taboola.push({<?php echo esc_js( $page_type ) ?>:'auto'});
				!function (e, f, u, i) {
					if (!document.getElementById(i)) {
						e.async = 1;
						e.src = u;
						e.id = i;
						f.parentNode.insertBefore(e, f);
					}
				}(document.createElement('script'),
					document.getElementsByTagName('script')[0],
					'//cdn.taboola.com/libtrc/<?php echo esc_js( trim( carbon_get_theme_option( 'ads_taboola_pub_name' ) ) ); ?>/loader.js',
					'tb_loader_script');
				if (window.performance && typeof window.performance.mark == 'function') {
					window.performance.mark('tbl_ic');
				}

			</script>
			<!-- End of Taboola Loader -->
			<?php
		}

		$taboola_loader_inserted = true;
	}
}
$pub_name = '';
if ( carbon_get_theme_option( 'ads_taboola_pub_name' ) ) {
	$pub_name = trim( carbon_get_theme_option( 'ads_taboola_pub_name' ) );
}

global $wp;
$data_src = home_url( $wp->request );

if ( isset( $args ) && ! array_key_exists( 'context', $args ) ) {
	$args['context'] = 'article';
}

$taboola_mode        = trim( carbon_get_theme_option( 'ads_taboola_' . $args['context'] . '_mode' ) );
$taboola_placement   = trim( carbon_get_theme_option( 'ads_taboola_' . $args['context'] . '_placement' ) );
$taboola_target_type = trim( carbon_get_theme_option( 'ads_taboola_' . $args['context'] . '_target_type' ) );

add_taboola_loader( $args['context'] );

if ( amp_is_request() ) {
	if ( carbon_get_theme_option( 'ads_taboola_amp_' . $args['context'] . '_enabled' ) ) {
		?>
		<amp-embed width=100 height=100
				   type="taboola"
				   layout="responsive"
				   data-publisher="<?php echo esc_attr( $pub_name ); ?>"
				   data-mode="<?php echo esc_attr( $taboola_mode ); ?>"
		data-placement=<?php echo esc_attr( $taboola_placement ); ?>"
				   data-target_type="<?php echo esc_attr( $taboola_target_type ); ?>"
		data-article="auto"
		data-url="<?php echo esc_url( $data_src ); ?>">
		</amp-embed>
		<?php
	}
} else {
	if ( carbon_get_theme_option( 'ads_taboola_' . $args['context'] . '_enabled' ) ) {
		?>
		<script type="text/plain" class="cmplazyload" data-cmp-vendor="42">
		  window._taboola = window._taboola || [];
		  _taboola.push({flush: true}); // Partisans using this here, should be on body end.
		  _taboola.push({
			  mode: '<?php echo esc_js( $taboola_mode ); ?>',
			  container: 'taboola-<?php echo esc_js( $args['context'] ); ?>',
			  placement: '<?php echo esc_js( $taboola_placement ); ?>',
			  target_type: '<?php echo esc_js( $taboola_target_type ); ?>'
		  });

		</script>
		<div id="taboola-<?php echo esc_attr( $args['context'] ); ?>"></div>
		<?php
	}
}
?>
