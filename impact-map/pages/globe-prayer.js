/* Globe Prayer Map JavaScript */

// Global Variables
const colorSchemes = {
    praying: '#FF3131',
    prayed_for: '#FFBF00',
    studying: '#FFBF00',
    training: '#98FB98',
    downloading: '#00BFFF',
    practicing: '#4CBB17',
    coaching: '#355E3B'
};

const coordinateOffsets = {
    praying: { x: 0.002, y: 0 },
    prayed_for: { x: 0.0012, y: 0.0012 },
    studying: { x: 0, y: -0.002 },
    training: { x: -0.002, y: 0 },
    downloading: { x: 0.0012, y: 0.0012 },
    practicing: { x: 0, y: -0.002 },
    coaching: { x: -0.0012, y: -0.0012 }
};

// Layer toggle configuration - which layers to display
const layerToggle = { 
    'praying': true, 
    'prayed_for': true, 
    'studying': false, 
    'training': false, 
    'downloading': false, 
    'practicing': false, 
    'coaching': false 
};

// Activity counts
let activityCounts = {
    praying: 0,
    prayed_for: 0,
    studying: 0,
    training: 0,
    downloading: 0,
    practicing: 0,
    coaching: 0
};

// Globe rotation settings
const globeSettings = {
    secondsPerRevolution: 240,
    maxSpinZoom: 5,
    slowSpinZoom: 3
};

// Geojson collections
const geojsonCollections = {};

// Initialize Map
function initializeMap() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check if mobile
    const isMobile = window.innerWidth < 768;
    
    // Determine zoom level based on device
    let zoom = 2.5;
    if (isMobile && window.innerWidth < 640) {
        zoom = 1;
    } else if (isMobile && window.innerWidth < 1350) {
        zoom = 2;
    }
    
    // Set map height based on URL parameters
    const mapHeight = urlParams.has('no_top') ? window.innerHeight : window.innerHeight - 50;
    updateMapHeight(mapHeight);
    
    // Initialize Mapbox
    mapboxgl.accessToken = jsObject.map_key;
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v9',
        projection: 'globe',
        zoom: zoom,
        center: [30, 15]
    });
    
    // Add fog to the globe
    map.on('style.load', () => {
        map.setFog({});
    });
    
    // Setup globe rotation
    setupGlobeRotation(map);
    
    // Load data
    fetchMapData(map);
    
    // Initialize UI elements
    initializeUIElements();
    
    // Check URL parameters to show/hide elements
    checkURLParameters();
}

// Update map height
function updateMapHeight(height) {
    document.getElementById('map-wrapper').style.height = `${height}px`;
    document.getElementById('map').style.height = `${height}px`;
}

// Setup globe rotation
function setupGlobeRotation(map) {
    let userInteracting = false;
    const spinEnabled = true;
    
    function spinGlobe() {
        const zoom = map.getZoom();
        if (spinEnabled && !userInteracting && zoom < globeSettings.maxSpinZoom) {
            let distancePerSecond = 360 / globeSettings.secondsPerRevolution;
            if (zoom > globeSettings.slowSpinZoom) {
                // Slow spinning at higher zooms
                const zoomDif = (globeSettings.maxSpinZoom - zoom) / (globeSettings.maxSpinZoom - globeSettings.slowSpinZoom);
                distancePerSecond *= zoomDif;
            }
            const center = map.getCenter();
            center.lng -= distancePerSecond;
            // Smoothly animate the map over one second.
            map.easeTo({ center, duration: 1000, easing: (n) => n });
        }
    }
    
    // Pause spinning on interaction
    map.on('mousedown', () => {
        userInteracting = true;
    });
    
    map.on('dragstart', () => {
        userInteracting = true;
    });
    
    // When animation is complete, start spinning if there is no ongoing interaction
    map.on('moveend', () => {
        userInteracting = false;
        spinGlobe();
    });
    
    map.on('zoomend', () => {
        spinGlobe();
    });
    
    // Start spinning initially
    spinGlobe();
}

// Fetch map data
function fetchMapData(map) {
    // Show loading overlay
    showLoading(true);
    
    // Initialize geojson collections
    Object.keys(layerToggle).forEach(key => {
        geojsonCollections[key] = {
            type: "FeatureCollection",
            features: []
        };
    });
    
    // Fetch data from API
    window.get_geojson().then(function(data) {
        // Process data and add to collections
        processData(data);
        
        // Update legend counts
        updateLegendCounts();
        
        // Add layers to map
        addLayersToMap(map);
        
        // Hide loading overlay
        showLoading(false);
    }).catch(function(error) {
        console.error("Error fetching map data:", error);
        showError("Failed to load map data. Please try refreshing the page.");
        showLoading(false);
    });
}

// Process the data from API
function processData(data) {
    data.features.forEach(feature => {
        const type = feature.properties.type;
        const subtype = feature.properties.subtype;
        
        // Special handling for prayer_for_location
        if (type === 'praying' && subtype === 'prayer_for_location') {
            // Apply offset
            applyCoordinateOffset(feature, 'prayed_for');
            geojsonCollections.prayed_for.features.push(feature);
            activityCounts.prayed_for++;
        } 
        // Handle other types that have layer toggle enabled
        else if (type in layerToggle) {
            // Apply offset
            applyCoordinateOffset(feature, type);
            geojsonCollections[type].features.push(feature);
            activityCounts[type]++;
        }
    });
}

