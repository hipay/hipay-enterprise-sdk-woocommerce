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
      $('#paypal-field').empty();
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

    const getCurrentShippingAddress = () => {

      const getFieldValue = (fieldId) => {
        const field = $(fieldId);
        if (!field.length) {
          return '';
        }

        const value = field[0] ? field[0].value : field.val();
        const trimmedValue = value ? String(value).trim() : '';

        return trimmedValue;
      };

      const zipCode = getFieldValue('#shipping_postcode') || getFieldValue('#billing_postcode');
      const city = getFieldValue('#shipping_city') || getFieldValue('#billing_city');
      const country = getFieldValue('#shipping_country') || getFieldValue('#billing_country');
      const streetaddress = getFieldValue('#shipping_address_1') || getFieldValue('#billing_address_1');
      const streetaddress2 = getFieldValue('#shipping_address_2') || getFieldValue('#billing_address_2');
      const firstname = getFieldValue('#shipping_first_name') || getFieldValue('#billing_first_name');
      const lastname = getFieldValue('#shipping_last_name') || getFieldValue('#billing_last_name');

      const address = {
        zipCode: zipCode,
        city: city,
        country: country,
        streetaddress: streetaddress,
        streetaddress2: streetaddress2,
        firstname: firstname,
        lastname: lastname
      };

      return address;
    };

    const validateShippingAddress = (address) => {

      if (!address || typeof address !== 'object') {
        return {
          isValid: false,
          errorMessage: hipay_config_paypal.i18n.addressRequired
        };
      }

      const requiredFields = ['zipCode', 'city', 'country', 'streetaddress'];

      const missingFields = requiredFields.filter((field) => {
        const value = address[field];
        const isEmpty = !value || (typeof value === 'string' && value.trim() === '');

        return isEmpty;
      });


      if (missingFields.length > 0) {

        const translatedFields = missingFields.map((field) => {
          return hipay_config_paypal.i18n.fieldNames[field] || field;
        });

        return {
          isValid: false,
          errorMessage:
            hipay_config_paypal.i18n.invalidAddressPrefix +
            translatedFields.join(', ') +
            '.'
        };
      }

      return { isValid: true };
    };

    const showAddressError = (errorMessage) => {
      const errorDiv = $('#error-js-paypal');
      const paypalField = $('#paypal-field');

      paypalField.empty();
      paypalField.removeAttr('data-paypal-button');
      paypalField.hide();

      errorDiv.html(errorMessage).show();
    };

    const hideAddressError = () => {
      const errorDiv = $('#error-js-paypal');
      const paypalField = $('#paypal-field');

      errorDiv.html('').hide();
      paypalField.empty().show();
    };

    const init = (selectors) => {
      const method = checkoutUtils.getSelectedMethod();
      checkoutUtils.handleSubmitButton(method, selectors);

      if (method === 'paypal' && paypal_version?.v2 !== null) {
        const paypalInstance = createPaypalInstance(method);

        if (paypalInstance) {
          methodsInstance[method] = paypalInstance;
          handlePaypalEvents(methodsInstance[method], selectors.checkoutForm);
        } else {
          checkoutUtils.handleSubmitButton(method, selectors);
        }
      }
    };

    const createPaypalInstance = (method) => {
      try {
        const paypalFieldExists = $('#paypal-field').length > 0;
        if (!paypalFieldExists) {
          return null;
        }

        const paypalField = $('#paypal-field');

        const containerContent = paypalField.html();

        const shippingAddress = getCurrentShippingAddress();

        const validationResult = validateShippingAddress(shippingAddress);

        if (!validationResult.isValid) {
          showAddressError(validationResult.errorMessage);
          return null;
        }

        paypalField.empty();
        paypalField.removeAttr('data-paypal-button');

        hideAddressError();

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
            amount: String(hipay_config_paypal.amount),
            customerShippingInformation: {
              zipCode: shippingAddress.zipCode,
              city: shippingAddress.city,
              country: shippingAddress.country,
              streetaddress: shippingAddress.streetaddress,
              streetaddress2: shippingAddress.streetaddress2 || '',
              firstname: shippingAddress.firstname || '',
              lastname: shippingAddress.lastname || ''
            }
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

        const instance = hipaySDK.create(method, options);

        if (instance && typeof instance.catch === 'function') {
          instance.catch((error) => {
            showAddressError(
              hipay_config_paypal.i18n.unableToInitialize
            );
            $('#paypal-field').empty();
          });
        }

        return instance;
      } catch (error) {
        showAddressError(
          hipay_config_paypal.i18n.unableToInitialize
        );
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
        hideAddressError();
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
    let addressChangeTimeout = null;
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

      if (!isOrderPayPage) {
        const addressFields = [
          '#shipping_postcode',
          '#shipping_city',
          '#shipping_country',
          '#shipping_address_1',
          '#billing_postcode',
          '#billing_city',
          '#billing_country',
          '#billing_address_1'
        ].join(', ');

        $(document.body).on('change blur input', addressFields, function() {
          const selectedMethod = checkoutUtils.getSelectedMethod();
          const fieldId = $(this).attr('id');
          const fieldValue = this.value;

          if (selectedMethod === 'paypal' && paypal_version?.v2 !== null) {
            if (addressChangeTimeout) {
              clearTimeout(addressChangeTimeout);
            }

            addressChangeTimeout = setTimeout(() => {
              paypalIntegration.updateMethods(selectors);
              addressChangeTimeout = null;
            }, 800);
          }
        });
      }
    };

    return {
      bindEvents
    };
  })();

  // Initialize the checkout process
  checkoutEventHandlers.bindEvents();
});
