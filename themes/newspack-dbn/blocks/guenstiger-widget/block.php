<?php
/**
 * FDL-1309 - Günstiger Script Widget (Heise Testtabellen-Widget)
 *
 * Make sure to exclude the script (gt_prodTbl.js) from deferring in the Perfmatters plugin.
 *
 */

$publisherId = block_value( 'publisher_id' ) ?: ''; // default: 364485
$tableId     = block_value( 'table_id' ) ?: ''; // The desired id for the specific table. For testig: 11309
$containerId = "gt_bg_comparisonTable_" . $tableId; // The id of the html element on the page, inside which the widget is to be included.

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
?>
	<div id="<?= esc_attr( $containerId ) ?>"></div>
	<script type="text/plain" class="cmplazyload" data-cmp-vendor="1016">
		gt_prodTbl.render({
			pubId: <?= esc_js( $publisherId ) ?>,
			tableId: <?= esc_js( $tableId ) ?>,
			containerId: "#<?= esc_js( $containerId ) ?>"
	})
	</script>
<?php
// phpcs:enable
