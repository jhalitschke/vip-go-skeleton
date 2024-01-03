<?php
/**
 * Funke Digital Newspack DBN Theme Options
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Newspack
 */


/**
 * Common Theme Options like
 *
 * - ad zone config
 * - inline ad config
 * - ContentType Options (source_content_type)
 *
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action( 'carbon_fields_register_fields', 'crb_attach_theme_options' );
if ( ! function_exists( 'crb_attach_theme_options' ) ) {
	function crb_attach_theme_options() {
		Container::make( 'theme_options', __( 'Theme Options', 'crb' ) )
		         ->where( 'current_user_role', '=', 'administrator' )
		         ->add_tab( __( 'Design' ), array(
			         Field::make( 'checkbox', 'menu_dropdown_icons_disabled', __( 'Menu Dropdown Icons disabled?' ) )
			              ->set_help_text( __( 'If checked, menu entries with child entries don\'t get a svg arrow icon, indicating the sub tree.' ) )
			              ->set_option_value( '1' )
			              ->set_default_value( '1' ),
			         // Footer Logo
			         Field::make( 'checkbox', 'footer_logo_enabled', __( 'Footer FUNKE Logo enabled?' ) )
			              ->set_help_text( __( 'If checked, a FUNKE Logo, linked to funkedigital.de displayed in footer.' ) )
			              ->set_option_value( '1' )
			              ->set_default_value( '1' ),
			         Field::make( 'select', 'footer_logo_color', __( 'Farbe des Logos' ) )
			              ->set_help_text( __( 'Only red and black are allowed by FUNKE Corporate Design.' ) )
			              ->set_default_value( 'black' )
			              ->add_options( array(
				              'black' => __( 'black' ),
				              'red'   => __( 'red' )
			              ) )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'footer_logo_enabled',
						              'value' => '1',
					              )
				              )
			              )

		         ) )
		         ->add_tab( __( 'Site Options' ), array(
			         Field::make( 'text', 'site_named_id', __( 'Named ID for CSS and other Controls.' ) )
			              ->set_help_text( __( 'Don\'t change this, once setup and in use!' ) )
			              ->set_default_value( 'default' )
		         ) )
		         ->add_tab( __( 'Ad Config' ), array(
			         Field::make( 'text', 'ads_spark_url', __( 'Spark Library URL' ) )
			              ->set_default_value( 'https://spark.cloud.funkedigital.de/spark.js' )
			              ->set_help_text( __( 'You can define dev versions for testing.' ) ),
			         Field::make( 'text', 'ads_site_code', __( 'Site Code' ) )
			              ->set_help_text( __( 'The Site for the AdUnit-Path.' ) ),
			         Field::make( 'text', 'ads_loktitel', __( 'loktitel Targeting' ) )
			              ->set_help_text( __( 'If empty, site code will be used for loktitel targeting.' ) ),
			         Field::make( 'text', 'ads_valid_zones', __( 'Valid Ad Zones' ) )
			              ->set_help_text( __( 'Comma separated list of ad zones to match against category slugs, e.g. homepage,sports,business,health' ) ),
			         Field::make( 'checkbox', 'ads_show_sb1', __( 'Show Superbanner (Desktop)' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'text', 'ads_sb1_name', __( 'Superbanner Name' ) )
			              ->set_help_text( __( 'Used for ad slot name and position targeting.' ) )
			              ->set_default_value( 'sb1' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_show_sb1',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'ads_sb1_size', __( 'Superbanner Sizes (not yet implemented!)' ) )
			              ->set_help_text( __( 'DFP array syntax for sizes [[100,200], [300, 400]]' ) )
			              ->set_default_value( '[[728, 90], [800, 250], [970, 250], [700, 90], [1280, 90]]' ) // TODO how to ensure correct JS array syntax?
			              ->set_conditional_logic(
					         array(
						         array(
							         'field' => 'ads_show_sb1',
							         'value' => '1',
						         )
					         )
				         ),

			         Field::make( 'checkbox', 'ads_show_pos1', __( 'Show Ad pos1 (Mobile)' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'checkbox', 'ads_show_pos1_amp', __( 'Show Ad pos1 (AMP)' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'checkbox', 'ads_show_mob_anchor', __( 'Show mobile Anchor-Ad' ) )
			              ->set_option_value( '1' )
			              ->set_default_value( '1' ),
			         Field::make( 'checkbox', 'ads_show_outofpage', __( 'Show OutOfPage-Ad (Desktop)' ) )
			              ->set_option_value( '1' )
			              ->set_default_value( '1' ),

			         Field::make( 'separator', 'crb_separator', __( 'AMP Rubicon IDs' ) ),
			         Field::make( 'text', 'ads_amp_rubicon_pos1', __( 'pos1' ) )
			              ->set_default_value( '18086-amp_wmn_pos1' )
			              ->set_help_text( __( 'Rubicon ID for pos1.' ) ),
			         Field::make( 'text', 'ads_amp_rubicon_pos2', __( 'pos2' ) )
			              ->set_default_value( '18086-amp_wmn_pos2' )
			              ->set_help_text( __( 'Rubicon ID for pos2.' ) ),
			         Field::make( 'text', 'ads_amp_rubicon_pos3', __( 'pos3' ) )
			              ->set_default_value( '18086-amp_wmn_pos3' )
			              ->set_help_text( __( 'Rubicon ID for pos3.' ) ),
			         Field::make( 'text', 'ads_amp_rubicon_pos4x', __( 'pos4-x' ) )
			              ->set_default_value( '18086-amp_wmn_pos4bisX' )
			              ->set_help_text( __( 'Rubicon ID for pos4...x.' ) ),
			         Field::make( 'text', 'ads_amp_rubicon_footer1', __( 'footer1' ) )
			              ->set_default_value( '18086-amp_wmn_footer1' )
			              ->set_help_text( __( 'Rubicon ID for footer1.' ) ),


		         ) )

			// Outbrain
			     ->add_tab( __( 'Outbrain' ), array(
				Field::make( 'separator', 'crb_separator_outbrain', __( 'Outbrain Smartfeed desktop/mobile' ) ),
				Field::make( 'text', 'ads_outbrain_script_id', __( 'Script ID' ) )
				     ->set_help_text( __( 'This ID will be appended to the widget script, like outbrain.js?i=xyz123' ) )
				     ->set_default_value( 'fabd2014' )
				     ->set_conditional_logic(
					     array(
						     'relation' => 'OR',
						     array(
							     'field' => 'ads_outbrain_article_enabled',
							     'value' => '1',
						     ),
						     array(
							     'field' => 'ads_outbrain_category_enabled',
							     'value' => '1',
						     ),
						     array(
							     'field' => 'ads_outbrain_homepage_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_outbrain_article_enabled', __( 'Enable Outbrain on articles?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_outbrain_article_widget_id', __( 'Widget ID' ) )
				     ->set_default_value( 'AR_1' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_article_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_outbrain_category_enabled', __( 'Enable Outbrain on category pages?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_outbrain_category_widget_id', __( 'Widget ID' ) )
				     ->set_default_value( 'AR_1' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_category_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_outbrain_homepage_enabled', __( 'Enable Outbrain on homepage?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_outbrain_homepage_widget_id', __( 'Widget ID' ) )
				     ->set_default_value( 'AR_1' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_homepage_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				// Outbrain AMP
				Field::make( 'separator', 'crb_separator_outbrain_amp', __( 'Outbrain Smartfeed AMP' ) ),
				Field::make( 'checkbox', 'ads_outbrain_amp_article_enabled', __( 'Enable Outbrain on AMP articles?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_outbrain_amp_article_widget_id', __( 'Widget ID' ) )
				     ->set_default_value( 'AMP_1' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_amp_article_enabled',
							     'value' => '1',
						     )
					     )
				     ),

				Field::make( 'separator', 'crb_separator_outbrain_ia', __( 'Outbrain In-Article' ) ),
				Field::make( 'checkbox', 'ads_outbrain_ia_enabled', __( 'Enable Outbrain In-Article for desktop/mobile' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_outbrain_ia_position', __( 'In-Article Position, replaces the DFP Slot on this position!' ) )
				     ->set_default_value( '2' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_outbrain_ia_widget_id', __( 'Outbrain In-Article Widget ID' ) )
				     ->set_default_value( 'IA_1' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_outbrain_ia_template_id', __( 'Outbrain In-Article Template' ) )
				     ->set_default_value( 'DE_futurezone.de' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_outbrain_ia_amp_enabled', __( 'Enable Outbrain In-Article for AMP' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_outbrain_ia_amp_position', __( 'In-Article Position, replaces the DFP Slot on this position!' ) )
				     ->set_default_value( '2' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_ia_amp_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_outbrain_ia_amp_widget_id', __( 'Outbrain In-Article AMP Widget ID' ) )
				     ->set_default_value( 'AMP_2' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_outbrain_ia_amp_enabled',
							     'value' => '1',
						     )
					     )
				     ),
			) )
			// Taboola
			     ->add_tab( __( 'Taboola' ), array(
				Field::make( 'separator', 'crb_separator_taboola', __( 'Taboola Feed desktop/mobile' ) ),
				Field::make( 'text', 'ads_taboola_pub_name', __( 'Publication Name (pub-name)' ) )
				     ->set_default_value( 'funke-futurezonede' )
				     ->set_conditional_logic(
					     array(
						     'relation' => 'OR',
						     array(
							     'field' => 'ads_taboola_article_enabled',
							     'value' => '1',
						     ),
						     array(
							     'field' => 'ads_taboola_category_enabled',
							     'value' => '1',
						     ),
						     array(
							     'field' => 'ads_taboola_home_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_taboola_article_enabled', __( 'Enable Taboola on articles?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_taboola_article_mode', __( 'Mode' ) )
				     ->set_default_value( 'thumbs-feed-01-b' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_article_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_article_placement', __( 'Placement' ) )
				     ->set_default_value( 'End of Article Feed' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_article_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_article_target_type', __( 'Target Type' ) )
				     ->set_default_value( 'mix' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_article_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_taboola_category_enabled', __( 'Enable Taboola on category pages?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_taboola_category_mode', __( 'Mode' ) )
				     ->set_default_value( 'thumbs-feed-01-b' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_category_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_category_placement', __( 'Placement' ) )
				     ->set_default_value( 'End of Category Feed' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_category_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_category_target_type', __( 'Target Type' ) )
				     ->set_default_value( 'mix' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_category_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_taboola_home_enabled', __( 'Enable Taboola on homepage?' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_taboola_home_mode', __( 'Mode' ) )
				     ->set_default_value( 'thumbs-feed-01-b' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_home_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_home_placement', __( 'Placement' ) )
				     ->set_default_value( 'End of Homepage Feed' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_home_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_home_target_type', __( 'Target Type' ) )
				     ->set_default_value( 'mix' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_home_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				// Taboola In-Article
				Field::make( 'separator', 'crb_separator_taboola_ia', __( 'Taboola In-Article' ) ),
				Field::make( 'checkbox', 'ads_taboola_ia_enabled', __( 'Enable Taboola In-Article for mobile' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_taboola_ia_position', __( 'In-Article Position, replaces the DFP Slot on this position!' ) )
				     ->set_default_value( '2' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_ia_mode', __( 'Mode' ) )
				     ->set_default_value( 'thumbs-feed-01-b' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_ia_placement', __( 'Placement' ) )
				     ->set_default_value( 'End of Homepage Feed' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_taboola_ia_target_type', __( 'Target Type' ) )
				     ->set_default_value( 'mix' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_taboola_ia_enabled',
							     'value' => '1',
						     )
					     )
				     ),
			) )
			// End Taboola
			     ->add_tab( __( 'Inline Ads' ), array(
				// TODO add https://docs.carbonfields.net/#/fields/conditional-logic
				Field::make( 'checkbox', 'ads_inline_enabled', __( 'Inline Ads enabled' ) )
				     ->set_help_text( __( 'Inline Ads will be inserted into the text body, between paragraphs, following these rules:' ) )
				     ->set_option_value( '1' ),
				Field::make( 'checkbox', 'ads_inline_debug_enabled', __( 'Debugging enabled' ) )
				     ->set_help_text( __( 'Output debug infos to error_log & into site markup.' ) )
				     ->set_option_value( '1' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_inline_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_inline_min_chars', __( 'Mininum chars per paragraph' ) )
				     ->set_help_text( __( 'How many chars a paragraph needs at least, to insert an ad after it.' ) )
				     ->set_default_value( 200 ),
				/*Field::make( 'checkbox', 'ads_inline_check_next_node', __( 'Check also text of the following paragraph?' ) )
					 ->set_help_text( __( 'If enabled, both paragraphs needs a text length, greater then the value above, to get an ad position between.' ) )
					 ->set_option_value( '1' ),*/
				Field::make( 'text', 'ads_inline_blacklist_classes', __( 'Blacklisted CSS Classes' ) )
				     ->set_help_text( __( 'Comma separated list of CSS classes (on paragrahs), which shouldn\'t get an inline-ad.' ) )
				     ->set_default_value( 'embed-block__description,blacklisted-class' ),
				Field::make( 'text', 'ads_inline_max_count', __( 'Max ad count desktop' ) )
				     ->set_help_text( __( 'How many inline ads should maximal be inserted on Desktop?' ) )
				     ->set_default_value( 1 ),
				Field::make( 'text', 'ads_inline_max_count_mobile', __( 'Max ad count mobile' ) )
				     ->set_help_text( __( 'How many inline ads should maximal be inserted on mobile?' ) )
				     ->set_default_value( 5 ),
				Field::make( 'text', 'ads_inline_max_count_amp', __( 'Max ad count AMP' ) )
				     ->set_help_text( __( 'How many inline ads should maximal be inserted on AMP?' ) )
				     ->set_default_value( 5 ),
				Field::make( 'text', 'ads_inline_start_paragraph', __( 'Starting paragraph' ) )
				     ->set_help_text( __( 'After how many paragraphs, the first ads is inserted?' ) )
				     ->set_default_value( 2 ),
				Field::make( 'checkbox', 'ads_inline_flying_carpet_enabled', __( 'AMP Flying Carpet enabled?' ) )
				     ->set_help_text( __( 'If enabled the inline position set in the next option, will be replaced by the flying carpet ad.' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_inline_flying_carpet_position', __( 'AMP Flying Carpet position?' ) )
				     ->set_help_text( __( 'Which posX should be the flying carpet ad?' ) )
				     ->set_default_value( 2 ),
				Field::make( 'checkbox', 'ads_inline_opinary_enabled', __( 'Opinary polls enabled?' ) )
				     ->set_help_text( __( 'If enabled the inline position set in the next option, will be replaced by the opinary poll.' ) )
				     ->set_option_value( '1' ),
				Field::make( 'text', 'ads_inline_opinary_position', __( 'Opinary polls position?' ) )
				     ->set_help_text( __( 'Which posX should be the opinary ad?' ) )
				     ->set_default_value( 4 )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_inline_opinary_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'text', 'ads_inline_opinary_script', __( 'Opinary Widget Script URL' ) )
				     ->set_default_value( 'https://widgets.opinary.com/a/derwesten.js' )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_inline_opinary_enabled',
							     'value' => '1',
						     )
					     )
				     ),
				Field::make( 'checkbox', 'ads_inline_outstream_enabled', __( 'Outstream position (po1) enabled?' ) )
				     ->set_option_value( '1' )
				     ->set_default_value( '1' ),
				Field::make( 'text', 'ads_inline_outstream_position', __( 'Outstream position?' ) )
				     ->set_help_text( __( 'Which posX should be po1 ad?' ) )
				     ->set_default_value( 2 )
				     ->set_conditional_logic(
					     array(
						     array(
							     'field' => 'ads_inline_outstream_enabled',
							     'value' => '1',
						     )
					     )
				     ),

			) )
		         ->add_tab( __( 'Loop Ads' ), array(
			         Field::make( 'checkbox', 'ads_loop_enabled', __( 'Loop Ads enabled' ) )
			              ->set_help_text( __( 'Loop Ads will be inserted into the main loop, between teaser items, following these rules:' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'text', 'ads_loop_position', __( 'Starting position' ) )
			              ->set_help_text( __( 'After how many items, the ad is inserted?' ) )
			              ->set_default_value( 4 )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),

			         Field::make( 'separator', 'crb_separator_loop_desktop', __( 'Loop ads desktop.' ) )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'ads_loop_max_count_sta', __( 'Max ad count desktop' ) )
			              ->set_default_value( 2 )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'ads_loop_sizes_sta', __( 'Ad sizes desktop' ) )
			              ->set_default_value( '[300, 250]' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),

			         Field::make( 'separator', 'crb_separator_loop_mob', __( 'Loop ads mobile.' ) )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'ads_loop_max_count_mob', __( 'Max ad count mobile.' ) )
			              ->set_default_value( 8 )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'ads_loop_sizes_mob', __( 'Ad sizes mobile.' ) )
			              ->set_default_value( '[[300, 100], [300, 150], [300, 250], [320, 50], [320, 75], [320, 100], [320, 150], [336, 280]]' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),

			         Field::make( 'separator', 'crb_separator_loop_amp', __( 'Loop ads AMP' ) )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'ads_loop_max_count_amp', __( 'Max ad count AMP.' ) )
			              ->set_default_value( 8 )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'ads_loop_enabled',
						              'value' => '1',
					              )
				              )
			              ),

			         Field::make( 'separator', 'crb_separator_apester', __( 'Apester Channel Strip' ) ),
			         Field::make( 'checkbox', 'apester_channel_enabled', __( 'Apester Strip enabled?' ) )
			              ->set_help_text( __( 'Inline Ads will be inserted into the text body, between paragraphs, following these rules:' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'text', 'apester_channel_strip_position', __( 'Apester Strip position?' ) )
			              ->set_default_value( 10 )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'apester_channel_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'apester_channel_token', __( 'Apester Strip Channel Token' ) )
			              ->set_help_text( __( 'Value for the attribute data-channel-tokens="...".' ) )
			              ->set_default_value( '61713c8aa6f38e000d0251b6' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'apester_channel_enabled',
						              'value' => '1',
					              )
				              )
			              ),
		         ) )
		         ->add_tab( __( 'CMP' ), array(
			         Field::make( 'text', 'cmp_id', __( 'CMP ID' ) )
			              ->set_default_value( '123456' ),
			         Field::make( 'text', 'cmp_privacy_url', __( 'CMP Privacy Page URL' ) )
			              ->set_default_value( 'https://example.com/privacy' ),
			         Field::make( 'text', 'cmp_imprint_url', __( 'CMP Imprint Page URL' ) )
			              ->set_default_value( 'https://example.com/imprint' ),
			         Field::make( 'text', 'cmp_logo_url', __( 'CMP Logo URL' ) )
			              ->set_default_value( 'https://example.com/my-logo.jpg' ),
			         Field::make( 'separator', 'crb_separator_cmp_amp', __( 'CMP AMP' ) ),
			         Field::make( 'text', 'cmp_amp_id', __( 'CMP-AMP ID' ) )
			              ->set_default_value( '654321' ),
			         Field::make( 'text', 'cmp_privacy_url_amp', __( 'CMP AMP Privacy Page URL' ) )
			              ->set_default_value( 'https://example.com/privacy?amp' ),
			         Field::make( 'text', 'cmp_imprint_url_amp', __( 'CMP AMP Imprint Page URL' ) )
			              ->set_default_value( 'https://example.com/imprint?amp' ),
			         Field::make( 'checkbox', 'cmp_outbrain_enabled', __( 'CMP blocking Outbrain' ) )
			              ->set_help_text( __( 'If checked, all Outbrain widgets wait for User Consent.' ) )
			              ->set_option_value( '1' )
		         ) )
		         ->add_tab( __( 'GTM/IVW' ), array(
			         Field::make( 'text', 'gtm_id', __( 'GTM ID' ) )
			              ->set_default_value( 'GTM-XXXXXXX' )
			              ->set_help_text( __( 'As long, GSK outputs no dataLayer for AMP, we use this own solution.' ) ),
			         Field::make( 'text', 'gtm_amp_id', __( 'GTM-AMP ID' ) )
			              ->set_default_value( 'GTM-AMPXXXX' )
			              ->set_help_text( __( 'As long, GSK outputs no dataLayer for AMP, we use this own solution.' ) ),
			         Field::make( 'text', 'gtm_url', __( 'GTM URL' ) )
			              ->set_default_value( 'https://www.googletagmanager.com' )
			              ->set_help_text( __( 'Use full qualified URL. including https, no trailing slash.' ) ),
			         Field::make( 'text', 'gtm_script', __( 'GTM Script File' ) )
			              ->set_help_text( __( 'Use this field to masquerade the filename of gtm.js for Ghostery & Co.' ) ),

			         // IVW / SZM
			         Field::make( 'separator', 'crb_separator_tracking_ivw', __( 'IVW / SZM' ) ),
			         Field::make( 'checkbox', 'tracking_ivw_enabled', __( 'Enable IVW Tracking?' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'text', 'tracking_ivw_desktop', __( 'Angebotskennung desktop' ) )
			              ->set_default_value( 'angebotskennung' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'tracking_ivw_mm_desktop', __( 'DomainService Name Desktop' ) )
			              ->set_help_text( 'data-acbd18db4c.example.com, see <a href="https://docs.infonline.de/infonline-measurement/integration/web/measurement_manager/#mit-preload-und-bundle-loader" target="_blank">Docs</a>' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'tracking_ivw_mobile', __( 'Angebotskennung mobile (MEW)' ) )
			              ->set_default_value( 'angebotmew' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'text', 'tracking_ivw_mm_mobile', __( 'DomainService Name Mobile' ) )
			              ->set_help_text( 'data-acbd18db4c.example.com, see <a href="https://docs.infonline.de/infonline-measurement/integration/web/measurement_manager/#mit-preload-und-bundle-loader" target="_blank">Docs</a>' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         // TODO frabo type https://www.infonline.de/support/frabo-variable-nur-bei-agof-teilnahme/
			         // i2 / mo - ke off
			         Field::make( 'checkbox', 'tracking_ivw_frabo_enabled', __( 'Use Frabo-Tag?' ) )
			              ->set_option_value( '1' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'checkbox', 'tracking_ivw_frabo_mobile_enabled', __( 'Use MEW Frabo-Tag?' ) )
			              ->set_option_value( '1' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'checkbox', 'tracking_ivw_mm_verified', __( 'Measurement Manager implementation verified by IVW?' ) )
			              ->set_option_value( '1' )
			              ->set_help_text( 'When checked, the IOMn(\'configure\') event gets property \'mh:5\' and old SZM Tag gets disabled.' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_ivw_enabled',
						              'value' => '1',
					              )
				              )
			              ),
			         Field::make( 'checkbox', 'tracking_trackonomics_enabled', __( 'Enable Trackonomics?' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'text', 'tracking_trackonomics_profile', __( 'Trackonomics Profile' ) )
			              ->set_default_value( 'funke_7c9be_bildderfrau' )
			              ->set_help_text( 'Value for the fullprofile var in tracking/trackonomics_amp.php' )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'tracking_trackonomics_enabled',
						              'value' => '1',
					              )
				              )
			              ),

		         ) )
		         ->add_tab( __( 'Social Media' ), array(
			         Field::make( 'text', 'social_facebook_url', __( 'Facebook' ) ),
			         Field::make( 'text', 'social_instagram_url', __( 'Instagram' ) ),
			         Field::make( 'text', 'social_twitter_url', __( 'Twitter' ) ),
			         Field::make( 'text', 'social_pinterest_url', __( 'Pinterest' ) ),
			         Field::make( 'text', 'social_flipboard_url', __( 'Flipboard' ) ),
		         ) )
		         ->add_tab( __( 'Font Awesome Icons' ), array(
			         Field::make( 'text', 'fa_kit_url', __( 'Font Awesome Kit URL' ) )
			              ->set_default_value( 'https://kit.fontawesome.com/75ebdac0c3.js' )
			              ->set_help_text( __( 'See https://fontawesome.com/kits' ) ),
			         Field::make( 'checkbox', 'fa_enabled', __( 'Font Awesome enabled' ) )
			              ->set_option_value( '1' ),
		         ) )
				 ->add_tab( __( 'Profilbild-Default' ), array( // FDL-1344 fix issue with gravatar image 
					Field::make('text', 'avatar_image_default_url', __('Bild-URL'))
						->set_help_text(__('Wenn die Bild-URL des Gravatar.com auf garavatar.com zeigt, wird sie durch diese URL ersetzt')),
				) );
	
		// FDL-858 / FDL-941
		Container::make( 'term_meta', __( 'Category/Tag Properties' ) )
		         ->where( 'current_user_role', '=', 'administrator' )
		         ->or_where( function ( $condition ) {
			         $condition->where( 'term_taxonomy', '=', 'category' );
			         $condition->where( 'term_taxonomy', '=', 'post_tag' );
		         } )
		         ->add_fields( array(
			         Field::make( 'text', 'ad_zone', __( 'Ad Zone' ) )
			              ->set_help_text( __( 'Needs to be a valid ad zone from Theme Options > Ad Config.' ) ),
			         Field::make( 'text', 'ad_lokstadt', __( 'Ad Targeting \'lokstadt\'' ) )
		         ) );
		// Ad Zone for Pages
		Container::make( 'post_meta', 'Custom Data' )
		         ->where( 'current_user_role', '=', 'administrator' )
		         ->where( 'post_type', '=', 'page' )
		         ->add_fields( array(
			         Field::make( 'text', 'ad_zone', __( 'Ad Zone' ) )
			              ->set_help_text( __( 'Set the ad server zone for this page.' ) ),
			         Field::make( 'checkbox', 'ads_disabled', __( 'Disable Ads for this Page?' ) )
			              ->set_option_value( '1' ),
		         ) );
		// FIXME only wmn specific - move to wmn theme?
		// doesn't work for the subdomain based prod system
		// if ( get_blog_details()->path === '/wmn/' ) {
		Container::make( 'post_meta', 'Post Options' )
		         ->where( 'post_type', '=', 'post' )
		         ->set_context( 'side' )
		         ->add_fields( array(
			         Field::make( 'checkbox', 'ads_disabled', __( 'Disable Ads for this Post?' ) )
			              ->set_option_value( '1' ),
			         Field::make( 'select', 'source_content_type', __( 'Post Type (ContentType)' ) )
			              ->set_help_text( __( 'Decide how article is tracked for SEO (pageDefinition metric).' ) )
			              ->set_default_value( 'vna' )
			              ->set_options( array(
				              'vna'  => __( 'Vertical News Article' ),
				              've'   => __( 'Vertical Evergreen' ),
				              'coco' => __( 'Vertical Commercial Content' ),
				              'adv'  => __( 'Advertorials' ),
				              'vnac' => __( 'Vertical Commercial News' ),
				              'vm'   => __( 'Vertical Magazine' ),
				              'vmc'  => __( 'Vertical Commercial Magazine' ),
				              'vec'  => __( 'Vertical Commercian Evergreen' ),
				              'vps'  => __( 'Vertical Performance Story' ),
				              'vbs'  => __( 'Vertical Brand Story' )
			              ) )
		         ) );
		// }

	}
}

