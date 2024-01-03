<?php
/**
 *
 * Escenic escenic-content-import-functions.php / Functions taken from 'function editor' on WPAI.
 * 
 * @package Escenic-Importer 
 */

/**
 * Undocumented function
 *
 * @param [type] $haystack Haystack.
 * @param [type] $needle Needle.
 * @return [string]
 */
function starts_with( $haystack, $needle ) {
	$length = strlen( $needle );
	return substr( $haystack, 0, $length ) === $needle;
}

/**
 * Undocumented function
 *
 * @param [type] $sourceid Source Id.
 * @return [string] link
 */
function get_link_by_sourceid( $sourceid ) {
	$args  = array(
		'meta_query' => array(
			array(
				'key'     => 'source_id',
				'value'   => $sourceid,
				'compare' => '=',
			),
		),
	);
	$posts = get_posts( $args );
	if ( is_array( $posts ) ) {
		return get_permalink( $posts[0]->ID );
	}
}

/**
 * Undocumented function
 *
 * @param [type] $slug Slug.
 * @return [string] Permalink.
 */
function get_link_by_slug_id( $slug ) {
	$slug_array = explode( '-', $slug );
	$slug_id    = array_pop( $slug_array );
	$args       = array(
		'meta_query' => array(
			array(
				'key'     => 'source_slug',
				'value'   => $slug_id,
				'compare' => 'LIKE',
			),
		),
	);
	$posts      = get_posts( $args );
	if ( is_array( $posts ) ) {
		return get_permalink( $posts[0]->ID );
	}
}

/**
 * Undocumented function
 *
 * @param [type] $filepath Filepath.
 * @return [type] array or int.
 */
function get_attachment_id_from_file( $filepath ) {
	$file       = basename( $filepath );
	$query_args = array(
		'post_status' => 'any',
		'post_type'   => 'attachment',
		'fields'      => 'ids',
		'meta_query'  => array(
			array(
				'value'   => $file,
				'compare' => 'LIKE',
			),
		),
	);
	$query      = new WP_Query( $query_args );
	if ( $query->have_posts() ) {
		return $query->posts[0]; // assume the first is correct or process further if you need.
	}
	return 0;
}

/**
 * Undocumented function
 *
 * @param [type] $node Node.
 * @return [string] $inner_html HTML Content.
 */
function get_inner_html( $node ) {
	$inner_html = '';
	$children   = $node->childNodes;
	foreach ( $children as $child ) {
		$inner_html .= $child->ownerDocument->saveXML( $child );
	}
	return $inner_html;
}

/**
 * Undocumented function
 *
 * @param [type] $body HTML Content to corret.
 * @return [string] $body HTML Content.
 */
function body_correction( $body ) {
	$html_dom = new DOMDocument();
	$body     = str_replace( '/uploads/swp/sxl34v/media/', 'https://www.wmn.de/crop/1920x1080/media/', $body );
	// Parse the HTML of the page using DOMDocument::loadHTML.
	@$html_dom->loadHTML( '<html><body>' . mb_convert_encoding( $body, 'HTML-ENTITIES', 'UTF-8' ) . '</body></html>', LIBXML_HTML_NODEFDTD );
	// Extract the links from the HTML.
	$links = $html_dom->getElementsByTagName( 'a' );
	foreach ( $links as $link ) {
		// Get the link text.
		$link_text = $link->nodeValue;
		// Get the link in the href attribute.
		$link_href = $link->getAttribute( 'href' );
		// If the link is empty, skip it and don't.
		// add it to our $extractedLinks array.
		if ( strlen( trim( $link_href ) ) == 0 ) {
			continue;
		}
		// Skip if it is a hashtag / anchor link.
		if ( '#' == $link_href[0] ) {
			continue;
		}
		if ( starts_with( $link_href, 'urn:newsml' ) == true ) {
			$link->setAttribute( 'href', get_link_by_sourceid( $link_href ) );
		}
		if ( starts_with( $link_href, 'https://www.wmn.de' ) == true ) {
			$link->setAttribute( 'href', get_link_by_slug_id( $link_href ) );
		}
	}
	// Extract the img from the HTML.
	$images = $html_dom->getElementsByTagName( 'img' );
	foreach ( $images as $image ) {
		$image_url = $image->getAttribute( 'src' );
		$att_id    = get_attachment_id_from_file( $image_url );
		$image->setAttribute( 'scrset', wp_get_attachment_image_srcset( $att_id ) );
		// $image->setAttribute("sizes","(max-width: 1024px) 100vw, 1024px");
		// $logger and call_user_func($logger, sprintf('Attachment ID `%s`', $image_url));
	}
	// Extract the blockquots from the HTML.
	$blockquotes = $html_dom->getElementsByTagName( 'blockquote' );
	foreach ( $blockquotes as $blockquote ) {
		try {
			$node_b = $blockquote->getElementsByTagName( 'b' )->item( 0 );
			$node_i = $blockquote->getElementsByTagName( 'i' )->item( 0 );
			if ( XML_ELEMENT_NODE == $node_b->nodeType && XML_ELEMENT_NODE == $node_i->nodeType ) {
				$b_value = $node_b->nodeValue;
				$blockquote->removeChild( $node_b );
				$i_value = $node_i->nodeValue;
				$blockquote->removeChild( $node_i );
				$blockquote->setAttribute( 'class', 'wp-block-quote block-editor-block-list__block wp-block' );
				$blockquote->setAttribute( 'data-type', 'core/quote' );
				$node_p            = $html_dom->createElement( 'p' );
				$node_p->nodeValue = $b_value;
				$blockquote->appendChild( $node_p );
				$node_footer          = $html_dom->createElement( 'footer' );
				$node_cite            = $html_dom->createElement( 'cite' );
				$node_cite->nodeValue = $i_value;
				$blockquote->appendChild( $node_footer );
				$node_footer->appendChild( $node_cite );
			}
		} catch ( Exception $e ) {
			$fake_empty = null;
		}
	}
	$html = substr( trim( $html_dom->saveHTML() ), 12, -14 );
	return $html;
}

