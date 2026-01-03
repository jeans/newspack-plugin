<?php
/**
 * Newspack Sports Sync
 *
 * Handles synchronization between the newspack_sport CPT and newspack_sport_tax taxonomy.
 * Ensures the shadow taxonomy stays in sync with the CPT for efficient querying.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Sports Sync class - Handles CPT-to-Taxonomy synchronization.
 */
class Sports_Sync {

	/**
	 * Initialize the sync functionality.
	 */
	public static function init() {
		// Sync when a sport/competition post is saved.
		add_action( 'save_post_' . Sports_CPT::POST_TYPE, [ __CLASS__, 'sync_post_to_taxonomy' ], 10, 3 );
		
		// Sync when a sport/competition post is deleted.
		add_action( 'before_delete_post', [ __CLASS__, 'sync_post_deletion' ], 10, 2 );
		
		// Sync when a post is restored from trash.
		add_action( 'untrashed_post', [ __CLASS__, 'sync_post_restoration' ], 10, 2 );
	}

	/**
	 * Sync a sport/competition post to the shadow taxonomy.
	 *
	 * Creates or updates a taxonomy term to mirror the CPT post.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 * @param bool    $update  Whether this is an update.
	 */
	public static function sync_post_to_taxonomy( $post_id, $post, $update ) {
		// Avoid infinite loops and autosaves.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Only sync published posts.
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$term_args = [
			'slug'        => $post->post_name,
			'description' => $post->post_excerpt,
		];

		// Get parent term ID if this is a child (Competition).
		if ( $post->post_parent ) {
			$parent_term = self::get_term_by_post_id( $post->post_parent );
			if ( $parent_term ) {
				$term_args['parent'] = $parent_term->term_id;
			}
		}

		// Check if term already exists.
		$existing_term = self::get_term_by_post_id( $post_id );

		if ( $existing_term ) {
			// Update existing term.
			$result = wp_update_term( $existing_term->term_id, Sports_Taxonomy::TAXONOMY, array_merge( $term_args, [ 'name' => $post->post_title ] ) );
			
			// Handle errors during term update.
			if ( is_wp_error( $result ) ) {
				// Log error for debugging.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Sports Sync: Failed to update term for post ID ' . $post_id . ': ' . $result->get_error_message() );
			}
		} else {
			// Create new term.
			$result = wp_insert_term( $post->post_title, Sports_Taxonomy::TAXONOMY, $term_args );
			
			if ( ! is_wp_error( $result ) ) {
				// Store the post ID in term meta for reverse lookup.
				update_term_meta( $result['term_id'], 'sport_post_id', $post_id );
			} else {
				// Log error for debugging.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Sports Sync: Failed to create term for post ID ' . $post_id . ': ' . $result->get_error_message() );
			}
		}
	}

	/**
	 * Sync post deletion to taxonomy.
	 *
	 * Deletes the corresponding taxonomy term when a sport post is deleted.
	 *
	 * @param int     $post_id The post ID being deleted.
	 * @param WP_Post $post    The post object.
	 */
	public static function sync_post_deletion( $post_id, $post ) {
		if ( Sports_CPT::POST_TYPE !== $post->post_type ) {
			return;
		}

		$term = self::get_term_by_post_id( $post_id );
		if ( $term ) {
			wp_delete_term( $term->term_id, Sports_Taxonomy::TAXONOMY );
		}
	}

	/**
	 * Sync post restoration from trash.
	 *
	 * Re-syncs the taxonomy term when a sport post is restored.
	 *
	 * @param int    $post_id The post ID being restored.
	 * @param string $previous_status The status before trashing.
	 */
	public static function sync_post_restoration( $post_id, $previous_status ) {
		$post = get_post( $post_id );
		if ( $post && Sports_CPT::POST_TYPE === $post->post_type ) {
			self::sync_post_to_taxonomy( $post_id, $post, true );
		}
	}

	/**
	 * Get taxonomy term by associated post ID.
	 *
	 * @param int $post_id The post ID.
	 * @return WP_Term|null The term object or null if not found.
	 */
	public static function get_term_by_post_id( $post_id ) {
		$terms = get_terms(
			[
				'taxonomy'   => Sports_Taxonomy::TAXONOMY,
				'hide_empty' => false,
				'meta_key'   => 'sport_post_id',
				'meta_value' => $post_id,
				'number'     => 1,
			]
		);

		return ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0] : null;
	}

	/**
	 * Get post ID by associated taxonomy term.
	 *
	 * @param int $term_id The term ID.
	 * @return int|null The post ID or null if not found.
	 */
	public static function get_post_id_by_term( $term_id ) {
		$post_id = get_term_meta( $term_id, 'sport_post_id', true );
		return $post_id ? (int) $post_id : null;
	}
}
