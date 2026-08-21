<?php
/**
 * Recipe archive template.
 *
 * @package oxboxwise
 */

get_header();
?>

<section class="recipe-archive" aria-labelledby="recipe-archive-title">
	<div class="container">
		<header class="recipe-archive__header">
			<h1 id="recipe-archive-title">
				<?php
				if ( is_tax() ) {
					single_term_title();
				} else {
					post_type_archive_title();
				}
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="recipe-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/recipe/card', null, array( 'heading_level' => 2 ) );
				endwhile;
				?>
			</div>
			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<p class="recipe-empty">Рецептов пока нет.</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
