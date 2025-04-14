# API Documentation

This document describes the REST API endpoints provided by the Gospel Ambition Impact Map plugin for retrieving map data and recording activities.

## Authentication

The API supports two authentication methods:

1. **WordPress Authentication**: For requests from the same WordPress instance
2. **Site-to-Site Authentication**: For requests from external applications using JWT

For site-to-site authentication, refer to the [Disciple.Tools Site-to-Site Link documentation](https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link).

## REST API Endpoints

### GeoJSON Data Endpoints

#### Get Globe Data

Retrieves activity data for the main globe visualization.

**Endpoint:** `POST /wp-json/app/v1/globe`

**Parameters:**
```json
{
  "action": "geojson",
  "parts": {
    "root": "app",
    "type": "globe"
  }
}
```

**Response:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "type": "praying",
        "subtype": "prayer_session",
        "timestamp": "2023-08-15T10:30:00Z"
      },
      "geometry": {
        "type": "Point",
        "coordinates": [30.5, 50.3, 1]
      }
    },
    // Additional features...
  ]
}
```

#### Get Prayer Globe Data

Retrieves prayer-specific activity data.

**Endpoint:** `POST /wp-json/app/v1/prayerglobe`

**Parameters:**
```json
{
  "action": "geojson",
  "parts": {
    "root": "app",
    "type": "prayerglobe"
  }
}
```

**Response:** Same format as Globe Data, filtered for prayer activities.

### Activity Recording Endpoints

#### Record New Activity

Records a new activity in the system.

**Endpoint:** `POST /wp-json/gospel-ambition-impact-map/v1/insert`

**Parameters:**
```json
{
  "type": "praying",
  "subtype": "prayer_for_location",
  "longitude": 30.5,
  "latitude": 50.3,
  "payload": {
    "language": "en",
    "custom_data": "any additional information"
  }
}
```

**Valid Activity Types:**
- `praying` - Prayer activity
- `studying` - Study/learning activity
- `training` - Training activity
- `downloading` - Resource download
- `practicing` - Practice activity
- `coaching` - Coaching activity

**Valid Subtypes:**
- `prayer_session` - Generic prayer time
- `prayer_for_location` - Praying for a specific location
- `study_session` - Learning session
- `training_session` - Training activity
- `resource_download` - Downloaded a resource
- (See code for complete list)

**Response:**
```json
{
  "status": "success",
  "activity_id": 1234
}
```

## Code Examples

### Recording Activity (PHP)

```php
$response = wp_remote_post(
    'https://example.com/wp-json/gospel-ambition-impact-map/v1/insert',
    [
        'method' => 'POST',
        'timeout' => 45,
        'headers' => [
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer ' . $jwt_token // For site-to-site auth
        ],
        'body' => json_encode([
            'type' => 'studying',
            'subtype' => 'study_session',
            'longitude' => 35.6,
            'latitude' => 40.2,
            'payload' => [
                'language' => 'en',
                'course' => 'disciple_making'
            ]
        ])
    ]
);
```

### Fetching Map Data (JavaScript)

```javascript
jQuery.ajax({
    type: "POST",
    data: JSON.stringify({ action: 'geojson', parts: jsObject.parts }),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
    beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce)
    }
})
.then(function(data) {
    // Process the GeoJSON data
    console.log(data);
})
.fail(function(e) {
    console.error("Error loading map data:", e);
});
```

## Data Structure

### Activity Object

```json
{
  "id": 1234,
  "type": "praying",
  "subtype": "prayer_session",
  "longitude": 30.5,
  "latitude": 50.3,
  "timestamp": "2023-08-15 10:30:00",
  "ip_address": "127.0.0.1",
  "payload": "{\"language\":\"en\",\"custom_data\":\"any additional information\"}",
  "hash": "abc123...",
  "grid_id": 12345,
  "source": "prayer_app"
}
```

### GeoJSON Feature

```json
{
  "type": "Feature",
  "properties": {
    "type": "praying",
    "subtype": "prayer_session",
    "timestamp": "2023-08-15T10:30:00Z"
  },
  "geometry": {
    "type": "Point",
    "coordinates": [30.5, 50.3, 1]
  }
}
```

## Error Handling

The API returns standard HTTP status codes:

- `200` - Success
- `400` - Bad request (invalid parameters)
- `401` - Unauthorized (authentication issues)
- `403` - Forbidden (authorization issues)
- `404` - Endpoint not found
- `500` - Server error

Error responses include a message explaining the issue:

```json
{
  "code": "invalid_parameters",
  "message": "Missing required parameters",
  "data": {
    "status": 400
  }
}
```

## Rate Limiting

To prevent abuse, the API implements rate limiting:

- IP-based limiting: 100 requests per minute
- User-based limiting: 1000 requests per hour

Exceeded limits return a `429 Too Many Requests` status code.

## Versioning

The API uses versioning in the URL path (`/v1/`) to ensure backward compatibility as the API evolves. Breaking changes will be introduced in new API versions. 