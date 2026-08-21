<?php
/**
 * Lazy YouTube embed. The iframe is created only after user interaction.
 *
 * @package oxboxwise
 */

$video_id = isset( $args['video_id'] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', $args['video_id'] ) : '';
if ( ! $video_id ) {
	return;
}

$preview = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : 'https://i.ytimg.com/vi/' . rawurlencode( $video_id ) . '/hqdefault.jpg';
?>

<div class="recipe-video" data-youtube-id="<?php echo esc_attr( $video_id ); ?>">
	<button class="recipe-video__button" type="button" aria-label="Воспроизвести видео: <?php echo esc_attr( get_the_title() ); ?>">
		<img src="<?php echo esc_url( $preview ); ?>" alt="" loading="lazy" width="1280" height="720">
		<span class="recipe-video__play" aria-hidden="true">▶</span>
	</button>
</div>
