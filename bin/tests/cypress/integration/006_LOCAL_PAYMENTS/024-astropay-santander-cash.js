import santanderCashJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/santander-cash.json';
import oxxoJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/oxxo";

describe('Pay by Santander Cash', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.activateAstropayMethods();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("santander_cash");
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

    it('Pay by Santander Cash', function () {

        cy.get('[for="payment_method_hipayenterprise_santander_cash"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("#hipay-santander-cash-field-national_identification_number", santanderCashJson.data.national_identification_number);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('paySantanderCash', santanderCashJson.url, "santander-cash");
    });

});