/**
 * Undocumented function
 *
 * @param [type] $timestamp timestamp.
 * @return [type] timestamp.
 */
function correct_timestamp( $timestamp ) {
	$user_timezone = new DateTimeZone( 'europe/berlin' );
	$gmt_timezone  = new DateTimeZone( 'GMT' );
	$my_datetime   = new DateTime( $timestamp, $gmt_timezone );
	$offset        = $user_timezone->getOffset( $my_datetime );
	$my_interval   = DateInterval::createFromDateString( (string) $offset . 'seconds' );
	$my_datetime->add( $my_interval );
	// $newDateTime = new DateTime($oldDateTime->format("Y-m-dTH:i:s"),$myTimezone);
	$new_timestamp = $my_datetime->format( 'Y-m-dTH:i:s' );
	return $new_timestamp;
}

/**
 * Undocumented function
 *
 * @param [string] $slug Slug.
 * @return [string] new Slug.
 */
function correct_slug( $slug ) {
	$slug_array = explode( '-', $slug );
	array_pop( $slug_array );
	$new_slug = implode( '-', $slug_array );
	return $new_slug;
}

/**
 * Undocumented function
 *
 * @param [type] $by_line By Line.
 * @param [type] $copyright_holder Copyright Holder.
 * @param [type] $copyright_notice Copyright Notice.
 * @return [string] Copyright String
 */
function get_copyright_notice( $by_line, $copyright_holder, $copyright_notice ) {
	if ( strlen( $by_line ) == 0 ) {
		$by_line = '';
	}
	if ( strlen( $copyright_holder ) == 0 ) {
		$copyright_holder = '';
	}
	if ( strlen( $copyright_notice ) == 0 ) {
		$copyright_notice = '';
	}
	$copyright_string = ' Foto: ';
	if ( $by_line != '' ) {
		$copyright_string .= $by_line;
	} elseif ( $copyright_notice != '' ) {
		$copyright_string .= $copyright_notice;
	}
	if ( $copyright_string != '' ) {
		$copyright_string .= ' / ';
	}
	if ( $copyright_holder != '' && $copyright_holder != $copyright_notice ) {
		$copyright_string .= $copyright_holder;
	}
	if ( $copyright_holder != '' && $copyright_notice != '' && $copyright_holder == $copyright_notice ) {
		$copyright_string .= $copyright_holder;
	}
	if ( $copyright_string == ' Foto: ' ) {
		$copyright_string = '';
	}
	return $copyright_string;
}

/**
 * Undocumented function
 *
 * @param [type] $id Id.
 * @return [int] Author Id.
 */
function map_authors( $id ) {
	// Only on WP in Cloud.
	switch ( $id ) {
		case '18':
			return '15';
		case '20':
			return '18';
		case '22':
			return '20';
		case '23':
			return '20';
		case '21':
			return '19';
		case '29':
			return '26';
		case '27':
			return '24';
		case '28':
			return '25';
		case '25':
			return '22';
		case '16':
			return '16';
		case '11':
			return '12';
		case '14':
			return '15';
		case '17':
			return '15';
		case '24':
			return '21';
		case '26':
			return '23';
		default:
			return $id;
	}
}

/**
 * Undocumented function
 *
 * @param [type] $extra Extra.
 * @param [type] $key Key.
 * @return [mixed] 
 */
