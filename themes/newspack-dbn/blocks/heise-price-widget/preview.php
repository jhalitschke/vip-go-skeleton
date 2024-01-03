<?php
$partner_id    = block_value( 'partner_id' ) ?: 'funkeone';
$widget_id     = block_value( 'widget_id' ) ?: '';
$widget_type   = block_value( 'type' ) ?: 'price';
$widget_layout = block_value( 'layout' ) ?: 'normal';
?>
<p>
	<strong>Heise Price Widget</strong><br/>
	Partner-ID: <?php echo esc_html( $partner_id ); ?>
	Widget-ID: <?php echo esc_html( $widget_id ); ?>
	Type: <?php echo esc_html( $widget_type ); ?>
	Layout: <?php echo esc_html( $widget_layout ); ?>
</p>

