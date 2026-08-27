<?php
/**
 * Reusable recipe card.
 *
 * @package oxboxwise
 */

$heading_level = isset( $args['heading_level'] ) && in_array( (int) $args['heading_level'], array( 2, 3 ), true ) ? (int) $args['heading_level'] : 2;
$categories    = get_the_terms( get_the_ID(), 'recipe_category' );
$time          = function_exists( 'get_field' ) ? get_field( 'recipe_cooking_time' ) : get_post_meta( get_the_ID(), 'recipe_cooking_time', true );
$video_id      = oxboxwise_get_recipe_video_id();
$youtube_url   = $video_id ? '' : oxboxwise_get_recipe_youtube_url();
$media_label   = $video_id ? 'Видео' : ( $youtube_url ? 'YouTube' : '' );
$image_url     = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : '';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'recipe-card' ); ?> data-recipe-card data-recipe-id="<?php the_ID(); ?>" data-recipe-title="<?php echo esc_attr( get_the_title() ); ?>" data-recipe-url="<?php echo esc_url( get_permalink() ); ?>" data-recipe-image="<?php echo esc_url( $image_url ); ?>">
	<a class="recipe-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large', array( 'sizes' => '(max-width: 600px) 50vw, (max-width: 1100px) 33vw, 280px' ) ); ?>
		<?php else : ?>
			<span class="recipe-card__placeholder"><span aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( get_the_title(), 0, 1 ) : substr( get_the_title(), 0, 1 ) ); ?></span><small>Без изображения</small></span>
		<?php endif; ?>
		<?php if ( $media_label ) : ?>
			<span class="recipe-card__video-label"><svg aria-hidden="true"><use href="#icon-play"></use></svg> <?php echo esc_html( $media_label ); ?></span>
		<?php endif; ?>
	</a>
	<div class="recipe-card__body">
		<?php if ( ! is_wp_error( $categories ) && $categories ) : ?>
			<a class="recipe-card__category" href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a>
		<?php endif; ?>
		<?php printf( '<h%1$d class="recipe-card__title"><a href="%2$s">%3$s</a></h%1$d>', $heading_level, esc_url( get_permalink() ), esc_html( get_the_title() ) ); ?>
		<?php if ( $time ) : ?><p class="recipe-card__time"><svg aria-hidden="true"><use href="#icon-clock"></use></svg><?php echo esc_html( $time ); ?></p><?php endif; ?>
		<button class="recipe-card__favorite" type="button" aria-label="Добавить рецепт в избранное" aria-pressed="false" data-favorite-toggle><svg aria-hidden="true"><use href="#icon-heart"></use></svg></button>
	</div>
</article>
