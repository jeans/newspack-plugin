# Newspack Sports Module

High-performance Sports module for managing Sports, Competitions, and Matches efficiently using a "Shadow Taxonomy" pattern. Designed for high-traffic sites to handle sports content without database bloat.

## Architecture

### 1. Custom Post Type (`newspack_sport`)
- **Purpose**: Hierarchical CPT serving as the "Editor Interface" for managing rich content pages
- **Structure**:
  - Level 1: Sports (e.g., "Soccer", "Basketball")
  - Level 2: Competitions (e.g., "Bundesliga", "Premier League")
- **Features**: Full WordPress editor support with thumbnails, revisions, and hierarchical organization

### 2. Shadow Taxonomy (`newspack_sport_tax`)
- **Purpose**: Automatically synced taxonomy for high-performance querying
- **Use Case**: Fast relationship queries like "All matches in Bundesliga"
- **Sync**: Automatically kept in sync with the CPT via hooks
- **Hidden**: Not shown in the admin UI - managed entirely through the CPT

### 3. Virtual Events
- **Concept**: Matches/Events are NOT stored as individual WordPress posts
- **Benefit**: Avoids database bloat on high-traffic sites with many events
- **Rendering**: Events are rendered via "Virtual Pages" using custom rewrite rules
- **URL Structure**: `/{plugin_root}/{sport_slug}/{competition_slug}/event/{event_slug}`
  - Example: `/sport/soccer/bundesliga/event/bayern-vs-dortmund`

### 4. Template System
Custom template loader routes requests to appropriate templates:
- `sport.php` - Sport archive pages
- `competition.php` - Competition archive pages
- `event.php` - Individual event/match pages

Templates can be placed in either:
- Theme directory: `{theme}/sport.php` or `{theme}/newspack-sports/sport.php`
- Plugin directory: `includes/sports/templates/sport.php` (fallback)

## Module Components

### class-sports.php
Main loader class that initializes all sub-modules.

### class-sports-cpt.php
Registers the `newspack_sport` Custom Post Type with hierarchical support.

### class-sports-taxonomy.php
Registers the `newspack_sport_tax` shadow taxonomy for efficient querying.

### class-sports-sync.php
Handles automatic synchronization between CPT and taxonomy:
- Creates/updates taxonomy terms when sport/competition posts are saved
- Deletes taxonomy terms when posts are deleted
- Maintains parent-child relationships
- Stores bidirectional references using term meta

### class-sports-rewrites.php
Manages URL rewrite rules and query variables:
- Registers custom rewrite rules for virtual event URLs
- Adds query vars for sport, competition, and event slugs
- Provides helper functions to access current page context

### class-sports-template-loader.php
Routes requests to appropriate templates:
- Detects page type (sport/competition/event)
- Locates templates in theme or plugin directories
- Falls back gracefully if templates are not found

## Usage

### Creating a Sport
1. Navigate to **Sports** in the WordPress admin
2. Click **Add New**
3. Enter sport name and content
4. Publish

### Creating a Competition
1. Navigate to **Sports** > **Add New**
2. Enter competition name and content
3. Select a **Parent** sport from the Page Attributes panel
4. Publish

### Displaying Events
Events are rendered via virtual URLs. To display an event:

1. Create an `event.php` template in your theme
2. Access event data using helper functions:
```php
<?php
$sport_slug = \Newspack\Sports\Sports_Rewrites::get_sport_slug();
$competition_slug = \Newspack\Sports\Sports_Rewrites::get_competition_slug();
$event_slug = \Newspack\Sports\Sports_Rewrites::get_event_slug();

// Fetch event data from your data source (API, database, etc.)
// Display event information
?>
```

### Querying with the Shadow Taxonomy
Use the shadow taxonomy for high-performance queries:

```php
// Get all posts tagged with "Bundesliga"
$args = [
    'tax_query' => [
        [
            'taxonomy' => \Newspack\Sports\Sports_Taxonomy::TAXONOMY,
            'field'    => 'slug',
            'terms'    => 'bundesliga',
        ],
    ],
];
$query = new WP_Query( $args );
```

## Template Functions

### Sports_Rewrites Helper Functions
```php
// Get current sport slug
\Newspack\Sports\Sports_Rewrites::get_sport_slug();

// Get current competition slug
\Newspack\Sports\Sports_Rewrites::get_competition_slug();

// Get current event slug
\Newspack\Sports\Sports_Rewrites::get_event_slug();

// Check if viewing a sports event
\Newspack\Sports\Sports_Rewrites::is_sports_event();
```

### Template Loader Functions
```php
// Get current page type: 'sport', 'competition', 'event', or null
\Newspack\Sports\Sports_Template_Loader::get_current_page_type();
```

## Data Fetching

Event data should be fetched from your external data source (API, database, etc.) within the template or via a separate data helper class. The Sports module handles routing and template selection, but does not prescribe a specific data storage method for events.

## Performance Considerations

- **Shadow Taxonomy**: Taxonomy queries are significantly faster than post queries for filtering and relationships
- **No Event Posts**: Avoids creating thousands of posts for individual matches
- **Caching**: Consider implementing object caching for event data
- **Query Optimization**: Use the shadow taxonomy for all relationship queries

## Activation

After activation, flush rewrite rules:
1. Go to **Settings** > **Permalinks**
2. Click **Save Changes** (no need to change anything)

Or use WP-CLI:
```bash
wp rewrite flush
```

## Hooks and Filters

### Actions
- `save_post_newspack_sport` - Triggered when a sport/competition is saved
- `before_delete_post` - Triggered before a sport/competition is deleted
- `untrashed_post` - Triggered when a post is restored from trash

### Filters
- `template_include` - Used to route to custom templates
- `query_vars` - Adds custom query variables

## Extending the Module

### Custom Data Sources
Create a data helper class to fetch event data:

```php
namespace Newspack\Sports;

class Sports_Data_Helper {
    public static function get_event( $event_slug ) {
        // Fetch from API or database
        return $event_data;
    }
}
```

### Custom Templates
Override templates by placing them in your theme:
- `{theme}/sport.php`
- `{theme}/competition.php`
- `{theme}/event.php`

Or in a subdirectory:
- `{theme}/newspack-sports/sport.php`
- `{theme}/newspack-sports/competition.php`
- `{theme}/newspack-sports/event.php`

## Technical Details

- **Post Type Slug**: `newspack_sport`
- **Taxonomy Slug**: `newspack_sport_tax`
- **Query Vars**: `sport_slug`, `competition_slug`, `event_slug`
- **Term Meta Key**: `sport_post_id` (stores CPT post ID in taxonomy term)
