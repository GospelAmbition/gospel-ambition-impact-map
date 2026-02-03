# AI Development Guide for Gospel Ambition Impact Map

This document provides comprehensive information for AI assistants and developers working with the Gospel Ambition Impact Map plugin codebase.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Core Design Patterns](#core-design-patterns)
3. [Key Classes and Relationships](#key-classes-and-relationships)
4. [Data Flow](#data-flow)
5. [Database Schema](#database-schema)
6. [Integration Points](#integration-points)
7. [Development Patterns](#development-patterns)
8. [File Structure](#file-structure)
9. [Common Tasks](#common-tasks)

## Architecture Overview

### Dual-Mode Plugin Architecture

The plugin operates in two distinct modes based on site context:

1. **Impact Map Mode** (`'GO Impact Map' === get_bloginfo()`)
   - Main visualization site
   - Displays global activity on interactive maps
   - Receives data from remote trackers
   - Located in `impact-map/` directory

2. **Remote Tracker Mode** (all other sites)
   - Tracks activities from various Gospel Ambition applications
   - Queues activity data for transmission
   - Sends data to the central Impact Map site
   - Located in `remote-trackers/` directory

### Context Switching

The `GO_Impact_Context_Switcher` class (in main plugin file) determines the mode:

```php
// Main entry point
GO_Impact_Context_Switcher::instance()

// Checks site name
if ( 'GO Impact Map' === get_bloginfo() ) {
    // Load impact-map functionality
} else {
    // Load remote-tracker functionality based on active plugins
}
```

### Key Dependencies

- **Disciple.Tools Theme** (required, v1.19+)
  - Provides location grid system (`wp_dt_location_grid`)
  - Provides mapping utilities (`DT_Mapbox_API`, `DT_Ipstack_API`)
  - Provides magic URL system (`DT_Magic_Url_Base`)
  - Provides REST API infrastructure

- **WordPress REST API** (v4.7+)
  - All data transmission uses REST endpoints
  - Custom endpoints under `gospel-ambition-impact-map/v1`

## Core Design Patterns

### Singleton Pattern

All major classes use the singleton pattern:

```php
class GO_Impact_Map {
    private static $_instance = null;
    
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
```

**Classes using singleton:**
- `GO_Impact_Context_Switcher`
- `GO_Impact_Map`
- `GO_Impact_Map_Endpoints`
- `GO_Impact_Map_Queue`
- `GO_Impact_Map_Globe`
- All page classes extending `DT_Magic_Url_Base`

### Magic URL Pattern

Frontend pages extend `DT_Magic_Url_Base` to create custom URLs:

```php
class GO_Impact_Map_Globe extends DT_Magic_Url_Base {
    public $root = 'app';
    public $type = 'globe';
    public static $token = 'app_globe';
    
    // URL: /app/globe
}
```

**Available Magic URLs:**
- `/app/home` - Home page
- `/app/globe` - Main globe visualization
- `/app/prayerglobe` - Prayer-focused globe
- `/app/activity` - Activity list view
- `/app/last100` - Last 100 activities

### Auto-Loader Pattern

The plugin uses recursive auto-loaders in `loader.php` files:

```php
// Scans directory and subdirectories
// Loads all .php files except index.php and loader.php
// Skips specified folders (e.g., 'html')
```

**Loader locations:**
- `impact-map/loader.php`
- `impact-map/pages/loader.php`
- `impact-map/maps/loader.php`
- `impact-map/rest-api/loader.php`

### Queue Pattern

Remote trackers use a queue system to batch and send activity data:

```php
// Add to queue
add_log_to_queue( $log_item );

// Send queue (typically via cron)
GO_Impact_Map_Queue::send_queue();
```

**Queue storage:**
- Stored as post meta on a special `log_queue` post type
- Post ID cached in option `go_logger_queue_id`

## Key Classes and Relationships

### Core Classes

#### `GO_Impact_Context_Switcher`
- **Location:** `gospel-ambition-impact-map.php`
- **Purpose:** Determines plugin mode and loads appropriate functionality
- **Key Method:** `__construct()` - Checks site name and loads modules

#### `GO_Impact_Map`
- **Location:** `impact-map/loader.php`
- **Purpose:** Main plugin controller for Impact Map mode
- **Key Methods:**
  - `instance()` - Singleton getter
  - `__construct()` - Loads REST API, pages, maps, admin
- **Dependencies:** Disciple.Tools theme

#### `GO_Impact_Map_Endpoints`
- **Location:** `impact-map/rest-api/rest-api.php`
- **Purpose:** Handles incoming activity data via REST API
- **Key Methods:**
  - `endpoint()` - Processes and stores activity logs
  - `_create_string()` - Formats activity descriptions
  - `_strings_for_*()` - Source-specific string formatters
- **REST Route:** `gospel-ambition-impact-map/v1/endpoint`

#### `GO_Impact_Map_Insert`
- **Location:** `impact-map/rest-api/insert.php`
- **Purpose:** Database insertion with duplicate checking
- **Key Method:** `insert()` - Inserts into `wp_dt_reports` table
- **Features:**
  - SHA256 hash-based duplicate detection
  - Optional duplicate checking
  - Serializes complex payloads

#### `GO_Impact_Map_Queue`
- **Location:** `remote-trackers/logger-queue.php`
- **Purpose:** Manages activity queue for remote sites
- **Key Methods:**
  - `add_to_queue()` - Adds activity to queue
  - `send_queue()` - Sends queued activities to Impact Map site
  - `_get_ip_address_for_log()` - Extracts client IP
- **Target URL:** `https://impact.gospelambition.org/wp-json/gospel-ambition-impact-map/v1/endpoint`

### Query Classes

#### `GO_Queries`
- **Location:** `impact-map/maps/queries.php`
- **Purpose:** Database queries for map data
- **Key Methods:**
  - `get_activity_geojson()` - Activity data as GeoJSON
  - `query_activity_geojson()` - Raw activity query
  - `stage_totals()` - User stage statistics
  - `churches_with_location()` - Church location data

#### `GO_Funnel_App_Heatmap`
- **Location:** `impact-map/maps/heatmap.php`
- **Purpose:** Complex queries for heatmap visualizations
- **Key Methods:**
  - `query_saturation_list()` - 50k location saturation data
  - `query_activity_list_simple()` - Simplified activity list
  - `query_activity_geojson()` - GeoJSON for heatmaps

### Page Classes (Extend `DT_Magic_Url_Base`)

#### `GO_Impact_Map_Globe`
- **Location:** `impact-map/pages/app-globe.php`
- **Purpose:** Main 3D globe visualization
- **Features:**
  - Mapbox GL integration
  - Activity clustering
  - Real-time updates
  - URL parameter configuration

#### `GO_Impact_Map_Globe_Prayer`
- **Location:** `impact-map/pages/app-globe-prayer.php`
- **Purpose:** Prayer-focused globe view
- **Filters:** Only prayer-related activities

#### `GO_Impact_Map_Activity`
- **Location:** `impact-map/pages/app-activity.php`
- **Purpose:** Activity list/dashboard view
- **Features:** Recent activities with details

#### `GO_Impact_Map_Last100`
- **Location:** `impact-map/pages/app-last100.php`
- **Purpose:** Last 100 activities view

### Remote Tracker Classes

Each tracker follows a similar pattern:

```php
// Example: remote-trackers/zume-training.php
add_action( 'hook_name', function( $data ) {
    $log_item = [
        'post_type' => 'zume_training',
        'type' => 'training',
        'subtype' => '1_heard',
        'location' => [
            'ip' => get_ip_address_for_log()
        ],
        'data' => $data,
        'time' => time()
    ];
    add_log_to_queue( $log_item );
});
```

**Available Trackers:**
- `prayer-campaigns.php` - Disciple.Tools Prayer Campaigns
- `prayer-global.php` - Prayer Global Porch
- `kingdom-training.php` - Kingdom Training
- `disciple-tools.php` - Disciple.Tools usage
- `zume-training.php` - Zume Training System
- `zume-training-coaching.php` - Zume Coaching System

## Data Flow

### Remote Site → Impact Map Site

```
1. User Action (e.g., completes Zume lesson)
   ↓
2. Tracker Hook Fires
   ↓
3. add_log_to_queue() - Stores in post meta
   ↓
4. Cron/Manual Trigger
   ↓
5. GO_Impact_Map_Queue::send_queue()
   ↓
6. HTTP POST to impact.gospelambition.org
   ↓
7. GO_Impact_Map_Endpoints::endpoint()
   ↓
8. Geocoding (IP → Location Grid)
   ↓
9. GO_Impact_Map_Insert::insert()
   ↓
10. wp_dt_reports table
```

### Impact Map Site → Frontend

```
1. User visits /app/globe
   ↓
2. GO_Impact_Map_Globe renders page
   ↓
3. JavaScript requests GeoJSON
   ↓
4. REST endpoint: /app/v1/globe
   ↓
5. GO_Queries::get_activity_geojson()
   ↓
6. Query wp_dt_reports + location joins
   ↓
7. Return GeoJSON FeatureCollection
   ↓
8. Mapbox GL renders on globe
```

### Geocoding Flow

When activity data arrives without full location:

```
1. Check for grid_id (preferred)
   ↓
2. Check for lng/lat coordinates
   ↓
3. Check for IP address
   ↓
4. If IP: DT_Ipstack_API::geocode_ip_address()
   ↓
5. Convert to Location Grid Meta
   ↓
6. Query wp_dt_location_grid for full details
   ↓
7. Format full location name
   ↓
8. Store in activity record
```

## Database Schema

### Primary Tables

#### `wp_dt_reports`
Main activity storage table (provided by Disciple.Tools theme):

```sql
CREATE TABLE wp_dt_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    parent_id BIGINT UNSIGNED,
    post_id BIGINT UNSIGNED,
    post_type VARCHAR(20),
    type VARCHAR(20),           -- 'training', 'praying', 'studying', etc.
    subtype VARCHAR(50),         -- '1_heard', 'prayer_for_location', etc.
    payload TEXT,               -- Serialized additional data
    value INT,                   -- Usually 1
    lng DECIMAL(10,7),          -- Longitude
    lat DECIMAL(10,7),          -- Latitude
    level VARCHAR(20),          -- Location level (admin0, admin1, etc.)
    label VARCHAR(255),          -- Human-readable location name
    grid_id BIGINT UNSIGNED,    -- Location Grid ID
    time_begin BIGINT,          -- Unix timestamp
    time_end BIGINT,            -- Unix timestamp (usually same as time_begin)
    timestamp BIGINT,           -- Record creation timestamp
    hash VARCHAR(64),           -- SHA256 hash for duplicate detection
    language_code VARCHAR(10)   -- ISO language code
);
```

**Key Indexes:**
- `hash` - For duplicate checking
- `time_end` - For time-based queries
- `type`, `subtype` - For filtering
- `grid_id` - For location joins

#### `wp_dt_location_grid`
Location hierarchy table (provided by Disciple.Tools theme):

```sql
-- Hierarchical location data
-- Levels: 0 (country), 1 (state), 2 (county), 3 (city), etc.
-- Contains: grid_id, name, latitude, longitude, country_code, population, etc.
```

#### `wp_dt_location_grid_meta`
Location metadata linking (provided by Disciple.Tools theme):

```sql
-- Links posts/contacts to location_grid entries
-- Contains: grid_meta_id, post_id, post_type, grid_id, etc.
```

### Activity Type System

**Type Categories:**
- `training` - Training activities (Zume, Kingdom Training)
- `praying` - Prayer activities
- `studying` - Study/learning activities
- `coaching` - Coaching activities
- `downloading` - Resource downloads
- `system` - System/internal (excluded from maps)

**Subtype Examples:**
- Zume: `1_heard`, `1_obeyed`, `1_shared`, `1_trained`
- Prayer: `prayer_for_location`, `prayer_person_location`, `lap_completed`
- Kingdom Training: `kt_registered`, `course_completed`, `lesson_completed`

## Integration Points

### Disciple.Tools Theme Integration

**Location Grid System:**
```php
// Get location by grid_id
Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id );

// Geocode IP address
DT_Ipstack_API::geocode_ip_address( $ip );
DT_Ipstack_API::convert_ip_result_to_location_grid_meta( $result );

// Format location name
$geocoder = new Location_Grid_Geocoder();
$geocoder->_format_full_name( $location_row );
```

**Mapbox Integration:**
```php
// Get Mapbox API key
DT_Mapbox_API::get_key();

// Get location grid mirror URL
dt_get_location_grid_mirror( true );
```

**Magic URL System:**
```php
// Extend for custom pages
class My_Page extends DT_Magic_Url_Base {
    public $root = 'app';
    public $type = 'mypage';
    public static $token = 'app_mypage';
}
```

### Remote Plugin Integration

**Hook Pattern:**
```php
// In remote-trackers/zume-training.php
add_action( 'zume_training_activity', function( $data ) {
    add_log_to_queue([
        'post_type' => 'zume_training',
        'type' => 'training',
        'subtype' => $data['subtype'],
        'location' => ['ip' => get_ip_address_for_log()],
        'data' => $data,
        'time' => time()
    ]);
});
```

**Active Plugin Detection:**
```php
$active_plugins = get_option( 'active_plugins' );
if ( in_array( 'zume-training-system/zume-training-system.php', $active_plugins ) ) {
    require_once( 'remote-trackers/zume-training.php' );
}
```

## Development Patterns

### Adding a New Activity Type

1. **Define string formatter** in `GO_Impact_Map_Endpoints`:
```php
public function _strings_for_my_app( $log ) {
    switch ( $log['subtype'] ) {
        case 'my_activity':
            return 'Someone did something in My App' . $this->_add_language_string( $log ) . '. (' . $log['label'] . ')';
        default:
            return '';
    }
}
```

2. **Add to `_create_string()` method:**
```php
$string = $this->_strings_for_my_app( $log );
if ( !empty( $string ) ) {
    return $string;
}
```

3. **Create remote tracker** (if needed):
```php
// remote-trackers/my-app.php
add_action( 'my_app_activity', function( $data ) {
    add_log_to_queue([
        'post_type' => 'my_app',
        'type' => 'my_type',
        'subtype' => $data['subtype'],
        'location' => ['ip' => get_ip_address_for_log()],
        'data' => $data,
        'time' => time()
    ]);
});
```

### Adding a New Map View

1. **Create page class:**
```php
// impact-map/pages/app-myview.php
class GO_Impact_Map_MyView extends DT_Magic_Url_Base {
    public $root = 'app';
    public $type = 'myview';
    public static $token = 'app_myview';
    
    // Implement required methods
}
```

2. **Add REST endpoint** (if needed):
```php
public function add_endpoints() {
    register_rest_route( 'app/v1', '/myview', [
        'methods' => 'POST',
        'callback' => [ $this, 'get_myview_data' ],
        'permission_callback' => '__return_true'
    ]);
}
```

3. **Auto-loaded** via `pages/loader.php`

### Query Pattern

**Standard activity query:**
```php
global $wpdb;
$results = $wpdb->get_results( $wpdb->prepare( "
    SELECT r.*, lg.name, lg.country_code
    FROM wp_dt_reports r
    LEFT JOIN wp_dt_location_grid lg ON lg.grid_id = r.grid_id
    WHERE r.type != 'system'
    AND r.time_end > %d
    AND r.time_end < %d
", $time_begin, $time_end ), ARRAY_A );
```

**GeoJSON conversion:**
```php
$features = [];
foreach ( $results as $row ) {
    $features[] = [
        'type' => 'Feature',
        'properties' => [
            'type' => $row['type'],
            'subtype' => $row['subtype'],
            'timestamp' => $row['time_end']
        ],
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [ $row['lng'], $row['lat'] ]
        ]
    ];
}
return [ 'type' => 'FeatureCollection', 'features' => $features ];
```

## File Structure

```
gospel-ambition-impact-map/
├── gospel-ambition-impact-map.php  # Main plugin file, context switcher
├── impact-map/                     # Impact Map mode
│   ├── loader.php                  # Main loader, GO_Impact_Map class
│   ├── admin/                      # Admin interface
│   ├── pages/                      # Frontend pages (Magic URLs)
│   │   ├── loader.php              # Auto-loader
│   │   ├── app-home.php
│   │   ├── app-globe.php
│   │   ├── app-globe-prayer.php
│   │   ├── app-activity.php
│   │   └── app-last100.php
│   ├── maps/                       # Map data and queries
│   │   ├── loader.php
│   │   ├── queries.php             # GO_Queries class
│   │   ├── heatmap.php             # GO_Funnel_App_Heatmap class
│   │   └── cluster-*.js             # JavaScript clustering
│   ├── rest-api/                   # REST API endpoints
│   │   ├── loader.php
│   │   ├── rest-api.php            # GO_Impact_Map_Endpoints
│   │   └── insert.php              # GO_Impact_Map_Insert
│   └── css/                        # Stylesheets
├── remote-trackers/                # Remote tracker mode
│   ├── logger-queue.php            # GO_Impact_Map_Queue
│   ├── logger-script.php           # Cron handler
│   ├── prayer-campaigns.php
│   ├── prayer-global.php
│   ├── kingdom-training.php
│   ├── disciple-tools.php
│   ├── zume-training.php
│   └── zume-training-coaching.php
└── docs/                           # Documentation
```

## Common Tasks

### Debugging Activity Flow

1. **Check queue on remote site:**
```php
$queue = get_log_queue();
var_dump( $queue );
```

2. **Check REST endpoint:**
```php
// Add logging in GO_Impact_Map_Endpoints::endpoint()
dt_write_log( $logs );
```

3. **Check database:**
```sql
SELECT * FROM wp_dt_reports 
WHERE type = 'training' 
ORDER BY timestamp DESC 
LIMIT 10;
```

### Testing Geocoding

```php
// Test IP geocoding
$ip = '8.8.8.8';
$result = DT_Ipstack_API::geocode_ip_address( $ip );
$lgm = DT_Ipstack_API::convert_ip_result_to_location_grid_meta( $result );
var_dump( $lgm );
```

### Adding Language Support

Languages are defined in `impact-map/loader.php`:
- `zume_languages()` - Full language list with metadata
- `impact_map_languages()` - Simplified locale → name mapping

To add a language, add entry to both functions.

### Performance Optimization

**Query optimization:**
- Use transients for expensive queries (see `query_saturation_list()`)
- Limit time ranges in queries
- Use proper indexes on `wp_dt_reports`

**Memory management:**
```php
ini_set('memory_limit', -1); // For large queries
```

**Caching:**
```php
if ( false !== ( $value = get_transient( __METHOD__ ) ) ) {
    return $value;
}
// ... expensive operation ...
set_transient( __METHOD__, $result, HOUR_IN_SECONDS );
```

## Key Relationships Diagram

```
GO_Impact_Context_Switcher
    ├── Impact Map Mode
    │   └── GO_Impact_Map
    │       ├── GO_Impact_Map_Endpoints (REST API)
    │       │   └── GO_Impact_Map_Insert (Database)
    │       ├── GO_Impact_Map_Globe (Pages)
    │       ├── GO_Queries (Data)
    │       └── GO_Funnel_App_Heatmap (Heatmaps)
    │
    └── Remote Tracker Mode
        └── GO_Impact_Map_Queue
            ├── logger-queue.php
            ├── prayer-campaigns.php
            ├── zume-training.php
            └── [other trackers]
```

## Important Notes

1. **Site Name Dependency:** The plugin uses `get_bloginfo()` to determine mode. Site must be named exactly `'GO Impact Map'` for map mode.

2. **Theme Dependency:** Requires Disciple.Tools theme v1.19+. Plugin checks version and shows admin notice if outdated.

3. **Hardcoded URL:** Remote trackers send to hardcoded URL `https://impact.gospelambition.org`. This should be configurable.

4. **Language Support:** Extensive language support (60+ languages) defined in loader.php. Used for activity descriptions.

5. **Duplicate Prevention:** Uses SHA256 hash of serialized data to prevent duplicate entries.

6. **Location Grid:** Heavily relies on Disciple.Tools location grid system for geocoding and location hierarchy.

7. **REST API Security:** Uses `dt_allow_rest_access` filter to allow public access to specific endpoints.

8. **Magic URLs:** All frontend pages use Disciple.Tools magic URL system for custom routing without permalinks.
