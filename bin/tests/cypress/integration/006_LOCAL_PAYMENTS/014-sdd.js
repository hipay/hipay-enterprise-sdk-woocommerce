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
        cy.wait(3000);
        cy.fill_hostedfield_sdd_form('B2C');

        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });

    it('Wrong form fields SEPA Direct Debit', function () {

        cy.get('[for="payment_method_hipayenterprise_sdd"]').click({force: true});
        cy.wait(3000);
        cy.fill_hostedfield_sdd_form('B2C', {
            gender: "M",
            lastname: "&",
            bank_name: "ccc",
            iban: "deez"
        });

        cy.get('#place_order').click({force: true});

        cy.checkHostedFieldsInlineError("Firstname is missing.", "sdd", "firstname");

        cy.checkHostedFieldsInlineError("Text field contains invalid characters.", "sdd", "lastname");

        cy.checkHostedFieldsInlineError("IBAN is invalid", "sdd", "iban");

    });
});
