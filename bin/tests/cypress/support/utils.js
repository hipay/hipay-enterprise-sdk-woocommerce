/**
 * Get input from form data query
 *
 * @param identifier
 * @param data
 * @returns {string}
 */
exports.fetchInput =  function(identifier,data) {
    var form_data = data.split('&');
    var valueInput = "";

    Cypress.$.each(form_data, function(key, value) {
        var data = value.split('=');
        if ( identifier == data[0] ) {
            valueInput =  decodeURIComponent(data[1]);
        }
    });
    return valueInput;
};

