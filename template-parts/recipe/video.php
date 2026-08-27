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

$poster      = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
$metadata    = wp_get_attachment_metadata( $video_id );
$width       = is_array( $metadata ) && ! empty( $metadata['width'] ) ? absint( $metadata['width'] ) : 0;
$height      = is_array( $metadata ) && ! empty( $metadata['height'] ) ? absint( $metadata['height'] ) : 0;
$orientation = 'unknown';
if ( $width && $height ) {
	$orientation = $width > $height ? 'landscape' : ( $height > $width ? 'portrait' : 'square' );
}
$video_style = $width && $height ? '--recipe-video-aspect: ' . $width . ' / ' . $height . ';' : '';
?>

<div class="recipe-video recipe-video--<?php echo esc_attr( $orientation ); ?>" data-recipe-video-frame<?php if ( $video_style ) : ?> style="<?php echo esc_attr( $video_style ); ?>"<?php endif; ?>>
	<video controls preload="metadata" playsinline data-recipe-video<?php if ( $poster ) : ?> poster="<?php echo esc_url( $poster ); ?>"<?php endif; ?>>
		<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $mime_type ); ?>">
		Ваш браузер не поддерживает воспроизведение видео.
	</video>
	<button class="recipe-media-play" type="button" data-recipe-video-play aria-label="Воспроизвести видео">
		<svg aria-hidden="true"><use href="#icon-play"></use></svg>
	</button>
</div>
