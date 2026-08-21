<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
/**
 * Add column in admin for post type POST
 */
add_filter( 'manage_'.'post'.'_posts_columns', 'add_views_column_post', 4 );
function add_views_column_post( $columns ){
    $num = 2; // после какой по счету колонки вставлять новые
    $new_columns = array(
        'post_post_thumbs' => 'Миниатюра',
//        'post_field_post' => 'Доп поле',
    );

    return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}
add_action('manage_'.'post'.'_posts_custom_column', 'fill_views_column_post', 5, 2 );
function fill_views_column_post( $colname, $post_id ){
    if ($colname === 'post_post_thumbs'){
        if(has_post_thumbnail()) {
            the_post_thumbnail('thumbnail');
        }  else {
            echo 'Отсутствует';
        }
    }
//    elseif ($colname === 'post_field_post'){
//        if(get_field('acf_field', $post_id)){
//            echo get_field('acf_field', $post_id);
//        } else {
//            echo 'нет';
//        }
//    }
}
