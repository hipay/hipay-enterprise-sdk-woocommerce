import boletoBancarioJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/boleto-bancario.json';
import bbvaBancomerJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/bbva-bancomer";

describe('Pay by Boleto Bancario', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.activateAstropayMethods();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("boleto_bancario");
        cy.switchWooCurrency("BRL");
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
        cy.fillBillingForm("BR");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Boleto Bancario', function () {

        cy.get('[for="payment_method_hipayenterprise_boleto_bancario"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("#hipay-boleto-bancario-field-national_identification_number", boletoBancarioJson.data.national_identification_number);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBoletoBancario', boletoBancarioJson.url, "boleto-bancario");
    });
});
