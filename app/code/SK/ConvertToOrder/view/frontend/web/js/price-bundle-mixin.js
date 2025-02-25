define([
    'jquery',
    'mage/url',
], function ($, url) {
    'use strict';

    return function (originalPriceBundle) {
        $.widget('mage.priceBundle', originalPriceBundle, {
            
            _init: function initPriceBundle() {
                this._super();
               // this.updateOptionQty();
            },

            /**
             * @private
             */
            _create: function createPriceBundle() {
                this._super();
                //this.updateOptionQty();  
            },
            
            /**
             * Update bundle option qty
             */
            updateOptionQty: function() {

                $('.bundle-option-qty').on('change', function () {
        
                    console.log('Product : ' +$('input[name="product"]').val());

                    var selectionId = $(this).data('selection-id');
                    var optionId = $(this).data('option-id');
                    var newQty = $(this).val();
                    var bundleProductId = $('input[name="product"]').val();
                    console.log(optionId + ' '+newQty);

                    var heatsinkCondition = $(this).data('heatsink-condition');
                    
                    $("[data-heatsink-performance]").removeClass("disabled", false);
                    if(newQty > 0 && heatsinkCondition) {

                        var optionInfo = $(this).data('option-info');

                        console.log(heatsinkCondition);
                        console.log('optionInfo ', optionInfo);
                        console.log(Number(optionInfo.tdp), Number(heatsinkCondition.tdp_greater_than));

                        var selectedHeatsinkPerformance = heatsinkCondition.heat_performance[0];
                        
                        if(optionInfo.tdp > heatsinkCondition.tdp_greater_than) {
                            
                            $("[data-heatsink-performance]").each(function() {
                                
                                if ($(this).data("heatsink-performance") !== selectedHeatsinkPerformance) {
                                    $(this).addClass("disabled", true);
                                } 
                                
                            });
                        } else{
                            $("[data-heatsink-performance]").each(function() {
                                
                                if ($(this).data("heatsink-performance") == selectedHeatsinkPerformance) {
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
                        
        
                        //$('#option-tier-prices-' + optionId).removeClass('disabled', false); // Enable only related
                        $('.options-'+ optionId + '-' + selectionId).removeClass('disabled', false).addClass('active', true); // Enable only related
                    } else {
                        $('input[name="bundle_option_qty[' + optionId + ']"]').val(newQty).trigger('change');
                        //$('select[name="bundle_option[' + optionId + ']"]').val(selectionId).trigger('change');
                       // $('input[name="bundle_option_qty[' + optionId + ']"]').val('');
                        // Enable all other tier prices except the one related to this option
                        $('.options-' + optionId).removeClass('active', false).removeClass('disabled', false); // Disable all
                        
                    }
                    
                });
            }
        });

        return $.mage.priceBundle;
    };
});
