<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
/**
 * добавляем кастомную категорию Врачи
 */
//function register_wisedev_doctors_post_type(){
//    register_post_type('spetsialisty', array(
//        'labels'             => array(
//            'name'               => __('Врачи'), // Основное название типа записи
//            'singular_name'      => __('Врачи'),
//            'add_new'            => 'Добавить нового врача',
//            'add_new_item'       => 'Добавить нового врача',
//            'edit_item'          => 'Редактировать',
//            'new_item'           => 'Новый врач',
//            'view_item'          => 'Посмотреть врача',
//            'search_items'       => 'Найти врача',
//            'not_found'          =>  'Врач не найден',
//            'not_found_in_trash' => 'В корзине нет врачей',
//            'parent_item_colon'  => '',
//            'all_items'             => 'Все врачи',
//            'menu_name'          => __('Врачи')
//
//        ),
//        'public'             => true,
//        'publicly_queryable' => true,
//        'show_ui'            => true,
//        'show_in_menu'       => true,
//        'show_in_nav_menus'  => true,
//        'query_var'          => true,
//        'rewrite'            => 'spetsialisty',
//        'capability_type'    => 'post',
//        'has_archive'        => true,
//        'hierarchical'       => false,
//        'menu_position'      => 6,
//        'menu_icon'					 => 'dashicons-welcome-learn-more',
//        'supports'           => array('title','editor','thumbnail'),
//        'show_in_rest'          => true,
//    ) );
//}
//add_action('init', 'register_wisedev_doctors_post_type');

/**
 * добавляем кастомную категорию Услуги и цены
 */
//function register_wisedev_services_post_type(){
//    register_taxonomy('uslugi-cat', array('uslugi'), array(
//        'label'                 => 'Категории', // определяется параметром $labels->name
//        'labels'                => array(
//            'name'              => 'Категории услуг',
//            'singular_name'     => 'Категория',
//            'search_items'      => 'Искать категорию услуг',
//            'all_items'         => 'Все категории услуг',
//            'parent_item'       => 'Родит. категория услуг',
//            'parent_item_colon' => 'Родит. категория услуг:',
//            'edit_item'         => 'Ред. категорию услуг',
//            'update_item'       => 'Обновить категорию услуг',
//            'add_new_item'      => 'Добавить категорию услуг',
//            'new_item_name'     => 'Новая категория услуг',
//            'menu_name'         => 'Категория услуг',
//        ),
//        'description'           => 'Категории для услуг',
//        'public'                => true,
//        'show_in_nav_menus'     => true,
//        'show_ui'               => true,
//        'show_in_rest'          => true,
//        'show_tagcloud'         => true,
//        'hierarchical'          => true,
//        'rewrite'               => array(
//            'hierarchical'=>true,
//            'with_front'=>true,
//            'feed'=>false ),
//        'show_admin_column'     => true,
//    ) );
//    register_post_type('uslugi', array(
//        'labels'             => array(
//            'name'               => 'Услуги и цены', // Основное название типа записи
//            'singular_name'      => 'Услуги и цены',
//            'add_new'            => 'Добавить новую услугу',
//            'add_new_item'       => 'Добавить новую услугу',
//            'edit_item'          => 'Редактировать',
//            'new_item'           => 'Новая услуга',
//            'view_item'          => 'Посмотреть услугу',
//            'search_items'       => 'Найти услугу',
//            'not_found'          =>  'Услуга не найдена',
//            'not_found_in_trash' => 'В корзине нет услуг',
//            'parent_item_colon'  => '',
//            'all_items'             => 'Все услуги',
//            'menu_name'          => 'Услуги и цены'
//
//        ),
//        'public'             => true,
//        'publicly_queryable' => true,
//        'show_ui'            => true,
//        'show_in_menu'       => true,
//        'show_in_nav_menus'  => true,
//        'query_var'          => true,
//        'rewrite'            => 'uslugi',
//        'capability_type'    => 'post',
//        'has_archive'        => true,
//        'hierarchical'       => false,
//        'menu_position'      => 7,
//        'menu_icon'					 => 'dashicons-welcome-add-page',
//        'supports'           => array('title','editor','thumbnail'),
//        'show_in_rest'          => true,
//    ) );
//}
//add_action('init', 'register_wisedev_services_post_type');
//


