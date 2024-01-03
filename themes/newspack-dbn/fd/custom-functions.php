<?php
/**
 * Funke Digital Custom Newspack Theme functions and definitions
 *
 * override functions, filters, hooks & actions from parent-theme
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Newspack
 */


require_once get_stylesheet_directory() . '/fd/escenic_content_import_functions.php';

function insert_jquery_in_header() {
	wp_enqueue_script( 'jquery', false, array(), false, false );
}

add_filter( 'wp_enqueue_scripts', 'insert_jquery_in_header', 1 );


/**
 * avoid svg dropdown-buttons in navigation
 */
add_action( 'after_setup_theme', function () {
	if ( get_option( '_menu_dropdown_icons_disabled' ) === '1' ) {
		remove_filter( 'walker_nav_menu_start_el', 'newspack_add_dropdown_icons', 10 );
	}
} );


/**
 * Funke Digital - Add Inline Ads to Post Body
 *
 * @see crb_attach_theme_options() for options config
 */
if ( get_option( '_ads_inline_enabled' ) === '1' ||
	 get_option( '_ads_outbrain_ia_enabled' ) === '1' ||
	 get_option( '_ads_outbrain_ia_amp_enabled' ) === '1' ||
	 get_option( '_ads_opinary_enabled' ) === '1' ) {

	add_filter( 'the_content', 'add_inline_ads' );

	function add_inline_ads( $content ) {

		if ( empty( $ad_config ) ) {
			$ad_config = fdwp_get_ad_config();
		}

		if ( $ad_config['ads_disabled'] ) {
			return $content;
		}

		if ( empty( $content ) || ! is_singular( 'post' ) ) {
			return $content;
		}

		if ( in_the_loop() ) {

			$debug_enabled       = boolval( carbon_get_theme_option( 'ads_inline_debug_enabled' ) && is_user_logged_in() );
			$paragraph_min_chars = intval( carbon_get_theme_option( 'ads_inline_min_chars' ) );
			$starting_paragraph  = intval( carbon_get_theme_option( 'ads_inline_start_paragraph' ) );
			$blacklisted_classes = array_map( 'trim', explode( ',', carbon_get_theme_option( 'ads_inline_blacklist_classes' ) ) );

			// old articles from escenic got these markers in article body and should only get the JS
			$opinary_script_only = false;
			if ( strpos( $content, '<div id="opinary-automation-placeholder"></div>' ) > 0 &&
				 ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
				$opinary_script_only = true; // prevents inserting of dynamic opinary polls
				fdwp_insert_opinary();
			}

			if ( jetpack_is_mobile() ) {
				$max_ad_count = carbon_get_theme_option( 'ads_inline_max_count_mobile' );
			} elseif ( amp_is_request() || fdwp_amp_is_request() ) {
				$max_ad_count = carbon_get_theme_option( 'ads_inline_max_count_amp' );
			} else {
				$max_ad_count = carbon_get_theme_option( 'ads_inline_max_count' );
			}
			$max_ad_count = intval( $max_ad_count );

			// FDL-1264 special settings for paged articles
			if ( is_paged() ) {
				$starting_paragraph  = 1;
				$paragraph_min_chars = 100;
			}

			if ( $debug_enabled ) {
				$debug_log = [];
			}

			$dom             = new DOMDocument();
			$internal_errors = libxml_use_internal_errors( true );
			$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			libxml_use_internal_errors( $internal_errors );

			// get all nodes on first level
			$node_list = $dom->documentElement->childNodes;

			// first we look for possible ad positions in the body and collect the items index.
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			/** @var ArrayObject $possible_ad_positions */
			$possible_ad_positions = [];

			if ( $node_list->length > 0 ) {
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				/* @var $current_node DOMElement */
				foreach ( $node_list as $index => $current_node ) {

					$current_node_length = strlen( $current_node->textContent );

					if ( $debug_enabled ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						error_log( "({$index}) {$current_node->nodeName} {$current_node_length}" );
						$debug_log[] = "({$index}) {$current_node->nodeName} {$current_node_length}";
					}

					// assume that the paragraph is safe to add an ad to
					$safe_to_add = true;

					// we only add ad-slots after paragraphs
					if ( $current_node->nodeName != 'p' ) {
						$safe_to_add = false;
						continue;
					}

					// reached start paragraph?
					if ( $index + 1 >= $starting_paragraph ) {
						$next_node = $node_list[ $index + 1 ];

						// decide if the current_node is a possible ad position by checking the text length
						if ( ( $current_node_length >= $paragraph_min_chars &&
							   isset( $next_node ) && ! empty( $next_node ) && // is there a next node?
							   // or followed by a headline? or paragraph
							   // TODO make these tags configurable?
							   in_array( $next_node->nodeName, [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ] ) ) ) {

							// check if the paragraph use a blacklisted class
							// TODO add feature to documentation
							if ( $current_node->hasAttribute( 'class' ) ) {
								$classes = explode( ' ', $current_node->getAttribute( 'class' ) );
								if ( ! empty( array_intersect( $blacklisted_classes, $classes ) ) ) {
									$safe_to_add = false;
								}
							}

							if ( $safe_to_add ) {
								$possible_ad_positions[] = $index;
							}
						}
					}
				}
			}

			// phpcs:disable
			if ( $debug_enabled ) {
				$message = 'possible_ad_positions on: ' . print_r( $possible_ad_positions, true );
				error_log( $message );
				$debug_log[] = $message;
			}
			// phpcs:enable

			// TODO distribute ad positions, if we got more possible then allowed?
			$step_width = 1;
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			/*if ( sizeof( $possible_ad_positions ) > $max_ad_count ) {
				$step_width = intval(round( sizeof( $possible_ad_positions ) / $max_ad_count ));
				// var_dump( $step_width );
			}*/

			global $ad_position_count;
			global $flying_carpet_inserted;
			global $outbrain_ia_inserted;
			global $opinary_ia_inserted;
			global $outstream_ia_inserted;
			global $taboola_ia_inserted;
			$ad_position_count      = 1;
			$flying_carpet_inserted = false;
			$outbrain_ia_inserted   = false;
			$opinary_ia_inserted    = false;
			$taboola_ia_inserted    = false;
			$outstream_ia_inserted  = false;
			foreach ( $possible_ad_positions as $current_ad_position ) {
				if ( $node_list->length >= $current_ad_position &&
					 $ad_position_count <= $max_ad_count ) {

					// phpcs:disable
					if ( $debug_enabled ) {
						$message = 'creating ad position: ' . $ad_position_count .
								   ' on item: ' . $current_ad_position .
								   ' with content: ' . mb_strimwidth( $node_list->item( $current_ad_position )->textContent, 0, 50, '...' ) .
								   "\n";
						error_log( $message );
						$debug_log[] = $message;
					}
					// phpcs:enable

					$ad_markup = create_ad_markup( $ad_position_count, $max_ad_count );
					if ( '' !== $ad_markup ) {
						$ad_node = create_document_element_from_HTML( $dom, $ad_markup );
						if ( $ad_node !== null ) {
							$node_list->item( $current_ad_position )->appendChild( $ad_node );
						}
					}
					$ad_position_count ++;
				}
			}

			// phpcs:disable
			if ( $debug_enabled && is_user_logged_in() ) {
				foreach ( $debug_log as $debug_line ) {
					echo "<p style='font-family: monospace;
									font-size: 12px;
									color: cornflowerblue;
									background-color: lightgrey;
									margin: 0 !important;'>{$debug_line}</p>";
				}
			}

			// phpcs:enable

			return $dom->saveHTML();
		}

		return $content;
	}
}


/**
 * Create the the parameters for an ad-slot, based on the position and environment
 * and generate the HTML based on
 *
 * @param int $pos_count
 * @param int $max_ad_count
 *
 * @return string @see template-parts/ads/ad-inline
 */
