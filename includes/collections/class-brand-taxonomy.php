<?php
/**
 * Brand Taxonomy Integration for Collections.
 *
 * @package Newspack\Collections
 */

namespace Newspack\Collections;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the Brand taxonomy integration for Collections.
 * This class provides integration with the Newspack Multibranded Site Plugin
 * when it is installed and active.
 */
class Brand_Taxonomy {

	/**
	 * Brand taxonomy name (from Multibranded Site Plugin).
	 *
	 * @var string
	 */
	private const BRAND_TAXONOMY = 'newspack_mbs_brand';

	/**
	 * Get the brand taxonomy name.
	 *
	 * @return string The taxonomy name.
	 */
	public static function get_taxonomy() {
		return self::BRAND_TAXONOMY;
	}

	/**
	 * Check if the Multibranded Site Plugin is active.
	 *
	 * @return bool True if the plugin is active.
	 */
	public static function is_multibranded_site_active() {
		return defined( 'NEWSPACK_MULTIBRANDED_SITE_PLUGIN_FILE' );
	}

	/**
	 * Initialize the brand taxonomy integration.
	 */
	public static function init() {
		// Only initialize if Multibranded Site Plugin is active.
		if ( ! self::is_multibranded_site_active() ) {
			return;
		}

		add_action( 'init', [ __CLASS__, 'register_brand_for_collections' ], 20 );
		add_action( 'manage_' . Post_Type::get_post_type() . '_posts_columns', [ __CLASS__, 'set_brand_column_name' ] );
	}

	/**
	 * Register the brand taxonomy for the collections post type.
	 * The brand taxonomy is already registered by the Multibranded Site Plugin,
	 * we just need to associate it with the collections post type.
	 */
	public static function register_brand_for_collections() {
		if ( ! taxonomy_exists( self::get_taxonomy() ) ) {
			return;
		}

		// Register the taxonomy for the collections post type.
		register_taxonomy_for_object_type( self::get_taxonomy(), Post_Type::get_post_type() );
	}

	/**
	 * Set the brand column name in the admin post list table.
	 * Used to simplify the column name to "Brand" instead of "Brands".
	 *
	 * @param array $posts_columns An associative array of column headings.
	 * @return array The modified columns array.
	 */
	public static function set_brand_column_name( $posts_columns ) {
		if ( isset( $posts_columns[ 'taxonomy-' . self::get_taxonomy() ] ) ) {
			$posts_columns[ 'taxonomy-' . self::get_taxonomy() ] = _x( 'Brand', 'label for brand column name', 'newspack-plugin' );
		}

		return $posts_columns;
	}

	/**
	 * Get the primary brand for a collection.
	 *
	 * @param int $collection_id The collection post ID.
	 * @return \WP_Term|null The primary brand term, or null if not set.
	 */
	public static function get_primary_brand( $collection_id ) {
		$brands = wp_get_object_terms( $collection_id, self::get_taxonomy() );

		if ( is_wp_error( $brands ) || empty( $brands ) ) {
			return null;
		}

		// Return the first brand as the primary brand.
		return $brands[0];
	}

	/**
	 * Get all brands for a collection.
	 *
	 * @param int $collection_id The collection post ID.
	 * @return \WP_Term[] Array of brand terms.
	 */
	public static function get_brands( $collection_id ) {
		$brands = wp_get_object_terms( $collection_id, self::get_taxonomy() );

		if ( is_wp_error( $brands ) ) {
			return [];
		}

		return $brands;
	}

	/**
	 * Set the brands for a collection.
	 *
	 * @param int   $collection_id The collection post ID.
	 * @param array $brand_ids Array of brand term IDs.
	 * @return array|\WP_Error The affected Term IDs or WP_Error on failure.
	 */
	public static function set_brands( $collection_id, $brand_ids ) {
		return wp_set_object_terms( $collection_id, $brand_ids, self::get_taxonomy() );
	}
}
