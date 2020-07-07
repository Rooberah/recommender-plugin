<?php
namespace Recommender;
$translation = [
    "receiving data"=>esc_html__("receiving data", "robera-recommender"),
    "training"=>esc_html__("training", "robera-recommender"),
    "trained"=>esc_html__("trained", "robera-recommender")
];
$statistics = [
    "on_item"=>esc_html__("Recommendations on item Statistics", "robera-recommender"),
    "on_user"=>esc_html__("Recommendations on user Statistics", "robera-recommender")
]
?>
<div class="semantic">
    <div class="ui container" style="margin-top: 1rem">
        <div class="ui segment">
            <div class="ui segment" style="background-color: #F2F2F2; width: max-content">
                <div class="step" style="width: 14em">
                    <?php if ($recommender_state=="trained"): ?>
                        <div class="ui header" style="color: #21ba45"><?php echo $translation[$recommender_state] ?></div>
                    <?php else: ?>
                        <div class="ui header"><?php echo $translation[$recommender_state] ?></div>
                    <?php endif; ?>
                    <div class="description" style="color: gray"><?php esc_html_e("The last state of robera", "robera-recommender") ?></div>
                </div>
            </div>
            <?php foreach($statistics as $key=>$value): ?>
                <h2 class="ui header">
                    <?php echo $value?>
                </h2>
                <div class="ui steps">
                    <div class="step" style="width: 14em">
                        <div class="title"><?php echo intval($data[$key]["num_recommendations"]) ?></div>
                        <div class="description"><?php esc_html_e("number of recommendations", "robera-recommender") ?></div>
                    </div>
                </div>
                <div class="ui steps">
                    <div class="step" style="width: 14em">
                        <div class="title"><?php echo $data[$key]["num_clicks"] ?></div>
                        <div class="description"><?php esc_html_e("Clicks", "robera-recommender") ?></div>
                    </div>
                </div>
                <div class="ui steps">
                    <div class="step" style="width: 14em">
                        <div class="title"><?php echo $data[$key]["num_bought"] ?></div>
                        <div class="description"><?php esc_html_e("Items bought", "robera-recommender") ?></div>
                    </div>
                </div>
                <div class="ui steps">
                    <div class="step" style="width: 14em">
                        <div class="title"><?php echo wc_price($data[$key]["sum_bought_value"]) ?></div>
                        <div class="description"><?php esc_html_e("Bought Value", "robera-recommender") ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <h2 class="ui header">
                <?php esc_html_e("Robera Recommender Settings", "robera-recommender")?>
            </h2>
            <div class="content">
                <div class="ui form">
                    <form action="/?rest_route=/<?php echo RECOMMENDER_PLUGIN_PREFIX ?>/v1/settings" method="post">
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wp_rest') ?>">
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
                                <input type="text" name="rel_section_class" placeholder="<?php esc_html_e("Class names", "robera-recommender") ?>" value="<?php echo get_option(RecommenderPlugin::$RECOMMEND_ON_RELATED_PRODUCTS_SECTION_CLASS_OPTION_NAME)?>"
                                >
                            </div>
                        </div>
                        <input type="submit" class='positive ui button' value="<?php echo esc_html_e('Submit', 'robera-recommender') ?>" />
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
