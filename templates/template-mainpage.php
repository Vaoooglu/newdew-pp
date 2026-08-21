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
				<h1 id="home-recipe-search-title">Что хочешь приготовить?</h1>
				<form role="search" method="get" class="recipe-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="screen-reader-text" for="home-recipe-search">Поиск рецептов</label>
					<input id="home-recipe-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Название, категория или ингредиент">
					<input type="hidden" name="post_type" value="recipe">
					<button type="submit">Найти</button>
				</form>
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
			<section class="home-recipe-categories" aria-labelledby="home-recipe-categories-title">
				<div class="container">
					<h2 id="home-recipe-categories-title">Категории</h2>
					<ul>
						<?php foreach ( $recipe_categories as $recipe_category ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $recipe_category ) ); ?>"><?php echo esc_html( $recipe_category->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<section class="home-recipes" aria-labelledby="latest-recipes-title">
			<div class="container">
				<h2 id="latest-recipes-title">Последние рецепты</h2>
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

		<section class="home-favorites" aria-labelledby="home-favorites-title" data-favorites-placeholder>
			<div class="container">
				<h2 id="home-favorites-title">Избранное</h2>
				<p>Здесь позже появятся рецепты, сохранённые на этом устройстве.</p>
			</div>
		</section>
	<?php endwhile; ?>


<?php
get_footer();
