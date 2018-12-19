import cards from '@hipay/hipay-cypress-utils/fixtures/payment-means/card';

describe('Pay by credit card iframe', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_page");
        cy.get('#display_hosted_page').select("iframe");

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();
    });

    beforeEach(function () {

        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('.payment_method_hipayenterprise_credit_card > label').click({force: true});
        cy.get('#place_order').click({force: true});
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('pay by visa', function () {
        cy.get('#wc_hipay_iframe').then(function ($iframe) {
            cy.payCcIframe($iframe, cards.visa.ok);
        });
        cy.checkOrderSuccess();
    });

    it('pay by visa refused', function () {
        cy.get('#wc_hipay_iframe').then(function ($iframe) {
            cy.payCcIframe($iframe, cards.visa.refused);
        });
        cy.checkOrderCancelled();
    });
});
