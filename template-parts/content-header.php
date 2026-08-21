<?php
/**
 * Template part for displaying header part
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

?>



<header class="header">

    <div class="container">
        <div class="header__inner df-sb-ac">
            <a href="<?php if ((is_front_page() || is_home())) {
                echo 'javascript:void(0);';
            } else {
                echo get_home_url();
            } ?>" class="logo">
                <? if(get_field('opt_site_logo','option')){?>
                    <img src="<?=get_field('opt_site_logo','option')['url']; ?>" alt="<?=get_bloginfo('name');?>">
                <? } else {?>
                    <img src="<?=get_template_directory_uri();?>/img/logo.png" alt="<?=get_bloginfo('name');?>">
                <? }?>
            </a>
            <div class="header-box">
                <div class="row df-sb-ac">
                    <!--            --><?php
                    //            wp_nav_menu( [
                    //                'theme_location'  => 'menu_main',
                    //                'container' => 'nav',
                    //                'container_class' => 'header__menu-top',
                    ////                'menu_class' => 'menu_main',
                    //                'depth'           => 1,
                    //            ] );
                    //            ?>
                    <nav class="header__menu-top">
                        <ul>

                            <li><a href="#">О компании</a></li>
                            <li><a href="#">Доставка и оплата</a></li>
                            <li><a href="#">Гарантия</a></li>
                            <li><a href="#">Контакты</a></li>
                        </ul>
                    </nav>
                    <? if($phone_site=get_field('opt_phone_site', 'option')){?>
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone_site) ?>" class="tel"><?=$phone_site; ?></a>
                    <? }?>
                </div>
                <div class="row df-sb-ac">

<!--                    --><?php //if( have_rows('opt_menu_cat_rep','option')){?>
<!--                        <nav class="header__menu-bottom">-->
<!--                            <ul>-->
<!--                                --><?//  while ( have_rows('opt_menu_cat_rep','option') ) : the_row();
//                                    $opt_menu_cat_rep_link = get_sub_field('opt_menu_cat_link');
//                                    if($opt_menu_cat_rep_link){?>
<!--                                        <li><a href="--><?//=$opt_menu_cat_rep_link['url'];?><!--">--><?//=$opt_menu_cat_rep_link['title'];?><!--</a></li>-->
<!--                                    --><?// } endwhile;?>
<!--                            </ul>-->
<!--                        </nav>-->
<!--                    --><?// }?>
                    <?=do_shortcode('[wise_search]')?>
                </div>
            </div>
            <div class="header-box df">
                <div class="custom-select language">
                    <select>
                        <option data-lang="ru|ru" value="1">RU</option>
                        <option data-lang="ru|en" value="0">ENG</option>
                    </select>
                </div>
<!--                --><?// oxboxwise_woocommerce_cart_link(); ?>
                <div class="header__btn js-modal-open" data-modal-id="modal-menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="19" viewBox="0 0 32 19" fill="none">
                        <rect x="8" width="24" height="3" rx="1.5" fill="#f3b946" />
                        <path d="M0 9.5C0 8.67157 0.671573 8 1.5 8H30.5C31.3284 8 32 8.67157 32 9.5V9.5C32 10.3284 31.3284 11 30.5 11H1.5C0.671573 11 0 10.3284 0 9.5V9.5Z" fill="#f3b946" />
                        <rect x="14" y="16" width="18" height="3" rx="1.5" fill="#f3b946" />
                    </svg>
                </div>
            </div>

        </div>
    </div>
</header>