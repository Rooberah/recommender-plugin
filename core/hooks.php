<?php

function recommender_activate()
{
    error_log("activate");
}

function recommender_deactivate()
{
    global $recommender;
    
    error_log("deactivate");

    $recommender->bg_user_copy->cancelProcess(true);
    $recommender->bg_order_item_copy->cancelProcess(true);
    $recommender->bg_product_copy->cancelProcess(true);

    global $wpdb;

    $plugin_options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'recommender_api_sent_%'");

    foreach ($plugin_options as $option) {
        delete_option($option->option_name);
    }
}
