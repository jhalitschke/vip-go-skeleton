<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Newspack
 */

get_header();
?>

	<section id="primary" class="content-area">

		<?php
			if (!wp_is_mobile()) {
				get_template_part('template-parts/ads/ad', 'skyscraper', array('ad_slot' => 'sky1'));

				get_template_part('template-parts/ads/ad', 'skyscraper', array('ad_slot' => 'skyl'));

			}
		?>

		<main id="main" class="site-main">
			<?php

			if (! wp_is_mobile() && ! amp_is_request()) {
    			get_template_part('template-parts/ads/pos1-superbanner', 'pos1-superbanner');
			}

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				// Template part for large featured images.
				if ( in_array( newspack_featured_image_position(), array( 'large', 'behind', 'beside' ) ) ) :
					get_template_part( 'template-parts/post/large-featured-image' );
				else :
				?>

					<header class="entry-header">
						<?php get_template_part( 'template-parts/header/entry', 'header' ); ?>
					</header>

				<?php endif; ?>

				<div class="main-content">
					<?php

					// Place smaller featured images inside of 'content' area.
					if ( 'small' === newspack_featured_image_position() ) :
						newspack_post_thumbnail();
					endif;

					get_template_part( 'template-parts/content/content', 'page' );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						newspack_comments_template();
					}
					?>
				</div><!-- .main-content -->

			<?php
			endwhile; // End of the loop.
			get_sidebar();

			if (wp_is_mobile()) {
				get_template_part('template-parts/ads/ad', 'pos3', array('ad_slot' => 'pos3'));
			}

			?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
