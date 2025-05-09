jQuery(function ($) {
  'use strict';

  $('#operating_mode').change(function () {
    if ($(this).val() == 'hosted_page') {
      $('.hosted_page_config').removeClass('hidden').show();
      $('.directPost_page_config').hide();
    } else {
      $('.directPost_page_config').removeClass('hidden').show();
      $('.hosted_page_config').hide();
    }
    return false;
  });

  $('ul.nav-tabs > li > a').on('shown.bs.tab', function (e) {
    var id = $(e.target).attr('href').substr(1);
    window.location.hash = id;
  });

  var hash = window.location.hash;
  $('#hipay-container-admin ul a[href="' + hash + '"]').tab('show');

  $('.decimal-input').change(function validate() {
    var value = $(this).val();
    var parsedValue = parseFloat(value.replace(/,/g, '.'));

    if (isNaN(parsedValue) || parsedValue == '') {
      parsedValue = 0;
    }
    $(this).val(parsedValue);
  });

  $('.woocommerce_hipayenterprise_methods_countries').multi({
    enable_search: false,
    non_selected_header: hipay_config_i18n.available_countries,
    selected_header: hipay_config_i18n.authorized_countries
  });

  $('.color-picker').wpColorPicker();
  var $useOneClick = $('#card_token');
  var $paramsOneClick = $('#one_click_params');
  $paramsOneClick.toggle($useOneClick.is(':checked'));
  $useOneClick.change(function() {
    $paramsOneClick.toggle($(this).is(':checked'));
  });
});