function create_ad_markup( int $pos_count, int $max_ad_count ): string {

	if ( jetpack_is_mobile() ) {
		$pos_count ++; // mobile the pos1 comes from single.php, so we have to start at 2 even on first inline pos
	} else {
		// desktop has only po1 or else-wise no inline position
		if ( false === carbon_get_theme_option( 'ads_inline_outstream_enabled' ) &&
			 1 === intval( carbon_get_theme_option( 'ads_inline_max_count' ) ) ) {
			return '';
		}
	}

	$positionBase          = 'pos'; // base name for ad-slot id
	$positionSuffix        = $pos_count;
	$ad_size               = '[300,400]';
	$flying_carpet         = false;
	$rubicon_id            = false;
	$outbrain_ia           = false;
	$opinary_ia            = false;
	$outstream_ia          = false;
	$outbrain_ia_widget_id = ''; // TODO really local?
	$outbrain_ia_template  = ''; // TODO really local?
	$taboola_ia            = false;
	global $opinary_script_only;
	global $ad_position_count;
	global $flying_carpet_inserted;
	global $outbrain_ia_inserted;
	global $opinary_ia_inserted;
	global $taboola_ia_inserted;
	global $outstream_ia_inserted;

	if ( jetpack_is_mobile() ) {

		$ad_size = '[[300,100], [300,150], [300,250], [320,50], [320,75], [320,100], [320,150], [336,280]]';

		if ( $pos_count > 3 ) {
			$positionSuffix = 'n';
		}
	}

	// AMP Ads
	if ( amp_is_request() || fdwp_amp_is_request() ) {
		$positionSuffix = $pos_count + 1;
		$ad_size        = '[300,250]';

		if ( get_option( '_ads_inline_flying_carpet_enabled', false ) === '1' && ! $flying_carpet_inserted ) {
			$flying_carpet_position = intval( get_option( '_ads_inline_flying_carpet_position' ) );
			if ( $positionSuffix === $flying_carpet_position ) {
				$flying_carpet          = true;
				$flying_carpet_inserted = true;
			}
		}

		if ( get_option( '_ads_outbrain_ia_amp_enabled', false ) === '1' && ! $outbrain_ia_inserted ) {
			$outbrain_ia_amp_position_position = intval( get_option( '_ads_outbrain_ia_amp_position' ) );
			if ( $pos_count === $outbrain_ia_amp_position_position ) {
				$outbrain_ia           = true;
				$outbrain_ia_widget_id = get_option( '_ads_outbrain_ia_amp_widget_id' );

				// decrease pos counter
				$outbrain_ia_inserted = true;
				$ad_position_count --;
			}
		}

		if ( $pos_count > 3 ) {
			$positionSuffix = 'n';
		}
		if ( $pos_count >= 3 ) {
			$rubicon_id = carbon_get_theme_option( 'ads_amp_rubicon_pos4x' );
		} else {
			$rubicon_id = carbon_get_theme_option( 'ads_amp_rubicon_' . $positionBase . $positionSuffix );
		}
	} else {

		// Special inline positions, that requires additional logic in the ad-inline template, controlled by it's *_ia flag

		if ( carbon_get_theme_option( 'ads_outbrain_ia_enabled' ) && ! $outbrain_ia_inserted ) {
			$outbrain_ia_position = intval( carbon_get_theme_option( 'ads_outbrain_ia_position' ) );
			if ( $pos_count === $outbrain_ia_position ) {
				$outbrain_ia           = true;
				$outbrain_ia_widget_id = carbon_get_theme_option( 'ads_outbrain_ia_widget_id' );
				$outbrain_ia_template  = carbon_get_theme_option( 'ads_outbrain_ia_template_id' );

				// decrease pos counter
				$outbrain_ia_inserted = true;
				$ad_position_count --;
			}
		}

		// dev hint:
		// when other in-article positions with pos decrease are inserted, check for same position like here: ! $outbrain_ia
		if ( carbon_get_theme_option( 'ads_inline_outstream_enabled' ) && ! jetpack_is_mobile() && ! $outstream_ia_inserted && ! ( $outbrain_ia || $taboola_ia || $opinary_ia ) ) {
			$outstream_ia_position = intval( carbon_get_theme_option( 'ads_inline_outstream_position' ) );
			if ( $pos_count === $outstream_ia_position || $pos_count === $max_ad_count ) {
				$outstream_ia          = true;
				$outstream_ia_inserted = true;

				// only decrease ad count until we reach max ads (avoids more ad positions on desktop)
				if ( $pos_count < $max_ad_count ) {
					$ad_position_count --;
				}
			}
		}

		if ( ! $opinary_script_only && carbon_get_theme_option( 'ads_inline_opinary_enabled' ) && ! ( $outbrain_ia || $taboola_ia || $outstream_ia ) ) {
			$opinary_ia_position_position = intval( carbon_get_theme_option( 'ads_inline_opinary_position' ) );
			if ( $pos_count === $opinary_ia_position_position ) {
				$opinary_ia = true;
				// decrease pos counter
				$opinary_ia_inserted = true;
				$ad_position_count --;
			}
		}

		// Taboola -In-Article for mobile
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions. jetpack_is_mobile_ jetpack_is_mobile - safe at this point
		if ( carbon_get_theme_option( 'ads_taboola_ia_enabled' ) && ! $taboola_ia_inserted && jetpack_is_mobile() && ! ( $outstream_ia || $outbrain_ia || $opinary_ia ) ) {
			$taboola_ia_position = intval( carbon_get_theme_option( 'ads_taboola_ia_position' ) );
			if ( $pos_count === $taboola_ia_position ) {
				$taboola_ia = true;

				// decrease pos counter
				$taboola_ia_inserted = true;
				$ad_position_count --;
			}
		}
	}

	return load_template_part(
		'template-parts/ads/ad-inline',
		'inline-' . $pos_count, array(
			'ad_slot'       => $positionBase . $positionSuffix,
			'ad_size'       => $ad_size,
			'poscount'      => $pos_count,
			'flying_carpet' => $flying_carpet,
			'ob_widget_id'  => $outbrain_ia_widget_id,
			'ob_template'   => $outbrain_ia_template,
			'ob_ia'         => $outbrain_ia,
			'taboola_ia'    => $taboola_ia,
			'opinary_ia'    => $opinary_ia,
			'rubicon_id'    => $rubicon_id,
			'outstream_ia'  => $outstream_ia
		)
	);

}

/**
 * Import the given HTML string as DOMNode into the given DOMDocument.
 *
 * @param DOMDocument $doc
 * @param String $html
 *
 * @return DOMNode|void
 */
function create_document_element_from_HTML( DOMDocument $doc, string $html ) {
	if ( '' === $html ) {
		return null;
	}

	$d               = new DOMDocument();
	$internal_errors = libxml_use_internal_errors( true );
	$d->loadHTML( $html );
	libxml_use_internal_errors( $internal_errors );
	if ( $d->documentElement !== null ) {
		return $doc->importNode( $d->documentElement, true );
	} else {
		return null;
	}
}


/**
 *    Buffered template part loading
 */
function load_template_part( $template_name, $part_name = null, $args = null ) {
	ob_start();
	get_template_part( $template_name, $part_name, $args );
	$template = ob_get_contents();
	ob_end_clean();

	return $template;
}

if ( amp_is_request() || fdwp_amp_is_request() ) {

	/**
	 * add dataLayer to amp-analytics
	 * @see https://felix-arntz.me/blog/customizing-google-analytics-configuration-site-kit-plugin/
	 */
	// build amp-analytics tag with nested dataLayer script block
	add_action( 'wp_body_open', function () {

		// get AMP GTM ID from Theme Options
		$gtm_amp_id = carbon_get_theme_option( 'gtm_amp_id' );
		// INFO: Server-Side GTM not yet possible for AMP
		// $gtm_url    = ( empty( carbon_get_theme_option( 'gtm_url' ) ) ) ? 'https://www.googletagmanager.com' : carbon_get_theme_option( 'gtm_url' );
		$gtm_url = 'https://www.googletagmanager.com';

		printf( '<amp-analytics config="%s/amp.json?id=%s&gtm.url=SOURCE_URL"
									  data-credentials="include">
							<script type="application/json">
								{
									"vars":%s
								}
							</script>
						</amp-analytics>',
			$gtm_url,
			$gtm_amp_id,
			getDataLayer()
		);

		// add permutive DMP analytics
		printf( '<amp-analytics type="permutive">
							<script type="application/json">
								{
									"vars": {
										"namespace": "funke",
										"key": "80430544-c5ac-44c5-b4ce-eb3058e62076"
									},
									"extraUrlParams":%s
								}
							</script>
						</amp-analytics>',
			getDataLayer( false, true )
		);

	} );

	// add amp-analytics component to wp_head
	add_action( 'wp_head', function () {
		echo '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';
	} );

} else {
	add_action( 'wp_head', 'fdwp_add_data_layer', 1 );
	add_action( 'wp_head', 'fdwp_add_gtm_head', 2 );
	add_action( 'wp_body_open', 'fdwp_add_gtm_body', 1 );
}

/**
 * Adds dataLayer JSON to wp_head
 *
 * TODO this should together with all dataLayer & GTM related code be moved to a separate plugin
 * @link https://github.com/funke-pe/fdwp-datalayer/blob/1.0.0/fdwp-datalayer.php
 *
 * @return void
 */
function fdwp_add_data_layer() {
	$dataLayerJSON = array_map( 'esc_js', getDataLayer( false ) );
	printf( '<script>
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push(%s)
		  </script>',
		wp_json_encode( $dataLayerJSON ) );
}

function fdwp_add_gtm_head() {

	// get GTM ID from Theme Options
	$gtm_id     = carbon_get_theme_option( 'gtm_id' );
	$gtm_url    = ( empty( carbon_get_theme_option( 'gtm_url' ) ) ) ? 'https://www.googletagmanager.com' : carbon_get_theme_option( 'gtm_url' );
	$gtm_script = ( empty( carbon_get_theme_option( 'gtm_script' ) ) ) ? 'gtm.js' : carbon_get_theme_option( 'gtm_script' );

	printf(
		"<!-- Google Tag Manager -->
				<script async>window.addEventListener('load', () => {(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
							new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
						j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
						'%s/%s?id='+i+dl;f.parentNode.insertBefore(j,f);
					})(window,document,'script','dataLayer','%s');})
				</script>
				<!-- End Google Tag Manager -->",
		$gtm_url,
		$gtm_script,
		$gtm_id
	);

}

function fdwp_add_gtm_body() {

	// get GTM ID from Theme Options
	$gtm_id  = carbon_get_theme_option( 'gtm_id' );
	$gtm_url = ( empty( carbon_get_theme_option( 'gtm_url' ) ) ) ? 'https://www.googletagmanager.com' : carbon_get_theme_option( 'gtm_url' );

	printf(
		'<!-- Google Tag Manager (noscript) -->
				<noscript><iframe src="%s/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
				<!-- End Google Tag Manager (noscript) -->',
		$gtm_url,
		$gtm_id
	);

}

/**
 * Generate VG Wort Pixel
 * If article has an import source set (postmeta 'src') don't prefix the id with -wp
 */
