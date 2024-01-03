<?php
// FDL-1309 - Günstiger Script Widget (Heise Testtabellen-Widget)
$publisherId = block_value( 'publisher_id' ) ?: '';
$tableId     = block_value( 'table_id' ) ?: '';
?>
<p>
	<strong>Heise Testtabellen-Widget</strong><br>
	Publisher-ID: <?= esc_html( $publisherId ); ?> -
	Tabellen-ID: <?= esc_html( $tableId ); ?>
</p>
