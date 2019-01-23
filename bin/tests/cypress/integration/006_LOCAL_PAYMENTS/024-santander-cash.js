import santanderCashJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/santander-cash.json';

describe('Pay by Santander Cash', function () {

    before(function () {

        cy.logToAdmin();
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
        cy.get('#santander-cash-national_identification_number')
            .type(santanderCashJson.data.national_identification_number, {force: true})
            .should('have.value', santanderCashJson.data.national_identification_number);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('paySantanderCash', santanderCashJson.url, "santander-cash");
    });


    it('Wrong form fields Santander Cash', function () {

        cy.get('[for="payment_method_hipayenterprise_santander_cash"]').click({force: true});
        cy.get('#santander-cash-national_identification_number')
            .type("1111", {force: true})
            .should('have.value', "1111");
        cy.get('#place_order').click({force: true});

        cy.get('#santander-cash-national_identification_number + .error-text-hp').contains("This is not a correct CPN/CURP");
    });
});