function fdwp_add_vgwort() {
	if ( is_single() ) {
		$post_id            = get_queried_object_id();
		$vgw_domain         = 'https://waz.met.vgwort.de';
		$vgw_account_id     = 1020093;
		$site_id            = get_blog_details()->blog_id;
		$is_worpress_prefix = ( empty( get_post_meta( $post_id, 'src', true ) ) ) ? '-wp' . $site_id . '-' : '-';
		$old_post_id        = get_post_meta( $post_id, 'post_id', true );
		if ( ! empty( $old_post_id ) ) {
			$post_id = preg_replace( '![^0-9]!', '', $old_post_id );
		}
		$vgw_pixel_src = $vgw_domain . '/na/vgzm.' . $vgw_account_id . $is_worpress_prefix . $post_id;
		if ( amp_is_request() || fdwp_amp_is_request() ) {
			echo '<amp-pixel src="' . esc_url( $vgw_pixel_src ) . '" layout="nodisplay" hidden="hidden"></amp-pixel>';
		} else {
			echo '<script>jQuery(document).ready(function(){var vgwort_image=new Image();vgwort_image.src="' . esc_url( $vgw_pixel_src ) . '";});</script>';
		}
	}
}


//note: call function to add vgwort
add_action( 'wp_body_open', 'fdwp_add_vgwort' );

// Disable admin-only for "VG Wort"
add_filter( 'carbon_fields_user_meta_container_admin_only_access', function ( $enable, $container_title, $container ) {
	if ( $container_title === 'VG WORT' ) {
		$enable = false;
	}

	return $enable;
}, 10, 3 );

/**
 * check if value is an URL? -> extract youtube videoId -> save videoId
 *
 * @param [type] $post_id
 *
 * @return void
 */
function fdwp_crb_after_save_event( $post_id ) {
	$yt    = carbon_get_post_meta( $post_id, 'fd_featured_video_yt_value' );
	$isUrl = parse_url( $yt );
	/*error_log( print_r( array( 'INFO'    => 'fdwp_crb_after_save_event()',
							   'isUrl'   => $isUrl,
							   'post_id' => $post_id
	), true ) );*/
	if ( $isUrl && array_key_exists( 'query', $isUrl ) ) {
		parse_str( $isUrl['query'], $ytQuery );
		//error_log( print_r( array( 'INFO' => 'fdwp_crb_after_save_event()', 'parsedString' => $ytQuery ), true ) );
		if ( ! empty( $ytQuery['v'] ) ) {
			carbon_set_post_meta( $post_id, 'fd_featured_video_yt_value', $ytQuery['v'] );
		}
	}
}

add_action( 'carbon_fields_post_meta_container_saved', 'fdwp_crb_after_save_event' ); //note: fires only when value changed

function fdwp_get_thumbnail_from_featured_video( $post_id, $post, $update ) {
	//note: 1. check if an featured video thumbnail exists
	$thumbId = get_post_meta( $post_id, '_fd_featured_video_thumbnail_id', true );
	if ( empty( $thumbId ) ) {
		//note: 2. import thumbnail, save to post_meta _fd_featured_video_thumbnail_id
		$fvs      = carbon_get_post_meta( $post_id, 'fd_featured_video_source' );
		$thumbUrl = '';
		switch ( $fvs ) {
			case 'youtube':
				$yt_eid   = carbon_get_post_meta( $post_id, 'fd_featured_video_yt_value' );
				$thumbUrl = 'https://img.youtube.com/vi/' . $yt_eid . '/hddefault.jpg';
				break;
			case 'kaltura':
				$kv_sc      = carbon_get_post_meta( $post_id, 'fd_featured_video_kaltura_value' );
				$parsedSC   = shortcode_parse_atts( $kv_sc ); //array with shortcode elements
				$server_url = get_site_option( 'kaltura_server_url', get_option( 'kaltura_server_url' ) );
				$partner_id = get_site_option( 'kaltura_partner_id', get_option( 'kaltura_partner_id' ) );
				$thumbUrl   = $server_url . '/p/' . $partner_id . '/thumbnail/entry_id/' . $parsedSC['entryid'] . '/width/800/nearest_aspect_ratio/1';
				break;
		}
		if ( ! empty( $thumbUrl ) ) {
			require_once get_stylesheet_directory() . '/fd/escenic_content_import_functions.php';
			$imageId = insert_attachment_from_url( $thumbUrl, array(), $post_id, true );
			update_post_meta( $post_id, '_fd_featured_video_thumbnail_id', $imageId );
		}
	} else {
		return;
	}
}

// currently disabeled to avoid trouble with imported feature video articles
// add_action( 'save_post', 'fdwp_get_thumbnail_from_featured_video', 100, 3 );

/**
 * Get the data layer as JSON String (default)
 * or assoc array with getDataLayer(false)
 *
 * @param bool $json return dataLayer as JSON
 * @param bool $extraUrlParams return only the data needed for permutive amp-analytics as JSON
 *
 * @return array|string
 */
function getDataLayer( bool $json = true, bool $extraUrlParams = false ) {

	global $post;
	$postId    = get_the_ID();
	$dataLayer = [];

	$dataLayer['pageArticleID']   = $postId;
	$dataLayer['pageDescription'] = get_post_meta( $postId, '_yoast_wpseo_metadesc', true );
	$dataLayer['pageTitle']       = ( str_ends_with( get_the_title( $postId ), get_bloginfo( 'name' ) ) ) ? get_the_title( $postId ) : get_the_title( $postId ) . ' - ' . get_bloginfo( 'name' );

	$author_id               = get_post_field( 'post_author', $postId );
	$author_name             = get_the_author_meta( 'display_name', $author_id );
	$dataLayer['pageAuthor'] = $author_name;

	$dataLayer['pageDefinition'] = get_post_meta( $postId, 'source_content_type', true );
	if ( $dataLayer['pageDefinition'] === "" ) {
		$dataLayer['pageDefinition'] = carbon_get_post_meta( $postId, 'source_content_type' );
	}

	$pageContentType = get_post_meta( $postId, '_yoast_wpseo_schema_article_type', true );
	if ( $pageContentType === "" ) {
		$pageContentType = "Artikel";
	}
	$dataLayer['pageContentType'] = $pageContentType;
	$dataLayer['pageCategory03']  = '';

	$pageCreatedDate = date( 'Y-m-d H:i:s O' ); // TODO: have some default value
	if ( $post ) { // TODO: there is at least one instance, where the function get called without a global post object

		$pageCreatedDate = ( get_post_meta( $postId, '_wp_old_date', true ) !== '' ) ? get_post_meta( $postId, '_wp_old_date', true ) : get_the_date( 'Y-m-d H:i:s O', $post->ID );
		if ( $pageCreatedDate !== '' ) {
			$pageCreatedDate = gmdate( "Y-m-d H:i:s O", strtotime( $pageCreatedDate ) );
		}

	}

	$dataLayer['pageCreated']     = $pageCreatedDate;
	$dataLayer['pageRepublished'] = ( get_post_meta( $postId, 'Republished', true ) == '0' ) ? false : true;

	$dataLayer['pageSource']            = 'wordpress';
	$dataLayer['pageSourcePublication'] = get_bloginfo( "title" );

	$pageSlug  = get_post_meta( $postId, 'source_slug', true );
	$splitSlug = explode( "-id", $pageSlug );
	if ( sizeof( $splitSlug ) > 1 ) {
		$oldId                     = $splitSlug[1];
		$dataLayer['pageSourceID'] = $oldId;
	}

	$dataLayer['pageCanonical'] = wp_get_canonical_url( $postId );

	$dataLayer['pagePublished']        = get_the_date( 'Y-m-d H:i:s', $postId );
	$dataLayer['pagePublishedISO8601'] = get_the_date( 'c', $postId );

	$dataLayer['pageModified'] = get_the_modified_date( 'Y-m-d H:i:s', $postId );

	$pageType = get_post_meta( $postId, '_yoast_wpseo_schema_page_type', true );
	if ( $pageType == "" ) {
		if ( is_single() ) {
			$pageType = "Webpage";
		} else {
			$pageType = "Rubrik";
		}

	}
	$dataLayer['pageType'] = $pageType;

	$yoastKeywords = get_post_meta( $postId, '_yoast_wpseo_focuskw', true );
	$keywordsStr   = '';
	if ( strlen( $yoastKeywords ) > 0 ) {
		$yoastKeywordsArr = explode( " ", $yoastKeywords );
		foreach ( $yoastKeywordsArr as $keyword ) {
			$keywordsStr .= $keyword . ', ';
		}
		$keywordsStr = rtrim( $keywordsStr, ", " );
	}

	$dataLayer['pageKeywords'] = $keywordsStr;

	$dataLayer['pageLengthWords'] = 0;
	$dataLayer['characterLength'] = 0;

	if ( $post ) { // TODO: there is at least one instance, where the function get called without a global post object
		$dataLayer['pageLengthWords'] = str_word_count( wp_strip_all_tags( $post->post_content ) );
		$dataLayer['characterLength'] = strlen( html_entity_decode( strip_shortcodes( wp_strip_all_tags( $post->post_content, true ) ) ) );
	}

	$dataLayer['pageCategory']   = '';
	$dataLayer['pageCategory02'] = '';
	$category_parent_id          = null;
	$the_category                = get_the_category( $postId );
	if ( count( $the_category ) > 0 ) {
		$dataLayer['pageCategory02'] = $the_category[0]->slug;
		$category_parent_id          = $the_category[0]->category_parent;
	}

	// category has no parent
	if ( $category_parent_id === 0 ) {
		$dataLayer['pageCategory']   = get_the_category( $postId )[0]->slug;
		$dataLayer['pageCategory02'] = '';
	} elseif ( $category_parent_id !== null ) {
		$the_term = get_term( $category_parent_id, 'category' );
		if ( is_object( $the_term ) ) {
			$dataLayer['pageCategory'] = $the_term->slug;
		}
	}

	// build separate data for amp permutive analytics
	if ( $extraUrlParams ) {
		$ad_config                            = fdwp_get_ad_config();
		$ampPermutive                         = [];
		$ampPermutive['properties.sectionL1'] = $ad_config['lokverz1'];

		if ( array_key_exists( 'lokverz2', $ad_config ) && $ad_config['lokverz2'] ) {
			$ampPermutive['properties.sectionL2'] = $ad_config['lokverz2'];
		}

		if ( array_key_exists( 'lokverz3', $ad_config ) && $ad_config['lokverz3'] ) {
			$ampPermutive['properties.sectionL3'] = $ad_config['lokverz3'];
		}

		$ampPermutive['properties.article.articleID']     = $dataLayer['pageArticleID'];
		$ampPermutive['properties.article.contentType']   = $ad_config['type'];
		$ampPermutive['properties.article.title']         = $dataLayer['pageTitle'];
		$ampPermutive['properties.article.PublishedDate'] = $dataLayer['pagePublishedISO8601'];

		// these values are only static
		$ampPermutive['properties.classifications_watson.categories']       = "\$alchemy_taxonomy";
		$ampPermutive['properties.classifications_watson.keywords']         = "\$alchemy_keywords";
		$ampPermutive['properties.classifications_watson.entities']         = "\$alchemy_entities";
		$ampPermutive['properties.classifications_watson.concepts']         = "\$alchemy_concepts";
		$ampPermutive['properties.classifications_watson.watson_sentiment'] = "\$alchemy_document_sentiment";
	}

	if ( $json ) {
		return json_encode( $dataLayer );
	} elseif ( $extraUrlParams ) {
		return json_encode( $ampPermutive );
	} else {
		return $dataLayer;
	}

}

