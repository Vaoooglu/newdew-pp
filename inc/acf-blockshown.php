<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
/**
 * Sets guttenberg blocks are show
 *
 */
add_filter('allowed_block_types', 'wiseoxbox_allowed_block_types');
function wiseoxbox_allowed_block_types($allowed_blocks)
{
    return array(
        'acf/block-titpage',
        'acf/block-gal',
        'acf/block-column2',
        'acf/block-variant',
        'acf/block-formemtel',
        'acf/block-slajdtext',
        'acf/block-2txtpic',
        'acf/block-steps',
        'acf/block-slajdpers',
        'acf/block-formmestel',
        'acf/block-vitrina',
        'acf/block-etag',
        'acf/block-vitrinaslaj',
        'acf/block-repare',
        'acf/block-pictxt',
        'acf/block-icons',
        'acf/block-icons2',
        'acf/block-techno',
        'acf/block-txtbtn',
        'acf/block-video',
        'acf/block-attention',
        'acf/block-formcheck',
        'acf/block-cards',
        'acf/block-cards2',
        'acf/block-advant',
        'acf/block-formgraph',
        'acf/block-trend',
//        'core/shortcode',
        'core/paragraph',
//        'core/heading',
        'core/spacer',
    );
}