import santanderHomeBankingJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/santander-home-banking.json';
import santanderCashJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/santander-cash";

describe('Pay by Santander HomeBanking', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.activateAstropayMethods();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("santander_home_banking");
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

    it('Pay by Santander HomeBanking', function () {

        cy.get('[for="payment_method_hipayenterprise_santander_home_banking"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("#hipay-santander-home-banking-field-national_identification_number", santanderHomeBankingJson.data.national_identification_number);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('paySantanderCash', santanderHomeBankingJson.url, "santander-home-banking");
    });
});
