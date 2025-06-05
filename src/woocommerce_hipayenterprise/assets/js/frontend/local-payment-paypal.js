'use strict';
jQuery(document).ready(($) => {
  var isOrderPayPage = Boolean(hipay_config_paypal.isOrderPayPage);

  // Separation of Concerns and Modularization
  const checkoutUtils = (() => {
    const cacheSelectors = () => ({
      checkoutForm: isAddPaymentPage()
        ? $('#add_payment_method')
        : isOrderPayPage
          ? $('#order_review')
          : $('form.checkout'),
      submitButton: isOrderPayPage
        ? $('#place_order').clone()
        : $('#payment .place-order .button').clone(),
      paypalField: $('#paypal-field')
    });

    const isAddPaymentPage = () => $('#add_payment_method').length > 0;

    const getSelectedMethod = () => {
      const selectedValue = $('input[name="payment_method"]:checked').val();
      return selectedValue
        ? selectedValue.replace('hipayenterprise_', '').replace(/_/g, '-')
        : '';
    };

    const handleSubmitButton = (method, selectors) => {
      const { submitButton } = selectors;

      if (isOrderPayPage) {
        // Handle order-pay page button
        const placeOrderButton = $('#place_order');

        if (method === 'paypal' && paypal_version?.v2 !== null) {
          // Hide the button when PayPal is selected
          placeOrderButton.hide();
        } else {
          // Show the button for other methods or restore it
          if (!placeOrderButton.is(':visible')) {
            placeOrderButton.show();
          }
        }
      } else {
        // Handle checkout page button (existing logic)
        const placeOrderButton = $('#payment .place-order .button');

        if (method === 'paypal' && paypal_version?.v2 !== null) {
          placeOrderButton.remove();
        } else if (!placeOrderButton.length) {
          $('#payment .place-order').append(submitButton);
        }
      }
    };

    const destroyMethods = (methodsInstance) => {
      Object.values(methodsInstance).forEach((method) => {
        if (typeof method?.destroy === 'function') {
          method.destroy();
        }
      });
      Object.keys(methodsInstance).forEach(
        (key) => delete methodsInstance[key]
      );
    };

    return {
      cacheSelectors,
      isAddPaymentPage,
      getSelectedMethod,
      handleSubmitButton,
      destroyMethods
    };
  })();

  const paypalIntegration = (() => {
    let methodsInstance = {};

    const init = (selectors) => {
      const method = checkoutUtils.getSelectedMethod();
      checkoutUtils.handleSubmitButton(method, selectors);

      if (method === 'paypal' && paypal_version?.v2 !== null) {
        methodsInstance[method] = createPaypalInstance(method);
        handlePaypalEvents(methodsInstance[method], selectors.checkoutForm);
      }
    };

    const createPaypalInstance = (method) => {
      try {
        const paypalFieldExists = $('#paypal-field').length > 0;
        if (!paypalFieldExists) {
          return null;
        }

        const hipaySDK = new HiPay({
          username: hipay_config_paypal.apiUsernameTokenJs,
          password: hipay_config_paypal.apiPasswordTokenJs,
          environment: hipay_config_paypal.environment,
          lang: hipay_config_paypal.lang
        });

        const options = {
          template: 'auto',
          request: {
            locale: hipay_config_paypal.locale,
            currency: hipay_config_paypal.currency,
            amount: String(hipay_config_paypal.amount)
          },
          paypalButtonStyle: {
            shape: hipay_config_paypal.buttonShape,
            height: Number(hipay_config_paypal.buttonHeight || 40),
            color: hipay_config_paypal.buttonColor,
            label: hipay_config_paypal.buttonLabel
          },
          selector: 'paypal-field',
          canPayLater: Boolean(hipay_config_paypal.bnpl)
        };

        return hipaySDK.create(method, options);
      } catch (error) {
        console.error('Error creating PayPal instance:', error);
        return null;
      }
    };

    const handlePaypalEvents = (instancePaypalButton, checkoutForm) => {
      if (!instancePaypalButton) return;

      instancePaypalButton.on('paymentAuthorized', (hipayToken) => {
        $('#paypal-orderId').val(hipayToken.orderID);
        $('#paypal-payment-product').val('paypal');
        $('#paypal-browserInfo').val(JSON.stringify(hipayToken.browser_info));
        $('#paypal-paymentmethod').val('paypal');
        $('#paypal-productlist').val('paypal');
        processPayment(checkoutForm);
      });
    };

    const processPayment = (checkoutForm) => {
      checkoutForm.submit();
    };

    const updateMethods = (selectors) => {
      checkoutUtils.destroyMethods(methodsInstance);

      const selectedMethod = checkoutUtils.getSelectedMethod();

      if (selectedMethod === 'paypal') {
        init(selectors);
      } else {
        // Handle button restoration when PayPal is deselected
        checkoutUtils.handleSubmitButton(selectedMethod, selectors);
      }
    };

    return {
      init,
      updateMethods
    };
  })();

  const checkoutEventHandlers = (() => {
    let pageLoaded = false;
    const selectors = checkoutUtils.cacheSelectors();

    const handlePaymentMethodChange = () => {
      const selectedMethod = checkoutUtils.getSelectedMethod();

      if (selectedMethod === 'paypal') {
        paypalIntegration.updateMethods(selectors);
      } else {
        // Handle button restoration for non-PayPal methods
        checkoutUtils.handleSubmitButton(selectedMethod, selectors);
      }
      pageLoaded = true;
    };

    const handleOrderReviewUpdate = () => {
      paypalIntegration.updateMethods(selectors);
      pageLoaded = true;

      const selectedMethod = checkoutUtils.getSelectedMethod();
      checkoutUtils.handleSubmitButton(selectedMethod, selectors);
    };

    const bindEvents = () => {
      // Listen for payment method change on the order-pay page
      if (isOrderPayPage) {
        // Direct event listener for payment method change
        $('input[name="payment_method"]').on('change', function () {
          handlePaymentMethodChange();
        });

        // Immediate initialization if paypal is selected
        const currentMethod = checkoutUtils.getSelectedMethod();
        if (currentMethod === 'paypal') {
          paypalIntegration.init(selectors);
        } else {
          // Ensure button is visible for non-PayPal methods on page load
          checkoutUtils.handleSubmitButton(currentMethod, selectors);
        }
      }

      // Standard checkout events
      $(document.body).on('payment_method_selected', () => {
        if (pageLoaded) {
          handlePaymentMethodChange();
        }
      });

      // For standard checkout pages
      $(document).ajaxComplete((event, xhr, settings) => {
        if (settings.url?.includes('update_order_review')) {
          handleOrderReviewUpdate();
        }
      });
    };

    return {
      bindEvents
    };
  })();

  // Initialize the checkout process
  checkoutEventHandlers.bindEvents();
});
