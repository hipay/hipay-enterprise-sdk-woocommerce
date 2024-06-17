jQuery(function ($) {
    'use strict';

    $('#operating_mode').change(function () {
        if ($(this).val() == "hosted_page") {
            $(".hosted_page_config").removeClass("hidden").show();
            $(".directPost_page_config").hide();
        } else {
            $(".directPost_page_config").removeClass("hidden").show();
            $(".hosted_page_config").hide();
        }
        return false;
    });

    $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
        var id = $(e.target).attr("href").substr(1);
        window.location.hash = id;
    });

    var hash = window.location.hash;
    $('#hipay-container-admin ul a[href="' + hash + '"]').tab('show');

    $(".decimal-input").change(function validate() {
        var value = $(this).val();
        var parsedValue = parseFloat(value.replace(/,/g, "."));

        if (isNaN(parsedValue) || parsedValue === "") {
            parsedValue = 0;
        }
        $(this).val(parsedValue);
    });

    $('.woocommerce_hipayenterprise_methods_countries').multi({
        enable_search: false,
        non_selected_header: hipay_config_i18n.available_countries,
        selected_header: hipay_config_i18n.authorized_countries
    });

    $(document).ready(function() {
        function toggleFields() {
            var merchantId = $('#woocommerce_hipayenterprise_methods_merchantIdpaypal').val();
            [
                'buttonColor',
                'buttonShape',
                'buttonLabel',
                'buttonHeight',
                'bnpl'
            ].forEach(function(field) {
                var fieldElement = $('#woocommerce_hipayenterprise_methods_' + field + 'paypal');
                if (merchantId === '') {
                    fieldElement.addClass('readonly').on('mousedown', preventInteraction);
                } else {
                    fieldElement.removeClass('readonly').off('mousedown', preventInteraction);
                }
            });
        }

        function preventInteraction(event) {
            event.preventDefault();
        }

        // Run the function when the page loads
        toggleFields();

        // Attach the function to the input event
        $('#woocommerce_hipayenterprise_methods_merchantIdpaypal').on('input', toggleFields);
    });


});
