var config = {
    // deps: [
    //     "SK_ConvertToOrder/js/custom-bundle"
    // ],
    config: {
        mixins: {
            'Magento_Bundle/js/price-bundle': {
                'SK_ConvertToOrder/js/price-bundle-mixin': true
            }
        }
    }
};
