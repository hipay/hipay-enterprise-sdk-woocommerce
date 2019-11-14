/**
 * Functionality tested
 *  - Skip on-hold status
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';

describe('Skip on hold ', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Test order with skip on-hold status and automatic capture', function () {
        cy.logToAdmin();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.setCaptureMode('automatic');
        cy.setSkipOnhOld(true);
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
                    cy.get("#select2-order_status-container").then(($msgText) => {
                        expect($msgText.text()).to.match(/Pending payment/);
                    });

                    cy.get('.note_content').then(($msgText) => {
                        expect($msgText.text()).to.match(/HiPay status: 116/);
                    });

                    cy.connectOnHipayBOFromAnotherDomain();
                    cy.openTransactionOnHipayBO(idOrder + "-");
                    cy.openNotificationOnHipayBO(118).then(() => {
                        cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                            cy.logAndGoToDetailOrder(idOrder);
                            cy.get("#select2-order_status-container").then(($msgText) => {
                                expect($msgText.text()).to.match(/Processing/);
                            });

                            cy.get('.note_content').then(($msgText) => {
                                expect($msgText.text()).to.match(/HiPay status: 118/);
                                expect($msgText.text()).to.match(/Order status changed from Pending payment to Processing\./);
                            });
                        });
                    });
                });
            });

        });
    });

    it('Test order without skip on-hold status and automatic capture', function () {
        cy.logToAdmin();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.setCaptureMode('automatic');
        cy.setSkipOnhOld(false);
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
                    cy.get("#select2-order_status-container").then(($msgText) => {
                        expect($msgText.text()).to.match(/On hold/);
                    });

                    cy.get('.note_content').then(($msgText) => {
                        expect($msgText.text()).to.match(/HiPay status: 116/);
                        expect($msgText.text()).to.match(/Order status changed from Pending payment to On hold\./);
                    });

                    cy.connectOnHipayBOFromAnotherDomain();
                    cy.openTransactionOnHipayBO(idOrder + "-");
                    cy.openNotificationOnHipayBO(118).then(() => {
                        cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                            cy.logAndGoToDetailOrder(idOrder);
                            cy.get("#select2-order_status-container").then(($msgText) => {
                                expect($msgText.text()).to.match(/Processing/);
                            });

                            cy.get('.note_content').then(($msgText) => {
                                expect($msgText.text()).to.match(/HiPay status: 118/);
                                expect($msgText.text()).to.match(/Order status changed from On hold to Processing\./);
                            });
                        });
                    });
                });
            });

        });
    });

    it('Test order with skip on-hold status and manual capture', function () {
        cy.logToAdmin();
        cy.setInStock(29, true);
        cy.setInStock(16, true);
        cy.setInStock(21, true);
        cy.setCaptureMode('manual');
        cy.setSkipOnhOld(true);
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
                    cy.get("#select2-order_status-container").then(($msgText) => {
                        expect($msgText.text()).to.match(/On hold/);
                    });

                    cy.get('.note_content').then(($msgText) => {
                        expect($msgText.text()).to.match(/HiPay status: 116/);
                        expect($msgText.text()).to.match(/Order status changed from Pending payment to On hold\./);
                    });

                    const stub = cy.stub();

                    cy.get("#woocommerce-order-items  p.add-items > button.button.capture-items").click();
                    cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").type("5");
                    cy.get("#order_line_items > tr:nth-child(3) > td.quantity > div.refund > input").type("3");
                    cy.get("#order_line_items > tr:nth-child(5) > td.quantity > div.refund > input").type("2");
                    cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").focus();
                    cy.get('#capture_amount', {timeout: 10000}).should('have.value', '468,00');
                    cy.on('window:confirm', stub);
                    cy.get('#hipay-capture-items > div.refund-actions > button.button.button-primary.do-api-capture').click();
                    cy.get("#woocommerce-order-items  p.add-items > button.button.capture-items", {timeout: 10000}).should('not.exist');

                    cy.connectOnHipayBOFromAnotherDomain();
                    cy.openTransactionOnHipayBO(idOrder + "-");
                    cy.openNotificationOnHipayBO(118).then(() => {
                        cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                            cy.logAndGoToDetailOrder(idOrder);
                            cy.get("#select2-order_status-container").then(($msgText) => {
                                expect($msgText.text()).to.match(/Processing/);
                            });

                            cy.get('.note_content').then(($msgText) => {
                                expect($msgText.text()).to.match(/HiPay status: 118/);
                                expect($msgText.text()).to.match(/Order status changed from On hold to Processing\./);
                            });
                        });
                    });
                });
            });

        });
    });
});