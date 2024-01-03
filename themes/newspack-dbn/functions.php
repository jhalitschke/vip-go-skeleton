<?php
/**
 * Funke Digital Newspack DBN Theme Functions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Newspack
 */

require 'fd/carbon-fields_options.php';


/**
 * override functions, filters, hooks & actions from parent-theme
 */
require get_stylesheet_directory() . '/fd/custom-functions.php';

/**
 * Customizer additions.
 */
require get_stylesheet_directory() . '/inc/customizer.php';

/**
 * #FDL-989 Yoast should not create an redirect on slug change
 */
add_filter( 'Yoast\WP\SEO\post_redirect_slug_change', '__return_true' );

/**
 * FDL-1344 fix issue with gravatar image pointing to external domain
 */
add_filter( 'get_avatar_url', function( $url, $id_or_email, $args ) {
	$replace_url = carbon_get_theme_option( 'avatar_image_default_url' );
	if( null === $replace_url || '' === trim( $replace_url ) ) {
		return $url;
	}
	if( false === strpos( $url, 'gravatar.com' ) ) {
		return $url;
	}
	return $replace_url;
}, 10, 3 );

/**
 * FDL-1344 fix issue with gravatar image pointing to external domain
 * when avatar url is set early
 */
add_filter( 'get_avatar_data', function( $args, $id_or_email) {
	if( null === $args || ! array_key_exists( 'url', $args ) ) {
		return $args;
	}
	$replace_url = carbon_get_theme_option( 'avatar_image_default_url' );
	if( null === $replace_url || '' === trim( $replace_url ) ) {
		return $args;
	}
	if( false === strpos( $args['url'], 'gravatar.com' ) ) {
		return $args;
	}
	$args['url'] = $replace_url;
	return $args;
}, 10, 2 );


function newspack_dbn_custom_colors_css_wrap() {
	// Only bother if we haven't customized the color.
	if ( ( ! is_customize_preview() && 'default' === get_theme_mod( 'theme_colors', 'default' ) ) || is_admin() ) {
		return;
	}
	require_once get_stylesheet_directory() . '/inc/child-color-patterns.php';
	?>

	<style type="text/css" id="custom-theme-colors-dbn">
		<?php echo newspack_dbn_custom_colors_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</style>
	<?php
}

add_action( 'wp_head', 'newspack_dbn_custom_colors_css_wrap' );


function load_custom_style() {
	$blog_name     = get_blog_name();
	$special_blogs = [ 'wmn', 'imtest', 'eatclub', 'selfies', 'futurezone', 'gofeminin', 'goldenekamera' ];
	$key           = array_search( $blog_name, $special_blogs );
	$style_name    = ( $key !== false ) ? $blog_name . '-style.css' : 'default-style.css';
	wp_enqueue_style( 'dbn-styles', get_stylesheet_directory_uri() . '/' . $style_name, array(), wp_get_theme()->get( 'Version' ) );
}

add_action( 'wp_enqueue_scripts', 'load_custom_style' );


/**
 * Shrink Desktop Logo for sticky header and centered logo
 */
if ( ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
	if ( true === get_theme_mod( 'header_center_logo', false ) && false === get_theme_mod( 'header_simplified', false ) && get_theme_mod( 'header_sticky', false ) ) {
		add_action( 'wp_footer', function () {
			echo '<script>
		if (window.innerWidth >= 782) {
		    let timer;
		    let header = document.getElementById("masthead");

			window.addEventListener("scroll", function() {
				if(timer) {
					window.clearTimeout(timer);
				}

				timer = window.setTimeout(function() {

					if ((document.documentElement.scrollTop > 100) ){
						header.classList.add("shrink");
					}
					else if ((document.documentElement.scrollTop == 0) ){
						header.classList.remove("shrink");
					}

				}, 75);

			});
		}
	</script>';
		}, 100 );
	}
}


/**
 * Header scroll visibility - extends Newspack Theme sticky header class
 */
if ( ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
	add_action( 'wp_footer', function () {
		echo '<script>
		const header = document.getElementById("masthead");
		const body = document.body;
		let lastScroll = 0;

		const HeaderThrottle = (func, time = 100) => {
		let lastTime = 0;
		return () => {
			const now = new Date();
			if (now - lastTime >= time) {
			func();
			time = now;
			}
		};
		};

		const validateHeader = () => {
			const windowY = window.scrollY;
			const windowH = window.innerHeight;
			const stickyPlayer = document.querySelector(".video-frame");

			if (windowY > windowH) {
				header.classList.add("is-sticky");
				if (windowY > windowH + 170) {
					if (windowY < lastScroll) {
						header.classList.add("scroll-up");
						if (!!stickyPlayer) {
							stickyPlayer.style.top = `${header.offsetHeight}px`;
						}
					} else {
						header.classList.remove("scroll-up");
						if (!!stickyPlayer) {
							stickyPlayer.style.top = "0px";
						}
					}
				} else {
				header.classList.remove("scroll-up");
				}
			} else {
				header.classList.remove("is-sticky");
			}
			lastScroll = windowY;
		};
		if (body.classList.contains("h-stk")){
			window.addEventListener("scroll", HeaderThrottle(validateHeader, 100));
		}
	</script>';
	} );
}

/**
 * Add Font Awesome Icon Kit
 */
if ( get_option( '_fa_enabled' ) == '1' ) {
	add_action( 'wp_enqueue_scripts', function () {
		wp_register_script( 'FontAwesome', carbon_get_theme_option( 'fa_kit_url' ), null, null, true );
		wp_enqueue_script( 'FontAwesome' );
	}, 99 );
}

/**
 * remove the links of the categories that comes from the newspack-blocks template
 */
add_filter( 'the_content', 'unlink_categories', 20 );
function unlink_categories( $content ) {
	$doc = new DOMDocument();
	if ( ! empty( $content ) ) {
		$internal_errors = libxml_use_internal_errors( true );
		// FIXME when doing loadHTML() right with LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED the content is not rendered correctly
		// so we try to fix it with a workaround: adding html and body tags, removing them afterwards
		$doc->loadHTML( '<html><body>' . mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) . '</body></html>', LIBXML_HTML_NODEFDTD );
		libxml_use_internal_errors( $internal_errors );
	}
	$finder = new DomXPath( $doc );
	// category name that comes from the newspack-blocks plugin
	$className = "cat-links";
	// find the node by the class name
	$categoryNodeLink = $finder->query( "//*[contains(@class, '$className')]" );
	foreach ( $categoryNodeLink as $node ) {
		$categoryName = htmlentities( $node->nodeValue );
		// create a span wrapper
		$spanWrapper = $doc->createElement( 'span', $categoryName );
		// reset the node value
		$node->nodeValue = '';
		// add the wrapper inside the category node
		$node->appendChild( $spanWrapper );
	}

	return substr( trim( $doc->saveHTML() ), 12, - 14 );
}

/**
 *    Adds Cleverpush Story Script, if single post contains a story widget
 *    TODO get this working for AMP too
 */
if ( ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
	function add_google_stories() {
		if ( is_single() && strpos( get_the_content(), 'cleverpush-story-widget' ) !== false ) {
			// TODO use wp_enqueue_script
			echo '<script src="https://static.cleverpush.com/sdk/cleverpush-story.js" async></script>';
		}
	}

	add_action( 'wp_head', 'add_google_stories' );
}


// only show posts & recipes in search (no pages)
// TODO add new custom post types here
function searchFilter( $query ) {
	if ( $query->is_search ) {
		$query->set( 'post_type', array(
			'post',
			'recipe'
		) );
	}

	return $query;
}

// don't use this filter in backend, otherwise no no images can be found!
if ( ! is_admin() ) {
	add_filter( 'pre_get_posts', 'searchFilter' );
}


/**
 * Add the first level category path as css class to nav item
 *
 * @param $classes
 * @param $item
 *
 * @return mixed
 */
function fdwp_add_category_nav_class( $classes, $item ) {
	$urlPath      = $item->url;
	$categoryPath = explode( '/', $urlPath );
	$category     = array_values( array_slice( $categoryPath, - 1 ) )[0];
	$classes[]    = $category;

	return $classes;
}

add_filter( 'nav_menu_css_class', 'fdwp_add_category_nav_class', 10, 2 );


/**
 * remove SpotOn attachments when a post is deleted
 */
add_action( 'trash_post', 'delete_handler' );
function delete_handler( $postid ) {
	$importSource = get_post_meta( $postid, 'import_source', true );
	if ( $importSource == 'spoton' ) {
		$attachments = get_attached_media( '', $postid );
		foreach ( $attachments as $attachment ) {
			wp_delete_attachment( $attachment->ID, 'true' );
		}
	}
}

/**
 * Hide SpotOn Pictures in Media Gallery Search
 */
add_filter( 'ajax_query_attachments_args', 'hide_spoton_images' );
function hide_spoton_images( $args ) {
	// only in admin backend
	if ( ! is_admin() ) {
		return;
	}
	$args['meta_query'] =
		array(
			'relation' => 'OR',
			array(
				'key'     => 'import_source',
				'compare' => 'NOT EXISTS'
			),
			array(
				'key'     => 'import_source',
				'value'   => 'spoton',
				'compare' => '!='
			)
		);

	return $args;
}

