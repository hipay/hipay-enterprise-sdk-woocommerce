import bbvaBancomerJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/bbva-bancomer.json';
import bancoDoBrasilJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/banco-do-brasil";

describe('Pay by BBVA Bancomer', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.activateAstropayMethods();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("bbva_bancomer");
        cy.switchWooCurrency("MXN");
        cy.adminLogOut();
    });

    after(function () {

        cy.logToAdmin();
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm("MX");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by BBVA Bancomer', function () {

        cy.get('[for="payment_method_hipayenterprise_bbva_bancomer"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("#hipay-bbva-bancomer-field-national_identification_number", bbvaBancomerJson.data.national_identification_number);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBbvaBancomer', bbvaBancomerJson.url, "bbva-bancomer");
    });
});
