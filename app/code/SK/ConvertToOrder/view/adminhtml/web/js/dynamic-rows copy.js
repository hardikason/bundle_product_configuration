// define([
//     'jquery',
//     'mage/utils/wrapper',
//     'mage/url'
// ], function ($, wrapper, url) {
//     'use strict';

//     $(document).ready(function () {
    
//             // Attach event listener on change for bundle_product dropdown
//             $(document).on('change', '[name*="bundle_product"]', function () {
//                 let bundleSku = $(this).val();
//                 let optionDropdown = $(this).closest('tr').find('[name*="bundle_option_title"]');

//                 if (!bundleSku) {
//                     return;
//                 }

//                 $.ajax({
//                     url: window.adminAjaxUrl,
//                     type: 'GET',
//                     data: {sku: bundleSku, form_key: window.FORM_KEY},
//                     dataType: 'json',
//                     success: function (response) {
//                         optionDropdown.empty();
                        
//                         $.each(response.options, function (key, value) {
//                             optionDropdown.append(new Option(value.label, value.value));
//                         });
//                     },
//                     error: function () {
//                         console.error('Failed to fetch bundle options. ');
//                     }
//                 });
//             });

//     });
    
// });


define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (uiRegistry, Select) {
    'use strict';

    return Select.extend({
        initialize: function () {
            this._super();
            this.bundleProductPath = 'index = compatible_with.record.bundle_product'; 

            this.observeChanges();
            return this;
        },

        observeChanges: function () {
            let self = this;

            uiRegistry.get(this.bundleProductPath, function (bundleProductField) {
                bundleProductField.on('value', function (newValue) {
                    self.loadOptions(newValue);
                });
            });
        },

        loadOptions: function (sku) {
            let self = this;
            if (!sku) {
                self.setOptions([]);
                return;
            }

            // Fetch bundle options dynamically via Ajax
            jQuery.ajax({
                url: window.adminAjaxUrl,
                type: 'GET',
                data: { sku: sku },
                success: function (response) {
                    self.setOptions(response);
                }
            });
        }
    });
});
