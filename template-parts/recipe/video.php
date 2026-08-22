<?php
/**
 * Recipe video stored as a WordPress media attachment.
 *
 * @package oxboxwise
 */

$video_id = isset( $args['video_id'] ) ? absint( $args['video_id'] ) : 0;
if ( ! $video_id || 'attachment' !== get_post_type( $video_id ) ) {
	return;
}

$video_url = wp_get_attachment_url( $video_id );
$mime_type = get_post_mime_type( $video_id );
if ( ! $video_url || ! is_string( $mime_type ) || 0 !== strpos( $mime_type, 'video/' ) ) {
	return;
}

$poster = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
?>

<div class="recipe-video">
	<video controls preload="metadata" playsinline<?php if ( $poster ) : ?> poster="<?php echo esc_url( $poster ); ?>"<?php endif; ?>>
		<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $mime_type ); ?>">
		Ваш браузер не поддерживает воспроизведение видео.
	</video>
</div>
