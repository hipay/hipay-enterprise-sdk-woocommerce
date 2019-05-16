// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './admin'
import './checkout'

import '@hipay/hipay-cypress-utils/commands/bo-merchant/login'
import '@hipay/hipay-cypress-utils/commands/bo-merchant/transaction'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/bnpp'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/belfius'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/sisal'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/ing-homepay'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/postfinance-card'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/postfinance-efinance'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/przelewy24'
import '@hipay/hipay-cypress-utils/commands/payment-means/local/mybank'
import '@hipay/hipay-cypress-utils/commands/payment-means/card/hostedpage'
import '@hipay/hipay-cypress-utils/commands/payment-means/card/hostedfields'
import '@hipay/hipay-cypress-utils/commands/payment-means/sdd/hostedfields'
import '@hipay/hipay-cypress-utils/commands/common/hostedfields'

// Alternatively you can use CommonJS syntax:
// require('./commands')
Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false;
});
