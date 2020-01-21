<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-async-request.php';
require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-background-process.php';

class RecommenderBackgroundProductCopy extends RecommenderBackgroundProcess
{

    /**
     * @var string
     */
    protected $action = 'product_copy';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task($item)
    {
        // Actions to perform
        $product = wc_get_product($item);

        $gallery_image_ids = $product->get_gallery_image_ids();
        $gallery_images = array();
        foreach ($gallery_image_ids as $gallery_image_id) {
            $gallery_images[] = wp_get_attachment_url($gallery_image_id);
        }

        $properties = array(
            'title' => $product->get_title(),
            'status' => $product->get_status(),
            'date_created' => $product->get_date_created(),
            'date_modified' => $product->get_date_modified(),
            'featured' => $product->get_featured(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'sku' => $product->get_sku(),
            'catalog_visibility' => $product->get_catalog_visibility(),
            'price' => $product->get_price(),
            'date_on_sale_from' => $product->get_date_on_sale_from(),
            'date_on_sale_to' => $product->get_date_on_sale_to(),
            'total_sales' => $product->get_total_sales(),
            'tax_status' => $product->get_tax_status(),
            'stock_status' => $product->get_stock_status(),
            'weigth' => $product->get_weight(),
            'category_ids' => $product->get_category_ids(),
            'tag_ids' => $product->get_tag_ids(),
            'permalink' => $product->get_permalink(),
            'rating_count' => $product->get_rating_count(),
            'availability' => $product->get_availability()['availability'],
            'image' => wp_get_attachment_url($product->get_image_id()),
            'gallery_images' => $gallery_images,
        );

        $response = $this->client->sendItem(
            $item,
            $properties
        );

        // check the response
        if (is_wp_error($response)) {
            error_log(sprintf("[RECOMMENDER] --- Error adding product %s.", $item));
            error_log("[RECOMMENDER] --- " . $response->get_error_message());
            return $item;
        }
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code != 201) {
            $error_body = wp_remote_retrieve_body($response);
            error_log("[RECOMMENDER] --- Error adding a user.");
            error_log("[RECOMMENDER] --- ".$error_body);
            if ($status_code != 400 || $error_body != '{"error":"Item is duplicated."}') {
                error_log("[RECOMMENDER] --- Retring copy product ".$item);
                return $item;
            }
        }
        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        error_log("complete");
        parent::complete();
        // Show notice to user or perform some other arbitrary task...
    }
}
