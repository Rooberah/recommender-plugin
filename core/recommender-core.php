<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH.'core/recommender-client.php';

class RecommenderCore
{

    public function __construct()
    {
        $this->client = new RecommenderClient();

        add_shortcode('user_recommendations', array(&$this, 'userRecommendations'));

        add_filter('woocommerce_related_products', array($this, 'getRelatedIds'), 100, 3);
    }

    public function getRelatedIds($related_products, $product_id, $args)
    {
        if (!get_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_OPTION_NAME)) {
            return $related_products;
        }

        $recommendation_ids = $this->client->getRecommendationsForUserProduct(
            get_current_user_id(),
            $product_id,
            $args["limit"]
        );
        if ($recommendation_ids) {
            return $recommendation_ids;
        }
        return $related_products;
    }

    public function userRecommendations($atts = array())
    {
        if (is_admin() || strpos($_SERVER['HTTP_REFERER'], 'action=edit') !== false)
            return 0;
        $num_products = 4;
        if (is_array($atts) && array_key_exists('columns', $atts)) {
            $num_products = $atts['columns'];
            $atts['limit'] = $atts['columns'];
        }
        $ids = $this->client->getRecommendationsForUser(get_current_user_id(), $num_products);
        // set up default parameters
        $atts = array_merge(array(
            'limit'        => '4',
            'columns'      => '4',
            'ids'          => join(",", $ids),
            'class'        => 'recommender-block-class'
        ), (array) $atts);

        $shortcode = new \WC_Shortcode_Products($atts, 'user_recommendations');
        return $shortcode->get_content();
    }
}
