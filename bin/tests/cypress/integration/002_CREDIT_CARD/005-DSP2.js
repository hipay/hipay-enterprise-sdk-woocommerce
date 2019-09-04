/**
 * Functionality tested
 *  - Populating DSP2 fields when they should be with the right values
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';

describe('DSP2 field population', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Makes an authenticated order with virtual products', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.addClient();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.customerLogIn();

        cy.goToCheckout();

        cy.fillBillingForm();

        cy.fill_hostedfield_card('visa_ok');

        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();

        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];

            cy.customerLogOut();
            cy.logToAdmin();
            cy.getOrderRequest(orderId).then((request) => {
                cy.adminLogOut();

                cy.log(request);
                expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                // Merchant risk statement
                expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                expect(request.merchant_risk_statement.reorder_indicator).to.match(/[12]/, "[merchant_risk_statement.reorder_indicator]");
                expect(request.merchant_risk_statement.shipping_indicator).to.eq("1", "[merchant_risk_statement.shipping_indicator]");
                expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                let d = new Date();
                let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                // Account info
                //  -> Customer
                expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                //  -> Purchase
                expect(request.account_info.purchase.count).to.match(/(.*)/, "[account_info.purchase.count]");
                expect(request.account_info.purchase.card_stored_24h).to.match(/(.*)/, "[account_info.purchase.card_stored_24h]");
                expect(request.account_info.purchase.payment_attempts_24h).to.match(/(.*)/, "[account_info.purchase.payment_attempts_24h]");
                expect(request.account_info.purchase.payment_attempts_1y).to.match(/(.*)/, "[account_info.purchase.payment_attempts_1y]");
                //  -> Payment
                expect(request.account_info.payment.enrollment_date).to.match(/(.*)/, "[account_info.payment.enrollment_date]");
                //  -> Shipping
                expect(request.account_info.shipping.shipping_used_date).to.match(/(.*)/, "[account_info.shipping.shipping_used_date]");
                expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                // Device Channel
                expect(request.device_channel).to.eq("2", "[device_channel]");

                // Browser info
                expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
            });
        });
    });

    it('Makes an authenticated order with one-click', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.addClient();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.activateOneClick();
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.customerLogIn();

        cy.goToCheckout();

        cy.fillBillingForm();

        cy.fill_hostedfield_card('visa_ok');
        cy.get('#wc-hipayenterprise_credit_card-new-payment-method').click();
        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });


    it('Connect to BO stage and send authorization', function () {

        cy.fixture('order').then((order) => {
            // Send 116 notif to save card
            cy.connectAndSelectAccountOnHipayBO();

            cy.openTransactionOnHipayBO(order.lastOrderId + "-");
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
                cy.log(this.lastTransactionId);
                order.lastTransactionId = this.lastTransactionId;
                cy.writeFile('cypress/fixtures/order.json', order);
            });

            cy.openNotificationOnHipayBO(118).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
            });
        });
    });

    it('Makes an authenticated order with reorder and one-click', function () {
        cy.logToAdmin();

        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.activateOneClick();
        cy.adminLogOut();

        cy.customerLogIn();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.goToCheckout();

        cy.fillBillingForm();

        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();
        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];

            cy.customerLogOut();
            cy.logToAdmin();
            cy.getOrderRequest(orderId).then((request) => {
                cy.adminLogOut();

                cy.log(request);
                cy.fixture('order').then((order) => {

                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    expect(request.previous_auth_info.transaction_reference).to.eq(order.lastTransactionId, "[previous_auth_info.transaction_reference]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("2", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("1", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("1", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("1", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("1", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("1", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.eq(today, "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.eq(today, "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });

    it('Makes an authenticated order with out of stock products, no virtual products and an account name different from the shipping name', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.addClient();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, false);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);

        cy.customerLogIn();

        cy.goToCheckout();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.wait(2000);
        cy.fill_hostedfield_card('visa_ok');

        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();

        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];

            cy.customerLogOut();
            cy.logToAdmin();
            cy.getOrderRequest(orderId).then((request) => {
                cy.adminLogOut();

                cy.log(request);
                expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                // Merchant risk statement
                expect(request.merchant_risk_statement.email_delivery_address).to.eq("", "[merchant_risk_statement.email_delivery_address]");
                expect(request.merchant_risk_statement.delivery_time_frame).to.eq("", "[merchant_risk_statement.delivery_time_frame]");
                expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                expect(request.merchant_risk_statement.reorder_indicator).to.eq("1", "[merchant_risk_statement.reorder_indicator]");
                expect(request.merchant_risk_statement.shipping_indicator).to.match(/[23]/, "[merchant_risk_statement.shipping_indicator]");
                expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                let d = new Date();
                let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                // Account info
                //  -> Customer
                expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                //  -> Purchase
                expect(request.account_info.purchase.count).to.match(/(.*)/, "[account_info.purchase.count]");
                expect(request.account_info.purchase.card_stored_24h).to.match(/(.*)/, "[account_info.purchase.card_stored_24h]");
                expect(request.account_info.purchase.payment_attempts_24h).to.match(/(.*)/, "[account_info.purchase.payment_attempts_24h]");
                expect(request.account_info.purchase.payment_attempts_1y).to.match(/(.*)/, "[account_info.purchase.payment_attempts_1y]");
                //  -> Payment
                expect(request.account_info.payment.enrollment_date).to.match(/(.*)/, "[account_info.payment.enrollment_date]");
                //  -> Shipping
                expect(request.account_info.shipping.shipping_used_date).to.match(/(.*)/, "[account_info.shipping.shipping_used_date]");
                expect(request.account_info.shipping.name_indicator).to.eq("2", "[account_info.shipping.name_indicator]");

                // Device Channel
                expect(request.device_channel).to.eq("2", "[device_channel]");

                // Browser info
                expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
            });
        });
    });

    it('Makes a non authenticated order with physical products', function () {
        cy.logToAdmin();
        cy.deleteClients();

        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.goToCheckout();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.wait(2000);
        cy.fill_hostedfield_card('visa_ok');


        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();
        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];

            cy.logToAdmin();
            cy.getOrderRequest(orderId).then((request) => {
                cy.adminLogOut();

                cy.log(request);
                cy.fixture('order').then((order) => {

                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq("", "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("3", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq("", "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq("", "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.eq("", "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.eq("", "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("2", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });

    it('Makes a non authenticated order with virtual products', function () {
        cy.logToAdmin();
        cy.deleteClients();

        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.adminLogOut();

        cy.selectVirtualItem(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.fill_hostedfield_card('visa_ok');

        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();
        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];

            cy.logToAdmin();
            cy.getOrderRequest(orderId).then((request) => {
                cy.adminLogOut();

                cy.log(request);
                cy.fixture('order').then((order) => {

                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("5", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq("", "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq("", "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.eq("", "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.eq("", "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });
});

