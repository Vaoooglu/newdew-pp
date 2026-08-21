<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
/**
 * Enqueue scripts and styles.
 */
if ( ! defined( '_S_VERSION' ) ) {
    // Replace the version number of the theme on each release.
    define( '_S_VERSION', '1.0.0' );
}
function oxboxwise_scripts() {
    wp_enqueue_style( 'oxboxwise-style', get_stylesheet_uri(), array(), _S_VERSION );
    wp_style_add_data( 'oxboxwise-style', 'rtl', 'replace' );
	wp_enqueue_style( 'oxboxwise-fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'oxboxwise-recipe-css', get_template_directory_uri() . '/css/recipe.css', array(), _S_VERSION, false );
	wp_enqueue_script( 'oxboxwise-site', get_template_directory_uri() . '/js/site.js', array(), _S_VERSION, true );
	if ( is_singular( 'recipe' ) ) {
		wp_enqueue_script( 'oxboxwise-recipe-video', get_template_directory_uri() . '/js/recipe-video.js', array(), _S_VERSION, true );
	}
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'oxboxwise_scripts' );

/**
 * Preconnect only to the two Google Fonts origins used by the theme.
 *
 * @param array  $urls          Resource hints.
 * @param string $relation_type Hint type.
 * @return array
 */
function oxboxwise_font_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'oxboxwise_font_resource_hints', 10, 2 );

function wpassist_remove_block_library_css(){
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'gtranslate-style' );
}
add_action( 'wp_enqueue_scripts', 'wpassist_remove_block_library_css' );
