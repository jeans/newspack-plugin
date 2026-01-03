<?php
/**
 * Sports Custom Post Type handler.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the Sports hierarchical custom post type.
 * Level 1: Sports (e.g., Football, Basketball)
 * Level 2: Competitions (e.g., Bundesliga, Champions League)
 */
class Sports_CPT {
	/**
	 * Post type for Sports.
	 *
	 * @var string
	 */
	const POST_TYPE = 'newspack_sport';

	/**
	 * Initialize the Sports CPT handler.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
	}

	/**
	 * Get the post type for Sports.
	 *
	 * @return string The post type.
	 */
	public static function get_post_type() {
		return self::POST_TYPE;
	}

	/**
	 * Register the Sports custom post type.
	 */
	public static function register_post_type() {
		$labels = [
			'name'               => _x( 'Sports', 'sport post type general name', 'newspack-plugin' ),
			'singular_name'      => _x( 'Sport', 'sport post type singular name', 'newspack-plugin' ),
			'add_new'            => _x( 'Add New', 'label for add new sport', 'newspack-plugin' ),
			'add_new_item'       => __( 'Add New Sport', 'newspack-plugin' ),
			'edit_item'          => __( 'Edit Sport', 'newspack-plugin' ),
			'new_item'           => __( 'New Sport', 'newspack-plugin' ),
			'view_item'          => __( 'View Sport', 'newspack-plugin' ),
			'search_items'       => __( 'Search Sports', 'newspack-plugin' ),
			'not_found'          => __( 'No sports found.', 'newspack-plugin' ),
			'not_found_in_trash' => __( 'No sports found in Trash.', 'newspack-plugin' ),
			'parent_item_colon'  => __( 'Parent Sport:', 'newspack-plugin' ),
			'all_items'          => __( 'All Sports', 'newspack-plugin' ),
			'menu_name'          => _x( 'Sports', 'label for sport menu name', 'newspack-plugin' ),
		];

		$args = [
			'labels'              => $labels,
			'description'         => __( 'Hierarchical post type for managing Sports and Competitions.', 'newspack-plugin' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'hierarchical'        => true,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes', 'revisions' ],
			'has_archive'         => true,
			'rewrite'             => [
				'slug'       => 'sports',
				'with_front' => false,
				'pages'      => true,
				'feeds'      => false,
			],
			'query_var'           => true,
			'can_export'          => true,
			'delete_with_user'    => false,
			'menu_icon'           => 'dashicons-awards',
			'capability_type'     => 'post',
		];

		register_post_type( self::POST_TYPE, $args );
	}
}
