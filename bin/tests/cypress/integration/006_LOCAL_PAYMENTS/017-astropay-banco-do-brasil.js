import bancoDoBrasilJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/banco-do-brasil.json';
import banamexJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/banamex";

describe('Pay by Banco Do Brasil', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.activateAstropayMethods();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("banco_do_brasil");
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

    it('Pay by Banco Do Brasil', function () {

        cy.get('[for="payment_method_hipayenterprise_banco_do_brasil"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("#hipay-banco-do-brasil-field-national_identification_number", bancoDoBrasilJson.data.national_identification_number);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBancoDoBrasil', bancoDoBrasilJson.url, "banco-do-brasil");
    });
});
