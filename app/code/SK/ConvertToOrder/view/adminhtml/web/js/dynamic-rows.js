define([
    'jquery',
    'mage/utils/wrapper',
    'mage/url'
], function ($, wrapper, url) {
    'use strict';

    $(document).ready(function () {
    
            // Attach event listener on change for bundle_product dropdown
            $(document).on('change', '[name*="bundle_product"]', function () {
                let bundleSku = $(this).val();
                let optionDropdown = $(this).closest('tr').find('[name*="bundle_option_title"]');

                if (!bundleSku) {
                    return;
                }

                $.ajax({
                    url: window.adminAjaxUrl,
                    type: 'GET',
                    data: {sku: bundleSku, form_key: window.FORM_KEY},
                    dataType: 'json',
                    success: function (response) {
                        optionDropdown.empty();
                        
                        $.each(response.options, function (key, value) {
                            optionDropdown.append(new Option(value.label, value.value));
                        });
                    },
                    error: function () {
                        console.error('Failed to fetch bundle options. ');
                    }
                });
            });

    });
    
});
