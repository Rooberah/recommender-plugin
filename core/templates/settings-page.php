<?php
namespace Recommender;

?>
<div class="semantic">
    <div class="ui container" style="margin-top: 1rem">
        <div class="ui segment">
            <h2 class="ui header">
                <?php esc_html_e("Recommendation Statistics", "robera-recommender")?>
            </h2>
            <div class="ui steps">
                <div class="step" style="width: 12em">
                    <div class="title"><?php echo $num_recommendations ?></div>
                    <div class="description"><?php esc_html_e("Recommendations", "robera-recommender") ?></div>
                </div>
            </div>
            <div class="ui steps">
                <div class="step" style="width: 12em">
                    <div class="title"><?php echo $num_clicks ?></div>
                    <div class="description"><?php esc_html_e("Clicks", "robera-recommender") ?></div>
                </div>
            </div>
            <div class="ui steps">
                <div class="step" style="width: 12em">
                    <div class="title"><?php echo $num_bought ?></div>
                    <div class="description"><?php esc_html_e("Items bought", "robera-recommender") ?></div>
                </div>
            </div>
            <div class="ui steps">
                <div class="step" style="width: 12em">
                    <div class="title"><?php echo wc_price($sum_bought_value) ?></div>
                    <div class="description"><?php esc_html_e("Bought Value", "robera-recommender") ?></div>
                </div>
            </div>
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
