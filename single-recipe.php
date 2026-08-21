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
			<header class="recipe-single__header">
				<?php get_template_part( 'template-parts/recipe/meta' ); ?>
				<h1><?php the_title(); ?></h1>
			</header>

			<?php
			$video_id = get_post_meta( get_the_ID(), '_recipe_youtube_video_id', true );
			if ( $video_id ) {
				get_template_part( 'template-parts/recipe/video', null, array( 'video_id' => $video_id ) );
			} elseif ( has_post_thumbnail() ) {
				?>
				<figure class="recipe-single__image">
					<?php the_post_thumbnail( 'large', array( 'sizes' => '(max-width: 768px) 100vw, 960px' ) ); ?>
				</figure>
				<?php
			}

			get_template_part( 'template-parts/recipe/content' );
			?>

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