/**
 * str_ends_with for PHP 7
 *
 * @deprecated with PHP 8 - @see https://www.php.net/manual/en/function.str-ends-with.php
 *
 * @param $string
 * @param $endString
 *
 * @return bool
 */
if ( ! function_exists( 'str_ends_with' ) ) {
	function str_ends_with( $string, $endString ): bool {
		$len = strlen( $endString );
		if ( $len == 0 ) {
			return true;
		}

		return ( substr( $string, - $len ) === $endString );
	}
}


/**
 * Custom check for AMP requests tries to consider
 * if the request is made for AMP, using the query param amp
 * or the path ending on /amp
 *
 * @return bool
 */
function fdwp_amp_is_request(): bool {

	$url_parsed = parse_url( $_SERVER['REQUEST_URI'] );
	if ( $url_parsed === false ) {
		return false;
	}
	if ( array_key_exists( 'query', $url_parsed ) && $url_parsed['query'] ) {
		return strpos( $url_parsed['query'], 'amp', 0 ) === 0;
	}

	if ( array_key_exists( 'path', $url_parsed ) && $url_parsed['path'] ) {
		if ( substr( $url_parsed['path'], - 4 ) === '/amp' || substr( $url_parsed['path'], - 5 ) === '/amp/' ) {
			return true;
		}
	}

	return false;
}

/**
 *    FDL-820 - Spark integration
 */
//add_action('wp_head', 'fdwp_get_ad_config');

/**
 * Figures out the ad_config zone
 */
function ad_config_find_zone( $valid_zones, $default = 'other' ): string {
	$the_category = get_the_category();
	if ( is_array( $the_category ) && count( $the_category ) > 0 ) {
		if ( in_array( $the_category[0]->slug, $valid_zones, true ) ) {
			return $the_category[0]->slug;
		}

		$the_term = get_term( $the_category[0]->category_parent, 'category' );
		if ( null !== $the_term && ! is_wp_error( $the_term ) &&
			 in_array( $the_term->slug, $valid_zones, true ) ) {
			return $the_term->slug;
		}
	}

	$query_var = get_query_var( 'cat' );
	if ( '' !== $query_var ) {
		$query_cat = get_category( $query_var );
		if ( null !== $query_cat && ! is_wp_error( $query_cat ) ) {
			$the_term_id      = $query_cat->term_id;
			$carbon_term_meta = carbon_get_term_meta( $the_term_id, 'ad_zone' );
			if ( in_array( $carbon_term_meta, $valid_zones, true ) ) {
				return $carbon_term_meta;
			}
		}
	}

	if ( is_tag() ) {
		$the_tags = get_the_tags();
		if ( is_array( $the_tags ) && count( $the_tags ) > 0 ) {
			$the_term_id      = $the_tags[0]->term_id;
			$carbon_term_meta = carbon_get_term_meta( $the_term_id, 'ad_zone' );
			if ( in_array( $carbon_term_meta, $valid_zones, true ) ) {
				return $carbon_term_meta;
			}
		}
	}

	if ( is_front_page() ) {
		return 'homepage';
	}

	if ( is_singular() && is_array( $the_category ) && count( $the_category ) > 0 ) {
		$the_term_id      = $the_category[0]->term_id;
		$carbon_term_meta = carbon_get_term_meta( $the_term_id, 'ad_zone' );
		if ( in_array( $carbon_term_meta, $valid_zones, true ) ) {
			return $carbon_term_meta;
		}
	}

	return $default;
}

/**
 * FDL-858 lokstadt
 */
function ad_config_lokstadt( $default = '' ): string {

	$query_var = get_query_var( 'cat' );
	if ( '' !== $query_var ) {
		$query_cat = get_category( $query_var );
		if ( null !== $query_cat && ! is_wp_error( $query_cat ) ) {
			$the_term_id      = $query_cat->term_id;
			$carbon_term_meta = carbon_get_term_meta( $the_term_id, 'ad_lokstadt' );
			if ( strlen( $carbon_term_meta ) > 0 ) {
				return $carbon_term_meta;
			}
		}
	}

	if ( is_tag() ) {
		$the_tags = get_the_tags();
		if ( is_array( $the_tags ) && count( $the_tags ) > 0 ) {
			$the_term_id      = $the_tags[0]->term_id;
			$carbon_term_meta = carbon_get_term_meta( $the_term_id, 'ad_lokstadt' );
			if ( strlen( $carbon_term_meta ) > 0 ) {
				return $carbon_term_meta;
			}
		}
	}

	if ( is_singular() ) {
		$the_category = get_the_category();
		if ( is_array( $the_category ) && count( $the_category ) > 0 ) {
			$the_term_id      = $the_category[0]->term_id;
			$carbon_term_meta = carbon_get_term_meta( $the_term_id, 'ad_lokstadt' );
			if ( strlen( $carbon_term_meta ) > 0 ) {
				return $carbon_term_meta;
			}
		}
	}

	return $default;
}

/**
 * Create an ad config array with several information from
 * - crb
 * - the current page or post
 * - path info
 *
 * @return array
 */
function fdwp_get_ad_config(): array {

	$ad_config = array();
	$postId    = get_queried_object_id();

	// set ad env
	$ad_config['environment'] = 'sta';
	if ( jetpack_is_mobile() ) {
		$ad_config['environment'] = 'mob';
	}
	if ( newspack_is_amp() ) {
		$ad_config['environment'] = 'amp';
	}

	$ad_config['site_code'] = carbon_get_theme_option( 'ads_site_code' );
	$ad_config['site_name'] = $ad_config['environment'] . "_" . $ad_config['site_code'];

	// set zone
	// business, family, health, homepage, lifestyle or love. For other channel use "other

	$valid_zones       = array_map( 'trim', explode( ',', carbon_get_theme_option( 'ads_valid_zones' ) ) );
	$ad_config['zone'] = ad_config_find_zone( $valid_zones, 'other' );

	// type index for home/channel - news for articles, posts, ...
	$ad_config['type'] = 'index';
	if ( is_single() ) {
		$ad_config['type'] = 'news';
	}

	// lokverz represents the levels in the path until 3rd level
	$path     = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	$sitePath = get_blog_details()->path;

	// strip of the site path
	$purePath = substr_replace( $path, "", 0, strlen( $sitePath ) );
	$thePath  = ''; // TODO correct?
	if ( is_singular( 'post' ) ) {
		global $post;
		$slugPos = strpos( $purePath, $post->post_name );
		if ( $slugPos !== false ) {
			$thePath = substr_replace( $purePath, "", $slugPos - 1 );
		}
	} elseif ( is_category() ) {
		$thePath = $purePath; // TODO correct?
	} else {
		$thePath = $purePath;
	}

	$ad_config['lokverz1'] = '';
	$ad_config['lokverz2'] = '';
	$ad_config['lokverz3'] = '';
	$pathLevels            = explode( "/", $thePath );
	foreach ( $pathLevels as $level => $pathLevel ) {
		$lokverz               = 'lokverz' . ( $level + 1 );
		$ad_config[ $lokverz ] = $pathLevel;
		if ( $level == 2 ) {
			break;
		}
	}

	// FDL-858 lokseite
	$ad_config['lokseite'] = '';
	if ( is_front_page() ) {
		$ad_config['lokseite'] = 'homepage';
	} elseif ( is_category() ) {
		$ad_config['lokseite'] = end( $pathLevels );
	}

	$ad_config['lokstadt'] = ad_config_lokstadt( '' );

	// FDL-858 loktitel
	$ad_config['loktitel'] = ( strlen( carbon_get_theme_option( 'ads_loktitel' ) ) > 0 ) ?
		carbon_get_theme_option( 'ads_loktitel' ) :
		carbon_get_theme_option( 'site_code' );

	// loksnewsid targets the post id
	$ad_config["loknewsid"] = $postId;

	// get tags from post and set as keyword targeting {"key":"keyword","value":["sport","thüringen","fußball"]}
	$posttags = get_the_tags( $postId );
	$tagnames = [];
	if ( $posttags ) {
		foreach ( $posttags as $tag ) {
			$tagnames[] = $tag->name;
		}
	}
	$ad_config['keywords'] = $tagnames;

	$ad_config['ads_disabled'] = false;
	if ( carbon_get_post_meta( $postId, 'ads_disabled' ) ) {
		$ad_config['ads_disabled'] = true;
	}

	return $ad_config;
}

