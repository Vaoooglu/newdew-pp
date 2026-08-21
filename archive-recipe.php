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
			<p class="eyebrow">Личная коллекция</p>
			<h1 id="recipe-archive-title">
				<?php
				if ( is_tax() ) {
					single_term_title();
				} else {
					post_type_archive_title();
				}
				?>
			</h1>
			<p>Быстрый доступ к рецептам, которые хочется приготовить снова.</p>
			<div class="recipe-archive__search"><?php get_search_form(); ?></div>
		</header>
		<?php get_template_part( 'template-parts/recipe/filters' ); ?>

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
			<div class="recipe-empty"><span aria-hidden="true">⌕</span><h2>Рецептов не найдено</h2><p>Попробуйте изменить фильтры или поисковый запрос.</p></div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
