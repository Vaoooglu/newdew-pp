<?php
/**
 * Recipe metadata.
 *
 * @package oxboxwise
 */

$time       = function_exists( 'get_field' ) ? get_field( 'recipe_cooking_time' ) : get_post_meta( get_the_ID(), 'recipe_cooking_time', true );
$portions   = function_exists( 'get_field' ) ? get_field( 'recipe_portions' ) : get_post_meta( get_the_ID(), 'recipe_portions', true );
$categories = get_the_terms( get_the_ID(), 'recipe_category' );

if ( ! $time && ! $portions && ( ! $categories || is_wp_error( $categories ) ) ) {
	return;
}
?>

<dl class="recipe-meta">
	<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
		<div class="recipe-meta__item">
			<dt>Категория</dt>
			<dd><?php echo wp_kses_post( get_the_term_list( get_the_ID(), 'recipe_category', '', ', ' ) ); ?></dd>
		</div>
	<?php endif; ?>
	<?php if ( $time ) : ?>
		<div class="recipe-meta__item"><dt>Время</dt><dd><?php echo esc_html( $time ); ?></dd></div>
	<?php endif; ?>
	<?php if ( $portions ) : ?>
		<div class="recipe-meta__item"><dt>Порции</dt><dd><?php echo esc_html( $portions ); ?></dd></div>
	<?php endif; ?>
</dl>