if ( ! ( amp_is_request() || fdwp_amp_is_request() ) ) {
	// wrapping in template_redirect to get conditional tags like is_category() working
	// phpcs:ignore WordPressVIPMinimum.Hooks.AlwaysReturnInFilter.MissingReturnStatement - fdwp_add_spark_scripts() returns always a value
	add_filter( 'template_redirect', function ( $query ) {
		add_action( 'wp_head', 'fdwp_add_spark_scripts', 7 );
	} );
	function fdwp_add_spark_scripts() {

		$ad_config = fdwp_get_ad_config();

		// FDL-1338 add body class flag for GTM & Co. to react on
		if ( $ad_config['ads_disabled'] ) {
			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'ads-disabled';

				return $classes;
			} );
		}

		$data_layer = getDataLayer( false, false );
		$spark_js   = ( empty( carbon_get_theme_option( 'ads_spark_url' ) ) ) ? 'https://spark.cloud.funkedigital.de/spark.js' : carbon_get_theme_option( 'ads_spark_url' );
		if ( function_exists( 'jetpack_is_mobile' ) ) {
			$is_mobile = jetpack_is_mobile();
		} else {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions. jetpack_is_mobile_ jetpack_is_mobile - Jetpack not everywher enabled
			$is_mobile = jetpack_is_mobile();
		}
		?>
		<script async src="<?php echo $spark_js ?>"></script>
		<script>
			var Spark = Spark || {cmd: []};
		</script>
		<script>
			Spark.cmd.push(function () {
				Spark.init({
					cmp: <?php echo carbon_get_theme_option( 'cmp_id' ) ?>,
					cmpParams: {
						cmp_imprinturl: '<?php echo carbon_get_theme_option( 'cmp_imprint_url' ) ?>',
						cmp_privacyurl: '<?php echo carbon_get_theme_option( 'cmp_privacy_url' ) ?>',
						cmp_logo: '<?php echo carbon_get_theme_option( 'cmp_logo_url' ) ?>'
					},
					permutiveTax: {
						sectionL1: "<?php echo esc_js( $ad_config['lokverz1'] );?>",
						sectionL2: "<?php echo esc_js( $ad_config['lokverz2'] );?>",
						sectionL3: "<?php echo esc_js( $ad_config['lokverz3'] );?>",
						article: {
							articleID: "<?php echo esc_js( $data_layer['pageArticleID'] );?>",
							contentType: "<?php echo esc_js( $data_layer['pageContentType'] );?>",
							sourcePublication: "<?php echo esc_js( $ad_config['loktitel'] );?>",
							title: "<?php echo esc_js( $data_layer['pageTitle'] );?>",
							PublishedDate: "<?php echo esc_js( $data_layer['pagePublishedISO8601'] );?>"
						}
					},
					<?php
					// don't define any slots, if ads are disabled
					if (false === $ad_config['ads_disabled']):
					if ($is_mobile && carbon_get_theme_option( 'ads_show_mob_anchor' )):?>
					mob_anchor: true,
					<?php endif;?>
					adslots: [
						<?php
						if (! $is_mobile):
						if (get_option( '_ads_show_sb1' ) === '1'):
						?>
						{
							name: 'sb1',
							id: 'sb1',
							sizes: [[728, 90], [800, 250], [970, 250], [700, 90], [1280, 90]],
							targetings: {
								pos: 'sb1'
							}
						},
						<?php
						endif;
						?>
						{
							name: 'sky1',
							id: 'sky1',
							sizes: [[120, 600], [160, 600], [200, 600], [300, 600], [500, 1000], [300, 1050]],
							targetings: {
								pos: 'sky1'
							},
							sizeMapping: [[[1400, 600], [[120, 600], [160, 600], [200, 600], [300, 600], [500, 1000], [300, 1050]]], [[0, 0], []]]
						},
						{
							name: 'skyl',
							id: 'skyl',
							sizes: [[120, 600], [160, 600], [200, 600], [300, 600], [300, 1050]],
							targetings: {
								pos: 'skyl'
							},
							sizeMapping: [[[1400, 600], [[120, 600], [160, 600], [200, 600], [300, 600], [300, 1050]]], [[0, 0], []]]
						},
						<?php if ( ! carbon_get_theme_option( 'ads_loop_enabled' ) &&
								   ( is_search() || is_category() || is_archive() || is_tag() || is_author() ) ): ?>
						{
							name: 'mr1',
							id: 'mr1',
							sizes: [300, 250],
							targetings: {
								pos: 'mr1'
							}
						},
						<?php endif; ?>
						<?php
						if (carbon_get_theme_option( 'ads_show_outofpage' )):
						?>
						{
							name: 'outofpage',
							id: 'outofpage',
							targetings: {
								pos: 'outofpage'
							}
						}
						<?php
						endif; // end desktop slots
						else:
						if (( ! carbon_get_theme_option( 'ads_loop_enabled' ) &&
							  carbon_get_theme_option( 'ads_show_pos1' ) &&
							  ( is_category() || is_search() || is_author() ) ) ||
							( carbon_get_theme_option( 'ads_show_pos1' ) && is_single() ) ):
						?>
						{
							name: 'pos1',
							id: 'pos1',
							sizes: [[300, 100], [300, 150], [300, 250], [320, 50], [320, 75], [320, 100], [320, 150], [336, 280]], // (2.3)
							targetings: {
								pos: 'pos1'
							}
						},
						<?php
						if (! ( is_singular() && carbon_get_theme_option( 'ads_loop_enabled' ) ) ):
						?>
						{
							name: 'pos2',
							id: 'pos2',
							sizes: [[300, 100], [300, 150], [300, 250], [320, 50], [320, 75], [320, 100], [320, 150], [336, 280]], // (2.3)
							targetings: {
								pos: 'pos2'
							}
						},
						{
							name: 'pos3',
							id: 'pos3',
							sizes: [[300, 100], [300, 150], [300, 250], [320, 50], [320, 75], [320, 100], [320, 150], [336, 280]], // (2.3)
							targetings: {
								pos: 'pos3'
							}
						},
						<?php
						endif;
						?>
						<?php
						endif;
						?>
						<?php
						endif;
						?>
					],
					<?php endif; // end if ads disabled ?>
					siteName: '<?php echo $ad_config['site_name']?>',
					zoneName: '<?php echo $ad_config['zone']?>',
					targetings: {
						environment: '<?php echo $ad_config['environment']?>',
						artikeltyp: '<?php echo $ad_config['type']?>',
						loktitel: '<?php echo $ad_config['loktitel']?>',
						lokverz1: '<?php echo array_key_exists( 'lokverz1', $ad_config ) ? $ad_config['lokverz1'] : ''; // TODO: correct? ?>',
						lokverz2: '<?php echo array_key_exists( 'lokverz2', $ad_config ) ? $ad_config['lokverz2'] : ''; // TODO: correct? ?>',
						lokverz3: '<?php echo array_key_exists( 'lokverz3', $ad_config ) ? $ad_config['lokverz3'] : ''; // TODO: correct? ?>',
						lokseite: '<?php echo array_key_exists( 'lokseite', $ad_config ) ? $ad_config['lokseite'] : ''; // TODO: correct? ?>',
						lokstadt: '<?php echo $ad_config['lokstadt']?>',
						loknewsid: '<?php echo esc_js( get_queried_object_id() )?>',
						keyword: <?php echo json_encode( $ad_config['keywords'] )?>
					}
				})
			});
			Spark.cmd.push(function () {
				Spark.start();
			});
			<?php
			// if no slots, no EL needed
			if (false === $ad_config['ads_disabled']):
			?>
			Spark.cmd.push(function () {
				Spark.addEventListener('slotRenderEnded', function (event) {
					var slotsWithoutAdMarker = ['sky1', 'skyl', 'sb1', 'outofpage', 'anchor1'];
					var slotId = event.slot.getSlotElementId();
					if (!event.isEmpty && slotsWithoutAdMarker.indexOf(slotId) === -1) {
						document.getElementById(slotId).classList.add('has-ad');
					}
				});
			});
			<?php
			endif;
			?>
		</script>

		<?php
	} // end fdwp_add_spark_scripts
}


/**
 * Loop Ads
 *
 * @see crb_attach_theme_options() for config options
 *
 */
// FIXME determinate how to init & boot crb earlier, to use the crb_* methods here (inside hooked functions, no problem)
if ( get_option( '_ads_loop_enabled' ) === '1' ) {
	add_filter( 'template_redirect', function ( $query ) {
		if ( is_category() || is_author() || is_search() || is_archive() ) {
			add_action( 'loop_start', 'fdwp_add_loop_ad_actions' );
		}

		return $query;
	}, 5 );
}

function fdwp_add_loop_ad_actions( $query ) {

	if ( empty( $ad_config ) ) {
		$ad_config = fdwp_get_ad_config();
	}

	if ( $ad_config['ads_disabled'] ) {
		return;
	}

	if ( $query->is_main_query() && ! is_feed() ) {
		add_action( 'the_post', 'fdwp_add_loop_ad_after_post' );
		// TODO can be used to add more than one ad in the loop
		add_action( 'loop_end', 'fdwp_loop_end' );
	}
}

