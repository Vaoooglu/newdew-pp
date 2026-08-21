<?php
/**
 * Recipe ACF fields and the YouTube metadata importer.
 *
 * @package oxboxwise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the small set of recipe-specific ACF fields in PHP.
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
					'key'           => 'field_oxboxwise_recipe_note',
					'label'         => 'Личная заметка',
					'name'          => 'recipe_note',
					'type'          => 'textarea',
					'rows'          => 4,
					'new_lines'     => '',
					'required'      => 0,
					'placeholder'   => 'Например: в следующий раз добавить больше соуса.',
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
					'key'           => 'field_oxboxwise_recipe_youtube_url',
					'label'         => 'YouTube URL',
					'name'          => 'recipe_youtube_url',
					'type'          => 'url',
					'required'      => 0,
					'instructions'  => 'Поддерживаются обычные ссылки YouTube, youtu.be и Shorts.',
					'placeholder'   => 'https://www.youtube.com/watch?v=…',
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
 * Render importer controls directly below the YouTube URL field.
 *
 * @param array $field ACF field configuration.
 */
function oxboxwise_render_youtube_importer( $field ) {
	unset( $field );
	$post_id     = get_the_ID();
	$video_id    = $post_id ? get_post_meta( $post_id, '_recipe_youtube_video_id', true ) : '';
	$video_title = $post_id ? get_post_meta( $post_id, '_recipe_youtube_title', true ) : '';
	$description = $post_id ? get_post_meta( $post_id, '_recipe_youtube_description', true ) : '';
	$thumbnail   = $post_id && get_post_thumbnail_id( $post_id ) ? wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'medium' ) : '';
	?>
	<div class="recipe-youtube-importer">
		<button type="button" class="button button-secondary recipe-youtube-importer__button">Получить данные</button>
		<span class="spinner"></span>
		<p class="recipe-youtube-importer__status" role="status" aria-live="polite"></p>
		<div class="recipe-youtube-importer__result"<?php echo $video_id ? '' : ' hidden'; ?>>
			<p><strong>Название:</strong> <span data-youtube-title><?php echo esc_html( $video_title ); ?></span></p>
			<p><img data-youtube-thumbnail<?php echo $thumbnail ? ' src="' . esc_url( $thumbnail ) . '"' : ''; ?> alt="<?php echo esc_attr( $video_title ); ?>" style="max-width: 320px; height: auto;"></p>
			<p><label><strong>Исходное описание:</strong><br><textarea data-youtube-description class="large-text" rows="8" readonly><?php echo esc_textarea( $description ); ?></textarea></label></p>
			<p>
				<button type="button" class="button" data-youtube-use-title>Подставить название</button>
				<button type="button" class="button" data-youtube-copy-description>Копировать описание</button>
			</p>
			<p class="description">Описание не вставляется в рецепт автоматически, поэтому уже введённый текст не будет изменён.</p>
		</div>
	</div>
	<?php
}
add_action( 'acf/render_field/name=recipe_youtube_url', 'oxboxwise_render_youtube_importer' );

/**
 * Load importer code only on recipe editing screens.
 *
 * @param string $hook_suffix Current admin page.
 */
function oxboxwise_recipe_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'recipe' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'oxboxwise-recipe-admin',
		get_template_directory_uri() . '/js/recipe-admin.js',
		array(),
		_S_VERSION,
		true
	);

	wp_localize_script(
		'oxboxwise-recipe-admin',
		'oxboxwiseRecipeAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'oxboxwise_youtube_import' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'oxboxwise_recipe_admin_assets' );

/**
 * Extract a video ID from supported YouTube URLs.
 *
 * @param string $url Candidate URL.
 * @return string
 */
function oxboxwise_get_youtube_video_id( $url ) {
	$url   = trim( $url );
	$parts = wp_parse_url( $url );

	if ( empty( $parts['host'] ) ) {
		return '';
	}

	$host = strtolower( preg_replace( '/^www\./', '', $parts['host'] ) );
	$id   = '';

	if ( 'youtu.be' === $host ) {
		$id = trim( isset( $parts['path'] ) ? $parts['path'] : '', '/' );
	} elseif ( in_array( $host, array( 'youtube.com', 'm.youtube.com' ), true ) ) {
		$path = trim( isset( $parts['path'] ) ? $parts['path'] : '', '/' );
		if ( 0 === strpos( $path, 'shorts/' ) || 0 === strpos( $path, 'embed/' ) ) {
			$segments = explode( '/', $path );
			$id       = isset( $segments[1] ) ? $segments[1] : '';
		} elseif ( 'watch' === $path && ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query );
			$id = isset( $query['v'] ) ? $query['v'] : '';
		}
	}

	return preg_match( '/^[A-Za-z0-9_-]{11}$/', $id ) ? $id : '';
}

/**
 * Read YouTube metadata through oEmbed and the public video page.
 *
 * @param string $url      Video URL.
 * @param string $video_id Video ID.
 * @return array|WP_Error
 */
