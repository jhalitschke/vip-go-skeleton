<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newspack
 */

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php

			if (! wp_is_mobile() && ! amp_is_request()) {
    			get_template_part('template-parts/ads/pos1-superbanner', 'pos1-superbanner');
			}

			if (have_posts()) {

				// Load posts loop.
				while (have_posts()) {
					the_post();
					get_template_part('template-parts/content/content', 'excerpt');
				}

				// Previous/next page navigation.
				newspack_the_posts_navigation();

			} else {

				// If no content, include the "No posts found" template.
				get_template_part('template-parts/content/content', 'none');

			}
			?>

		</main><!-- .site-main -->
		<?php
			get_sidebar();

			if (!wp_is_mobile()) {
				get_template_part('template-parts/ads/ad', 'skyscraper', array('ad_slot' => 'sky1'));
			} else {
				get_template_part('template-parts/ads/ad', 'pos3', array('ad_slot' => 'pos3'));
			}
		?>

	</section><!-- .content-area -->

<?php
get_footer();
