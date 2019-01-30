import giropayJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/giropay.json';

describe('Pay by Giropay', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("giropay");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm("DE");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Giropay', function () {

        cy.get('[for="payment_method_hipayenterprise_giropay"]').click({force: true});
        cy.get('#giropay-issuer_bank_id').type(giropayJson.data.bic);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payGiropay', giropayJson.url, "giropay");
    });
});
