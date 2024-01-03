<?php
/**
 * FDL-1087 - Heise Price Widget Block Template.
 */

// Load values and assign defaults.
$partner_id    = block_value( 'partner_id' ) ?: 'funkeone';
$widget_id     = block_value( 'widget_id' ) ?: '';
$widget_type   = block_value( 'type' ) ?: 'price';
$widget_layout = block_value( 'layout' ) ?: 'normal';
// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
?>
	<div
		id="heise_widget"
		class="heise_pricewidget pvg-widget"
		data-partner="<?php echo esc_attr( $partner_id ); ?>"
		data-wid="<?php echo esc_attr( $widget_id ); ?>"
		data-type="<?php echo esc_attr( $widget_type ); ?>"
		data-layout="<?php echo esc_attr( $widget_layout ); ?>"
		data-campaign="{&quot;content&quot;: {&quot;url&quot;: &quot;<?php echo esc_url( get_permalink() ); ?>&quot;}}"
		data-headless
	></div>
	<script type="text/plain" src="https://www.heise.de/assets/pvg/widget.js" class="cmplazyload"
	        data-cmp-vendor="1016"></script>
	</div>
<?php
// phpcs:enable
