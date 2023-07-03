/*global woocommerce_admin_meta_boxes, woocommerce_admin, accounting, woocommerce_admin_meta_boxes_order, wcSetClipboard, wcClearClipboard */
jQuery(function ($) {

    /**
     * Order Items Panel
     */
    var hipay_meta_boxes_order_items = {
        init: function () {
            $('#order_captures').detach().appendTo('.woocommerce_order_items_wrapper');
            $('#hipay-capture-items').insertAfter('.wc-order-refund-items');
            $('#woocommerce-order-items')
                .on('click', 'button.capture-items', this.capture_items)
                .on('click', 'button.do-api-capture', this.do_capture)
                .on( 'change', '.refund input.refund_line_total, .refund input.refund_line_tax', this.input_changed )
                .on( 'change keyup', '#capture_amount', this.amount_changed );

            $('#order_line_items .refund input.refund_order_item_qty').each(function (index, item) {
                // Set all items quantities to max capturable and trigger change event to update other values (tax, total...)

                var order_item_id = $(item.closest('tr')).attr('data-order_item_id');
                var amountCaptured = $('.hipay-captured[data-order_item_id = ' + order_item_id + ']')
                                        .find('.quantity .view .captured')[0]
                                        ?.innerText;

                amountCaptured = amountCaptured ? amountCaptured : 0;

                var capturable = item.max - amountCaptured;

                if(capturable) {
                  $(item)
                    .val(capturable)
                    .attr('max', capturable)
                    .change();
                }
            });
        },

        amount_changed: function() {
            var total = accounting.unformat( $( this ).val(), woocommerce_admin.mon_decimal_point );

            $( 'button .wc-order-refund-amount .amount' ).text( accounting.formatMoney( total, {
                symbol:    woocommerce_admin_meta_boxes.currency_format_symbol,
                decimal:   woocommerce_admin_meta_boxes.currency_format_decimal_sep,
                thousand:  woocommerce_admin_meta_boxes.currency_format_thousand_sep,
                precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
                format:    woocommerce_admin_meta_boxes.currency_format
            } ) );
        },

        input_changed: function() {
            var capture_amount = 0;
            var $items        = $( '.woocommerce_order_items' ).find( 'tr.item, tr.fee, tr.shipping' );

            $items.each(function() {
                var $row               = $( this );
                var refund_cost_fields = $row.find( '.refund input:not(.refund_order_item_qty)' );

                refund_cost_fields.each(function( index, el ) {
                    capture_amount += parseFloat( accounting.unformat( $( el ).val() || 0, woocommerce_admin.mon_decimal_point ) );
                });
            });

            var captured_amount_remaining = $('input#captured_amount_remaining').val();
            var diffCaptured = captured_amount_remaining - capture_amount;
            if (Math.abs(diffCaptured) <= 0.01) {
                capture_amount = captured_amount_remaining;
            }
            var report_capture_amount = accounting.formatNumber(
                capture_amount,
                woocommerce_admin_meta_boxes.currency_format_num_decimals,
                '',
                woocommerce_admin.mon_decimal_point
            );

            $( '#capture_amount' ).val( report_capture_amount).change();
        },

        block: function () {
            $('#woocommerce-order-items').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        unblock: function() {
            $( '#woocommerce-order-items' ).unblock();
        },

        capture_items: function () {
            $('div.wc-order-capture-items').slideDown();
            $('div.wc-order-data-row-toggle').not('div.wc-order-capture-items').slideUp();
            $('div.wc-order-totals-items').slideUp();
            $('#woocommerce-order-items').find('div.refund').show();
            $('.wc-order-edit-line-item .wc-order-edit-line-item-actions').hide();

            if($(this).hasClass('capture-complete-only')) {
                $('#woocommerce-order-items').find('div.refund input').prop('readonly', 'readonly');
                $('#capture_amount').prop('readonly', 'readonly');
            }

            return false;
        },

        do_capture: function () {
            hipay_meta_boxes_order_items.block();

            if (window.confirm(hipay_config_i18n.msg_confirm_capture)) {
                var capture_amount = $('input#capture_amount').val();
                var captured_amount = $('input#captured_amount').val();
                // Get line item Capture
                var line_item_qtys = {};
                var line_item_totals = {};
                var line_item_tax_totals = {};

                $('.refund input.refund_order_item_qty').each(function (index, item) {
                    if ($(item).closest('tr').data('order_item_id')) {
                        if (item.value) {
                            line_item_qtys[$(item).closest('tr').data('order_item_id')] = item.value;
                        }
                    }
                });

                $('.refund input.refund_line_total').each(function (index, item) {
                    if ($(item).closest('tr').data('order_item_id')) {
                        line_item_totals[$(item).closest('tr').data('order_item_id')] = accounting.unformat(item.value, woocommerce_admin.mon_decimal_point);
                    }
                });

                $('.refund input.refund_line_tax').each(function (index, item) {
                    if ($(item).closest('tr').data('order_item_id')) {
                        var tax_id = $(item).data('tax_id');

                        if (!line_item_tax_totals[$(item).closest('tr').data('order_item_id')]) {
                            line_item_tax_totals[$(item).closest('tr').data('order_item_id')] = {};
                        }

                        line_item_tax_totals[$(item).closest('tr').data('order_item_id')][tax_id] = accounting.unformat(item.value, woocommerce_admin.mon_decimal_point);
                    }
                });

                var data = {
                    action: 'woocommerce_capture_line_items',
                    order_id: woocommerce_admin_meta_boxes.post_id,
                    capture_amount: capture_amount,
                    captured_amount: captured_amount,
                    line_item_qtys: JSON.stringify(line_item_qtys, null, ''),
                    line_item_totals: JSON.stringify(line_item_totals, null, ''),
                    line_item_tax_totals: JSON.stringify(line_item_tax_totals, null, ''),
                    security: woocommerce_admin_meta_boxes.order_item_nonce
                };

                $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
                    if (true === response.success) {
                        // Redirect to same page for show the refunded status
                        window.location.href = window.location.href;
                    } else {
                        window.alert(response.data.error);
                        //hipay_meta_boxes_order_items.reload_items();
                        hipay_meta_boxes_order_items.unblock();
                    }
                });
            } else {
                hipay_meta_boxes_order_items.unblock();
            }
        }
    };

    hipay_meta_boxes_order_items.init();
});
