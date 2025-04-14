# User Guide

This guide explains how to use the Gospel Ambition Impact Map as an end-user, including navigating the different map views and understanding the visualizations.

## Map Views

The Gospel Ambition Impact Map offers several different visualizations:

### Globe View

The main globe view displays all types of activities across the world on a 3D globe.

**URL:** `/app/globe`

![Globe View](images/globe-view.jpg)

#### Features:

- **Auto-Rotating Globe**: The globe rotates automatically to show worldwide activity
- **Activity Points**: Colored dots show different types of activities
- **Clustering**: In areas with many activities, points cluster together with a number showing the count
- **Legend**: Shows the types of activities and their counts

### Prayer Globe View

A focused view showing only prayer-related activities.

**URL:** `/app/prayerglobe`

![Prayer Globe View](images/prayer-globe-view.jpg)

#### Features:

- Similar to the main globe but focused exclusively on prayer activities
- Red dots show individual prayers
- Yellow dots show locations being prayed for

### Activity Dashboard

A summary view showing recent activities in a list format.

**URL:** `/app/activity`

![Activity Dashboard](images/activity-dashboard.jpg)

## Interacting with the Maps

### Basic Navigation

- **Zoom**: Scroll with mouse wheel or pinch on touchscreens
- **Pan**: Click and drag to move the map
- **Rotate**: Hold Shift + drag to rotate the view (on 3D maps)
- **Stop Rotation**: The globe stops rotating when you interact with it

### Reading the Visualization

#### Activity Types and Colors

- **Red** - Prayer activities
- **Yellow** - Study/learning activities
- **Light Green** - Training activities
- **Blue** - Resource downloads
- **Dark Green** - Coaching activities

#### Clusters

When many activities occur in a similar location, they form clusters:
- The number inside the cluster shows how many points are combined
- Zoom in to see the individual points
- Clusters are colored according to the majority activity type

### Customizing the View

The gear icon in the top-right corner provides options to customize your view:

![Gear Menu](images/gear-menu.jpg)

Options include:
- **Show Donate QR**: Display a QR code linking to donation options
- **Show Mobile Map QR**: Display a QR code for accessing the map on mobile devices
- **Hide Donation Panel**: Toggle the donation information panel
- **Remove Top Bar**: For cleaner presentations, hide the navigation bar

### Sharing Specific Views

You can share a link to a specific configuration by:

1. Using the gear menu to set up your desired view
2. Clicking "Launch" to generate a URL with those settings
3. Sharing the resulting URL

Example URL with parameters:
```
https://example.com/app/globe?qr-donate&no_top
```

## Understanding the Data

### Data Timeframe

By default, the maps show activities from the last 30 days. This helps visualize recent engagement rather than historical data.

### Activity Types Explained

- **Prayer Activity**: Someone engaged in prayer related to disciple-making
- **Prayer for Location**: Someone specifically prayed for a geographic location
- **Study Activity**: Someone learning disciple-making principles
- **Training Activity**: Someone participating in or leading training
- **Download Activity**: Someone downloading resources for disciple-making
- **Coaching Activity**: Someone receiving or providing coaching

### Privacy Considerations

The data shown is anonymized and slightly offset from exact locations to protect privacy. No personal identifying information is displayed on the maps.

## Use Cases

### Ministry Leaders

- Monitor global engagement with your resources
- Identify areas of high or low activity
- Share impact with supporters and partners

### Prayer Teams

- Focus prayer efforts on specific regions
- Celebrate answered prayers
- Visualize global prayer movement

### Individual Believers

- Be encouraged by global gospel activity
- Join in prayer for specific regions
- Understand the worldwide impact of digital discipleship

## Troubleshooting

### Map Not Loading

- Check your internet connection
- Ensure your browser supports WebGL (required for 3D globe views)
- Try using a different browser (Chrome and Firefox work best)

### No Data Visible

- Check if the time period is correct (default is 30 days)
- Verify that activity recording is properly set up
- There may genuinely be no recorded activities yet

### Performance Issues

- On mobile devices or older computers, the 3D globe may run slowly
- Try zooming out to reduce the number of visible points
- Use a device with better graphics capabilities for optimal experience 