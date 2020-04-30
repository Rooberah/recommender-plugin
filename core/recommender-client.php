<?php
/**
 * Recommender client that talks with server
 *
 * Sends events & gets predictions.
 *
 * @category  Plugin
 * @package   Recommender
 * @author    Erfan Loghmani <erfan.loghmani@gmail.com>
 * @copyright 2019 Rahe Kaar
 * @license   GPL-3.0-or-later https://www.gnu.org/licenses/gpl-3.0.html
 * @version   GIT: 0.0
 * @link      http://napor.ir
 * @see       http://napor.ir
 * @since     0.0
 */

namespace Recommender;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class that handles all communications to Recomender servers
 *
 * @category Plugin
 * @package  Recommender
 * @author   Erfan Loghmani <erfan.loghmani@gmail.com>
 * @license  GPL-3.0-or-later https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://napor.ir
 * @since    0.0
 */
class RecommenderClient
{
    const DATE_TIME_FORMAT = \DateTime::ISO8601;
    const TIMEOUT = 3;
    const HTTPVERSION = '1.1';
    const CLIENT_DUPLICATE_STATUS = "CLIENT_DUPLICATE";
    //const EVENTS_URL = 'http://localhost:8000/recommender/api/core/api/v1/';
    const EVENTS_URL = 'https://pinkorblue.info/recommender/api/core/api/v1/';

    /**
     * Sets client basic information
     */
    public function __construct($options = null)
    {
        if (!$options) {
            $options = get_option('recommender_options');
        }

        $this->site_name = wp_parse_url(get_bloginfo('url'))['host'];
        $this->client_secret = $options && array_key_exists('recommender_api_client_secret', $options) ? $options['recommender_api_client_secret'] : '';
        $this->client_id = $options && array_key_exists('client_id', $options) ? $options['client_id'] : '';
        $this->client_secret = get_option(RecommenderPlugin::CLIENT_SECRET_OPTION);
        global $wp_version;
        $this->user_agent = $this->site_name . ' WordPress/' . $wp_version . ' - ' .
            'Recommender/' . RECOMMENDER_PLUGIN_VERSION;
    }

    /**
     * Get header
     *
     * @return array request header
     */
    public function getHeader($use_authorization=true)
    {
        $header = array(
            'Content-Type' => 'application/json',
            'User-Agent' => $this->user_agent,
            'Accept-Encoding' => 'gzip',
        );
        if ($use_authorization)
            $header['Authorization'] = "SECRET " . $this->site_name . "#" . $this->client_secret;

        return $header;
    }

    /**
     * Get client token empty for now
     *
     * @param boolean $client_id client id
     * @param boolean $client_secret client secret
     *
     * @return string                 result token
     */
    public function getToken($client_id, $client_secret)
    {
        return '';
    }

    /**
     * Gets event time in the proper format
     *
     * @param object $event_time the default event time
     *
     * @return object             result event time
     */
    public function getEventTime($event_time = null)
    {
        $result = $event_time;
        if (!isset($event_time)) {
            $result = (new \DateTime('NOW'))->format(self::DATE_TIME_FORMAT);
        }
        return $result;
    }

