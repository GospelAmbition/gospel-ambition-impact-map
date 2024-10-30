<?php
$map_image_url = trailingslashit( plugin_dir_url( __DIR__ ) ) . 'images/'
?>

<style id="custom-style">
    #wrapper {
        height: 2000px !important;
        position: relative;
    }
    #map-wrapper {
        height: 2000px !important;
    }
    #map {
        height: 2000px !important;
    }
</style>
<style>
    #map-header {
        position: absolute;
        top:10px;
        left:10px;
        z-index: 10;
        background-color: white;
        padding:1em;
        opacity: 0.8;
        border-radius: 5px;
    }
    #map-header-title {
        text-transform: uppercase;
        font-weight: bold;
    }
</style>

<div id="initialize-screen">
    <div id="initialize-spinner-wrapper" class="center">
        <progress class="success initialize-progress" max="46" value="0"></progress><br>
        <?php echo esc_html__( 'Loading the planet', 'zume' ) ?> ...<br>
        <span id="initialize-people" style="display:none;"><?php echo esc_html__( 'Locating world population', 'zume' ) ?> ...</span><br>
        <span id="initialize-activity" style="display:none;"><?php echo esc_html__( 'Calculating movement activity', 'zume' ) ?> ...</span><br>
        <span id="initialize-coffee" style="display:none;"><?php echo esc_html__( 'Shamelessly brewing coffee', 'zume' ) ?> ...</span><br>
        <span id="initialize-dothis" style="display:none;"><?php echo esc_html__( 'Let‘s do this', 'zume' ) ?> ...</span><br>
    </div>
</div>

