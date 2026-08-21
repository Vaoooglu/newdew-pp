<?php
/**
 * Template part for displaying footer part
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

?>
<footer class="footer">
    <div class="container">
        <div class="footer__inner">
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
            <nav class="footer__menu">
                <ul>
                    <li><a href="#">Услуги</a></li>
                    <li><a href="#">О компании</a></li>
                    <li><a href="#">Контакты</a></li>
                    <li><a href="#">Блог</a></li>
                </ul>
            </nav>
<!--            --><?php
//            wp_nav_menu( [
//                'theme_location'  => 'menu_footer',
//                'container' => 'nav',
//                'container_class' => 'footer__menu',
////                'menu_class' => 'modal-menu__list',
//                'depth'           => 1,
//            ] );
//            ?>
            <div class="footer__copyright">
                &copy; <?=date("Y"); ?> Все права зищищены
            </div>
        </div>
    </div>
</footer>
<? get_template_part( 'template-parts/content', 'svg' );?>
<div class="modal-container js-modal-container">
    <div class="overlay js-modal-close-all"></div>
    <div class="modal menu js-modal" id="modal-menu">
        <div class="scroll-container">
            <div class="modal-menu__top">
                <span class="icon-close js-modal-close-all"></span>
            </div>
            <nav class="modal-menu">
                <ul class="modal-menu__list">
                    <li>
                        Услуги
                        <ul>
                            <li><a href="#">DevOps услуги</a></li>
                            <li><a href="#">Обслуживание серверов</a></li>
                            <li><a href="#">Облачные услуги</a></li>
                            <li><a href="#">Системная интеграция</a></li>
                            <li><a href="#">Обслуживание баз данных</a></li>
                            <li><a href="#">Услуги Big data</a></li>
                            <li><a href="#">Разработка личных кабинетов</a></li>
                            <li><a href="#">Перенос данных</a></li>
                        </ul>
                    </li>
                    <li><a href="#">О компании</a></li>
                    <li><a href="#">Контакты</a></li>
                    <li><a href="#">Блог</a></li>
                </ul>
            </nav>
            <? if($phone_site=get_field('opt_phone_site', 'option')){?>
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone_site) ?>" class="tel"><?=$phone_site; ?></a>
            <? }?>
        </div>
    </div>
</div>
<div class="modal-container js-modal-container">
    <div class="overlay js-modal-close-all"></div>
    <div class="modal js-modal" id="modal-feedback">
        <div class="modal-content-title">
            <div class="modal-content-title__box">
                <p>Обратная связь</p>
            </div>
            <span class="icon-close js-modal-close-all"></span>
        </div>
        <div class="scroll-container">
            <form action="#" data-controller="">
                <div class="input input-wrapper required">
                    <label class="input-label" for="feedback-name">Имя</label>
                    <input type="text" id="feedback-name" name="NAME" required>
                </div>
                <div class="input input-wrapper required">
                    <label class="input-label" for="feedback-tel">Телефон</label>
                    <input type="tel" id="feedback-tel" name="TEL" required>
                </div>
                <div class="input input-wrapper">
                    <label class="input-label" for="feedback-email">E-mail </label>
                    <input type="email" id="feedback-email" name="MAIL">
                </div>
                <div class="input textarea input-wrapper required">
                    <label class="input-label" for="feedback-mes">Ваше сообщение</label>
                    <textarea name="MESSAGE" id="feedback-mes" cols="30" rows="10" required></textarea>
                </div>
                <label class="checkbox-container required">
                    Согласен на обработку
                    <a href="<?=get_field('opt_link_personal','option')['url'];?>" target="_blank">персональных данных</a>
                    <input type="checkbox" checked name="agreement" required>
                    <span class="checkmark"></span>
                </label>
                <input type="hidden" name="THEME" value="Обратная связь">
                <input type="hidden" name="action" value="sendform">
                <input type="hidden" name="PAGE" value="<?php $SiteUri=get_site_url();$Path=$_SERVER['REQUEST_URI']; echo $URI=$SiteUri.$Path; ?>">
                <div class="col-bottom">
                <button type="submit" class="ok_but button btn">Отправить</button>
                </div>
            </form>
        </div>
    </div>
</div>

