<?php
/**
 * Sports Sync handler.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Handles synchronization between Sports CPT and Shadow Taxonomy.
 * This ensures that every Sport/Competition post has a corresponding taxonomy term.
 */
class Sports_Sync {
	/**
	 * Meta key for storing the linked taxonomy term ID in post meta.
	 *
	 * @var string
	 */
	const LINKED_TERM_META_KEY = '_newspack_sport_term_id';

	/**
	 * Meta key for storing the linked post ID in term meta.
	 *
	 * @var string
	 */
	const LINKED_POST_META_KEY = '_newspack_sport_post_id';

	/**
	 * Flag to prevent infinite sync loops.
	 *
	 * @var bool
	 */
	private static $is_syncing = false;

	/**
	 * Initialize the Sports sync handler.
	 */
	public static function init() {
		add_action( 'save_post_' . Sports_CPT::get_post_type(), [ __CLASS__, 'handle_post_save' ], 10, 3 );
		add_action( 'before_delete_post', [ __CLASS__, 'handle_post_deleted' ] );
		add_action( 'wp_trash_post', [ __CLASS__, 'handle_post_trashed' ] );
		add_action( 'untrashed_post', [ __CLASS__, 'handle_post_untrashed' ] );
	}

	/**
	 * Handle post save to sync with taxonomy.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 */
	public static function handle_post_save( $post_id, $post, $update ) {
		// Prevent infinite loops and skip autosaves/revisions.
		if ( self::$is_syncing || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Only sync published posts.
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		self::$is_syncing = true;

		// Get or create linked term.
		$term_id = get_post_meta( $post_id, self::LINKED_TERM_META_KEY, true );

		if ( ! $term_id ) {
			// Create new term.
			$term = self::create_linked_term( $post );
			if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
				$term_id = $term['term_id'];
			}
		} else {
			// Update existing term.
			self::sync_post_to_term( $post_id, $term_id );
		}

		self::$is_syncing = false;
	}

	/**
	 * Create a linked term for a post.
	 *
	 * @param WP_Post $post Post object.
	 * @return array|WP_Error Term array if created, WP_Error on failure.
	 */
	private static function create_linked_term( $post ) {
		$term_args = [
			'slug'        => $post->post_name,
			'description' => get_the_excerpt( $post ),
		];

		// If post has a parent, find the parent term.
		if ( $post->post_parent ) {
			$parent_term_id = get_post_meta( $post->post_parent, self::LINKED_TERM_META_KEY, true );
			if ( $parent_term_id ) {
				$term_args['parent'] = $parent_term_id;
			}
		}

		$term = wp_insert_term( $post->post_title, Sports_Taxonomy::get_taxonomy(), $term_args );

		if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
			// Link the post and term.
			update_post_meta( $post->ID, self::LINKED_TERM_META_KEY, $term['term_id'] );
			update_term_meta( $term['term_id'], self::LINKED_POST_META_KEY, $post->ID );
		}

		return $term;
	}

	/**
	 * Sync post changes to its linked term.
	 *
	 * @param int $post_id Post ID.
	 * @param int $term_id Term ID.
	 */
	private static function sync_post_to_term( $post_id, $term_id ) {
		$post = get_post( $post_id );
		$term = get_term( $term_id, Sports_Taxonomy::get_taxonomy() );

		if ( ! $post || ! $term || is_wp_error( $term ) ) {
			return;
		}

		$term_args = [];

		if ( $term->name !== $post->post_title ) {
			$term_args['name'] = $post->post_title;
		}

		if ( $term->slug !== $post->post_name ) {
			$term_args['slug'] = $post->post_name;
		}

		$excerpt = get_the_excerpt( $post );
		if ( $term->description !== $excerpt ) {
			$term_args['description'] = $excerpt;
		}

		// Update parent if changed.
		$parent_term_id = 0;
		if ( $post->post_parent ) {
			$parent_term_id = get_post_meta( $post->post_parent, self::LINKED_TERM_META_KEY, true );
		}
		if ( $term->parent !== $parent_term_id ) {
			$term_args['parent'] = $parent_term_id;
		}

		if ( ! empty( $term_args ) ) {
			wp_update_term( $term_id, Sports_Taxonomy::get_taxonomy(), $term_args );
		}
	}

	/**
	 * Handle post deletion to remove linked term.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function handle_post_deleted( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || Sports_CPT::get_post_type() !== $post->post_type ) {
			return;
		}

		$term_id = get_post_meta( $post_id, self::LINKED_TERM_META_KEY, true );

		if ( $term_id && ! self::$is_syncing ) {
			self::$is_syncing = true;
			wp_delete_term( $term_id, Sports_Taxonomy::get_taxonomy() );
			self::$is_syncing = false;
		}
	}

	/**
	 * Handle post being trashed.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function handle_post_trashed( $post_id ) {
		// For now, keep the term but we could mark it as inactive.
		// This allows restoration if the post is untrashed.
	}

	/**
	 * Handle post being untrashed.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function handle_post_untrashed( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || Sports_CPT::get_post_type() !== $post->post_type ) {
			return;
		}

		// Re-sync the post to ensure term is up to date.
		self::handle_post_save( $post_id, $post, true );
	}
}
