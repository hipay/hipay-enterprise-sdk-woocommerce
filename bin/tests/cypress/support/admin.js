/**
 * Log to Woocomerce Admin
 */
Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/wp-admin');
    cy.wait(300);
    cy.get('body').then(($body) => {
        if ($body.find('#user_login').length) {
            cy.get('#user_login').type("admin-wordpress@hipay.com");
            cy.get('#user_pass').type('hipay');
            cy.get('#wp-submit').click();
        }
    });
});

/**
 *  Connect and go to detail of an order
 */
Cypress.Commands.add("logAndGoToDetailOrder", (lastOrderId) => {
    cy.logToAdmin();
    cy.goToOrderTab();
    cy.goToDetailOrder(lastOrderId);
});

/**
 * Logout from admin
 */
Cypress.Commands.add("adminLogOut", () => {
    cy.get('#wp-admin-bar-logout > .ab-item').click({force: true});
});

Cypress.Commands.add('addClient', (customerLang) => {

    let customerFixture = "customerFR";

    if (customerLang != undefined) {
        customerFixture = "customer" + customerLang;
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.visit('/wp-admin/user-new.php');
        cy.get("#first_name").type(customer.firstName);
        cy.get("#last_name").type(customer.lastName);
        cy.get("#user_login").type(customer.email);
        cy.get("#email").type(customer.email);
        cy.get('button.wp-generate-pw').click();
        cy.get("#pass1-text").type(customer.password);
        cy.get('.pw-checkbox').click();
        cy.get("#role").select('Customer');
        cy.get("#createusersub").click();
    });
});

/**
 * Go to Tab for Payment
 */
Cypress.Commands.add("goToPaymentsTab", () => {
    // cy.get('#toplevel_page_woocommerce > .wp-submenu > :nth-child(5) > a').click({force: true});
    // cy.get('[href="' + Cypress.config('baseUrl') + '/wp-admin/admin.php?page=wc-settings&tab=checkout"]').click({force: true});
    cy.visit('wp-admin/admin.php?page=wc-settings&tab=checkout"');
});

Cypress.Commands.add("activateBasket", () => {
    cy.get('#methods-tab').click();
    cy.get('#activate_basket').check();
    cy.get('.submit > .button-primary').click();
});

/**
 * Go to Tab for Payment
 */
Cypress.Commands.add("switchWooCurrency", (currency) => {
    cy.get('#toplevel_page_woocommerce > .wp-submenu > :nth-child(5) > a').click({force: true});
    cy.get('#woocommerce_currency').select(currency, {force: true});
    cy.get('button[name="save"]').click();
});

Cypress.Commands.add("activateBasket", () => {
    cy.get('#methods-tab').click();
    cy.get('#activate_basket').check();
    cy.get('.submit > .button-primary').click();
});

/**
 * Go to Tab for Payment
 */
Cypress.Commands.add("switchWooCurrency", (currency) => {
    cy.get('#toplevel_page_woocommerce > .wp-submenu > :nth-child(5) > a').click({force: true});
    cy.get('#woocommerce_currency').select(currency, {force: true});
    cy.get('button[name="save"]').click();
});

/**
 *  Go to Hipay configuration
 */
Cypress.Commands.add("activateAstropayMethods", () => {
    cy.get('#enableAstropay').check({force: true});
    cy.get('button[name="save"]').click();
});

Cypress.Commands.add("goToAdminHipayConfig", () => {
    cy.get('[data-gateway_id="hipayenterprise_credit_card"] > .name > .wc-payment-gateway-method-title').click({force: true});
});

/**
 *  Activate Payment Methods
 */
Cypress.Commands.add("activatePaymentMethods", (method) => {
    cy.get(
        '[data-gateway_id="hipayenterprise_' + method + '"] > .status > .wc-payment-gateway-method-toggle-enabled > .woocommerce-input-toggle'
    ).then(($btn) => {
        if ($btn.hasClass('woocommerce-input-toggle--disabled')) {
            $btn.click();
        }
    });
});

/**
 * Got to order list
 */
Cypress.Commands.add("goToOrderTab", () => {
    cy.get('#toplevel_page_woocommerce > .wp-submenu > li.wp-first-item > .wp-first-item').click({force: true});
});