function get_from_extra( $extra, $key ) {
	if ( strlen( $extra && $key != '' ) ) {
		$fixed_data = preg_replace_callback(
			'!s:(\d+):"(.*?)";!',
			function( $match ) {
				return ( $match[1] == strlen( $match[2] ) ) ? $match[0] : 's:' . strlen( $match[2] ) . ':"' . $match[2] . '";';
			},
			$extra
		);
			$vals   = unserialize( $fixed_data );
		if ( array_key_exists( $key, $vals ) || array_key_exists( $key, $vals['dataLayer'] ) ) {
			if ( is_string( $vals[ $key ] ) ) {
				return strip_tags( html_entity_decode( $vals[ $key ] ) );
			} elseif ( is_bool( $vals[ $key ] ) && $vals[ $key ] == 1 ) {
				return $vals[ $key ];
			} elseif ( is_string( $vals['dataLayer'][ $key ] ) ) {
				return strip_tags( html_entity_decode( $vals['dataLayer'][ $key ] ) );
			} else {
				return 0;
			}
		}
	}
	return '';
}

/**
 * Undocumented function
 *
 * @param [type] $extra Extra.
 * @return [string] ArticleType.
 */
function get_article_type( $extra ) {
	$old_type = get_from_extra( $extra, 'contentType' );
	if ( $old_type == 'vna' ) {
		return 'NewsArticle';
	} else {
		return 'Article';
	}
}

/**
 * Insert an attachment from an URL address.
 *
 * @param  String $url URL.
 * @param  Array  $meta MetaData.
 * @param  Int    $parent_post_id Parent Post Id.
 * @param  Bool   $force_insert Force Insert.
 * @return Int|Bool Attachment ID or Boolean. 
 */
function insert_attachment_from_url( $url, $meta = array(), $parent_post_id = null, $force_insert = false ) {
	$parent_post_id = ! empty( $parent_post_id ) ? $parent_post_id : ( isset( $meta['post_id'] ) ? $meta['post_id'] : 0 );
	debug_log(
		'insert_attachment_from_url()',
		array(
			'args' => array(
				'url'            => $url,
				'meta'           => $meta,
				'parent_post_id' => $parent_post_id,
			),
		)
	);

	if ( ! $force_insert ) {
		// note: only insert attachment if not exists!!!, otherwise get imageId.
		$args        = array(
			'numberposts' => 1,
			'post_type'   => 'attachment',
			'meta_key'    => '_ee_origin_eid',
			'meta_value'  => $meta['escenicId'],
		);
		$attach_post = get_posts( $args );
		debug_log(
			'insert_attachment_from_url() -> get known',
			array(
				'attach_posts' => $attach_post,
			)
		);
	}

	if ( ( isset( $attach_post ) && empty( $attach_post ) ) || $force_insert ) { // nicht vorhanden.
		$upload_dir = wp_upload_dir();
		$filename   = isset( $meta['escenicId'] ) ? $meta['escenicId'] . '.' . pathinfo( basename( $url ), PATHINFO_EXTENSION ) : basename( $url );

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// $image_data = file_get_contents( $url );
		$args = array(
			'stream'    => true,
			'sslverify' => false,
			'filename'  => $file,
		);
		$response   = wp_remote_get( $url, $args );
		debug_log( 'insert_attachment_from_url() -> wp_remote_get done', array( 'response' => $response ) );
	
		// file_put_contents( $file, $image_data );
		// debug_log( 'insert_attachment_from_url() -> file_put_contents done' );
		// .

		if ( ! is_wp_error( $response ) ) {
			$wp_filetype = wp_check_filetype( $filename, null );
			$attachment  = array(
				'guid'           => $upload_dir['url'] . '/' . $filename,
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => sanitize_file_name( pathinfo( basename( $url ), PATHINFO_FILENAME ) ),
				'post_content'   => isset( $meta['credit'] ) ? $meta['credit'] : '', // Caption.
				'post_excerpt'   => isset( $meta['alt'] ) ? $meta['alt'] : '', // Description.
				'post_status'    => 'inherit',
			);
	
			$attach_id = wp_insert_attachment( $attachment, $file, $parent_post_id );
			$test_me   = null;
			debug_log(
				'insert_attachment_from_url() -> wp_insert_attachment done',
				array(
					'attach_id' => $attach_id,
					'file'      => $file,
				)
			);
	
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			$res         = wp_update_attachment_metadata( $attach_id, $attach_data );
			debug_log(
				'insert_attachment_from_url() -> wp_update_attachment_metadata done',
				array(
					'result' => $res,
				)
			);
	
			if ( ! $force_insert ) {
				update_post_meta( $attach_id, '_ee_origin_metadata', $meta ); // $meta is serialized.
				update_post_meta( $attach_id, '_ee_origin_eid', $meta['escenicId'] ); // save escenicId.
			}
	
			return $attach_id;

		} else {
			// todo: output error.
			return false;
		}
	} else { // vorhanden.
		$id = 0;
		foreach ( $attach_post as $post ) {
			$id = $post->ID;
		}
		debug_log(
			'insert_attachment_from_url() -> exists',
			array(
				'attach_id' => $id,
			)
		);
		return $id; // note: return known attachId.
	}

}


