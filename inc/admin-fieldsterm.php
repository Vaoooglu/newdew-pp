<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}

/**
 * Add column in admin for taxonomy KATALOG-KAMAZ
 */
//add_filter( 'manage_edit-'.'katalog-kamaz'.'_columns', 'add_views_column_katalog_kamaz');
//function add_views_column_katalog_kamaz( $columns ){
//    $num = 2; // после какой по счету колонки вставлять новые
//    $new_columns = array(
//        'tax_post_img' => 'Миниатюра',
//    );
//    return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
//}
//add_action('manage_'.'katalog-kamaz'.'_custom_column', 'fill_views_column_katalog_kamaz', 10, 3 );
//function fill_views_column_katalog_kamaz( $string, $colname, $term_id ){
//    $term= get_term($term_id, 'katalog-kamaz');
//    if ($colname === 'tax_post_img'){
//        if(get_field('tax_katalog_img', $term)) {?>
<!--            <img src="--><?//=get_field('tax_katalog_img', $term)['sizes']['thumbnail'];?><!--">-->
<!--        --><?// }  else {
//            echo 'Отсутствует';
//        }
//    }
//
//}
