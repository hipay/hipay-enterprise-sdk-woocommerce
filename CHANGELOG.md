# CHANGELOG

## UNRELEASED

- **Fix** : Improve the API calls to "availablePaymentProduct" to enhance performance
- **Fix** : Fix issuer_bank_id param for IDeal

## 2.7.0

- **Add** : Update OneClick payment

## 2.6.0

- **Add** : Dynamically get Alma MAX/MIN from merchant account

## 2.5.0

- **Add** : Added Klarna payment method
- **Add** : Added PayPal v2
- **Add** : Added new branding colors to HiPay module

## 2.4.0

- **Add** : Added Mooney reference without redirection
- **Fix** : Fixed PHP 8 issue

## 2.3.1

- **Fix**: Fixed 3DS v2 merchant risk information builder
- **Fix**: Fixed multibanco details display after order processing
- **Fix**: Fixed moment where checked are made for insertion of HiPay SDK

## 2.3.0

- **Add**: Add cancel button option for hosted page

## 2.2.0

- **Add**: Added support for new payment means:
  - Alma 3x
  - Alma 4x
- **Add**: Removed support for some unused payment means:
  - Astropay payment methods
  - Belfius Direct Net
  - ING Home'Pay
  - Klarna
  - Yandex Money
  - WebMoney Transfer

## Version 2.1.1

- **Fix**: Optimized autoloader

## Version 2.1.0

- **Add**: Added Bancontact QR Code payment method
- **Fix**: Fixed PHP issues about short PHP open tags, thanks to [khypsos](https://github.com/khypsos) for issue [#2](https://github.com/hipay/hipay-enterprise-sdk-woocommerce/issues/2)

## Version 2.0.1

- **Fix**: Fixed PHP Compatibility problem

## Version 2.0.0

- **Add**: Switch HostedPage to HPayment v2

## Version 1.9.1

- **Fix**: Default to english if no translation is found

## Version 1.9.0

- **Add**: Improve capture form default quantities
- **Add**: Add Payment Expired status

## Version 1.8.4

- **Fix**: Order cancel on other gateway

## Version 1.8.3

- **Fix**: Hosted Page V2 redirection
- **Fix**: DSP required paramaters

## Version 1.8.2

- **Fix**: Merchant Promotion for Oney
- **Fix**: Fee product category for Oney
- **Fix**: Mbway Phone

## Version 1.8.1

- **Fix**: Fixed phone check

## Version 1.8.0

- **Add**: Added custom redirection after order for Multibanco payment method
- **Add**: Added phone controls using **Google libphonenumber** library

## Version 1.7.0

- **Add**: Added MBWay and Multibanco payment methods
- **Add**: Added order expiration limit field for Multibanco payment method
- **Add**: Merchant promotion for Oney payment methods
- **Fix**: Fixed product reference using SKU + EAN field
- **Fix**: Fixed behavior on multi use token parameter
- **Fix**: Increased Hipay API timeout

## Version 1.6.2

- **Fix**: Error logs on Wordpress BO

## Version 1.6.1

- **Fix**: Error logs on Wordpress BO

## Version 1.6.0

- **New**: Init HiPay configuration from SDK-PHP
- **New**: Notification when a new version is available
- **New**: Add Skip on-hold status field
- **New**: Cancel an order on woocommerce cancels the transaction on Hipay

## Version 1.5.1

- Fix: card token saving now on 116 and 118 notifications

## Version 1.5.0

- Added DSP2 handling (3DSv2)

## Version 1.4.0

- Add MyBank Payment method

## Version 1.3.0

- Add oneclick in my account

## Version 1.2.0

- Fix: loader for local payment
- Add local payment methods
- Add OneClick
- hosted fields for all methods

## Version 1.1.0

- Adding support for custom data
- Adding category and delivery method mapping
- Adding support for "basket"
- Refund and Capture support
- Adding display name for credit card
- Adding support for upgrade configuration
- Improving logging
- Improving support and configuration for local payment
- Adding support for Paypal
- Adding new statuses for partially capture and refund
- Adding functionals tests with cypress

## Version 1.0.0

- Official version of HiPay woocommerce plugin
