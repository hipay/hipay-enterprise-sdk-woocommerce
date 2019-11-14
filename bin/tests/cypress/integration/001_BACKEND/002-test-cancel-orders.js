/**
 * Functionality tested
 *  - Cancel order on woocommerce cancels transaction on BO
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';

describe('Order cancellation', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Test cancel on an order without transaction number', function () {
        cy.logToAdmin();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.setCaptureMode('automatic');
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.goToCheckout();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.wait(2000);
        cy.fill_hostedfield_card('visa_ok');


        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();

        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((idOrder) => {

            cy.logAndGoToDetailOrder(idOrder);
            cy.get('#order_status').select('Cancelled', {force: true});
            cy.get('.save_order').click();

            cy.get('.note_content').then(($msgText) => {
                expect($msgText.text()).to.match(/The HiPay transaction was not canceled because no transaction reference exists. You can see and cancel the transaction directly from HiPay’s BackOffice \(https:\/\/merchant.hipay-tpp.com\/default\/auth\/login\)/);
            });
        });
    });

    it('Test cancel on a captured order', function () {
        cy.logToAdmin();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.setCaptureMode('automatic');
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.goToCheckout();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.wait(2000);
        cy.fill_hostedfield_card('visa_ok');


        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();

        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((idOrder) => {

            cy.connectOnHipayBOFromAnotherDomain();
            cy.openTransactionOnHipayBO(idOrder + "-");
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                    cy.logAndGoToDetailOrder(idOrder);
                    cy.get('#order_status').select('Cancelled', {force: true});
                    cy.get('.save_order').click();

                    cy.get('.note_content').then(($msgText) => {
                        expect($msgText.text()).to.match(/There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay’s BackOffice \(https:\/\/merchant.hipay-tpp.com\/default\/auth\/login\)\nMessage was : \[Action denied : Wrong transaction status]/);
                    });
                });
            });

        });
    });

    it('Test cancel on a cancellable order', function () {
        cy.logToAdmin();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.setCaptureMode('manual');
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.goToCheckout();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.wait(2000);
        cy.fill_hostedfield_card('visa_ok');


        cy.get('#place_order').click({force: true});

        cy.checkOrderSuccess();

        cy.get('.woocommerce-order-overview__order > strong:nth-child(1)').invoke('text').then((idOrder) => {
            cy.connectOnHipayBOFromAnotherDomain();
            cy.openTransactionOnHipayBO(idOrder + "-");
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                    cy.logAndGoToDetailOrder(idOrder);
                    cy.get('#order_status').select('Cancelled', {force: true});
                    cy.get('.save_order').click();
                    cy.adminLogOut();

                    cy.connectOnHipayBOFromAnotherDomain();
                    cy.openTransactionOnHipayBO(idOrder + "-");

                    cy.get('#data-status-message').then(($msgText) => {
                        expect($msgText.text()).to.match(/Authorization cancellation requested/);
                    });

                    cy.logToAdmin();
                    cy.setCaptureMode('automatic');
                    cy.adminLogOut();
                });
            });
        });

    });
});