/**
 * Go to Order detail
 */
Cypress.Commands.add("goToDetailOrder", (id) => {
    cy.get('#post-' + id + ' > .order_number > .order-view > strong').click();
});

/**
 * Check Authorization
 */
Cypress.Commands.add("checkAuthorizationStatusMessage", () => {
    cy.get(".note_content").contains("HiPay status: 116");
    cy.get(".note_content").contains("Order status changed from Pending payment to On hold.");
});

/**
 * Check notification
 */
Cypress.Commands.add("checkCaptureStatusMessage", () => {
    cy.get(".note_content").contains("HiPay status: 117");
    cy.get(".note_content").contains("HiPay status: 118");
    cy.get(".note_content").contains("Order status changed from On hold to Completed.");
});

/**
 * Reset configuration for CC Method
 */
Cypress.Commands.add("resetCCConfigForm", () => {
    cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_visa_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_mastercard_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_cb_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_maestro_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_american-express_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_bcmc_activated').check({force: true});
});

/**
 *  Reset field for production
 */
Cypress.Commands.add("resetProductionConfigForm", () => {
    cy.get('#woocommerce_hipayenterprise_account_production_username').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_password').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_tokenjs_username').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_password_publickey').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_secret_passphrase').clear();
});

/**
 * Configure and Activate Hosted Fields
 *
 * Log Admin and configure and activate credit card with Hosted Fields
 * At the end, we are always on configuration page
 *
 */
Cypress.Commands.add("configureAndActivateHostedFields", () => {
    cy.logToAdmin();
    cy.goToPaymentsTab();
    cy.activatePaymentMethods("credit_card");
    cy.goToAdminHipayConfig();
    cy.get('#methods-tab').click();
    cy.get('#operating_mode').select("hosted_fields");
});

/**
 * Configure and Activate Capture Mode
 */
Cypress.Commands.add("configureAndSetCaptureMode", (mode) => {
    cy.logToAdmin();
    cy.goToPaymentsTab();
    cy.goToAdminHipayConfig();
    cy.get('#methods-tab').click();
    cy.get('#capture_mode').select(mode);
});

Cypress.Commands.add("activateOneClick", () => {
    cy.logToAdmin();
    cy.visit('/wp-admin/admin.php?page=wc-settings&tab=checkout&section=hipayenterprise_credit_card');
    cy.get('#methods-tab').click();
    cy.get('#card_token').then(($cardToken) => {
        if(!$cardToken.is(':checked')){
            cy.wrap($cardToken).click();
        }
        cy.get('button').contains('Save changes').click();
    });
});

Cypress.Commands.add("setCaptureMode", (mode) => {
    cy.logToAdmin();
    cy.visit('/wp-admin/admin.php?page=wc-settings&tab=checkout&section=hipayenterprise_credit_card');
    cy.get('#methods-tab').click();
    cy.get('#capture_mode').select(mode);
    cy.get('button').contains('Save changes').click();
});

Cypress.Commands.add("setSkipOnhOld", (state) => {
    cy.logToAdmin();
    cy.visit('/wp-admin/admin.php?page=wc-settings&tab=checkout&section=hipayenterprise_credit_card');
    cy.get('#methods-tab').click();
    cy.get('#skip_onhold').then((checkbox) => {
        if(checkbox.is(':checked') !== state){
            cy.wrap(checkbox).click();
        }
    });
    cy.get('button').contains('Save changes').click();
});


/**
 * Clear All configuration for credit Card (Min Amount etc) ,save and log out form admin
 * At the end ready for checkout on front end
 */
Cypress.Commands.add("saveConfigurationAndLogOut", () => {
    cy.resetCCConfigForm();
    cy.get('.submit > .button-primary').click();
    cy.adminLogOut();
});