<div id="wrapper">

    <div class="grid-x">
        <div class="cell medium-9" id="map-container">
            <div id="map-wrapper">
                <span class="loading-spinner active"></span>
                <div id='map'></div>
                <div id="map-header">
                    <button
                        id="exit-btn"
                        class="btn outline | d-flex gap--2 px-1 py--2 | absolute under left"
                        aria-label="<?php echo esc_html__( 'Close', 'zume' ) ?>"
                        type="button"
                    >
                        <span><?php echo esc_html__( 'Close', 'zume' ) ?></span><span class="icon z-icon-close"></span>
                    </button>
                    <h3 id="map-header-title"><span class="loading-spinner active"></span></h3>
                    <span id="map-header-description"><span class="loading-spinner active"></span></span>
                </div>
            </div>
        </div>
        <div class="cell medium-3" id="map-sidebar-wrapper">
            <!-- details panel -->
            <div id="details-panel">
                <div class="grid-x grid-padding-x" >
                    <div class="cell">
                        <br></br>
                        <h1 id="title"></h1>
                        <h3><?php echo esc_html__( 'Population', 'zume' ) ?>: <span id="population">0</span></h3>
                        <hr>
                    </div>
                    <div class="cell">
                        <h2 id="panel-type-title"></h2>
                    </div>
                    <div class="cell" id="needed-row">
                        <h3><?php echo esc_html__( 'Needed', 'zume' ) ?>: <span id="needed">0</span></h3>
                    </div>
                    <div class="cell">
                        <h3><?php echo esc_html__( 'Reported', 'zume' ) ?>: <span id="reported">0</span></h3>
                    </div>
                    <div class="cell">
                        <hr>
                    </div>
                    <div class="cell" id="goal-row">
                        <h2><?php echo esc_html__( 'Goal', 'zume' ) ?>: <span id="saturation-goal">0</span>%</h2>
                        <meter id="meter" class="meter" value="30" min="0" low="33" high="66" optimum="100" max="100"></meter>
                    </div>
                </div>
            </div>

            <!-- start screen training-->
            <div id="training-start-screen" class="training-content">
                <div class="grid-x grid-padding-x" >
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'search.svg' ?>" alt="search icon" />
                        <h2><?php echo esc_html__( 'Search', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Search for any city or place with the search input.', 'zume' ) ?></p>
                    </div>
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'zoom.svg' ?>" alt="zoom icon"  />
                        <h2><?php echo esc_html__( 'Zoom', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Scroll zoom with your mouse or pinch zoom with track pads and phones to focus on sections of the map.', 'zume' ) ?></p>
                    </div>
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'drag.svg' ?>" alt="drag icon"  />
                        <h2><?php echo esc_html__( 'Drag', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Click and drag the map any direction to look at a different part of the map.', 'zume' ) ?></p>
                    </div>
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'click.svg' ?>" alt="click icon" />
                        <h2><?php echo esc_html__( 'Click', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Click a single section and reveal a details panel with more information about the location.', 'zume' ) ?></p>
                    </div>
                </div>
            </div>
            <div id="training-help-screen" class="training-content" style="display:none;"><hr>
                <div class="grid-x grid-padding-x" >
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'search.svg' ?>" alt="search icon" />
                        <h2><?php echo esc_html__( 'Search', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Search for any city or place with the search input.', 'zume' ) ?></p>
                    </div>
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'zoom.svg' ?>" alt="zoom icon"  />
                        <h2><?php echo esc_html__( 'Zoom', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Scroll zoom with your mouse or pinch zoom with track pads and phones to focus on sections of the map.', 'zume' ) ?></p>
                    </div>
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'drag.svg' ?>" alt="drag icon"  />
                        <h2><?php echo esc_html__( 'Drag', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Click and drag the map any direction to look at a different part of the map.', 'zume' ) ?></p>
                    </div>
                    <div class="cell center">
                        <img class="training-screen-image" src="<?php echo esc_url( $map_image_url ) . 'click.svg' ?>" alt="click icon" />
                        <h2><?php echo esc_html__( 'Click', 'zume' ) ?></h2>
                        <p><?php echo esc_html__( 'Click a single section and reveal a details panel with more information about the location.', 'zume' ) ?></p>
                    </div>
                </div>
            </div>
            <div class="center"><i class="fi-info" id="help-toggle-icon" onclick="jQuery('#training-help-screen').toggle()"></i></div>
        </div>
    </div>
</div>

<style>
    .world-list-wrapper {
    }
    .a0-list-wrapper {
        padding: 0 0 0 3em;
    }
    .a1-list-wrapper {
        padding: 0 0 0 6em;
    }
    .a2-list-wrapper {
        padding: 0 0 0 9em;
    }
    .a3-list-wrapper {
        padding: 0 0 0 12em;
    }
    .progress-list-item {
        border: 1px solid lightgrey;
        padding: 1em;
    }
</style>
<!-- modal -->
<div class="off-canvas position-right is-closed" id="offCanvasNestedPush" data-transition-time=".3s" data-off-canvas>
    <div class="grid-x" id="canvas_panel">
        <div class="cell">
            <div class="grid-x">
                <div class="cell">
                    <br></br><!-- remove conflict with close button -->
                    <h1 id="modal_tile"></h1>
                    <h3><?php echo esc_html__( 'Population', 'zume' ) ?>: <span id="modal_population">0</span></h3>
                </div>
            </div>
            <hr>
        </div>
        <div class="cell" id="slider-content">
            <div class="grid-x grid-padding-x">
                <div class="cell medium-6">
                    <div class="grid-x">
                        <div class="cell">
                            <h3 style="text-transform: uppercase;"><?php echo esc_html__( 'PROGRESS BREAKDOWN', 'zume' ) ?></h3>
                        </div>
                        <div class="cell" id="progress-content">
                            <div class="grid-x">
                                <div class="cell"><hr></div>
                                <div class="cell world-list-wrapper temp-spinner" id="world-list-item"></div>
                                <div class="cell a0-list-wrapper temp-spinner" id="a0-list-item"></div>
                                <div class="cell a1-list-wrapper temp-spinner" id="a1-list-item"></div>
                                <div class="cell a2-list-wrapper temp-spinner" id="a2-list-item"></div>
                                <div class="cell a3-list-wrapper temp-spinner" id="a3-list-item"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cell medium-6">
                    <div class="grid-x">
                        <div class="cell">
                            <h3><?php echo esc_html__( 'ACTIVITY', 'zume' ) ?></h3>
                        </div>
                        <div class="cell"><hr></div>
                        <div class="cell" id="activity-content">
                            <span class="loading-spinner"></span>
                            <ul style="list-style-type: none;" id="activity-content-list"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
