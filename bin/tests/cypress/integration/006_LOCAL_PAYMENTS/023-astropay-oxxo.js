import oxxoJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/oxxo.json';
import itauJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/itau";

describe('Pay by Oxxo', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.activateAstropayMethods();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("oxxo");
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

    it('Pay by Oxxo', function () {

        cy.get('[for="payment_method_hipayenterprise_oxxo"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("#hipay-oxxo-field-national_identification_number", oxxoJson.data.national_identification_number);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payOxxo', oxxoJson.url, "oxxo");
    });
});
