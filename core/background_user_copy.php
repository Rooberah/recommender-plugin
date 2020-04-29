<?php

namespace Recommender;

//Security to limit direcct access to the plugin file
defined('ABSPATH') or die('No script kiddies please!');

require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-async-request.php';
require_once RECOMMENDER_PLUGIN_PATH . 'libraries/recommender-background-process.php';

class RecommenderBackgroundUserCopy extends RecommenderBackgroundProcess
{

    /**
     * @var string
     */
    protected $action = 'user_copy';

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
        $user = get_userdata($item);

        $properties = array(
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
        );

        $response = $this->client->sendUser(
            $item,
            $properties
        );

        // check the response
        if (is_wp_error($response)) {
            error_log("[RECOMMENDER] --- Error adding a user.");
            error_log("[RECOMMENDER] --- " . $response->get_error_message());
            return $item;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code != 201) {
            $error_body = wp_remote_retrieve_body($response);
            error_log("[RECOMMENDER] --- Error adding a user.");
            error_log("[RECOMMENDER] --- ".$error_body);
            if ($status_code != 400 || $error_body != '{"error":"User is duplicated."}') {
                error_log("[RECOMMENDER] --- Retring copy user ".$item);
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
        error_log("complete sending users.");
        parent::complete();
        // Show notice to user or perform some other arbitrary task...
    }
}
