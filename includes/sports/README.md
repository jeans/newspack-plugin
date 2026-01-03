# Newspack Sports Module

## Overview

The Sports module is a high-performance solution designed for high-traffic sites to manage Sports, Competitions, and Matches efficiently using a "Shadow Taxonomy" pattern.

## Architecture

### 1. Custom Post Type (`newspack_sport`)

A hierarchical custom post type that serves as the editor interface for managing sports content:

- **Level 1: Sports** (e.g., Football, Basketball, Baseball)
- **Level 2: Competitions** (e.g., Bundesliga, Champions League, World Series)

This CPT allows editors to create rich content pages with full editorial control including:
- Custom content and descriptions
- Featured images
- Page hierarchy
- Revisions

### 2. Shadow Taxonomy (`newspack_sport_tax`)

A taxonomy automatically synchronized with the CPT that enables:
- High-performance querying of relationships
- Fast lookups for "All matches in Bundesliga"
- Efficient tag-based filtering
- Scalable querying without post meta overhead

Posts (articles, news) can be tagged with sports/competitions via this taxonomy.

### 3. Virtual Events

Matches and events are NOT stored as individual WordPress posts to avoid database bloat on high-traffic sites. Instead:

- Events are rendered via "Virtual Pages" using custom rewrite rules
- Event data can be fetched from external APIs or custom storage
- URL Structure: `/sports/{sport_slug}/{competition_slug}/event/{event_slug}`

Example URLs:
- `/sports/football/bundesliga` - Competition page
- `/sports/football/bundesliga/event/bayern-dortmund-2024` - Event page

### 4. Template Loader

Custom template loader routes requests to specific templates:

- `sport.php` - Sport overview page
- `competition.php` - Competition page with events list
- `event.php` - Individual event/match page

Templates can be overridden by placing them in your theme:
- `{theme}/newspack-sports/sport.php`
- `{theme}/newspack-sports/competition.php`
- `{theme}/newspack-sports/event.php`

## Files Structure

```
includes/sports/
├── class-sports.php                   # Main loader class
├── class-sports-cpt.php               # CPT registration
├── class-sports-taxonomy.php          # Shadow taxonomy registration
├── class-sports-sync.php              # CPT ↔ Taxonomy synchronization
├── class-sports-rewrites.php          # URL rewrite rules
├── class-sports-template-loader.php   # Template routing
└── README.md                          # This file
```

## Usage

### Creating a Sport and Competition

1. Go to **Sports** in the WordPress admin
2. Create a new Sport (e.g., "Football")
3. Create child pages as Competitions (e.g., "Bundesliga", set parent to "Football")
4. Publish both to automatically create shadow taxonomy terms

### Tagging Posts

When editing a post, use the "Sports Taxonomy" meta box to tag articles with relevant sports/competitions for efficient querying.

### Virtual Event Pages

Event pages are virtual and require custom implementation for data fetching. The template loader will look for an `event.php` template where you can:

1. Get event data from query vars:
```php
$sport_slug = \Newspack\Sports\Sports_Rewrites::get_sport_slug();
$competition_slug = \Newspack\Sports\Sports_Rewrites::get_competition_slug();
$event_slug = \Newspack\Sports\Sports_Rewrites::get_event_slug();
```

2. Fetch event data from your API or data source
3. Render the event page

### Querying Posts by Sport/Competition

Use the helper methods to query posts:

```php
// Get the sport/competition post
$sport = \Newspack\Sports\Sports_Template_Loader::get_sport_by_slug( 'football' );
$competition = \Newspack\Sports\Sports_Template_Loader::get_competition_by_slug( 'football', 'bundesliga' );

// Get the linked taxonomy term
$term_id = get_post_meta( $competition->ID, '_newspack_sport_term_id', true );

// Query posts tagged with this competition
$query = \Newspack\Sports\Sports_Template_Loader::get_posts_by_term( $term_id );
```

## Performance Benefits

1. **Shadow Taxonomy Pattern**: Eliminates slow post meta queries in favor of fast taxonomy queries
2. **Virtual Events**: Avoids creating thousands of posts for events/matches
3. **Hierarchical Structure**: Organizes sports and competitions logically
4. **Efficient Relationships**: Uses WordPress native taxonomy system for optimal performance

## Hooks and Filters

The module respects WordPress standards and can be extended through the following:

- `save_post_newspack_sport` - Fires when a sport/competition is saved
- `template_include` - Filter for custom template loading (priority 99)

## Requirements

- WordPress 5.0+
- Newspack Plugin
- PHP 7.4+

## Future Enhancements

Potential future additions:
- Event data storage in custom tables
- API integrations for live scores
- Admin UI for managing events directly
- Event scheduling and calendar views
- Statistics and performance tracking
