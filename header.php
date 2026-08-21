<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package oxboxwise
 */
?>
    <!doctype html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="https://gmpg.org/xfn/11">

        <?php wp_head(); ?>
    </head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wrapper">
    <div class="content">
<?php get_template_part( 'template-parts/content', 'header' ); ?>
    <main id="main-content" class="main">
<?php if ((is_front_page() || is_home())) {} else {
    get_template_part( 'template-parts/content', 'breadcrumbs' );
} ?>
