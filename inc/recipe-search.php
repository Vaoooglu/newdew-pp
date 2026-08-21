<?php
/**
 * Search helpers for recipes.
 *
 * @package oxboxwise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit public search to recipes and enable taxonomy matching.
 *
 * @param WP_Query $query Query instance.
 */
function oxboxwise_prepare_recipe_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', 'recipe' );
	$query->set( 'oxboxwise_recipe_search', true );
}
add_action( 'pre_get_posts', 'oxboxwise_prepare_recipe_search' );

/**
 * Extend WordPress title/content search with recipe taxonomy names.
 *
 * Uses EXISTS subqueries so future AJAX queries can opt in without duplicate rows.
 *
 * @param string   $search Existing search SQL.
 * @param WP_Query $query  Query instance.
 * @return string
 */
function oxboxwise_recipe_taxonomy_search_sql( $search, $query ) {
	global $wpdb;

	if ( ! $query->get( 'oxboxwise_recipe_search' ) ) {
		return $search;
	}

	$terms = $query->get( 'search_terms' );
	if ( ! is_array( $terms ) || ! $terms ) {
		$terms = array( trim( (string) $query->get( 's' ) ) );
	}
	$terms = array_filter( $terms, 'strlen' );
	if ( ! $terms ) {
		return $search;
	}

	$taxonomies = array( 'recipe_category', 'recipe_ingredient', 'post_tag' );
	$placeholders = implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) );
	$clauses      = array();

	foreach ( $terms as $term ) {
		$like   = '%' . $wpdb->esc_like( $term ) . '%';
		$params = array_merge( array( $like, $like, $like ), $taxonomies, array( $like ) );
		$clauses[] = $wpdb->prepare(
			"(
				{$wpdb->posts}.post_title LIKE %s
				OR {$wpdb->posts}.post_excerpt LIKE %s
				OR {$wpdb->posts}.post_content LIKE %s
				OR EXISTS (
					SELECT 1
					FROM {$wpdb->term_relationships} AS recipe_tr
					INNER JOIN {$wpdb->term_taxonomy} AS recipe_tt ON recipe_tt.term_taxonomy_id = recipe_tr.term_taxonomy_id
					INNER JOIN {$wpdb->terms} AS recipe_t ON recipe_t.term_id = recipe_tt.term_id
					WHERE recipe_tr.object_id = {$wpdb->posts}.ID
					AND recipe_tt.taxonomy IN ({$placeholders})
					AND recipe_t.name LIKE %s
				)
			)",
			$params
		);
	}

	$search = ' AND (' . implode( ' AND ', $clauses ) . ')';
	if ( ! is_user_logged_in() ) {
		$search .= " AND ({$wpdb->posts}.post_password = '')";
	}

	return $search;
}
add_filter( 'posts_search', 'oxboxwise_recipe_taxonomy_search_sql', 10, 2 );

/**
 * Build reusable arguments for a recipe text/taxonomy search.
 *
 * @param string $search Search phrase.
 * @param array  $args   Additional WP_Query arguments.
 * @return array
 */
function oxboxwise_recipe_search_query_args( $search, $args = array() ) {
	$defaults = array(
		'post_type'               => 'recipe',
		'post_status'             => 'publish',
		'posts_per_page'          => 12,
		's'                       => sanitize_text_field( $search ),
		'oxboxwise_recipe_search' => true,
	);

	return wp_parse_args( $args, $defaults );
}

/**
 * Build reusable query arguments for recipes containing every ingredient.
 *
 * @param array $ingredients Ingredient term slugs or IDs.
 * @param array $args        Additional WP_Query arguments.
 * @return array
 */
function oxboxwise_recipe_ingredient_query_args( $ingredients, $args = array() ) {
	$ingredient_slugs = array();
	foreach ( (array) $ingredients as $ingredient ) {
		if ( is_numeric( $ingredient ) ) {
			$term = get_term( (int) $ingredient, 'recipe_ingredient' );
			if ( $term && ! is_wp_error( $term ) ) {
				$ingredient_slugs[] = $term->slug;
			}
		} else {
			$ingredient_slugs[] = sanitize_title( $ingredient );
		}
	}
	$ingredients = array_values( array_unique( array_filter( $ingredient_slugs ) ) );
	$defaults     = array(
		'post_type'      => 'recipe',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
	);

	if ( $ingredients ) {
		$defaults['tax_query'] = array(
			array(
				'taxonomy' => 'recipe_ingredient',
				'field'    => 'slug',
				'terms'    => $ingredients,
				'operator' => 'AND',
			),
		);
	}

	return wp_parse_args( $args, $defaults );
}
