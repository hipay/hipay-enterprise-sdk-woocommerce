describe('Pay by credit card', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_fields");

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();
    });

    beforeEach(function () {

        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
        cy.get('#hipay-field-cardHolder > iframe');
        cy.wait(3000);
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    ['visa_ok', 'mastercard_ok', 'cb_ok', 'maestro_ok', 'american-express_ok'].forEach((card) => {
        it('Pay by : ' + card, function () {
            cy.fill_hostedfield_card(card);
            cy.get('#place_order').click({force: true});
            cy.checkOrderSuccess();
        });
    });

    it('Pay by Visa refused', function () {
        cy.fill_hostedfield_card('visa_refused');
        cy.get('#place_order').click({force: true});
        cy.checkPaymentRefused();
    });

    it('Pay by BCMC Unsupported', function () {
        cy.fill_hostedfield_card('bcmc_ok');
        cy.get('#place_order').click({force: true});
        cy.checkUnsupportedPayment();
    });

    it('Pay by Wrong credit card number', function () {

        let customCard = {
            "cardHolder": "John Doe",
            "cardNumber": "123465978",
            "expiryMonth": "08",
            "expiryYear": "2024",
            "cvc": "666"
        };

        cy.fill_hostedfield_card('custom', customCard);
        cy.get('#place_order').click({force: true});
        cy.checkHostedFieldsError("Card number is invalid.");
    });

});
