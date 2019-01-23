import postfinanceEfinanceJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/postfinance-card.json';

describe('Pay by PostFinance E-Finance', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("postfinance_efinance");
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

    it('Pay by PostFinance E-Finance', function () {

        cy.get('.payment_method_hipayenterprise_postfinance_efinance > label').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payPostfinanceEfinance', postfinanceEfinanceJson.url, "postfinance-efinance");
    });
});
