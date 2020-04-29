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
        return $this->getRedirectToSettingsResponse();
    }

    public function registerUserRecommendationsBlock()
    {
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

    public function createMenus()
    {
        add_menu_page(esc_html__('Robera Recommender', 'robera-recommender'), esc_html__('Robera Recommender', 'robera-recommender'), 'manage_options', RecommenderAdmin::$SETTINGS_PAGE_NAME, array($this, 'settingsPage'), 'dashicons-smiley', 6);

        // add_submenu_page(RecommenderAdmin::$SETTINGS_PAGE_NAME, esc_html__('Settings', 'robera-recommender'), esc_html__('Settings', "robera-recommender"), 'manage_options', RecommenderAdmin::$SETTINGS_PAGE_NAME, array($this, 'settingsPage'));
    }

    public function settingsPage()
    {
        $data = $this->client->getOverviewStatistics();
        $num_recommendations = $data["num_recommendations"];
        $num_clicks = $data["num_clicks"];
        $num_bought = $data["num_bought"];
        $sum_bought_value = $data["sum_bought_value"];
        require_once('templates/settings-page.php');
    }

    public function enqueueScripts($hook_suffix)
    {
        if (strpos($hook_suffix, RECOMMENDER_PLUGIN_PREFIX) !== false) {
            wp_register_script('jquery3.1.1', plugins_url('static/jquery.min.js', __FILE__), array(), null, false);
            wp_add_inline_script('jquery3.1.1', 'var jQuery3_1_1 = $.noConflict(true);');
            if (is_rtl()) {
                wp_enqueue_style('semantic-style-rtl', plugins_url('static/semantic.rtl.min.css', __FILE__));
            } else {
                wp_enqueue_style('semantic-style', plugins_url('static/semantic.min.css', __FILE__));
            }
            wp_enqueue_script('semantic-js', plugins_url('static/semantic.min.js', __FILE__), array('jquery3.1.1'));
        }
    }
}
