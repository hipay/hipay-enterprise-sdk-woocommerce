/**
 * HiPay Fullservice library to tokenize credit cards
 */
var HiPay = (function (HiPay, reqwest) {

    HiPay.allowedParameters = {
        'card_number':true,
        'card_holder':true,
        'card_expiry_month':true,
        'card_expiry_year':true,
        'cvc':true,
        'multi_use':true,
        'generate_request_id':true
    };

    HiPay.target = 'production';
    HiPay.username = '';
    HiPay.publicKey = '';

    HiPay.isCardNumberValid = function (value) {
        // accept only digits, dashes or spaces
        if (/[^0-9-\s]+/.test(value)) return false;

        // The Luhn Algorithm. It's so pretty.
        var nCheck = 0, nDigit = 0, bEven = false;
        value = value.replace(/\D/g, "");

        for (var n = value.length - 1; n >= 0; n--) {
            var cDigit = value.charAt(n),
                nDigit = parseInt(cDigit, 10);

            if (bEven) {
                if ((nDigit *= 2) > 9) nDigit -= 9;
            }

            nCheck += nDigit;
            bEven = !bEven;
        }

        return (nCheck % 10) == 0;
    };

    HiPay.isValid = function (params) {
        var errors = {'code':0, 'message':''};
        var unallowedParams = [];
        for (key in params) {
            if (HiPay.allowedParameters[key] != true) {
                unallowedParams.push(key);
            }
        }

        if (unallowedParams.length > 0) {

            errors.code = 408;
            var message = 'unallowed parameters: {'
            for (key in unallowedParams) {
                console.log(unallowedParams[key]);
                message += unallowedParams[key] + ' ';
            }
            message += '}';
            message += ' allowed parameters are: {';

            for (key in HiPay.allowedParameters) {
                message += key;
                message += ' ';
            }
            message += '}';

            errors.message = message;
        }

        if ( ! HiPay.isCardNumberValid(params['card_number']) ) {
            errors.code = 409;
            errors.message = 'cardNumber is invalid : luhn check failed';
        }

        return errors;
    };

    HiPay.setTarget = function(target) {
        HiPay.target = target;
    };

    HiPay.getTarget = function() {
        return HiPay.target;
    };

    HiPay.setCredentials = function(username, publicKey) {
        HiPay.username = username;
        HiPay.publicKey = publicKey;
    };

    HiPay.create = function(params, fn_success, fn_failure) {
        if(params['card_expiry_month'].length < 2) {
            params['card_expiry_month'] = '0' + params['card_expiry_month'];
        }
        if(params['card_expiry_year'].length == 2) {
            params['card_expiry_year'] = '20' + params['card_expiry_year'];
        }
        errors = HiPay.isValid(params);
        if ( errors.code != 0 ) {
            fn_failure(errors);
        } else {

            var endpoint = 'https://secure2-vault.hipay-tpp.com/rest/v2/token/create.json';
            if (HiPay.getTarget() == 'test' || HiPay.getTarget() == 'stage' ) {
                endpoint = 'https://stage-secure2-vault.hipay-tpp.com/rest/v2/token/create.json';
            } else if (HiPay.getTarget() == 'dev') {
                endpoint = 'http://dev-secure2-vault.hipay-tpp.com/rest/v2/token/create.json';
            }

            if (!("generate_request_id" in params)) {
                params['generate_request_id'] = 0;
            }

            //ie 8 9 debug
            if ('XDomainRequest' in window && window.XDomainRequest !== null) {

                params['Authorization'] = 'Basic ' + window.btoa(HiPay.username + ':' + HiPay.publicKey);



                var xdr;
                function err() {
                    fn_failure({message: 'Une erreur est survenue.'});
                }
                function timeo() {
                    fn_failure({message: 'Une erreur est survenue.'});
                }
                function loadd() {
                	var resp = xdr.responseText;
                    resp = JSON.parse(resp);
                    if (typeof resp['code'] != 'undefined') {
                        fn_failure({code: resp['code'], message: resp['message']});
                    } else {
                        fn_success(resp);
                    }
                }
                function stopdata() {
                    xdr.abort();
                }
                xdr = new XDomainRequest();
                if (xdr) {
                    xdr.onerror = err;
                    xdr.ontimeout = timeo;
                    xdr.onload = loadd;
                    xdr.timeout = 10000;
                    xdr.open('POST',endpoint);
                    xdr.send(JSON.stringify(params));
                    //xdr.send('foo=<?php echo $foo; ?>'); to send php variable
                } else {
                    fn_failure({message: 'Une erreur est survenue.'});
                }

            } else {

                reqwest({
                    url: endpoint,
                    // crossOrigin: true,
                    // contentType: 'application/x-www-form-urlencoded',
                    // method: 'post',
                    // withCredentials: true,
                    // 'contentType': 'application/json',
                    crossOrigin: true,
                    method: 'post',
                    headers: {
                        'Authorization': 'Basic ' + window.btoa(HiPay.username + ':' + HiPay.publicKey)
                        // 'accept': 'application/json'
                    },
                    data: params,
                    success: function (resp) {

                        if (typeof resp['code'] != 'undefined') {
                            fn_failure({code: resp['code'], message: resp['message']});
                        } else {
                            fn_success(resp);
                        }
                    },
                    error: function (err) {
                        obj = JSON.parse(err['response']);
                        fn_failure({code: obj['code'], message: obj['message']});
                    }
                });
            }
        }
    };

    return HiPay;

} (HiPay || {}, reqwest));