/**
 * Replace the last yoast breadcrumb item with h1 tag
 * entry-title is displayed as h2
 *
 * @param $output
 *
 * @return false|string
 * @see newspack-dbn/template-parts/header/entry-header.php:28
 * @see newspack-dbn/archive.php:69
 *
 */
function custom_wpseo_breadcrumb_output( $output ) {

	if ( is_search() || is_404() || is_front_page() ) {
		return $output;
	}

	$dom             = new DOMDocument();
	$dom->encoding   = 'utf-8';
	$internal_errors = libxml_use_internal_errors( true );
	$dom->loadHTML( utf8_decode( $output ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_use_internal_errors( $internal_errors );
	$finder = new DomXPath( $dom );

	$classname = "breadcrumb_last";
	$span_list = $finder->query( "//*[contains(@class, '$classname')]" );
	$span      = $span_list->item( 0 );

	$content = '';
	if ( is_single() ) {
		$content = get_post_meta( get_the_ID(), '_yoast_wpseo_title', true );
	}

	// strip off the placeholders from SEO title %%title%% %%page%% %%sep%% %%sitename%%
	$content = preg_replace( "/%%.*%%/", "", $content );

	// if seo title is only build from yoast vars, we use the post title
	if ( strlen( trim( $content ) ) === 0 ) {
		if ( is_author() || is_archive() ) {
			$content = get_the_archive_title();
			$content = preg_replace( "!(.*<span class=\"page-description\">)(.*)(</span>)!", "$2", $content );
		} else {
			$content = the_title( '', '', false );
		}
	}

	// check for hard coded blogname on seo title end, like "bla bla bla - wmn"
	$content = preg_replace( "/ - " . get_blog_name() . "$/i", "", $content );

	$heading = $dom->createElement( 'h1', htmlspecialchars( $content ) );
	if ( $span->parentNode instanceof DOMNode ) {
		$span->parentNode->replaceChild( $heading, $span );
	}

	$output = $dom->saveHTML();

	return html_entity_decode( $output );
}

add_filter( 'wpseo_breadcrumb_output', 'custom_wpseo_breadcrumb_output' );

/**
 * Get the blog "named id" as string of the current blog from theme options
 * fallback if not set, works for subdomain or folder based installations too.
 *
 *
 * @return string
 */
function get_blog_name(): string {
	// try to get value from theme options
	$blog_name = get_option( '_site_named_id' );

	// fallbacks
	if ( empty( $blog_name ) ) {

		if ( get_blog_details()->path !== "/" ) {
			// multisite setup in subdirectories
			$blog_name = substr( get_blog_details()->path, 1, strlen( get_blog_details()->path ) - 2 );
		} else {
			// multisite setup in subdomains
			$blog_name = substr( get_blog_details()->domain, 0, strpos( get_blog_details()->domain, '.' ) );
		}

		// $blog_name = str_replace( ' ', '', strtolower( get_blog_details()->blogname ) );
	}

	return $blog_name;
}


/**
 * Changes position of sky1 based on header width & height
 */
if ( ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
	add_action( 'wp_footer', function () {
		echo '<script>
	(() => {
		let masthead = document.getElementById("masthead");
		if ((window.innerWidth >= 782) && (masthead.offsetWidth > 1000)) {
		  let skyscraper = document.getElementById("sky1");
		  let skyscraperL = document.getElementById("skyl");
		  let [defaultHeight, bufferOffset] = [70, 50];

		  let headerHeight = masthead.offsetHeight ? masthead.offsetHeight : defaultHeight;
		  !!skyscraper && (skyscraper.style.top = headerHeight + bufferOffset + "px");
		  !!skyscraperL && (skyscraperL.style.top = headerHeight + bufferOffset + "px");
		}
	})();
</script>';
	}, 100 );
}

/**
 * Sticky container - offset based on header height
 */
if ( ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
	add_action( 'wp_footer', function () {
		echo '<script>
	(() => {
		let stickyCont = document.querySelector(".sticky-container");
		let masthead = document.getElementById("masthead");

		  if (window.innerWidth >= 782) {
			let [defaultHeight, bufferOffset] = [150, 30];
			let offset = masthead.offsetHeight || defaultHeight;
			!!stickyCont && (stickyCont.style.top = offset + bufferOffset + "px");
		  }

	})();
	</script>';
	}, 100 );
}
