define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function ($, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            customProperty: 'customValue',
            listens: {
                'value': 'onValueChange'
            }
        },

        /**
         * Called when the select field value changes
         */
        onValueChange: function (value) {
            console.log('Bundle Product Value Changed:', value);

            if (!value) {
                return;
            }

            let rowIndex;
            let nameAttr = this.inputName; // Get name attribute
            let match = nameAttr.match(/\[dynamic_row\]\[(\d+)\]/); // Extract row index

            if (match) {
                rowIndex = match[1]; // Extracted index
            }
            
            let optionDropdown = $('[name="product[compatible_with][dynamic_row]['+rowIndex+'][bundle_option]"]');
            $.ajax({
                url: window.adminAjaxUrl,
                type: 'GET',
                data: {sku: value, form_key: window.FORM_KEY},
                dataType: 'json',
                success: function (response) {
                    //console.log('response', response);
                    optionDropdown.empty();
                    
                    $.each(response.options, function (key, value) {
                        optionDropdown.append(new Option(value.label, value.value));
                    });
                },
                error: function () {
                    console.error('Failed to fetch bundle options. ');
                }
            });
        },

    });
});
