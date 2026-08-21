<?php
/**
 * Recipe archive/search filters.
 *
 * @package oxboxwise
 */

$categories  = get_terms( array( 'taxonomy' => 'recipe_category', 'hide_empty' => true ) );
$ingredients = get_terms( array( 'taxonomy' => 'recipe_ingredient', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 60 ) );
$tags         = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => true, 'number' => 40 ) );
$selected_category = isset( $_GET['recipe_category_filter'] ) ? sanitize_title( wp_unslash( $_GET['recipe_category_filter'] ) ) : '';
$selected_tag      = isset( $_GET['recipe_tag'] ) ? sanitize_title( wp_unslash( $_GET['recipe_tag'] ) ) : '';
$selected_ingredients = isset( $_GET['ingredients'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['ingredients'] ) ) : array();

if ( is_tax( 'recipe_category' ) && ! $selected_category ) {
	$selected_category = get_queried_object()->slug;
}

$active_count = count( array_filter( $selected_ingredients ) ) + ( $selected_category ? 1 : 0 ) + ( $selected_tag ? 1 : 0 );
$archive_url  = get_post_type_archive_link( 'recipe' );
$base_args    = array();
if ( is_search() ) {
	$base_args['s']         = get_search_query();
	$base_args['post_type'] = 'recipe';
}
if ( $selected_ingredients ) {
	$base_args['ingredients'] = $selected_ingredients;
}
if ( $selected_tag ) {
	$base_args['recipe_tag'] = $selected_tag;
}
$all_categories_url = add_query_arg( $base_args, $archive_url );
$reset_url          = is_search() ? add_query_arg( array( 's' => get_search_query(), 'post_type' => 'recipe' ), home_url( '/' ) ) : $archive_url;
?>

<div class="recipe-filter-bar">
	<div class="recipe-filter-bar__categories" aria-label="Категории рецептов">
		<a class="filter-chip<?php echo $selected_category ? '' : ' is-active'; ?>" href="<?php echo esc_url( $all_categories_url ); ?>">Все</a>
		<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
			<?php foreach ( $categories as $category ) : ?>
				<?php $category_url = add_query_arg( array_merge( $base_args, array( 'recipe_category_filter' => $category->slug ) ), $archive_url ); ?>
				<a class="filter-chip<?php echo $selected_category === $category->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( $category_url ); ?>"><?php echo esc_html( $category->name ); ?></a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<button class="filter-trigger" type="button" data-panel-open="filter-panel" aria-controls="filter-panel" aria-expanded="false">
		<svg aria-hidden="true"><use href="#icon-filter"></use></svg>
		<span>Фильтры<?php echo $active_count ? ' · ' . esc_html( $active_count ) : ''; ?></span>
	</button>
</div>

<section id="filter-panel" class="site-panel site-panel--filters" role="dialog" aria-modal="true" aria-labelledby="filter-panel-title" aria-hidden="true" hidden>
	<div class="site-panel__handle" aria-hidden="true"></div>
	<div class="site-panel__header">
		<h2 id="filter-panel-title">Фильтры</h2>
		<button class="icon-button" type="button" data-panel-close aria-label="Закрыть фильтры"><svg aria-hidden="true"><use href="#icon-close"></use></svg></button>
	</div>
	<form class="recipe-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'recipe' ) ); ?>">
		<?php if ( is_search() ) : ?>
			<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
		<?php endif; ?>
		<input type="hidden" name="post_type" value="recipe">

		<fieldset>
			<legend>Категория</legend>
			<select name="recipe_category_filter">
				<option value="">Все категории</option>
				<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->slug ); ?>"<?php selected( $selected_category, $category->slug ); ?>><?php echo esc_html( $category->name ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</fieldset>

		<?php if ( $ingredients && ! is_wp_error( $ingredients ) ) : ?>
			<fieldset>
				<legend>Что есть под рукой?</legend>
				<p class="recipe-filters__hint">Рецепт должен содержать все выбранные ингредиенты.</p>
				<div class="recipe-filters__options">
					<?php foreach ( $ingredients as $ingredient ) : ?>
						<label class="filter-option">
							<input type="checkbox" name="ingredients[]" value="<?php echo esc_attr( $ingredient->slug ); ?>"<?php checked( in_array( $ingredient->slug, $selected_ingredients, true ) ); ?>>
							<span><?php echo esc_html( $ingredient->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
			<fieldset>
				<legend>Тег</legend>
				<select name="recipe_tag">
					<option value="">Все теги</option>
					<?php foreach ( $tags as $tag ) : ?>
						<option value="<?php echo esc_attr( $tag->slug ); ?>"<?php selected( $selected_tag, $tag->slug ); ?>><?php echo esc_html( $tag->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</fieldset>
		<?php endif; ?>

		<div class="recipe-filters__actions">
			<a href="<?php echo esc_url( $reset_url ); ?>">Сбросить</a>
			<button type="submit">Показать рецепты</button>
		</div>
	</form>
</section>
