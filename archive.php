<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package oxboxwise
 */

get_header();
?>

	<div id="primary" class="content-area">

		<?php if ( have_posts() ) : ?>

				<?=get_the_archive_title();?>
				<?=get_the_archive_description();?>

			<?php while ( have_posts() ) : the_post();?>


				<? get_template_part( 'template-parts/content', get_post_type() );?>

			<? endwhile;?>


		<? else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

	</div>

<?php
get_footer();
