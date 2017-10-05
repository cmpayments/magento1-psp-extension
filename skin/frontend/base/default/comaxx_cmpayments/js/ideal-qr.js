/**
 * CM Payments iDEAL QR javascript file
 *
 * Used to check for payment updates
 */
(function ($, $$) {
    document.observe("dom:loaded", function() {
        var idealQR = $('cmpayments-ideal-qr'),
            form_key = $$('input[name="form_key"]:first')[0].value;

        if (idealQR) {
            var updateUrl = idealQR.readAttribute('data-update-url'), timeoutId, ajax;

            function checkPayment() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }

                new Ajax.Request(updateUrl, {
                    'method': 'post', 'parameters': {
                        'form_key': form_key
                    },
                    'onSuccess': function(response) {
                        if (response.responseJSON.success) {
                            location.href = response.responseJSON.redirect;
                            return;
                        }

                        timeoutId = setTimeout(checkPayment, 2000);
                    }
                });
            }

            checkPayment();
        }
    });
})($, $$);