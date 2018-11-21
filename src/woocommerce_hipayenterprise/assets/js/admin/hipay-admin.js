jQuery( function( $ ) {
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

    $( '.woocommerce_hipayenterprise_methods_creditCard_countries' ).multi({
        enable_search: false,
        non_selected_header: 'Available countries',
    	selected_header: 'Authorized countries'
	});

    //Todo à merger avec creditCard
    $( '.woocommerce_hipayenterprise_methods_local_countries' ).multi({
        enable_search: false,
        non_selected_header: 'Available countries',
        selected_header: 'Authorized countries'
    });

    $('.creditCard_admin_menu').click(function () {
        var id = jQuery(this).attr("data-id");
        $('.creditCard_admin_menu').removeClass("creditCard_admin_menu_sel");
        $(this).addClass("creditCard_admin_menu_sel");
        $('.creditCard_admin_config').addClass("hidden");
        $('.creditCard_admin_config_' + id).removeClass("hidden");
        return false;
    });

    //Todo A revoir totalement
    $('.local_admin_menu').click(function () {
        var id = jQuery(this).attr("data-id");
        $('.local_admin_menu').removeClass("local_admin_menu_sel");
        $(this).addClass("local_admin_menu_sel");
        $('.local_admin_config').addClass("hidden");
        $('.local_admin_config_' + id).removeClass("hidden");
        return false;
    });


} );
