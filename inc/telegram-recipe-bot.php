<?php
/**
 * Telegram webhook backend for creating recipes.
 *
 * @package oxboxwise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OXBOXWISE_TELEGRAM_WEBHOOK_ROUTE', '/oxboxwise/v1/telegram/webhook' );
define( 'OXBOXWISE_TELEGRAM_TERM_PAGE_SIZE', 12 );

/**
 * Add Telegram settings to the existing ACF options page.
 */
function oxboxwise_register_telegram_bot_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_oxboxwise_telegram_recipe_bot',
			'title'    => 'Telegram-бот рецептов',
			'fields'   => array(
				array(
					'key'          => 'field_oxboxwise_telegram_bot_token',
					'label'        => 'Bot token',
					'name'         => 'telegram_recipe_bot_token',
					'type'         => 'password',
					'required'     => 0,
					'instructions' => 'Токен бота, полученный от BotFather. Не передавайте его другим людям.',
				),
				array(
					'key'          => 'field_oxboxwise_telegram_webhook_secret',
					'label'        => 'Webhook secret',
					'name'         => 'telegram_recipe_webhook_secret',
					'type'         => 'password',
					'required'     => 0,
					'instructions' => 'Можно оставить пустым: при сохранении WordPress безопасно сгенерирует секрет. Допустимы 32–256 символов: латинские буквы, цифры, дефис и подчёркивание.',
				),
				array(
					'key'          => 'field_oxboxwise_telegram_allowed_users',
					'label'        => 'Разрешённые Telegram user ID',
					'name'         => 'telegram_recipe_allowed_user_ids',
					'type'         => 'textarea',
					'rows'         => 3,
					'required'     => 0,
					'instructions' => 'Один или несколько числовых user ID через запятую или с новой строки. Команда /id доступна до добавления ID.',
				),
				array(
					'key'          => 'field_oxboxwise_telegram_webhook_info',
					'label'        => 'Webhook endpoint',
					'name'         => '',
					'type'         => 'message',
					'message'      => '<code>' . esc_html( rest_url( ltrim( OXBOXWISE_TELEGRAM_WEBHOOK_ROUTE, '/' ) ) ) . '</code><br>Webhook регистрируется в Telegram автоматически после сохранения настроек.',
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
add_action( 'acf/init', 'oxboxwise_register_telegram_bot_fields' );

/**
 * Validate the BotFather token format.
 *
 * @param bool|string $valid Current result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function oxboxwise_validate_telegram_bot_token( $valid, $value ) {
	$value = trim( (string) $value );
	if ( true !== $valid || '' === $value ) {
		return $valid;
	}

	return preg_match( '/^[0-9]+:[A-Za-z0-9_-]{20,}$/', $value ) ? $valid : 'Bot token имеет неверный формат.';
}
add_filter( 'acf/validate_value/key=field_oxboxwise_telegram_bot_token', 'oxboxwise_validate_telegram_bot_token', 10, 2 );

/**
 * Validate the Telegram webhook secret.
 *
 * @param bool|string $valid Current result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function oxboxwise_validate_telegram_webhook_secret( $valid, $value ) {
	$value = trim( (string) $value );
	if ( true !== $valid || '' === $value ) {
		return $valid;
	}

	return preg_match( '/^[A-Za-z0-9_-]{32,256}$/', $value ) ? $valid : 'Webhook secret должен содержать 32–256 допустимых символов.';
}
add_filter( 'acf/validate_value/key=field_oxboxwise_telegram_webhook_secret', 'oxboxwise_validate_telegram_webhook_secret', 10, 2 );

/**
 * Validate the Telegram user allowlist.
 *
 * @param bool|string $valid Current result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function oxboxwise_validate_telegram_allowed_users( $valid, $value ) {
	$value = trim( (string) $value );
	if ( true !== $valid || '' === $value ) {
		return $valid;
	}

	$items = preg_split( '/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
	foreach ( $items as $item ) {
		if ( ! ctype_digit( $item ) || 0 >= (int) $item ) {
			return 'Укажите только числовые Telegram user ID через запятую или с новой строки.';
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_oxboxwise_telegram_allowed_users', 'oxboxwise_validate_telegram_allowed_users', 10, 2 );

/**
 * Read an unformatted Telegram option.
 *
 * @param string $name ACF field name.
 * @return mixed
 */
function oxboxwise_telegram_get_option( $name ) {
	return function_exists( 'get_field' ) ? get_field( $name, 'option', false ) : get_option( 'options_' . $name );
}

/**
 * Return configured Telegram token.
 *
 * @return string
 */
function oxboxwise_telegram_get_token() {
	return trim( (string) oxboxwise_telegram_get_option( 'telegram_recipe_bot_token' ) );
}

/**
 * Return configured webhook secret.
 *
 * @return string
 */
function oxboxwise_telegram_get_webhook_secret() {
	return trim( (string) oxboxwise_telegram_get_option( 'telegram_recipe_webhook_secret' ) );
}

/**
 * Return allowed Telegram users.
 *
 * @return int[]
 */
function oxboxwise_telegram_get_allowed_users() {
	$items = preg_split( '/[\s,;]+/', trim( (string) oxboxwise_telegram_get_option( 'telegram_recipe_allowed_user_ids' ) ), -1, PREG_SPLIT_NO_EMPTY );
	return array_values( array_unique( array_filter( array_map( 'absint', $items ) ) ) );
}

/**
 * Execute a Telegram Bot API request without logging credentials or payload.
 *
 * @param string $method  Telegram method.
 * @param array  $payload Request payload.
 * @return array|WP_Error
 */
function oxboxwise_telegram_api_request( $method, $payload = array() ) {
	$token = oxboxwise_telegram_get_token();
	if ( ! preg_match( '/^[0-9]+:[A-Za-z0-9_-]{20,}$/', $token ) ) {
		return new WP_Error( 'telegram_not_configured', 'Telegram bot token is not configured.' );
	}

	$method = preg_replace( '/[^A-Za-z]/', '', (string) $method );
	if ( '' === $method ) {
		return new WP_Error( 'telegram_invalid_method', 'Invalid Telegram API method.' );
	}

	$response = wp_remote_post(
		'https://api.telegram.org/bot' . $token . '/' . $method,
		array(
			'timeout'     => 60,
			'redirection' => 0,
			'headers'     => array( 'Content-Type' => 'application/json' ),
			'body'        => wp_json_encode( $payload ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'telegram_request_failed', 'Telegram API request failed.' );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 !== wp_remote_retrieve_response_code( $response ) || ! is_array( $body ) || empty( $body['ok'] ) ) {
		$description = is_array( $body ) && ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : 'Telegram rejected the request.';
		return new WP_Error( 'telegram_api_error', $description );
	}

	return isset( $body['result'] ) ? $body['result'] : array();
}

/**
 * Register the webhook and bot commands after the ACF options are saved.
 *
 * @param mixed $post_id ACF post ID.
 */
function oxboxwise_telegram_sync_webhook_after_save( $post_id ) {
	if ( ! in_array( (string) $post_id, array( 'option', 'options' ), true ) ) {
		return;
	}

	$token  = oxboxwise_telegram_get_token();
	$secret = oxboxwise_telegram_get_webhook_secret();
	if ( '' === $token && '' === $secret ) {
		return;
	}
	if ( '' !== $token && '' === $secret ) {
		$secret = wp_generate_password( 48, false, false );
		if ( function_exists( 'update_field' ) ) {
			update_field( 'field_oxboxwise_telegram_webhook_secret', $secret, 'option' );
		} else {
			update_option( 'options_telegram_recipe_webhook_secret', $secret, false );
		}
	}
	if ( ! preg_match( '/^[0-9]+:[A-Za-z0-9_-]{20,}$/', $token ) || ! preg_match( '/^[A-Za-z0-9_-]{32,256}$/', $secret ) ) {
		update_option( 'oxboxwise_telegram_webhook_notice', array( 'success' => false, 'message' => 'Заполните Bot token и корректный Webhook secret.' ), false );
		return;
	}

	$url = rest_url( ltrim( OXBOXWISE_TELEGRAM_WEBHOOK_ROUTE, '/' ) );
	if ( 0 !== strpos( $url, 'https://' ) ) {
		update_option( 'oxboxwise_telegram_webhook_notice', array( 'success' => false, 'message' => 'Telegram принимает webhook только по HTTPS. Проверьте адрес WordPress.' ), false );
		return;
	}

	$result = oxboxwise_telegram_api_request(
		'setWebhook',
		array(
			'url'             => $url,
			'secret_token'    => $secret,
			'allowed_updates' => array( 'message', 'callback_query' ),
			'drop_pending_updates' => false,
		)
	);
	if ( is_wp_error( $result ) ) {
		update_option( 'oxboxwise_telegram_webhook_notice', array( 'success' => false, 'message' => 'Webhook не зарегистрирован: ' . $result->get_error_message() ), false );
		return;
	}

	oxboxwise_telegram_api_request(
		'setMyCommands',
		array(
			'commands' => array(
				array( 'command' => 'start', 'description' => 'Начать работу' ),
				array( 'command' => 'newrecipe', 'description' => 'Создать рецепт' ),
				array( 'command' => 'cancel', 'description' => 'Отменить создание' ),
				array( 'command' => 'id', 'description' => 'Показать Telegram user ID' ),
			),
		)
	);

	update_option( 'oxboxwise_telegram_webhook_notice', array( 'success' => true, 'message' => 'Telegram webhook успешно зарегистрирован.' ), false );
}
add_action( 'acf/save_post', 'oxboxwise_telegram_sync_webhook_after_save', 30 );

/**
 * Display the last webhook registration result on the settings page.
 */
function oxboxwise_telegram_webhook_admin_notice() {
	if ( empty( $_GET['page'] ) || 'theme-general-settings' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$notice = get_option( 'oxboxwise_telegram_webhook_notice' );
	if ( ! is_array( $notice ) || empty( $notice['message'] ) ) {
		return;
	}

	$class = ! empty( $notice['success'] ) ? 'notice notice-success is-dismissible' : 'notice notice-error';
	echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $notice['message'] ) . '</p></div>';
}
add_action( 'admin_notices', 'oxboxwise_telegram_webhook_admin_notice' );

/**
 * Register protected Telegram webhook route.
 */
function oxboxwise_register_telegram_webhook_route() {
	register_rest_route(
		'oxboxwise/v1',
		'/telegram/webhook',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'oxboxwise_telegram_webhook',
			'permission_callback' => 'oxboxwise_telegram_webhook_permission',
		)
	);
}
add_action( 'rest_api_init', 'oxboxwise_register_telegram_webhook_route' );

/**
 * Verify Telegram's secret header before processing an update.
 *
 * @param WP_REST_Request $request REST request.
 * @return true|WP_Error
 */
function oxboxwise_telegram_webhook_permission( $request ) {
	$token  = oxboxwise_telegram_get_token();
	$secret = oxboxwise_telegram_get_webhook_secret();
	if ( ! preg_match( '/^[0-9]+:[A-Za-z0-9_-]{20,}$/', $token ) || ! preg_match( '/^[A-Za-z0-9_-]{32,256}$/', $secret ) ) {
		return new WP_Error( 'telegram_webhook_not_configured', 'Telegram webhook is not configured.', array( 'status' => 503 ) );
	}

	$provided = trim( (string) $request->get_header( 'x-telegram-bot-api-secret-token' ) );
	if ( '' === $provided ) {
		return new WP_Error( 'telegram_webhook_authentication_required', 'Webhook secret is required.', array( 'status' => 401 ) );
	}
	if ( ! hash_equals( $secret, $provided ) ) {
		return new WP_Error( 'telegram_webhook_invalid_secret', 'Webhook secret is invalid.', array( 'status' => 403 ) );
	}

	return true;
}

/**
 * Send a text message.
 *
 * @param int        $chat_id Chat ID.
 * @param string     $text Message text.
 * @param array|null $markup Inline keyboard.
 * @return array|WP_Error
 */
function oxboxwise_telegram_send_message( $chat_id, $text, $markup = null ) {
	$payload = array(
		'chat_id' => (int) $chat_id,
		'text'    => (string) $text,
	);
	if ( is_array( $markup ) ) {
		$payload['reply_markup'] = $markup;
	}

	return oxboxwise_telegram_api_request( 'sendMessage', $payload );
}

/**
 * Build an inline keyboard.
 *
 * @param array $rows Rows containing text/data pairs.
 * @return array
 */
function oxboxwise_telegram_keyboard( $rows ) {
	$keyboard = array();
	foreach ( $rows as $row ) {
		$buttons = array();
		foreach ( $row as $button ) {
			$buttons[] = array(
				'text'          => (string) $button[0],
				'callback_data' => (string) $button[1],
			);
		}
		$keyboard[] = $buttons;
	}

	return array( 'inline_keyboard' => $keyboard );
}

/**
 * Answer a callback query.
 *
 * @param string $callback_id Callback ID.
 * @param string $text Optional notification.
 */
function oxboxwise_telegram_answer_callback( $callback_id, $text = '' ) {
	$payload = array( 'callback_query_id' => (string) $callback_id );
	if ( '' !== $text ) {
		$payload['text'] = $text;
	}
	oxboxwise_telegram_api_request( 'answerCallbackQuery', $payload );
}

/**
 * Replace an existing inline keyboard.
 *
 * @param int   $chat_id Chat ID.
 * @param int   $message_id Message ID.
 * @param array $markup Keyboard.
 */
function oxboxwise_telegram_edit_markup( $chat_id, $message_id, $markup ) {
	oxboxwise_telegram_api_request(
		'editMessageReplyMarkup',
		array(
			'chat_id'      => (int) $chat_id,
			'message_id'   => (int) $message_id,
			'reply_markup' => $markup,
		)
	);
}

/**
 * State transient key for one Telegram user.
 *
 * @param int $user_id Telegram user ID.
 * @return string
 */
function oxboxwise_telegram_state_key( $user_id ) {
	return 'oxboxwise_tg_recipe_' . hash( 'sha256', (string) absint( $user_id ) );
}

/**
 * Read conversation state.
 *
 * @param int $user_id Telegram user ID.
 * @return array|null
 */
function oxboxwise_telegram_get_state( $user_id ) {
	$state = get_transient( oxboxwise_telegram_state_key( $user_id ) );
	return is_array( $state ) ? $state : null;
}

/**
 * Save conversation state for seven days.
 *
 * @param int    $user_id Telegram user ID.
 * @param int    $chat_id Chat ID.
 * @param string $step Current step.
 * @param array  $data Collected recipe data.
 */
function oxboxwise_telegram_save_state( $user_id, $chat_id, $step, $data ) {
	set_transient(
		oxboxwise_telegram_state_key( $user_id ),
		array(
			'chat_id' => (int) $chat_id,
			'step'    => sanitize_key( $step ),
			'data'    => $data,
		),
		7 * DAY_IN_SECONDS
	);
}

/**
 * Delete conversation state.
 *
 * @param int $user_id Telegram user ID.
 */
function oxboxwise_telegram_delete_state( $user_id ) {
	delete_transient( oxboxwise_telegram_state_key( $user_id ) );
}

/**
 * Check whether a Telegram user may create recipes.
 *
 * @param int $user_id Telegram user ID.
 * @return bool
 */
function oxboxwise_telegram_user_is_allowed( $user_id ) {
	return in_array( absint( $user_id ), oxboxwise_telegram_get_allowed_users(), true );
}

/**
 * Return a safe command name from message text.
 *
 * @param string $text Message text.
 * @return string
 */
function oxboxwise_telegram_command( $text ) {
	if ( 0 !== strpos( $text, '/' ) ) {
		return '';
	}

	$first = strtok( $text, " \t\r\n" );
	$first = strtok( (string) $first, '@' );
	return strtolower( (string) $first );
}

/**
 * Begin a recipe conversation.
 *
 * @param int $user_id Telegram user ID.
 * @param int $chat_id Chat ID.
 * @param int $message_id Source message ID.
 */
function oxboxwise_telegram_start_recipe( $user_id, $chat_id, $message_id ) {
	$data = array(
		'external_id'          => 'telegram-' . absint( $chat_id ) . '-' . absint( $message_id ),
		'recipe_category_ids'  => array(),
		'recipe_ingredient_ids'=> array(),
		'tag_ids'              => array(),
	);
	oxboxwise_telegram_save_state( $user_id, $chat_id, 'title', $data );
	oxboxwise_telegram_send_message( $chat_id, 'Введите название рецепта. Для отмены: /cancel' );
}

/**
 * Return a page of existing terms.
 *
 * @param string $taxonomy Taxonomy.
 * @param int    $page Page number.
 * @return array|WP_Error
 */
function oxboxwise_telegram_get_terms_page( $taxonomy, $page ) {
	if ( ! in_array( $taxonomy, array( 'recipe_category', 'recipe_ingredient', 'post_tag' ), true ) ) {
		return new WP_Error( 'telegram_invalid_taxonomy', 'Invalid recipe taxonomy.' );
	}

	$page  = max( 1, absint( $page ) );
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => OXBOXWISE_TELEGRAM_TERM_PAGE_SIZE,
			'offset'     => ( $page - 1 ) * OXBOXWISE_TELEGRAM_TERM_PAGE_SIZE,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $terms;
	}

	$total = wp_count_terms( $taxonomy, array( 'hide_empty' => false ) );
	if ( is_wp_error( $total ) ) {
		$total = count( $terms );
	}

	return array(
		'terms'       => $terms,
		'total_pages' => max( 1, (int) ceil( absint( $total ) / OXBOXWISE_TELEGRAM_TERM_PAGE_SIZE ) ),
	);
}

/**
 * Shorten term names to fit Telegram buttons.
 *
 * @param string $name Term name.
 * @return string
 */
function oxboxwise_telegram_button_text( $name ) {
	$name = wp_strip_all_tags( (string) $name );
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $name, 'UTF-8' ) > 45 ) {
		return mb_substr( $name, 0, 42, 'UTF-8' ) . '…';
	}
	return strlen( $name ) > 60 ? substr( $name, 0, 57 ) . '...' : $name;
}

