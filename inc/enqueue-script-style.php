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
    wp_enqueue_style('oxboxwise-lib-css', get_template_directory_uri() .'/css/libs.min.css', array(), _S_VERSION, false);
    wp_enqueue_style( 'oxboxwise-style', get_stylesheet_uri(), array(), _S_VERSION );
    wp_style_add_data( 'oxboxwise-style', 'rtl', 'replace' );
    if(is_page_template( 'templates/template-configurator.php' )){
    }
    wp_enqueue_style('oxboxwise-critical-css', get_template_directory_uri() .'/css/critical.css', array(), _S_VERSION, false);
//    wp_enqueue_style('oxboxwise-intlTelInput-css', get_template_directory_uri() .'/css/intlTelInput.css', array(), _S_VERSION, false);
    wp_enqueue_style('oxboxwise-custom-css', get_template_directory_uri() .'/css/custom.css', array(), _S_VERSION, false);
    wp_enqueue_script( 'jquery' );
//    wp_enqueue_script( 'oxboxwise-swiper-script', get_template_directory_uri() . '/js/swiper-bundle.min.js', array(), null, true );
    wp_enqueue_script( 'oxboxwise-mask-script', get_template_directory_uri() . '/js/jquery.mask.min.js', array( 'jquery' ), '1.14.16', true );
    wp_enqueue_script( 'oxboxwise-validate-script', get_template_directory_uri() . '/js/jquery.validate.min.js', array( 'jquery' ), '1.19.3', true );
//    wp_enqueue_script( 'oxboxwise-intlTelInput-script', get_template_directory_uri() . '/js/intlTelInput-jquery.min.js', array(), '1.19.3', true );
    wp_enqueue_script( 'oxboxwise-main-script', get_template_directory_uri() . '/js/main.js', array( 'jquery', 'oxboxwise-mask-script', 'oxboxwise-validate-script' ), _S_VERSION, true );
    wp_enqueue_script( 'oxboxwise-custom-script', get_template_directory_uri() . '/js/custom.js', array( 'jquery' ), _S_VERSION, true );
	if ( is_post_type_archive( 'recipe' ) || is_singular( 'recipe' ) || is_tax( array( 'recipe_category', 'recipe_ingredient' ) ) || is_search() || is_page_template( 'templates/template-mainpage.php' ) ) {
		wp_enqueue_style( 'oxboxwise-recipe-css', get_template_directory_uri() . '/css/recipe.css', array(), _S_VERSION, false );
	}
	if ( is_singular( 'recipe' ) ) {
		wp_enqueue_script( 'oxboxwise-recipe-video', get_template_directory_uri() . '/js/recipe-video.js', array(), _S_VERSION, true );
	}
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'oxboxwise_scripts' );

function wpassist_remove_block_library_css(){
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'gtranslate-style' );
}
add_action( 'wp_enqueue_scripts', 'wpassist_remove_block_library_css' );
