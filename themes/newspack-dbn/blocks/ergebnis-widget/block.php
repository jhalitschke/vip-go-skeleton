<?php
/**
 * ImTest Fazit Block
 *
 * green
 * 1,0 bis 1,5 = sehr gut
 * 1,6 bis 2,5 = gut
 *
 * yellow
 * 2,6 bis 3,5 = befriedigend
 * 3,6 bis 4,5 = ausreichend
 *
 * red
 * 4,6 bis 5,5 = mangelhaft
 * 5,6 bis 6,0 = ungenügend
 */
$ergebnis_score = (float) ( str_replace( ",", ".", block_value( 'ergebnis-number' ) ) );
$bg_color       = '';

if ( $ergebnis_score <= 1.5 ) {
	$ergebnis_wording = 'sehr gut';
	$bg_color         = 'great';
} elseif ( $ergebnis_score <= 2.5 ) {
	$ergebnis_wording = 'gut';
	$bg_color         = 'great';
} elseif ( $ergebnis_score <= 3.5 ) {
	$ergebnis_wording = 'befriedigend';
	$bg_color         = 'good';
} elseif ( $ergebnis_score <= 4.5 ) {
	$ergebnis_wording = 'ausreichend';
	$bg_color         = 'good';
} elseif ( $ergebnis_score <= 5.5 ) {
	$ergebnis_wording = 'mangelhaft';
	$bg_color         = 'bad';
} else {
	$ergebnis_wording = 'ungenügend';
	$bg_color         = 'bad';
}
?>

<div class="score bg_color-<?php echo $bg_color; ?>">
	<p><?php block_field( 'ergebnis-title' ); ?></p>
	<p><?php echo $ergebnis_wording; ?> <?php block_field( 'ergebnis-number' ); ?></p>
</div>