/**
 * Build taxonomy picker markup.
 *
 * @param string $kind Picker kind.
 * @param int    $page Page number.
 * @param array  $data Conversation data.
 * @return array|WP_Error
 */
function oxboxwise_telegram_picker_markup( $kind, $page, $data ) {
	$config = array(
		'category'    => array( 'recipe_category', 'recipe_category_ids', 'cat', false ),
		'ingredients' => array( 'recipe_ingredient', 'recipe_ingredient_ids', 'ing', true ),
		'tags'        => array( 'post_tag', 'tag_ids', 'tag', true ),
	);
	if ( ! isset( $config[ $kind ] ) ) {
		return new WP_Error( 'telegram_invalid_picker', 'Invalid term picker.' );
	}

	list( $taxonomy, $field, $prefix, $multiple ) = $config[ $kind ];
	$result = oxboxwise_telegram_get_terms_page( $taxonomy, $page );
	if ( is_wp_error( $result ) ) {
		return $result;
	}

	$selected = array_map( 'absint', isset( $data[ $field ] ) && is_array( $data[ $field ] ) ? $data[ $field ] : array() );
	$rows     = array();
	foreach ( $result['terms'] as $term ) {
		$checked = in_array( (int) $term->term_id, $selected, true ) ? '✓ ' : '';
		$rows[]  = array( array( $checked . oxboxwise_telegram_button_text( $term->name ), $prefix . ':' . (int) $term->term_id . ':' . max( 1, absint( $page ) ) ) );
	}

	$navigation = array();
	if ( $page > 1 ) {
		$navigation[] = array( '←', $prefix . 'p:' . ( $page - 1 ) );
	}
	if ( $page < $result['total_pages'] ) {
		$navigation[] = array( '→', $prefix . 'p:' . ( $page + 1 ) );
	}
	if ( $navigation ) {
		$rows[] = $navigation;
	}
	$rows[] = $multiple
		? array( array( 'Готово', $prefix . ':done' ), array( 'Пропустить', $prefix . ':skip' ) )
		: array( array( 'Без категории', $prefix . ':skip' ) );

	return oxboxwise_telegram_keyboard( $rows );
}

