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

        // getOption: function (value) {
        //     console.log('bundle product getOption -- ', value);
        //     this.onValueChange(value);
        //     return this.indexedOptions[value];
        // },

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
            
            let waitForDropdown = setInterval(function () {
                let optionDropdown = $('[name="product[compatible_with][dynamic_row]['+rowIndex+'][bundle_option]"]');
                let bundleProductDropdown = $('[name="product[compatible_with][dynamic_row]['+rowIndex+'][bundle_product]"]');
                if (optionDropdown.length > 0) {
                    clearInterval(waitForDropdown);
                    console.log('Dropdown found:', optionDropdown.length);
                    
                    $.ajax({
                        url: window.adminAjaxUrl,
                        type: 'GET',
                        data: {sku: bundleProductDropdown.val(), form_key: window.FORM_KEY},
                        dataType: 'json',
                        success: function (response) {
                            //console.log('response', response);
                            optionDropdown.empty();
                            
                            $.each(response.options, function (key, option) {
                                //optionDropdown.append(new Option(value.label, value.value));
                                let newOption = new Option(option.label, option.value);
                                if (option.value == value) {
                                    newOption.selected = true; // Mark "Option 2" as selected
                                    console.log('Selected option:', value);
                                }
                                optionDropdown.append(newOption);
                            });

                            // Trigger change event to reflect selection in UI components (if needed)
                            optionDropdown.trigger("change");
                        },
                        error: function () {
                            console.error('Failed to fetch bundle options. ');
                        }
                    });
                }
            }, 500); // Check every 500m
        },

    });
});
