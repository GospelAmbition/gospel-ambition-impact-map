<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'gospel-ambition-impact-map/gospel-ambition-impact-map.php' );

        $this->assertContains(
            'gospel-ambition-impact-map/gospel-ambition-impact-map.php',
            get_option( 'active_plugins' )
        );
    }
}
