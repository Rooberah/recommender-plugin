<?php
/**
 * Class RecommenderTest
 *
 * @package Recommender
 */

namespace Recommender;

class IsGoodProductRequest extends \PHPUnit\Framework\Constraint\Constraint
{
    private $id;

    public function __construct($item_id, $item_image)
    {
        parent::__construct();
        $this->item_id = $item_id;
        $this->item_image = $item_image;
        error_log($item_id . ' ' . $item_image);
    }

    public function matches($other): bool
    {
        $body = json_decode($other['body'], true);
        if ($body['item_id'] != $this->item_id || $body["properties"]['image'] != $this->item_image) {
            return false;
        }
        return true;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is product request ok';
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return 0;
    }
}

class IsGoodRecommendationProductRequest extends \PHPUnit\Framework\Constraint\Constraint
{
    private $id;

    public function __construct($user_id, $item_id, $num_products)
    {
        parent::__construct();
        $this->user_id = $user_id;
        $this->item_id = $item_id;
        $this->num_products = $num_products;
    }

    public function matches($other): bool
    {
        $body = json_decode($other['body'], true);
        if ($body['user_id'] != $this->user_id || $body['item_id'] != $this->item_id ||
                $body['num_products'] != $this->num_products) {
            return false;
        }
        return true;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is product request ok';
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return 0;
    }
}
/**
 * Constraint that accepts any input value.
 */

class IsGoodRequest extends \PHPUnit\Framework\Constraint\Constraint
{
    private $id;

    public function __construct($user_id, $item_id)
    {
        parent::__construct();
        $this->user_id = $user_id;
        $this->item_id = $item_id;
    }

    public function matches($other): bool
    {
        $body = json_decode($other['body'], true);
        if ($body['user_id'] != $this->user_id || $body['item_id'] != $this->item_id) {
            return false;
        }
        return true;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is request ok';
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return 0;
    }
}


/**
 * Recommender test case.
 */
class RecommenderTest extends \WP_UnitTestCase
{
    use \phpmock\phpunit\PHPMock;

    private function addUsers(&$bg_user_copy)
    {
        $args = array(
            'fields'      => 'ids',
        );

        $user_ids = get_users($args);

        foreach ($user_ids as $id) {
            $bg_user_copy->pushToQueue($id);
        }
    }

    private function initWoocommerce()
    {
        activate_plugin('woocommerce/woocommerce.php');
        WC()->init();
        do_action('woocommerce_after_register_post_type');
    }

    private function createProduct()
    {
        $product = new \WC_Product();

        $product->set_name('test_product');
        $product->set_status('publish');
        $product->set_short_description('test_short_desc');
        $product->set_description('test_desc');
        // Attempts to create the new product.
        $product->save();
        
        // Set attachment data
        $filename = 'images/product-image.jpg';
        $attachment = array(
            'post_mime_type' => 'jpg',
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit'
        );
        // Create the attachment
        $attach_id = wp_insert_attachment($attachment, wp_upload_dir()["path"] . '/' . $filename, $product->get_id());

        $product->set_image_id($attach_id);
        $product->save();

        return $product;
    }

    private function createOrder()
    {
        $product = new \WC_Product();

        $product->set_name('test_product');
        $product->set_status('publish');
        $product->set_short_description('test_short_desc');
        $product->set_description('test_desc');
        // Attempts to create the new product.
        $product->save();
        $order = new \WC_Order();
        $order->set_status("completed");
        $order->save();

        $ctp = new \WC_Order_Data_Store_CPT();
        $ctp->create($order);

        $order_item = new \WC_Order_Item_Product();
        $order_item->set_product($product);

        $order_item->set_order_id($order->get_id());
        $order_item->set_backorder_meta();
        $order_item->save();
        $order->add_item($order_item);
        $order->save();
        return $order;
    }

    public function testGetRecommendationsOnProduct()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(array(
            "body" => "{\"recommendations\": [33, 31, 32]}",
            'response' => array(
                'code' => 200
            )
        ));

        $client = new RecommenderClient();

