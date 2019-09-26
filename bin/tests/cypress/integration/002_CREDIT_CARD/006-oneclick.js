/**
 * Functionality tested
 *  - Oneclick card registration
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';

describe('Oneclick card registration', function () {

    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Makes an authenticated order with one-click', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.addClient();
        cy.setInStock(29, true);
        cy.activateOneClick();
        cy.adminLogOut();

        cy.selectShirtItem(1);

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
            });
        });
    });

    it('Makes an authenticated order with reorder and one-click', function () {
        cy.logToAdmin();

        cy.setInStock(29, true);
        cy.activateOneClick();
        cy.adminLogOut();

        cy.customerLogIn();

        cy.selectShirtItem(1);

        cy.goToCheckout();

        cy.get('*[name="wc-hipayenterprise_credit_card-payment-token"]').then(($input) => {
            expect($input).to.exist;
            expect($input).to.be.visible;
        });
    });

    it('Makes an authenticated order with one-click', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.addClient();
        cy.setInStock(29, true);
        cy.activateOneClick();
        cy.adminLogOut();

        cy.selectShirtItem(1);

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
            // Send 118 notif to save card
            cy.connectAndSelectAccountOnHipayBO();

            cy.openTransactionOnHipayBO(order.lastOrderId + "-");
            cy.openNotificationOnHipayBO(118).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
            });
        });
    });

    it('Makes an authenticated order with reorder and one-click', function () {
        cy.logToAdmin();

        cy.setInStock(29, true);
        cy.activateOneClick();
        cy.adminLogOut();

        cy.customerLogIn();

        cy.selectShirtItem(1);

        cy.goToCheckout();

        cy.get('*[name="wc-hipayenterprise_credit_card-payment-token"]').then(($input) => {
            expect($input).to.exist;
            expect($input).to.be.visible;
        });
    })
});