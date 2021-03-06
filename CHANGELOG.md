# CHANGELOG

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
