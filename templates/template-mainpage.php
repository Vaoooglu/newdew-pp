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

	<?php while ( have_posts() ) : the_post(); ?>
		<section class="home-recipe-search" aria-labelledby="home-recipe-search-title">
			<div class="container">
				<p class="eyebrow">Моя книга рецептов</p>
				<h1 id="home-recipe-search-title">Что хочешь приготовить?</h1>
				<p class="home-recipe-search__intro">Любимые блюда, заметки и видео — без долгих поисков.</p>
				<?php get_search_form(); ?>
				<div class="home-recipe-search__quick-links">
					<span>Например:</span>
					<a href="<?php echo esc_url( add_query_arg( array( 's' => 'курица', 'post_type' => 'recipe' ), home_url( '/' ) ) ); ?>">курица</a>
					<a href="<?php echo esc_url( add_query_arg( array( 's' => 'быстро', 'post_type' => 'recipe' ), home_url( '/' ) ) ); ?>">быстро</a>
					<a href="<?php echo esc_url( add_query_arg( array( 's' => 'десерт', 'post_type' => 'recipe' ), home_url( '/' ) ) ); ?>">десерт</a>
				</div>
			</div>
		</section>

		<?php
		$recipe_categories = get_terms(
			array(
				'taxonomy'   => 'recipe_category',
				'hide_empty' => true,
			)
		);
		if ( $recipe_categories && ! is_wp_error( $recipe_categories ) ) :
			?>
			<section id="categories" class="home-recipe-categories" aria-labelledby="home-recipe-categories-title">
				<div class="container">
					<div class="section-heading">
						<div><p class="eyebrow">Быстрый выбор</p><h2 id="home-recipe-categories-title">Категории</h2></div>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'recipe' ) ); ?>">Смотреть все</a>
					</div>
					<ul class="category-list">
						<?php foreach ( $recipe_categories as $recipe_category ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $recipe_category ) ); ?>"><span><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( $recipe_category->name, 0, 1 ) : substr( $recipe_category->name, 0, 1 ) ); ?></span><?php echo esc_html( $recipe_category->name ); ?><small><?php echo esc_html( $recipe_category->count ); ?></small></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<section class="home-recipes" aria-labelledby="latest-recipes-title">
			<div class="container">
				<div class="section-heading"><div><p class="eyebrow">Недавно добавлено</p><h2 id="latest-recipes-title">Последние рецепты</h2></div><a href="<?php echo esc_url( get_post_type_archive_link( 'recipe' ) ); ?>">Все рецепты</a></div>
				<?php
				$latest_recipes = new WP_Query(
					array(
						'post_type'           => 'recipe',
						'post_status'         => 'publish',
						'posts_per_page'      => 8,
						'ignore_sticky_posts' => true,
					)
				);
				if ( $latest_recipes->have_posts() ) :
					?>
					<div class="recipe-grid">
						<?php
						while ( $latest_recipes->have_posts() ) :
							$latest_recipes->the_post();
							get_template_part( 'template-parts/recipe/card', null, array( 'heading_level' => 3 ) );
						endwhile;
						?>
					</div>
				<?php else : ?>
					<p class="recipe-empty">Рецепты появятся здесь после публикации.</p>
				<?php endif; ?>
				<?php wp_reset_postdata(); ?>
			</div>
		</section>

		<section id="favorites" class="home-favorites" aria-labelledby="home-favorites-title" data-favorites-section>
			<div class="container">
				<div class="section-heading"><div><p class="eyebrow">Всегда под рукой</p><h2 id="home-favorites-title">Избранное</h2></div></div>
				<div class="favorites-list" data-favorites-list></div>
				<div class="favorites-empty" data-favorites-empty><svg aria-hidden="true"><use href="#icon-heart"></use></svg><p>Нажимайте на сердечко в карточке — рецепт сохранится на этом устройстве.</p></div>
			</div>
		</section>
	<?php endwhile; ?>


<?php
get_footer();
