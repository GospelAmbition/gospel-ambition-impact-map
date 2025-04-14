# Installation Guide

This document provides detailed instructions for installing and configuring the Gospel Ambition Impact Map plugin.

## Prerequisites

Before installing the plugin, ensure you have:

- WordPress 4.7+
- Disciple.Tools Theme 1.19+ installed and activated
- PHP 7.4+
- A Mapbox API key (for map visualization)

## Installation

### Automatic Installation (Recommended)

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "Gospel Ambition Impact Map"
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Download the latest release from the [GitHub repository](https://github.com/GospelAmbition/gospel-ambition-impact-map)
2. In your WordPress admin, navigate to **Plugins > Add New > Upload Plugin**
3. Choose the downloaded ZIP file and click "Install Now"
4. After installation completes, click "Activate Plugin"

## Initial Configuration

### Setting Up Mapbox API

1. If you don't already have a Mapbox account, create one at [Mapbox.com](https://www.mapbox.com/)
2. Generate a new API key with the following scopes:
   - Maps: Read
   - Styles: Read
   - Vision: Read
3. In your WordPress admin, navigate to **Disciple.Tools > Settings > Mapbox API Key**
4. Enter your Mapbox API key and save changes

### Plugin Configuration

1. Navigate to **Disciple.Tools > Extensions > Gospel Ambition Impact Map**
2. Configure the following settings:
   - Default map view (Globe or Flat)
   - Activity types to display
   - Data retention period
   - Custom styling options (if desired)

## Verification

To verify your installation was successful:

1. Navigate to your site's frontend
2. Visit `/app/globe` - you should see a 3D globe visualization
3. Visit `/app/prayerglobe` - you should see the prayer-specific globe

## Troubleshooting

### Common Issues

- **Blank Map Display**: Ensure your Mapbox API key is valid and has the correct permissions
- **No Data Showing**: The plugin needs data from tracked activities - if you're just getting started, there may not be data yet
- **Missing Globe or Prayer Globe**: Check that your server meets the minimum requirements for 3D visualization (WebGL support)

### Getting Support

If you encounter issues:

1. Check the [GitHub issues](https://github.com/GospelAmbition/gospel-ambition-impact-map/issues) for known problems
2. Contact the [Disciple.Tools community](https://disciple.tools/community/) for assistance

## Next Steps

Once installed successfully, you can:

- Customize your map displays using the [Admin Guide](admin-guide.md)
- Learn how to interact with the maps in the [User Guide](user-guide.md)
- Set up activity tracking from your other tools using the [API Documentation](api.md) 