/***********************/

function channel_url($urlslug){
	//$urlslug = str_replace("https://www.gofeminin.de","",$urlslug);
	$slug_array = explode("/",trim($urlslug,"/"));
	$return_tax = "";
	foreach ($slug_array as $slug){
		if(strpos($slug, ".html") === false) {
			$return_tax.=$slug."/";
		}
	}
	//$max = max(array_keys($slug_array));
	//$return_tax=$slug_array[$max-1]."/".$slug_array[$max];
	//if($slug_array[$max-2] != "") $return_tax = $slug_array[$max-2] . "/". $return_tax;
	return $return_tax;
}
function my_saved_post( $post_id, $xml_node, $is_update ) {

    //error_log("URL:".$xml_node->feature_image->attributes()->url. " Credit:".$xml_node->feature_image->credit);
	update_credits((string)$xml_node->feature_image->attributes()->url,(string)$xml_node->feature_image->credit);
	
	foreach($xml_node->images->image as $image){
		//error_log($fi->attributes()->url);
		//error_log("URL:".$image->attributes()->url. " Credit:".$image->figcaptions->credit);
		update_credits((string)$image->attributes()->url,(string)$image->figcaptions->credit);
        //error_log("Artikel ID: ".$post_id);
	}

}
add_action( 'pmxi_saved_post', 'my_saved_post', 10, 3 );

function get_aid_by_image($url){
	global $wpdb;
	$results = $wpdb->get_results( "SELECT attachment_id FROM {$wpdb->prefix}pmxi_images WHERE image_url LIKE '".$url."' LIMIT 1", OBJECT );
	return $results[0];
}

function update_credits($image_url,$credit=''){
		$a_id = get_aid_by_image($image_url);
		$meta = get_post_meta( $a_id->attachment_id,  "_media_credit", true );
		if ($meta == "" && $credit != "") {
			add_post_meta( $a_id->attachment_id, "_media_credit", $credit, false );
		}
	return $image_url;
}
function kaltura_video($id){
	return '[kaltura-widget uiconfid="23465290" entryid="'.$id.'" width="100%" height="56.25%" responsive="true" hoveringControls="true" isplaylist="" /]';
}
function get_tax($tax){
	return trim($tax);
}

function get_state($state){
	switch ($state){
		case "publish":
			return "publish";
		default: 
			return "draft";
	}
	return $state;
}

function immage_correction($url){
	return str_replace("https://img.","https://www.",$url);
}
function timestamp_to_date($timestamp){
	$timestamp = substr(trim($timestamp),0,-3);
	$timestamp = date("Y-m-dTH:i:s",$timestamp);
	$userTimezone = new DateTimeZone('europe/berlin');
	$gmtTimezone = new DateTimeZone('GMT');
	$myDateTime = new DateTime($timestamp, $gmtTimezone);
	$offset = $userTimezone->getOffset($myDateTime);	
	$myInterval=DateInterval::createFromDateString((string)$offset . 'seconds');
	$myDateTime->add($myInterval);
	//$newDateTime = new DateTime($oldDateTime->format("Y-m-dTH:i:s"),$myTimezone);
	$new_timestamp=$myDateTime->format("Y-m-dTH:i:s");
	return $new_timestamp;
}

function slug($oldslug){
	$slug_array = explode("/",$oldslug);
	$max = max(array_keys($slug_array));
	return str_replace(".html","",$slug_array[$max-1]."/".$slug_array[$max]);
}
function slug_moin($oldslug){
	$slug_array = explode("-",$oldslug);
	$max = max(array_keys($slug_array));
	return str_replace($slug_array[$max],"",$oldslug);
}
function primary_slug($slug){
	$slug_array = explode("/",$slug);
	$max = max(array_keys($slug_array));
	return $slug_array[$max];
}

function get_name($full,$part){
	$name=explode(" ",$full);
	if ($part == "first") return $name[0];
	if ($part == "last") return $name[1];
}
function get_author_email($email){
    if ($email=="")	return "24"; else return $email;
}
function get_vgwort_authors( $author ) {
	if ( $author === "" ) {
		return "24";
	} else {
		return $author;
	}
}

if ( ! function_exists( 'get_vgwort_authors_goka' ) ) {
	function get_vgwort_authors_goka( $author ) {
		if ( $author === "" ) {
			return "447";
		} else {
			return $author;
		}
	}
}

if ( ! function_exists( 'get_vgwort_authors_bdf' ) ) {
	function get_vgwort_authors_bdf( $author ) {
		if ( $author === "" ) {
			return "449";
		} else {
			return $author;
		}
	}
}
