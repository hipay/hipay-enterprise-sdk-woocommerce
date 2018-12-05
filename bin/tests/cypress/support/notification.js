Cypress.Commands.add("sendNotification", (notifDta) => {
    cy.fixture('notification').then((notificationConfig) => {
        cy.request({
            method: 'POST',
            url: notificationConfig.url, // baseUrl is prepended to url
            body: notifDta.data,
            form: true,
            headers: {
                [notificationConfig.signatureHeader]: notifDta.hash
            }
        });
    });
});
