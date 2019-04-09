import postfinanceCardJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/postfinance-card.json';

describe('Pay by PostFinance Card', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("postfinance_card");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('pay PostFinance Card', function () {

        cy.get('[for="payment_method_hipayenterprise_postfinance_card"]').click({force: true});
        cy.wait(500);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payPostfinanceCard', postfinanceCardJson.url, "postfinance-card");
    });
});
