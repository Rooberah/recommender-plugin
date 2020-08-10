<?php
namespace Recommender;
$translation = [
    "receiving data"=>esc_html__("Receiving Data", "robera-recommender"),
    "training"=>esc_html__("Training the Engine", "robera-recommender"),
    "trained"=>esc_html__("Ready", "robera-recommender")
];
$tooltip_translation = [
    "receiving data"=>esc_html__("We are receiving your data and will work on them ASAP.", "robera-recommender"),
    "training"=>esc_html__("Data received. We are studying customers’ behavior based on your data.", "robera-recommender"),
    "trained"=>esc_html__("Robera is at your service. Enjoy our recommendation system.", "robera-recommender")
];
$statistics = [
    "on_item"=>esc_html__("Related Items Recommendation's Statistics", "robera-recommender"),
    "on_user"=>esc_html__("Recommendations on user Statistics", "robera-recommender")
];

$rec_tooltip = [
    "on_item"=>esc_html__("Number of related products recommendation blocks shown to users.", "robera-recommender"),
    "on_user"=>esc_html__("Number of recommendations on user block shown to users", "robera-recommender")
];
?>
<div class="semantic" style="font-family: rbr-font-family">
    <div class="ui container" style="margin-top: 1rem">
        <div class="ui segment">
            <!div class="ui segment" style="background-color: #F2F2F2; width: max-content">
                <div class="ui steps">
                    <div class="active step">
                        <?php if (empty($data)): ?>
                            <img src="<?php echo plugins_url('../static/not-connected.png', __FILE__) ?>" width=50em style="margin: 0.5em"></img>
                        <?php else: ?>
                            <img src="<?php echo plugins_url('../static/RooBeRah_Logo-04.svg', __FILE__) ?>" width=50em style="margin: 0.5em"></img>
                        <?php endif; ?>
                        <div class="content">
                            <div id="robera-desc" class="description"><?php esc_html_e("Robera's State", "robera-recommender") ?></div>
                            <?php if (empty($data)): ?>
                                <div class="title robera-tooltip" style="font-family: rbr-font-family;" data-tooltip="Refresh the page to try again."><?php echo esc_html_e("Connection to Robera failed", "robera-recommender") ?></div>
                            <?php elseif ($data["state"]=="trained"): ?>
                                <div class="title robera-tooltip" style="color: #21ba45;font-family: rbr-font-family;" data-tooltip="<?php echo $tooltip_translation[$data["state"]] ?>" ><?php echo $translation[$data["state"]] ?></div>
                            <?php else: ?>
                                <div class="title robera-tooltip" style="font-family: rbr-font-family;" data-tooltip="<?php echo $tooltip_translation[$data["state"]] ?>"><?php echo $translation[$data["state"]] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <!/div>
            <?php foreach($statistics as $key=>$value): ?>
                <h2 class="ui header" style="font-family: rbr-font-family">
                    <?php echo $value?>
                </h2>
                <div class="ui steps" data-tooltip="<?php echo $rec_tooltip[$key]; ?>">
                    <div class="step" style="width: 16em">
                        <div class="title"><?php echo empty($data) ? "..." : intval($data[$key]["num_recommendations"] ) ?></div>
                        <div class="description" ><?php esc_html_e("Recommendation blocks", "robera-recommender") ?></div>
                    </div>
                </div>
                <div class="ui steps" data-tooltip="<?php esc_html_e("Number of Clicks on the items of the Recommendation Block. Click on any item of the block will count.","robera-recommender")?>">
                    <div class="step" style="width: 16em" data-tooltip="<>">
                        <div class="title"><?php echo empty($data) ? "..." : $data[$key]["num_clicks"] ?></div>
                        <div class="description"><?php esc_html_e("Clicks", "robera-recommender") ?></div>
                    </div>
                </div>
                <div class="ui steps" data-tooltip="<?php esc_html_e("Shows the number of bought items from Robera’s recommended items. If a person buys 2 of one product, it will count 2.", "robera-recommender")?>">
                    <div class="step" style="width: 16em" >
                        <div class="title"><?php echo empty($data) ? "..." : $data[$key]["num_bought"] ?></div>
                        <div class="description"><?php esc_html_e("Bought Items", "robera-recommender") ?></div>
                    </div>
                </div>
                <div class="ui steps" data-tooltip="<?php esc_html_e("Shows the total money earned by recommended products.", "robera-recommender")?>">
                    <div class="step" style="width: 16em" >
                        <div class="title" style="font-family: rbr-font-family"><?php echo empty($data) ? "..." : wc_price($data[$key]["sum_bought_value"]) ?></div>
                        <div class="description"><?php esc_html_e("Bought Value", "robera-recommender") ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <h2 class="ui header" style="font-family: rbr-font-family">
                <?php esc_html_e("Robera Recommender Settings", "robera-recommender")?>
            </h2>
            <div class="content">
                <div class="ui form">
                    <form action="/?rest_route=/<?php echo RECOMMENDER_PLUGIN_PREFIX ?>/v1/settings" method="post">
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wp_rest') ?>">
                        <div class="inline field">
                            <div>
                                <label><?php esc_html_e("To add personalized recommendation block in any page (including homepage), use Robera recommendation block inside Woocommerce blocks in page editor", "robera-recommender") ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" tabindex="0" class="hidden" name="related" <?php echo get_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_OPTION_NAME) ? "checked=checked" : ""?>
                                >
                                <label><?php esc_html_e("Use recommendations for related products.", "robera-recommender") ?></label>
                            </div>
                        </div>
                        <div class="four wide field">
                            <label><?php esc_html_e("Related products section class name", "robera-recommender") ?></label>
                            <div class="ui input">
                                <input type="text" name="rel_section_class" placeholder="related products" value="<?php echo get_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_SECTION_CLASS_OPTION_NAME)?>"
                                >
                            </div>
                        </div>
                        <input type="submit" class='positive ui button' style="font-family: rbr-font-family" value="<?php echo esc_html_e('Submit', 'robera-recommender') ?>" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    ( function( $ ) {
        $( document ).ready(function() {
            $('.ui.checkbox')
                .checkbox()
            ;
        });
    }( jQuery3_1_1 ) );
</script>
