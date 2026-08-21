<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register recipes and their public taxonomies.
 */
function oxboxwise_register_recipe_content() {
	register_post_type(
		'recipe',
		array(
			'labels' => array(
				'name'                  => 'Рецепты',
				'singular_name'         => 'Рецепт',
				'menu_name'             => 'Рецепты',
				'add_new'               => 'Добавить рецепт',
				'add_new_item'          => 'Добавить рецепт',
				'edit_item'             => 'Редактировать рецепт',
				'new_item'              => 'Новый рецепт',
				'view_item'             => 'Посмотреть рецепт',
				'view_items'            => 'Посмотреть рецепты',
				'search_items'          => 'Найти рецепты',
				'not_found'             => 'Рецепты не найдены',
				'not_found_in_trash'    => 'В корзине рецептов нет',
				'all_items'             => 'Все рецепты',
				'archives'              => 'Архив рецептов',
				'featured_image'        => 'Изображение рецепта',
				'set_featured_image'    => 'Задать изображение рецепта',
				'remove_featured_image' => 'Удалить изображение рецепта',
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'recipes' ),
			'menu_icon'    => 'dashicons-food',
			'menu_position'=> 5,
			'show_in_rest' => false,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'comments' ),
			'taxonomies'   => array( 'post_tag' ),
		)
	);

	register_taxonomy(
		'recipe_category',
		array( 'recipe' ),
		array(
			'labels' => array(
				'name'          => 'Категории рецептов',
				'singular_name' => 'Категория рецепта',
				'search_items'  => 'Найти категории',
				'all_items'     => 'Все категории',
				'edit_item'     => 'Редактировать категорию',
				'update_item'   => 'Обновить категорию',
				'add_new_item'  => 'Добавить категорию',
				'new_item_name' => 'Название новой категории',
				'menu_name'     => 'Категории',
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => array( 'slug' => 'recipe-category' ),
		)
	);

	register_taxonomy(
		'recipe_ingredient',
		array( 'recipe' ),
		array(
			'labels' => array(
				'name'                       => 'Ингредиенты',
				'singular_name'              => 'Ингредиент',
				'search_items'               => 'Найти ингредиенты',
				'popular_items'              => 'Популярные ингредиенты',
				'all_items'                  => 'Все ингредиенты',
				'edit_item'                  => 'Редактировать ингредиент',
				'update_item'                => 'Обновить ингредиент',
				'add_new_item'               => 'Добавить ингредиент',
				'new_item_name'              => 'Название нового ингредиента',
				'separate_items_with_commas' => 'Разделяйте ингредиенты запятыми',
				'add_or_remove_items'        => 'Добавить или удалить ингредиенты',
				'choose_from_most_used'      => 'Выбрать из часто используемых',
				'menu_name'                  => 'Ингредиенты',
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => array( 'slug' => 'ingredient' ),
		)
	);

	register_taxonomy_for_object_type( 'post_tag', 'recipe' );
}
add_action( 'init', 'oxboxwise_register_recipe_content' );

/**
 * Refresh rewrite rules once when the theme is activated.
 */
function oxboxwise_recipe_rewrite_flush() {
	oxboxwise_register_recipe_content();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'oxboxwise_recipe_rewrite_flush' );

/**
 * Normalize ingredient names so differently cased values are not duplicated.
 *
 * @param string $term     Term name.
 * @param string $taxonomy Taxonomy name.
 * @return string
 */
function oxboxwise_normalize_recipe_ingredient( $term, $taxonomy ) {
	if ( 'recipe_ingredient' !== $taxonomy ) {
		return $term;
	}

	$term = preg_replace( '/\s+/u', ' ', trim( wp_strip_all_tags( $term ) ) );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $term, 'UTF-8' ) : strtolower( $term );
}
add_filter( 'pre_insert_term', 'oxboxwise_normalize_recipe_ingredient', 10, 2 );
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


