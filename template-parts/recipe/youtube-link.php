<?php
/**
 * Recipe YouTube preview that starts an iframe player on demand.
 *
 * @package oxboxwise
 */

$youtube_url = isset( $args['youtube_url'] ) ? esc_url_raw( $args['youtube_url'] ) : '';
$parts       = wp_parse_url( $youtube_url );
$video_id    = '';
if ( is_array( $parts ) && ! empty( $parts['query'] ) ) {
	parse_str( $parts['query'], $query );
	$video_id = isset( $query['v'] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $query['v'] ) : '';
}
if ( ! $youtube_url || 11 !== strlen( $video_id ) ) {
	return;
}

$embed_url = 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id ) . '?autoplay=1&rel=0';
?>

<div class="recipe-youtube-player" data-recipe-youtube-player data-youtube-embed="<?php echo esc_url( $embed_url ); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="recipe-single__image">
			<?php the_post_thumbnail( 'large', array( 'sizes' => '(max-width: 767px) 100vw, 58vw' ) ); ?>
		</figure>
	<?php endif; ?>
	<button class="recipe-media-play" type="button" data-recipe-youtube-play aria-label="Воспроизвести видео с YouTube" data-video-title="<?php echo esc_attr( get_the_title() ); ?>">
		<svg aria-hidden="true"><use href="#icon-play"></use></svg>
	</button>
	<noscript><a href="<?php echo esc_url( $youtube_url ); ?>" target="_blank" rel="noopener noreferrer">Открыть видео на YouTube</a></noscript>
</div>
