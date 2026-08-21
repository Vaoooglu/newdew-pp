jQuery(function ($) {
    $('.modal-menu a').on('click', function () {
        $(this).parent().parent().parent().parent().find('.icon-close').trigger('click');
    });

    // let google = getCookie('googtrans');
    // console.log('google',google);
    // if(google == '/ru/en'){
    //     $('.glink.enlang').removeClass('active');
    //     $('.glink.rulang').addClass('active');
    // } else {
    //     $('.glink.rulang').removeClass('active');
    //     $('.glink.enlang').addClass('active');
    // }
    // $('.glink').on('click', function () {
    //     $('.glink').toggleClass('active');
    // });
    // //центрирование активного элемента меню
    // if($(".current-menu-item").length){
    //     $(".aside_menu_ul").scrollCenter(".current-menu-item", 300);
    // }
});

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
jQuery.fn.scrollCenter = function(elem, speed) {
    var active = jQuery(this).find(elem); // find the active element
    var activeWidth = active.width() / 2; // get active width center
    var pos = active.position().left + activeWidth; //get left position of active li + center position
    var elpos = jQuery(this).scrollLeft(); // get current scroll position
    var elW = jQuery(this).width(); //get div width
    pos = pos + elpos - elW / 2; // for center position if you want adjust then change this
    jQuery(this).animate({
        scrollLeft: pos
    }, speed == undefined ? 1000 : speed);
    return this;
};