<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package oxboxwise
 */

get_header();
?>



		<?php while ( have_posts() ) :	the_post();?>
			<h1><?=get_the_title();?></h1>
			<div class="text-content">
				<? the_content();?>
			</div>


			<? if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;?>

		<? endwhile; ?>



<?php
get_footer();
