var methodsInstance = {};

jQuery(function ($) {
  if (isAddPaymentPage()) {
    var checkout_form = $('#add_payment_method');
  } else {
    var checkout_form = $('form.checkout');
  }
  var hipaySDK = {};

  var useOneClick = Boolean(hipay_config.useOneClick);

  function destroy() {
    for (var method in methodsInstance) {
      methodsInstance[method].destroy();
      delete methodsInstance[method];
    }

    $(document.body).off('click', '#place_order', submitOrder);

    checkout_form.off(
      'change',
      'input[name="payment_method"]',
      addPaymentMethod
    );
  }

  function init() {
    var defaultMethod = getSelectedMethod();

    hipaySDK = HiPay({
      username: hipay_config.apiUsernameTokenJs,
      password: hipay_config.apiPasswordTokenJs,
      environment: hipay_config.environment,
      lang: hipay_config.lang
    });

    hipaySDK.injectBaseStylesheet();

    if (containerExist()) {
      createHostedFieldsInstance(defaultMethod);
    }
  }

  function addPaymentMethod() {
    var method = getSelectedMethod();

    if (methodsInstance[method] === undefined) {
      createHostedFieldsInstance(method);
    }
  }

  function isHostedFields() {
    return hipay_config_card.operating_mode === 'hosted_fields';
  }
  function isOneClick() {
    return (
      $(
        'input[name="wc-hipayenterprise_credit_card-payment-token"]:checked'
      ).val() !== undefined &&
      $(
        'input[name="wc-hipayenterprise_credit_card-payment-token"]:checked'
      ).val() !== 'new'
    );
  }
  function checkPayment() {
    if (isOneClick()) {
        injectInput(
          $('#hipay-token-payment-data'),
          'browser_info',
          hipaySDK.getBrowserInfo(),
          'card'
        );
        injectInput(
          $('#hipay-token-payment-data'),
          'device_fingerprint',
          hipaySDK.getDeviceFingerprint(),
          'card'
        );
        processPayment();
    } else {
      processPayment();
    }
  }

  function submitOrder(e) {
    if (isHiPayMethod()) {
      e.preventDefault();
      e.stopPropagation();

      if (
        isCreditCardSelected() &&
        !isAddPaymentPage() &&
        (!isHostedFields() || isOneClick())
      ) {
        checkPayment();
      } else {
        var method = getSelectedMethod();

        if (isCreditCardSelected()) {
          method = 'card';
        }

        getPaymentData(method);
      }
    }
  }

  function processPayment() {
    checkout_form.submit();
  }

  function applyPaymentData(response, method) {
    var methodForm = $('#' + methodsInstance[method].options.selector);
    for (var key in response) {
      injectInput(methodForm, key, response[key], method);
    }
  }

  function injectInput(form, key, data, method) {
    if (form) {
      var valueResponse = data instanceof Object ? JSON.stringify(data) : data;
      form.append(
        $('<input>')
          .attr('type', 'hidden')
          .attr('name', method + '-' + key)
          .val(valueResponse)
      );
    }
  }

  function getPaymentData(method) {
    hideErrorDiv(method);
    methodsInstance[method].getPaymentData().then(
      function (response) {
        if (isCreditCardSelected() && !isCardTypeActivated(response)) {
          fillErrorDiv(hipay_config_i18n.activated_card_error, method);
        } else {
          applyPaymentData(response, method);
          processPayment();
        }
      },
      function (error) {
        handleError(error, method);
      }
    );
  }

  function handleError(errors, method) {
    for (var error in errors) {
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-" +
          method +
          '-field-error-' +
          errors[error].field +
          "']"
      );

      // If DOM element add error inside
      if (domElement) {
        domElement.innerText = errors[error].error;
      }
    }
  }

  function fillErrorDiv(error, method) {
    $('#error-js-' + method).show();
    $('#error-js-' + method).html(error);
  }

  function hideErrorDiv(method) {
    $('#error-js-' + method).hide();
    $('#error-js-' + method).html('');
  }

  function isHiPayMethod() {
    return (
      $('input[name="payment_method"]:checked')
        .val()
        .indexOf('hipayenterprise_') !== -1
    );
  }

  function isAddPaymentPage() {
    return $('#add_payment_method').length;
  }

  function createHostedFieldsInstance(method) {
    if( isPayPalV2()) {
      return  false;
    }

    if (
      !isHiPayMethod() ||
      (isCreditCardSelected() && !isHostedFields() && !isAddPaymentPage())
    ) {
      return true;
    }

    var configHostedFields = {};

    if (isCreditCardSelected()) {
      method = 'card';
      configHostedFields = getCardConfig();
    } else {
      configHostedFields['template'] = 'auto';
    }

    if (methodsInstance[method] !== undefined) {
      return methodsInstance[method];
    }

    blockUI();
    configHostedFields['selector'] = 'hipayHF-container-' + method;
    configHostedFields['styles'] = {
      base: {
        fontFamily: hipay_config.fontFamily,
        color: hipay_config.color,
        fontSize: hipay_config.fontSize,
        fontWeight: hipay_config.fontWeight,
        placeholderColor: hipay_config.placeholderColor,
        caretColor: hipay_config.caretColor,
        iconColor: hipay_config.iconColor
      },
      components: {
        switch: {
          mainColor: hipay_config.useOneClick.switch_color
        },
        checkbox: {
          mainColor: hipay_config.useOneClick.checkbox_color
        }
      },
    };

    methodsInstance[method] = hipaySDK.create(method, configHostedFields);

    const cardForm = document.getElementById('hipayHF-card-form-container');
    if (!cardForm) {
      console.error('Card form container not found');
      return;
    }

    if (useOneClick) {
      methodsInstance[method].on('ready', function () {
        const savedCards = getSavedCustomerCards();
        const payOtherCardButton = document.getElementById('pay-other-card');

        if (savedCards.length > 0 && payOtherCardButton) {
          payOtherCardButton.addEventListener('click', () => {
            cardForm.classList.toggle('hidden');
          });
        } else {
          cardForm.classList.remove('hidden');
        }

        const savedCardsElements = document.getElementsByClassName('saved-card');
        if (savedCardsElements.length > 0) {
          cardForm.classList.add('hidden');
          Array.from(savedCardsElements).forEach(card => {
            card.addEventListener('click', () => {
              cardForm.classList.add('hidden');
            });
          });
        }
      });
    } else {
      cardForm.classList.remove('hidden');
    }

    methodsInstance[method].on('blur', function (data) {
      // Get error container
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-" +
          method +
          '-field-error-' +
          data.element +
          "']"
      );

      // Finish function if no error DOM element
      if (!domElement) {
        return;
      }

      // If not valid & not empty add error
      if (!data.validity.valid && !data.validity.empty) {
        domElement.innerText = data.validity.error;
      } else {
        domElement.innerText = '';
      }
    });

    methodsInstance[method].on('inputChange', function (data) {
      // Get error container
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-" +
          method +
          '-field-error-' +
          data.element +
          "']"
      );

      // Finish function if no error DOM element
      if (!domElement) {
        return;
      }

      // If not valid & not potentiallyValid add error (input is focused)
      if (!data.validity.valid && !data.validity.potentiallyValid) {
        domElement.innerText = data.validity.error;
      } else {
        domElement.innerText = '';
      }
    });

    methodsInstance[method].on('helpButtonToggled', function (data) {
      // Get error container
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-help-" + data.element + "']"
      );

      // Finish function if no error DOM element
      if (!domElement) {
        return;
      }

      if (domElement) {
        // Toggle visible class
        domElement.classList.toggle('hipay-visible');
        if (domElement.innerHTML.trim()) {
          domElement.innerHTML = '';
        } else {
          domElement.innerHTML = data.message;
        }
      }
    });

    methodsInstance[method].on('ready', function () {
      unBlockUI();
    });
  }
  var isCardsDisplayedAreLimited = Boolean(
      hipay_config?.useOneClick?.card_count &&
      Number(hipay_config.useOneClick.card_count) > 0
  );
  function getCardConfig() {
    var firstName = $('#billing_first_name').val();
    var lastName = $('#billing_last_name').val();

    return {
      brand:hipay_config_current_cart.activatedCreditCard,
      one_click: {
        enabled: useOneClick,
        ...(isCardsDisplayedAreLimited && {
          cards_display_count: Number(hipay_config.useOneClick.card_count)
        }),
        cards: getSavedCustomerCards(),
      },
      fields: {
        savedCards: {
          selector: 'hipay-card-saved-cards'
        },
        cardHolder: {
          selector: 'hipay-card-field-cardHolder',
          defaultFirstname: firstName,
          defaultLastname: lastName
        },
        cardNumber: {
          selector: 'hipay-card-field-cardNumber'
        },
        expiryDate: {
          selector: 'hipay-card-field-expiryDate'
        },
        cvc: {
          selector: 'hipay-card-field-cvc',
          helpButton: true
        },
        savedCardButton: {
          selector: 'hipay-saved-card-btn'
        }
      }
    };
  }

  function isCreditCardSelected() {
    return getSelectedMethod() === 'credit-card';
  }

  function getSelectedMethod() {
    return $('input[name="payment_method"]:checked')
      .val()
      .replace('hipayenterprise_', '')
      .replace(/_/g, '-');
  }

  function blockUI() {
    $(
      '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table'
    ).block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6
      }
    });
  }

  function unBlockUI() {
    $(
      '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table'
    ).unblock();
  }

  function containerExist() {
    return $('.hipay-container-hosted-fields').length;
  }

  function isCardTypeActivated(result) {
    return hipay_config_current_cart.activatedCreditCard.includes(
      result.payment_product
    );
  }

  function getSavedCustomerCards() {
    var savedCards = hipay_config_current_cart.savedCreditCards;

    if (!savedCards || !savedCards.length) {
      return [];
    }

    return savedCards.filter(card =>  hipay_config_current_cart.activatedCreditCard.includes(
        card.brand
    ));
  }

  function isPayPalV2()
  {
    return (paypal_version.v2 === '1' && getSelectedMethod() === 'paypal');
  }

  $(document.body).on('updated_checkout', function () {
    if ($('input[name="payment_method"]:checked').length) {
      init();
      $(document.body).on('click', '#place_order', submitOrder);
      checkout_form.on(
        'change',
        'input[name="payment_method"]',
        addPaymentMethod
      );
    }
  });

  $(document.body).on('update_checkout', function () {
    destroy();
  });

  $(document.body).on('init_add_payment_method', function () {
    destroy();
    if (containerExist()) {
      init();
      $(document.body).on('click', '#place_order', submitOrder);
    }
  });

  checkout_form.on(
    'change',
    '#billing_first_name, #billing_last_name',
    function () {
      if (containerExist()) {
        $(document.body).trigger('update_checkout');
      }
    }
  );
});
