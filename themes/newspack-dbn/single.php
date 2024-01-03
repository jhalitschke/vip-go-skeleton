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

	<section id="primary" class="content-area <?php echo esc_attr( newspack_get_category_tag_classes( get_the_ID() ) ); ?>">
		<main id="main" class="site-main">

			<?php

			if (!wp_is_mobile() && !amp_is_request()) {

				get_template_part('template-parts/ads/ad', 'skyscraper', array('ad_slot' => 'sky1'));

				get_template_part( 'template-parts/ads/ad', 'skyscraper', array( 'ad_slot' => 'skyl' ) );

				get_template_part( 'template-parts/ads/pos1-superbanner', 'pos1-superbanner' );

			}


			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				// Template part for large featured images && featured video
				if ( in_array( newspack_featured_image_position(), array( 'large', 'behind', 'beside', 'above' ) ) || get_post_meta( get_the_ID(), '_fd_featured_video_source', true )  != 'disabled' )  :
					get_template_part( 'template-parts/post/large-featured-image' );
				else :
				?>
					<header class="entry-header">
						<?php get_template_part( 'template-parts/header/entry', 'header' ); ?>
					</header>

				<?php endif; ?>

                <div class="main-content">

                    <?php
					if ( is_active_sidebar( 'article-1' ) ) {
						dynamic_sidebar( 'article-1' );
					}

					// Place smaller featured images inside of 'content' area.
					if ( 'small' === newspack_featured_image_position() && ((get_post_meta( get_the_ID(), '_fd_featured_video_source', true )  == 'disabled')) ) :
						newspack_post_thumbnail();
					endif;

					// insert mob/amp pos1 below featured image
					if ( wp_is_mobile() || amp_is_request() ) {
						get_template_part( 'template-parts/ads/pos1-superbanner', 'pos1-superbanner' );
					}

					get_template_part( 'template-parts/content/content-single', 'single' );

					newspack_previous_next();

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						newspack_comments_template();
					}

					get_template_part( 'template-parts/ads/outbrain', 'outbrain', array( 'context' => 'article' ) );
					get_template_part( 'template-parts/ads/taboola', 'taboola', array( 'context' => 'article' ) );

					?>
                </div><!-- .main-content -->
			<?php
				endwhile;
				get_sidebar();
			?>

    </main><!-- #main -->
</section><!-- #primary -->

<?php
get_footer();
