<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newspack
 */

// Get sponsors for this taxonomy archive.
if ( function_exists( 'newspack_get_all_sponsors' ) ) {
	$all_sponsors         = newspack_get_all_sponsors(
		get_the_id(),
		null,
		'post',
		[
			'maxwidth'  => 150,
			'maxheight' => 100,
		]
	);
	$native_sponsors      = newspack_get_native_sponsors( $all_sponsors );
	$underwriter_sponsors = newspack_get_underwriter_sponsors( $all_sponsors );
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
	<aside>
			<div class="sticky-container">
				<?php
				if ( ! wp_is_mobile() && ! amp_is_request() ) {
					get_template_part( 'template-parts/ads/ad-inline', 'mr1-single', array(
							'ad_slot'   => 'mr1',
							'ad_size'   => [ 300, 250 ],
							'poscount'  => '0',
							'blockmode' => true
					) );

					get_template_part( 'template-parts/ads/ad-inline', 'mr2-single', array(
							'ad_slot'   => 'mr2',
							'ad_size'   => [ [ 300, 250 ], [ 300, 600 ] ],
							'poscount'  => '1',
							'blockmode' => true
					) );
				}

				if ( ! empty( $underwriter_sponsors ) ) :
					newspack_sponsored_underwriters_info( $underwriter_sponsors );
				endif;

				?>
			</div>
		</aside>

		<article class="article-body">
			<?php
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'newspack' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			?>
		</article>
		<?php
		wp_link_pages(
			array(
				'before' => '<div class="page-links">',
				'after'  => '</div>',
				'next_or_number'   => 'next',
				'previouspagelink' => '<div class="prev">Zurück</div>',
				'nextpagelink' => '<div class="next">Weiter</div>',
			)
		);

		if ( is_active_sidebar( 'article-2' ) && is_single() ) {
			dynamic_sidebar( 'article-2' );
		}
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php newspack_entry_footer(); ?>
	</footer><!-- .entry-footer -->

	<?php
	if ( ! empty( $native_sponsors ) ) :
		newspack_sponsor_footer_bio( $native_sponsors );
	elseif ( ! is_singular( 'attachment' ) ) :
		get_template_part( 'template-parts/post/author', 'bio' );
	endif;
	?>

</article><!-- #post-${ID} -->