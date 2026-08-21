<?php
/**
 * Site header.
 *
 * @package oxboxwise
 */

$recipe_archive = get_post_type_archive_link( 'recipe' );
$home_url       = home_url( '/' );
$logo           = function_exists( 'get_field' ) ? get_field( 'opt_site_logo', 'option' ) : null;
?>

<a class="skip-link screen-reader-text" href="#main-content">Перейти к содержимому</a>
<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-brand" href="<?php echo esc_url( $home_url ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — главная">
			<?php if ( is_array( $logo ) && ! empty( $logo['ID'] ) ) : ?>
				<?php echo wp_get_attachment_image( $logo['ID'], 'full', false, array( 'class' => 'site-brand__image', 'loading' => 'eager' ) ); ?>
			<?php else : ?>
				<span class="site-brand__mark" aria-hidden="true">Р</span>
				<span class="site-brand__name"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="desktop-nav" aria-label="Основная навигация">
			<?php if ( has_nav_menu( 'menu_main' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu_main',
						'container'      => false,
						'menu_class'     => 'desktop-nav__list',
						'depth'          => 1,
					)
				);
				?>
			<?php else : ?>
				<ul class="desktop-nav__list">
					<li><a href="<?php echo esc_url( $recipe_archive ); ?>">Рецепты</a></li>
					<li><a href="<?php echo esc_url( $home_url . '#categories' ); ?>">Категории</a></li>
					<li><a href="<?php echo esc_url( $home_url . '#favorites' ); ?>">Избранное</a></li>
				</ul>
			<?php endif; ?>
		</nav>

		<button class="site-header__search" type="button" data-panel-open="search-panel" aria-controls="search-panel" aria-expanded="false">
			<svg aria-hidden="true"><use href="#icon-search"></use></svg>
			<span>Поиск</span>
		</button>
	</div>
</header>
