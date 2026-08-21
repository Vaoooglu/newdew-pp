<?php
/**
 * Template part for displaying part breadcrumbs
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

?>

    <? if( function_exists('bcn_display') ){?>
        <div class="breadcrumbs">
            <ul>
                <?php bcn_display_list();?>
            </ul>
        </div>
        <script type="application/ld+json"><?php bcn_display_json_ld();?></script>
    <? }?>
