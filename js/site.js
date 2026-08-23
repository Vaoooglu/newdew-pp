( function () {
	'use strict';

	var activePanel = null;
	var panelTrigger = null;
	var backdrop = document.querySelector( '.site-panel-backdrop' );
	var favoriteStorageKey = 'oxboxwise_recipe_favorites';

	function openPanel( panelId, trigger ) {
		var panel = document.getElementById( panelId );
		if ( ! panel ) {
			return;
		}

		if ( activePanel ) {
			closePanel( false );
		}

		activePanel = panel;
		panelTrigger = trigger;
		panel.hidden = false;
		panel.setAttribute( 'aria-hidden', 'false' );
		if ( backdrop ) {
			backdrop.hidden = false;
		}
		document.body.classList.add( 'panel-open' );
		trigger.setAttribute( 'aria-expanded', 'true' );

		window.requestAnimationFrame( function () {
			panel.classList.add( 'is-open' );
			if ( backdrop ) {
				backdrop.classList.add( 'is-open' );
			}
			var focusTarget = panel.querySelector( 'input[type="search"]' ) || panel.querySelector( 'input, select, button, a[href]' );
			if ( focusTarget ) {
				focusTarget.focus();
			}
		} );
	}

	function closePanel( restoreFocus ) {
		if ( ! activePanel ) {
			return;
		}

		var panel = activePanel;
		var trigger = panelTrigger;
		panel.classList.remove( 'is-open' );
		panel.setAttribute( 'aria-hidden', 'true' );
		if ( backdrop ) {
			backdrop.classList.remove( 'is-open' );
		}
		document.body.classList.remove( 'panel-open' );
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
		}

		window.setTimeout( function () {
			panel.hidden = true;
			if ( backdrop && ! activePanel ) {
				backdrop.hidden = true;
			}
		}, 240 );

		activePanel = null;
		panelTrigger = null;
		if ( restoreFocus !== false && trigger ) {
			trigger.focus();
		}
	}

	function readFavorites() {
		try {
			var stored = JSON.parse( window.localStorage.getItem( favoriteStorageKey ) || '[]' );
			return Array.isArray( stored ) ? stored : [];
		} catch ( error ) {
			return [];
		}
	}

	function writeFavorites( favorites ) {
		try {
			window.localStorage.setItem( favoriteStorageKey, JSON.stringify( favorites ) );
		} catch ( error ) {
			return;
		}
	}

	function updateFavoriteButtons() {
		var ids = readFavorites().map( function ( recipe ) { return String( recipe.id ); } );
		document.querySelectorAll( '[data-favorite-toggle]' ).forEach( function ( button ) {
			var card = button.closest( '[data-recipe-card]' );
			var isFavorite = card && ids.indexOf( String( card.dataset.recipeId ) ) !== -1;
			button.classList.toggle( 'is-active', isFavorite );
			button.setAttribute( 'aria-pressed', isFavorite ? 'true' : 'false' );
			button.setAttribute( 'aria-label', isFavorite ? 'Удалить рецепт из избранного' : 'Добавить рецепт в избранное' );
			var label = button.querySelector( 'span' );
			if ( label ) {
				label.textContent = isFavorite ? 'В избранном' : 'В избранное';
			}
		} );
	}

	function renderFavorites() {
		var list = document.querySelector( '[data-favorites-list]' );
		var empty = document.querySelector( '[data-favorites-empty]' );
		if ( ! list ) {
			return;
		}

		var favorites = readFavorites();
		list.replaceChildren();
		if ( empty ) {
			empty.hidden = favorites.length > 0;
		}

		favorites.forEach( function ( recipe ) {
			var link = document.createElement( 'a' );
			var media = document.createElement( recipe.image ? 'img' : 'span' );
			var title = document.createElement( 'strong' );
			link.className = 'favorite-item';
			link.href = recipe.url;
			if ( recipe.image ) {
				media.src = recipe.image;
				media.alt = '';
				media.loading = 'lazy';
			} else {
				media.className = 'favorite-item__placeholder';
				media.textContent = recipe.title ? recipe.title.charAt( 0 ) : 'Р';
			}
			title.textContent = recipe.title;
			link.append( media, title );
			list.append( link );
		} );
	}

	function toggleFavorite( button ) {
		var card = button.closest( '[data-recipe-card]' );
		if ( ! card ) {
			return;
		}

		var recipe = {
			id: card.dataset.recipeId,
			title: card.dataset.recipeTitle,
			url: card.dataset.recipeUrl,
			image: card.dataset.recipeImage || ''
		};
		var favorites = readFavorites();
		var existingIndex = favorites.findIndex( function ( item ) { return String( item.id ) === String( recipe.id ); } );
		if ( existingIndex === -1 ) {
			favorites.unshift( recipe );
		} else {
			favorites.splice( existingIndex, 1 );
		}
		writeFavorites( favorites.slice( 0, 50 ) );
		updateFavoriteButtons();
		renderFavorites();
	}

	function shareRecipe( button ) {
		var card = button.closest( '[data-recipe-card]' );
		var status = card ? card.parentElement.querySelector( '[data-action-status]' ) : null;
		if ( ! card ) {
			return;
		}

		var data = { title: card.dataset.recipeTitle, url: card.dataset.recipeUrl };
		if ( navigator.share ) {
			navigator.share( data ).catch( function () {} );
		} else if ( navigator.clipboard ) {
			navigator.clipboard.writeText( data.url ).then( function () {
				if ( status ) {
					status.textContent = 'Ссылка скопирована.';
				}
			} );
		}
	}

	function updateRecipeVideoOrientation( video ) {
		var frame = video.closest( '[data-recipe-video-frame]' );
		var width = video.videoWidth;
		var height = video.videoHeight;
		if ( ! frame || ! width || ! height ) {
			return;
		}

		var orientation = width > height ? 'landscape' : ( height > width ? 'portrait' : 'square' );
		frame.classList.remove( 'recipe-video--unknown', 'recipe-video--landscape', 'recipe-video--portrait', 'recipe-video--square' );
		frame.classList.add( 'recipe-video--' + orientation );
		frame.style.setProperty( '--recipe-video-aspect', width + ' / ' + height );
	}

	document.addEventListener( 'click', function ( event ) {
		var opener = event.target.closest( '[data-panel-open]' );
		if ( opener ) {
			openPanel( opener.dataset.panelOpen, opener );
			return;
		}
		if ( event.target.closest( '[data-panel-close]' ) ) {
			closePanel();
			return;
		}
		var favoriteButton = event.target.closest( '[data-favorite-toggle]' );
		if ( favoriteButton ) {
			toggleFavorite( favoriteButton );
			return;
		}
		var shareButton = event.target.closest( '[data-share-recipe]' );
		if ( shareButton ) {
			shareRecipe( shareButton );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && activePanel ) {
			closePanel();
			return;
		}
		if ( 'Tab' === event.key && activePanel ) {
			var focusable = Array.prototype.slice.call( activePanel.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled])' ) );
			if ( ! focusable.length ) {
				return;
			}
			var first = focusable[0];
			var last = focusable[ focusable.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	updateFavoriteButtons();
	renderFavorites();
	document.querySelectorAll( '[data-recipe-video]' ).forEach( function ( video ) {
		if ( video.readyState >= 1 ) {
			updateRecipeVideoOrientation( video );
		} else {
			video.addEventListener( 'loadedmetadata', function () {
				updateRecipeVideoOrientation( video );
			}, { once: true } );
		}
	} );
}() );
