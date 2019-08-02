<?php

function pr( $val ) {
    echo '<pre>';
    print_r( $val );
    echo '</pre>';
}

function pre( $val ) {
    echo '<pre>';
    print_r( $val );
    echo '</pre>';
    exit;
}

function VikCalPath() {
    return plugin_dir_url( __FILE__ );
}

function VikCalUrl() {
    return plugin_dir_path( __FILE__ );
}