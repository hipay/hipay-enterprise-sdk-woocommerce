var methodsInstance = {};
var isProcessing = false;
var processingTimeout = null;
var processingCount = 0;
var MAX_LOCK_TIME = 5000;

jQuery(function ($) {
  var isOrderPayPage = Boolean(hipay_config.isOrderPayPage);
  var checkout_form = isAddPaymentPage()
    ? $('#add_payment_method')
    : isOrderPayPage
      ? $('#order_review')
      : $('form.checkout');
  var hipaySDK = {};
  var useOneClick = Boolean(hipay_config.useOneClick);

  var originalTrigger = $.fn.trigger;
  $.fn.trigger = function (event) {
    if (event === 'update_checkout' && isProcessing) {
      return this;
    }
    return originalTrigger.apply(this, arguments);
  };

  // Safety mechanism - force release lock if held too long
  function ensureLockReleased() {
    if (isProcessing && processingTimeout) {
      clearTimeout(processingTimeout);
      isProcessing = false;
      processingTimeout = null;
    }
  }

  // Reset processing state
  function releaseProcessingLock() {
    clearTimeout(processingTimeout);
    isProcessing = false;
    processingTimeout = null;
    processingCount = 0;
  }

  // Acquire processing lock with safety timeout
  function acquireProcessingLock() {
    if (isProcessing) {
      return false;
    }

    isProcessing = true;
    processingCount++;

    clearTimeout(processingTimeout);
    processingTimeout = setTimeout(function () {
      releaseProcessingLock();
    }, MAX_LOCK_TIME);

    return true;
  }

  function destroy() {
    // Remove event listeners first
    $(document.body).off('click', '#place_order', submitOrder);
    checkout_form.off(
      'change',
      'input[name="payment_method"]',
      addPaymentMethod
    );

    // Then destroy method instances
    for (var method in methodsInstance) {
      try {
        if (
          methodsInstance[method] &&
          typeof methodsInstance[method].destroy === 'function'
        ) {
          methodsInstance[method].destroy();
        }
      } catch (e) {
        console.error('Error destroying method:', e);
      }
      delete methodsInstance[method];
    }

    // Clear field containers to ensure empty state
    clearFieldContainers();
  }

  // Clear all field containers
  function clearFieldContainers() {
    var selectors = [
      'hipay-card-field-cardHolder',
      'hipay-card-field-cardNumber',
      'hipay-card-field-expiryDate',
      'hipay-card-field-cvc',
      'hipay-card-saved-cards',
      'hipay-saved-card-btn'
    ];

    selectors.forEach(function (selector) {
      $('#' + selector + ', .' + selector)
        .empty()
        .html('');
    });
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
    if (isProcessing) return;

    var method = getSelectedMethod();
    if (methodsInstance[method] === undefined) {
      createHostedFieldsInstance(method);
    }
  }

  function isHostedFields() {
    return hipay_config_card?.operating_mode === 'hosted_fields';
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
    if (isProcessing) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

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
    if (form && form.length) {
      var valueResponse = data instanceof Object ? JSON.stringify(data) : data;

      // Remove existing input with the same name to prevent duplicates
      form.find('input[name="' + method + '-' + key + '"]').remove();

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
    try {
      if (
        methodsInstance[method] &&
        typeof methodsInstance[method].getPaymentData === 'function'
      ) {
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
      } else {
        console.error('Payment method not properly initialized:', method);
        fillErrorDiv('Payment method not ready. Please try again.', method);
      }
    } catch (e) {
      console.error('Error getting payment data:', e);
      fillErrorDiv('Error processing payment. Please try again.', method);
    }
  }

  function handleError(errors, method) {
    if (!errors) return;

    for (var error in errors) {
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-" +
          method +
          '-field-error-' +
          errors[error].field +
          "']"
      );

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
    var selected = $('input[name="payment_method"]:checked').val();
    return selected && selected.indexOf('hipayenterprise_') !== -1;
  }

  function isAddPaymentPage() {
    return $('#add_payment_method').length > 0;
  }

  function createHostedFieldsInstance(method) {
    if (
      !method ||
      isPayPalV2() ||
      !isHiPayMethod() ||
      (isCreditCardSelected() && !isHostedFields() && !isAddPaymentPage())
    ) {
      return true;
    }

    if (methodsInstance[method] !== undefined) {
      return methodsInstance[method];
    }

    blockUI();

    // Clear fields before creation
    clearFieldContainers();

    var configHostedFields = {};

    if (isCreditCardSelected()) {
      method = 'card';
      configHostedFields = getCardConfig();
    } else {
      configHostedFields['template'] = 'auto';
    }

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
      }
    };

    try {
      methodsInstance[method] = hipaySDK.create(method, configHostedFields);

      if (isCreditCardSelected() && isHostedFields()) {
        const cardForm = document.getElementById('hipayHF-card-form-container');
        if (!cardForm) {
          console.error('Card form container not found');
          unBlockUI();
          return;
        }

        if (useOneClick) {
          methodsInstance[method].on('ready', function () {
            const savedCards = getSavedCustomerCards();
            const payOtherCardButton =
              document.getElementById('pay-other-card');

            if (savedCards.length > 0 && payOtherCardButton) {
              payOtherCardButton.addEventListener('click', () => {
                cardForm.classList.toggle('hidden');
              });
            } else {
              cardForm.classList.remove('hidden');
            }

            const savedCardsElements =
              document.getElementsByClassName('saved-card');
            if (savedCardsElements.length > 0) {
              cardForm.classList.add('hidden');
              Array.from(savedCardsElements).forEach((card) => {
                card.addEventListener('click', () => {
                  cardForm.classList.add('hidden');
                });
              });
            }
          });
        } else {
          cardForm.classList.remove('hidden');
        }
      }

      // Set up event handlers for the method instance
      methodsInstance[method].on('blur', handleFieldBlur);
      methodsInstance[method].on('inputChange', handleInputChange);
      methodsInstance[method].on('helpButtonToggled', handleHelpButtonToggle);
      methodsInstance[method].on('ready', function () {
        unBlockUI();
      });
      methodsInstance[method].on('error', function (error) {
        console.error('Method error:', error);
        unBlockUI();
      });

      return methodsInstance[method];
    } catch (e) {
      console.error('Error creating hosted fields instance:', e);
      unBlockUI();
      return false;
    }
  }

  // Handler functions for method instance events
  function handleFieldBlur(data) {
    var method = getSelectedMethod();
    if (isCreditCardSelected()) method = 'card';

    var domElement = document.querySelector(
      "[data-hipay-id='hipay-" + method + '-field-error-' + data.element + "']"
    );

    if (!domElement) return;

    if (!data.validity.valid && !data.validity.empty) {
      domElement.innerText = data.validity.error;
    } else {
      domElement.innerText = '';
    }
  }

  function handleInputChange(data) {
    var method = getSelectedMethod();
    if (isCreditCardSelected()) method = 'card';

    var domElement = document.querySelector(
      "[data-hipay-id='hipay-" + method + '-field-error-' + data.element + "']"
    );

    if (!domElement) return;

    if (!data.validity.valid && !data.validity.potentiallyValid) {
      domElement.innerText = data.validity.error;
    } else {
      domElement.innerText = '';
    }
  }

  function handleHelpButtonToggle(data) {
    var domElement = document.querySelector(
      "[data-hipay-id='hipay-help-" + data.element + "']"
    );

    if (!domElement) return;

    domElement.classList.toggle('hipay-visible');
    if (domElement.innerHTML.trim()) {
      domElement.innerHTML = '';
    } else {
      domElement.innerHTML = data.message;
    }
  }

  var isCardsDisplayedAreLimited = Boolean(
    hipay_config?.useOneClick?.card_count &&
      Number(hipay_config.useOneClick.card_count) > 0
  );

  function getCardConfig() {
    var firstName = '';
    var lastName = '';
    if (hipay_config.isOrderPayPage) {
      firstName = hipay_config.customerFirstName || '';
      lastName = hipay_config.customerLastName || '';
    } else {
      firstName = $('#billing_first_name').val() || '';
      lastName = $('#billing_last_name').val() || '';
    }

    return {
      brand: hipay_config_current_cart.activatedCreditCard,
      one_click: {
        enabled: useOneClick,
        ...(isCardsDisplayedAreLimited && {
          cards_display_count: Number(hipay_config.useOneClick.card_count)
        }),
        cards: getSavedCustomerCards()
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
    var selected = $('input[name="payment_method"]:checked').val();
    return selected
      ? selected.replace('hipayenterprise_', '').replace(/_/g, '-')
      : '';
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
    return $('.hipay-container-hosted-fields').length > 0;
  }

  function isCardTypeActivated(result) {
    return (
      result &&
      result.payment_product &&
      hipay_config_current_cart.activatedCreditCard.includes(
        result.payment_product
      )
    );
  }

  function getSavedCustomerCards() {
    var savedCards = hipay_config_current_cart.savedCreditCards;

    if (!savedCards || !savedCards.length) {
      return [];
    }

    return savedCards.filter((card) =>
      hipay_config_current_cart.activatedCreditCard.includes(card.brand)
    );
  }

  function isPayPalV2() {
    return (
      paypal_version &&
      paypal_version.v2 === '1' &&
      getSelectedMethod() === 'paypal'
    );
  }

  function processCheckout() {
    if (!acquireProcessingLock()) {
      return;
    }

    destroy();

    setTimeout(function () {
      try {
        init();
        $(document.body).on('click', '#place_order', submitOrder);
        checkout_form.on(
          'change',
          'input[name="payment_method"]',
          addPaymentMethod
        );
      } catch (e) {
        console.error('Error during checkout processing:', e);
      } finally {
        setTimeout(releaseProcessingLock, 500);
      }
    }, 200);
  }

  function debounce(func, wait, immediate) {
    var timeout;
    return function () {
      var context = this,
        args = arguments;
      var later = function () {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  }

  var debouncedCheckoutProcess = debounce(processCheckout, 300, false);

  $(document).ready(function () {
    setInterval(ensureLockReleased, MAX_LOCK_TIME);

    if (isOrderPayPage && $('input[name="payment_method"]:checked').length) {
      processCheckout();
    }
  });

  $(document.body).on('updated_checkout', function () {
    if ($('input[name="payment_method"]:checked').length) {
      debouncedCheckoutProcess();
    }
  });

  $(document.body).on('update_checkout', function () {
    if (!isProcessing) {
      destroy();
    }
  });

  $(document.body).on('init_add_payment_method', function () {
    if (!isProcessing && containerExist()) {
      processCheckout();
    }
  });

  // Use a debounced handler for billing info changes with additional checks
  var debouncedBillingUpdate = debounce(
    function () {
      if (!isProcessing && containerExist()) {
        $(document.body).trigger('update_checkout');
      }
    },
    500,
    false
  );

  checkout_form.on(
    'change',
    '#billing_first_name, #billing_last_name',
    debouncedBillingUpdate
  );
});
