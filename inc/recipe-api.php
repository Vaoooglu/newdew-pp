<?php
/**
 * Protected REST API for creating recipes.
 *
 * @package oxboxwise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register API configuration fields on the existing theme options page.
 */
function oxboxwise_register_recipe_api_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_oxboxwise_recipe_api',
			'title'    => 'API рецептов',
			'fields'   => array(
				array(
					'key'          => 'field_oxboxwise_recipe_api_token',
					'label'        => 'API token',
					'name'         => 'recipe_api_token',
					'type'         => 'password',
					'required'     => 0,
					'instructions' => 'Секретный Bearer token длиной не менее 32 символов для внешних server-to-server запросов.',
				),
				array(
					'key'           => 'field_oxboxwise_recipe_api_author',
					'label'         => 'Автор рецептов API',
					'name'          => 'recipe_api_author',
					'type'          => 'user',
					'required'      => 0,
					'instructions'  => 'От имени этого пользователя API и Telegram-бот создают рецепты и загружают файлы.',
					'role'          => array(),
					'allow_null'    => 1,
					'multiple'      => 0,
					'return_format' => 'id',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'theme-general-settings',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'oxboxwise_register_recipe_api_fields' );

/**
 * Validate a token stored through ACF.
 *
 * @param bool|string $valid Validation result received from ACF.
 * @param mixed       $value Submitted token.
 * @return bool|string
 */
function oxboxwise_validate_recipe_api_token( $valid, $value ) {
	if ( true !== $valid || '' === trim( (string) $value ) ) {
		return $valid;
	}

	return strlen( trim( (string) $value ) ) >= 32 ? $valid : 'API token должен содержать не менее 32 символов.';
}
add_filter( 'acf/validate_value/key=field_oxboxwise_recipe_api_token', 'oxboxwise_validate_recipe_api_token', 10, 2 );

/**
 * Return the configured API token without exposing its source.
 *
 * @return string
 */
function oxboxwise_get_recipe_api_token() {
	if ( function_exists( 'get_field' ) ) {
		return trim( (string) get_field( 'recipe_api_token', 'option', false ) );
	}

	return '';
}

/**
 * Return the WordPress user assigned as the API recipe author.
 *
 * @return int
 */
function oxboxwise_get_recipe_api_author_id() {
	if ( function_exists( 'get_field' ) ) {
		return absint( get_field( 'recipe_api_author', 'option', false ) );
	}

	return 0;
}

/**
 * Build an authentication error with a WordPress REST status code.
 *
 * @param string $code    Error code.
 * @param string $message Public error message.
 * @param int    $status  HTTP status.
 * @return WP_Error
 */
function oxboxwise_recipe_api_auth_error( $code, $message, $status ) {
	return new WP_Error( $code, $message, array( 'status' => $status ) );
}

/**
 * Authenticate the server-to-server request before parsing recipe data.
 *
 * @param WP_REST_Request $request REST request.
 * @return true|WP_Error
 */
function oxboxwise_recipe_api_permission( $request ) {
	$configured_token = oxboxwise_get_recipe_api_token();
	if ( strlen( $configured_token ) < 32 ) {
		return oxboxwise_recipe_api_auth_error( 'recipe_api_not_configured', 'Recipe API is not configured.', 503 );
	}

	$authorization = trim( (string) $request->get_header( 'authorization' ) );
	if ( ! preg_match( '/^Bearer\s+(.+)$/i', $authorization, $matches ) ) {
		return oxboxwise_recipe_api_auth_error( 'recipe_api_authentication_required', 'A Bearer token is required.', 401 );
	}

	$provided_token = trim( $matches[1] );
	if ( '' === $provided_token || ! hash_equals( $configured_token, $provided_token ) ) {
		return oxboxwise_recipe_api_auth_error( 'recipe_api_invalid_token', 'The supplied Bearer token is invalid.', 403 );
	}

	return true;
}

/**
 * Register the recipe creation endpoint.
 */
function oxboxwise_register_recipe_api_routes() {
	register_rest_route(
		'oxboxwise/v1',
		'/recipes',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'oxboxwise_recipe_api_create_recipe',
			'permission_callback' => 'oxboxwise_recipe_api_permission',
		)
	);

	register_rest_route(
		'oxboxwise/v1',
		'/media',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'oxboxwise_recipe_api_upload_media',
			'permission_callback' => 'oxboxwise_recipe_api_permission',
		)
	);

	register_rest_route(
		'oxboxwise/v1',
		'/terms',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'oxboxwise_recipe_api_get_terms',
			'permission_callback' => 'oxboxwise_recipe_api_permission',
		)
	);
}
add_action( 'rest_api_init', 'oxboxwise_register_recipe_api_routes' );