Cypress.Commands.add("mapCategories", () => {
    cy.get('#toplevel_page_hipay-settings.wp-has-submenu a.wp-first-item').contains("Mapping category");
    cy.get('#toplevel_page_hipay-settings.wp-has-submenu a.wp-first-item').click({force: true});
    cy.get('table.table-striped tbody tr:nth-child(1) select.form-control').select("Home & Gardening");
    cy.get('table.table-striped tbody tr:nth-child(2) select.form-control').select("Home appliances");
    cy.get('table.table-striped tbody tr:nth-child(3) select.form-control').select("Home appliances");
    cy.get('table.table-striped tbody tr:nth-child(4) select.form-control').select("Home appliances");
    cy.get('table.table-striped tbody tr:nth-child(5) select.form-control').select("Home appliances");
    cy.get('table.table-striped tbody tr:nth-child(6) select.form-control').select("Home & Gardening");
    cy.get('.button-primary').click();
    cy.get('#message').contains("Your settings have been saved.");
});

Cypress.Commands.add("mapCarriers", () => {
    cy.get('#toplevel_page_hipay-settings.wp-has-submenu a ').contains("Mapping delivery method").click({force: true});
    cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').clear();
    cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').clear();
    cy.get('.button-primary').click();
    cy.get('#message').contains("Your settings have been saved.");
    cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').type("1");
    cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').type("2");
    cy.get('table.table-striped tbody tr:nth-child(1) select[name="mapping_mode_flat_rate"]').select("store");
    cy.get('table.table-striped tbody tr:nth-child(1) select[name="mapping_shipping_flat_rate"]').select("standard");
    cy.get('.button-primary').click();
    cy.get('#message').contains("Your settings have been saved.");
});

Cypress.Commands.add('deleteClients', () => {
    cy.visit('/wp-admin/users.php');

    cy.get('#cb-select-all-1').click();
    cy.get('#bulk-action-selector-top').select('Delete');
    cy.get('#doaction').click();

    cy.get('body').then(($body) => {
        if ($body.find('#delete_option0').length) {
            cy.get('#delete_option0').click();
            cy.get('#submit').click();
        }
    });
});

Cypress.Commands.add('setInStock', (productId, inStock) => {
    cy.visit('/wp-admin/post.php?post=' + productId + '&action=edit');

    cy.get('.inventory_options').click();

    if (inStock) {
        cy.get('#_stock_status').select('In stock');
    } else {
        cy.get('#_stock_status').select('On backorder');
    }

    cy.get('#publish').click();
});

Cypress.Commands.add("getLastOrderRequest", () => {
    cy.getAllRequests().then((requests) => {
        requests.reverse();
        for (let request of requests) {
            if (request.orderid) {
                return request;
            }
        }

        return null;
    });
});

Cypress.Commands.add("getOrderRequest", (orderId) => {
    let goodOrder = null;
    let regex = new RegExp(orderId + ".*");
    cy.getAllRequests().then((requests) => {
        for (let request of requests) {
            if (request.orderid && request.orderid.match(regex)) {
                goodOrder = request;
            }
        }

        return goodOrder;
    });
});


Cypress.Commands.add("getAllRequests", () => {
    cy.visit('/wp-admin/admin.php?page=wc-status&tab=logs');
    cy.get('select[name="log_file"] option').contains('hipayenterprise_credit_card-request').then(($option) => {
        cy.get('select[name="log_file"]').select($option.text());
        cy.get('button').contains('View').click();

        cy.get('#log-viewer pre')
            .invoke('text')
            .then((text) => {
                let rawLogArray = text.split(/^20[0-9]{2}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2} DEBUG /gm);
                let logArray = [];
                for (let rawLog of rawLogArray) {
                    if (rawLog !== "") {
                        let logJson = rawLog.replace(/\\/gm, '\\\\"')
                            .replace(/"/gm, '\\"')
                            .replace(/ => /gm, '": "')
                            .replace(/^\s*\[(.*)\]": /gm, '"$1": ')
                            .replace(/(.)$/gm, '$1",')
                            .replace(/\s*"?Array",\s*\(",/gm, '{')
                            .replace(/^\s*\)",\s*$/gm, '},')
                            .replace(/,\s*}/gm, '}')
                        logJson = logJson.substr(0, logJson.length - 1);

                        let log = JSON.parse(logJson);
                        logArray.push(log);
                    }
                }

                cy.log(logArray).then(() => {
                    return logArray;
                });
            });
    });

});