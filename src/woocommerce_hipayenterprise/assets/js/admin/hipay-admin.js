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

    $('.woocommerce_hipayenterprise_methods_countries').multi({
        enable_search: false,
        non_selected_header: hipay_config_i18n.available_countries,
        selected_header: hipay_config_i18n.authorized_countries
    });

    $('#hipay-container-admin ul a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
        var id = $(e.target).attr("href").substr(1);
        window.location.hash = id;
    });

    var hash = window.location.hash;
    $('#hipay-container-admin ul a[href="' + hash + '"]').tab('show');
});
