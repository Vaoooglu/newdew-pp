<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
/**
 * Sets admin ACF Option Page
 *
 */
if(function_exists('acf_add_options_page')){
    $parent = acf_add_options_page(array(
        'menu_title' => 'ОБЩИЕ НАСТРОЙКИ сайта',
        'page_title' => 'ОБЩИЕ НАСТРОЙКИ сайта',
        'menu_slug' 	=> 'theme-general-settings',
        'redirect' => false,
        'position' =>16,
        'update_button'		=> __('Обновить данные', 'acf'),
        'updated_message'	=> __("Данные обновлены", 'acf'),
    ));
//    acf_add_options_sub_page(array(
//        'menu_title' 	=> 'Описания на Услуги и Сервис',
//        'page_title' => 'Описания на Услуги и Сервис',
//        'parent_slug' 	=> $parent['menu_slug'],
//        'update_button'		=> __('Обновить', 'acf'),
//        'updated_message'	=> __('Данные обновлены', 'acf'),
//    ));
}