/**
 * Send a taxonomy picker.
 *
 * @param int    $chat_id Chat ID.
 * @param string $kind Picker kind.
 * @param int    $page Page number.
 * @param array  $data Conversation data.
 * @return true|WP_Error
 */
function oxboxwise_telegram_send_picker( $chat_id, $kind, $page, $data ) {
	$titles = array(
		'category'    => 'Выберите категорию.',
		'ingredients' => 'Выберите ингредиенты.',
		'tags'        => 'Выберите теги.',
	);
	$markup = oxboxwise_telegram_picker_markup( $kind, $page, $data );
	if ( is_wp_error( $markup ) ) {
		return $markup;
	}

	$result = oxboxwise_telegram_send_message( $chat_id, $titles[ $kind ], $markup );
	return is_wp_error( $result ) ? $result : true;
}

/**
 * Extract an image reference from a Telegram message.
 *
 * @param array $message Telegram message.
 * @return array|null
 */
function oxboxwise_telegram_extract_image( $message ) {
	if ( ! empty( $message['photo'] ) && is_array( $message['photo'] ) ) {
		$photo = end( $message['photo'] );
		if ( ! empty( $photo['file_id'] ) ) {
			return array(
				'file_id'   => (string) $photo['file_id'],
				'filename'  => 'telegram-photo-' . sanitize_file_name( (string) $photo['file_unique_id'] ) . '.jpg',
				'mime_type' => 'image/jpeg',
			);
		}
	}

	$document = ! empty( $message['document'] ) && is_array( $message['document'] ) ? $message['document'] : array();
	$mime     = isset( $document['mime_type'] ) ? sanitize_mime_type( $document['mime_type'] ) : '';
	if ( ! empty( $document['file_id'] ) && 0 === strpos( $mime, 'image/' ) ) {
		return array(
			'file_id'   => (string) $document['file_id'],
			'filename'  => sanitize_file_name( isset( $document['file_name'] ) ? $document['file_name'] : 'telegram-image' ),
			'mime_type' => $mime,
		);
	}

	return null;
}

