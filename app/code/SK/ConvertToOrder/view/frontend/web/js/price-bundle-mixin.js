define([
    'jquery',
    'mage/url',
], function ($, url) {
    'use strict';

    return function (originalPriceBundle) {
        $.widget('mage.priceBundle', originalPriceBundle, {
            
            _init: function initPriceBundle() {
                this._super();
                this.updateOptionQty();
            },
            
            /**
             * Update bundle option qty
             */
            updateOptionQty: function() {

                var configureId = this.getProductConfigureIdFromUrl();

                $('.bundle-option-qty').on('change', function () {
        
                    console.log('Product : ' +$('input[name="product"]').val());

                    let selectionId = $(this).data('selection-id');
                    let optionId = $(this).data('option-id');
                    let newQty = $(this).val();
                    console.log(optionId + ' '+newQty);
                    let heatsinkCondition = $(this).data('heatsink-condition');
                    console.log(heatsinkCondition);
                    let optionInfo = $(this).data('option-info');
                    console.log('optionInfo ', optionInfo);
                    
                    $("[data-heatsink-performance]").removeClass("disabled", false);

                    console.log($("[data-heatsink-performance]").data('option-id'), Number(heatsinkCondition.heatsink_option_id));
                    let heatsinkOptionId = $("[data-heatsink-performance]").data('option-id');
                    let configuredHeatsinkOptionId = Number(heatsinkCondition.heatsink_option_id);

                    if(newQty > 0 && heatsinkCondition && heatsinkOptionId == configuredHeatsinkOptionId) {

                        if(Number(optionInfo.tdp) >= (heatsinkCondition.tdp_greater_than)) {
                            
                            $("[data-heatsink-performance]").each(function() {
                                
                                if (heatsinkCondition.heat_performance != $(this).data("heatsink-performance")
                                    //&& !heatsinkCondition.heat_performance.includes($(this).data("heatsink-performance"))
                                ) {
                                    $(this).addClass("disabled", true);
                                }
                                
                            });
                        } else{
                            $("[data-heatsink-performance]").each(function() {
                                
                                if (heatsinkCondition.heat_performance == $(this).data("heatsink-performance")
                                   // && heatsinkCondition.heat_performance.includes($(this).data("heatsink-performance"))
                                ) {
                                    $(this).addClass("disabled", true);
                                }
                                
                            });
                        }
                    }
                    
                    $('select[name="bundle_option[' + optionId + ']"]').trigger('change');
                    
                    if(newQty > 0) {

                        $('select[name="bundle_option[' + optionId + ']"]').val(selectionId);
                        
                        $('input[name="bundle_option_qty[' + optionId + ']"]').val(newQty).trigger('change');
                        
                        // Disable all other tier prices except the one related to this option
                        $('.options-' + optionId).removeClass('active', false).addClass('disabled', true); // Disable all
                        
                        $('.options-'+ optionId + '-' + selectionId).removeClass('disabled', false).addClass('active', true); // Enable only related
                    } else {
                        $('input[name="bundle_option_qty[' + optionId + ']"]').val(newQty).trigger('change');
                        
                        $('.options-' + optionId).removeClass('active', false).removeClass('disabled', false); // Disable all
                        
                        if(newQty == 0 && optionInfo.option_id == heatsinkCondition.cpu_option_id) {
                            $('select[name="bundle_option_[' + heatsinkCondition.heatsink_option_id + ']"]').val(newQty).trigger('change');
                            $('input[name="bundle_option_qty[' + heatsinkCondition.heatsink_option_id + ']"]').val(newQty).trigger('change');
                            $('.options-' + heatsinkCondition.heatsink_option_id).removeClass('active', false).removeClass('disabled', false); // Disable all
                        }

                    }
                    
                });
            },

            getProductConfigureIdFromUrl: function() {
                // Extracting the id and product_id from the URL
                var pathArray = window.location.pathname.split('/');
                var idIndex = pathArray.indexOf("id");
                var id = idIndex !== -1 ? pathArray[idIndex + 1] : null;
                return id;
            }
        });

        return $.mage.priceBundle;
    };
});
