/**
 * Functionality tested
 *  - Configuration basket exists
 *  - Process an Payment without mapping (Should be OK)
 *
 */
var utils= require('../../support/utils');

describe('Pay by credit card with basket activated and mapping ', function () {

    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('basket').as("basket");
    });


    /**
     * Process mapping for category
     */
    it('Hipay mapping Categories', function () {
        cy.configureAndActivateHostedFields();
        cy.get('#activate_basket').check();
        cy.saveConfigurationAndLogOut();
        cy.logToAdmin();
        cy.get('#toplevel_page_hipay-settings.wp-has-submenu a.wp-first-item').contains("Mapping category");
        cy.get('#toplevel_page_hipay-settings.wp-has-submenu a.wp-first-item').click({force:true});
        cy.get('table.table-striped tbody tr:nth-child(1) select.form-control').select("Home & Gardening");
        cy.get('table.table-striped tbody tr:nth-child(2) select.form-control').select("Home appliances");
        cy.get('table.table-striped tbody tr:nth-child(3) select.form-control').select("Home appliances");
        cy.get('table.table-striped tbody tr:nth-child(4) select.form-control').select("Home appliances");
        cy.get('table.table-striped tbody tr:nth-child(5) select.form-control').select("Home appliances");
        cy.get('table.table-striped tbody tr:nth-child(6) select.form-control').select("Home & Gardening");
        cy.get('.button-primary').click();
        cy.get('#message').contains("Your settings have been saved.");
        cy.get('table.table-striped tbody tr:nth-child(1) select.form-control').should('have.value', '1');
        cy.get('table.table-striped tbody tr:nth-child(2) select.form-control').should('have.value', '3');
        cy.get('table.table-striped tbody tr:nth-child(3) select.form-control').should('have.value', '3');
        cy.get('table.table-striped tbody tr:nth-child(4) select.form-control').should('have.value', '3');
        cy.get('table.table-striped tbody tr:nth-child(5) select.form-control').should('have.value', '3');
        cy.get('table.table-striped tbody tr:nth-child(6) select.form-control').should('have.value', '1');
    });

    /**
     * Process mapping for delivery method
     */
    it('Hipay mapping delivery method ( Bad values) ', function () {
        cy.logToAdmin();
        cy.get('#toplevel_page_hipay-settings.wp-has-submenu a ').contains("Mapping delivery method").click({force:true});
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').clear();
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').clear();
        cy.get('.button-primary').click();
        cy.get('#message').contains("Your settings have been saved.");
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').type("BAD");
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').type("BAD");
        cy.get('table.table-striped tbody tr:nth-child(1) select[name="mapping_mode_flat_rate"]').select("store");
        cy.get('table.table-striped tbody tr:nth-child(1) select[name="mapping_shipping_flat_rate"]').select("standard");
        cy.get('.button-primary').click();
        cy.get('#message').contains("Your settings have been saved.");
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').should('have.value', '0');
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').should('have.value', '0');
    });

    /**
     * Process mapping for delivery method
     */
    it('Hipay mapping delivery method ( Good values) ', function () {
        cy.logToAdmin();
        cy.get('#toplevel_page_hipay-settings.wp-has-submenu a ').contains("Mapping delivery method").click({force:true});
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').clear();
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').clear();
        cy.get('.button-primary').click();
        cy.get('#message').contains("Your settings have been saved.");
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').type("1");
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').type("2");
        cy.get('table.table-striped tbody tr:nth-child(1) select[name="mapping_mode_flat_rate"]').select("store");
        cy.get('table.table-striped tbody tr:nth-child(1) select[name="mapping_shipping_flat_rate"]').select("standard");
        cy.get('.button-primary').click();
        cy.get('#message').contains("Your settings have been saved.");
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_order_preparation_flat_rate"]').should('have.value', '1');
        cy.get('table.table-striped tbody tr:nth-child(1) input[name="mapping_delivery_estimated_flat_rate"]').should('have.value', '2');
    });

    /**
     * Process an payment without mapping ( Transaction should be OK )
     */
    it('Succeed Transaction with mapping', function () {
        cy.processTransactionWithBasket();
    });

    /**
     * Process an payment without mapping ( Transaction should be OK )
     */
    it('Check Basket in transaction with mapping', function () {
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(116).then(() => {
            var basketTransaction = utils.fetchInput("basket",decodeURI(this.data));
            cy.log(basketTransaction);
            assert.equal(basketTransaction,JSON.stringify(this.basket.basketWithMapping));
        });
    });

});
