/**
 * Functionality tested
 *  - Configuration basket exists
 *  - Process an Payment without mapping (Should be OK)
 *
 */
var utils= require('../../support/utils');
describe('Pay by credit card with basket activated', function () {

    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('basket').as("basket");
    });


    /**
     * Process an payment without mapping ( Transaction should be OK )
     */
    it('Succeed Transaction without mapping', function () {
        cy.processTransactionWithBasket();
    });

    /**
     * Process an payment without mapping ( Transaction should be OK )
     */
    it('Check Basket in transaction', function () {
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(116).then(() => {
            var basketTransaction = utils.fetchInput("basket",decodeURI(this.data));
            assert.equal(basketTransaction,JSON.stringify(this.basket.basketWithoutMapping));
        });
    });


});
