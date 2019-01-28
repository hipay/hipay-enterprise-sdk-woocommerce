import sddJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/sdd.json';

describe('Pay by SEPA Direct Debit', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("sdd");
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

    it('Pay by SEPA Direct Debit', function () {

        cy.get('[for="payment_method_hipayenterprise_sdd"]').click({force: true});
        cy.get('#sdd-gender').type("M", {force: true});
        cy.get('#sdd-firstname').type("John", {force: true});
        cy.get('#sdd-lastname').type("Doe", {force: true});
        cy.get('#sdd-bank_name').type(sddJson.data.bank_name, {force: true});
        cy.get('#sdd-iban').type(sddJson.data.iban, {force: true});
        cy.get('#sdd-issuer_bank_id').type(sddJson.data.bic, {force: true});
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });
});
