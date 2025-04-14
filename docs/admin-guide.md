# Admin Guide

This guide is for administrators managing the Gospel Ambition Impact Map plugin, including configuration, customization, and maintenance tasks.

## Administrative Interfaces

The plugin's administrative interfaces can be accessed through the WordPress admin dashboard.

### Plugin Settings

Access the plugin's settings by navigating to:

**Disciple.Tools > Extensions > Gospel Ambition Impact Map**

![Admin Settings](images/admin-settings.jpg)

## Configuration Options

### General Settings

- **Default Map View**: Select which map type to show by default (Globe, Prayer Globe, or Activity List)
- **Data Retention**: Configure how long to keep activity data (30-365 days)
- **Site Title**: Customize the site title displayed in the map header
- **Default Language**: Set the default language for the interface

### Activity Types

You can enable or disable specific activity types from appearing on your maps:

- Prayer Activities
- Study Activities
- Training Activities
- Download Activities
- Coaching Activities

### Map Appearance

- **Map Style**: Choose from several Mapbox styles:
  - Streets (default)
  - Light
  - Dark
  - Satellite
  - Outdoors
- **Color Scheme**: Customize the colors used for different activity types
- **Auto-rotation Speed**: Adjust globe rotation speed or disable it completely

## Tracking Integration

### Setting Up Activity Tracking

The plugin includes integration with several Gospel Ambition tools. To enable tracking:

1. Navigate to **Disciple.Tools > Extensions > Gospel Ambition Impact Map > Tracking**
2. Enable the integrations for each tool:
   - Prayer Campaign
   - Prayer Global
   - Kingdom Training
   - Disciple Tools
   - Zume Training
   - Zume Coaching

For each integration, you can configure:
- Which activities to track
- How activities are categorized
- Whether to include location information

### Custom Tracking Integration

To track activities from custom sources:

1. Create an integration in the admin interface
2. Generate an API key for your custom application
3. Use the [API documentation](api.md) to send activity data from your application

## Content Management

### QR Codes

You can customize the QR codes displayed on the maps:

1. Navigate to **Disciple.Tools > Extensions > Gospel Ambition Impact Map > QR Codes**
2. Upload custom QR code images
3. Set the URLs that the QR codes should point to

### Donation Panel

To customize the donation panel content:

1. Navigate to **Disciple.Tools > Extensions > Gospel Ambition Impact Map > Donation**
2. Edit the HTML content displayed in the donation panel
3. Configure when and how the donation panel appears

## Maintenance Tasks

### Database Management

The activity database can grow large over time. To maintain performance:

1. Navigate to **Disciple.Tools > Extensions > Gospel Ambition Impact Map > Maintenance**
2. Use the **Clean Old Data** tool to remove activities older than a specified time
3. Use the **Optimize Database** tool to maintain database performance

### Testing Map Functionality

Before major events or presentations:

1. Navigate to **Disciple.Tools > Extensions > Gospel Ambition Impact Map > Testing**
2. Use the **Test Map Loading** tool to verify the maps load correctly
3. Use the **Generate Test Data** tool to add test points to the map (these are flagged as test data and can be easily removed)

## Security Considerations

### API Key Management

1. Regularly rotate your Mapbox API keys
2. Use environment variables or secure storage to manage API keys outside of code
3. Set appropriate restrictions on your Mapbox API keys (domain restrictions, usage limits)

### Data Privacy

The plugin handles location data, which requires privacy considerations:

1. Configure the appropriate level of location fuzzing to protect user privacy
2. Enable IP anonymization to prevent storing identifiable user information
3. Review and update your privacy policy to disclose the data collection

## Performance Optimization

### Caching Configuration

To improve map loading performance:

1. Enable GeoJSON caching in the settings
2. Configure cache duration based on your update frequency
3. If using a caching plugin, ensure the API endpoints are excluded from cache

### Server Requirements

For optimal performance with large datasets:

- Minimum 2GB RAM
- PHP memory limit of at least 256MB
- PHP timeout of at least 30 seconds
- MySQL with appropriate index optimization

## Troubleshooting Common Issues

### Missing Map Data

If activities aren't appearing on the map:

1. Check that tracking is properly configured for your tools
2. Verify the REST API endpoints are accessible
3. Look for PHP errors in the WordPress debug log
4. Check for JavaScript errors in the browser console

### Mapbox API Issues

If the map itself doesn't load:

1. Verify your Mapbox API key is valid and has appropriate permissions
2. Check that your domain is allowed in the Mapbox key restrictions
3. Verify your Mapbox account is in good standing (usage limits, billing)

### Performance Problems

If the maps load slowly:

1. Check the size of your activity database
2. Enable database query optimization in the settings
3. Consider reducing the data retention period
4. Implement a CDN for faster asset loading

## Backup and Recovery

Always maintain regular backups of:

1. The WordPress database (contains all activity data)
2. Custom configuration settings
3. Any customized plugin files

Use the export tool to create a portable configuration file that can be imported after a site migration or recovery. 