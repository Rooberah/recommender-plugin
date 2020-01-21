<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-client.php';
require_once RECOMMENDER_PLUGIN_PATH.'core/background_user_copy.php';
require_once RECOMMENDER_PLUGIN_PATH.'core/background_order_item_copy.php';
require_once RECOMMENDER_PLUGIN_PATH.'core/background_product_copy.php';

class RecommenderPlugin
{
    public function __construct($options)
    {
        $this->options = $options;
        $this->options['post_type'] = 'product';

        $this->bg_user_copy = new RecommenderBackgroundUserCopy();
        $this->bg_order_item_copy = new RecommenderBackgroundOrderItemCopy();
        $this->bg_product_copy = new RecommenderBackgroundProductCopy();
    } // end of method -> __construct

    public function register()
    {
    }

    // Add all users using background processing
    public function addAllUsersBackground()
    {
        $args = array(
            'fields'      => 'ids',
        );

        $user_ids = get_users($args);

        // Array of WP_User objects.

        foreach ($user_ids as $id) {
            $this->bg_user_copy->pushToQueue($id);
        }

        $this->bg_user_copy->save()->dispatch();
    } //end-of-method add_all_users_background()
    public function addAllProductsBackground()
    {
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            error_log("Woocommerce is not installed");
            return;
        }
        $args = array(
            'limit' => '-1',
            'return' => 'ids'
        );
        $product_ids = wc_get_products($args);

        foreach ($product_ids as $id) {
            $this->bg_product_copy->pushToQueue($id);
        }

        $this->bg_product_copy->save()->dispatch();
    }

    public function addAllOrderItemsBackground()
    {
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            error_log("Woocommerce is not installed");
            return;
        }

        $query = new \WC_Order_Query(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        $orders = $query->get_orders();

        foreach ($orders as $order) {
            $items = $order->get_items();
            foreach ($items as $item) {
                $this->bg_order_item_copy->pushToQueue($item->get_id());
            }
        }
        $this->bg_order_item_copy->save()->dispatch();
    }

} // end of class --> Recomendo_Plugin
