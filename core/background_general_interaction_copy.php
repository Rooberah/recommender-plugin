<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-async-request.php';
require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-background-process.php';

class RecommenderBackgroundGeneralInteractionCopy extends RecommenderBackgroundProcess
{

    /**
     * @var string
     */
    protected $action = 'general_interaction_copy';

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
        $user_id = $item[0];
        $item_id = $item[1];
        $interaction_type = $item[2];
        $interaction_time = $item[3];

        $properties = array();

        $response = $this->client->sendInteraction($user_id, $item_id, $interaction_type, 1, $interaction_time, $properties);

        // check the response
        if (is_wp_error($response)) {
            error_log("[RECOMMENDER] --- Error adding an interaction.");
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
        error_log($this->identifier . " complete");
        parent::complete();
        // Show notice to user or perform some other arbitrary task...
    }
}
