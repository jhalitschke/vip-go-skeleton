<?php
/**
 * Template part for superbanner / pos1
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

if ( ! wp_is_mobile() && ! amp_is_request() && get_option( '_ads_show_sb1' ) == '1' ) {
	get_template_part( 'template-parts/ads/ad', 'superbanner', array( 'ad_slot' => 'sb1' ) );
} elseif ( wp_is_mobile() && get_option( '_ads_show_pos1' ) == '1' ) {
	get_template_part( 'template-parts/ads/ad', 'pos1', array( 'ad_slot' => 'pos1' ) );
} elseif ( amp_is_request() && get_option( '_ads_show_pos1_amp' ) == '1' ) {
	get_template_part( 'template-parts/ads/ad', 'pos1-amp', array( 'ad_slot' => 'pos1' ) );
}
