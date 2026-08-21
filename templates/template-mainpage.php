<?php
/**
 * Template Name: Главная
 * Template Post Type: page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

get_header();
?>

		<?php while ( have_posts() ) : the_post();?>

<!--			--><?// get_template_part( 'template-parts/content', 'page' );?>
    <section class="banner" style="background-image: url(<?=get_template_directory_uri();?>/img/banner.jpg);">
        <div class="container">
            <h1>DevOps Services that empower your business</h1>
            <p>Cloud native services provided by the expert team of our company</p>
        </div>
    </section>
            <? endwhile; ?>


<?php
get_footer();
