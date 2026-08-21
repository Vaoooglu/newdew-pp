<?php
/**
 * Reusable recipe card.
 *
 * @package oxboxwise
 */

$heading_level = isset( $args['heading_level'] ) && in_array( (int) $args['heading_level'], array( 2, 3 ), true ) ? (int) $args['heading_level'] : 2;
$categories    = get_the_terms( get_the_ID(), 'recipe_category' );
$time          = function_exists( 'get_field' ) ? get_field( 'recipe_cooking_time' ) : get_post_meta( get_the_ID(), 'recipe_cooking_time', true );
$video_id      = get_post_meta( get_the_ID(), '_recipe_youtube_video_id', true );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'recipe-card' ); ?>>
	<a class="recipe-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large', array( 'sizes' => '(max-width: 600px) 50vw, (max-width: 1100px) 33vw, 280px' ) ); ?>
		<?php else : ?>
			<span class="recipe-card__placeholder">Без изображения</span>
		<?php endif; ?>
		<?php if ( $video_id ) : ?>
			<span class="recipe-card__video-label">Видео</span>
		<?php endif; ?>
	</a>
	<div class="recipe-card__body">
		<?php if ( ! is_wp_error( $categories ) && $categories ) : ?>
			<a class="recipe-card__category" href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a>
		<?php endif; ?>
		<?php printf( '<h%1$d class="recipe-card__title"><a href="%2$s">%3$s</a></h%1$d>', $heading_level, esc_url( get_permalink() ), esc_html( get_the_title() ) ); ?>
		<?php if ( $time ) : ?>
			<p class="recipe-card__time"><?php echo esc_html( $time ); ?></p>
		<?php endif; ?>
		<button class="recipe-card__favorite" type="button" aria-label="Добавить рецепт в избранное" data-recipe-id="<?php the_ID(); ?>" disabled>♡</button>
	</div>
</article>
