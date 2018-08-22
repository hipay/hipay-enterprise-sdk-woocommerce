/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

//Subscriber supplied variables for snare// Snare operation to perform
var io_operation = 'ioBegin';
// io_bbout_element_id should refer to the hidden field in your form that contains the blackbox
var io_bbout_element_id = 'ioBB';

var io_install_stm = false; // do not try to download activex control
var io_exclude_stm = 12;  // do not attempt to instantiate an activex control
// installed by another customer
var io_install_flash = false; // do not force installation of Flash Player
var io_install_rip = true; // do attempt to collect real ip

// uncomment any of the below to signal an error when ActiveX or Flash is not present
//var io_install_stm_error_handler = "redirectActiveX();";
var io_flash_needs_update_handler = "";
var io_install_flash_error_handler = "";

document.write(unescape("%3Cscript src='https://mpsnare.iesnare.com/snare.js' type='text/javascript'%3E%3C/script%3E"));
