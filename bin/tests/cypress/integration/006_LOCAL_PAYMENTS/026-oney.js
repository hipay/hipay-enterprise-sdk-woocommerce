import oneyJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/oney.json';

describe('Pay by Oney', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("oney_3xcb");
        cy.activatePaymentMethods("oney_3xcb_no_fees");
        cy.activatePaymentMethods("oney_4xcb");
        cy.activatePaymentMethods("oney_4xcb_no_fees");
        cy.goToAdminHipayConfig();
        cy.activateBasket();
        cy.switchWooCurrency("EUR");
        cy.mapCarriers();
        cy.mapCategories();
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectSeveralItemsAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    ['3xcb', '3xcb_no_fees', '4xcb', '4xcb_no_fees'].forEach((oneyMethod) => {
        it('Pay by : ' + oneyMethod, function () {
            cy.get('[for="payment_method_hipayenterprise_oney_'+oneyMethod+'"]').click({force: true});
            cy.get('#place_order').click({force: true});
            cy.payAndCheck('payOney', oneyJson.url, "oney");
        });
    });
});