// Apply coordinate offset to prevent overlapping points
function applyCoordinateOffset(feature, type) {
    if (coordinateOffsets[type]) {
        feature.geometry.coordinates[0] += coordinateOffsets[type].x;
        feature.geometry.coordinates[1] += coordinateOffsets[type].y;
    }
}

// Update legend counts
function updateLegendCounts() {
    Object.keys(activityCounts).forEach(key => {
        const element = document.getElementById(`legend_${key}`);
        if (element) {
            element.innerHTML = numberWithCommas(activityCounts[key]);
        }
    });
}

// Add layers to map
function addLayersToMap(map) {
    // Add each layer based on layerToggle configuration
    Object.keys(layerToggle).forEach(layerType => {
        if (layerToggle[layerType] && geojsonCollections[layerType].features.length > 0) {
            addLayerToMap(map, layerType);
        }
    });
}

// Add a specific layer to the map
function addLayerToMap(map, layerType) {
    // Add source
    map.addSource(`layer-source-geojson-${layerType}`, {
        type: 'geojson',
        data: geojsonCollections[layerType],
        cluster: true,
        clusterMaxZoom: 20,
        clusterRadius: 50
    });
    
    // Add cluster layer
    map.addLayer({
        id: `clusters-${layerType}`,
        type: 'circle',
        source: `layer-source-geojson-${layerType}`,
        filter: ['has', 'point_count'],
        paint: {
            'circle-color': [
                'step',
                ['get', 'point_count'],
                colorSchemes[layerType],
                20,
                colorSchemes[layerType],
                150,
                colorSchemes[layerType]
            ],
            'circle-radius': [
                'step',
                ['get', 'point_count'],
                20,
                100,
                30,
                750,
                40
            ]
        }
    });
    
    // Add cluster count layer
    map.addLayer({
        id: `cluster-count-${layerType}`,
        type: 'symbol',
        source: `layer-source-geojson-${layerType}`,
        filter: ['has', 'point_count'],
        layout: {
            'text-field': '{point_count_abbreviated}',
            'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
            'text-size': 12
        }
    });
    
    // Add unclustered point layer
    map.addLayer({
        id: `unclustered-point-${layerType}`,
        type: 'circle',
        source: `layer-source-geojson-${layerType}`,
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-color': colorSchemes[layerType],
            'circle-radius': 12,
            'circle-stroke-width': 1,
            'circle-stroke-color': '#fff'
        }
    });
}

// Initialize UI elements
function initializeUIElements() {
    // Legend toggling
    jQuery('#legend').on('click', function() {
        jQuery('.click-hide').toggle();
    });
    
    // Gear menu toggle
    jQuery('.fi-widget').click(function() {
        jQuery('#gear-dropdown').toggle();
    });
    
    // Launch button click handler
    jQuery('#launch-btn').click(function() {
        const params = [];
        
        if (jQuery('#qr-donate-toggle').is(':checked')) {
            params.push('qr-donate');
        }
        if (jQuery('#qr-toggle').is(':checked')) {
            params.push('qr');
        }
        if (jQuery('#donation-toggle').is(':checked')) {
            params.push('donation');
        }
        if (jQuery('#no-top-toggle').is(':checked')) {
            params.push('no_top');
        }
        
        let url = 'https://goimpactmap.com/app/prayerglobe';
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.open(url, '_blank');
    });
}

// Check URL parameters and show/hide elements accordingly
function checkURLParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Show/hide donation panel
    jQuery('#donation').toggle(urlParams.has('donation'));
    
    // Show/hide QR codes
    jQuery('#qr').toggle(urlParams.has('qr'));
    jQuery('#qr-donate').toggle(urlParams.has('qr-donate'));
}

// Show or hide loading overlay
function showLoading(show) {
    if (show) {
        // Create loading overlay if it doesn't exist
        if (!document.getElementById('loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner';
            
            const text = document.createElement('div');
            text.innerText = 'Loading map data...';
            
            overlay.appendChild(spinner);
            overlay.appendChild(text);
            
            document.getElementById('map-wrapper').appendChild(overlay);
        } else {
            document.getElementById('loading-overlay').style.display = 'flex';
        }
    } else {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
}

// Show error message
function showError(message) {
    // Create error element
    const errorEl = document.createElement('div');
    errorEl.style.position = 'absolute';
    errorEl.style.top = '50%';
    errorEl.style.left = '50%';
    errorEl.style.transform = 'translate(-50%, -50%)';
    errorEl.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
    errorEl.style.padding = '20px';
    errorEl.style.borderRadius = '5px';
    errorEl.style.color = '#b13634';
    errorEl.style.fontWeight = 'bold';
    errorEl.style.zIndex = '25';
    errorEl.style.textAlign = 'center';
    errorEl.innerText = message;
    
    document.getElementById('map-wrapper').appendChild(errorEl);
}

// Format number with commas
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Initialize map when document is ready
jQuery(document).ready(function() {
    initializeMap();
}); 