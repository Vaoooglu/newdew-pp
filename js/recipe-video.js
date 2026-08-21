( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.recipe-video__button' );
		if ( ! button ) {
			return;
		}

		var wrapper = button.closest( '.recipe-video' );
		var videoId = wrapper.dataset.youtubeId;
		if ( ! /^[A-Za-z0-9_-]{11}$/.test( videoId ) ) {
			return;
		}

		var iframe = document.createElement( 'iframe' );
		iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent( videoId ) + '?autoplay=1';
		iframe.title = button.getAttribute( 'aria-label' ).replace( 'Воспроизвести видео: ', '' );
		iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
		iframe.allowFullscreen = true;
		wrapper.replaceChildren( iframe );
	} );
}() );
