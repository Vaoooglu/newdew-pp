<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
if ( ! function_exists( 'oxboxwise_setup' ) ) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function oxboxwise_setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on oxboxwise, use a find and replace
         * to change 'oxboxwise' to the name of your theme in all the template files.
         */
        load_theme_textdomain( 'oxboxwise', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support( 'post-thumbnails' );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(
            array(
                'menu_main' => esc_html__( 'Главное', 'oxboxwise' ),
                'menu_footer' => esc_html__( 'В подвале', 'oxboxwise' ),
            )
        );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
            )
        );

        // Set up the WordPress core custom background feature.
        add_theme_support(
            'custom-background',
            apply_filters(
                'oxboxwise_custom_background_args',
                array(
                    'default-color' => 'ffffff',
                    'default-image' => '',
                )
            )
        );

        // Add theme support for selective refresh for widgets.
        add_theme_support( 'customize-selective-refresh-widgets' );

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support(
            'custom-logo',
            array(
                'height'      => 250,
                'width'       => 250,
                'flex-width'  => true,
                'flex-height' => true,
            )
        );
    }
endif;
add_action( 'after_setup_theme', 'oxboxwise_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function oxboxwise_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'oxboxwise_content_width', 640 );
}
add_action( 'after_setup_theme', 'oxboxwise_content_width', 0 );


/**
 * Admin footer modification
 */
function remove_footer_admin ()
{
    echo '<span id="footer-thankyou">Developed by: <a href="mailto:oxbox@oxbox.ru">oxbox.ru </a></span>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

/**
 *  Remove emoji Wordpress
 */
remove_action("wp_head", "print_emoji_detection_script", 7);
remove_action("admin_print_scripts", "print_emoji_detection_script");
remove_action("wp_print_styles", "print_emoji_styles");
remove_action("admin_print_styles", "print_emoji_styles");

/**
 *  Security  for WP from change files of users adminpanel
 */
## Полное Удаление версии WP
## Также нужно удалить файл readme.html в корне сайта
//remove_action('wp_head', 'wp_generator'); // из заголовка
//add_filter('the_generator', '__return_empty_string');
// Отключим вывод ошибок на странице авторизации
add_filter('login_errors', 'login_obscure_func');
function login_obscure_func(){
    return 'Ошибка: вы ввели неправильный логин или пароль.';
}
/**
 *  Удаляет "Рубрика: ", "Метка: " и т.д. из заголовка архива
 */
add_filter( 'get_the_archive_title', function( $title ){
    return preg_replace('~^[^:]+: ~', '', $title );
});


/**
 *  Custom thumbnail size
 */
//if ( function_exists( 'add_image_size' ) ) {
//    add_image_size( 'wise_img', 460, 320, array( 'center', 'center' ) );
//    add_image_size( 'wise_gal', 420, 260, array( 'center', 'center' ) );
//}

/**
 * Custom Form for search
 * for enable use shortcode
 *   <?=do_shortcode('[wise_search]')?>
 */
function wise_searchform_custom( $form ) {
    $form = '<form action="#" id="search-form" class="search-form" method="get"><div class="search-form__input"><input type="text" id="s" name="s" placeholder="Введите слово для поиска" onfocus="if (this.value == \'Введите слово для поиска\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Введите слово для поиска\';}" ><button type="submit"><svg id="icon-search" viewBox="0 0 12 12"><path d="M11.3,10.3L8.2,7.1C8.7,6.4,9,5.6,9,4.7C9,2.4,7,0.4,4.7,0.4c-2.3,0-4.2,1.9-4.2,4.2s1.9,4.2,4.2,4.2 c0.9,0,1.7-0.3,2.4-0.8l3.1,3.1c0.1,0.1,0.3,0.2,0.5,0.2s0.4-0.1,0.5-0.2C11.6,11,11.6,10.6,11.3,10.3z M2,4.7 c0-1.5,1.2-2.8,2.8-2.8s2.8,1.2,2.8,2.8S6.2,7.4,4.7,7.4S2,6.2,2,4.7z"> </path></svg></button></div></form>';
    return $form;
}
add_shortcode('wise_search', 'wise_searchform_custom');

/**
 *  change post per page for CPT services
 */
function wisedev_number_displayed_posts($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if (is_post_type_archive('vakances')) {
        $query->set('posts_per_page', 100);
        return;
    }
}
add_action('pre_get_posts', 'wisedev_number_displayed_posts', 1);

if( function_exists('bcn_display') ) {
    add_filter('bcn_breadcrumb_title', 'my_breadcrumb_title_swapper', 3, 10);
    function my_breadcrumb_title_swapper($title, $type, $id)
    {
        if (in_array('home', $type)) {
            $title = __('Главная');
        }
        return $title;
    }
}