        try {
            $res = $client->getRecommendationsForUserProduct(0, "32", 3);
            $this->assertEquals(count($res), 3);
        } catch (\WPDieException $e) {
        }
    }

    public function testGetRelatedIds()
    {
        $item_id = "32";
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->with(
            $this->anything(),
            new IsGoodRecommendationProductRequest(0, $item_id, 3)
        )->willReturn(array(
            "body" => "{\"recommendations\": [\"33\", \"31\", \"32\"]}",
            'response' => array(
                'code' => 200
            )
        ));

        update_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_OPTION_NAME, true);

        $core = new RecommenderCore();

        try {
            $res = $core->getRelatedIds(["31", "34", "36"], $item_id, array(
                "limit" => 3
            ));
            $this->assertEquals(count($res), 3);
            $this->assertEquals($res, ["33", "31", "32"]);
        } catch (\WPDieException $e) {
        }
    }

    public function testGetRelatedIdsNotSet()
    {
        $item_id = "32";
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->never());

        $core = new RecommenderCore();

        try {
            $related_passed = ["31", "34", "36"];
            $res = $core->getRelatedIds($related_passed, $item_id, array(
                "limit" => 3
            ));
            $this->assertEquals(count($res), 3);
            $this->assertEquals($res, $related_passed);
        } catch (\WPDieException $e) {
        }
    }

    public function testGetRecommendationsOnProductError()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            new \WP_Error()
        );

        $client = new RecommenderClient();

        try {
            $res = $client->getRecommendationsForUserProduct(0, "32", 3);
            $this->assertEquals(count($res), 0);
        } catch (\WPDieException $e) {
        }
    }

    public function testEventSchedules()
    {
        $options = get_option('recommender_options');
        $recommender = new RecommenderPlugin($options);

        $time = $this->getFunctionMock(__NAMESPACE__, "time");
        $time->expects($this->any())->willReturn(3);

        $schedule_event = $this->getFunctionMock(__NAMESPACE__, "wp_schedule_event");
        $schedule_event->expects($this->once())
                       ->with(3, $this->anything(), $this->anything());

        $recommender->addAllUsersBackground();
    }

    public function testOrdersEventSchedulesWithoutWoocommerce()
    {
        $options = get_option('recommender_options');
        $recommender = new RecommenderPlugin($options);

        $time = $this->getFunctionMock(__NAMESPACE__, "in_array");
        $time->expects($this->any())->willReturn(false);

        $schedule_event = $this->getFunctionMock(__NAMESPACE__, "wp_schedule_event");
        $schedule_event->expects($this->never());

        $recommender->addAllOrderItemsBackground();
    }

    public function testOrdersEventSchedulesOK()
    {
        $this->initWoocommerce();

        $options = get_option('recommender_options');
        $recommender = new RecommenderPlugin($options);

        $schedule_event = $this->getFunctionMock(__NAMESPACE__, "wp_schedule_event");
        $schedule_event->expects($this->once());

        $recommender->addAllOrderItemsBackground();
    }

    public function testOrderCopyHandle()
    {
        $this->initWoocommerce();

        $client_mock = $this->getMockBuilder(RecommenderClient::class)
                            ->setMethods(['sendInteraction'])
                            ->getMock();

        $client_mock->expects($this->once())
                    ->method('sendInteraction');


        $bg_order_item_copy = new RecommenderBackgroundOrderItemCopy();

        $bg_order_item_copy->client = $client_mock;

        $order = $this->createOrder();
        $order_item_id = array_values($order->get_items())[0]->get_id();
        $bg_order_item_copy->pushToQueue("$order_item_id");
        try {
            $bg_order_item_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testOrderCopyHandleMockRequest()
    {
        $this->initWoocommerce();

        $bg_order_item_copy = new RecommenderBackgroundOrderItemCopy();

        $order = $this->createOrder();
        $user_id = $order->get_user_id();
        $item_id = array_values($order->get_items())[0]->get_product_id();
        $order_item_id = array_values($order->get_items())[0]->get_id();

        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->with($this->anything(), new IsGoodRequest($user_id, $item_id));

        $bg_order_item_copy->pushToQueue("$order_item_id");
        try {
            $bg_order_item_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testUserCopyHandle()
    {
        $client_mock = $this->getMockBuilder(RecommenderClient::class)
                            ->setMethods(['sendUser'])
                            ->getMock();

        $client_mock->expects($this->once())
                    ->method('sendUser');

        $bg_user_copy = new RecommenderBackgroundUserCopy();

        $bg_user_copy->client = $client_mock;

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testGetRecommendations()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(array(
            "body" => "{\"recommendations\": [33, 31, 32, 34]}",
            'response' => array(
                'code' => 200
            )
        ));

        $client = new RecommenderClient();

        try {
            $res = $client->getRecommendationsForUser(0, 4);
            $this->assertEquals(count($res), 4);
        } catch (\WPDieException $e) {
        }
    }

    public function testGetRecommendationsError()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            new \WP_Error()
        );

        $client = new RecommenderClient();

        try {
            $res = $client->getRecommendationsForUser(0, 4);
            $this->assertEquals(count($res), 0);
        } catch (\WPDieException $e) {
        }
    }

    public function testUserCopyHandleMockRequest()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once());

        $bg_user_copy = new RecommenderBackgroundUserCopy();

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testUserCopyHandleMockRequestWPError()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            new \WP_Error()
        );

        $background_user_mock = $this->getMockBuilder(RecommenderBackgroundUserCopy::class)
                            ->setMethods(['complete'])
                            ->getMock();

        $background_user_mock->expects($this->never())
                             ->method('complete');

        $bg_user_copy = $background_user_mock;

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testUserCopyHandleMockRequestError()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            array(
                'response' => array(
                    'code' => 400
                )
            )
        );


        $background_user_mock = $this->getMockBuilder(RecommenderBackgroundUserCopy::class)
                            ->setMethods(['complete'])
                            ->getMock();

        $background_user_mock->expects($this->never())
                             ->method('complete');

        $bg_user_copy = $background_user_mock;

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testUserCopyHandleMockRequestOk()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            array(
                'response' => array(
                    'code' => 201
                )
            )
        );

        $background_user_mock = $this->getMockBuilder(RecommenderBackgroundUserCopy::class)
                            ->setMethods(['complete'])
                            ->getMock();

        $background_user_mock->expects($this->once())
                             ->method('complete');

        $bg_user_copy = $background_user_mock;

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }


    public function testUserCopyHandleMockRequestOkNoMock()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            array(
                'response' => array(
                    'code' => 201
                )
            )
        );

        $bg_user_copy = new RecommenderBackgroundUserCopy();

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testUserCopyHandleMockRequestDuplicate()
    {
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            array(
                'body'     => '{"error":"User is duplicated."}',
                'response' => array(
                    'code' => 400,
                )
            )
        );

        $background_user_mock = $this->getMockBuilder(RecommenderBackgroundUserCopy::class)
                            ->setMethods(['complete'])
                            ->getMock();

        $background_user_mock->expects($this->once())
                             ->method('complete');

        $bg_user_copy = $background_user_mock;

        $this->addUsers($bg_user_copy);

        try {
            $bg_user_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testProductEventSchedulesWithoutWoocommerce()
    {
        $options = get_option('recommender_options');
        $recommender = new RecommenderPlugin($options);

        $time = $this->getFunctionMock(__NAMESPACE__, "in_array");
        $time->expects($this->any())->willReturn(false);

        $schedule_event = $this->getFunctionMock(__NAMESPACE__, "wp_schedule_event");
        $schedule_event->expects($this->never());

        $recommender->addAllProductsBackground();
    }

    public function testProductEventSchedulesOK()
    {
        $this->initWoocommerce();

        $options = get_option('recommender_options');
        $recommender = new RecommenderPlugin($options);

        $schedule_event = $this->getFunctionMock(__NAMESPACE__, "wp_schedule_event");
        $schedule_event->expects($this->once());

        $recommender->addAllProductsBackground();
    }

    public function testProductCopyHandle()
    {
        $this->initWoocommerce();

        $client_mock = $this->getMockBuilder(RecommenderClient::class)
                            ->setMethods(['sendItem'])
                            ->getMock();

        $client_mock->expects($this->once())
                    ->method('sendItem');


        $bg_product_copy = new RecommenderBackgroundProductCopy();

        $bg_product_copy->client = $client_mock;

        $product = $this->createProduct();

        $bg_product_copy->pushToQueue(sprintf("%s", $product->get_id()));
        try {
            $bg_product_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testProductCopyHandleMockRequest()
    {
        $this->initWoocommerce();

        $bg_product_copy = new RecommenderBackgroundProductCopy();

        $product = $this->createProduct();

        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->
            with($this->anything(), new IsGoodProductRequest($product->get_id(), wp_get_attachment_url($product->get_image_id())));

        $bg_product_copy->pushToQueue(sprintf("%s", $product->get_id()));
        try {
            $bg_product_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }

    public function testProductCopyHandleMockRequestDuplicate()
    {
        $this->initWoocommerce();
        
        $request_mock = $this->getFunctionMock(__NAMESPACE__, "wp_remote_post");
        $request_mock->expects($this->once())->willReturn(
            array(
                'body'     => '{"error":"Item is duplicated."}',
                'response' => array(
                    'code' => 400,
                )
            )
        );

        $background_product_mock = $this->getMockBuilder(RecommenderBackgroundProductCopy::class)
                            ->setMethods(['complete'])
                            ->getMock();

        $background_product_mock->expects($this->once())
                             ->method('complete');

        $bg_product_copy = $background_product_mock;

        $product = $this->createProduct();

        $bg_product_copy->pushToQueue(sprintf("%s", $product->get_id()));

        try {
            $bg_product_copy->save()->handleCronHealthcheck();
        } catch (\WPDieException $e) {
        }
    }
}
