<?php
/**
 * Sports Shadow Taxonomy handler.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the Sports shadow taxonomy.
 * This taxonomy is automatically synced with the Sports CPT for high-performance querying.
 */
class Sports_Taxonomy {
	/**
	 * Taxonomy for Sports.
	 *
	 * @var string
	 */
	const TAXONOMY = 'newspack_sport_tax';

	/**
	 * Initialize the Sports taxonomy handler.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_taxonomy' ] );
	}

	/**
	 * Get the taxonomy for Sports.
	 *
	 * @return string The taxonomy name.
	 */
	public static function get_taxonomy() {
		return self::TAXONOMY;
	}

	/**
	 * Register the Sports shadow taxonomy.
	 */
	public static function register_taxonomy() {
		$labels = [
			'name'          => _x( 'Sports Taxonomy', 'sport taxonomy general name', 'newspack-plugin' ),
			'singular_name' => _x( 'Sport', 'sport taxonomy singular name', 'newspack-plugin' ),
			'search_items'  => __( 'Search Sports', 'newspack-plugin' ),
			'popular_items' => __( 'Popular Sports', 'newspack-plugin' ),
			'all_items'     => __( 'All Sports', 'newspack-plugin' ),
			'view_item'     => __( 'View Sport', 'newspack-plugin' ),
			'edit_item'     => __( 'Edit Sport', 'newspack-plugin' ),
			'update_item'   => __( 'Update Sport', 'newspack-plugin' ),
			'add_new_item'  => __( 'Add New Sport', 'newspack-plugin' ),
			'new_item_name' => __( 'New Sport Name', 'newspack-plugin' ),
			'menu_name'     => _x( 'Sports', 'label for sport menu name', 'newspack-plugin' ),
		];

		$args = [
			'labels'             => $labels,
			'description'        => __( 'Shadow taxonomy for high-performance sports and competition queries.', 'newspack-plugin' ),
			'public'             => true,
			'publicly_queryable' => true,
			'hierarchical'       => true,
			'show_ui'            => false, // Hidden from admin UI since it's managed via CPT.
			'show_in_menu'       => false,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => false,
			'show_in_quick_edit' => false,
			'query_var'          => true,
			'rewrite'            => [
				'slug'         => 'sports',
				'with_front'   => false,
				'hierarchical' => true,
			],
			'show_in_rest'       => true,
		];

		// Register taxonomy for posts to allow tagging articles with sports/competitions.
		register_taxonomy( self::TAXONOMY, [ 'post' ], $args );
	}
}
