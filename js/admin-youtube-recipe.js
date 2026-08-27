( function( $ ) {
	'use strict';

	var config = window.oxboxwiseYoutubeRecipe || {};
	var $button = $( '#oxboxwise-youtube-import-button' );
	var $url = $( '#oxboxwise-youtube-url' );
	var $status = $( '#oxboxwise-youtube-import-status' );

	function setEditorContent( content ) {
		if ( window.tinymce ) {
			var editor = window.tinymce.get( 'content' );
			if ( editor && ! editor.isHidden() ) {
				editor.setContent( content );
				editor.save();
				return;
			}
		}
		$( '#content' ).val( content ).trigger( 'change' );
	}

	function currentContent() {
		if ( window.tinymce ) {
			var editor = window.tinymce.get( 'content' );
			if ( editor && ! editor.isHidden() ) {
				return editor.getContent();
			}
		}
		return $( '#content' ).val() || '';
	}

	$button.on( 'click', function() {
		var sourceUrl = $.trim( $url.val() );
		if ( ! sourceUrl ) {
			$status.text( 'Сначала вставьте ссылку на YouTube.' );
			$url.trigger( 'focus' );
			return;
		}

		if ( ( $.trim( $( '#title' ).val() ) || $.trim( currentContent() ) ) && ! window.confirm( 'Заголовок или текст уже заполнены. Заменить их данными из YouTube?' ) ) {
			return;
		}

		$button.prop( 'disabled', true );
		$status.text( 'Получаем данные YouTube…' );

		$.post( config.ajaxUrl, {
			action: 'oxboxwise_import_youtube_recipe',
			nonce: config.nonce,
			post_id: config.postId,
			url: sourceUrl
		} ).done( function( response ) {
			if ( ! response || ! response.success ) {
				$status.text( response && response.data && response.data.message ? response.data.message : 'Не удалось импортировать видео.' );
				return;
			}

			$( '#title' ).val( response.data.title ).trigger( 'input' ).trigger( 'change' );
			setEditorContent( response.data.content );
			$( '[data-key="field_oxboxwise_recipe_youtube_url"] input' ).val( response.data.youtubeUrl ).trigger( 'change' );

			if ( response.data.attachmentId ) {
				$( '#_thumbnail_id' ).val( response.data.attachmentId );
				if ( response.data.thumbnailHtml ) {
					$( '#postimagediv .inside' ).html( response.data.thumbnailHtml );
				}
			}

			if ( response.data.hasDescription ) {
				$status.text( response.data.attachmentId ? 'Данные добавлены. Проверьте и отредактируйте рецепт.' : 'Заголовок, описание и ссылка добавлены, но превью загрузить не удалось.' );
			} else {
				$status.text( response.data.attachmentId ? 'Заголовок, превью и ссылка добавлены. Описание видео получить не удалось.' : 'Заголовок и ссылка добавлены. Описание и превью получить не удалось.' );
			}
		} ).fail( function( xhr ) {
			var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
			$status.text( message || 'Ошибка соединения при обращении к YouTube.' );
		} ).always( function() {
			$button.prop( 'disabled', false );
		} );
	} );
}( jQuery ) );
