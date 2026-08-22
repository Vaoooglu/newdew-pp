<?php
/**
 * Recipe-specific ACF fields and media helpers.
 *
 * @package oxboxwise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register recipe-specific ACF fields in PHP.
 */
function oxboxwise_register_recipe_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_oxboxwise_recipe_details',
			'title'    => 'Дополнительные данные рецепта',
			'fields'   => array(
				array(
					'key'         => 'field_oxboxwise_recipe_note',
					'label'       => 'Личная заметка',
					'name'        => 'recipe_note',
					'type'        => 'textarea',
					'rows'        => 4,
					'new_lines'   => '',
					'required'    => 0,
					'placeholder' => 'Например: в следующий раз добавить больше соуса.',
				),
				array(
					'key'      => 'field_oxboxwise_recipe_cooking_time',
					'label'    => 'Время приготовления',
					'name'     => 'recipe_cooking_time',
					'type'     => 'text',
					'required' => 0,
					'wrapper'  => array( 'width' => 50 ),
				),
				array(
					'key'      => 'field_oxboxwise_recipe_portions',
					'label'    => 'Порции',
					'name'     => 'recipe_portions',
					'type'     => 'text',
					'required' => 0,
					'wrapper'  => array( 'width' => 50 ),
				),
				array(
					'key'           => 'field_oxboxwise_recipe_video',
					'label'         => 'Видео рецепта',
					'name'          => 'recipe_video',
					'type'          => 'file',
					'required'      => 0,
					'instructions'  => 'Выберите видео, сохранённое в медиатеке WordPress, или загрузите новый файл.',
					'return_format' => 'id',
					'library'       => 'all',
					'mime_types'    => 'mp4,m4v,mov,webm,ogv',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'recipe',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
		)
	);
}
add_action( 'acf/init', 'oxboxwise_register_recipe_fields' );

/**
 * Ensure that the selected media item is a video attachment.
 *
 * @param bool|string $valid Validation result received from ACF.
 * @param mixed       $value Submitted attachment ID.
 * @return bool|string
 */
function oxboxwise_validate_recipe_video( $valid, $value ) {
	if ( true !== $valid || ! $value ) {
		return $valid;
	}

	$video_id = absint( $value );
	$mime     = $video_id ? get_post_mime_type( $video_id ) : '';

	if ( ! $video_id || 'attachment' !== get_post_type( $video_id ) || ! is_string( $mime ) || 0 !== strpos( $mime, 'video/' ) ) {
		return 'Выберите видеофайл из медиатеки WordPress.';
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_oxboxwise_recipe_video', 'oxboxwise_validate_recipe_video', 10, 2 );

/**
 * Return the valid video attachment assigned to a recipe.
 *
 * @param int $post_id Recipe post ID.
 * @return int
 */
function oxboxwise_get_recipe_video_id( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return 0;
	}

	$value    = function_exists( 'get_field' ) ? get_field( 'recipe_video', $post_id, false ) : get_post_meta( $post_id, 'recipe_video', true );
	$video_id = absint( $value );

	if ( ! $video_id || 'attachment' !== get_post_type( $video_id ) ) {
		return 0;
	}

	$mime_type = get_post_mime_type( $video_id );
	return is_string( $mime_type ) && 0 === strpos( $mime_type, 'video/' ) ? $video_id : 0;
}