/**
 * Extract a video reference from a Telegram message.
 *
 * @param array $message Telegram message.
 * @return array|null
 */
function oxboxwise_telegram_extract_video( $message ) {
	$video = ! empty( $message['video'] ) && is_array( $message['video'] ) ? $message['video'] : array();
	if ( ! empty( $video['file_id'] ) ) {
		$unique = ! empty( $video['file_unique_id'] ) ? sanitize_file_name( $video['file_unique_id'] ) : wp_generate_uuid4();
		return array(
			'file_id'   => (string) $video['file_id'],
			'filename'  => sanitize_file_name( ! empty( $video['file_name'] ) ? $video['file_name'] : 'telegram-video-' . $unique . '.mp4' ),
			'mime_type' => sanitize_mime_type( ! empty( $video['mime_type'] ) ? $video['mime_type'] : 'video/mp4' ),
		);
	}

	$document = ! empty( $message['document'] ) && is_array( $message['document'] ) ? $message['document'] : array();
	$mime     = isset( $document['mime_type'] ) ? sanitize_mime_type( $document['mime_type'] ) : '';
	if ( ! empty( $document['file_id'] ) && 0 === strpos( $mime, 'video/' ) ) {
		return array(
			'file_id'   => (string) $document['file_id'],
			'filename'  => sanitize_file_name( isset( $document['file_name'] ) ? $document['file_name'] : 'telegram-video.mp4' ),
			'mime_type' => $mime,
		);
	}

	return null;
}

/**
 * Process a normal Telegram message.
 *
 * @param array $message Telegram message.
 * @return true|WP_Error
 */
