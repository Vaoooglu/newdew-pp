<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
/**
 * Register Blocks
 * @see https://www.billerickson.net/building-gutenberg-block-acf/#register-block
 *
 */
function be_register_blocks() {
    if( ! function_exists('acf_register_block') )
        return;
    acf_register_block( array(
        'name'			=> 'block_price',
        'title'			=> __( 'Стоимость услуг', 'oxboxwise' ),
        'render_template'	=> 'gutblocks/block_price.php',
        'category'		=> 'common',
        'icon'			=> 'admin-users',
        'mode'			=> 'edit',
        'keywords'		=> array( 'profile', 'user', 'author' ),
    ));
    acf_register_block( array(
        'name'			=> 'block_video',
        'title'			=> 'ВИДЕО vs Schema',
        'render_template'	=> 'gutblocks/block_video.php',
        'category'		=> 'common',
        'example'  => array(
            'attributes' => array(
                'mode' => 'preview',
            )
        ),
        'icon'			=> 'admin-users',
        'mode'			=> 'edit',
        'keywords'		=> array( 'profile', 'user', 'author' ),
        'supports' => array( 'multiple' => false ),
    ));


}
add_action('acf/init', 'be_register_blocks' );
