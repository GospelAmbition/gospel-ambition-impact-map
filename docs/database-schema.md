# Database Schema Documentation

This document provides complete database schema information for the Gospel Ambition Impact Map plugin.

**Generated:** 2025-01-XX  
**Source:** Extracted from MySQL dump and code analysis  
**Database:** `goimpactmap`  
**Table Prefix:** `wp_`

## Overview

The Impact Map plugin uses three primary tables provided by the Disciple.Tools theme:

1. **`wp_dt_reports`** - Stores activity/event records with location data
2. **`wp_dt_location_grid`** - Hierarchical location data (countries, states, cities, etc.)
3. **`wp_dt_location_grid_meta`** - Links posts/contacts to location grid entries

## Table: `wp_dt_reports`

The main table for storing activity records. Each record represents an event (training, prayer, study, etc.) with associated location information.

### CREATE TABLE Statement

```sql
CREATE TABLE `wp_dt_reports` (
  `id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(22) DEFAULT NULL,
  `post_id` bigint(22) NOT NULL,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `subtype` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_520_ci,
  `value` bigint(22) NOT NULL DEFAULT '0',
  `lng` float DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `level` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `grid_id` bigint(22) DEFAULT NULL,
  `time_begin` int(11) DEFAULT NULL,
  `time_end` int(11) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `hash` varchar(65) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `type` (`type`),
  KEY `subtype` (`subtype`),
  KEY `lng` (`lng`),
  KEY `lat` (`lat`),
  KEY `level` (`level`),
  KEY `grid_id` (`grid_id`),
  KEY `time_begin` (`time_begin`),
  KEY `time_end` (`time_end`),
  KEY `timestamp` (`timestamp`),
  KEY `parent_id` (`parent_id`),
  KEY `post_type` (`post_type`),
  KEY `hash` (`hash`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

### Columns

| Column | Type | Null | Key | Default | Description |
|--------|------|------|-----|---------|-------------|
| `id` | bigint(22) unsigned | NO | PRI | AUTO_INCREMENT | Primary key, unique record identifier |
| `parent_id` | bigint(22) | YES | MUL | NULL | Parent report ID for hierarchical relationships |
| `post_id` | bigint(22) | NO | MUL | - | WordPress post ID (contact, group, etc.) |
| `post_type` | varchar(20) | YES | MUL | NULL | Post type (e.g., 'contacts', 'groups', 'zume_training') |
| `type` | varchar(100) | NO | MUL | - | Activity type: 'training', 'praying', 'studying', 'coaching', 'downloading', 'system' |
| `subtype` | varchar(100) | YES | MUL | NULL | Activity subtype (e.g., '1_heard', 'prayer_for_location') |
| `payload` | longtext | YES | - | NULL | Serialized additional data (JSON or PHP serialized) |
| `value` | bigint(22) | NO | MUL | 0 | Numeric value (usually 1 for counting) |
| `lng` | float | YES | MUL | NULL | Longitude coordinate |
| `lat` | float | YES | MUL | NULL | Latitude coordinate |
| `level` | varchar(100) | YES | MUL | NULL | Location level: 'admin0' (country), 'admin1' (state), 'admin2' (county), etc. |
| `label` | varchar(255) | YES | - | NULL | Human-readable location name (e.g., "New York, New York, United States") |
| `grid_id` | bigint(22) | YES | MUL | NULL | Foreign key to `wp_dt_location_grid.grid_id` |
| `time_begin` | int(11) | YES | MUL | NULL | Unix timestamp for activity start |
| `time_end` | int(11) | YES | MUL | NULL | Unix timestamp for activity end (usually same as time_begin) |
| `timestamp` | int(11) | NO | MUL | - | Unix timestamp when record was created |
| `hash` | varchar(65) | YES | MUL | NULL | SHA256 hash for duplicate detection |

### Indexes

- **PRIMARY KEY**: `id` - Unique identifier
- **KEY `post_id`**: For joining with WordPress posts
- **KEY `type`**: For filtering by activity type
- **KEY `subtype`**: For filtering by activity subtype
- **KEY `lng`**: For spatial queries (longitude)
- **KEY `lat`**: For spatial queries (latitude)
- **KEY `level`**: For filtering by location level
- **KEY `grid_id`**: For joining with location grid
- **KEY `time_begin`**: For time-based queries
- **KEY `time_end`**: For time-based queries (most commonly used)
- **KEY `timestamp`**: For sorting by creation time
- **KEY `parent_id`**: For hierarchical relationships
- **KEY `post_type`**: For filtering by post type
- **KEY `hash`**: For duplicate detection
- **KEY `value`**: For numeric value queries

### Notes

- **Code References**: The plugin code references `user_id` and `language_code` columns that may not exist in all installations. These may be added in future versions or specific installations.
- **Hash Field**: Used to prevent duplicate entries. Generated from serialized record data using SHA256.
- **Payload**: Can contain complex data structures. Check `GO_Impact_Map_Insert::insert()` for serialization logic.
- **Time Fields**: All time fields use Unix timestamps (integers), not MySQL datetime types.

### Common Queries

**Get recent activities:**
```sql
SELECT * FROM wp_dt_reports 
WHERE type != 'system' 
AND time_end > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 100 HOUR))
ORDER BY time_end DESC;
```

**Get activities by type:**
```sql
SELECT * FROM wp_dt_reports 
WHERE type = 'training' 
AND subtype LIKE '%heard'
ORDER BY time_end DESC;
```

**Get activities by location:**
```sql
SELECT * FROM wp_dt_reports r
LEFT JOIN wp_dt_location_grid lg ON lg.grid_id = r.grid_id
WHERE lg.country_code = 'US'
AND r.type != 'system';
```

## Table: `wp_dt_location_grid`

Hierarchical location data providing a standardized geographic reference system. Contains countries, states/provinces, counties, cities, and other administrative divisions.

### CREATE TABLE Statement

```sql
CREATE TABLE `wp_dt_location_grid` (
  `grid_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `level` int(1) DEFAULT NULL,
  `level_name` varchar(7) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `country_code` varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `admin0_code` varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `admin0_grid_id` bigint(20) DEFAULT NULL,
  `admin1_grid_id` bigint(20) DEFAULT NULL,
  `admin2_grid_id` bigint(20) DEFAULT NULL,
  `admin3_grid_id` bigint(20) DEFAULT NULL,
  `admin4_grid_id` bigint(20) DEFAULT NULL,
  `admin5_grid_id` bigint(20) DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `north_latitude` float DEFAULT NULL,
  `south_latitude` float DEFAULT NULL,
  `east_longitude` float DEFAULT NULL,
  `west_longitude` float DEFAULT NULL,
  `population` bigint(20) NOT NULL DEFAULT '0',
  `modification_date` date DEFAULT NULL,
  `alt_name` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `alt_population` bigint(20) DEFAULT '0',
  `alt_name_changed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`grid_id`),
  KEY `level` (`level`),
  KEY `latitude` (`latitude`),
  KEY `longitude` (`longitude`),
  KEY `admin0_code` (`admin0_code`),
  KEY `country_code` (`country_code`),
  KEY `north_latitude` (`north_latitude`),
  KEY `south_latitude` (`south_latitude`),
  KEY `parent_id` (`parent_id`),
  KEY `west_longitude` (`east_longitude`),
  KEY `east_longitude` (`west_longitude`),
  KEY `admin0_grid_id` (`admin0_grid_id`),
  KEY `admin1_grid_id` (`admin1_grid_id`),
  KEY `admin2_grid_id` (`admin2_grid_id`),
  KEY `admin3_grid_id` (`admin3_grid_id`),
  KEY `admin4_grid_id` (`admin4_grid_id`),
  KEY `admin5_grid_id` (`admin5_grid_id`),
  KEY `level_name` (`level_name`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `alt_name` (`alt_name`)
) ENGINE=InnoDB AUTO_INCREMENT=100386738 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

### Columns

| Column | Type | Null | Key | Description |
|--------|------|------|-----|-------------|
| `grid_id` | bigint(20) | NO | PRI | Primary key, unique location identifier |
| `name` | varchar(200) | NO | FT | Location name (e.g., "New York", "California") |
| `level` | int(1) | YES | MUL | Administrative level: 0=country, 1=state, 2=county, 3=city, etc. |
| `level_name` | varchar(7) | YES | MUL | Level name: 'admin0', 'admin1', 'admin2', etc. |
| `country_code` | varchar(10) | YES | MUL | ISO country code (e.g., 'US', 'GB') |
| `admin0_code` | varchar(10) | YES | MUL | Administrative level 0 code |
| `parent_id` | bigint(20) | YES | MUL | Parent location's grid_id |
| `admin0_grid_id` | bigint(20) | YES | MUL | Country's grid_id |
| `admin1_grid_id` | bigint(20) | YES | MUL | State/province's grid_id |
| `admin2_grid_id` | bigint(20) | YES | MUL | County's grid_id |
| `admin3_grid_id` | bigint(20) | YES | MUL | City's grid_id |
| `admin4_grid_id` | bigint(20) | YES | MUL | Level 4 administrative division |
| `admin5_grid_id` | bigint(20) | YES | MUL | Level 5 administrative division |
| `longitude` | float | YES | MUL | Center longitude coordinate |
| `latitude` | float | YES | MUL | Center latitude coordinate |
| `north_latitude` | float | YES | MUL | Northern boundary |
| `south_latitude` | float | YES | MUL | Southern boundary |
| `east_longitude` | float | YES | MUL | Eastern boundary |
| `west_longitude` | float | YES | MUL | Western boundary |
| `population` | bigint(20) | NO | - | Population count |
| `modification_date` | date | YES | - | Last modification date |
| `alt_name` | varchar(200) | YES | FT | Alternative name |
| `alt_population` | bigint(20) | YES | - | Alternative population count |
| `alt_name_changed` | tinyint(1) | NO | - | Flag if alternative name was changed |

### Indexes

- **PRIMARY KEY**: `grid_id` - Unique location identifier
- **KEY `level`**: For filtering by administrative level
- **KEY `latitude`**: For spatial queries
- **KEY `longitude`**: For spatial queries
- **KEY `admin0_code`**: For country-level queries
- **KEY `country_code`**: For country filtering
- **KEY `north_latitude`**: For boundary queries
- **KEY `south_latitude`**: For boundary queries
- **KEY `parent_id`**: For hierarchical navigation
- **KEY `west_longitude`**: For boundary queries (note: index name may be swapped)
- **KEY `east_longitude`**: For boundary queries (note: index name may be swapped)
- **KEY `admin0_grid_id`**: For joining country-level data
- **KEY `admin1_grid_id`**: For joining state-level data
- **KEY `admin2_grid_id`**: For joining county-level data
- **KEY `admin3_grid_id`**: For joining city-level data
- **KEY `admin4_grid_id`**: For joining level 4 data
- **KEY `admin5_grid_id`**: For joining level 5 data
- **KEY `level_name`**: For filtering by level name
- **FULLTEXT KEY `name`**: For text search on location names
- **FULLTEXT KEY `alt_name`**: For text search on alternative names

### Notes

- **Large Table**: This table contains ~100 million rows (AUTO_INCREMENT starts at 100386738)
- **Hierarchical Structure**: Locations form a tree structure via `parent_id` and admin grid IDs
- **Boundary Data**: Contains bounding box coordinates for geographic queries
- **Full-Text Search**: Supports full-text search on location names

### Common Queries

**Get location by grid_id:**
```sql
SELECT * FROM wp_dt_location_grid WHERE grid_id = 12345;
```

**Get country by code:**
```sql
SELECT * FROM wp_dt_location_grid 
WHERE level = 0 AND country_code = 'US';
```

**Get locations within bounding box:**
```sql
SELECT * FROM wp_dt_location_grid
WHERE latitude > 40.0 AND latitude < 41.0
AND longitude > -75.0 AND longitude < -74.0;
```

## Table: `wp_dt_location_grid_meta`

Links WordPress posts (contacts, groups, etc.) to location grid entries. Provides the connection between content and geographic locations.

### CREATE TABLE Statement

```sql
CREATE TABLE `wp_dt_location_grid_meta` (
  `grid_meta_id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `postmeta_id_location_grid` bigint(20) NOT NULL,
  `grid_id` bigint(22) NOT NULL,
  `lng` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `lat` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `level` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'place',
  `source` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'user',
  `label` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`grid_meta_id`),
  KEY `post_id` (`post_id`),
  KEY `post_type` (`post_type`),
  KEY `grid_id` (`grid_id`),
  KEY `lng` (`lng`),
  KEY `lat` (`lat`),
  KEY `level` (`level`),
  KEY `source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

### Columns

| Column | Type | Null | Key | Default | Description |
|--------|------|------|-----|---------|-------------|
| `grid_meta_id` | bigint(22) unsigned | NO | PRI | AUTO_INCREMENT | Primary key |
| `post_id` | bigint(20) | NO | MUL | - | WordPress post ID (contact, group, etc.) |
| `post_type` | varchar(20) | NO | MUL | - | Post type: 'contacts', 'groups', etc. |
| `postmeta_id_location_grid` | bigint(20) | NO | - | - | Reference to wp_postmeta.meta_id |
| `grid_id` | bigint(22) | NO | MUL | - | Foreign key to `wp_dt_location_grid.grid_id` |
| `lng` | varchar(20) | NO | MUL | - | Longitude as string |
| `lat` | varchar(20) | NO | MUL | - | Latitude as string |
| `level` | varchar(20) | NO | MUL | 'place' | Location level |
| `source` | varchar(20) | NO | MUL | 'user' | Source of location data: 'user', 'ip', etc. |
| `label` | varchar(255) | YES | - | NULL | Human-readable location label |

### Indexes

- **PRIMARY KEY**: `grid_meta_id` - Unique identifier
- **KEY `post_id`**: For joining with WordPress posts
- **KEY `post_type`**: For filtering by post type
- **KEY `grid_id`**: For joining with location grid
- **KEY `lng`**: For spatial queries
- **KEY `lat`**: For spatial queries
- **KEY `level`**: For filtering by level
- **KEY `source`**: For filtering by data source

### Notes

- **Coordinates as Strings**: Unlike `wp_dt_reports`, coordinates are stored as varchar, not float
- **Post Type Filtering**: Used to link specific post types to locations
- **Source Tracking**: Tracks how location was determined (user input, IP geocoding, etc.)

### Common Queries

**Get locations for a contact:**
```sql
SELECT lgm.*, lg.name, lg.country_code
FROM wp_dt_location_grid_meta lgm
LEFT JOIN wp_dt_location_grid lg ON lg.grid_id = lgm.grid_id
WHERE lgm.post_id = 123 AND lgm.post_type = 'contacts';
```

**Get all contacts in a country:**
```sql
SELECT DISTINCT lgm.post_id
FROM wp_dt_location_grid_meta lgm
LEFT JOIN wp_dt_location_grid lg ON lg.grid_id = lgm.grid_id
WHERE lg.country_code = 'US' AND lgm.post_type = 'contacts';
```

## Relationships

### Entity Relationship Diagram

```
wp_dt_reports
    ├── grid_id → wp_dt_location_grid.grid_id
    └── post_id → wp_posts.ID

wp_dt_location_grid_meta
    ├── grid_id → wp_dt_location_grid.grid_id
    └── post_id → wp_posts.ID

wp_dt_location_grid
    ├── parent_id → wp_dt_location_grid.grid_id (self-referential)
    ├── admin0_grid_id → wp_dt_location_grid.grid_id
    ├── admin1_grid_id → wp_dt_location_grid.grid_id
    └── [admin2-5_grid_id] → wp_dt_location_grid.grid_id
```

### Foreign Key Relationships

**Note:** MySQL foreign key constraints may not be explicitly defined. Relationships are maintained through application logic.

1. **wp_dt_reports.grid_id** → **wp_dt_location_grid.grid_id**
   - Links activity records to locations
   - Used for geographic queries and display

2. **wp_dt_location_grid_meta.grid_id** → **wp_dt_location_grid.grid_id**
   - Links posts to locations
   - Used for contact/group location mapping

3. **wp_dt_location_grid.parent_id** → **wp_dt_location_grid.grid_id**
   - Hierarchical location structure
   - Enables location tree navigation

4. **wp_dt_location_grid.admin[0-5]_grid_id** → **wp_dt_location_grid.grid_id**
   - Direct references to parent administrative levels
   - Enables efficient country/state/city queries

## Data Types Reference

### Integer Types
- **bigint(20)**: Large integers for IDs and counts (up to 9,223,372,036,854,775,807)
- **bigint(22)**: Same as bigint(20), number in parentheses is display width only
- **int(11)**: Standard integers for timestamps and small counts

### String Types
- **varchar(n)**: Variable-length strings up to n characters
- **longtext**: Very large text fields (up to 4GB)

### Float Types
- **float**: Single-precision floating point for coordinates

### Date/Time Types
- **date**: Date only (YYYY-MM-DD)
- **int(11)**: Unix timestamps stored as integers

## Performance Considerations

### Index Usage

1. **Time-based queries**: Use `time_end` index for filtering recent activities
2. **Spatial queries**: Use `lng`/`lat` indexes for coordinate-based searches
3. **Type filtering**: Use `type`/`subtype` indexes for activity filtering
4. **Location joins**: Use `grid_id` indexes for joining location data

### Query Optimization Tips

1. **Limit time ranges**: Always filter by `time_end` when querying activities
2. **Use indexes**: Filter by indexed columns (`type`, `subtype`, `grid_id`)
3. **Avoid full table scans**: Use WHERE clauses on indexed columns
4. **Limit results**: Use LIMIT for large result sets
5. **Join efficiently**: Join on indexed columns (`grid_id`)

### Table Sizes

- **wp_dt_reports**: Grows with activity volume. Consider archiving old records.
- **wp_dt_location_grid**: ~100 million rows, read-only reference data
- **wp_dt_location_grid_meta**: Grows with number of posts with locations

## Migration Notes

### Adding Columns

If you need to add `user_id` or `language_code` to `wp_dt_reports`:

```sql
ALTER TABLE wp_dt_reports 
ADD COLUMN user_id bigint(20) unsigned DEFAULT NULL AFTER post_id,
ADD COLUMN language_code varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL AFTER hash;

ALTER TABLE wp_dt_reports 
ADD KEY user_id (user_id),
ADD KEY language_code (language_code);
```

### Backup Recommendations

1. **wp_dt_reports**: Backup regularly (contains user activity data)
2. **wp_dt_location_grid**: Backup after updates (rarely changes)
3. **wp_dt_location_grid_meta**: Backup with posts (linked to content)

## References

- Disciple.Tools Theme: Provides these tables
- Location Grid System: [Disciple.Tools Documentation](https://github.com/DiscipleTools/disciple-tools-theme)
- Impact Map Plugin: Uses these tables for activity tracking