function fdwp_add_loop_ad_after_post() {

	if ( empty( $ad_config ) ) {
		$ad_config = fdwp_get_ad_config();
	}

	if ( $ad_config['ads_disabled'] ) {
		return;
	}

	static $nr = 0; // counts the loop pass
	static $pos_count = 1; // counts the inserted ad positions (slots)

	// if max ad count inserted, remove the loop action and return
	$max_count = intval( carbon_get_theme_option( 'ads_loop_max_count_' . $ad_config['environment'] ) );
	if ( $pos_count > $max_count ) {
		remove_action( 'the_post', 'fdwp_add_loop_ad_after_post' );

		return;
	}

	$position       = intval( carbon_get_theme_option( 'ads_loop_position' ) );
	$ad_size        = carbon_get_theme_option( 'ads_loop_sizes_' . $ad_config['environment'] );
	$rubicon_id     = carbon_get_theme_option( 'ads_amp_rubicon_pos' . $pos_count );
	$positionBase   = ( 'sta' === $ad_config['environment'] ) ? 'mr' : 'pos';
	$positionSuffix = ( $pos_count > 3 ) ? 'n' : $pos_count;

	$nr ++;
	// the mobile pos1 should come directly after first post, regardless which loop position is set
	if ( ( ( 'mob' === $ad_config['environment'] || 'amp' === $ad_config['environment'] ) && $nr === 2 )
		 || $nr % $position === 0 ) {
		$ad = '<!-- loop ad ' . $pos_count . '--><article class="entry loop-ad">'; // wrap ad into article.entry to keep behaviour
		$ad .= load_template_part(
			'template-parts/ads/ad-inline',
			'loop-' . $pos_count, array(
				'ad_slot'       => $positionBase . $positionSuffix,
				'ad_size'       => $ad_size,
				'poscount'      => $pos_count,
				'flying_carpet' => false,
				'ob_widget_id'  => '',
				'ob_template'   => '',
				'ob_ia'         => false,
				'opinary_ia'    => false,
				'rubicon_id'    => $rubicon_id
			)
		);
		echo $ad . '</article>';

		$pos_count ++;

	}

	/**
	 * FDL-920 add Apester channel strip
	 */
	$apester_position = intval( carbon_get_theme_option( 'apester_channel_strip_position' ) );
	if ( carbon_get_theme_option( 'apester_channel_enabled' ) && is_category() && $apester_position === $nr ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo load_template_part( 'template-parts/ads/apester-strip', 'apester-strip' );
	}

}

function fdwp_loop_end() {
	remove_action( 'the_post', 'fdwp_add_loop_ad_after_post' );
}


add_action( 'plugins_loaded', function () {


} );

/**
 * AMP-CMP
 */
if ( amp_is_request() || fdwp_amp_is_request() ) {
	add_action( 'wp_head', 'fdwp_add_cmp_head' );
	function fdwp_add_cmp_head() {
		?>
		<meta name="amp-consent-blocking" content="amp-ad"/>
		<script async custom-element="amp-consent" src="https://cdn.ampproject.org/v0/amp-consent-0.1.js"></script>
		<?php
	}

	add_action( 'wp_body_open', 'fdwp_add_cmp_body' );
	function fdwp_add_cmp_body() {
		$cmpConfig                       = [];
		$cmpConfig["clientConfig"]["id"] = carbon_get_theme_option( 'cmp_amp_id' );
		if ( strlen( carbon_get_theme_option( 'cmp_privacy_url_amp' ) ) > 0 || strlen( carbon_get_theme_option( 'cmp_imprint_url_amp' ) ) > 0 || strlen( carbon_get_theme_option( 'cmp_logo_url_amp' ) ) > 0 ) {
			$cmpConfig["clientConfig"]["params"] = sprintf( "cmp_privacyurl=%s&cmp_imprinturl=%s&cmp_logo=%s",
				urlencode( carbon_get_theme_option( 'cmp_privacy_url_amp' ) ),
				urlencode( carbon_get_theme_option( 'cmp_imprint_url_amp' ) ),
				urlencode( carbon_get_theme_option( 'cmp_logo_url_amp' ) )
			);
		}
		$cmpConfig["uiConfig"]["overlay"] = true;
		?>
		<amp-consent id="ConsentManager" layout="nodisplay" type="ConsentManager">
			<script type="application/json">
				<?php echo json_encode( $cmpConfig ) ?>











			</script>
		</amp-consent>
		<?php
	}
}

/**
 * AMP Ad-Refresh
 */
if ( amp_is_request() || fdwp_amp_is_request() ) {
	add_action( 'wp_head', 'fdwp_add_meta_refresh' );
	function fdwp_add_meta_refresh() {
		?>
		<meta name="amp-ad-enable-refresh" content="doubleclick=30">
		<?php
	}
}
/**
 * AMP Flying Carpet component
 */
if ( amp_is_request() || fdwp_amp_is_request() ) {
	if ( get_option( '_ads_inline_flying_carpet_enabled' ) === '1' ) {
		add_action( 'wp_head', 'fdwp_add_flying_carpet_component' );
		function fdwp_add_flying_carpet_component() {
			?>
			<script async custom-element="amp-fx-flying-carpet"
					src="https://cdn.ampproject.org/v0/amp-fx-flying-carpet-0.1.js"></script>
			<?php
		}
	}
}

function custom_post_thumbnail_html( $html, $post_id, $thumb_id, $size, $attr ) {
	$fvs = carbon_get_post_meta( $post_id, 'fd_featured_video_source' );
	if ( 'newspack-featured-image' !== $size ) {
		if ( $fvs === "kaltura" || $fvs === "youtube" ) {
			$html = wrap_video_teaser( $html, $fvs );
		}

		return $html;
	}
	//note: there is no way to get ratio (height,width) from browser?, set following as default
	switch ( $fvs ) {
		case 'youtube':
			$yt_eid = carbon_get_post_meta( $post_id, 'fd_featured_video_yt_value' );
			if ( is_single() ) {
				$html    = "";
				$title   = carbon_get_post_meta( $post_id, 'fd_featured_video_title' );
				$caption = carbon_get_post_meta( $post_id, 'fd_featured_video_caption' );
				if ( ! amp_is_request() && ! fdwp_amp_is_request() ) {
					$html .= '<div style="width: 100%;display: inline-block;position: relative;" class="yt-video">';
					$html .= '<div style="margin-top: 56%"></div>';
				}
				$html .= wp_oembed_get( 'https://www.youtube.com/watch?v=' . $yt_eid, array( 'width' => '1200' ) );
				if ( ! amp_is_request() && ! fdwp_amp_is_request() ) {
					$html .= '</div>';
				}
				$html .= '<figcaption><p class="caption-title">' . $title . '</p><p class="caption-description">' . $caption . '</p></figcaption>';
			} else {
				$html = wrap_video_teaser( $html, $fvs );
			}
			break;

		case 'kaltura':

			if ( is_single() ) {
				$kv_sc   = carbon_get_post_meta( $post_id, 'fd_featured_video_kaltura_value' );
				$title   = carbon_get_post_meta( $post_id, 'fd_featured_video_title' );
				$caption = carbon_get_post_meta( $post_id, 'fd_featured_video_caption' );
				$html    = '<div class="video-wrapper"><div class="video-frame">';
				$html    .= do_shortcode( $kv_sc );
				$html    .= '<figcaption><p class="caption-title">' . $title . '</p><p class="caption-description">' . $caption . '</p></figcaption>';
				$html    .= '</div></div>';
			} else {
				$html = wrap_video_teaser( $html, $fvs );
			}
			break;
	}

	return $html;
}

add_filter( 'post_thumbnail_html', 'custom_post_thumbnail_html', 10, 5 );

/**
 * Wraps the given $html into <div class="has_video $fvs">...</div>
 *
 * @param string $html
 * @param string $fvs
 *
 * @return false|string
 */
function wrap_video_teaser( $html, $fvs ) {
	$dom             = new DOMDocument();
	$internal_errors = libxml_use_internal_errors( true );
	@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_use_internal_errors( $internal_errors );
	$img     = $dom->getElementsByTagName( 'img' );
	$new_div = $dom->createElement( 'div' );
	$new_div->setAttribute( 'class', 'has_video ' . $fvs );
	$svgPath = get_stylesheet_directory() . '/assets/icon-video.svg';
	$svg     = $dom->createDocumentFragment();
	$svg->appendXML( file_get_contents( $svgPath ) );

	foreach ( $img as $image ) {
		$new_div_clone = $new_div->cloneNode();
		$image->parentNode->replaceChild( $new_div_clone, $image );
		$new_div_clone->appendChild( $image );
		$new_div_clone->appendChild( $svg );
	}

	return $dom->saveHTML();
}


/**
 * Template-Tag copied from newspack-theme/inc/template-tags.php:332
 * to avoid doubled figcaptions
 *
 * TODO newspack-theme needs an output filter in this function
 *
 * @param string $size
 */
