( function () {
	'use strict';

	function getPostId() {
		var postId = document.getElementById( 'post_ID' );
		return postId ? postId.value : '';
	}

	function initializeImporter( importer ) {
		var field = importer.closest( '.acf-field' );
		var urlInput = field ? field.querySelector( 'input[type="url"]' ) : null;
		var button = importer.querySelector( '.recipe-youtube-importer__button' );
		var spinner = importer.querySelector( '.spinner' );
		var status = importer.querySelector( '.recipe-youtube-importer__status' );
		var result = importer.querySelector( '.recipe-youtube-importer__result' );
		var importedTitle = importer.querySelector( '[data-youtube-title]' ).textContent || '';

		if ( ! urlInput || ! button ) {
			return;
		}

		button.addEventListener( 'click', function () {
			var body = new URLSearchParams();
			body.append( 'action', 'oxboxwise_import_youtube' );
			body.append( 'nonce', oxboxwiseRecipeAdmin.nonce );
			body.append( 'postId', getPostId() );
			body.append( 'url', urlInput.value );

			button.disabled = true;
			spinner.classList.add( 'is-active' );
			status.textContent = 'Получаем данные…';
			result.hidden = true;

			fetch( oxboxwiseRecipeAdmin.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			} )
				.then( function ( response ) { return response.json(); } )
				.then( function ( response ) {
					if ( ! response.success ) {
						throw new Error( response.data && response.data.message ? response.data.message : 'Не удалось получить данные.' );
					}

					importedTitle = response.data.title || '';
					result.querySelector( '[data-youtube-title]' ).textContent = importedTitle;
					result.querySelector( '[data-youtube-description]' ).value = response.data.description || '';
					result.querySelector( '[data-youtube-thumbnail]' ).src = response.data.attachment_url || response.data.thumbnail;
					result.querySelector( '[data-youtube-thumbnail]' ).alt = importedTitle;
					result.hidden = false;
					status.textContent = 'Данные получены. Миниатюра сохранена в медиатеке и назначена записи.';

					var titleInput = document.getElementById( 'title' );
					if ( titleInput && ! titleInput.value.trim() ) {
						titleInput.value = importedTitle;
						titleInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
					}
				} )
				.catch( function ( error ) {
					status.textContent = error.message;
				} )
				.finally( function () {
					button.disabled = false;
					spinner.classList.remove( 'is-active' );
				} );
		} );

		importer.querySelector( '[data-youtube-use-title]' ).addEventListener( 'click', function () {
			var titleInput = document.getElementById( 'title' );
			if ( titleInput && importedTitle ) {
				titleInput.value = importedTitle;
				titleInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			}
		} );

		importer.querySelector( '[data-youtube-copy-description]' ).addEventListener( 'click', function () {
			var description = result.querySelector( '[data-youtube-description]' );
			description.select();
			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( description.value );
			} else {
				document.execCommand( 'copy' );
			}
			status.textContent = 'Описание скопировано. Вставьте его в рецепт там, где нужно.';
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.recipe-youtube-importer' ).forEach( initializeImporter );
	} );
}() );