add_action( 'carbon_fields_register_fields', 'crb_attach_user_options' );
if ( ! function_exists( 'crb_attach_user_options' ) ) {
	function crb_attach_user_options() {
		Container::make( 'user_meta', 'VG WORT' )
		         ->add_fields( array(
			         Field::make( 'text', 'user_vg-wort_id', 'VG Wort ID' ),
			         Field::make( 'text', 'user_vg-wort_firstname', 'VG Wort Vorname' ),
			         Field::make( 'text', 'user_vg-wort_lastname', 'VG Wort Nachname' ),
		         ) );
	}
}

add_action( 'carbon_fields_register_fields', 'crb_attach_feature_video' );
function crb_attach_feature_video() {
	$uuid                 = 'featured-video-' . uniqid();
	$kaltura_video_button = '<button class="kaltura-video"><a class="iframe-lightbox-link-' . $uuid . ' kaltura-video featured-image-callback" id="kalturavideo" data-id="' . $uuid . '" href="#">' . __( 'Edit Video' ) . '</a></button>';
	Container::make( 'post_meta', __( 'Featured Video' ) )
	         ->where( 'post_type', '=', 'post' )
	         ->add_fields(
		         array(
			         Field::make( 'select', 'fd_featured_video_source', 'Video Source' )
			              ->set_help_text( __( 'Choose video source the enable Featured Video! If disabled, WordPress uses standard "Featured Image" feature.' ) )
			              ->add_options(
				              array(
					              'disabled' => 'Disabled',
					              'youtube'  => 'YouTube',
					              //'dailymotion' => 'Dailymotion',
					              'kaltura'  => 'Kaltura',
				              )
			              ),

			         Field::make( 'text', 'fd_featured_video_title', 'Title' )
			              ->set_help_text( __( 'Add a custom title to the Featured Video.' ) )
			              ->set_conditional_logic(
				              array(
					              'relation' => 'OR',
					              array(
						              'field' => 'fd_featured_video_source',
						              'value' => 'kaltura',
					              ),
					              array(
						              'field' => 'fd_featured_video_source',
						              'value' => 'youtube',
					              ),
				              )
			              ),


			         Field::make( 'text', 'fd_featured_video_caption', 'Caption' )
			              ->set_help_text( __( 'Add a custom caption to the Featured Video.' ) )
			              ->set_conditional_logic(
				              array(
					              'relation' => 'OR',
					              array(
						              'field' => 'fd_featured_video_source',
						              'value' => 'kaltura',
					              ),
					              array(
						              'field' => 'fd_featured_video_source',
						              'value' => 'youtube',
					              ),
				              )
			              ),

			         Field::make( 'text', 'fd_featured_video_yt_value', 'YouTube Embed Video-ID or URL' )
				         //->set_help_text(__('enter YouTube Embed Video ID'))
				          ->set_conditional_logic(
					         array(
						         array(
							         'field' => 'fd_featured_video_source',
							         'value' => 'youtube',
						         )
					         )
				         )
			              ->set_attribute( 'placeholder', '2zfqw8nhUwA' ),

			         Field::make( 'text', 'fd_featured_video_kaltura_value', 'Kaltura Video Shortcode' )
			              ->set_classes( $uuid )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'fd_featured_video_source',
						              'value' => 'kaltura',
					              )
				              )
			              ),

			         Field::make( 'html', 'fd_featured_video_kaltura_html' )
			              ->set_html( $kaltura_video_button )
			              ->set_classes( $uuid )
			              ->set_conditional_logic(
				              array(
					              array(
						              'field' => 'fd_featured_video_source',
						              'value' => 'kaltura',
					              )
				              )
			              ),

		         )
	         );
}
