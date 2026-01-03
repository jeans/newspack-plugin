<?php
/**
 * Newspack Sports Taxonomy
 *
 * Registers the newspack_sport_tax "Shadow Taxonomy" for high-performance querying.
 * This taxonomy mirrors the CPT structure but is optimized for fast relationship queries.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Sports Taxonomy class - Registers the Shadow Taxonomy.
 */
class Sports_Taxonomy {

	/**
	 * Taxonomy slug.
	 */
	const TAXONOMY = 'newspack_sport_tax';

	/**
	 * Initialize the taxonomy.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_taxonomy' ] );
	}

	/**
	 * Register the newspack_sport_tax taxonomy.
	 *
	 * Shadow taxonomy synced with the newspack_sport CPT.
	 * Used for high-performance querying of relationships.
	 */
	public static function register_taxonomy() {
		$labels = [
			'name'              => _x( 'Sport Categories', 'taxonomy general name', 'newspack-plugin' ),
			'singular_name'     => _x( 'Sport Category', 'taxonomy singular name', 'newspack-plugin' ),
			'search_items'      => __( 'Search Sport Categories', 'newspack-plugin' ),
			'all_items'         => __( 'All Sport Categories', 'newspack-plugin' ),
			'parent_item'       => __( 'Parent Sport Category', 'newspack-plugin' ),
			'parent_item_colon' => __( 'Parent Sport Category:', 'newspack-plugin' ),
			'edit_item'         => __( 'Edit Sport Category', 'newspack-plugin' ),
			'update_item'       => __( 'Update Sport Category', 'newspack-plugin' ),
			'add_new_item'      => __( 'Add New Sport Category', 'newspack-plugin' ),
			'new_item_name'     => __( 'New Sport Category Name', 'newspack-plugin' ),
			'menu_name'         => __( 'Sport Categories', 'newspack-plugin' ),
		];

		$args = [
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => false, // Hidden from UI - managed via CPT.
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'sport-category' ],
			'show_in_rest'      => true,
		];

		register_taxonomy( self::TAXONOMY, [ 'post' ], $args );
	}

	/**
	 * Get the taxonomy slug.
	 *
	 * @return string The taxonomy slug.
	 */
	public static function get_taxonomy() {
		return self::TAXONOMY;
	}
}
