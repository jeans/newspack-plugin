<?php
/**
 * Sports URL Rewrite Rules handler.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Handles custom rewrite rules for Sports virtual pages.
 * URL Structure: /sports/{sport_slug}/{competition_slug}/event/{event_slug}
 */
class Sports_Rewrites {
	/**
	 * Plugin root for sports URLs.
	 *
	 * @var string
	 */
	const PLUGIN_ROOT = 'sports';

	/**
	 * Event endpoint name.
	 *
	 * @var string
	 */
	const EVENT_ENDPOINT = 'event';

	/**
	 * Initialize the Sports rewrite rules handler.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ], 20 );
		add_action( 'init', [ __CLASS__, 'add_query_vars' ] );
		add_filter( 'query_vars', [ __CLASS__, 'register_query_vars' ] );
	}

	/**
	 * Add custom rewrite rules for virtual event pages.
	 */
	public static function add_rewrite_rules() {
		// Rule for events: /sports/{sport}/{competition}/event/{event_slug}
		add_rewrite_rule(
			'^' . self::PLUGIN_ROOT . '/([^/]+)/([^/]+)/' . self::EVENT_ENDPOINT . '/([^/]+)/?$',
			'index.php?newspack_sport=$matches[1]&newspack_competition=$matches[2]&newspack_event=$matches[3]',
			'top'
		);

		// Rule for competition pages: /sports/{sport}/{competition}
		add_rewrite_rule(
			'^' . self::PLUGIN_ROOT . '/([^/]+)/([^/]+)/?$',
			'index.php?newspack_sport=$matches[1]&newspack_competition=$matches[2]',
			'top'
		);

		// Rule for sport pages: /sports/{sport}
		add_rewrite_rule(
			'^' . self::PLUGIN_ROOT . '/([^/]+)/?$',
			'index.php?newspack_sport=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars endpoint.
	 */
	public static function add_query_vars() {
		// Add event endpoint for use in URLs.
		add_rewrite_endpoint( self::EVENT_ENDPOINT, EP_ALL );
	}

	/**
	 * Register custom query vars.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public static function register_query_vars( $vars ) {
		$vars[] = 'newspack_sport';
		$vars[] = 'newspack_competition';
		$vars[] = 'newspack_event';
		return $vars;
	}

	/**
	 * Get the sport slug from query vars.
	 *
	 * @return string|false Sport slug or false if not set.
	 */
	public static function get_sport_slug() {
		return get_query_var( 'newspack_sport', false );
	}

	/**
	 * Get the competition slug from query vars.
	 *
	 * @return string|false Competition slug or false if not set.
	 */
	public static function get_competition_slug() {
		return get_query_var( 'newspack_competition', false );
	}

	/**
	 * Get the event slug from query vars.
	 *
	 * @return string|false Event slug or false if not set.
	 */
	public static function get_event_slug() {
		return get_query_var( 'newspack_event', false );
	}
}
