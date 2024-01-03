<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newspack
 */
get_header();

// Get sponsors for this taxonomy archive.
if ( function_exists( 'newspack_get_all_sponsors' ) ) {
	$all_sponsors         = newspack_get_all_sponsors( get_queried_object_id() );
	$native_sponsors      = newspack_get_native_sponsors( $all_sponsors );
	$underwriter_sponsors = newspack_get_underwriter_sponsors( $all_sponsors );
}
?>

	<section id="primary" class="content-area">
		<?php
		if ( ! wp_is_mobile() && ! amp_is_request() ) {

			get_template_part( 'template-parts/ads/ad', 'skyscraper', array( 'ad_slot' => 'sky1' ) );

			get_template_part( 'template-parts/ads/ad', 'skyscraper', array( 'ad_slot' => 'skyl' ) );

			get_template_part( 'template-parts/ads/pos1-superbanner', 'pos1-superbanner' );

			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_is_mobile_wp_is_mobile
		} elseif ( ! carbon_get_theme_option( 'ads_loop_enabled' ) && ( wp_is_mobile() || amp_is_request() ) ) {

			get_template_part( 'template-parts/ads/pos1-superbanner', 'pos1-superbanner' );

		}
		?>
		<?php if ( is_author() ) : ?>
			<header class="page-header">
				<?php
				if ( is_author() ) {

					$queried       = get_queried_object();
					$author_avatar = '';

					if ( function_exists( 'coauthors_posts_links' ) ) {
						// Check if this is a guest author post type.
						if ( 'guest-author' === get_post_type( $queried->{'ID'} ) ) {
							// If yes, make sure the author actually has an avatar set; otherwise, coauthors_get_avatar returns a featured image.
							if ( get_post_thumbnail_id( $queried->{'ID'} ) ) {
								$author_avatar = coauthors_get_avatar( $queried, 300 );
							} else {
								// If there is no avatar, force it to return the current fallback image.
								$author_avatar = get_avatar( ' ' );
							}
						} else {
							$author_avatar = coauthors_get_avatar( $queried, 300 );
						}
					} else {
						$author_id     = get_query_var( 'author' );
						$author_avatar = get_avatar( $author_id, 300 );
					}

					if ( $author_avatar ) {
						echo wp_kses( $author_avatar, newspack_sanitize_avatars() );
					}
				}
				?>
				<div class="<?php if( is_author() ) { echo 'author-description'; } ?>">

					<?php
					if ( ( is_category() || is_tag() ) && ! empty( $native_sponsors ) ) {
						// Get label for native archive sponsors.
						newspack_sponsor_label( $native_sponsors, null, true );
					}
					?>

					<?php the_archive_title( '<h2 class="page-title">', '</h2>' ); ?>

					<?php do_action( 'newspack_theme_below_archive_title' ); ?>

					<?php
					if ( ( is_category() || is_tag() ) && ! empty( $native_sponsors ) ) {
						// Get description for native archive sponsors.
						newspack_sponsor_archive_description( $native_sponsors );
					}
					if ( is_author() ) { ?>
						<div class="taxonomy-description">
							<?php echo wp_kses_post( wpautop( get_the_archive_description() ) ); ?>
						</div>
					<?php }

					if ( ( is_category() || is_tag() ) && ! empty( $underwriter_sponsors ) ) {
						// Get info for underwriter archive sponsors.
						newspack_sponsored_underwriters_info( $underwriter_sponsors );
					}
					?>

					<?php if ( is_author() ) : ?>
						<div class="author-meta">
							<?php
							$author_email = get_the_author_meta( 'user_email', get_query_var( 'author' ) );
							if ( true === get_theme_mod( 'show_author_email', false ) && '' !== $author_email ) :
								?>
								<a class="author-email" href="<?php echo 'mailto:' . esc_attr( $author_email ); ?>">
									<?php echo wp_kses( newspack_get_social_icon_svg( 'mail', 18 ), newspack_sanitize_svgs() ); ?>
									<?php echo esc_html( $author_email ); ?>
								</a>
							<?php endif; ?>

							<?php newspack_author_social_links( get_the_author_meta( 'ID' ), 20 ); ?>
						</div><!-- .author-meta -->

						<?php do_action( 'newspack_theme_below_author_archive_meta' ); ?>

					<?php endif; ?>
				</div>

			</header><!-- .page-header -->
		<?php endif; ?>

		<?php do_action( 'before_archive_posts' ); ?>

		<main id="main" class="site-main">

			<?php
			if ( have_posts() ) :
				$post_count = 0;
				?>

				<?php
				// Start the Loop.
				while ( have_posts() ) :
					$post_count++;
					the_post();

					if ( 1 === $post_count || true === get_theme_mod( 'archive_show_excerpt', false ) ) {
						get_template_part( 'template-parts/content/content', 'excerpt' );
					} else {
						get_template_part( 'template-parts/content/content', 'archive' );
					}

					// End the loop.
				endwhile;

				// Previous/next page navigation.
				newspack_the_posts_navigation();

			// If no content, include the "No posts found" template.
			else :
				get_template_part( 'template-parts/content/content', 'none' );

			endif;

			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_is_mobile_wp_is_mobile
			if ( ! carbon_get_theme_option( 'ads_loop_enabled' ) && ( wp_is_mobile() || amp_is_request() ) ) {
				get_template_part( 'template-parts/ads/ad', 'pos3', array( 'ad_slot' => 'pos3' ) );
			}
			?>
			<?php if ( '' !== get_the_archive_description() && ( ! is_author() ) ) : ?>
				<div class="taxonomy-description">
					<?php the_archive_title( '<h3 class="page-title">', '</h3>' );
					echo wp_kses_post( wpautop( get_the_archive_description() ) ); ?>
				</div>
			<?php
			endif;
			get_template_part( 'template-parts/ads/outbrain', 'outbrain', array( 'context' => 'category' ) );
			get_template_part( 'template-parts/ads/taboola', 'taboola', array( 'context' => 'category' ) );
			?>
		</main><!-- #main -->
		<?php get_sidebar(); ?>
	</section><!-- #primary -->

<?php
get_footer();
