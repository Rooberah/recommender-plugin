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
    public static $RECOMMEND_ON_RELATED_PRODUCTS_OPTION_NAME = RECOMMENDER_PLUGIN_PREFIX . "recommend_on_related";

    public function __construct($options)
    {
        $this->client = new RecommenderClient($options);

        add_action('plugins_loaded', array(&$this, 'loadTranslationFiles'));

        $this->options = $options;
        $this->options['post_type'] = 'product';

        $this->bg_user_copy = new RecommenderBackgroundUserCopy();
        $this->bg_order_item_copy = new RecommenderBackgroundOrderItemCopy();
        $this->bg_product_copy = new RecommenderBackgroundProductCopy();

        add_action('admin_init', array(&$this, 'sendData'));
        add_action('wp_enqueue_scripts', array(&$this, 'enqueueScripts'));

        register_activation_hook(RECOMMENDER_PLUGIN_FILE_PATH, array($this, 'recommenderActivate'));
        register_deactivation_hook(RECOMMENDER_PLUGIN_FILE_PATH, array($this, 'recommenderDeactivate'));
        register_uninstall_hook(RECOMMENDER_PLUGIN_FILE_PATH, array('\Recommender\RecommenderPlugin', 'uninstall'));
    } // end of method -> __construct

    public function enqueueScripts()
    {
        wp_register_script('jquery3.1.1', plugins_url('static/jquery.min.js', __FILE__), array(), null, false);
        wp_add_inline_script('jquery3.1.1', 'var jQuery3_1_1 = $.noConflict(true);');
        
        wp_enqueue_script(RECOMMENDER_PLUGIN_PREFIX.'recommender-js', plugins_url('static/recommender.js', __FILE__), array('jquery3.1.1'), null, false);

        $product_id = null;
        if (is_product()) {
            global $product;
            if (!is_object($product)) {
                $product = wc_get_product(get_the_ID());
            }
            $product_id = $product->get_id();
        }

        wp_localize_script(
            RECOMMENDER_PLUGIN_PREFIX.'recommender-js',
            'recommender_info',
            array(
                'click_event_url' => RecommenderClient::EVENTS_URL."event/click/",
                'site_name' => wp_parse_url(get_bloginfo('url'))['host'],
                'user_id' => get_current_user_id(),
                'is_product' => is_product(),
                'product_id' => $product_id
            )
        );
    }

    public function loadTranslationFiles()
    {
        load_plugin_textdomain('robera-recommender', false, basename(RECOMMENDER_PLUGIN_PATH) . '/languages/');
    }

    public function registerUserRecommendationsBlock()
    {
        // automatically load dependencies and version
        // $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');

        wp_register_script(
            'gutenberg-user-recommendation-block',
            plugins_url('static/block.js', __FILE__),
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor',
            )
        );

        wp_set_script_translations('gutenberg-user-recommendation-block', 'robera-recommender', plugin_dir_path(RECOMMENDER_PLUGIN_FILE_PATH) . 'languages');

        register_block_type('recommender/user-recommendation', array(
                'editor_script' => 'gutenberg-user-recommendation-block',
        ));
    }

    public function register()
    {
    }

    public function trySendingOnce($action, $func)
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

    public function sendData()
    {
        $this->trySendingOnce("users", [&$this, "addAllUsersBackground"]);
        $this->trySendingOnce("products", [&$this, "addAllProductsBackground"]);
        $this->trySendingOnce("orders", [&$this, "addAllOrderItemsBackground"]);
    }

    public function recommenderActivate()
    {
        error_log("activate");
    }

    public function recommenderDeactivate()
    {
        
        error_log("deactivate");

        $this->bg_user_copy->cancelProcess(true);
        $this->bg_order_item_copy->cancelProcess(true);
        $this->bg_product_copy->cancelProcess(true);
    }

    public static function uninstall()
    {
        global $wpdb;

        $plugin_options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'recommender_api_sent_%'");

        foreach ($plugin_options as $option) {
            delete_option($option->option_name);
        }
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
