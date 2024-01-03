<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Newspack
 */

?>

	<?php do_action( 'before_footer' ); ?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">

		<?php remove_filter( 'get_the_date', 'newspack_convert_to_time_ago', 10, 3 ); ?>
		<?php get_template_part( 'template-parts/footer/footer', 'branding' ); ?>
		<?php get_template_part( 'template-parts/footer/footer', 'widgets' ); ?>

		<div class="site-info">

			<?php get_template_part( 'template-parts/footer/below-footer', 'widgets' ); ?>
			<?php
					$copyright_info   = get_bloginfo( 'name' );
					$custom_copyright = get_theme_mod( 'footer_copyright', '' );
					if ( ! empty( $custom_copyright ) ) {
						$copyright_info = $custom_copyright;
					}
				?>

			<?php if ( ! empty( $copyright_info ) ) :
					// This should be a theme option
				if ( carbon_get_theme_option( 'footer_logo_enabled' ) == '1' ) : ?>
					<a href="https://www.funkemedien.de/" title="FUNKE Mediengruppe" target="_blank">
						<img alt="FUNKE Logo" class="funke-logo"
							 src="<?php echo get_stylesheet_directory_uri() . '/assets/funke_logo_' . carbon_get_theme_option( 'footer_logo_color' ) . '.svg' ?>"/>
					</a>
				<?php endif; ?>
					<span class="copyright">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( $copyright_info ); ?>.</span>
			<?php endif; ?>

		</div><!-- .site-info -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php
	wp_footer();
	if ( ! amp_is_request() && ! wp_is_mobile() && '1' == carbon_get_theme_option( 'ads_show_outofpage' ) ) {
		get_template_part( 'template-parts/ads/ad', 'outofpage', array( 'ad_slot' => 'outofpage' ) );
	} elseif ( amp_is_request() ) {
		get_template_part( 'template-parts/ads/amp-sticky', 'amp-sticky', array( 'ad_slot' => 'footer1' ) );
		get_template_part( 'template-parts/tracking/amp_cookie_sync', 'amp-cookie-sync' );
	}
?>
<!--Version: newspack-dbn - 2022-06-21T11:11:46.529Z - jhalitschke-->
</body>
</html>