function oxboxwise_telegram_handle_message( $message ) {
	$user_id    = absint( isset( $message['from']['id'] ) ? $message['from']['id'] : 0 );
	$chat_id    = (int) ( isset( $message['chat']['id'] ) ? $message['chat']['id'] : 0 );
	$message_id = absint( isset( $message['message_id'] ) ? $message['message_id'] : 0 );
	$text       = isset( $message['text'] ) ? trim( (string) $message['text'] ) : '';
	$command    = oxboxwise_telegram_command( $text );

	if ( ! $user_id || ! $chat_id ) {
		return new WP_Error( 'telegram_invalid_message', 'Telegram message has no user or chat ID.' );
	}
	if ( '/id' === $command ) {
		oxboxwise_telegram_send_message( $chat_id, 'Ваш Telegram user ID: ' . $user_id );
		return true;
	}
	if ( ! oxboxwise_telegram_user_is_allowed( $user_id ) ) {
		oxboxwise_telegram_send_message( $chat_id, 'Доступ запрещён. Добавьте Telegram user ID ' . $user_id . ' в общих настройках сайта.' );
		return true;
	}
	if ( '/start' === $command ) {
		oxboxwise_telegram_send_message( $chat_id, 'Бот создаёт рецепты в WordPress. Команды: /newrecipe, /cancel, /id' );
		return true;
	}
	if ( '/cancel' === $command ) {
		oxboxwise_telegram_delete_state( $user_id );
		oxboxwise_telegram_send_message( $chat_id, 'Создание рецепта отменено.' );
		return true;
	}
	if ( '/newrecipe' === $command ) {
		oxboxwise_telegram_start_recipe( $user_id, $chat_id, $message_id );
		return true;
	}

	$state = oxboxwise_telegram_get_state( $user_id );
	if ( ! $state ) {
		oxboxwise_telegram_send_message( $chat_id, 'Используйте /newrecipe, чтобы создать рецепт.' );
		return true;
	}
	if ( (int) $state['chat_id'] !== $chat_id ) {
		oxboxwise_telegram_send_message( $chat_id, 'Продолжите создание рецепта в исходном чате или используйте /cancel.' );
		return true;
	}

	return oxboxwise_telegram_process_step( $user_id, $chat_id, $message, $state );
}

/**
 * Process one conversation input step.
 *
 * @param int   $user_id Telegram user ID.
 * @param int   $chat_id Chat ID.
 * @param array $message Telegram message.
 * @param array $state Conversation state.
 * @return true|WP_Error
 */
function oxboxwise_telegram_process_step( $user_id, $chat_id, $message, $state ) {
	$step = isset( $state['step'] ) ? $state['step'] : '';
	$data = isset( $state['data'] ) && is_array( $state['data'] ) ? $state['data'] : array();
	$text = isset( $message['text'] ) ? trim( (string) $message['text'] ) : '';

	if ( 'title' === $step ) {
		if ( '' === $text || 0 === strpos( $text, '/' ) ) {
			oxboxwise_telegram_send_message( $chat_id, 'Название должно быть текстом.' );
			return true;
		}
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : strlen( $text );
		if ( $length > 250 ) {
			oxboxwise_telegram_send_message( $chat_id, 'Название не должно превышать 250 символов.' );
			return true;
		}
		$data['title'] = sanitize_text_field( $text );
		oxboxwise_telegram_save_state( $user_id, $chat_id, 'content', $data );
		oxboxwise_telegram_send_message( $chat_id, 'Введите описание приготовления или /skip.' );
		return true;
	}

	if ( 'content' === $step ) {
		if ( '/skip' === $text ) {
			$data['content'] = '';
		} elseif ( '' !== $text ) {
			$data['content'] = $text;
		} else {
			oxboxwise_telegram_send_message( $chat_id, 'Отправьте текст описания или /skip.' );
			return true;
		}
		oxboxwise_telegram_save_state( $user_id, $chat_id, 'category', $data );
		return oxboxwise_telegram_send_picker( $chat_id, 'category', 1, $data );
	}

	if ( 'image' === $step ) {
		if ( '/skip' === $text ) {
			oxboxwise_telegram_save_state( $user_id, $chat_id, 'video', $data );
			oxboxwise_telegram_send_message( $chat_id, 'Отправьте видеофайл или /skip.' );
			return true;
		}
		$media = oxboxwise_telegram_extract_image( $message );
		if ( ! $media ) {
			oxboxwise_telegram_send_message( $chat_id, 'Отправьте изображение как фото/файл или /skip.' );
			return true;
		}
		$data['_featured_media_source'] = $media;
		oxboxwise_telegram_save_state( $user_id, $chat_id, 'video', $data );
		oxboxwise_telegram_send_message( $chat_id, 'Изображение принято. Теперь отправьте видеофайл или /skip.' );
		return true;
	}

	if ( 'video' === $step ) {
		if ( '/skip' === $text ) {
			oxboxwise_telegram_save_state( $user_id, $chat_id, 'cooking_time', $data );
			oxboxwise_telegram_send_message( $chat_id, 'Укажите время приготовления или /skip.' );
			return true;
		}
		$media = oxboxwise_telegram_extract_video( $message );
		if ( ! $media ) {
			oxboxwise_telegram_send_message( $chat_id, 'Отправьте видео или video-файл как документ, либо /skip.' );
			return true;
		}
		$data['_recipe_video_source'] = $media;
		oxboxwise_telegram_save_state( $user_id, $chat_id, 'cooking_time', $data );
		oxboxwise_telegram_send_message( $chat_id, 'Видео принято. Укажите время приготовления или /skip.' );
		return true;
	}

	$text_steps = array(
		'cooking_time' => array( 'recipe_cooking_time', 'portions', 'Укажите количество порций или /skip.' ),
		'portions'     => array( 'recipe_portions', 'note', 'Добавьте личную заметку или /skip.' ),
		'note'         => array( 'recipe_note', 'status', '' ),
	);
	if ( isset( $text_steps[ $step ] ) ) {
		list( $field, $next_step, $prompt ) = $text_steps[ $step ];
		if ( '/skip' === $text ) {
			$data[ $field ] = '';
		} elseif ( '' !== $text ) {
			$data[ $field ] = $text;
		} else {
			oxboxwise_telegram_send_message( $chat_id, 'Отправьте текст или /skip.' );
			return true;
		}
		oxboxwise_telegram_save_state( $user_id, $chat_id, $next_step, $data );
		if ( 'status' === $next_step ) {
			oxboxwise_telegram_send_message(
				$chat_id,
				'Выберите статус рецепта.',
				oxboxwise_telegram_keyboard( array( array( array( 'Черновик', 'status:draft' ), array( 'Опубликовать', 'status:publish' ) ) ) )
			);
		} else {
			oxboxwise_telegram_send_message( $chat_id, $prompt );
		}
		return true;
	}

	oxboxwise_telegram_send_message( $chat_id, 'Используйте кнопки под предыдущим сообщением или /cancel.' );
	return true;
}

