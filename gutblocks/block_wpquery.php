<?
$args_news = array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'cat' => 1
);

$query_news = new WP_Query( $args_news );
if ( $query_news->have_posts() ) {
    ?>
    <section class="news">
        <? if(get_field('main_bl7_tit')){?>
            <div class="section__title revealOnScroll" data-animation="fadeInDown">
                <div class="container">
                    <h2><?=get_field('main_bl7_tit');?></h2>
                </div>
            </div>
        <? }?>

        <div class="container">
            <div class="swiper news-slider revealOnScroll" data-animation="fadeInDown">
                <div class="swiper-wrapper">
                    <?
                    while ( $query_news->have_posts() ) {
                        $query_news->the_post();
                        ?>
                        <div class="swiper-slide">
                            <a href="<?=get_the_permalink();?>" class="news__item">
                                <div class="news__item-img">
                                    <? the_post_thumbnail('wise_over');?>
                                </div>
                                <div class="news__item-info">
                                    <p><?=get_the_date('d.m.Y'); ?></p>
                                    <p><?=get_the_title();?></p>
                                </div>
                            </a>
                        </div>
                    <? }?>
                </div>
            </div>
            <div class="news__bottom revealOnScroll" data-animation="fadeInDown">
                <div class="slider__arrows">
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
                <a href="<?=get_category_link(1);?>" class="link">
                                    <span>
                                        <svg class="arrow-icon">
                                            <use xlink:href="#arrow-icon">
                                        </svg>
                                    </span>
                    Все новости
                </a>
            </div>
        </div>

    </section>
<? } wp_reset_postdata();?>