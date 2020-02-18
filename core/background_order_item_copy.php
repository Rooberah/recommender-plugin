<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-async-request.php';
require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-background-process.php';

class RecommenderBackgroundOrderItemCopy extends RecommenderBackgroundProcess
{

    /**
     * @var string
     */
    protected $action = 'order_item_copy';

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
        $order_item = new \WC_Order_Item_Product($item);
        $order = $order_item->get_order();

        if ($order->get_status() != "completed") {
            return false;
        }

        // Send the order
        $user_id = $order->get_user_id();

        $properties = array(
            'billing_email'       => $order->get_billing_email(),
            'billing_first_name'  => $order->get_billing_first_name(),
            'billing_last_name'   => $order->get_billing_last_name(),
            'customer_ip_address' => $order->get_customer_ip_address(),
            'customer_user_agent' => $order->get_customer_user_agent(),
            'date_completed'      => $order->get_date_completed(),
            'date_paid'           => $order->get_date_paid()
        );

        $response = $this->client->sendInteraction($user_id, $order_item->get_product_id(), "purchase", $order_item->get_quantity(), $order->get_date_modified(), $properties);

        // check the response
        if (is_wp_error($response)) {
            error_log("[RECOMMENDER] --- Error adding an order.");
            error_log("[RECOMMENDER] --- " . $response->get_error_message());
            return $item;
        }
        
        if (wp_remote_retrieve_response_code($response) != 201) {
            error_log("[RECOMMENDER] --- Error adding an interaction.");
            error_log("[RECOMMENDER] --- ". wp_remote_retrieve_body($response));
            return $item;
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
