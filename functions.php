<?php
/**
 * oxboxwise functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package oxboxwise
 */

/**
 * Theme settings
 */
require get_template_directory() . '/inc/theme-settings.php';

/**
 * ACF options
 */
require get_template_directory() . '/inc/acf-options.php';

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
require get_template_directory() . '/inc/widget-areas.php';

/**
 * Enqueue scripts and styles.
 */
require get_template_directory() . '/inc/enqueue-script-style.php';

/**
 * Add custom Post types
 */
require get_template_directory() . '/inc/post-types.php';

/**
 * Recipe fields and media helpers.
 */
require get_template_directory() . '/inc/recipe-admin.php';

/**
 * Recipe REST API.
 */
require get_template_directory() . '/inc/recipe-api.php';

/**
 * Telegram webhook for creating recipes.
 */
require get_template_directory() . '/inc/telegram-recipe-bot.php';

/**
 * Recipe search helpers.
 */
require get_template_directory() . '/inc/recipe-search.php';

/**
 * Add custom Guttenberg blocks with ACF
 */
//require get_template_directory() . '/inc/guttenblock-register.php';

/**
 * Add Ajax request
 */
require get_template_directory() . '/inc/ajax-request.php';
/**
 * Add fields to admin
 */
require get_template_directory() . '/inc/admin-fields.php';


/**
 * Add custom excerpt
 */
//require get_template_directory() . '/inc/kama_excerpt.php';

/**
 * Add custom pagination
 */
require get_template_directory() . '/inc/pagination.php';

/**
 * Add custom pagination query
 */
//require get_template_directory() . '/inc/pagination_query.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
//if ( defined( 'JETPACK__VERSION' ) ) {
//	require get_template_directory() . '/inc/jetpack.php';
//}

/**
 * Load WooCommerce compatibility file.
 */
//if ( class_exists( 'WooCommerce' ) ) {
//	require get_template_directory() . '/inc/woocommerce.php';
//}
