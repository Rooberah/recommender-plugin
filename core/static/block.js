if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}

const { ServerSideRender } = wp;
const { RawHTML } = wp.element;
const { __, _x, _n, _nx } = wp.i18n;

jQuery(document).ready(function(){
( function( blocks, editor, element ) {
    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var Fragment = element.Fragment;
    var CheckboxControl = wp.components.CheckboxControl;
    var RangeControl = wp.components.RangeControl;

    var blockStyle = {
        backgroundColor: '#022',
        color: '#fff',
        padding: '20px',
    };
    blocks.registerBlockType('recommender/user-recommendation', {
        title: __('Product Recommendation for User', 'robera-recommender'),
        icon: 'smiley',
        category: 'woocommerce',
        attributes: {
            columns: {
                type: 'number',
                default: 3
            }
        },
        example: {},
        edit: function(props) {
            // attributes = {};
            // return el( ServerSideRender, {
            //     block: 'gutenberg-examples/example-01-basic-esnext',
            //     attributes: attributes
            // })
            return [
            el(
                Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        RangeControl,
                        {
                            label: __('Number of products', 'robera-recommender'),
                            min: 2,
                            max: 5,
                            value: props.attributes.columns,
                            onChange: function(value) {props.setAttributes( { columns: value } )}
                        }
                    ),
                )
            ),
            el(
                'p',
                { style: blockStyle },
                [
                    __('Here you will have a recommendation block for user with {0} columns. ',
                       'robera-recommender').format(props.attributes.columns),
                    el('br', {},),
                    __('For changing number of columns use sidebar settings.', 'robera-recommender')
                ]
            ),
            ];
        },
        save: function(props) {
            var myShortcode = '[user_recommendations columns="' + props.attributes.columns + '"]';
            return el(
                'div', {}, el(RawHTML, {}, myShortcode)
            );
        },
    } );
}(
    wp.blocks,
    wp.editor,
    wp.element
) );
});