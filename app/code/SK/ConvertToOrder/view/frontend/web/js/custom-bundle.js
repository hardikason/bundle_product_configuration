define([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function () {

        var isUserInteracting = false;
        // $('.bundle-option-checkbox').on('change', function () {
        //     var selectionId = $(this).val();
        //     var qtyDropdown = $('#bundle-option-qty-' + selectionId);

        //     if ($(this).is(':checked')) {
        //         qtyDropdown.prop('disabled', false);
        //     } else {
        //         qtyDropdown.prop('disabled', true);
        //     }
        // });

         // Set flag when the user interacts with the dropdown
        $('.bundle-option-qty').on('mousedown keydown', function () {
            isUserInteracting = true;
        });

        $('.bundle-option-qty').on('change', function () {

            if (!isUserInteracting) {
                return; // Prevent default execution on page load
            }
            
            var selectionId = $(this).data('selection-id');
            var optionId = $(this).data('option-id');
            var newQty = $(this).val();
            console.log(optionId + ' '+newQty);

            if(newQty > 0) {
                // Disable all other tier prices except the one related to this option
                $('.options-' + optionId).removeClass('active', false); // Enable only related
                $('.options-' + optionId).addClass('disabled', true); // Disable all
                

                //$('#option-tier-prices-' + optionId).removeClass('disabled', false); // Enable only related
                $('.options-'+ optionId + '-' + selectionId).addClass('active', true); // Enable only related
            } else {
                // Enable all other tier prices except the one related to this option
                $('.options-' + optionId).removeClass('active', false); // Enable only related
                $('.options-' + optionId).removeClass('disabled', false); // Disable all
                
            }
            

            $('select[name="bundle_option[' + optionId + ']"]').val(selectionId);
            $('input[name="bundle_option_qty[' + optionId + ']"]').val(newQty);
            
            // default input for option if qty selection
            $('input[name="bundle_option_qty[' + optionId + ']"]').trigger('change');
            
            
        });
    });
});
