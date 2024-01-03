<?php
/*
Template Name: Google Feed
*/

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';

global $wp;

?>

<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>

    <?php
        $the_query = new WP_Query( 'posts_per_page=1&ignore_sticky_posts=1' );
        while ($the_query -> have_posts()) : $the_query -> the_post();
        $lastPost = $post->ID;
        $date = new DateTime(get_the_date('c',$postId));
								$date->setTimezone(new DateTimeZone('UTC'));
        $lastPubDate = $date->format(DateTime::RFC2822);
        endwhile;
        wp_reset_postdata();
    ?>

    <lastBuildDate><?php echo $lastPubDate ?></lastBuildDate>
        <title><![CDATA[<?php echo bloginfo( "title" ); ?>]]></title>
        <description><![CDATA[<?php echo wp_filter_nohtml_kses(bloginfo( "description" )); ?>]]></description>
        <link><?php echo get_home_url(); ?></link>
        <language>de-de</language>
        <copyright>(c) <?php echo get_home_url(); ?>. RSS Meldungen dürfen nur unverändert wiedergegeben und ausschließlich online verwendet werden. Die eingeräumten Nutzungsrechte beinhalten ausdrücklich nicht das Recht zur Weitergabe an Dritte. Insbesondere ist es nicht gestattet, die Daten auf öffentlichen Screens oder zum Download anzubieten - weder kostenlos noch kostenpflichtig.</copyright>
        <atom:link href="<?php echo home_url( $wp->request ) ?>" rel="self" type="application/rss+xml" />
            <?php

            $the_query = new WP_Query( 'posts_per_page=25&ignore_sticky_posts=1' );
            while ($the_query -> have_posts()) : $the_query -> the_post();
            $postId = $post->ID;
            ?>
            <item xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/">
                        <guid><?php echo the_permalink() ?></guid>
                        <?php
                            $date = new DateTime(get_the_date('c',$postId));
																												$date->setTimezone(new DateTimeZone('UTC'));
                            $pubDate = $date->format(DateTime::RFC2822);
                        ?>
                        <pubDate><?php echo $pubDate;?></pubDate>
                        <title><?php the_title(); ?></title>
                        <description><![CDATA[<?php echo get_post_meta($postId, '_yoast_wpseo_metadesc', true); ?>]]></description>
                        <content:encoded><![CDATA[<?php echo htmlspecialchars($post->post_content); ?>]]></content:encoded>
                        <link><?php the_permalink() ?></link>

                        <?php
                            $imgUrl = get_the_post_thumbnail_url($postId);
                            $img_id = get_post_thumbnail_id( $postId );
                            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $postId ), 'single-post-thumbnail' );

                            if ($image) :
								$image_file = get_attached_file( get_post_thumbnail_id( $postId ) );
								$content_type = mime_content_type( $image_file );
								$imgSize = filesize( $image_file );
                                $description = get_post_meta( $img_id, '_wp_attachment_image_alt', true  );
                                ?>
								<media:content url="<?php echo $imgUrl ?>" type="<?php echo $content_type ?>" expression="full" width="<?php  echo $image[1]; ?>" height="<?php  echo $image[2]; ?>">
									<media:description type="plain"><![CDATA[<?php echo $description ?>]]></media:description>
								</media:content>
                                <?php
                            endif;
                        ?>
                </item>
            <?php
            endwhile;
            wp_reset_postdata();
            ?>
    </channel>

</rss>
