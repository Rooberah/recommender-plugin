<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-client.php';

class RecommenderAdmin
{
    public static $SETTINGS_PAGE_NAME = RECOMMENDER_PLUGIN_PREFIX . "_settings";

    public function __construct()
    {
        add_action('init', array(&$this, 'registerUserRecommendationsBlock'));

        add_action('admin_menu', array(&$this, 'createMenus'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueueScripts'));

        add_action('rest_api_init', array(&$this, 'restApisRegisteration'));

        $this->client = new RecommenderClient();
    }

    private function getRedirectToSettingsResponse()
    {
        $response = new \WP_REST_Response();
        $response->set_status(302);
        $response->header('Location', '/wp-admin/admin.php?page=' . RecommenderAdmin::$SETTINGS_PAGE_NAME);
        return $response;
    }

    public function restApisRegisteration()
    {
        $can_edit_others = function () {
            return current_user_can('edit_others_posts');
        };
        register_rest_route(RECOMMENDER_PLUGIN_PREFIX.'/v1', '/settings', array(
            'methods' => 'POST',
            'permission_callback' => $can_edit_others,
            'callback' => array($this, 'changeSettings')
        ));
    }

    public function changeSettings($request)
    {
        $data = $request->get_body_params();
        update_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_OPTION_NAME, isset($data["related"]));
        update_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_SECTION_CLASS_OPTION_NAME, $data["rel_section_class"]);
        return $this->getRedirectToSettingsResponse();
    }

    public function registerUserRecommendationsBlock()
    {
        $args = array(
            'limit' => '5',
            'return' => 'ids',
            'offset' => '0'
        );
        $product_ids = wc_get_products($args);
        $products = array();
        $i = 0;
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            $products[$i++] = array(
                "name" => $product->get_data()['name'],
                "id" => $product->get_data()['id'],
                "image_url" => wp_get_attachment_image_url($product->get_image_id(), 'full'),
                "sale_price" => $product->get_data()['sale_price'],
                "regular_price" => $product->get_data()['regular_price'],
                "price" => $product->get_data()['price'],
            );
        }
        wp_enqueue_style('block-style', RecommenderPlugin::$STATIC_FILES_URL."blockStyle.css");
        wp_register_script(
            'gutenberg-user-recommendation-block',
            RecommenderPlugin::$STATIC_FILES_URL."block.js",
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor',
            )
        );

        wp_localize_script(
            'gutenberg-user-recommendation-block',
            "data",
            array(
                "products"=>$products,
                "msg"=>esc_html__('Note: The items shown in this block are personalized. It means each person would see a unique content on this block.', 'robera-recommender')
                )
        );

        wp_set_script_translations('gutenberg-user-recommendation-block', 'robera-recommender', plugin_dir_path(RECOMMENDER_PLUGIN_FILE_PATH) . 'languages');

        register_block_type('recommender/user-recommendation', array(
                'editor_script' => 'gutenberg-user-recommendation-block',
        ));
    }

    public function createMenus()
    {

        add_menu_page(esc_html__('Robera', 'robera-recommender'), esc_html__('Robera', 'robera-recommender'), 'manage_options', RecommenderAdmin::$SETTINGS_PAGE_NAME, array($this, 'settingsPage'), plugins_url('static/RooBeRah_Logo_Gray_2.svg', __FILE__), 6);

        // add_submenu_page(RecommenderAdmin::$SETTINGS_PAGE_NAME, esc_html__('Settings', 'robera-recommender'), esc_html__('Settings', "robera-recommender"), 'manage_options', RecommenderAdmin::$SETTINGS_PAGE_NAME, array($this, 'settingsPage'));
    }

    public function settingsPage()
    {
        $data = $this->client->getOverviewStatistics();
        require_once('templates/settings-page.php');
    }

    public function enqueueScripts($hook_suffix)
    {
        if (strpos($hook_suffix, RECOMMENDER_PLUGIN_PREFIX) !== false) {
            wp_register_script('jquery3.1.1', RecommenderPlugin::$STATIC_FILES_URL.'jquery.min.js', array(), null, false);
            wp_add_inline_script('jquery3.1.1', 'var jQuery3_1_1 = $.noConflict(true);');
            wp_enqueue_style('robera-style', plugins_url("static/robera-styles.css", __FILE__));
            if (is_rtl()) {
                wp_enqueue_style('semantic-style-rtl', RecommenderPlugin::$STATIC_FILES_URL."semantic.rtl.min.css");
            } else {
                wp_enqueue_style('semantic-style', RecommenderPlugin::$STATIC_FILES_URL."semantic.min.css");
            }
            wp_enqueue_script('semantic-js', RecommenderPlugin::$STATIC_FILES_URL."semantic.min.js", array('jquery3.1.1'));
        }
    }
}
