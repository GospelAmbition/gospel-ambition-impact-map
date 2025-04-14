# Developer Documentation

This document explains the architecture, code organization, and extension points of the Gospel Ambition Impact Map plugin.

## Architecture Overview

The plugin is built on WordPress and the Disciple.Tools theme framework, using a modular architecture to separate concerns:

```
gospel-ambition-impact-map/
├── impact-map/              # Main plugin functionality
│   ├── admin/               # Admin interfaces
│   ├── css/                 # Stylesheets
│   ├── images/              # Image assets
│   ├── maps/                # Map configuration and utilities
│   ├── pages/               # Frontend page templates
│   ├── rest-api/            # REST API endpoints
│   └── loader.php           # Main loader
├── remote-trackers/         # Remote activity tracking
├── vendor/                  # Dependencies
└── gospel-ambition-impact-map.php  # Plugin bootstrap
```

## Core Classes

### DT_Magic_Url_Base

This is the foundation class provided by the Disciple.Tools theme that simplifies creating magic links. All map pages extend this class.

Key functionality includes:
- URL routing and access control
- Template management
- Script and style registration
- REST API integration

Example of the relationship:
```php
class GO_Impact_Map_Globe extends DT_Magic_Url_Base {
    // Class-specific implementation
}
```

### GO_Impact_Map

The main plugin controller class that:
- Initializes the plugin
- Loads required components
- Handles activation/deactivation
- Registers with the Disciple.Tools system

### GO_Impact_Map_Globe / GO_Impact_Map_Globe_Prayer

These classes handle the two main globe visualizations:
- Implement the 3D globe with Mapbox GL JS
- Process and display data points
- Handle user interactions
- Manage URL parameters for sharing specific views

## Data Flow

1. **Data Collection**: Remote trackers log activity via the REST API
2. **Data Storage**: Activities are stored in the WordPress database with location data
3. **Data Retrieval**: Frontend templates request data via REST API
4. **Visualization**: JavaScript processes data into visualizations using Mapbox GL

## Key Components

### Mapbox Integration

The plugin uses Mapbox GL JS for map rendering, with a custom implementation that:
- Creates a 3D globe visualization
- Implements auto-rotation
- Manages data clustering
- Handles different activity types with unique styling

```javascript
// Example of map initialization
mapboxgl.accessToken = jsObject.map_key;
const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v9',
    projection: 'globe',
    zoom: zoom,
    center: [30, 15]
});
```

### REST API Endpoints

The plugin registers custom REST endpoints for data operations:

- `/app/v1/globe` - Globe visualization data
- `/app/v1/prayerglobe` - Prayer-specific globe data
- Other endpoint handlers for data insertion and retrieval

### URL Parameter System

The globe visualizations support URL parameters to:
- Toggle specific elements (donation panel, QR codes)
- Control the top bar visibility
- Allow sharing specific configurations

Example:
```
https://example.com/app/globe?qr-donate&no_top
```

## Extending the Plugin

### Adding New Activity Types

1. Update the `GO_Queries` class to support the new activity type
2. Modify the frontend JavaScript to handle the new type in data processing
3. Add appropriate CSS styling for the new activity type

### Creating Custom Visualizations

1. Create a new class extending `DT_Magic_Url_Base` in the `pages` directory
2. Implement required methods: `__construct()`, `body()`, etc.
3. Register and add the necessary JavaScript for your visualization
4. Add a REST endpoint handler if needed

Example skeleton:
```php
class GO_Impact_Map_Custom extends DT_Magic_Url_Base {
    public $magic = false;
    public $parts = false;
    public $page_title = 'Custom Visualization';
    public $root = 'app';
    public $type = 'custom';
    
    // Required implementation
    public function __construct() {
        parent::__construct();
        // Custom initialization
    }
    
    public function body() {
        // Output HTML for your visualization
    }
}
GO_Impact_Map_Custom::instance();
```

## Hooks and Filters

The plugin provides several hooks and filters for extending functionality:

### Filters
- `go_activity_types` - Modify available activity types
- `go_map_settings` - Adjust global map settings
- `go_impact_query_args` - Modify query arguments for data retrieval

### Actions
- `go_before_activity_insert` - Fires before a new activity is inserted
- `go_after_activity_insert` - Fires after a new activity is inserted
- `go_map_initialized` - Fires after a map is initialized in JavaScript

## Best Practices

1. **Performance**: Activities can grow quickly. Use indexing and consider data cleanup processes.
2. **Security**: Always sanitize and validate all data, especially location information.
3. **Compatibility**: Test with different versions of WordPress and Disciple.Tools theme.
4. **Modularity**: Add new features by extending existing classes rather than modifying them.
5. **JavaScript**: Use modern ES6+ practices but ensure compatibility with older browsers. 