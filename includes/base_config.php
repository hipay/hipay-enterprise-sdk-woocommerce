<?php 
//authorized: by hipay, if empty = all
//available:  by merchant, if empty = none
define("HIPAY_ENTERPRISE_PAYMENT_METHODS", '
[
{ "key":"visa", "title":"Visa","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"mastercard", "title":"Mastercard","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"cb", "title":"Carte Bancaire","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"maestro", "title":"Maestro","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"american-express", "title":"American Express","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"bcmc", "title":"Bancontact","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"paypal", "title":"Paypal","is_credit_card":"0","is_local_payment":"1","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"dexia-directnet", "title":"Belfius","is_credit_card":"0","is_local_payment":"1","description":"","max_amount":"","min_amount":"","authorized_countries":"FR,GB","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"giropay", "title":"Giropay","is_credit_card":"0","is_local_payment":"1","description":"","max_amount":"","min_amount":"","authorized_countries":"DE","authorized_currencies":"EUR","is_active":"0","available_countries":"DE" },
{ "key":"ideal", "title":"iDEAL","is_credit_card":"0","is_local_payment":"1","description":"","max_amount":"","min_amount":"","authorized_countries":"NL","authorized_currencies":"EUR","is_active":"0","available_countries":"NL" },
{ "key":"ing-homepay", "title":"ING Home\'Pay","is_credit_card":"0","is_local_payment":"1","description":"","max_amount":"","min_amount":"","authorized_countries":"FR,GB","authorized_currencies":"","is_active":"0","available_countries":"" },
{ "key":"multibanco", "title":"Multibanco","is_credit_card":"0","is_local_payment":"1","description":"Pay Multibanco references on ATM or Homebanking","max_amount":"2500","min_amount":"1","authorized_countries":"PT","authorized_currencies":"EUR","is_active":"0","available_countries":"PT" }
]');

define("HIPAY_ENTERPRISE_LOCAL_PAYMENTS", 'off');
//{ "key":"visa", "title":"Visa","is_credit_card":"1","is_local_payment":"0","description":"","max_amount":"","min_amount":"","authorized_countries":"","authorized_currencies":"","is_active":"0","available_countries":"" }