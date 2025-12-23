<?php
/**
 * Unit tests for the Brand Taxonomy Integration.
 *
 * @package Newspack\Tests
 * @covers \Newspack\Collections\Brand_Taxonomy
 */

namespace Newspack\Tests\Unit\Collections;

use WP_UnitTestCase;
use Newspack\Collections\Brand_Taxonomy;
use Newspack\Collections\Post_Type;

/**
 * Test the Brand Taxonomy integration for Collections.
 */
class Test_Brand_Taxonomy extends WP_UnitTestCase {
	use Traits\Trait_Collections_Test;

	/**
	 * Test collection ID.
	 *
	 * @var int
	 */
	private $collection_id;

	/**
	 * Set up the test environment.
	 */
	public function set_up() {
		parent::set_up();

		// Register the post type.
		Post_Type::register_post_type();

		// Create a test collection.
		$this->collection_id = $this->create_collection( 'Test Collection' );
	}

	/**
	 * Test that brand taxonomy integration checks for plugin presence.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::is_multibranded_site_active
	 */
	public function test_multibranded_site_detection() {
		// The plugin should not be active in test environment.
		$this->assertFalse( Brand_Taxonomy::is_multibranded_site_active() );
	}

	/**
	 * Test that get_taxonomy returns the correct taxonomy name.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::get_taxonomy
	 */
	public function test_get_taxonomy() {
		$this->assertEquals( 'newspack_mbs_brand', Brand_Taxonomy::get_taxonomy() );
	}

	/**
	 * Test that brand registration for collections works when taxonomy exists.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::register_brand_for_collections
	 */
	public function test_register_brand_for_collections() {
		// Create a mock brand taxonomy.
		register_taxonomy(
			'newspack_mbs_brand',
			[ 'post' ],
			[
				'public'       => true,
				'show_in_rest' => true,
			]
		);

		// Register brand for collections.
		Brand_Taxonomy::register_brand_for_collections();

		// Check if the taxonomy is registered for the collections post type.
		$taxonomy = get_taxonomy( 'newspack_mbs_brand' );
		$this->assertNotNull( $taxonomy );
		$this->assertContains( Post_Type::get_post_type(), $taxonomy->object_type );
	}

	/**
	 * Test getting primary brand when no brands are set.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::get_primary_brand
	 */
	public function test_get_primary_brand_empty() {
		// Create a mock brand taxonomy.
		register_taxonomy(
			'newspack_mbs_brand',
			[ Post_Type::get_post_type() ],
			[
				'public'       => true,
				'show_in_rest' => true,
			]
		);

		$primary_brand = Brand_Taxonomy::get_primary_brand( $this->collection_id );
		$this->assertNull( $primary_brand );
	}

	/**
	 * Test getting primary brand when brand is set.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::get_primary_brand
	 */
	public function test_get_primary_brand_with_brand() {
		// Create a mock brand taxonomy.
		register_taxonomy(
			'newspack_mbs_brand',
			[ Post_Type::get_post_type() ],
			[
				'public'       => true,
				'show_in_rest' => true,
			]
		);

		// Create a brand term.
		$brand = wp_insert_term( 'Test Brand', 'newspack_mbs_brand' );
		$this->assertIsArray( $brand );

		// Set the brand for the collection.
		wp_set_object_terms( $this->collection_id, [ $brand['term_id'] ], 'newspack_mbs_brand' );

		// Get the primary brand.
		$primary_brand = Brand_Taxonomy::get_primary_brand( $this->collection_id );
		$this->assertInstanceOf( \WP_Term::class, $primary_brand );
		$this->assertEquals( 'Test Brand', $primary_brand->name );
	}

	/**
	 * Test getting all brands for a collection.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::get_brands
	 */
	public function test_get_brands() {
		// Create a mock brand taxonomy.
		register_taxonomy(
			'newspack_mbs_brand',
			[ Post_Type::get_post_type() ],
			[
				'public'       => true,
				'show_in_rest' => true,
			]
		);

		// Create brand terms.
		$brand1 = wp_insert_term( 'Brand One', 'newspack_mbs_brand' );
		$brand2 = wp_insert_term( 'Brand Two', 'newspack_mbs_brand' );

		// Set brands for the collection.
		wp_set_object_terms( $this->collection_id, [ $brand1['term_id'], $brand2['term_id'] ], 'newspack_mbs_brand' );

		// Get all brands.
		$brands = Brand_Taxonomy::get_brands( $this->collection_id );
		$this->assertCount( 2, $brands );
		$this->assertContainsOnlyInstancesOf( \WP_Term::class, $brands );
	}

	/**
	 * Test setting brands for a collection.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::set_brands
	 */
	public function test_set_brands() {
		// Create a mock brand taxonomy.
		register_taxonomy(
			'newspack_mbs_brand',
			[ Post_Type::get_post_type() ],
			[
				'public'       => true,
				'show_in_rest' => true,
			]
		);

		// Create brand terms.
		$brand1 = wp_insert_term( 'Brand Alpha', 'newspack_mbs_brand' );
		$brand2 = wp_insert_term( 'Brand Beta', 'newspack_mbs_brand' );

		// Set brands for the collection.
		$result = Brand_Taxonomy::set_brands( $this->collection_id, [ $brand1['term_id'], $brand2['term_id'] ] );
		$this->assertIsArray( $result );

		// Verify brands were set.
		$brands = Brand_Taxonomy::get_brands( $this->collection_id );
		$this->assertCount( 2, $brands );
	}

	/**
	 * Test brand column name customization.
	 *
	 * @covers \Newspack\Collections\Brand_Taxonomy::set_brand_column_name
	 */
	public function test_set_brand_column_name() {
		$columns = [
			'title'                       => 'Title',
			'taxonomy-newspack_mbs_brand' => 'Brands',
			'date'                        => 'Date',
		];

		$updated_columns = Brand_Taxonomy::set_brand_column_name( $columns );
		$this->assertEquals( 'Brand', $updated_columns['taxonomy-newspack_mbs_brand'] );
	}
}
