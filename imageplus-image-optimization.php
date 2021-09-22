<?php
/**
 * Plugin Name:       Imageplus Image Optimization
 * Description:       Attempts to optimize images
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Harry Hindson <Imageplus>
 */

define('IMAGE_OPTIMIZATION_ENTRY', dirname(__FILE__));

use IPIO\Classes\OptimizeImages;
use IPIO\Classes\BulkOptimizer;

require IMAGE_OPTIMIZATION_ENTRY . '/vendor/autoload.php';

add_action('plugins_loaded', 'init');

function init(){
    OptimizeImages::addFilters();

    BulkOptimizer::initialise();
}