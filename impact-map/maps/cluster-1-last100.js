jQuery(document).ready(function(){
  var isMobile = false; //initiate as false
  // device detection
  if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {
    isMobile = true;
  }
  let chartDiv = jQuery('#chart')

  window.activity_list = {}
  window.activity_geojson = {
    "type": "FeatureCollection",
    "features": []
  }
  window.activity_geojson_praying = {
    "type": "FeatureCollection",
    "features": []
  }
  window.activity_geojson_studying = {
    "type": "FeatureCollection",
    "features": []
  }
  window.activity_geojson_training = {
    "type": "FeatureCollection",
    "features": []
  }
  window.activity_geojson_practicing = {
    "type": "FeatureCollection",
    "features": []
  }
  window.activity_geojson_coaching = {
    "type": "FeatureCollection",
    "features": []
  }

  // colors
  window.color_praying = '#FF3131'
  window.color_studying = '#FFBF00'
  window.color_training = '#98FB98'
  window.color_practicing = '#4CBB17'
  window.color_coaching = '#355E3B'

  // Add html and map
  let map_height = window.innerHeight
  let mobile_show = 'inherit'
  if ( isMobile && window.innerWidth < 640 ) {
    map_height = window.innerHeight / 2
    mobile_show = 'none'
  }
  chartDiv.empty().html(`
      <style>
          body {
                background-color: white;
                padding: 0;
            }
          #activity-wrapper {
              height: ${window.innerHeight - 400}px !important;
              overflow: scroll;
          }
          #activity-list{
              height: ${window.innerHeight - 450}px !important;
              overflow: scroll;
          }
          #activity-list li {
              font-size:.8em;
              list-style-type: none;
          }
          #activity-list h2 {
              font-size:1.2em;
              font-weight:bold;
          }
          #map-wrapper {
              height: ${map_height}px !important;
          }
          #map {
              height: ${map_height}px !important;
          }
          #map-header {
              position: absolute;
              top:10px;
              left:10px;
              z-index: 20;
              background-color: white;
              padding:1em;
              opacity: 0.8;
              border-radius: 5px;
          }
          .mapboxgl-ctrl-geocoder.mapboxgl-ctrl {
              display: ${mobile_show};
          }
          .onscreen {
              font-weight: bold;
          }
          .color-block.praying {
              background-color: ${window.color_praying};
              width: 20px;
              height: 20px;
              float: left;
              margin-right: 5px;
          }
          .color-block.studying {
              background-color: ${window.color_studying};
              width: 20px;
              height: 20px;
              float: left;
              margin-right: 5px;
          }
          .color-block.training {
              background-color: ${window.color_training};
              width: 20px;
              height: 20px;
              float: left;
              margin-right: 5px;
          }
          .color-block.practicing {
              background-color: ${window.color_practicing};
              width: 20px;
              height: 20px;
              float: left;
              margin-right: 5px;
          }
          .color-block.coaching {
              background-color: ${window.color_coaching};
              width: 20px;
              height: 20px;
              float: left;
              margin-right: 5px;
          }
          .coaching, .practicing {
              display:none;
          }
          .cell .practicing {
              display:none;
          }
          .cell .coaching {
              display:none;
          }
      </style>
      <div class="grid-x">
        <div class="medium-9 cell">
            <div id="map-wrapper">
                <div id='map'></div>
                <div id="map-header">
                  <h3>Movement Activities</h3>
                  ${mapObject.translation.countries}: <span id="country_count">0</span> | ${mapObject.translation.languages}: <span id="languages_count">0</span>
                </div>
            </div>
        </div>
        <div class="medium-3 cell">
            <div class="grid-x grid-padding-x" style="margin-top:10px;">
                <div class="cell">
                    <div>
                        <select name="type" id="type-dropdown" class="input-filter">
                            <option value="none">${mapObject.translation.all_types}</option>
                        </select>
                    </div>
                    <div>
                        <select name="country" id="country-dropdown" class="input-filter">
                            <option value="none">${mapObject.translation.all_countries}</option>
                        </select>
                    </div>
                    <div>
                        <select name="language" id="language-dropdown" class="input-filter">
                            <option value="none">${mapObject.translation.all_languages}</option>
                        </select>
                    </div>
                     <div>
                        <select name="type" id="project-dropdown" class="input-filter">
                            <option value="none">${mapObject.translation.all_projects}</option>
                        </select>
                    </div>
                    <div id="stats-list"></div>
                </div>
                <div class="cell"><div class="loading-spinner active"></div></div>
            </div>
            <div id="activity-wrapper">
                <ul id="activity-list"></ul>
            </div>
        </div>
      </div>
  `)
  let container = jQuery('#activity-list');

  mapboxgl.accessToken = mapObject.map_key;
  var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/discipletools/cl1qp8vuf002l15ngm5a7up59',
    center: [-98, 38.88],
    minZoom: 1,
    maxZoom: 15,
    zoom: 1
  });

  // disable map rotation using right click + drag
  map.dragRotate.disable();
  map.touchZoomRotate.disableRotation();

  map.addControl(
    new MapboxGeocoder({
      accessToken: mapboxgl.accessToken,
      mapboxgl: mapboxgl
    })
  );

  map.on('load', function() {
    initialize_cluster_map()
  });

  map.on('zoomend', function(e){
    load_map_activity()
  })
  map.on('dragend', function(e){
    load_map_activity()
  })

  function initialize_cluster_map() {
    // full geojson source
    map.addSource('layer-source-geojson', {
      type: 'geojson',
      data: window.activity_geojson,
      cluster: true,
      clusterMaxZoom: 20,
      clusterRadius: 50
    });

    // prayer geojson source and cluster
    map.addSource('layer-source-geojson-praying', {
      type: 'geojson',
      data: window.activity_geojson_praying,
      cluster: true,
      clusterMaxZoom: 20,
      clusterRadius: 50
    });
    map.addLayer({
      id: 'clusters-praying',
      type: 'circle',
      source: 'layer-source-geojson-praying',
      filter: ['has', 'point_count'],
      paint: {
        'circle-color': [
          'step',
          ['get', 'point_count'],
          window.color_praying,
          20,
          window.color_praying,
          150,
          window.color_praying
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
    map.addLayer({
      id: 'cluster-count-praying',
      type: 'symbol',
      source: 'layer-source-geojson-praying',
      filter: ['has', 'point_count'],
      layout: {
        'text-field': '{point_count_abbreviated}',
        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
        'text-size': 12
      }
    });
    map.addLayer({
      id: 'unclustered-point-prayer',
      type: 'circle',
      source: 'layer-source-geojson-praying',
      filter: ['!', ['has', 'point_count'] ],
      paint: {
        'circle-color': window.color_praying,
        'circle-radius':12,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff'
      }
    });


    // studying geojson source and cluster
    map.addSource('layer-source-geojson-studying', {
      type: 'geojson',
      data: window.activity_geojson_studying,
      cluster: true,
      clusterMaxZoom: 20,
      clusterRadius: 50
    });
    console.log('geojson_studying')
    console.log(window.activity_geojson_studying)
    map.addLayer({
      id: 'clusters-studying',
      type: 'circle',
      source: 'layer-source-geojson-studying',
      filter: ['has', 'point_count'],
      paint: {
        'circle-color': [
          'step',
          ['get', 'point_count'],
          window.color_studying,
          20,
          window.color_studying,
          150,
          window.color_studying
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
    map.addLayer({
      id: 'cluster-count-studying',
      type: 'symbol',
      source: 'layer-source-geojson-studying',
      filter: ['has', 'point_count'],
      layout: {
        'text-field': '{point_count_abbreviated}',
        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
        'text-size': 12
      }
    });
    map.addLayer({
      id: 'unclustered-point-studying',
      type: 'circle',
      source: 'layer-source-geojson-studying',
      filter: ['!', ['has', 'point_count'] ],
      paint: {
        'circle-color': window.color_studying,
        'circle-radius':12,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff'
      }
    });


    // practicing geojson source and cluster
    map.addSource('layer-source-geojson-training', {
      type: 'geojson',
      data: window.activity_geojson_training,
      cluster: true,
      clusterMaxZoom: 20,
      clusterRadius: 50
    });
    map.addLayer({
      id: 'clusters-training',
      type: 'circle',
      source: 'layer-source-geojson-training',
      filter: ['has', 'point_count'],
      paint: {
        'circle-color': [
          'step',
          ['get', 'point_count'],
          window.color_training,
          20,
          window.color_training,
          150,
          window.color_training
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
    map.addLayer({
      id: 'cluster-count-training',
      type: 'symbol',
      source: 'layer-source-geojson-training',
      filter: ['has', 'point_count'],
      layout: {
        'text-field': '{point_count_abbreviated}',
        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
        'text-size': 12
      }
    });
    map.addLayer({
      id: 'unclustered-point-training',
      type: 'circle',
      source: 'layer-source-geojson-training',
      filter: ['!', ['has', 'point_count'] ],
      paint: {
        'circle-color': window.color_training,
        'circle-radius':12,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff'
      }
    });


    map.addSource('layer-source-geojson-practicing', {
      type: 'geojson',
      data: window.activity_geojson_practicing,
      cluster: true,
      clusterMaxZoom: 20,
      clusterRadius: 50
    });
    map.addLayer({
      id: 'clusters-practicing',
      type: 'circle',
      source: 'layer-source-geojson-practicing',
      filter: ['has', 'point_count'],
      paint: {
        'circle-color': [
          'step',
          ['get', 'point_count'],
          window.color_practicing,
          20,
          window.color_practicing,
          150,
          window.color_practicing
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
    map.addLayer({
      id: 'cluster-count-practicing',
      type: 'symbol',
      source: 'layer-source-geojson-practicing',
      filter: ['has', 'point_count'],
      layout: {
        'text-field': '{point_count_abbreviated}',
        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
        'text-size': 12
      }
    });
    map.addLayer({
      id: 'unclustered-point-practicing',
      type: 'circle',
      source: 'layer-source-geojson-practicing',
      filter: ['!', ['has', 'point_count'] ],
      paint: {
        'circle-color': window.color_practicing,
        'circle-radius':12,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff'
      }
    });


    map.addSource('layer-source-geojson-coaching', {
      type: 'geojson',
      data: window.activity_geojson_coaching,
      cluster: true,
      clusterMaxZoom: 20,
      clusterRadius: 50
    });
    map.addLayer({
      id: 'clusters-coaching',
      type: 'circle',
      source: 'layer-source-geojson-coaching',
      filter: ['has', 'point_count'],
      paint: {
        'circle-color': [
          'step',
          ['get', 'point_count'],
          window.color_coaching,
          20,
          window.color_coaching,
          150,
          window.color_coaching
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
    map.addLayer({
      id: 'cluster-count-coaching',
      type: 'symbol',
      source: 'layer-source-geojson-coaching',
      filter: ['has', 'point_count'],
      layout: {
      'text-field': '{point_count_abbreviated}',
      'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
      'text-size': 12,
      },
      paint: {
          'text-color': '#FFF'
      }
    });
    map.addLayer({
      id: 'unclustered-point-coaching',
      type: 'circle',
      source: 'layer-source-geojson-coaching',
      filter: ['!', ['has', 'point_count'] ],
      paint: {
        'circle-color': window.color_coaching,
        'circle-radius':12,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff'
      }
    });

  }

  function load_geojson(){
    let data = get_filters()
    window.post_request('load_geojson', data )
      .done( data => {
        console.log('loaded_geojson')
        console.log(data)
        "use strict";

        window.activity_geojson = data
        data.features.forEach( (v) => {
          if ( 'praying' === v.properties.type ) {
            window.activity_geojson_praying.features.push(v)
          }
          else if ( 'studying' === v.properties.type ) {
            v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.002 // layer shift so that they don't overlap
            window.activity_geojson_studying.features.push(v)
          }
          else if ( 'training' === v.properties.type ) {
            v.geometry.coordinates[0] = v.geometry.coordinates[0] - 0.002 // layer shift so that they don't overlap
            window.activity_geojson_training.features.push(v)
          }
          else if ( 'practicing' === v.properties.type ) {
            v.geometry.coordinates[1] = v.geometry.coordinates[1] + 0.002 // layer shift so that they don't overlap
            window.activity_geojson_practicing.features.push(v)
          }
          else if ( 'coaching' === v.properties.type ) {
            v.geometry.coordinates[1] = v.geometry.coordinates[1] - 0.002 // layer shift so that they don't overlap
            window.activity_geojson_coaching.features.push(v)
          }
        })

        var mapSource = map.getSource('layer-source-geojson');
        var mapSourcePraying = map.getSource('layer-source-geojson-praying');
        var mapSourceStudying = map.getSource('layer-source-geojson-studying');
        var mapSourceTraining = map.getSource('layer-source-geojson-training');
        var mapSourcePracticing = map.getSource('layer-source-geojson-practicing');
        var mapSourceCoaching = map.getSource('layer-source-geojson-coaching');

        if( typeof mapSource !== 'undefined') {
          map.getSource('layer-source-geojson').setData(window.activity_geojson);
        }
        if( typeof mapSourcePraying !== 'undefined') {
          map.getSource('layer-source-geojson-praying').setData(window.activity_geojson_praying);
        }
        if( typeof mapSourceStudying !== 'undefined') {
          map.getSource('layer-source-geojson-studying').setData(window.activity_geojson_studying);
        }
        if( typeof mapSourceTraining !== 'undefined') {
          map.getSource('layer-source-geojson-training').setData(window.activity_geojson_training);
        }
        if( typeof mapSourcePracticing !== 'undefined') {
          map.getSource('layer-source-geojson-practicing').setData(window.activity_geojson_practicing);
        }
        if( typeof mapSourceCoaching !== 'undefined') {
          map.getSource('layer-source-geojson-coaching').setData(window.activity_geojson_coaching);
        }

        load_type_dropdown( )
        load_countries_dropdown()
        load_languages_dropdown()
        load_project_dropdown()
        load_title_stats()
        load_map_activity()

      })
  }

  function load_map_activity() {
    container.empty()
    let spinner = jQuery('.loading-spinner')
    spinner.addClass('active')
    let data = get_filters()

    window.post_request('activity_list', data )
      .done( data => {
        let spinner = jQuery('.loading-spinner')
        console.log('loaded_map_activity')
        console.log(data)
        "use strict";
        window.activity_list = data
        update_activity_list()
        spinner.removeClass('active')
      })
  }
  load_geojson()


  jQuery('.input-filter').on('change', function(e){
    load_map_activity()
    limit_cluster_to_filter()
  })

  function get_filters() {
    window.current_bounds = map.getBounds()
    let country = jQuery('#country-dropdown').val()
    let language = jQuery('#language-dropdown').val()
    let type = jQuery('#type-dropdown').val()
    let project = jQuery('#project-dropdown').val()
    return {
      bounds: { 'n_lat': window.current_bounds._ne.lat, 's_lat': window.current_bounds._sw.lat, 'e_lng': window.current_bounds._ne.lng, 'w_lng': window.current_bounds._sw.lng},
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      country: country,
      language: language,
      type: type,
      project: project,
      zoom: map.getZoom()
    }
  }
  function limit_cluster_to_filter() {
    let data = get_filters()
    console.log(data)

    let geojson = {
      "type": "FeatureCollection",
      "features": []
    }
    let praying = {
      "type": "FeatureCollection",
      "features": []
    }
    let studying = {
      "type": "FeatureCollection",
      "features": []
    }
    let training = {
      "type": "FeatureCollection",
      "features": []
    }
    let practicing = {
      "type": "FeatureCollection",
      "features": []
    }
    let coaching = {
      "type": "FeatureCollection",
      "features": []
    }

    jQuery.each( window.activity_geojson_praying.features, function(i,v){
       praying = _build_geojson(praying, v)
     })
     jQuery.each( window.activity_geojson_studying.features, function(i,v){
      studying = _build_geojson(studying, v)
    })
    jQuery.each( window.activity_geojson_training.features, function(i,v){
      training = _build_geojson(training, v)
    })
    jQuery.each( window.activity_geojson_practicing.features, function(i,v){
      practicing = _build_geojson(practicing, v)
    })
    jQuery.each( window.activity_geojson_coaching.features, function(i,v){
      coaching = _build_geojson(coaching, v)
    })

    function _build_geojson( geojson, v ){
      // none set
      if ( 'none' === data.project && 'none' === data.type && 'none' === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }
      // country set
      else if ( 'none' === data.project && 'none' === data.type && 'none' === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // project set
      else if ( v.properties.project === data.project && 'none' === data.type && 'none' === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }
      // type set
      else if ( 'none' === data.project && v.properties.type === data.type && 'none' === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }
      // language set
      else if ( 'none' === data.project && 'none' === data.type && v.properties.language === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }

      // language & type set
      else if ( 'none' === data.project && v.properties.type === data.type && v.properties.language === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }
      // country & type set
      else if ( 'none' === data.project && v.properties.type === data.type && 'none' === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // country & language set
      else if ( 'none' === data.project && 'none' === data.type && v.properties.language === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // project & country set
      else if ( v.properties.project === data.project && 'none' === data.type && 'none' === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // project & language set
      else if ( v.properties.project === data.project && 'none' === data.type && v.properties.language === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }
      // project & type set
      else if ( v.properties.project === data.project && v.properties.type === data.type && 'none' === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }


      // country & language & type set
      else if ( 'none' === data.project && v.properties.type === data.type && v.properties.language === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // project & country & type set
      else if ( v.properties.project === data.project && v.properties.type === data.type && 'none' === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // project & country & language set
      else if ( v.properties.project === data.project && 'none' === data.type && v.properties.language === data.language && v.properties.country === data.country ) {
        geojson.features.push(v)
      }
      // project & language & type set
      else if ( v.properties.project === data.project && v.properties.type === data.type && v.properties.language === data.language && 'none' === data.country ) {
        geojson.features.push(v)
      }
      return geojson;
    }

    var mapSource= map.getSource('layer-source-geojson');
    if(typeof mapSource === 'undefined') {
      load_geojson()
    } else {
      map.getSource('layer-source-geojson-praying').setData(praying);
      map.getSource('layer-source-geojson-studying').setData(studying);
      map.getSource('layer-source-geojson-training').setData(training);
      map.getSource('layer-source-geojson-practicing').setData(practicing);
      map.getSource('layer-source-geojson-coaching').setData(coaching);
    }
  }

  function update_activity_list(){
    container.empty()
    let spinner = jQuery('.loading-spinner')

    jQuery('#stats-list').empty().append(`
      <div class="grid-x">
        <div class="cell">
          <strong>NEW ACTIVITIES in 100 HOURS</strong>
        </div>
        <div class="cell">
          <div class="color-block praying"></div> <strong>${mapObject.translation.praying}</strong>: <span class="onscreen praying">0</span> (<span class="stats praying">0</span>)<br>
        </div>
        <div class="cell">
          <div class="color-block studying"></div> <strong>${mapObject.translation.studying}</strong>: <span class="onscreen studying">0</span> (<span class="stats studying">0</span>)<br>
        </div>
        <div class="cell">
          <div class="color-block training"></div> <strong>${mapObject.translation.training}</strong>: <span class="onscreen training">0</span> (<span class="stats training">0</span>)<br>
        </div>
        <div class="cell practicing">
          <div class="color-block practicing"></div> <strong>${mapObject.translation.practicing}</strong>: <span class="onscreen practicing">0</span> (<span class="stats practicing">0</span>)<br>
        </div>
        <div class="cell coaching">
          <div class="color-block coaching"></div> <strong>${mapObject.translation.coaching}</strong>: <span class="onscreen coaching">0</span> (<span class="stats coaching">0</span>)<br>
        </div>
      </div>
      <hr>
      `)
    jQuery.each(window.activity_geojson.types, function(i,v){
      jQuery('.stats.'+v.code).html(`${v.count}`)
    })
    jQuery.each(window.activity_list.types, function(i,v){
      jQuery('.onscreen.'+v.code).html(`${v.count}`)
    })

    jQuery.each( window.activity_list.list, function(i,v){
      if ( '' === v.note ) {
        return
      }
      container.append(`<li class="${v.type} ${v.country} ${v.language}"><strong>(${v.time})</strong> ${v.note} </li>`)
    })

    if ( ! window.activity_list.list  ) {
      container.append(`<li><strong>${mapObject.translation.results}</strong> 0</li>`)
    }

    if ( window.activity_list.count > 250 ) {
      container.append(`<hr><li><strong>${window.activity_list.count - 250} ${mapObject.translation.additional_records}</strong></li><br><br>`)
    }

    spinner.removeClass('active')
  }

  function load_countries_dropdown() {
    let country_dropdown = jQuery('#country-dropdown')
    let points = window.activity_geojson
    window.selected_country = country_dropdown.val()
    country_dropdown.empty()

    let add_selected = ''
    country_dropdown.append(`<option value="none">${mapObject.translation.all_countries}</option>`)
    country_dropdown.append(`<option disabled>---</option>`)
    jQuery.each(points.countries, function(i,v){
      add_selected = ''
      if ( v.code === window.selected_country ) {
        add_selected = ' selected'
      }
      country_dropdown.append(`<option value="${v.code}" ${add_selected}>${v.name} (${v.count})</option>`)
    })
  }
  function load_languages_dropdown() {
    let language_dropdown = jQuery('#language-dropdown')
    let points = window.activity_geojson
    window.selected_language = language_dropdown.val()
    language_dropdown.empty()

    let add_selected = ''
    language_dropdown.append(`<option value="none">${mapObject.translation.all_languages}</option>`)
    language_dropdown.append(`<option disabled>---</option>`)
    jQuery.each(points.languages, function(i,v){
      add_selected = ''
      if ( v.code === window.selected_language ) {
        add_selected = ' selected'
      }
      language_dropdown.append(`<option value="${v.code}" ${add_selected}>${v.name} (${v.count})</option>`)
    })
  }
  function load_type_dropdown( ) {
    let type_dropdown = jQuery('#type-dropdown')
    window.selected_type = type_dropdown.val()

    let add_selected = ''
    type_dropdown.empty().append(
      `
        <option value="none">${mapObject.translation.all_types}</option>
        <option disabled>---</option>
        <option value="" class="dd praying">${mapObject.translation.praying}: 0</option>
        <option value="" class="dd studying">${mapObject.translation.studying}: 0</option>
        <option value="" class="dd training">${mapObject.translation.training}: 0</option>
        <option value="" class="dd practicing">${mapObject.translation.practicing}: 0</option>
        <option value="" class="dd coaching">${mapObject.translation.coaching}: 0</option>
        `
    )

    jQuery.each(window.activity_geojson.types, function(i,v){
      add_selected = ''
      if ( v.code === window.selected_type ) {
        add_selected = ' selected'
      }
      jQuery('.dd.'+v.code).val(v.code).html(`${v.name} (${v.count})`)
    })

    // Only proceed with style modifications if the map style is loaded
    if (map.isStyleLoaded()) {
      let ids = [
        'clusters-praying',
        'cluster-count-praying',
        'unclustered-point-prayer',
        'clusters-studying',
        'cluster-count-studying',
        'unclustered-point-studying',
        'clusters-training',
        'cluster-count-training',
        'unclustered-point-training',
        'clusters-practicing',
        'cluster-count-practicing',
        'unclustered-point-practicing',
        'clusters-coaching',
        'cluster-count-coaching',
        'unclustered-point-coaching'
      ]
      jQuery.each(ids, function(i,v){
        map.setLayoutProperty(v, 'visibility', 'none');
      })
      console.log(window.selected_type)
      if ( 'praying' === window.selected_type ) {
        map.setLayoutProperty('clusters-praying', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-praying', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-prayer', 'visibility', 'visible');
      }
      else if ( 'studying' === window.selected_type ) {
        map.setLayoutProperty('clusters-studying', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-studying', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-studying', 'visibility', 'visible');
      }
      else if ( 'training' === window.selected_type ) {
        map.setLayoutProperty('clusters-training', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-training', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-training', 'visibility', 'visible');
      }
      else if ( 'practicing' === window.selected_type ) {
        map.setLayoutProperty('clusters-practicing', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-practicing', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-practicing', 'visibility', 'visible');
      }
      else if ( 'coaching' === window.selected_type ) {
        map.setLayoutProperty('clusters-coaching', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-coaching', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-coaching', 'visibility', 'visible');
      }
      else {
        map.setLayoutProperty('clusters-praying', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-praying', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-prayer', 'visibility', 'visible');
        map.setLayoutProperty('clusters-studying', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-studying', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-studying', 'visibility', 'visible');
        map.setLayoutProperty('clusters-training', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-training', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-training', 'visibility', 'visible');
        map.setLayoutProperty('clusters-practicing', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-practicing', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-practicing', 'visibility', 'visible');
        map.setLayoutProperty('clusters-coaching', 'visibility', 'visible');
        map.setLayoutProperty('cluster-count-coaching', 'visibility', 'visible');
        map.setLayoutProperty('unclustered-point-coaching', 'visibility', 'visible');
      }
    } else {
      // If style is not loaded, wait for it to load before setting properties
      map.once('style.load', () => {
        load_type_dropdown();
      });
    }
  }
  function load_title_stats() {
    jQuery('#country_count').html(window.activity_geojson.countries_count)
    jQuery('#languages_count').html(window.activity_geojson.languages_count)
  }
  function load_project_dropdown() {
    let project_dropdown = jQuery('#project-dropdown')
    let points = window.activity_geojson
    window.selected_project = project_dropdown.val()

    let add_selected = ''
    project_dropdown.empty().append(
      `
        <option value="none">All Projects</option>
        <option disabled>---</option>
        <option value="" class="dd prayer_global">Prayer Global: 0</option>
        <option value="" class="dd prayer_tools">Prayer Tools: 0</option>
        <option value="" class="dd zume">Zume: 0</option>
        <option value="" class="dd kingdom_training"></option>
        <option value="" class="dd disciple_tools"></option>
        `
    )
    jQuery.each(points.projects, function(i,v){
      add_selected = ''
      if ( v.code === window.selected_project ) {
        add_selected = ' selected'
      }
      jQuery('.dd.'+v.code).val(v.code).html(`${v.name} (${v.count})`)
      jQuery('.stats.'+v.code).html(`${v.name}: ${v.count}`)
    })
  }
})

jQuery(document).ready(function(){
  jQuery(document).foundation()
})
