<?php
/**
 * Newspack Sports CPT
 *
 * Registers the hierarchical newspack_sport Custom Post Type.
 * This serves as the "Editor Interface" for managing rich content pages.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Sports CPT class - Registers the Custom Post Type.
 */
class Sports_CPT {

	/**
	 * Custom Post Type slug.
	 */
	const POST_TYPE = 'newspack_sport';

	/**
	 * Initialize the CPT.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
	}

	/**
	 * Register the newspack_sport Custom Post Type.
	 *
	 * Hierarchical CPT for Sports (Level 1) and Competitions (Level 2).
	 */
	public static function register_post_type() {
		$labels = [
			'name'                     => _x( 'Sports', 'post type general name', 'newspack-plugin' ),
			'singular_name'            => _x( 'Sport', 'post type singular name', 'newspack-plugin' ),
			'menu_name'                => _x( 'Sports', 'admin menu', 'newspack-plugin' ),
			'name_admin_bar'           => _x( 'Sport', 'add new on admin bar', 'newspack-plugin' ),
			'add_new'                  => _x( 'Add New', 'sport', 'newspack-plugin' ),
			'add_new_item'             => __( 'Add New Sport', 'newspack-plugin' ),
			'new_item'                 => __( 'New Sport', 'newspack-plugin' ),
			'edit_item'                => __( 'Edit Sport', 'newspack-plugin' ),
			'view_item'                => __( 'View Sport', 'newspack-plugin' ),
			'all_items'                => __( 'All Sports', 'newspack-plugin' ),
			'search_items'             => __( 'Search Sports', 'newspack-plugin' ),
			'parent_item_colon'        => __( 'Parent Sport:', 'newspack-plugin' ),
			'not_found'                => __( 'No sports found.', 'newspack-plugin' ),
			'not_found_in_trash'       => __( 'No sports found in Trash.', 'newspack-plugin' ),
			'items_list'               => __( 'Sports list', 'newspack-plugin' ),
			'item_published'           => __( 'Sport published', 'newspack-plugin' ),
			'item_published_privately' => __( 'Sport published privately', 'newspack-plugin' ),
			'item_reverted_to_draft'   => __( 'Sport reverted to draft', 'newspack-plugin' ),
			'item_scheduled'           => __( 'Sport scheduled', 'newspack-plugin' ),
			'item_updated'             => __( 'Sport updated', 'newspack-plugin' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'sport' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-awards',
			'show_in_rest'       => true,
			'supports'           => [ 'title', 'editor', 'thumbnail', 'page-attributes', 'revisions' ],
		];

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Get the post type slug.
	 *
	 * @return string The post type slug.
	 */
	public static function get_post_type() {
		return self::POST_TYPE;
	}
}
