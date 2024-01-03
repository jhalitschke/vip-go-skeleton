<?php
/**
 * Newspack DBN Theme: Customizer
 *
 * @package Newspack
 */

/**
 * Extended Customizer Controls for DBN Theme
 * - archive_layout (added DBN Layout)
 * - post show excerpt
 *
 * To extend a control or section, you have first to remove the customizer object by it's $id
 * and then re-add your customized object with the same $id and $domain ('newspack')
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function newspack_dbn_customize_register( $wp_customize ) {

	/* extended archive layout options */
	$wp_customize->remove_control( 'archive_layout' );
	$wp_customize->add_control(
		'archive_layout',
		array(
			'type'    => 'radio',
			'label'   => esc_html__( 'Archive Layout', 'newspack' ),
			'choices' => array(
				'default'         => esc_html__( 'Default', 'newspack' ),
				'one-column'      => esc_html__( 'One column', 'newspack' ),
				'one-column-wide' => esc_html__( 'One column wide', 'newspack' ),
				'one-column-dbn'  => esc_html__( 'DBN Layout', 'newspack' ),
			),
			'section' => 'archive_options',
		)
	);

	// Add option to show excerpts for all posts.
	$wp_customize->add_setting(
		'post_show_excerpt',
		array(
			'default'           => false,
			'sanitize_callback' => 'newspack_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'post_show_excerpt',
		array(
			'type'    => 'checkbox',
			'label'   => esc_html__( 'Show excerpts for all posts', 'newspack' ),
			'section' => 'post_default_settings',
		)
	);
}

add_action( 'init', function () {
	add_action( 'customize_register', 'newspack_dbn_customize_register' );
} );
