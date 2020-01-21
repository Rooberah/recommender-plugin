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
 * Version:           0.1.1
 */

namespace Recommender;

if (!defined('RECOMMENDER_PLUGIN_PATH')) {
    define('RECOMMENDER_PLUGIN_PATH', dirname(__FILE__).'/');
    define('RECOMMENDER_PLUGIN_VERSION', '0.1.1');
}


require 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/Rooberah/recommender-plugin',
    __FILE__,
    'recommender-plugin'
);

require_once RECOMMENDER_PLUGIN_PATH.'core/hooks.php';
require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-plugin.php';

function trySendingOnce($action, $func)
{
    $sent_option_key = 'recommender_api_sent_' . $action;
    if (! get_option($sent_option_key)) {
        update_option($sent_option_key, true);
        try {
            $func();
        } catch (\Exception $e) {
            error_log($e);
            delete_option($sent_option_key);
        }
    }
}

if (class_exists('\Recommender\RecommenderPlugin')) {
    $options = get_option('recommender_options');
    $recommender = new RecommenderPlugin($options);

    add_action('admin_init', function () {
        global $recommender;

        trySendingOnce("users", [$recommender, "addAllUsersBackground"]);
        trySendingOnce("products", [$recommender, "addAllProductsBackground"]);
        trySendingOnce("orders", [$recommender, "addAllOrderItemsBackground"]);
    });
}

register_activation_hook(__FILE__, 'recommender_activate');
register_deactivation_hook(__FILE__, 'recommender_deactivate');
