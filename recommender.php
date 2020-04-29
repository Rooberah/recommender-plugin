<?php
/**
 * Plugin Name
 *
 * @package   Recommender
 * @author    Erfan Loghmani
 * @copyright 2019 Rooberah
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       recommender
 * Description:       This Plugins recommends stuff to your users
 * Text Domain:       robera-recommender
 * Version:           0.2.0
 */

namespace Recommender;

if (!defined('RECOMMENDER_PLUGIN_PATH')) {
    define('RECOMMENDER_PLUGIN_PATH', dirname(__FILE__).'/');
    define('RECOMMENDER_PLUGIN_FILE_PATH', __FILE__);
    define('RECOMMENDER_PLUGIN_VERSION', '0.2.0');
    define('RECOMMENDER_PLUGIN_PREFIX', 'recommender');
}


require_once __DIR__ . '/vendor/autoload.php';

require 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/Rooberah/recommender-plugin',
    __FILE__,
    'recommender-plugin'
);

require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-plugin.php';
require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-core.php';
require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-admin.php';

if (class_exists('\Recommender\RecommenderPlugin') && !isset($TESTING)) {
    $options = get_option('recommender_options');
    $recommender = new RecommenderPlugin($options);
    $core = new RecommenderCore();
    $admin = new RecommenderAdmin();
}
