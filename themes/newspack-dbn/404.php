<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Newspack
 */

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="not-found">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'OJE! DIE SEITE WURDE NICHT GEFUNDEN.', 'newspack' ); ?></h1>
				</header><!-- .page-header -->
				<p>Möglicherweise sind Sie einem falschen oder veralteten Link gefolgt oder Sie haben sich bei der
					Eingabe der URL vertippt.</p>
				<p>Sie können auf die Startseite zurückkehren oder die gewünschten Inhalte über unsere Suche ausfindig
					machen.</p>
				<p>Vielen Dank, dass Sie die <?php echo get_bloginfo( 'name' ); ?> besuchen.</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<button aria-label="Zurück">Zurück zur Startseite</button>
				</a>
				<?php
				get_template_part( 'template-parts/ads/taboola', 'taboola', array( 'context' => 'article' ) );
				?>
			</div>
		</main>
	</section><!-- #primary -->

<?php
get_footer();
