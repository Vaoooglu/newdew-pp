<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package oxboxwise
 */

get_header();
?>


			<section class="error-404 not-found">
					<h1 class="page-title"><?php esc_html_e( 'Упс! Такой страницы не существует', 'oxboxwise' ); ?></h1>

				<div class="page-content">
					<p><?php esc_html_e( 'Ничего не найдено. Попробуйте изменить параметры поиска или перейти по другой ссылке.', 'oxboxwise' ); ?></p>

					<?php
					get_search_form();

					?>

				</div>
			</section>


<?php
get_footer();
