<?php
/**
 * Site footer, mobile navigation and lightweight panels.
 *
 * @package oxboxwise
 */

$home_url       = home_url( '/' );
$recipe_archive = get_post_type_archive_link( 'recipe' );
$phone          = function_exists( 'get_field' ) ? get_field( 'opt_phone_site', 'option' ) : '';
$socials        = array(
	'opt_soc_tg'    => 'Telegram',
	'opt_soc_wa'    => 'WhatsApp',
	'opt_soc_insta' => 'Instagram',
	'opt_soc_fb'    => 'Facebook',
);
?>

<footer class="site-footer">
	<div class="container site-footer__inner">
		<div>
			<p class="site-footer__brand"><?php bloginfo( 'name' ); ?></p>
			<p class="site-footer__description">Личная библиотека любимых рецептов.</p>
		</div>
		<nav class="site-footer__nav" aria-label="Навигация в подвале">
			<a href="<?php echo esc_url( $recipe_archive ); ?>">Все рецепты</a>
			<a href="<?php echo esc_url( $home_url . '#categories' ); ?>">Категории</a>
			<a href="<?php echo esc_url( $home_url . '#favorites' ); ?>">Избранное</a>
		</nav>
		<div class="site-footer__contacts">
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
			<?php endif; ?>
			<div class="site-footer__socials">
				<?php foreach ( $socials as $field_name => $label ) : ?>
					<?php $social_url = function_exists( 'get_field' ) ? get_field( $field_name, 'option' ) : ''; ?>
					<?php if ( $social_url ) : ?>
						<a href="<?php echo esc_url( $social_url, array( 'http', 'https', 'tg', 'viber' ) ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( $label ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="container site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>

<nav class="mobile-nav" aria-label="Мобильная навигация">
	<a class="mobile-nav__item<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( $home_url ); ?>">
		<svg aria-hidden="true"><use href="#icon-home"></use></svg><span>Главная</span>
	</a>
	<button class="mobile-nav__item" type="button" data-panel-open="search-panel" aria-controls="search-panel" aria-expanded="false">
		<svg aria-hidden="true"><use href="#icon-search"></use></svg><span>Поиск</span>
	</button>
	<a class="mobile-nav__item" href="<?php echo esc_url( $home_url . '#favorites' ); ?>">
		<svg aria-hidden="true"><use href="#icon-heart"></use></svg><span>Избранное</span>
	</a>
	<button class="mobile-nav__item" type="button" data-panel-open="menu-panel" aria-controls="menu-panel" aria-expanded="false">
		<svg aria-hidden="true"><use href="#icon-menu"></use></svg><span>Меню</span>
	</button>
</nav>

<div class="site-panel-backdrop" data-panel-close hidden></div>

<section id="search-panel" class="site-panel site-panel--search" role="dialog" aria-modal="true" aria-labelledby="search-panel-title" aria-hidden="true" hidden>
	<div class="site-panel__handle" aria-hidden="true"></div>
	<div class="site-panel__header">
		<h2 id="search-panel-title">Найти рецепт</h2>
		<button class="icon-button" type="button" data-panel-close aria-label="Закрыть поиск">
			<svg aria-hidden="true"><use href="#icon-close"></use></svg>
		</button>
	</div>
	<?php get_search_form(); ?>
	<p class="site-panel__hint">Ищите по названию, ингредиенту, категории или тегу.</p>
</section>

<section id="menu-panel" class="site-panel site-panel--menu" role="dialog" aria-modal="true" aria-labelledby="menu-panel-title" aria-hidden="true" hidden>
	<div class="site-panel__handle" aria-hidden="true"></div>
	<div class="site-panel__header">
		<h2 id="menu-panel-title">Меню</h2>
		<button class="icon-button" type="button" data-panel-close aria-label="Закрыть меню">
			<svg aria-hidden="true"><use href="#icon-close"></use></svg>
		</button>
	</div>
	<nav class="panel-nav" aria-label="Мобильное меню">
		<a href="<?php echo esc_url( $home_url ); ?>">Главная</a>
		<a href="<?php echo esc_url( $recipe_archive ); ?>">Все рецепты</a>
		<a href="<?php echo esc_url( $home_url . '#categories' ); ?>">Категории</a>
		<a href="<?php echo esc_url( $home_url . '#favorites' ); ?>">Избранное</a>
	</nav>
</section>

<?php get_template_part( 'template-parts/content', 'svg' ); ?>
