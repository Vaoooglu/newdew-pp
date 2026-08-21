<?php
/**
 * Recipe search form.
 *
 * @package oxboxwise
 */

$search_id = wp_unique_id( 'recipe-search-' );
?>

<form role="search" method="get" class="recipe-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>">Поиск рецептов</label>
	<span class="recipe-search-form__icon" aria-hidden="true"><svg><use href="#icon-search"></use></svg></span>
	<input id="<?php echo esc_attr( $search_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Название или ингредиент" autocomplete="off">
	<input type="hidden" name="post_type" value="recipe">
	<button type="submit">Найти</button>
</form>