/**
 * Return a predictable API validation or processing error.
 *
 * @param string $code    Machine-readable error code.
 * @param string $message Public error message.
 * @param int    $status  HTTP status.
 * @return WP_REST_Response
 */
function oxboxwise_recipe_api_error_response( $code, $message, $status ) {
	return new WP_REST_Response(
		array(
			'success' => false,
			'error'   => array(
				'code'    => $code,
				'message' => $message,
			),
		),
		$status
	);
}

/**
 * Record a sanitized operational error without request content or credentials.
 *
 * @param string $code        Error code.
 * @param string $reason      Technical reason.
 * @param string $external_id Optional external request ID.
 */
function oxboxwise_recipe_api_log_error( $code, $reason = '', $external_id = '' ) {
	$context = array(
		'endpoint'    => '/oxboxwise/v1/recipes',
		'error_code'  => sanitize_key( $code ),
		'reason'      => sanitize_text_field( $reason ),
		'external_id' => sanitize_text_field( $external_id ),
	);

	do_action( 'oxboxwise_recipe_api_error', $context );

	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( '[oxboxwise-recipe-api] ' . wp_json_encode( $context ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Validate an array of existing term IDs for a fixed taxonomy.
 *
 * @param mixed  $value    Submitted value.
 * @param string $taxonomy Taxonomy name.
 * @param string $field    API field name.
 * @return array|WP_Error
 */
function oxboxwise_recipe_api_validate_terms( $value, $taxonomy, $field ) {
	if ( ! is_array( $value ) ) {
		return new WP_Error( 'invalid_' . $field, $field . ' must be an array of term IDs.', array( 'status' => 422 ) );
	}

	$term_ids = array();
	foreach ( $value as $term_id ) {
		if ( ! is_int( $term_id ) && ! ( is_string( $term_id ) && ctype_digit( $term_id ) ) ) {
			return new WP_Error( 'invalid_' . $field, $field . ' must contain only integer term IDs.', array( 'status' => 422 ) );
		}

		$term_id = absint( $term_id );
		if ( ! $term_id || ! term_exists( $term_id, $taxonomy ) ) {
			return new WP_Error( 'invalid_' . $field, sprintf( 'Term %d does not exist in taxonomy %s.', $term_id, $taxonomy ), array( 'status' => 404 ) );
		}

		$term_ids[] = $term_id;
	}

	return array_values( array_unique( $term_ids ) );
}

/**
 * Validate an attachment ID and its expected media family.
 *
 * @param mixed  $value      Submitted value.
 * @param string $media_type Expected MIME prefix, such as image or video.
 * @param string $field      API field name.
 * @return int|WP_Error
 */
function oxboxwise_recipe_api_validate_attachment( $value, $media_type, $field ) {
	if ( ! is_int( $value ) && ! ( is_string( $value ) && ctype_digit( $value ) ) ) {
		return new WP_Error( 'invalid_' . $field, $field . ' must be an integer attachment ID.', array( 'status' => 422 ) );
	}

	$attachment_id = absint( $value );
	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) || 'trash' === get_post_status( $attachment_id ) ) {
		return new WP_Error( 'invalid_' . $field, 'The requested media attachment does not exist.', array( 'status' => 404 ) );
	}

	$mime_type = get_post_mime_type( $attachment_id );
	if ( ! is_string( $mime_type ) || 0 !== strpos( $mime_type, $media_type . '/' ) ) {
		return new WP_Error( 'invalid_' . $field, sprintf( '%s must reference a %s attachment.', $field, $media_type ), array( 'status' => 422 ) );
	}
	if ( 'video' === $media_type && ! in_array( $mime_type, array( 'video/mp4', 'video/quicktime', 'video/webm', 'video/ogg' ), true ) ) {
		return new WP_Error( 'invalid_' . $field, 'recipe_video_id must reference an MP4, M4V, MOV, WebM or OGV attachment.', array( 'status' => 422 ) );
	}

	return $attachment_id;
}

/**
 * Find a recipe previously created for the same external request ID.
 *
 * @param string $external_id External request ID.
 * @return int
 */
function oxboxwise_recipe_api_find_duplicate( $external_id ) {
	$recipes = get_posts(
		array(
			'post_type'      => 'recipe',
			'post_status'    => array( 'draft', 'publish', 'pending', 'private', 'future' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_oxboxwise_recipe_api_external_id',
			'meta_value'     => $external_id,
		)
	);

	return $recipes ? absint( $recipes[0] ) : 0;
}

/**
 * Acquire a short-lived cross-request lock for an external request ID.
 *
 * @param string $external_id External request ID.
 * @return string|WP_Error Option name when acquired.
 */
function oxboxwise_recipe_api_acquire_lock( $external_id ) {
	$option_name = 'oxboxwise_recipe_api_lock_' . hash( 'sha256', $external_id );
	$now         = time();

	if ( add_option( $option_name, $now, '', 'no' ) ) {
		return $option_name;
	}

	$created_at = absint( get_option( $option_name ) );
	if ( $created_at && $created_at < $now - 300 ) {
		delete_option( $option_name );
		if ( add_option( $option_name, $now, '', 'no' ) ) {
			return $option_name;
		}
	}

	return new WP_Error( 'recipe_request_in_progress', 'A request with this external_id is already being processed.' );
}

/**
 * Store a recipe ACF field while retaining a post-meta fallback.
 *
 * @param int    $post_id   Recipe ID.
 * @param string $field_key ACF field key.
 * @param string $field     Meta field name.
 * @param mixed  $value     Field value.
 * @return bool
 */
function oxboxwise_recipe_api_update_field( $post_id, $field_key, $field, $value ) {
	if ( function_exists( 'update_field' ) ) {
		update_field( $field_key, $value, $post_id );
	} else {
		update_post_meta( $post_id, $field, $value );
	}

	return (string) get_post_meta( $post_id, $field, true ) === (string) $value;
}

/**
 * Delete a partially created recipe and release its request lock.
 *
 * @param int    $post_id     Recipe ID.
 * @param string $lock_option Lock option name.
 */
function oxboxwise_recipe_api_rollback( $post_id, $lock_option ) {
	if ( $post_id ) {
		wp_delete_post( $post_id, true );
	}
	if ( $lock_option ) {
		delete_option( $lock_option );
	}
}

/**
 * Format a successful recipe response.
 *
 * @param int  $recipe_id Recipe ID.
 * @param bool $duplicate Whether the external request was already processed.
 * @return WP_REST_Response
 */
function oxboxwise_recipe_api_success_response( $recipe_id, $duplicate = false ) {
	$status   = get_post_status( $recipe_id );
	$response = new WP_REST_Response(
		array(
			'success'   => true,
			'recipe_id' => $recipe_id,
			'status'    => $status,
			'slug'      => get_post_field( 'post_name', $recipe_id ),
			'permalink' => get_permalink( $recipe_id ),
			'edit_url'  => get_edit_post_link( $recipe_id, 'raw' ),
			'duplicate' => (bool) $duplicate,
			'message'   => $duplicate ? 'Recipe already exists.' : 'Recipe created successfully.',
		),
		$duplicate ? 200 : 201
	);

	if ( ! $duplicate ) {
		$response->header( 'Location', get_permalink( $recipe_id ) );
	}

	return $response;
}

/**
 * Return and authorize the configured service user.
 *
 * @param string $capability Required WordPress capability.
 * @return WP_User|WP_REST_Response
 */
function oxboxwise_recipe_api_get_author( $capability ) {
	$author_id = oxboxwise_get_recipe_api_author_id();
	$author    = $author_id ? get_user_by( 'id', $author_id ) : false;

	if ( ! $author ) {
		return oxboxwise_recipe_api_error_response( 'recipe_api_author_not_configured', 'Recipe API author is not configured.', 503 );
	}
	if ( ! user_can( $author, $capability ) ) {
		return oxboxwise_recipe_api_error_response( 'recipe_api_author_forbidden', 'Configured API author does not have the required capability.', 403 );
	}

	return $author;
}

/**
 * Upload an image or recipe video to the WordPress Media Library.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function oxboxwise_recipe_api_upload_media( $request ) {
	unset( $request );
	$author = oxboxwise_recipe_api_get_author( 'upload_files' );
	if ( $author instanceof WP_REST_Response ) {
		return $author;
	}

	$files = isset( $_FILES ) && is_array( $_FILES ) ? $_FILES : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( empty( $files['file'] ) || ! is_array( $files['file'] ) ) {
		return oxboxwise_recipe_api_error_response( 'missing_media_file', 'A multipart file field named file is required.', 400 );
	}

	$file = $files['file'];
	if ( ! isset( $file['error'] ) || UPLOAD_ERR_OK !== (int) $file['error'] ) {
		return oxboxwise_recipe_api_error_response( 'media_upload_error', 'PHP did not receive the uploaded file successfully.', 400 );
	}
	if ( empty( $file['tmp_name'] ) || empty( $file['name'] ) || empty( $file['size'] ) ) {
		return oxboxwise_recipe_api_error_response( 'invalid_media_file', 'The uploaded file is empty or incomplete.', 422 );
	}
	if ( (int) $file['size'] > wp_max_upload_size() ) {
		return oxboxwise_recipe_api_error_response( 'media_file_too_large', 'The file exceeds the WordPress upload limit.', 413 );
	}

	$allowed_mimes = get_allowed_mime_types( $author->ID );
	$file_check    = wp_check_filetype_and_ext( $file['tmp_name'], sanitize_file_name( $file['name'] ), $allowed_mimes );
	$mime_type     = isset( $file_check['type'] ) ? $file_check['type'] : '';
	$extension     = isset( $file_check['ext'] ) ? $file_check['ext'] : '';

	if ( ! $mime_type || ! $extension ) {
		return oxboxwise_recipe_api_error_response( 'invalid_media_type', 'WordPress does not allow this file type.', 422 );
	}

	$is_image = 0 === strpos( $mime_type, 'image/' );
	$is_video = 0 === strpos( $mime_type, 'video/' );
	if ( ! $is_image && ! $is_video ) {
		return oxboxwise_recipe_api_error_response( 'invalid_media_type', 'Only image and video files are accepted.', 422 );
	}
	if ( $is_video && ! in_array( $mime_type, array( 'video/mp4', 'video/quicktime', 'video/webm', 'video/ogg' ), true ) ) {
		return oxboxwise_recipe_api_error_response( 'invalid_video_type', 'Video must be MP4, M4V, MOV, WebM or OGV.', 422 );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_handle_upload(
		'file',
		0,
		array(
			'post_author' => $author->ID,
			'post_title'  => sanitize_text_field( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
		),
		array( 'test_form' => false )
	);
	if ( is_wp_error( $attachment_id ) ) {
		oxboxwise_recipe_api_log_error( 'media_upload_failed', $attachment_id->get_error_message() );
		return oxboxwise_recipe_api_error_response( 'media_upload_failed', 'WordPress could not save the media file.', 500 );
	}

	return new WP_REST_Response(
		array(
			'success'       => true,
			'attachment_id' => (int) $attachment_id,
			'media_type'    => $is_video ? 'video' : 'image',
			'mime_type'     => get_post_mime_type( $attachment_id ),
			'url'           => wp_get_attachment_url( $attachment_id ),
		),
		201
	);
}

/**
 * Return existing terms from one of the recipe taxonomies.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function oxboxwise_recipe_api_get_terms( $request ) {
	$allowed_taxonomies = array( 'recipe_category', 'recipe_ingredient', 'post_tag' );
	$taxonomy           = sanitize_key( (string) $request->get_param( 'taxonomy' ) );
	if ( ! in_array( $taxonomy, $allowed_taxonomies, true ) ) {
		return oxboxwise_recipe_api_error_response( 'invalid_taxonomy', 'taxonomy must be recipe_category, recipe_ingredient or post_tag.', 422 );
	}

	$page     = max( 1, absint( $request->get_param( 'page' ) ) );
	$per_page = min( 100, max( 1, absint( $request->get_param( 'per_page' ) ) ) );
	$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );
	$terms    = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => $per_page,
			'offset'     => ( $page - 1 ) * $per_page,
			'search'     => $search,
		)
	);
	if ( is_wp_error( $terms ) ) {
		oxboxwise_recipe_api_log_error( 'recipe_terms_failed', $terms->get_error_message() );
		return oxboxwise_recipe_api_error_response( 'recipe_terms_failed', 'WordPress could not read recipe terms.', 500 );
	}

	$total = wp_count_terms(
		$taxonomy,
		array(
			'hide_empty' => false,
			'search'     => $search,
		)
	);
	$total = is_wp_error( $total ) ? count( $terms ) : absint( $total );

	$items = array();
	foreach ( $terms as $term ) {
		$items[] = array(
			'id'    => (int) $term->term_id,
			'name'  => $term->name,
			'slug'  => $term->slug,
			'count' => (int) $term->count,
		);
	}

	return new WP_REST_Response(
		array(
			'success'     => true,
			'taxonomy'    => $taxonomy,
			'terms'       => $items,
			'page'        => $page,
			'per_page'    => $per_page,
			'total'       => $total,
			'total_pages' => (int) ceil( $total / $per_page ),
		),
		200
	);
}

/**
 * Create a recipe from a validated REST request.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function oxboxwise_recipe_api_create_recipe( $request ) {
	$params = $request->get_json_params();
	if ( ! is_array( $params ) ) {
		return oxboxwise_recipe_api_error_response( 'invalid_json', 'Request body must contain a JSON object.', 400 );
	}

	$allowed_fields = array(
		'title',
		'content',
		'featured_media_id',
		'recipe_video_id',
		'recipe_category_ids',
		'recipe_ingredient_ids',
		'tag_ids',
		'recipe_note',
		'recipe_cooking_time',
		'recipe_portions',
		'status',
		'external_id',
	);
	$unknown_fields = array_diff( array_keys( $params ), $allowed_fields );
	if ( $unknown_fields ) {
		return oxboxwise_recipe_api_error_response( 'unknown_fields', 'Unknown fields: ' . implode( ', ', $unknown_fields ) . '.', 400 );
	}

	if ( ! isset( $params['title'] ) || ! is_string( $params['title'] ) || '' === trim( wp_strip_all_tags( $params['title'] ) ) ) {
		return oxboxwise_recipe_api_error_response( 'missing_title', 'title is required and must be a non-empty string.', 422 );
	}

	$title = sanitize_text_field( $params['title'] );
	if ( '' === $title ) {
		return oxboxwise_recipe_api_error_response( 'invalid_title', 'title is empty after sanitization.', 422 );
	}

	$content = '';
	if ( array_key_exists( 'content', $params ) ) {
		if ( ! is_string( $params['content'] ) ) {
			return oxboxwise_recipe_api_error_response( 'invalid_content', 'content must be a string.', 422 );
		}
		$content = wp_kses_post( $params['content'] );
	}

	$status = isset( $params['status'] ) ? sanitize_key( $params['status'] ) : 'draft';
	if ( ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
		return oxboxwise_recipe_api_error_response( 'invalid_status', 'status must be draft or publish.', 422 );
	}

	$required_capability = 'publish' === $status ? 'publish_posts' : 'edit_posts';
	$author              = oxboxwise_recipe_api_get_author( $required_capability );
	if ( $author instanceof WP_REST_Response ) {
		return $author;
	}
	$author_id = $author->ID;

	$external_id = '';
	if ( array_key_exists( 'external_id', $params ) ) {
		if ( ! is_string( $params['external_id'] ) || ! preg_match( '/^[A-Za-z0-9._:-]{1,191}$/', $params['external_id'] ) ) {
			return oxboxwise_recipe_api_error_response( 'invalid_external_id', 'external_id may contain only letters, numbers, dot, underscore, colon and hyphen.', 422 );
		}
		$external_id = $params['external_id'];
	}

	if ( $external_id ) {
		$duplicate_id = oxboxwise_recipe_api_find_duplicate( $external_id );
		if ( $duplicate_id ) {
			return oxboxwise_recipe_api_success_response( $duplicate_id, true );
		}
	}

	$taxonomy_fields = array(
		'recipe_category_ids'   => 'recipe_category',
		'recipe_ingredient_ids' => 'recipe_ingredient',
		'tag_ids'               => 'post_tag',
	);
	$taxonomy_values = array();
	foreach ( $taxonomy_fields as $field => $taxonomy ) {
		if ( ! array_key_exists( $field, $params ) ) {
			continue;
		}

		$term_ids = oxboxwise_recipe_api_validate_terms( $params[ $field ], $taxonomy, $field );
		if ( is_wp_error( $term_ids ) ) {
			$error_data = $term_ids->get_error_data();
			$status_code = is_array( $error_data ) && isset( $error_data['status'] ) ? absint( $error_data['status'] ) : 422;
			return oxboxwise_recipe_api_error_response( $term_ids->get_error_code(), $term_ids->get_error_message(), $status_code );
		}
		$taxonomy_values[ $taxonomy ] = $term_ids;
	}

	$featured_media_id = 0;
	if ( array_key_exists( 'featured_media_id', $params ) ) {
		$featured_media_id = oxboxwise_recipe_api_validate_attachment( $params['featured_media_id'], 'image', 'featured_media_id' );
		if ( is_wp_error( $featured_media_id ) ) {
			$error_data = $featured_media_id->get_error_data();
			$status_code = is_array( $error_data ) && isset( $error_data['status'] ) ? absint( $error_data['status'] ) : 422;
			return oxboxwise_recipe_api_error_response( $featured_media_id->get_error_code(), $featured_media_id->get_error_message(), $status_code );
		}
	}

	$recipe_video_id = 0;
	if ( array_key_exists( 'recipe_video_id', $params ) ) {
		$recipe_video_id = oxboxwise_recipe_api_validate_attachment( $params['recipe_video_id'], 'video', 'recipe_video_id' );
		if ( is_wp_error( $recipe_video_id ) ) {
			$error_data = $recipe_video_id->get_error_data();
			$status_code = is_array( $error_data ) && isset( $error_data['status'] ) ? absint( $error_data['status'] ) : 422;
			return oxboxwise_recipe_api_error_response( $recipe_video_id->get_error_code(), $recipe_video_id->get_error_message(), $status_code );
		}
	}

	$text_fields = array(
		'recipe_note'         => 'sanitize_textarea_field',
		'recipe_cooking_time' => 'sanitize_text_field',
		'recipe_portions'     => 'sanitize_text_field',
	);
	$recipe_fields = array();
	foreach ( $text_fields as $field => $sanitize_callback ) {
		if ( ! array_key_exists( $field, $params ) ) {
			continue;
		}
		if ( ! is_string( $params[ $field ] ) ) {
			return oxboxwise_recipe_api_error_response( 'invalid_' . $field, $field . ' must be a string.', 422 );
		}
		$recipe_fields[ $field ] = call_user_func( $sanitize_callback, $params[ $field ] );
	}

	$lock_option = '';
	if ( $external_id ) {
		$lock_option = oxboxwise_recipe_api_acquire_lock( $external_id );
		if ( is_wp_error( $lock_option ) ) {
			return oxboxwise_recipe_api_error_response( $lock_option->get_error_code(), $lock_option->get_error_message(), 409 );
		}

		$duplicate_id = oxboxwise_recipe_api_find_duplicate( $external_id );
		if ( $duplicate_id ) {
			delete_option( $lock_option );
			return oxboxwise_recipe_api_success_response( $duplicate_id, true );
		}
	}

	$recipe_id = wp_insert_post(
		array(
			'post_type'    => 'recipe',
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'draft',
			'post_author'  => $author_id,
		),
		true
	);

	if ( is_wp_error( $recipe_id ) ) {
		if ( $lock_option ) {
			delete_option( $lock_option );
		}
		oxboxwise_recipe_api_log_error( 'recipe_creation_failed', $recipe_id->get_error_message(), $external_id );
		return oxboxwise_recipe_api_error_response( 'recipe_creation_failed', 'WordPress could not create the recipe.', 500 );
	}

	foreach ( $taxonomy_values as $taxonomy => $term_ids ) {
		$result = wp_set_object_terms( $recipe_id, $term_ids, $taxonomy, false );
		if ( is_wp_error( $result ) ) {
			oxboxwise_recipe_api_rollback( $recipe_id, $lock_option );
			oxboxwise_recipe_api_log_error( 'recipe_taxonomy_failed', $result->get_error_message(), $external_id );
			return oxboxwise_recipe_api_error_response( 'recipe_taxonomy_failed', 'WordPress could not assign recipe terms.', 500 );
		}
	}

	if ( $featured_media_id && ! set_post_thumbnail( $recipe_id, $featured_media_id ) ) {
		oxboxwise_recipe_api_rollback( $recipe_id, $lock_option );
		oxboxwise_recipe_api_log_error( 'recipe_thumbnail_failed', 'set_post_thumbnail returned false', $external_id );
		return oxboxwise_recipe_api_error_response( 'recipe_thumbnail_failed', 'WordPress could not assign the featured image.', 500 );
	}

	$field_keys = array(
		'recipe_note'         => 'field_oxboxwise_recipe_note',
		'recipe_cooking_time' => 'field_oxboxwise_recipe_cooking_time',
		'recipe_portions'     => 'field_oxboxwise_recipe_portions',
	);
	foreach ( $recipe_fields as $field => $value ) {
		if ( ! oxboxwise_recipe_api_update_field( $recipe_id, $field_keys[ $field ], $field, $value ) ) {
			oxboxwise_recipe_api_rollback( $recipe_id, $lock_option );
			oxboxwise_recipe_api_log_error( 'recipe_field_failed', 'Could not save ' . $field, $external_id );
			return oxboxwise_recipe_api_error_response( 'recipe_field_failed', 'WordPress could not save recipe fields.', 500 );
		}
	}

	if ( $recipe_video_id && ! oxboxwise_recipe_api_update_field( $recipe_id, 'field_oxboxwise_recipe_video', 'recipe_video', $recipe_video_id ) ) {
		oxboxwise_recipe_api_rollback( $recipe_id, $lock_option );
		oxboxwise_recipe_api_log_error( 'recipe_video_failed', 'Could not save recipe_video', $external_id );
		return oxboxwise_recipe_api_error_response( 'recipe_video_failed', 'WordPress could not assign the recipe video.', 500 );
	}

	if ( $external_id ) {
		update_post_meta( $recipe_id, '_oxboxwise_recipe_api_external_id', $external_id );
		if ( $external_id !== get_post_meta( $recipe_id, '_oxboxwise_recipe_api_external_id', true ) ) {
			oxboxwise_recipe_api_rollback( $recipe_id, $lock_option );
			oxboxwise_recipe_api_log_error( 'recipe_external_id_failed', 'Could not save external_id', $external_id );
			return oxboxwise_recipe_api_error_response( 'recipe_external_id_failed', 'WordPress could not save the external request ID.', 500 );
		}
	}

	if ( 'publish' === $status ) {
		$published_id = wp_update_post(
			array(
				'ID'          => $recipe_id,
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $published_id ) ) {
			oxboxwise_recipe_api_rollback( $recipe_id, $lock_option );
			oxboxwise_recipe_api_log_error( 'recipe_publish_failed', $published_id->get_error_message(), $external_id );
			return oxboxwise_recipe_api_error_response( 'recipe_publish_failed', 'WordPress created but could not publish the recipe.', 500 );
		}
	}

	if ( $lock_option ) {
		delete_option( $lock_option );
	}

	return oxboxwise_recipe_api_success_response( $recipe_id );
}