/**
 * Download a Telegram file and save it in the WordPress Media Library.
 *
 * @param array $source Telegram file reference.
 * @return int|WP_Error
 */
function oxboxwise_telegram_import_media( $source ) {
	$author = oxboxwise_recipe_api_get_author( 'upload_files' );
	if ( $author instanceof WP_REST_Response ) {
		$data = $author->get_data();
		return new WP_Error( isset( $data['error']['code'] ) ? $data['error']['code'] : 'telegram_media_author_error', isset( $data['error']['message'] ) ? $data['error']['message'] : 'Media author is unavailable.' );
	}
	if ( empty( $source['file_id'] ) || empty( $source['filename'] ) ) {
		return new WP_Error( 'telegram_invalid_media_source', 'Telegram media source is incomplete.' );
	}

	$file_info = oxboxwise_telegram_api_request( 'getFile', array( 'file_id' => (string) $source['file_id'] ) );
	if ( is_wp_error( $file_info ) ) {
		return $file_info;
	}
	$file_size = isset( $file_info['file_size'] ) ? absint( $file_info['file_size'] ) : 0;
	$max_size  = min( 20 * MB_IN_BYTES, wp_max_upload_size() );
	if ( $file_size && $file_size > $max_size ) {
		return new WP_Error( 'telegram_media_too_large', 'Файл превышает лимит Telegram Bot API или WordPress.' );
	}
	if ( empty( $file_info['file_path'] ) ) {
		return new WP_Error( 'telegram_file_path_missing', 'Telegram не вернул путь к файлу.' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$token    = oxboxwise_telegram_get_token();
	$file_url = 'https://api.telegram.org/file/bot' . $token . '/' . ltrim( (string) $file_info['file_path'], '/' );
	$temp     = download_url( $file_url, 120 );
	if ( is_wp_error( $temp ) ) {
		return new WP_Error( 'telegram_file_download_failed', 'Не удалось скачать файл из Telegram.' );
	}
	if ( ! file_exists( $temp ) || filesize( $temp ) > $max_size ) {
		wp_delete_file( $temp );
		return new WP_Error( 'telegram_media_too_large', 'Файл превышает лимит Telegram Bot API или WordPress.' );
	}

	$filename = sanitize_file_name( (string) $source['filename'] );
	$check    = wp_check_filetype_and_ext( $temp, $filename, get_allowed_mime_types( $author->ID ) );
	$mime     = isset( $check['type'] ) ? $check['type'] : '';
	$is_image = is_string( $mime ) && 0 === strpos( $mime, 'image/' );
	$is_video = is_string( $mime ) && 0 === strpos( $mime, 'video/' );
	if ( empty( $check['ext'] ) || ( ! $is_image && ! $is_video ) ) {
		wp_delete_file( $temp );
		return new WP_Error( 'telegram_invalid_media_type', 'Допустимы только изображения и видеофайлы.' );
	}
	if ( $is_video && ! in_array( $mime, array( 'video/mp4', 'video/quicktime', 'video/webm', 'video/ogg' ), true ) ) {
		wp_delete_file( $temp );
		return new WP_Error( 'telegram_invalid_video_type', 'Видео должно быть MP4, M4V, MOV, WebM или OGV.' );
	}

	$previous_user = get_current_user_id();
	wp_set_current_user( $author->ID );
	$attachment_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $temp,
			'error'    => 0,
			'size'     => filesize( $temp ),
		),
		0,
		sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ),
		array( 'post_author' => $author->ID )
	);
	wp_set_current_user( $previous_user );

	if ( is_wp_error( $attachment_id ) ) {
		if ( file_exists( $temp ) ) {
			wp_delete_file( $temp );
		}
		return new WP_Error( 'telegram_media_import_failed', 'WordPress не смог сохранить файл в медиатеке.' );
	}

	return absint( $attachment_id );
}

/**
 * Import pending image and video before recipe creation.
 *
 * @param int   $user_id Telegram user ID.
 * @param int   $chat_id Chat ID.
 * @param array $data Conversation data passed by reference.
 * @return true|WP_Error
 */
function oxboxwise_telegram_prepare_media( $user_id, $chat_id, &$data ) {
	$fields = array(
		array( '_featured_media_source', 'featured_media_id' ),
		array( '_recipe_video_source', 'recipe_video_id' ),
	);
	foreach ( $fields as $fields_pair ) {
		list( $source_key, $attachment_key ) = $fields_pair;
		if ( ! empty( $data[ $attachment_key ] ) || empty( $data[ $source_key ] ) ) {
			continue;
		}
		$attachment_id = oxboxwise_telegram_import_media( $data[ $source_key ] );
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}
		$data[ $attachment_key ] = $attachment_id;
		oxboxwise_telegram_save_state( $user_id, $chat_id, 'confirm', $data );
	}

	return true;
}

/**
 * Human-readable recipe confirmation.
 *
 * @param array $data Recipe data.
 * @return string
 */
function oxboxwise_telegram_recipe_summary( $data ) {
	return "Проверьте рецепт:\n"
		. 'Название: ' . ( isset( $data['title'] ) ? $data['title'] : '' ) . "\n"
		. 'Категория ID: ' . wp_json_encode( isset( $data['recipe_category_ids'] ) ? $data['recipe_category_ids'] : array() ) . "\n"
		. 'Ингредиенты IDs: ' . wp_json_encode( isset( $data['recipe_ingredient_ids'] ) ? $data['recipe_ingredient_ids'] : array() ) . "\n"
		. 'Теги IDs: ' . wp_json_encode( isset( $data['tag_ids'] ) ? $data['tag_ids'] : array() ) . "\n"
		. 'Изображение: ' . ( ! empty( $data['_featured_media_source'] ) || ! empty( $data['featured_media_id'] ) ? 'да' : 'нет' ) . "\n"
		. 'Видео: ' . ( ! empty( $data['_recipe_video_source'] ) || ! empty( $data['recipe_video_id'] ) ? 'да' : 'нет' ) . "\n"
		. 'Статус: ' . ( isset( $data['status'] ) ? $data['status'] : 'draft' );
}