function newspack_post_thumbnail( $size = 'newspack-featured-image' ) {
	if ( ! newspack_can_show_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) : ?>

		<figure class="post-thumbnail">

			<?php

			// If using the behind or beside image styles, add the object-fit argument for AMP.
			if ( in_array( newspack_featured_image_position(), array( 'behind', 'beside' ) ) ) :

				the_post_thumbnail(
					$size,
					array(
						'object-fit' => 'cover',
					)
				);
			else :

				if ( 'above' === newspack_featured_image_position() ) :
					the_post_thumbnail(
						$size,
						array(
							'layout' => 'responsive',
						)
					);
				else :
					the_post_thumbnail( $size );
				endif;

				$post_thumbnail_id = get_post_thumbnail_id();
				$caption           = get_the_excerpt( $post_thumbnail_id );
				// Check the existance of the caption separately, so filters -- like ones that add ads -- don't interfere.
				$post_thumbnail_post = get_post( $post_thumbnail_id );
				$caption_exists      = null;
				if ( $post_thumbnail_post ) {
					$caption_exists = $post_thumbnail_post->post_excerpt;
				}

				// Account for featured images that have a credit but no caption.
				if ( ! $caption_exists && class_exists( '\Newspack\Newspack_Image_Credits' ) ) {
					$maybe_newspack_image_credit = \Newspack\Newspack_Image_Credits::get_media_credit_string( get_post_thumbnail_id() );
					if ( strlen( wp_strip_all_tags( $maybe_newspack_image_credit ) ) ) {
						$caption_exists = true;
					}
				}
				$post_id = get_the_ID();
				$fvs     = carbon_get_post_meta( $post_id, 'fd_featured_video_source' );
				if ( $caption_exists && $fvs === "disabled" ) :
					?>
					<figcaption><?php echo wp_kses_post( $caption ); ?></figcaption>
				<?php
				endif;
			endif;
			?>

		</figure><!-- .post-thumbnail -->

	<?php else : ?>

		<figure class="post-thumbnail">
			<a class="post-thumbnail-inner" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( $size ); ?>
			</a>
		</figure>

	<?php
	endif; // End is_singular().
}

function custom_has_post_thumbnail( $value, $post_id, $meta_key, $single ) {
	if ( '_thumbnail_id' === $meta_key ) {
		if ( is_admin() ) {
			$current_screen = get_current_screen();
		} else {
			$current_screen = null;
		}

		// break loop
		remove_filter( 'get_post_metadata', 'custom_has_post_thumbnail', 1 );
		$thumb_id = get_post_thumbnail_id( $post_id );
		add_filter( 'get_post_metadata', 'custom_has_post_thumbnail', 1, 4 );

		$fvs = carbon_get_post_meta( $post_id, 'fd_featured_video_source' );
		if ( $fvs !== 'disabled' ) {
			$value = ! empty( $thumb_id ) ? $thumb_id : ( ! empty( $current_screen ) ? 0 : 1 );
		} else {
			$value = $thumb_id;
		}
		if ( ! $single ) {
			$value = array( $value );
		}
	}

	return $value;
}

//add_filter( 'get_post_metadata', 'custom_has_post_thumbnail', 1, 4 );

// FIXME doesn't work like this
function create_amp_sitemap( $url, $post ) {
	if ( basename( $_SERVER["REQUEST_URI"] ) == 'news-sitemap.xml?amp' ) {
		return $url . '?amp';
	}

	return $url;
}

add_filter( 'wpseo_xml_sitemap_post_url', 'create_amp_sitemap', 10, 2 );

/**
 * Extend SVG Upload
 * @see https://kulturbanause.de/blog/svg-dateien-in-die-wordpress-mediathek-hochladen/
 */

add_filter( 'upload_mimes', function ( $svg_mime ) {
	$svg_mime['svg'] = 'image/svg+xml';

	return $svg_mime;
} );

/**
 *    remove the delicious recipe login form from frontend
 *    because it's hooked from inside a class, you need to pass the class name
 */
add_action( 'after_setup_theme', function () {
	remove_action( 'wp_footer', array( 'DeliciousPublic', 'get_login_registration_form' ) );
} );

/**
 * add the blog name to end of the title tag, if editors delete the var in yoast title
 * @see \Yoast\WP\SEO\Presenters\Title_Presenter
 */
add_filter( 'wpseo_title', function ( $title ) {
	if ( ! str_ends_with( $title, get_bloginfo( 'name' ) ) ) {
		$title = $title . ' - ' . get_bloginfo( 'name' );
	}

	return $title;
} );

/**
 * Add custom block style selects to several editor blocks
 */
add_action( 'init', function () {
	register_block_style( 'newspack-blocks/homepage-articles', [
		'name'  => '-dbn-tags',
		'label' => __( 'Cat-links w/background' ),
	] );
	register_block_style( 'newspack-blocks/homepage-articles', [
		'name'  => '-top-list',
		'label' => __( 'Top list' ),
	] );
	register_block_style( 'newspack-blocks/homepage-articles', [
		'name'  => '-top-list-2',
		'label' => __( 'Top list 2' ),
	] );
	register_block_style( 'core/columns', [
		'name'  => '-dbn-home-hero-1',
		'label' => __( 'Home Hero 3x3' ),
	] );
	register_block_style( 'newspack-blocks/homepage-articles', [
		'name'  => '-text-sticker',
		'label' => __( 'Sticker text' ),
	] );
} );

/**
 * Set the current post_id as meta value for use in Futurezone custom field permalinks:
 * /%category%/%field_post_id%/%postname%.html
 *
 * This let's us keep the old Escenic article URLs without need for any redirects.
 *
 * TODO clarify how to handle the old ID on republish: keep old ece or update to wp ID?
 *
 * @see https://github.com/athlan/wordpress-custom-fields-permalink-plugin
 *
 */
/* Disabled because custom-fields-permalink-plugin was customized
 * TODO push modifications to GitHub
 * if ( get_blog_name() === 'futurezone' ) {
	add_action( 'pre_post_update', function ( $post_id ) {
		$old_post_id = get_post_meta( $post_id, 'post_id', true );
		if ( empty( $old_post_id ) ) {
			update_post_meta( $post_id, 'post_id', 'article' . $post_id );
		}
	}, 10, 1 );
}*/


/**
 * Redirect uppercase urls to lowercase ones
 *
 * adapted from https://plugins.trac.wordpress.org/browser/wp-force-lowercase-urls/trunk/wp-force-lowercase-urls.php
 */
if ( 'futurezone' === get_blog_name() && ! is_admin() ) {
	add_action( 'init',
		function () {
			// Grab requested URL
			$url    = $_SERVER['REQUEST_URI'];
			$params = $_SERVER['QUERY_STRING'];

			// If URL contains a period but not .html, halt (likely contains a filename and filenames are case specific)
			if ( preg_match( '/[\.](?!html)/', $url ) ) {
				return;
			}

			// If URL contains a capital letter
			if ( preg_match( '/[A-Z]/', $url ) ) {
				// Convert URL to lowercase
				$lc_url = empty( $params )
					? strtolower( $url )
					: strtolower( substr( $url, 0, strrpos( $url, '?' ) ) ) . '?' . $params;

				// if url was modified, re-direct
				if ( $lc_url !== $url ) {
					// 301 redirect to new lowercase URL
					header( 'Location: ' . $lc_url, true, 301 );
					exit();
				}

			}
		} );
}

// IVW/SZM
add_action( 'init', function () {
	if ( carbon_get_theme_option( 'tracking_ivw_enabled' ) == '1' ) {
		if ( amp_is_request() || fdwp_amp_is_request() ) {
			add_action( 'wp_head', 'fdwp_add_ivw_head', 5 );
		} else {
			add_action( 'wp_head', 'fdwp_add_ivw_mm_head', 5 );
		}
		add_action( 'wp_body_open', function () {
			get_template_part( 'template-parts/tracking/ivw', 'ivw' );
		} );
	}
} );


function fdwp_get_ivw_config(): array {
	$config = array();
	// FIXME workaround for amp-iframe policy https://github.com/ampproject/amphtml/blob/main/docs/spec/amp-iframe-origin-policy.md
	// we call the helper iframe over the network domain dbn.funkedigital.de instead creating a extra subdomain for IVW
	$config['url'] = sprintf( 'https://%s/wp-content/themes/%s/assets/amp-analytics-infonline.html', get_network()->domain, get_stylesheet() );

	if ( amp_is_request() || fdwp_amp_is_request() || jetpack_is_mobile() ) {
		$config['kennung'] = carbon_get_theme_option( 'tracking_ivw_mobile' );
		$config['frabo']   = ( carbon_get_theme_option( 'tracking_ivw_frabo_mobile_enabled' ) == '1' ) ? 'mo' : 'ke';
	} else {
		$config['kennung'] = carbon_get_theme_option( 'tracking_ivw_desktop' );
		$config['frabo']   = ( carbon_get_theme_option( 'tracking_ivw_frabo_enabled' ) == '1' ) ? 'i2' : 'ke';
	}

	// FDL-906 add measurement manager
	if ( amp_is_request() || fdwp_amp_is_request() || jetpack_is_mobile() ) {
		$data_domain   = carbon_get_theme_option( 'tracking_ivw_mm_mobile' );
		$config['qds'] = ( carbon_get_theme_option( 'tracking_ivw_frabo_mobile_enabled' ) == '1' ) ? 'mo' : false;
	} else {
		$data_domain   = carbon_get_theme_option( 'tracking_ivw_mm_desktop' );
		$config['qds'] = ( carbon_get_theme_option( 'tracking_ivw_frabo_enabled' ) == '1' ) ? 'in' : false;
	}

	// see docs https://docs.infonline.de/infonline-measurement/integration/web/measurement_manager/
	if ( ! preg_match( '/^(data-)([a-f0-9]{10})\.([a-zA-Z0-9][a-zA-Z0-9-_]{0,61})(?:\.([a-z]{2,62}))?\.([a-z]{2,62})$/', $data_domain ) ) {
		$config['data_domain'] = false;
	} else {
		$config['data_domain'] = $data_domain;
	}

	// IOMm verified?
	$config['iomm_verified'] = carbon_get_theme_option( 'tracking_ivw_mm_verified' ) == '1';

	// build tracking code string: kennung-cat1slug[-art]
	$code           = ( empty( get_the_category()[0]->slug ) ) ? get_page_uri() : get_the_category()[0]->slug;
	$config['code'] = $config['kennung'] . '-' . $code;

	if ( is_single() && ! is_page() ) {
		$config['code'] = $config['code'] . '-art';
	}

	return $config;
}

/**
 * Add amp-analytics component to head
 */
function fdwp_add_ivw_head() {
	echo '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';
}

