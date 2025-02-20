define([
    'jquery'
], function ($) {
    'use strict';

    return function (originalPriceBundle) {
        $.widget('mage.priceBundle', originalPriceBundle, {
            
            _init: function initPriceBundle() {
                this._super();
                this.updateOptionQty();
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
        
                    var selectionId = $(this).data('selection-id');
                    var optionId = $(this).data('option-id');
                    var newQty = $(this).val();
                    console.log(optionId + ' '+newQty);

                    $('select[name="bundle_option[' + optionId + ']"]').trigger('change');
                    
        
                    if(newQty > 0) {

                        $('select[name="bundle_option[' + optionId + ']"]').val(selectionId);
                        
                        $('input[name="bundle_option_qty[' + optionId + ']"]').val(newQty).trigger('change');
                        
                        // Disable all other tier prices except the one related to this option
                        $('.options-' + optionId).removeClass('active', false).addClass('disabled', true); // Disable all
                        
        
                        //$('#option-tier-prices-' + optionId).removeClass('disabled', false); // Enable only related
                        $('.options-'+ optionId + '-' + selectionId).removeClass('disabled', false).addClass('active', true); // Enable only related
                    } else {

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
