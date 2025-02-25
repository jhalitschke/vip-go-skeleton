<?php
/**
 * Displays the post header
 *
 * @package Newspack
 */
$discussion = ! is_page() && newspack_can_show_post_thumbnail() ? newspack_get_discussion_data() : null;
// Get sponsors for this post.
if ( function_exists( 'newspack_get_all_sponsors' ) ) {
	$all_sponsors         = newspack_get_all_sponsors( get_the_id() );
	$native_sponsors      = newspack_get_native_sponsors( $all_sponsors );
	$underwriter_sponsors = newspack_get_underwriter_sponsors( $all_sponsors );
}
?>
<?php if ( is_singular() ) : ?>
<?php
	if ( ! is_page() ) :
		if ( ! empty( $native_sponsors ) ) {
			newspack_sponsor_label( $native_sponsors, null, true );
		} else {
			newspack_categories();
		}
	endif;
	?>
<?php
		$subtitle = get_post_meta( $post->ID, 'newspack_post_subtitle', true );
	?>
<h2 class="entry-title <?php echo $subtitle ? 'entry-title--with-subtitle' : ''; ?>">
    <?php echo wp_kses_post( get_the_title() ); ?>
</h2>
<?php if ( $subtitle ) : ?>
<div class="newspack-post-subtitle">
    <?php echo esc_html( $subtitle ); ?>
</div>
<?php endif; ?>
<?php else : ?>
<h2 class="entry-title">
    <a href="<?php the_permalink(); ?>" rel="bookmark">
        <?php echo wp_kses_post( get_the_title() ); ?>
    </a>
</h2>
<?php endif; ?>
<?php
$sharing_enabled = ! is_page() || ! empty( get_post_meta( $post->ID, 'newspack_show_share_buttons', true ) );
if ( $sharing_enabled ) :
	?>
<div class="entry-subhead">
    <?php if ( ! is_page() ) : ?>
    <?php if ( ! empty( $native_sponsors ) ) : ?>
    <div class="entry-meta entry-sponsor">
        <?php newspack_sponsor_logo_list( $native_sponsors ); ?>
        <span>
            <?php
				newspack_sponsor_byline($native_sponsors);
				newspack_posted_on();
				do_action( 'newspack_theme_entry_meta' );
			?>
        </span>
    </div>
    <?php else : ?>
    <div class="entry-meta">
        <?php
			newspack_posted_by();
			newspack_posted_on();
			do_action( 'newspack_theme_entry_meta' );
		?>
    </div><!-- .meta-info -->
    <?php endif; ?>
    <?php endif; ?>
    <?php
// Display Jetpack Share icons, if enabled.
if ( function_exists( 'sharing_display' ) ) {
	sharing_display( '', true );
}
		?>
</div>
	<?php
	if ( get_theme_mod( 'post_show_excerpt' ) ){
		?>
		<div class="lead">
			<?php the_excerpt(); ?>
		</div>
	<?php } ?>
<?php endif; ?>