function oxboxwise_get_youtube_metadata( $url, $video_id ) {
	$oembed_url = add_query_arg(
		array(
			'url'    => 'https://www.youtube.com/watch?v=' . rawurlencode( $video_id ),
			'format' => 'json',
		),
		'https://www.youtube.com/oembed'
	);
	$response = wp_safe_remote_get( $oembed_url, array( 'timeout' => 12, 'redirection' => 3 ) );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'youtube_oembed_failed', 'YouTube не вернул данные для этого видео.' );
	}

	$oembed = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $oembed['title'] ) ) {
		return new WP_Error( 'youtube_invalid_response', 'Не удалось прочитать название видео.' );
	}

	$description   = '';
	$page_response = wp_safe_remote_get(
		'https://www.youtube.com/watch?v=' . rawurlencode( $video_id ),
		array(
			'timeout'     => 12,
			'redirection' => 3,
			'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
		)
	);

	if ( ! is_wp_error( $page_response ) && 200 === wp_remote_retrieve_response_code( $page_response ) ) {
		$body = wp_remote_retrieve_body( $page_response );
		if ( preg_match( '/"shortDescription":"((?:[^"\\\\]|\\\\.)*)"/', $body, $matches ) ) {
			$decoded = json_decode( '"' . $matches[1] . '"' );
			if ( is_string( $decoded ) ) {
				$description = $decoded;
			}
		}
	}

	return array(
		'video_id'   => $video_id,
		'title'      => sanitize_text_field( $oembed['title'] ),
		'thumbnail'  => esc_url_raw( isset( $oembed['thumbnail_url'] ) ? $oembed['thumbnail_url'] : 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg' ),
		'description'=> sanitize_textarea_field( $description ),
		'url'        => esc_url_raw( $url ),
	);
}

/**
 * Reuse an imported thumbnail or sideload it into the Media Library.
 *
 * @param int    $post_id  Recipe ID.
 * @param array  $metadata Imported metadata.
 * @return int|WP_Error
 */
function oxboxwise_import_youtube_thumbnail( $post_id, $metadata ) {
	$current_thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( $current_thumbnail_id && $metadata['video_id'] === get_post_meta( $current_thumbnail_id, '_oxboxwise_youtube_video_id', true ) ) {
		return $current_thumbnail_id;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_oxboxwise_youtube_video_id',
			'meta_value'     => $metadata['video_id'],
		)
	);

	if ( $existing ) {
		set_post_thumbnail( $post_id, $existing[0] );
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $metadata['thumbnail'], $post_id, $metadata['title'], 'id' );
	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	update_post_meta( $attachment_id, '_oxboxwise_youtube_video_id', $metadata['video_id'] );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $metadata['title'] );
	set_post_thumbnail( $post_id, $attachment_id );

	return $attachment_id;
}

/**
 * Secure admin AJAX endpoint for YouTube importing.
 */
function oxboxwise_ajax_import_youtube() {
	check_ajax_referer( 'oxboxwise_youtube_import', 'nonce' );

	$post_id = isset( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;
	$url     = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

	if ( ! $post_id || 'recipe' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Недостаточно прав для изменения рецепта.' ), 403 );
	}

	$video_id = oxboxwise_get_youtube_video_id( $url );
	if ( ! $video_id ) {
		wp_send_json_error( array( 'message' => 'Укажите корректную ссылку YouTube.' ), 400 );
	}

	$metadata = oxboxwise_get_youtube_metadata( $url, $video_id );
	if ( is_wp_error( $metadata ) ) {
		wp_send_json_error( array( 'message' => $metadata->get_error_message() ), 502 );
	}

	$attachment_id = oxboxwise_import_youtube_thumbnail( $post_id, $metadata );
	if ( is_wp_error( $attachment_id ) ) {
		wp_send_json_error( array( 'message' => 'Данные получены, но изображение не удалось загрузить: ' . $attachment_id->get_error_message() ), 502 );
	}

	update_post_meta( $post_id, 'recipe_youtube_url', $metadata['url'] );
	update_post_meta( $post_id, '_recipe_youtube_video_id', $metadata['video_id'] );
	update_post_meta( $post_id, '_recipe_youtube_title', $metadata['title'] );
	update_post_meta( $post_id, '_recipe_youtube_description', $metadata['description'] );

	$metadata['attachment_id']  = $attachment_id;
	$metadata['attachment_url'] = wp_get_attachment_image_url( $attachment_id, 'medium' );
	wp_send_json_success( $metadata );
}
add_action( 'wp_ajax_oxboxwise_import_youtube', 'oxboxwise_ajax_import_youtube' );

/**
 * Keep the validated video ID in sync when a recipe is saved manually.
 *
 * @param mixed $value   Field value.
 * @param int   $post_id Post ID.
 * @return string
 */
function oxboxwise_update_youtube_id_from_acf( $value, $post_id ) {
	$video_id = oxboxwise_get_youtube_video_id( $value );
	$old_id   = get_post_meta( $post_id, '_recipe_youtube_video_id', true );
	if ( $old_id && $old_id !== $video_id ) {
		delete_post_meta( $post_id, '_recipe_youtube_title' );
		delete_post_meta( $post_id, '_recipe_youtube_description' );
	}
	if ( $video_id ) {
		update_post_meta( $post_id, '_recipe_youtube_video_id', $video_id );
	} else {
		delete_post_meta( $post_id, '_recipe_youtube_video_id' );
	}
	return $value;
}
add_filter( 'acf/update_value/name=recipe_youtube_url', 'oxboxwise_update_youtube_id_from_acf', 10, 2 );
