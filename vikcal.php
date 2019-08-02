<?php

/**
 *
 * @link              TBA
 * @since             1.0.0
 *
 * Plugin Name: VikCal
 * Plugin URI: TBA
 * Description: Simple event calendar for WordPress
 * Version: 1.0.0
 * Author: Slawek Sulkowski
 * Author URI: TBA
 * License: GPL2
 * Text Domain: vikcal
 */

if( !defined( 'ABSPATH' ) ) {
    die();
}

require 'vendor/autoload.php';

use VikCal\Main;

$VikCal = Main::init();