    public function getRecommendationsForUserProduct($uid, $pid, $num_products = 4)
    {
        $url = self::EVENTS_URL . 'recommend/recommend_to_user_on_item/';

        $response = wp_remote_post(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader(),
                'body' => json_encode(
                    [
                        'site_name' => $this->site_name,
                        'user_id' => $uid,
                        'item_id' => $pid,
                        'num_products' => $num_products
                    ]
                )
            )
        );
        if (is_wp_error($response)) {
            error_log("[RECOMMENDER] --- Error getting recommendations for user on product page.");
            error_log("[RECOMMENDER] --- " . $response->get_error_message());
            return array();
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code != 200) {
            $error_body = wp_remote_retrieve_body($response);
            error_log("[RECOMMENDER] --- Error getting recommendations for user on product page.");
            error_log("[RECOMMENDER] --- " . $error_body);
            return array();
        }
        return json_decode($response["body"], true)["recommendations"];
    }

    public function getRecommendationsForUser($uid, $num_products = 4)
    {
        $url = self::EVENTS_URL . 'recommend/recommend_to_user/';

        $response = wp_remote_post(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader(),
                'body' => json_encode(
                    [
                        'site_name' => $this->site_name,
                        'user_id' => $uid,
                        'num_products' => $num_products
                    ]
                )
            )
        );
        if (is_wp_error($response)) {
            error_log("[RECOMMENDER] --- Error getting recommendations for user.");
            error_log("[RECOMMENDER] --- " . $response->get_error_message());
            return array();
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code != 200) {
            $error_body = wp_remote_retrieve_body($response);
            error_log("[RECOMMENDER] --- Error getting recommendations for user.");
            error_log("[RECOMMENDER] --- " . $error_body);
            return array();
        }
        return json_decode($response["body"], true)["recommendations"];
    }

    public function getOverviewStatistics()
    {
        $url = self::EVENTS_URL . 'statistic/overview/?' . http_build_query(
                array("site_name" => $this->site_name)
            );
        $response = wp_remote_get(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader()
            )
        );
        if (is_wp_error($response)) {
            error_log("[RECOMMENDER] --- Error getting overview statistics.");
            error_log("[RECOMMENDER] --- " . $response->get_error_message());
            return array();
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code != 200) {
            $error_body = wp_remote_retrieve_body($response);
            error_log("[RECOMMENDER] --- Error getting overview statistics.");
            error_log("[RECOMMENDER] --- " . $error_body);
            return array();
        }
        return json_decode($response["body"], true);
    }

    /**
     * Set a user entity
     *
     * @param int|string $uid User Id
     * @param array $properties Properties of the user entity to set
     * @param string $event_time Time of the event in ISO 8601 format
     *                               (e.g. 2014-09-09T16:17:42.937-08:00).
     *                               Default is the current time.
     *
     * @return string JSON response
     */
    public function sendUser($uid, array $properties = array(), $event_time = null)
    {
        $event_time = $this->getEventTime($event_time);
        // casting to object so that an empty array would be represented as {}
        if (empty($properties)) {
            $properties = (object)$properties;
        }

        $url = self::EVENTS_URL . 'user/';
        $response = wp_remote_post(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader(),
                'body' => json_encode(
                    [
                        'site_name' => $this->site_name,
                        'user_id' => $uid,
                        'properties' => $properties,
                        'created_at' => $event_time,
                    ]
                )
            )
        );


        return $response;
    }

    public function sendItem($iid, array $properties = array(), $event_time = null)
    {
        $event_time = $this->getEventTime($event_time);
        // casting to object so that an empty array would be represented as {}
        if (empty($properties)) {
            $properties = (object)$properties;
        }

        $url = self::EVENTS_URL . 'item/';

        $response = wp_remote_post(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader(),
                'body' => json_encode(
                    [
                        'site_name' => $this->site_name,
                        'item_id' => $iid,
                        'properties' => $properties,
                        'created_at' => $event_time,
                    ]
                )
            )
        );

        return $response;
    }

    public function sendClientSecret($secret)
    {
        $url = self::EVENTS_URL . 'client/';

        $response = wp_remote_post(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader(false),
                'body' => json_encode(
                    [
                        'site_name' => $this->site_name,
                        'client_secret' => $secret
                    ]
                )
            )
        );
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code != 201 && $body['status'] != self::CLIENT_DUPLICATE_STATUS) {
            $error_body = wp_remote_retrieve_body($response);
            error_log("[RECOMMENDER] --- Sending client secret.");
            error_log("[RECOMMENDER] --- " . $error_body);
            return false;
        }

        return true;
    }

    public function sendInteraction($user_id, $item_id, $interaction_type, $interaction_value, $interaction_time, $interaction_id = null, array $properties = array(), array $user_features = array(), array $item_features = array(), $event_time = null)
    {
        $event_time = $this->getEventTime($event_time);
        // casting to object so that an empty array would be represented as {}
        if (empty($properties)) {
            $properties = (object)$properties;
        }
        $interactionId = $interaction_id;
        if (!$interactionId) {
            $character_pool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@$%^&*()_=~`:;./\\'\"[]{}|";
            $interactionId = "";
            for ($j = 0; $j < 32; $j++) {
                $interactionId .= $character_pool[rand(0, strlen($character_pool) - 1)];
            }
        }

        $url = self::EVENTS_URL . 'interaction/';
        $response = wp_remote_post(
            $url,
            array(
                'timeout' => self::TIMEOUT,
                'httpversion' => self::HTTPVERSION,
                'headers' => $this->getHeader(),
                'body' => json_encode(
                    [
                        'site_name' => $this->site_name,
                        'item_id' => $item_id,
                        'user_id' => $user_id,
                        'interaction_type' => $interaction_type,
                        'interaction_value' => $interaction_value,
                        'interaction_time' => (string)$interaction_time,
                        'user_features' => $user_features,
                        'item_features' => $item_features,
                        'properties' => $properties,
                        'created_at' => $event_time,
                        'interaction_id' => $interactionId
                    ]
                )
            )
        );

        return $response;
    }
} // end of class Recomendo_Client
