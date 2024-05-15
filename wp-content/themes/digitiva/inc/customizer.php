<?php
/**
 * Customizer
 * 
 * @package WordPress
 * @subpackage digitiva
 * @since digitiva 1.0
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function digitiva_customize_register( $wp_customize ) {
	$wp_customize->add_section( new Digitiva_Upsell_Section($wp_customize,'upsell_section',array(
		'title'            => __( 'Digitiva Pro', 'digitiva' ),
		'button_text'      => __( 'Upgrade Pro', 'digitiva' ),
		'url'              => esc_url( DIGITIVA_BUY_NOW ),
		'priority'         => 0,
	)));
}
add_action( 'customize_register', 'digitiva_customize_register' );

/**
 * Enqueue script for custom customize control.
 */
function digitiva_custom_control_scripts() {
	wp_enqueue_script( 'digitiva-custom-controls-js', get_template_directory_uri() . '/assets/js/custom-controls.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), '1.0', true );
}
add_action( 'customize_controls_enqueue_scripts', 'digitiva_custom_control_scripts' );