<?php
/**
 * Main recipe content and optional personal data.
 *
 * @package oxboxwise
 */

$note        = function_exists( 'get_field' ) ? get_field( 'recipe_note' ) : get_post_meta( get_the_ID(), 'recipe_note', true );
$ingredients = get_the_term_list( get_the_ID(), 'recipe_ingredient', '', ', ' );
$tags        = get_the_tag_list( '', ', ' );
?>

<div class="recipe-content text-content">
	<?php the_content(); ?>
</div>

<?php if ( $note ) : ?>
	<aside class="recipe-note" aria-labelledby="recipe-note-title">
		<h2 id="recipe-note-title">Личная заметка</h2>
		<p><?php echo wp_kses_post( nl2br( esc_html( $note ) ) ); ?></p>
	</aside>
<?php endif; ?>

<?php if ( $ingredients ) : ?>
	<section class="recipe-taxonomy" aria-labelledby="recipe-ingredients-title">
		<h2 id="recipe-ingredients-title">Основные ингредиенты</h2>
		<p><?php echo wp_kses_post( $ingredients ); ?></p>
	</section>
<?php endif; ?>

<?php if ( $tags ) : ?>
	<section class="recipe-taxonomy" aria-labelledby="recipe-tags-title">
		<h2 id="recipe-tags-title">Теги</h2>
		<p><?php echo wp_kses_post( $tags ); ?></p>
	</section>
<?php endif; ?>
