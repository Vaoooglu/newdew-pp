<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}

function loadmore(){
    $args = unserialize( stripslashes( $_POST['query'] ) );
    $args['paged'] = $_POST['page'] + 1; // следующая страница
    $args['post_status'] = 'publish';
    // обычно лучше использовать WP_Query, но не здесь
    query_posts( $args );
    // если посты есть
    if( have_posts() ) :
        while( have_posts() ): the_post();?>
            <? get_template_part( 'template-parts/content', 'services' );?>
        <?php endwhile;
    endif;
    wp_die();
}
add_action('wp_ajax_loadmore', 'loadmore');
add_action('wp_ajax_nopriv_loadmore', 'loadmore');

function sendform(){
//    Передать name input с форм для отправки на почту и их названия в письме
//    поля name== PAGE, THEME, action - перечислять в массиве НЕ нужно!
//    если в форме есть отправка файлов - добавить
//    <input type="hidden" name="filesend" value="multiple">   value { one || multiple }
    $FIELDS=array(
        'NAME' => 'Имя',
        'TEL' => 'Телефон',
        'MAIL' => 'E-mail',
        'MESSAGE' => 'Cообщение',
    );

//  Заполняем поле имя "от кого" в почте
    add_filter( 'wp_mail_from_name', function($from_name){
        $from_nametxt = get_field('opt_blog_from','option') ? get_field('opt_blog_from','option') : 'Письмо с сайта';
        return $from_nametxt;
    });
// Заполняем поле e-mail "от кого" (можно заполнить в ОБЩИЕ НАСТРОЙКИ сайта )
    add_filter( 'wp_mail_from',  function( $email_address ){
        if(get_field('opt_email_from','option')){
            return get_field('opt_email_from','option');
        } else {
            return 'info@test.ru';
        };
    });
// Настройки цветов оформления письма
    $bgheader = get_field('opt_mailcolheader','option') ? get_field('opt_mailcolheader','option') : '#cccccc';
    $bgfooter = get_field('opt_mailcolfooter','option') ? get_field('opt_mailcolfooter','option') : '#002261';
    $bgfootertxt = get_field('opt_mailcoltxt','option') ? get_field('opt_mailcoltxt','option') : '#ffffff';

    $opt_mail_logo = get_field('opt_mail_logo', 'option');
    $qur_year = date('Y');
    $subject=get_field('opt_blog_from','option') ? get_field('opt_blog_from','option') : 'Заявка с сайта';
    $mail_send = get_field('opt_emailsend','option') ? get_field('opt_emailsend','option') : get_option('admin_email');

    add_filter( 'wp_mail_content_type',  function() {
        return 'text/html';
    });
    if($opt_mail_logo) {
        $opt_mail_logo_url = $opt_mail_logo['url'];
        $opt_mail_logo_alt = $opt_mail_logo['alt'];
        $opt_mail_logo_mail = '<a target="_blank" href="'.get_site_url().'"><img src="'.$opt_mail_logo_url.'" width="155" height="auto" alt="'.$opt_mail_logo_alt.'"></a>';
    } else {
        $opt_mail_logo_mail='';
    }
    $thm = $_REQUEST['THEME'];
    $page = $_REQUEST['PAGE'];
    $response = array();



    if(isset($page)){
        $msg_page=( $page!=='' ? '<tr><td><b>URL страницы</b></td><td>'.$page.'</td></tr>' : '');
    }
    else {
        $msg_page='';
    }
    if(isset($thm)){
        $msg_thm=( $thm!=='' ? '<tr><td style="padding:25px;text-align:center;"><span style="font-size:25px;line-height:1.3;">'.wp_strip_all_tags($thm).'</span></td></tr>' : '');
    }
    else {
        $thm = 'Нет заголовка';
        $msg_thm='';
    }
    $table_cont_before = '<tr align="center"><td><div><center><table border=1 cellpadding=6 cellspacing=0 width=90% bordercolor="#DBDBDB">';
    $table_cont_after = '</table></center></div></td></tr>';



    $msg_header='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=edge" /><meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="https://fonts.googleapis.com/css?family=Ubuntu:400,500" rel="stylesheet"><title></title></head><body style="margin:0;padding:0;min-width:100%;background-color:#F7F9FA;"><div style="width:100%;font-family:\'Ubuntu\',sans-serif;font-size:16px;font-weight:400;line-height:1.625;color:#1B2733;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td style="height:10px;"></td></tr><tr align="center"><td><div style="max-width:600px;background:'.$bgheader.';"><table style="max-width:600px;" cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td style="height:25px;"></td></tr><tr><td style="height:20px;color:#4c5d7c;text-align:center;line-height:1;">'.$opt_mail_logo_mail.'</td></tr><tr><td style="height:25px;"></td></tr></table></div></td></tr><tr align="center"><td><div style="max-width:600px;background:#FFF;border-left:1px solid rgba(99,114,130,0.2);border-right:1px solid rgba(99,114,130,0.2);"><table style="max-width:600px;" cellpadding="0" cellspacing="0" border="0" width="100%">';

    $msg_footer='<tr><td style="height:60px;"></td></tr></table></div></td></tr><tr align="center"><td><div style="max-width:600px;background:'.$bgfooter.';"><table style="max-width:600px;font-size:14px;color:'.$bgfootertxt.';" cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td style="padding:36px 0;text-align:center;"><div style="display:inline-block;"><table style="max-width:600px;line-height:1;" cellpadding="0" cellspacing="0" border="0"><tr></tr></table></div></td></tr><tr><td style="padding:0 28px 36px 28px;text-align:center;">© '.$qur_year.' Все права защищены</td></tr></table></div></td></tr><tr><td style="height:10px;"></td></tr></table></div></body></html>';

    $msg = $msg_header.$msg_thm.$table_cont_before;
    foreach ( $FIELDS as $key => $value ) {
        if ( $value != '' && $_REQUEST[$key] != '' ) {
            $msg .= '<tr><td><b>'.$value.'</b></td><td>'.$_REQUEST[$key].'</td></tr>';
        }
    }
    $msg .=$msg_page.$table_cont_after.$msg_footer;

    $mail_to = $mail_send;

    if(wp_mail($mail_to, $subject, $msg)){
        $response['SUCCESS'] = 'Y';
        $response['HTML'] = '<div class="response-popup"><h2>Спасибо!</h2><div class="response-popup__text">Наши менеджеры свяжутся с Вами в ближайшее время.</div></div>';
        $response['POST1'] = $_POST;
    } else {
        $response['SUCCESS'] = 'N';
        $response['MESSAGE'] = '<div class="response-popup"><h2>Извините, произошла ошибка отправки сообщения</h2><p>Попробуйте позже</p></div>';
    }
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ){
        wp_send_json( $response);

    } else {
        wp_die();
    }
}

add_action('wp_ajax_nopriv_sendform', 'sendform' );
add_action('wp_ajax_sendform', 'sendform' );