/**
 * Create a recipe through the same validated callback as the external REST API.
 *
 * @param array $data Conversation data.
 * @return array|WP_Error
 */
function oxboxwise_telegram_create_recipe( $data ) {
	$payload = array();
	foreach ( $data as $key => $value ) {
		if ( 0 !== strpos( $key, '_' ) ) {
			$payload[ $key ] = $value;
		}
	}

	$request = new WP_REST_Request( 'POST', '/oxboxwise/v1/recipes' );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_body( wp_json_encode( $payload ) );
	$response = oxboxwise_recipe_api_create_recipe( $request );
	if ( ! $response instanceof WP_REST_Response ) {
		return new WP_Error( 'telegram_recipe_creation_failed', 'WordPress не смог создать рецепт.' );
	}
	$result = $response->get_data();
	if ( $response->get_status() >= 400 || empty( $result['success'] ) ) {
		$message = isset( $result['error']['message'] ) ? $result['error']['message'] : 'WordPress не смог создать рецепт.';
		return new WP_Error( 'telegram_recipe_creation_failed', $message );
	}

	return $result;
}

/**
 * Process a Telegram inline-button callback.
 *
 * @param array $callback Callback query.
 * @return true|WP_Error
 */
function oxboxwise_telegram_handle_callback( $callback ) {
	$callback_id = isset( $callback['id'] ) ? (string) $callback['id'] : '';
	$user_id     = absint( isset( $callback['from']['id'] ) ? $callback['from']['id'] : 0 );
	$message     = isset( $callback['message'] ) && is_array( $callback['message'] ) ? $callback['message'] : array();
	$chat_id     = (int) ( isset( $message['chat']['id'] ) ? $message['chat']['id'] : 0 );
	$message_id  = absint( isset( $message['message_id'] ) ? $message['message_id'] : 0 );
	$action      = isset( $callback['data'] ) ? (string) $callback['data'] : '';

	if ( ! oxboxwise_telegram_user_is_allowed( $user_id ) ) {
		oxboxwise_telegram_answer_callback( $callback_id, 'Доступ запрещён' );
		return true;
	}
	$state = oxboxwise_telegram_get_state( $user_id );
	if ( ! $state || (int) $state['chat_id'] !== $chat_id ) {
		oxboxwise_telegram_answer_callback( $callback_id, 'Диалог уже завершён' );
		return true;
	}

	$step = isset( $state['step'] ) ? $state['step'] : '';
	$data = isset( $state['data'] ) && is_array( $state['data'] ) ? $state['data'] : array();
	if ( 0 === strpos( $action, 'status:' ) && 'status' === $step ) {
		$status = substr( $action, 7 );
		if ( ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
			oxboxwise_telegram_answer_callback( $callback_id, 'Некорректный статус' );
			return true;
		}
		$data['status'] = $status;
		oxboxwise_telegram_save_state( $user_id, $chat_id, 'confirm', $data );
		oxboxwise_telegram_answer_callback( $callback_id );
		oxboxwise_telegram_send_message(
			$chat_id,
			oxboxwise_telegram_recipe_summary( $data ),
			oxboxwise_telegram_keyboard( array( array( array( 'Создать', 'confirm:create' ), array( 'Отмена', 'confirm:cancel' ) ) ) )
		);
		return true;
	}

	if ( 'confirm:cancel' === $action && 'confirm' === $step ) {
		oxboxwise_telegram_delete_state( $user_id );
		oxboxwise_telegram_answer_callback( $callback_id );
		oxboxwise_telegram_send_message( $chat_id, 'Создание рецепта отменено.' );
		return true;
	}
	if ( 'confirm:create' === $action && 'confirm' === $step ) {
		oxboxwise_telegram_answer_callback( $callback_id, 'Создаю рецепт…' );
		$media_result = oxboxwise_telegram_prepare_media( $user_id, $chat_id, $data );
		if ( is_wp_error( $media_result ) ) {
			return $media_result;
		}
		$result = oxboxwise_telegram_create_recipe( $data );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		oxboxwise_telegram_delete_state( $user_id );
		$text = 'Рецепт создан: ID ' . absint( $result['recipe_id'] ) . ', статус ' . sanitize_key( $result['status'] ) . '.';
		if ( ! empty( $result['permalink'] ) ) {
			$text .= "\n" . esc_url_raw( $result['permalink'] );
		}
		if ( ! empty( $result['edit_url'] ) ) {
			$text .= "\nРедактировать: " . esc_url_raw( $result['edit_url'] );
		}
		oxboxwise_telegram_send_message( $chat_id, $text );
		return true;
	}

	$pickers = array(
		'category'    => array( 'cat', 'recipe_category_ids', 'ingredients' ),
		'ingredients' => array( 'ing', 'recipe_ingredient_ids', 'tags' ),
		'tags'        => array( 'tag', 'tag_ids', 'image' ),
	);
	if ( ! isset( $pickers[ $step ] ) ) {
		oxboxwise_telegram_answer_callback( $callback_id, 'Эта кнопка больше не активна' );
		return true;
	}

	list( $prefix, $field, $next_step ) = $pickers[ $step ];
	if ( 0 === strpos( $action, $prefix . 'p:' ) ) {
		$page   = max( 1, absint( substr( $action, strlen( $prefix ) + 2 ) ) );
		$markup = oxboxwise_telegram_picker_markup( $step, $page, $data );
		if ( is_wp_error( $markup ) ) {
			return $markup;
		}
		oxboxwise_telegram_edit_markup( $chat_id, $message_id, $markup );
		oxboxwise_telegram_answer_callback( $callback_id );
		return true;
	}
	if ( 0 !== strpos( $action, $prefix . ':' ) ) {
		oxboxwise_telegram_answer_callback( $callback_id, 'Некорректное действие' );
		return true;
	}

	$value = substr( $action, strlen( $prefix ) + 1 );
	if ( in_array( $value, array( 'skip', 'done' ), true ) ) {
		if ( 'skip' === $value ) {
			$data[ $field ] = array();
		}
		oxboxwise_telegram_save_state( $user_id, $chat_id, $next_step, $data );
		oxboxwise_telegram_answer_callback( $callback_id );
		if ( in_array( $next_step, array( 'ingredients', 'tags' ), true ) ) {
			return oxboxwise_telegram_send_picker( $chat_id, $next_step, 1, $data );
		}
		oxboxwise_telegram_send_message( $chat_id, 'Отправьте изображение как фото/файл или /skip.' );
		return true;
	}

	$value_parts = explode( ':', $value, 2 );
	$term_id     = absint( $value_parts[0] );
	$current_page = isset( $value_parts[1] ) ? max( 1, absint( $value_parts[1] ) ) : 1;
	$taxonomy    = array( 'category' => 'recipe_category', 'ingredients' => 'recipe_ingredient', 'tags' => 'post_tag' )[ $step ];
	if ( ! $term_id || ! term_exists( $term_id, $taxonomy ) ) {
		oxboxwise_telegram_answer_callback( $callback_id, 'Термин больше не существует' );
		return true;
	}
	if ( 'category' === $step ) {
		$data[ $field ] = array( $term_id );
		oxboxwise_telegram_save_state( $user_id, $chat_id, $next_step, $data );
		oxboxwise_telegram_answer_callback( $callback_id );
		return oxboxwise_telegram_send_picker( $chat_id, $next_step, 1, $data );
	}

	$selected = array_map( 'absint', isset( $data[ $field ] ) && is_array( $data[ $field ] ) ? $data[ $field ] : array() );
	if ( in_array( $term_id, $selected, true ) ) {
		$selected = array_values( array_diff( $selected, array( $term_id ) ) );
	} else {
		$selected[] = $term_id;
	}
	$selected      = array_values( array_unique( $selected ) );
	sort( $selected );
	$data[ $field ] = $selected;
	oxboxwise_telegram_save_state( $user_id, $chat_id, $step, $data );
	$markup = oxboxwise_telegram_picker_markup( $step, $current_page, $data );
	if ( is_wp_error( $markup ) ) {
		return $markup;
	}
	oxboxwise_telegram_edit_markup( $chat_id, $message_id, $markup );
	oxboxwise_telegram_answer_callback( $callback_id );
	return true;
}

