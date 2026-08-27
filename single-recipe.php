<?php
/**
 * Single recipe template.
 *
 * @package oxboxwise
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'recipe-single' ); ?>>
		<div class="container recipe-single__container">
			<a class="recipe-single__back" href="<?php echo esc_url( get_post_type_archive_link( 'recipe' ) ); ?>">← Все рецепты</a>
			<div class="recipe-single__hero">
				<div class="recipe-single__media">
					<?php
					$video_id    = oxboxwise_get_recipe_video_id();
					$youtube_url = oxboxwise_get_recipe_youtube_url();
					if ( $video_id ) {
						get_template_part( 'template-parts/recipe/video', null, array( 'video_id' => $video_id ) );
					} elseif ( $youtube_url ) {
						get_template_part( 'template-parts/recipe/youtube-link', null, array( 'youtube_url' => $youtube_url ) );
					} elseif ( has_post_thumbnail() ) {
						?>
						<figure class="recipe-single__image">
							<?php the_post_thumbnail( 'large', array( 'sizes' => '(max-width: 767px) 100vw, 58vw' ) ); ?>
						</figure>
						<?php
					} else {
						?>
						<div class="recipe-single__image recipe-single__image--empty"><span aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( get_the_title(), 0, 1 ) : substr( get_the_title(), 0, 1 ) ); ?></span><small>Изображение не добавлено</small></div>
						<?php
					}
					?>
				</div>
				<header class="recipe-single__header">
					<p class="eyebrow">Рецепт</p>
					<h1><?php the_title(); ?></h1>
					<?php get_template_part( 'template-parts/recipe/meta' ); ?>
					<div class="recipe-single__actions" data-recipe-card data-recipe-id="<?php the_ID(); ?>" data-recipe-title="<?php echo esc_attr( get_the_title() ); ?>" data-recipe-url="<?php echo esc_url( get_permalink() ); ?>" data-recipe-image="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ); ?>">
						<button type="button" data-favorite-toggle aria-pressed="false"><svg aria-hidden="true"><use href="#icon-heart"></use></svg><span>В избранное</span></button>
						<button type="button" data-share-recipe><svg aria-hidden="true"><use href="#icon-share"></use></svg><span>Поделиться</span></button>
					</div>
					<p class="recipe-single__action-status" data-action-status role="status" aria-live="polite"></p>
				</header>
			</div>

			<div class="recipe-single__body">
				<?php get_template_part( 'template-parts/recipe/content' ); ?>
			</div>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<section class="recipe-single__comments" aria-label="Комментарии">
					<?php comments_template(); ?>
				</section>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
