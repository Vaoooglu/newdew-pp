<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

?>
<a href="<?=get_the_permalink(); ?>" class="services__item revealOnScroll" data-animation="fadeInDown">
    <div class="services__item-img">
        <? the_post_thumbnail('wise_over'); ?>
    </div>
    <div class="services__item-icon">
        <? if(get_field('uslugipost_svg')){?>
            <div class="advantage_svg" style="background-image: url(<?=get_field('uslugipost_svg')['url'];?>)"></div>
        <? } else {?>
            <div class="advantage_svg" style="background-image: url('<?=get_template_directory_uri();?>/img/uslimg.svg')"></div>
        <? }?>
    </div>
    <div class="services__item-bottom">
        <p><?=get_the_title(); ?></p>
        <div class="services__item-arrow">
            <svg class="arrow-icon">
                <use xlink:href="#arrow-icon">
            </svg>
        </div>
    </div>
</a>