/**
 * Log a sanitized Telegram processing error.
 *
 * @param string $code Error code.
 * @param int    $update_id Update ID.
 * @param int    $user_id Telegram user ID.
 */
function oxboxwise_telegram_log_error( $code, $update_id, $user_id ) {
	$context = array(
		'endpoint'   => OXBOXWISE_TELEGRAM_WEBHOOK_ROUTE,
		'error_code' => sanitize_key( $code ),
		'update_id'  => absint( $update_id ),
		'user_id'    => absint( $user_id ),
	);
	do_action( 'oxboxwise_telegram_webhook_error', $context );
	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( '[oxboxwise-telegram] ' . wp_json_encode( $context ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Handle one authenticated Telegram update.
 *
 * A short global lock serializes updates, while the bounded processed-ID list
 * prevents Telegram retries from advancing the conversation twice.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function oxboxwise_telegram_webhook( $request ) {
	$update = $request->get_json_params();
	if ( ! is_array( $update ) || empty( $update['update_id'] ) ) {
		return new WP_REST_Response( array( 'success' => false, 'error' => array( 'code' => 'invalid_telegram_update', 'message' => 'Invalid Telegram update.' ) ), 400 );
	}

	$update_id = absint( $update['update_id'] );
	$lock_name = 'oxboxwise_telegram_webhook_lock';
	$lock_time = absint( get_option( $lock_name ) );
	if ( $lock_time && $lock_time < time() - 180 ) {
		delete_option( $lock_name );
	}
	if ( ! add_option( $lock_name, time(), '', 'no' ) ) {
		return new WP_REST_Response( array( 'success' => false, 'error' => array( 'code' => 'telegram_webhook_busy', 'message' => 'Webhook is busy.' ) ), 503 );
	}

	$processed = get_option( 'oxboxwise_telegram_processed_updates', array() );
	$processed = is_array( $processed ) ? array_map( 'absint', $processed ) : array();
	if ( in_array( $update_id, $processed, true ) ) {
		delete_option( $lock_name );
		return new WP_REST_Response( array( 'success' => true, 'duplicate' => true ), 200 );
	}

	$user_id = absint(
		isset( $update['message']['from']['id'] )
			? $update['message']['from']['id']
			: ( isset( $update['callback_query']['from']['id'] ) ? $update['callback_query']['from']['id'] : 0 )
	);
	$chat_id = (int) (
		isset( $update['message']['chat']['id'] )
			? $update['message']['chat']['id']
			: ( isset( $update['callback_query']['message']['chat']['id'] ) ? $update['callback_query']['message']['chat']['id'] : 0 )
	);

	if ( isset( $update['callback_query'] ) && is_array( $update['callback_query'] ) ) {
		$result = oxboxwise_telegram_handle_callback( $update['callback_query'] );
	} elseif ( isset( $update['message'] ) && is_array( $update['message'] ) ) {
		$result = oxboxwise_telegram_handle_message( $update['message'] );
	} else {
		$result = true;
	}

	if ( is_wp_error( $result ) ) {
		oxboxwise_telegram_log_error( $result->get_error_code(), $update_id, $user_id );
		if ( $chat_id ) {
			oxboxwise_telegram_send_message( $chat_id, 'Не удалось выполнить действие: ' . $result->get_error_message() );
		}
	}

	$processed[] = $update_id;
	$processed   = array_slice( array_values( array_unique( $processed ) ), -200 );
	update_option( 'oxboxwise_telegram_processed_updates', $processed, false );
	delete_option( $lock_name );

	return new WP_REST_Response( array( 'success' => true ), 200 );
}
