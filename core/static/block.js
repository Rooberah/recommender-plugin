if (!String.prototype.format) {
    String.prototype.format = function () {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function (match, number) {
            return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}

const {ServerSideRender} = wp;
const {RawHTML} = wp.element;
const {__, _x, _n, _nx} = wp.i18n;

jQuery(document).ready(function () {

    (function (blocks, editor, element) {
        var el = element.createElement;
        var InspectorControls = editor.InspectorControls;
        var Fragment = element.Fragment;
        var CheckboxControl = wp.components.CheckboxControl;
        var RangeControl = wp.components.RangeControl;
        const icon = el('svg', { width: 17, height: 17.32, version:"1.1", xmlns:"http://www.w3.org/2000/svg", preserveAspectRatio:"xMidYMid meet", viewBox:"243.67031161849135 241.57842523020744 153.96288075599924 156.75448853507154"},
            el('path', { d: "M386.43 325.3C385.96 331.44 387.3 340.49 389.83 346.49C398.21 366.39 396.65 385.26 376.97 392.62C363.2 397.76 346.7 394.45 338.03 392C334.69 391.06 332.67 387.71 333.34 384.3C336.36 368.87 334.44 356.85 325.6 349.63C326.16 349.32 328.96 347.79 334 345.04L334.52 325.83C343.54 325.85 348.55 325.87 349.55 325.87C349.55 325.87 354.61 321.19 361.17 319.23C368.83 316.96 376.88 318.05 381.99 319.26C384.76 319.91 386.65 322.46 386.43 325.3Z", id:"c4rguG5Vck"}),
            el('path', { d: "M324.6 253.63C330.74 254.11 339.79 252.77 345.79 250.24C365.69 241.86 384.56 243.42 391.92 263.1C397.06 276.87 393.76 293.37 391.3 302.04C390.36 305.38 387.01 307.4 383.6 306.73C368.17 303.71 356.15 305.63 348.93 314.47C348.62 313.91 347.09 311.11 344.34 306.07L325.13 305.55C325.15 296.53 325.17 291.52 325.17 290.51C325.17 290.51 320.49 285.46 318.53 278.9C316.26 271.24 317.36 263.19 318.56 258.08C319.21 255.31 321.76 253.42 324.6 253.63Z", id:"b2ZP7PV6Y"}),
            el('path', { d: "M252.87 312.62C253.34 306.47 252.01 297.42 249.48 291.42C241.1 271.52 242.65 252.65 262.33 245.3C276.11 240.15 292.61 243.46 301.28 245.91C304.61 246.85 306.63 250.2 305.97 253.61C302.95 269.04 304.86 281.06 313.7 288.28C313.14 288.59 310.34 290.12 305.3 292.87L304.79 312.08C295.76 312.06 290.75 312.04 289.75 312.04C289.75 312.04 284.69 316.72 278.14 318.68C270.47 320.95 262.42 319.85 257.31 318.65C254.55 318 252.65 315.45 252.87 312.62Z", id:"b1OXNhEZiZ" } ),
            el('path', { d: "M314.71 386.55C308.56 386.08 299.52 387.41 293.52 389.94C273.62 398.32 254.74 396.76 247.39 377.08C242.24 363.31 245.55 346.81 248 338.14C248.94 334.8 252.3 332.79 255.7 333.45C271.13 336.47 283.15 334.55 290.37 325.72C290.68 326.28 292.21 329.07 294.96 334.11L314.17 334.63C314.15 343.65 314.14 348.66 314.13 349.67C314.13 349.67 318.82 354.72 320.77 361.28C323.04 368.94 321.94 376.99 320.75 382.1C320.09 384.87 317.55 386.77 314.71 386.55Z", id:"a29KYKQaV3"}),
        );

        function get_product_element(product) {
            return el(
                'div',
                {className: 'RBR-column'},
                [
                    el(
                        'div',
                        {},
                        el(
                            'img',
                            {
                                className: 'RBR-image',
                                src: product.image_url ? product.image_url : "https://market.rooberah.co/wp-content/uploads/woocommerce-placeholder.png",
                            }
                        )
                    ),
                    el(
                        'span',
                        {className: "wc-block-grid__product-onsale"},
                        el(
                            'span',
                            {ariaHidden: true},
                            "Sale!"
                        ),
                        el(
                            'span',
                            {className: "screen-reader-text"},
                            "Product on sale"
                        ),
                    ),
                    el(
                        'div',
                        {className: "wc-block-grid__product-price price"},
                        [
                            el(
                                'del',
                                [],
                                el(
                                    'span',
                                    {className: "woocommerce-Price-amount amount"},
                                    [
                                        el(
                                            'span',
                                            {className: "woocommerce-Price-currencySymbol"},
                                            "$"
                                        ),
                                        product.regular_price
                                    ]
                                ),
                            ),
                            el(
                                'ins',
                                [],
                                el(
                                    'span',
                                    {className: "woocommerce-Price-amount amount"},
                                    [
                                        el(
                                            'span',
                                            {className: "woocommerce-Price-currencySymbol"},
                                            "$"
                                        ),
                                        product.price
                                    ]
                                ),
                            ),
                        ]
                    ),
                    el(
                        'div',
                        {className: ""},
                        el(
                            'a',
                            {className: "RBR-cart", style:{color:'white'}},
                            "Add to cart"
                        ),
                    ),
                ]
            );
        }
        var blockStyle = {
            backgroundColor: '#022',
            color: '#fff',
            padding: '20px',
        };

        let elements = data.products.map(get_product_element);
        blocks.registerBlockType('recommender/user-recommendation', {
            title: __('Robera Recommendation', 'robera-recommender'),
            icon: icon,
            category: 'woocommerce',
            attributes: {
                columns: {
                    type: 'number',
                    default: 3
                }
            },
            example: {},
            edit: function (props) {
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
                                    onChange: function (value) {
                                        props.setAttributes({columns: value})
                                    }
                                }
                            ),
                        )
                    ),
                    el(
                        'div',
                        {className: 'RBR-back'},
                        el(
                            'span',
                            {},
                            data.msg
                        )
                    ),
                    el(
                        'div',
                        {className: 'RBR-row RBR-back'},
                        elements.slice(0, props.attributes.columns)

                    )
                ]
                    ;
            },
            save: function (props) {
                var myShortcode = '[user_recommendations columns="' + props.attributes.columns + '"]';
                return el(
                    'div', {}, el(RawHTML, {}, myShortcode)
                );
            },
        });
    }(
        wp.blocks,
        wp.editor,
        wp.element
    ));
});