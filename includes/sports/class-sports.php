<?php
/**
 * Newspack Sports Module
 *
 * High-performance Sports module for managing Sports, Competitions, and Matches.
 * Uses a "Shadow Taxonomy" pattern for efficient querying on high-traffic sites.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Main Sports class - Module loader.
 */
class Sports {

	/**
	 * Initialize the Sports module.
	 *
	 * Loads all sub-modules and hooks them into WordPress.
	 */
	public static function init() {
		// Load dependencies.
		require_once __DIR__ . '/class-sports-cpt.php';
		require_once __DIR__ . '/class-sports-taxonomy.php';
		require_once __DIR__ . '/class-sports-sync.php';
		require_once __DIR__ . '/class-sports-rewrites.php';
		require_once __DIR__ . '/class-sports-template-loader.php';

		// Initialize sub-modules.
		Sports_CPT::init();
		Sports_Taxonomy::init();
		Sports_Sync::init();
		Sports_Rewrites::init();
		Sports_Template_Loader::init();
	}
}

Sports::init();
