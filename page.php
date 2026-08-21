<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

get_header();
?>

	<div id="primary" class="content-area">
		<?php while ( have_posts() ) : the_post();?>
            <h1><?=get_the_title();?></h1>
			<div class="text-content">
                <? the_content();?>
            </div>

		<? endwhile; ?>

	</div>

<?php
get_footer();
