(function ($) {
    'use strict';

    var config         = window.hipayBancomatPayConfig || {};
    var MAX_POLLS      = 30;
    var pollCount      = 0;
    var pollInterval   = null;
    var pendingStatuses = ['pending', 'on-hold'];
    var successStatuses = ['processing', 'completed'];
    var failedStatuses  = ['failed', 'cancelled', 'refunded'];

    if (pendingStatuses.indexOf(config.orderStatus) === -1) {
        return;
    }

    function showState(state) {
        var $section = $('#hipay-bancomat-status');
        $section.find('.hipay-bancomat-pending__state').hide();
        $section.find('.hipay-bancomat-pending__state--' + state).show();

        // Update section background class
        $section
            .removeClass('hipay-bancomat-pending--success hipay-bancomat-pending--failed')
            .addClass('hipay-bancomat-pending--' + state);

        // Scroll section into view
        $('html, body').animate(
            { scrollTop: $section.offset().top - 40 },
            300
        );
    }

    function checkOrderStatus() {
        pollCount++;

        if (pollCount > MAX_POLLS) {
            clearInterval(pollInterval);
            return;
        }

        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action:    'hipay_check_order_status',
                nonce:     config.nonce,
                order_id:  config.orderId,
                order_key: config.orderKey
            },
            success: function (response) {
                if (!response.success) {
                    return;
                }

                var status = response.data.status;

                if (successStatuses.indexOf(status) !== -1) {
                    clearInterval(pollInterval);
                    showState('success');
                } else if (failedStatuses.indexOf(status) !== -1) {
                    clearInterval(pollInterval);
                    showState('failed');
                }
                // pending/on-hold → keep polling
            }
        });
    }

    $(document).ready(function () {
        pollInterval = setInterval(checkOrderStatus, 10000);
    });

}(jQuery));