/**
 * Adds the INFOnline Measurement Manager Scripts
 * @see https://docs.infonline.de/infonline-measurement/integration/web/measurement_manager/
 */
function fdwp_add_ivw_mm_head() {
	if ( jetpack_is_mobile() ) {
		$data_domain = carbon_get_theme_option( 'tracking_ivw_mm_mobile' );
	} else {
		$data_domain = carbon_get_theme_option( 'tracking_ivw_mm_desktop' );
	}

	// see docs https://docs.infonline.de/infonline-measurement/integration/web/measurement_manager/
	if ( ! preg_match( '/^(data-)([a-f0-9]{10})\.([a-zA-Z0-9][a-zA-Z0-9-_]{0,61})(?:\.([a-z]{2,62}))?\.([a-z]{2,62})$/', $data_domain ) ) {
		echo '<!-- error: ivw domain service name not valid -->';

		return;
	}

	printf( '
	<!-- begin preload of IOM web manager -->
	<link rel="preload" href="//%s/iomm/latest/manager/base/es6/bundle.js" as="script" id="IOMmBundle">
	<link rel="preload" href="//%s/iomm/latest/bootstrap/loader.js" as="script">
	<!-- end preload of IOM web manager -->
	<!-- begin loading of IOMm bootstrap code -->
	<script type="text/javascript" src="//%s/iomm/latest/bootstrap/loader.js"></script>
	<!-- end loading of IOMm bootstrap code -->', $data_domain, $data_domain, $data_domain );

}

/**
 * Wrapper function to remove Newspack Parent Theme hook & functions
 * - newspack_thumbnails_in_rss() puts unwanted featured image into the item description
 */
function child_remove_parent_functions() {
	remove_filter( 'the_excerpt_rss', 'newspack_thumbnails_in_rss' );
	remove_filter( 'the_content_feed', 'newspack_thumbnails_in_rss' );
}

add_action( 'init', 'child_remove_parent_functions' );

/**
 * Adds an enclosure tag to each rss item, if post has a featured image set
 */
function fdwp_add_enclosure_in_rss_item() {
	global $post;
	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail    = get_the_post_thumbnail_url( $post->ID );
		$image_file   = get_attached_file( get_post_thumbnail_id( $post->ID ) );
		$image_size   = filesize( $image_file );
		$content_type = wp_check_filetype( $image_file )['type'];
		if ( ! $content_type ) {
			$content_type = '';
		}
		echo "<enclosure url=\"{$thumbnail}\" length=\"{$image_size}\" type=\"{$content_type}\" />";
	}
}

add_action( 'rss2_item', 'fdwp_add_enclosure_in_rss_item' );


/**
 * FDL-882
 * inserts eventListener script for opinary polls
 *
 */
function fdwp_insert_opinary() {
	printf( '<script type="text/plain" data-cmp-src="%s" data-cmp-vendor="488" class="cmplazyload" async></script>',
		get_option( '_ads_inline_opinary_script' ) );
	?>
	<script type="text/plain" class="cmplazyload" data-cmp-vendor="488">
		window.addEventListener('OpinaryReady', function () {
			Opinary.on('opinary.vote', function (vote, poll) {
				if (permutive && poll.dmpIntegration) {
					permutive.track('SurveyResponse', {
						survey: {
							id: poll.pollId,
							type: poll.type,
							solution: "Opinary"
						},
						question: {
							text: poll.header
						},
						answer: {
							text: vote.label,
							posX: vote.x || 0.0,
							posY: vote.y || 0.0,
							optionIdentifier: vote.optionID || "",
							optionPosition: vote.position || 0,
							rawValue: vote.value || 0.0,
							unit: vote.unit || ""
						}
					});
				}
			});
		});











	</script>
	<?php
}

/**
 * FDL-921 trackonomics AMP
 *
 * Used only on Comercial Content Posts (coco)
 *
 */
if ( amp_is_request() || fdwp_amp_is_request() ) {
	add_action( 'template_redirect', function () {
		if ( carbon_get_theme_option( 'tracking_trackonomics_enabled' ) &&
			 strlen( carbon_get_theme_option( 'tracking_trackonomics_profile' ) ) > 0 &&
			 get_post_meta( get_the_ID(), '_source_content_type', true ) === 'coco' ) {
			add_action( 'wp_footer', function () {
				get_template_part( 'template-parts/tracking/trackonomic_amp', 'trackonomics',
					array( 'fullprofile' => carbon_get_theme_option( 'tracking_trackonomics_profile' ) ) );
			} );
		}
	} );
}

/**
 * Set ElasticSearch result order by date
 */
add_filter( 'ep_set_sort', 'ep_sort_by_date', 20, 2 );

function ep_sort_by_date( $sort, $order ) {
	$sort = array(
		array(
			'post_date' => array(
				'order' => $order,
			),
		),
	);

	return $sort;
}

/**
 * Some recipes (ID 190146 for example) seems to have time values stored as string
 *
 * This filter prevents
 *  Fatal error: Uncaught Error: Unsupported operand types: string * int
 *  in /wp/wp-content/plugins/delicious-recipes/src/classes/class-delicious-recipes-recipe.php on line 455
 *
 * @param mixed $time | false
 *
 * @return mixed $time | false
 */
function ensure_integer( $time ) {

	if ( false === $time || '' === trim( $time ) ) {
		return false;
	}

	if ( is_numeric( trim( $time ) ) ) {
		return intval( $time );
	}

	return false;
}

add_filter( 'wp_delicious_cook_time', 'ensure_integer' );
add_filter( 'wp_delicious_preparation_time', 'ensure_integer' );
add_filter( 'wp_delicious_rest_time', 'ensure_integer' );

/**
 * FDL-920 Insert Apester Scripts everywhere, if not done by block or loop ad
 *
 */
add_action( 'wp_footer', function () {
	global $apester_strip_inserted;

	if ( ! $apester_strip_inserted && carbon_get_theme_option( 'apester_channel_enabled' ) ) {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped - static template content
		echo '<script type="text/plain"
			 nomodule
			 async
			 data-cmp-src="https://sdk.apester.com/web-sdk.core.legacy.min.js"
			 class="cmplazyload"
			 data-cmp-vendor="354"></script>';

		echo load_template_part( 'template-parts/ads/helpers/apester-module-loader.php', 'apester-loader' );
		// phpcs:enable
	}
} );

/**
 * FDL-1253 Set max-age Headers for sitemap XML files
 */
add_filter( 'wpseo_sitemap_http_headers', function ( $headers ) {
	// Yoast header format is somewhat obscure
	$expires                                         = 60;
	$headers[ 'Cache-Control: max-age=' . $expires ] = '';

	return $headers;
}, 10000, 1 );

/**
 * This workaround for eatclub (blog_id = 4) fixes the headers
 * from the latest Delicious Recipes Plugin version (1.5.2)
 * which prevents the pages from being cache in the Varnish!
 */
if ( ! is_admin() && 4 === get_current_blog_id() ) {
	add_action( 'init', function () {
		header_remove( 'Set-Cookie' );
		header_remove( 'set-cookie' );
		header_remove( 'Pragma' );
		header_remove( 'pragma' );
		header_remove( 'Cache-Control' );

		header( 'Cache-Control: max-age=300, must-revalidate', true );
	} );
}

/**
 * FDL-1263 UI im WordPress Beiträge-Backend optimieren
 *
 * Important columns should have bigger width.
 */
function adjust_admin_style() {
	echo '<style>
    .column-title {
    width: max(10rem, 20vw);
    }
    .fixed .column-post_modified_gmt,
    .fixed .column-post_date_gmt,
    .fixed .column-expirationdate,
    .fixed .column-coauthors,
    .fixed .column-author,
    .fixed .column-taxonomy-cvd-topics,
    .fixed .column-categories,
    .fixed .column-tags {
    width: 5rem;
    }
    .fixed .column-distributor {
    width: 1rem;
    }
    .fixed .column-date {
    width: 12rem;
    }
  </style>';
}

add_action( 'admin_head', 'adjust_admin_style' );

/**
 * FDL-1268
 * Remove the remaining markup from disabled web-stories plugin
 *
 * @param string $block_content The block content about to be appended.
 * @param array $block The full block, including name and attributes.
 *
 * @return string Modified block content.
 */
function remove_web_stories( string $block_content, array $block ): string {
	return '<!-- removed web-stories -->';
}

// only add filter if web-stories plugin isn't activated
if ( ! defined( 'WEBSTORIES_VERSION' ) ) {
	add_action( 'template_redirect', function () {
		if ( is_single() ) {
			add_filter( 'render_block_web-stories/embed', 'remove_web_stories', 10, 2 );
		}
	} );
}

// FDL-1309 - Günstiger Script Widget (Heise Testtabellen-Widget)
function guenstiger_widget_script() {
	if ( has_block( 'genesis-custom-blocks/guenstiger-widget' ) ) {
		wp_register_script( 'guenstiger-widget-script', 'https://api.guenstiger.de/scripts/gt_prodTbl.js' );
		wp_enqueue_script( 'guenstiger-widget-script' );
	}
}

add_action( 'wp_enqueue_scripts', 'guenstiger_widget_script' );

// FDL-1295-Accordion-Block
function fd_accordion_script() {
	if ( has_block( 'genesis-custom-blocks/fd-accordion' ) ) {
		wp_enqueue_style( 'fd-accordion-style', get_stylesheet_directory_uri() . '/blocks/fd-accordion/fd-accordion.css' );
		wp_enqueue_script( 'fd-accordion-script', get_stylesheet_directory_uri() . '/blocks/fd-accordion/fd-accordion.js', [], false, true );
	}
}

add_action( 'enqueue_block_assets', 'fd_accordion_script' );
