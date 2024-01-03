<?php
/**
 * The template for displaying the static front page.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Newspack
 */

get_header();
?>
	<section id="primary" class="content-area">

		<?php
			if (!wp_is_mobile() && !amp_is_request()) {
				get_template_part('template-parts/ads/ad', 'skyscraper', array('ad_slot' => 'sky1'));

				get_template_part( 'template-parts/ads/ad', 'skyscraper', array( 'ad_slot' => 'skyl' ) );

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
				?>

				<header class="entry-header">
					<?php get_template_part( 'template-parts/header/entry', 'header' ); ?>
				</header>

				<?php
				get_template_part( 'template-parts/content/content', 'page' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					newspack_comments_template();
				}

			endwhile; // End of the loop.

			get_template_part( 'template-parts/ads/outbrain', 'outbrain', array( 'context' => 'homepage' ) );
			get_template_part( 'template-parts/ads/taboola', 'taboola', array( 'context' => 'home' ) );

			?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
