import bnppfData from '../fixtures/bnppf.json'
import cards from '../fixtures/credit-cards.json'

Cypress.Commands.add("payBnppf", () => {

    cy.location('pathname', {timeout: 50000}).should('include', '/souscription.do');

    cy.get("#in_jj").type(bnppfData.dayOfBirth);
    cy.get("#in_mm").type(bnppfData.monthOfBirth);
    cy.get("#in_aaaa").type(bnppfData.yearOfBirth);
    cy.get("#in_pays").select(bnppfData.countryOfBirth);
    cy.get("#in_departement").type(bnppfData.stateOfBirth);
    cy.get("#in_ville").select(bnppfData.cityOfBirth);


    cy.get("#ji_nationalite").select(bnppfData.nationality);
    cy.get("#ji_type").select(bnppfData.IdType);
    cy.get("#ji_numero").type(bnppfData.IdNumber);
    cy.get("#ji_jj").type(bnppfData.deliveryDay);
    cy.get("#ji_mm").type(bnppfData.deliveryMonth);
    cy.get("#ji_aaaa").type(bnppfData.deliveryYear);

    cy.get("#cb_pan1").type(cards.visa.number.slice(0, 4));
    cy.get("#cb_pan2").type(cards.visa.number.slice(4, 8));
    cy.get("#cb_pan3").type(cards.visa.number.slice(8, 12));
    cy.get("#cb_pan4").type(cards.visa.number.slice(12, 16));
    cy.get("#cb_mm").type(cards.visa.expiryMonth);
    cy.get("#cb_aa").type(cards.visa.expiryYear.slice(2, 4));
    cy.get("#cb_cvx").type(cards.visa.cvv);

    cy.get("#i_mentions").click({force: true});
    cy.get("#bt-next").click();
    cy.get("#valider-paiement-haut").click();
});
