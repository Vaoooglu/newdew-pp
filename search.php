<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package oxboxwise
 */

get_header();
?>

	<section id="primary" class="content-area recipe-search-results">
		<div class="container">
			<header class="recipe-search-results__header">
				<p class="eyebrow">Поиск по библиотеке</p>

		<?php if ( have_posts() ) : ?>


				<h1 class="page-title">
					<?php
					/* translators: %s: search query. */
					printf( esc_html__( 'Результаты поиска: %s', 'oxboxwise' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
					?>
				</h1>
				<p>Найденные рецепты можно уточнить по категории, ингредиентам или тегу.</p>
			</header>
			<?php get_template_part( 'template-parts/recipe/filters' ); ?>

			<div class="recipe-grid">
			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				if ( 'recipe' === get_post_type() ) {
					get_template_part( 'template-parts/recipe/card', null, array( 'heading_level' => 2 ) );
				} else {
					get_template_part( 'template-parts/content', 'search' );
				}

			endwhile;
			?>
			</div>
			<?php

			the_posts_navigation();

		else :
			?>
				<h1 class="page-title">Ничего не найдено</h1>
				<p>По запросу «<?php echo esc_html( get_search_query() ); ?>» рецептов пока нет.</p>
			</header>
			<?php get_template_part( 'template-parts/recipe/filters' ); ?>
			<div class="recipe-empty"><span aria-hidden="true">⌕</span><h2>Попробуйте другой запрос</h2><p>Можно искать по названию, категории, ингредиенту или тегу.</p><?php get_search_form(); ?></div>
			<?php

		endif;
		?>

		</div>
	</section><!-- #primary -->

<?php
get_footer();
