<?php
/**
 * Newspack Sports Rewrites
 *
 * Manages URL rewrite rules and query vars for virtual event pages.
 * Events are NOT stored as individual posts to avoid database bloat.
 *
 * URL Structure: /{sport_slug}/{competition_slug}/event/{event_slug}
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Sports Rewrites class - Manages URL rules and query vars.
 */
class Sports_Rewrites {

	/**
	 * Query var for sport slug.
	 */
	const QUERY_VAR_SPORT = 'sport_slug';

	/**
	 * Query var for competition slug.
	 */
	const QUERY_VAR_COMPETITION = 'competition_slug';

	/**
	 * Query var for event slug.
	 */
	const QUERY_VAR_EVENT = 'event_slug';

	/**
	 * Initialize the rewrite functionality.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
		add_action( 'template_redirect', [ __CLASS__, 'handle_virtual_event' ] );
	}

	/**
	 * Add custom rewrite rules for virtual event pages.
	 */
	public static function add_rewrite_rules() {
		// Event URL: /sport/{sport_slug}/{competition_slug}/event/{event_slug}
		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/event/([^/]+)/?$',
			'index.php?' . self::QUERY_VAR_SPORT . '=$matches[1]&' .
			self::QUERY_VAR_COMPETITION . '=$matches[2]&' .
			self::QUERY_VAR_EVENT . '=$matches[3]',
			'top'
		);

		// Competition archive: /sport/{sport_slug}/{competition_slug}
		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/?$',
			'index.php?' . self::QUERY_VAR_SPORT . '=$matches[1]&' .
			self::QUERY_VAR_COMPETITION . '=$matches[2]',
			'top'
		);

		// Sport archive: /sport/{sport_slug}
		add_rewrite_rule(
			'^sport/([^/]+)/?$',
			'index.php?' . self::QUERY_VAR_SPORT . '=$matches[1]',
			'top'
		);
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = self::QUERY_VAR_SPORT;
		$vars[] = self::QUERY_VAR_COMPETITION;
		$vars[] = self::QUERY_VAR_EVENT;
		return $vars;
	}

	/**
	 * Handle virtual event page requests.
	 *
	 * Sets up the global query for virtual event pages.
	 */
	public static function handle_virtual_event() {
		$event_slug = get_query_var( self::QUERY_VAR_EVENT );
		
		if ( empty( $event_slug ) ) {
			return;
		}

		// Set up query flags for the template loader.
		global $wp_query;
		$wp_query->is_singular = true;
		$wp_query->is_single   = false;
		$wp_query->is_page     = false;
		$wp_query->is_archive  = false;
		$wp_query->is_home     = false;
		$wp_query->is_404      = false;

		// Store event data in query for template use.
		$wp_query->set( 'is_sports_event', true );
	}

	/**
	 * Get sport slug from current request.
	 *
	 * @return string The sport slug or empty string.
	 */
	public static function get_sport_slug() {
		return get_query_var( self::QUERY_VAR_SPORT, '' );
	}

	/**
	 * Get competition slug from current request.
	 *
	 * @return string The competition slug or empty string.
	 */
	public static function get_competition_slug() {
		return get_query_var( self::QUERY_VAR_COMPETITION, '' );
	}

	/**
	 * Get event slug from current request.
	 *
	 * @return string The event slug or empty string.
	 */
	public static function get_event_slug() {
		return get_query_var( self::QUERY_VAR_EVENT, '' );
	}

	/**
	 * Check if current request is for a virtual event.
	 *
	 * @return bool True if this is a virtual event request.
	 */
	public static function is_sports_event() {
		return (bool) get_query_var( 'is_sports_event', false );
	}
}
