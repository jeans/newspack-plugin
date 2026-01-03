<?php
/**
 * Sports Module Main Loader.
 *
 * @package Newspack
 */

namespace Newspack\Sports;

defined( 'ABSPATH' ) || exit;

/**
 * Main Sports Module class.
 * Handles initialization and coordination of all Sports module components.
 */
class Sports {
	/**
	 * Initialize the Sports module.
	 */
	public static function init() {
		// Load required classes.
		require_once __DIR__ . '/class-sports-cpt.php';
		require_once __DIR__ . '/class-sports-taxonomy.php';
		require_once __DIR__ . '/class-sports-sync.php';
		require_once __DIR__ . '/class-sports-rewrites.php';
		require_once __DIR__ . '/class-sports-template-loader.php';

		// Initialize components.
		Sports_CPT::init();
		Sports_Taxonomy::init();
		Sports_Sync::init();
		Sports_Rewrites::init();
		Sports_Template_Loader::init();
	}
}

// Initialize the Sports module.
Sports::init();
