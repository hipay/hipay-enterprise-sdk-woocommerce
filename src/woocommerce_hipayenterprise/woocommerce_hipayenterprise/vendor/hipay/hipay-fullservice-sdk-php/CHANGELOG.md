# Version 1.5.1

- Fix : Invert payment method and billing adress in  OrderRequest

# Version 1.5.0
New - Operation response in transaction object (id, reference...)
New - Basket in transaction object
New - Add translation in category collection

# Version 1.4.0
New - Add new fields for 3x/4x payment

# Version 1.3.0
New - Add 2 methods to get transaction details + order's transactions
New - Improvements for support of basket in Order and Maintenance Request
Fix circle CI
Fix order transaction info with single transaction response 

# Version 1.2.1
Fix - Get order transaction info with single transaction response

# Version 1.2.0
New - Add 2 methods to get transaction details + order's transactions
Fix - Delete git index .idea and composer.lock
Fix - PHPUNIT for CircleCI
Fix - PHPDoc return tags

# Version 1.1.3
Fix - Set request_id parameter optional, add to end point url and set curl post parameters only for POST method

# Version 1.1.2
New optional vault param generate_request_id when creating a token
Fix - missing response fields (domesticNetwork + requestId)

# Version 1.1.1
Update documentation URL to the HiPay portal developer

# Version 1.1.0
Adds a *transaction reference* getter in the `Transaction` model class

# Version 1.0.4
Fix - update the name of the const MANUALLY_KEYED_CARD_PRESENT in ECI class

# Version 1.0.3
Update version sdk for PHP in composer.json

# Version 1.0.2
# Version 1.0.1
update circle.yml for version PHP

# Version 1.0.0
First version of the HiPay Fullservice SDK for PHP