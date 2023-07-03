import sisalJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/sisal.json';

describe('Pay by Sisal', function () {
  before(function () {
    cy.logToAdmin();
    cy.goToPaymentsTab();
    cy.activatePaymentMethods('sisal');
    cy.switchWooCurrency('EUR');
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

  it('pay Sisal', function () {
    cy.get('[for="payment_method_hipayenterprise_sisal"]').click({
      force: true
    });
    cy.wait(500);
    cy.get('#place_order').click({ force: true });
    cy.payAndCheck('paySisal', sisalJson.url, 'sisal');
  });
});
