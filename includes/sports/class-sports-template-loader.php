<?php
/**
 * Newspack Sports Template Loader
 *
 * Handles template selection for sport, competition, and event pages.
 * Routes requests to appropriate templates based on the URL structure.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Sports Template Loader class - Handles template routing.
 */
class Sports_Template_Loader {

	/**
	 * Initialize the template loader.
	 */
	public static function init() {
		add_filter( 'template_include', [ __CLASS__, 'template_loader' ], 99 );
	}

	/**
	 * Load the appropriate template for sports-related pages.
	 *
	 * Routes to sport.php, competition.php, or event.php based on query vars.
	 *
	 * @param string $template The path of the template to include.
	 * @return string Modified template path.
	 */
	public static function template_loader( $template ) {
		$sport_slug       = Sports_Rewrites::get_sport_slug();
		$competition_slug = Sports_Rewrites::get_competition_slug();
		$event_slug       = Sports_Rewrites::get_event_slug();

		// Determine which template to load.
		if ( ! empty( $event_slug ) && ! empty( $competition_slug ) && ! empty( $sport_slug ) ) {
			// Event page: sport/{sport}/{competition}/event/{event}
			$new_template = self::locate_template( 'event.php' );
		} elseif ( ! empty( $competition_slug ) && ! empty( $sport_slug ) ) {
			// Competition page: sport/{sport}/{competition}
			$new_template = self::locate_template( 'competition.php' );
		} elseif ( ! empty( $sport_slug ) ) {
			// Check if this is a sport CPT single post.
			if ( is_singular( Sports_CPT::POST_TYPE ) ) {
				$new_template = self::locate_template( 'single-' . Sports_CPT::POST_TYPE . '.php' );
				if ( ! $new_template ) {
					$new_template = self::locate_template( 'sport.php' );
				}
			} else {
				// Sport archive: sport/{sport}
				$new_template = self::locate_template( 'sport.php' );
			}
		} else {
			return $template;
		}

		return $new_template ? $new_template : $template;
	}

	/**
	 * Locate a template file.
	 *
	 * Searches in theme directory first, then plugin directory.
	 *
	 * @param string $template_name The template file name.
	 * @return string|false Template path or false if not found.
	 */
	protected static function locate_template( $template_name ) {
		// Check in theme directory.
		// Themes can place templates in:
		// - Root: {theme}/sport.php
		// - Subdirectory: {theme}/newspack-sports/sport.php
		$theme_template = locate_template( [ $template_name, 'newspack-sports/' . $template_name ] );
		
		if ( $theme_template ) {
			return $theme_template;
		}

		// No template found - WordPress will use its default template hierarchy.
		return false;
	}

	/**
	 * Get the current page type.
	 *
	 * @return string|null 'sport', 'competition', 'event', or null.
	 */
	public static function get_current_page_type() {
		$event_slug       = Sports_Rewrites::get_event_slug();
		$competition_slug = Sports_Rewrites::get_competition_slug();
		$sport_slug       = Sports_Rewrites::get_sport_slug();

		if ( ! empty( $event_slug ) ) {
			return 'event';
		} elseif ( ! empty( $competition_slug ) ) {
			return 'competition';
		} elseif ( ! empty( $sport_slug ) ) {
			return 'sport';
		}

		return null;
	}
}
