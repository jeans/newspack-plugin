<?php
/**
 * Sports Template Loader handler.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Handles template loading for Sports virtual pages.
 * Routes requests to sport.php, competition.php, or event.php templates.
 */
class Sports_Template_Loader {
	/**
	 * Initialize the Sports template loader.
	 */
	public static function init() {
		add_filter( 'template_include', [ __CLASS__, 'load_template' ], 99 );
	}

	/**
	 * Load the appropriate template for sports pages.
	 *
	 * @param string $template The path of the template to include.
	 * @return string Modified template path.
	 */
	public static function load_template( $template ) {
		// Check if this is a sports-related query.
		$sport_slug       = Sports_Rewrites::get_sport_slug();
		$competition_slug = Sports_Rewrites::get_competition_slug();
		$event_slug       = Sports_Rewrites::get_event_slug();

		// If no sport query var, this is not a sports page.
		if ( ! $sport_slug ) {
			return $template;
		}

		// Determine which template to load based on query vars.
		$template_name = '';

		if ( $event_slug ) {
			// Event page: /sports/{sport}/{competition}/event/{event_slug}
			$template_name = 'event.php';
		} elseif ( $competition_slug ) {
			// Competition page: /sports/{sport}/{competition}
			$template_name = 'competition.php';
		} else {
			// Sport page: /sports/{sport}
			$template_name = 'sport.php';
		}

		// Try to locate the template.
		$located_template = self::locate_template( $template_name );

		if ( $located_template ) {
			return $located_template;
		}

		// Fallback to default template if custom template not found.
		return $template;
	}

	/**
	 * Locate a template file.
	 * Checks theme directory first. Plugin templates can be added in the future
	 * if default templates are needed.
	 *
	 * @param string $template_name Template file name.
	 * @return string|false Template path or false if not found.
	 */
	private static function locate_template( $template_name ) {
		// Check theme directory for templates.
		// Supports both 'newspack-sports/' subdirectory and root theme directory.
		$theme_template = locate_template( [ 'newspack-sports/' . $template_name, $template_name ] );

		if ( $theme_template ) {
			return $theme_template;
		}

		// No default plugin templates provided - themes must implement templates.
		// Future enhancement: Add default templates in includes/templates/sports/.
		return false;
	}

	/**
	 * Get the sport post by slug.
	 *
	 * @param string $slug Sport slug.
	 * @return WP_Post|null Sport post object or null if not found.
	 */
	public static function get_sport_by_slug( $slug ) {
		$posts = get_posts(
			[
				'name'           => $slug,
				'post_type'      => Sports_CPT::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'post_parent'    => 0, // Top-level sports only.
			]
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get the competition post by sport and competition slugs.
	 *
	 * @param string $sport_slug       Sport slug.
	 * @param string $competition_slug Competition slug.
	 * @return WP_Post|null Competition post object or null if not found.
	 */
	public static function get_competition_by_slug( $sport_slug, $competition_slug ) {
		// First get the sport.
		$sport = self::get_sport_by_slug( $sport_slug );

		if ( ! $sport ) {
			return null;
		}

		// Then get the competition as a child of the sport.
		$posts = get_posts(
			[
				'name'           => $competition_slug,
				'post_type'      => Sports_CPT::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'post_parent'    => $sport->ID,
			]
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get posts tagged with a specific sport/competition term.
	 *
	 * @param int   $term_id Term ID.
	 * @param array $args    Optional. Query arguments.
	 * @return WP_Query Query object.
	 */
	public static function get_posts_by_term( $term_id, $args = [] ) {
		$defaults = [
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => Sports_Taxonomy::get_taxonomy(),
					'field'    => 'term_id',
					'terms'    => $term_id,
				],
			],
		];

		$args = wp_parse_args( $args, $defaults );

		return new \WP_Query( $args );
	}
}
