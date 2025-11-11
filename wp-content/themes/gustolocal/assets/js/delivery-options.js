/**
 * Delivery Options Handler
 * Handles delivery type changes in cart and checkout
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        var $cartForm = $('form.woocommerce-cart-form');
        if ($cartForm.length) {
            $cartForm.on('change', 'input[name="delivery_type"]', function() {
                var $hidden = $cartForm.find('input.wmb-update-cart-hidden');
                if (!$hidden.length) {
                    $hidden = $('<input>', { type: 'hidden', name: 'update_cart', value: '1', class: 'wmb-update-cart-hidden' }).appendTo($cartForm);
                }
                $hidden.val('1');

                var $button = $cartForm.find('button[name="update_cart"]');
                $button.prop('disabled', false);
                $cartForm.trigger('submit');
            });
        }

        var $checkoutForm = $('form.checkout');
        if ($checkoutForm.length) {
            $checkoutForm.on('change', 'input[name="delivery_type"]', function() {
                var deliveryType = $(this).val();
                var $container = $(this).closest('.delivery-options-checkout');
                $container.addClass('loading');

                $.ajax({
                    url: gustolocal_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'update_delivery_type',
                        delivery_type: deliveryType,
                        nonce: gustolocal_ajax.nonce
                    }
                }).always(function() {
                    $(document.body).trigger('update_checkout');
                    $container.removeClass('loading');
                });
            });
        }
    });
    
})(jQuery);

