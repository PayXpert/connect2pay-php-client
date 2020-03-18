<?php

namespace PayXpert\Connect2Pay;

/**
 * Copyright 2013-2018 PayXpert
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Client class for the PayXpert payment page system.
 *
 * The normal workflow is as follows:
 * - Instantiate the class
 * - Set all the required parameters of the payment
 * - Call preparePayment() to create the payment
 * - Call getCustomerRedirectURL() and redirect the customer to this URL
 * - If receiving result via callback, use handleCallbackStatus to initialize
 * the status from the POST request (don't forget to authenticate the received
 * callback)
 * - If receiving result via customer redirection, use handleRedirectStatus to
 * initialize the status from the POST data
 *
 * This class does not do any sanitization on received data.
 * This must be done externally.
 * Every text must be encoded as UTF-8 when passed to this class.
 *
 * PHP dependencies:
 * PHP >= 5.3.0
 * PHP CURL extension
 * PHP OpenSSL extension
 */
class Connect2PayClient {
  /*
   * Client version
   */
  const CLIENT_VERSION = "2.13.0";

  /*
   * API version implemented by this class
   */
  const API_VERSION = "002.61";

  /*
   * Payment methods constants
   */
  const PAYMENT_METHOD_CREDITCARD = 'CreditCard';
  const PAYMENT_METHOD_TODITOCASH = 'ToditoCash';
  const PAYMENT_METHOD_BANKTRANSFER = 'BankTransfer';
  const PAYMENT_METHOD_DIRECTDEBIT = 'DirectDebit';
  const PAYMENT_METHOD_WECHAT = 'WeChat';
  const PAYMENT_METHOD_LINE = 'Line';
  const PAYMENT_METHOD_ALIPAY = 'AliPay';

  /*
   * Legacy payment types
   */
  /**
   *
   * @deprecated Use PAYMENT_METHOD_CREDITCARD
   */
  const _PAYMENT_TYPE_CREDITCARD = self::PAYMENT_METHOD_CREDITCARD;
  /**
   *
   * @deprecated Use PAYMENT_METHOD_TODITOCASH
   */
  const _PAYMENT_TYPE_TODITOCASH = self::PAYMENT_METHOD_TODITOCASH;
  /**
   *
   * @deprecated Use PAYMENT_METHOD_BANKTRANSFER
   */
  const _PAYMENT_TYPE_BANKTRANSFER = self::PAYMENT_METHOD_BANKTRANSFER;
  /**
   *
   * @deprecated Use PAYMENT_METHOD_DIRECTDEBIT
   */
  const _PAYMENT_TYPE_DIRECTDEBIT = self::PAYMENT_METHOD_DIRECTDEBIT;
  /**
   *
   * @deprecated Use PAYMENT_METHOD_WECHAT
   */
  const _PAYMENT_TYPE_WECHAT = self::PAYMENT_METHOD_WECHAT;
  /**
   *
   * @deprecated Use PAYMENT_METHOD_LINE
   */
  const _PAYMENT_TYPE_LINE = self::PAYMENT_METHOD_LINE;

  /*
   * Payment networks constants
   */
  const PAYMENT_NETWORK_SOFORT = 'sofort';
  const PAYMENT_NETWORK_PRZELEWY24 = 'przelewy24';
  const PAYMENT_NETWORK_IDEAL = 'ideal';
  const PAYMENT_NETWORK_GIROPAY = 'giropay';
  const PAYMENT_NETWORK_EPS = 'eps';
  const PAYMENT_NETWORK_POLI = 'poli';
  const PAYMENT_NETWORK_DRAGONPAY = 'dragonpay';
  const PAYMENT_NETWORK_TRUSTLY = 'trustly';

  /*
   * Legacy payment providers constants
   */
  /**
   *
   * @deprecated Use PAYMENT_NETWORK_SOFORT
   */
  const _PAYMENT_PROVIDER_SOFORT = self::PAYMENT_NETWORK_SOFORT;
  /**
   *
   * @deprecated Use PAYMENT_NETWORK_PRZELEWY24
   */
  const _PAYMENT_PROVIDER_PRZELEWY24 = self::PAYMENT_NETWORK_PRZELEWY24;
  /**
   *
   * @deprecated Use PAYMENT_NETWORK_IDEAL
   */
  const _PAYMENT_PROVIDER_IDEAL = self::PAYMENT_NETWORK_IDEAL;
  /**
   *
   * @deprecated Use PAYMENT_NETWORK_GIROPAY
   */
  const _PAYMENT_PROVIDER_GIROPAY = self::PAYMENT_NETWORK_GIROPAY;

  /*
   * Operation types constants
   */
  const OPERATION_TYPE_SALE = 'sale';
  const OPERATION_TYPE_AUTHORIZE = 'authorize';
  /**
   *
   * @deprecated Use OPERATION_TYPE_SALE
   */
  const _OPERATION_TYPE_SALE = self::OPERATION_TYPE_SALE;
  /**
   *
   * @deprecated Use OPERATION_TYPE_AUTHORIZE
   */
  const _OPERATION_TYPE_AUTHORIZE = self::OPERATION_TYPE_AUTHORIZE;

  /*
   * Payment modes constants
   */
  const PAYMENT_MODE_SINGLE = 'Single';
  const PAYMENT_MODE_ONSHIPPING = 'OnShipping';
  const PAYMENT_MODE_RECURRENT = 'Recurrent';
  const PAYMENT_MODE_INSTALMENTS = 'InstalmentsPayments';
  /**
   *
   * @deprecated Use PAYMENT_MODE_SINGLE
   */
  const _PAYMENT_MODE_SINGLE = self::PAYMENT_MODE_SINGLE;
  /**
   *
   * @deprecated Use PAYMENT_MODE_ONSHIPPING
   */
  const _PAYMENT_MODE_ONSHIPPING = self::PAYMENT_MODE_ONSHIPPING;
  /**
   *
   * @deprecated Use PAYMENT_MODE_RECURRENT
   */
  const _PAYMENT_MODE_RECURRENT = self::PAYMENT_MODE_RECURRENT;
  /**
   *
   * @deprecated Use PAYMENT_MODE_INSTALMENTS
   */
  const _PAYMENT_MODE_INSTALMENTS = self::PAYMENT_MODE_INSTALMENTS;

  /*
   * Shipping types constants
   */
  const SHIPPING_TYPE_PHYSICAL = 'Physical';
  const SHIPPING_TYPE_ACCESS = 'Access';
  const SHIPPING_TYPE_VIRTUAL = 'Virtual';
  /**
   *
   * @deprecated Use SHIPPING_TYPE_PHYSICAL
   */
  const _SHIPPING_TYPE_PHYSICAL = self::SHIPPING_TYPE_PHYSICAL;
  /**
   *
   * @deprecated Use SHIPPING_TYPE_ACCESS
   */
  const _SHIPPING_TYPE_ACCESS = self::SHIPPING_TYPE_ACCESS;
  /**
   *
   * @deprecated Use SHIPPING_TYPE_VIRTUAL
   */
  const _SHIPPING_TYPE_VIRTUAL = self::SHIPPING_TYPE_VIRTUAL;

  /*
   * Subscription types constants
   */
  const SUBSCRIPTION_TYPE_NORMAL = 'normal';
  const SUBSCRIPTION_TYPE_PARTPAYMENT = 'partpayment';
  const SUBSCRIPTION_TYPE_LIFETIME = 'lifetime';
  const SUBSCRIPTION_TYPE_ONETIME = 'onetime';
  const SUBSCRIPTION_TYPE_INFINITE = 'infinite';
  /**
   *
   * @deprecated Use SUBSCRIPTION_TYPE_NORMAL
   */
  const _SUBSCRIPTION_TYPE_NORMAL = self::SUBSCRIPTION_TYPE_NORMAL;
  /**
   *
   * @deprecated Use SUBSCRIPTION_TYPE_LIFETIME
   */
  const _SUBSCRIPTION_TYPE_LIFETIME = self::SUBSCRIPTION_TYPE_LIFETIME;
  /**
   *
   * @deprecated Use SUBSCRIPTION_TYPE_ONETIME
   */
  const _SUBSCRIPTION_TYPE_ONETIME = self::SUBSCRIPTION_TYPE_ONETIME;
  /**
   *
   * @deprecated Use SUBSCRIPTION_TYPE_INFINITE
   */
  const _SUBSCRIPTION_TYPE_INFINITE = self::SUBSCRIPTION_TYPE_INFINITE;

  /*
   * Lang constants
   */
  const LANG_EN = 'en';
  const LANG_FR = 'fr';
  const LANG_ES = 'es';
  const LANG_IT = 'it';
  const LANG_DE = 'de';
  const LANG_ZH_HANT = 'zh_hant';

  /**
   *
   * @deprecated Use LANG_EN
   */
  const _LANG_EN = self::LANG_EN;
  /**
   *
   * @deprecated Use LANG_FR
   */
  const _LANG_FR = self::LANG_FR;
  /**
   *
   * @deprecated Use LANG_ES
   */
  const _LANG_ES = self::LANG_ES;
  /**
   *
   * @deprecated Use LANG_IT
   */
  const _LANG_IT = self::LANG_IT;

  /*
   * ~~~~
   * Subscription cancel reasons
   * ~~~~
   */
  /**
   * Bank denial
   */
  const SUBSCRIPTION_CANCEL_BANK_DENIAL = 1000;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_BANK_DENIAL
   */
  const _SUBSCRIPTION_CANCEL_BANK_DENIAL = self::SUBSCRIPTION_CANCEL_BANK_DENIAL;

  /**
   * Canceled due to refund
   */
  const SUBSCRIPTION_CANCEL_REFUNDED = 1001;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_REFUNDED
   */
  const _SUBSCRIPTION_CANCEL_REFUNDED = self::SUBSCRIPTION_CANCEL_REFUNDED;

  /**
   * Canceled due to retrieval request
   */
  const SUBSCRIPTION_CANCEL_RETRIEVAL = 1002;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_RETRIEVAL
   */
  const _SUBSCRIPTION_CANCEL_RETRIEVAL = self::SUBSCRIPTION_CANCEL_RETRIEVAL;

  /**
   * Cancellation letter sent by bank
   */
  const SUBSCRIPTION_CANCEL_BANK_LETTER = 1003;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_BANK_LETTER
   */
  const _SUBSCRIPTION_CANCEL_BANK_LETTER = self::SUBSCRIPTION_CANCEL_BANK_LETTER;

  /**
   * Chargeback
   */
  const SUBSCRIPTION_CANCEL_CHARGEBACK = 1004;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_CHARGEBACK
   */
  const _SUBSCRIPTION_CANCEL_CHARGEBACK = self::SUBSCRIPTION_CANCEL_CHARGEBACK;

  /**
   * Company account closed
   */
  const SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED = 1005;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED
   */
  const _SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED = self::SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED;

  /**
   * Site account closed
   */
  const SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED = 1006;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED
   */
  const _SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED = self::SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED;

  /**
   * Didn't like the site
   */
  const SUBSCRIPTION_CANCEL_DID_NOT_LIKE = 1007;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_DID_NOT_LIKE
   */
  const _SUBSCRIPTION_CANCEL_DID_NOT_LIKE = self::SUBSCRIPTION_CANCEL_DID_NOT_LIKE;

  /**
   * Disagree ('Did not do it' or 'Do not recognize the transaction')
   */
  const SUBSCRIPTION_CANCEL_DISAGREE = 1008;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_DISAGREE
   */
  const _SUBSCRIPTION_CANCEL_DISAGREE = self::SUBSCRIPTION_CANCEL_DISAGREE;

  /**
   * Fraud from webmaster
   */
  const SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD = 1009;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD
   */
  const _SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD = self::SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD;

  /**
   * I could not get in to the site
   */
  const SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO = 1010;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO
   */
  const _SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO = self::SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO;

  /**
   * No problem, just moving on
   */
  const SUBSCRIPTION_CANCEL_NO_PROBLEM = 1011;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_NO_PROBLEM
   */
  const _SUBSCRIPTION_CANCEL_NO_PROBLEM = self::SUBSCRIPTION_CANCEL_NO_PROBLEM;

  /**
   * Not enough updates
   */
  const SUBSCRIPTION_CANCEL_NOT_UPDATED = 1012;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_NOT_UPDATED
   */
  const _SUBSCRIPTION_CANCEL_NOT_UPDATED = self::SUBSCRIPTION_CANCEL_NOT_UPDATED;

  /**
   * Problems with the movies/videos
   */
  const SUBSCRIPTION_CANCEL_TECH_PROBLEM = 1013;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_TECH_PROBLEM
   */
  const _SUBSCRIPTION_CANCEL_TECH_PROBLEM = self::SUBSCRIPTION_CANCEL_TECH_PROBLEM;

  /**
   * Site was too slow
   */
  const SUBSCRIPTION_CANCEL_TOO_SLOW = 1014;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_TOO_SLOW
   */
  const _SUBSCRIPTION_CANCEL_TOO_SLOW = self::SUBSCRIPTION_CANCEL_TOO_SLOW;

  /**
   * The site did not work
   */
  const SUBSCRIPTION_CANCEL_DID_NOT_WORK = 1015;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_DID_NOT_WORK
   */
  const _SUBSCRIPTION_CANCEL_DID_NOT_WORK = self::SUBSCRIPTION_CANCEL_DID_NOT_WORK;

  /**
   * Too expensive
   */
  const SUBSCRIPTION_CANCEL_TOO_EXPENSIVE = 1016;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_TOO_EXPENSIVE
   */
  const _SUBSCRIPTION_CANCEL_TOO_EXPENSIVE = self::SUBSCRIPTION_CANCEL_TOO_EXPENSIVE;

  /**
   * Un-authorized signup by family member
   */
  const SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY = 1017;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY
   */
  const _SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY = self::SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY;

  /**
   * Undetermined reasons
   */
  const SUBSCRIPTION_CANCEL_UNDETERMINED = 1018;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_UNDETERMINED
   */
  const _SUBSCRIPTION_CANCEL_UNDETERMINED = self::SUBSCRIPTION_CANCEL_UNDETERMINED;

  /**
   * Webmaster requested to cancel
   */
  const SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED = 1019;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED
   */
  const _SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED = self::SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED;

  /**
   * I haven't received my item
   */
  const SUBSCRIPTION_CANCEL_NOTHING_RECEIVED = 1020;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_NOTHING_RECEIVED
   */
  const _SUBSCRIPTION_CANCEL_NOTHING_RECEIVED = self::SUBSCRIPTION_CANCEL_NOTHING_RECEIVED;

  /**
   * The item was damaged or defective
   */
  const SUBSCRIPTION_CANCEL_DAMAGED = 1021;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_DAMAGED
   */
  const _SUBSCRIPTION_CANCEL_DAMAGED = self::SUBSCRIPTION_CANCEL_DAMAGED;

  /**
   * The box was empty
   */
  const SUBSCRIPTION_CANCEL_EMPTY_BOX = 1022;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_EMPTY_BOX
   */
  const _SUBSCRIPTION_CANCEL_EMPTY_BOX = self::SUBSCRIPTION_CANCEL_EMPTY_BOX;

  /**
   * The order was incomplete
   */
  const SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER = 1023;
  /**
   *
   * @deprecated Use SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER
   */
  const _SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER = self::SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER;

  /*
   * Field content constants
   */
  /**
   * To be used when a parameter is not available
   *
   * @var string
   */
  const UNAVAILABLE_PARAM = 'NA';
  /**
   * To be used when a country is not available
   *
   * @var string
   */
  const UNAVAILABLE_COUNTRY = 'ZZ';
  /**
   *
   * @deprecated Use UNAVAILABLE_PARAM
   */
  const _UNAVAILABLE = self::UNAVAILABLE_PARAM;
  /**
   *
   * @deprecated Use UNAVAILABLE_COUNTRY
   */
  const _UNAVAILABLE_COUNTRY = self::UNAVAILABLE_COUNTRY;

  /*
   * API calls routes
   */
  private static $API_ROUTES = array(/* */
      'ACCOUNT_INFO' => '/account', /* */
      'TRANS_PREPARE' => '/payment/prepare', /* */
      'PAYMENT_STATUS' => '/payment/:merchantToken/status', /* */
      'TRANS_INFO' => '/transaction/:transactionID/info', /* */
      'TRANS_EXPORT' => '/transactions/export', /* */
      'TRANS_REFUND' => '/transaction/:transactionID/refund', /* */
      'TRANS_REBILL' => '/transaction/:transactionID/rebill', /* */
      'TRANS_CANCEL' => '/transaction/:transactionID/cancel', /* */
      'TRANS_CAPTURE' => '/transaction/:transactionID/capture', /* */
      'TRANS_DOPAY' => '/payment/:customerToken', /* */
      'SUB_CANCEL' => '/subscription/:subscriptionID/cancel', /* */
      'WECHAT_DIRECT_PROCESS' => '/payment/:customerToken/process/wechat/direct', /* */
      'ALIPAY_DIRECT_PROCESS' => '/payment/:customerToken/process/alipay/direct' /* */
  );

  /*
   * Fields required for payment creation
   */
  protected $fieldsRequired = array('orderID', 'currency', 'amount', 'shippingType', 'paymentMode');

  /*
   * Fields maximum size
   */
  protected $fieldsSize = array(/* */
    'shopperID' => 32, /* */
    'shopperEmail' => 100, /* */
    'shipToCountryCode' => 2, /* */
    'shopperCountryCode' => 2, /* */
    'affiliateID' => 16, /* */
    'campaignName' => 128, /* */
    'orderID' => 100, /* */
    'orderDescription' => 500, /* */
    'currency' => 3, /* */
    'orderFOLanguage' => 50, /* */
    'shippingType' => 50, /* */
    'shippingName' => 50, /* */
    'paymentMethod' => 32, /* */
    'operation' => 32, /* */
    'paymentMode' => 30, /* */
    'subscriptionType' => 32, /* */
    'trialPeriod' => 10, /* */
    'rebillPeriod' => 10, /* */
    'ctrlRedirectURL' => 2048, /* */
    'ctrlCallbackURL' => 2048, /* */
    'timeOut' => 10, /* */
    'merchantNotificationTo' => 100, /* */
    'merchantNotificationLang' => 10, /* */
    'ctrlCustomData' => 2048 /* */
  );

  /*
   * Fields validation constraints
   */
  protected $fieldsValidate = array(/* */
    'shopperID' => 'isString', /* */
    'shopperEmail' => 'isEmail', /* */
    'shipToCountryCode' => 'isCountryName', /* */
    'shopperCountryCode' => 'isCountryName', /* */
    'affiliateID' => 'isNumeric', /* */
    'campaignName' => 'isString', /* */
    'orderID' => 'isString', /* */
    'orderDescription' => 'isString', /* */
    'currency' => 'isString', /* */
    'amount' => 'isInt', /* */
    'orderTotalWithoutShipping' => 'isInt', /* */
    'orderShippingPrice' => 'isInt', /* */
    'orderDiscount' => 'isInt', /* */
    'orderFOLanguage' => 'isString', /* */
    'shippingType' => 'isShippingType', /* */
    'shippingName' => 'isString', /* */
    'paymentMethod' => 'isPaymentMethod', /* */
    'paymentNetwork' => 'isPaymentNetwork', /* */
    'operation' => 'isOperation', /* */
    'paymentMode' => 'isPaymentMode', /* */
    'offerID' => 'isInt', /* */
    'subscriptionType' => 'isSubscriptionType', /* */
    'trialPeriod' => 'isString', /* */
    'rebillAmount' => 'isInt', /* */
    'rebillPeriod' => 'isString', /* */
    'rebillMaxIteration' => 'isInt', /* */
    'ctrlRedirectURL' => 'isAbsoluteUrl', /* */
    'ctrlCallbackURL' => 'isAbsoluteUrl', /* */
    'timeOut' => 'isString', /* */
    'merchantNotification' => 'isBool', /* */
    'merchantNotificationTo' => 'isEmail', /* */
    'merchantNotificationLang' => 'isString', /* */
    'themeID' => 'isInt' /* */
  );

  /*
   * Fields to be included in JSON
   */
  protected $fieldsJSON = array(/* */
    'apiVersion', /* */
    'shopperID', /* */
    'shopperEmail', /* */
    'shipToFirstName', /* */
    'shipToLastName', /* */
    'shipToCompany', /* */
    'shipToPhone', /* */
    'shipToAddress', /* */
    'shipToState', /* */
    'shipToZipcode', /* */
    'shipToCity', /* */
    'shipToCountryCode', /* */
    'shopperFirstName', /* */
    'shopperLastName', /* */
    'shopperPhone', /* */
    'shopperAddress', /* */
    'shopperState', /* */
    'shopperZipcode', /* */
    'shopperCity', /* */
    'shopperCountryCode', /* */
    'shopperBirthDate', /* */
    'shopperIDNumber', /* */
    'shopperCompany', /* */
    'shopperLoyaltyProgram', /* */
    'affiliateID', /* */
    'campaignName', /* */
    'orderID', /* */
    'orderDescription', /* */
    'currency', /* */
    'amount', /* */
    'orderTotalWithoutShipping', /* */
    'orderShippingPrice', /* */
    'orderDiscount', /* */
    'orderFOLanguage', /* */
    'orderCartContent', /* */
    'shippingType', /* */
    'shippingName', /* */
    'paymentMethod', /* */
    'paymentNetwork', /* */
    'operation', /* */
    'paymentMode', /* */
    'secure3d', /* */
    'offerID', /* */
    'subscriptionType', /* */
    'trialPeriod', /* */
    'rebillAmount', /* */
    'rebillPeriod', /* */
    'rebillMaxIteration', /* */
    'ctrlCustomData', /* */
    'ctrlRedirectURL', /* */
    'ctrlCallbackURL', /* */
    'timeOut', /* */
    'merchantNotification', /* */
    'merchantNotificationTo', /* */
    'merchantNotificationLang', /* */
    'themeID' /* */
  );
  private $apiVersion = self::API_VERSION;

  /**
   * URL of the connect2pay application
   *
   * @var string
   */
  private $url;

  /**
   * Login for the connect2pay application
   *
   * @var string
   */
  private $merchant;

  /**
   * Password for the connect2pay application
   *
   * @var string
   */
  private $password;

  // ~~~~
  // Transaction related data
  // ~~~~

  /**
   * Force the transaction to use Secure 3D
   *
   * @var Boolean
   */
  private $secure3d;

  // Customer fields
  /**
   * Merchant unique customer numeric id
   *
   * @var string
   */
  private $shopperID;
  /**
   * Customer email address
   *
   * @var string
   */
  private $shopperEmail;
  /**
   * Customer first name for shipping
   *
   * @var string
   */
  private $shipToFirstName;
  /**
   * Customer last name for shipping
   *
   * @var string
   */
  private $shipToLastName;
  /**
   * Customer company name for shipping
   *
   * @var string
   */
  private $shipToCompany;
  /**
   * Customer phone for shipping ; if many, separate by ";"
   *
   * @var string
   */
  private $shipToPhone;
  /**
   * Customer address for shipping
   *
   * @var string
   */
  private $shipToAddress;
  /**
   * Customer state for shipping
   *
   * @var string
   */
  private $shipToState;
  /**
   * Customer ZIP Code for shipping
   *
   * @var string
   */
  private $shipToZipcode;
  /**
   * Customer city for shipping
   *
   * @var string
   */
  private $shipToCity;
  /**
   * Customer country for shipping
   *
   * @var string
   */
  private $shipToCountryCode;
  /**
   * Customer first name for invoicing
   *
   * @var string
   */
  private $shopperFirstName;
  /**
   * Customer last name for invoicing
   *
   * @var string
   */
  private $shopperLastName;
  /**
   * Customer phone for invoicing ; if many, separate by ";"
   *
   * @var string
   */
  private $shopperPhone;
  /**
   * Customer address for invoicing
   *
   * @var string
   */
  private $shopperAddress;
  /**
   * Customer state for invoicing
   *
   * @var string
   */
  private $shopperState;
  /**
   * Customer ZIP Code for invoicing
   *
   * @var string
   */
  private $shopperZipcode;
  /**
   * Customer city for invoicing
   *
   * @var string
   */
  private $shopperCity;
  /**
   * Customer country for invoicing
   *
   * @var string
   */
  private $shopperCountryCode;
  /**
   * Customer birth date YYYYMMDD
   *
   * @var string
   */
  private $shopperBirthDate;
  /**
   * Customer ID number (identity card, passport...)
   *
   * @var string
   */
  private $shopperIDNumber;
  /**
   * Customer company name for invoicing
   *
   * @var string
   */
  private $shopperCompany;
  /**
   * Customer Loyalty Program name
   *
   * @var string
   */
  private $shopperLoyaltyProgram;

  // Affiliation fields
  /**
   * Identifier of the affiliate
   *
   * @var integer
   */
  private $affiliateID;

  /**
   * Name of the campaign
   *
   * @var string
   */
  private $campaignName;

  // Order Fields
  /**
   * Merchant internal unique order ID
   *
   * @var string
   */
  private $orderID;
  /**
   * Sum up of the order to display on the payment page
   *
   * @var string
   */
  private $orderDescription;
  /**
   * Currency for the current order
   *
   * @var string
   */
  private $currency;
  /**
   * The transaction amount in cents (for 1€ => 100)
   *
   * @var integer
   */
  private $amount;
  /**
   * The transaction amount in cents, without shipping fee
   *
   * @var integer
   */
  private $orderTotalWithoutShipping;
  /**
   * The shipping amount in cents (for 1€ => 100)
   *
   * @var integer
   */
  private $orderShippingPrice;
  /**
   * The discount amount in cents (for 1€ => 100)
   *
   * @var integer
   */
  private $orderDiscount;
  /**
   * Language of the Front Office used to validate the order
   *
   * @var string
   */
  private $orderFOLanguage;
  /**
   * Product or service bought - see details below
   *
   * @var array[](integer CartProductId, string CartProductName, float
   *      CartProductUnitPrice,
   *      integer CartProductQuantity, string CartProductBrand, string
   *      CartProductMPN,
   *      string CartProductCategoryName, integer CartProductCategoryID)
   */
  private $orderCartContent;

  // Shipping Fields
  /**
   * Type can be either : Physical (for physical goods), Virtual (for
   * dematerialized goods), Access (for protected content)
   *
   * @var string
   */
  private $shippingType;
  /**
   * In case of Physical shipping type, name of the shipping company
   *
   * @var string
   */
  private $shippingName;

  // Payment Detail Fields
  /**
   * Can be CreditCard, ToditoCash, BankTransfer or empty.
   * This will change the type of the payment page displayed.
   * If empty, a selection page will be displayed to the customer with payment
   * types available for the account.
   *
   * @var string
   */
  private $paymentMethod;

  /**
   * The payment netxork to use for the payment.
   * This can be needed for payment methods other than credit card where several
   * networks are available and can not be all used in every countries.
   *
   * @var string
   */
  private $paymentNetwork;

  /**
   * Can be authorize or sale (default value is according to what is configured
   * for the account).
   * This will change the operation done for the payment page.
   * Only relevant for Credit Card payment type.
   *
   * @var string
   */
  private $operation;

  /**
   * Can be either : Single, OnShipping, Recurrent, InstalmentsPayments
   *
   * @var string
   */
  private $paymentMode;

  /**
   * Predefined price point with initial and rebill period (for Recurrent,
   * InstalmentsPayments payment types)
   *
   * @var integer
   */
  private $offerID;

  /**
   * Type of subscription.
   *
   * @var string
   */
  private $subscriptionType;

  /**
   * Number of days in the initial period (for Recurrent, InstalmentsPayments
   * payment types)
   *
   * @var integer
   */
  private $trialPeriod;

  /**
   * Number in minor unit, amount to be rebilled after the initial period (for
   * Recurrent, InstalmentsPayments payment types)
   *
   * @var integer
   */
  private $rebillAmount;

  /**
   * Number of days next re-billing transaction will be settled in (for
   * Recurrent, InstalmentsPayments payment types)
   *
   * @var integer
   */
  private $rebillPeriod;

  /**
   * Number of re-billing transactions that will be settled (for Recurrent,
   * InstalmentsPayments payment types)
   *
   * @var integer
   */
  private $rebillMaxIteration;

  // Template and Control Fields
  /**
   * The URL where to redirect the customer after the transaction processing
   *
   * @var string
   */
  private $ctrlRedirectURL;

  /**
   * A URL that will be notified of the status of the transaction
   *
   * @var string
   */
  private $ctrlCallbackURL;

  /**
   * Custom data that will be returned back with the status of the transaction
   *
   * @var string
   */
  private $ctrlCustomData;

  /**
   * Validity for the payment link in ISO 8601 duration format.
   * See http://en.wikipedia.org/wiki/ISO_8601.
   * For example: 2 days => P2D, 1 month => P1M
   *
   * @var string
   */
  private $timeOut;

  /**
   * Whether or not to send notification to the merchant after payment
   * processing
   *
   * @var boolean
   */
  private $merchantNotification;

  /**
   * Mail address to send merchant notification to
   *
   * @var string
   */
  private $merchantNotificationTo;

  /**
   * Lang to use in merchant notification (defaults to the customer lang)
   *
   * @var string
   */
  private $merchantNotificationLang;

  /**
   * Select a predefined payment page template
   *
   * @var integer
   */
  private $themeID;

  // Data returned from prepare call
  private $returnCode;
  private $returnMessage;
  private $merchantToken;
  private $customerToken;

  // Data returned from status call
  private $status;

  // Internal data
  private $clientErrorMessage;

  // HTTP Proxy data
  private $proxy_host = null;
  private $proxy_port = null;
  private $proxy_username = null;
  private $proxy_password = null;

  // Internal Currency Helper
  private $currencyHelper = null;

  // Extra CURL options that can be set by the caller
  private $extraCurlOptions = array();

  /**
   * Instantiate a new payment page client
   *
   * @param string $url
   *          The URL of the payment page application
   * @param string $merchant
   *          The login of the merchant on the payment page
   * @param string $password
   *          The password of the merchant on the payment page
   * @param array $data
   *          Data for the transaction to create (optional)
   */
  public function __construct($url, $merchant, $password, $data = null) {
    $this->url = preg_replace('/\/*$/', '', $url);
    $this->merchant = $merchant;
    $this->password = $password;

    if ($data != null && is_array($data)) {
      foreach ($data as $var => $value) {
        if (property_exists($this, $var)) {
          $this->$var = $value;
        }
      }
    }
  }

  /**
   * Set the parameter in the case of the use of an outgoing proxy
   *
   * @param string $host
   *          The proxy host.
   * @param int $port
   *          The proxy port.
   * @param string $username
   *          The proxy username.
   * @param string $password
   *          The proxy password.
   */
  public function useProxy($host, $port, $username = null, $password = null) {
    $this->proxy_host = $host;
    $this->proxy_port = $port;
    $this->proxy_username = $username;
    $this->proxy_password = $password;
  }

  /**
   * Force the validation of the Connect2Pay SSL certificate.
   *
   * @param string $certFilePath
   *          The path to the PEM file containing the certification chain.
   *          If not set, defaults to
   *          "_current-dir_/ssl/connect2pay-signing-ca-cert.pem"
   * @deprecated Has no effect anymore
   */
  public function forceSSLValidation($certFilePath = null) {
    Utils::deprecation_error('Custom certificate file path is deprecated. Will use the system CA.');
  }

  /**
   * Add extra curl options
   */
  public function setExtraCurlOption($name, $value) {
    $this->extraCurlOptions[$name] = $value;
  }

  /**
   * Get information about the API account.
   */
  public function getAccountInformation() {
    $url = $this->url . self::$API_ROUTES['ACCOUNT_INFO'];
    $url .= '?apiVersion=' . $this->apiVersion;

    $result = $this->doGet($url, array(), false);

    if ($result !== null && is_object($result)) {
      $accountInfo = AccountInformation::getFromJson($result);

      if ($accountInfo != null) {
        return $accountInfo;
      }
    }

    return null;
  }

  /**
   *
   * @deprecated Use preparePayment() instead.
   */
  public function prepareTransaction() {
    Utils::deprecation_error('Method prepareTransaction() is deprecated, use preparePayment() instead');
    return $this->preparePayment();
  }

  /**
   * Prepare a new payment on the payment page application.
   * This method will validate the payment data and call
   * the payment page application to create a new payment.
   * The fields returnCode, returnMessage, merchantToken and
   * customerToken will be populated according to the call result.
   *
   * @return boolean true if creation is successful, false otherwise
   */
  public function preparePayment() {
    if ($this->validate()) {
      $trans = array();

      foreach ($this->fieldsJSON as $fieldName) {
        if (is_array($this->{$fieldName}) || !C2PValidate::isEmpty($this->{$fieldName})) {
          $trans[$fieldName] = $this->{"get" . ucfirst($fieldName)}();
        }
      }

      // Only PHP >= 5.4 has JSON_UNESCAPED_SLASHES option
      $post_data = str_replace('\\/', '/', json_encode($trans));
      $url = $this->url . Connect2PayClient::$API_ROUTES['TRANS_PREPARE'];

      $result = $this->doPost($url, $post_data);

      if ($result != null && is_array($result)) {
        $this->returnCode = $result['code'];
        $this->returnMessage = $result['message'];

        if ($this->returnCode == "200") {
          $this->merchantToken = $result['merchantToken'];
          $this->customerToken = $result['customerToken'];

          return true;
        } else {
          $this->clientErrorMessage = $this->returnMessage;
        }
      }
    }

    return false;
  }

  /**
   *
   * Get information of a specific transaction.
   *
   * @param string $transactionId
   *          The transaction reference to get status of.
   */
  public function getTransactionInfo($transactionId) {
    if ($transactionId != null && strlen(trim($transactionId)) > 0) {
      $url = $this->url . str_replace(":transactionID", $transactionId, Connect2PayClient::$API_ROUTES['TRANS_INFO']);
      $url .= '?apiVersion=' . $this->apiVersion;

      $result = $this->doGet($url, array(), false);

      if ($result !== null && is_object($result)) {
        $this->status = TransactionAttempt::getFromJson($result);

        if (isset($this->status)) {
          return $this->status;
        }
      }
    }

    return null;
  }

  /**
   *
   * Export a list of all transactions in a certain date range.
   *
   * @param ExportTransactionsRequest $request
   *          The request containing fields to filter the list of exported transactions
   *
   * @return ExportTransactionsResponse The transactions response object
   */
  public function exportTransactions($request) {
    if ($request !== null) {
      $url = $this->url . Connect2PayClient::$API_ROUTES['TRANS_EXPORT'];

      $request->setApiVersion($this->getApiVersion());

      $result = $this->doGet($url, $request->toParamsArray(), false);

      if ($result != null && is_object($result)) {
        return ExportTransactionsResponse::getFromJson($result);
      } else {
        $this->clientErrorMessage = 'No result received from export transactions call: ' . $this->clientErrorMessage;
      }
    } else {
      $this->clientErrorMessage = '"request" must not be null';
    }

    return null;
  }

  /**
   *
   * @deprecated Use getPaymentStatus($merchantToken) instead.
   *
   * @param string $merchantToken
   */
  public function getTransactionStatus($merchantToken) {
    Utils::deprecation_error('getTransactionStatus is deprecated, use getPaymentStatus instead');
    return $this->getPaymentStatus($merchantToken);
  }

  /**
   * Do a transaction status request on the payment page application.
   *
   * @param string $merchantToken
   *          The merchant token related to this payment
   * @return PaymentStatus The PaymentStatus object of the payment or null on
   *         error
   */
  public function getPaymentStatus($merchantToken) {
    if ($merchantToken != null && strlen(trim($merchantToken)) > 0) {
      $url = $this->url . str_replace(":merchantToken", $merchantToken, Connect2PayClient::$API_ROUTES['PAYMENT_STATUS']);
      $url .= '?apiVersion=' . $this->apiVersion;

      $result = $this->doGet($url, array(), false);

      if ($result !== null && is_object($result)) {
        $this->initStatus($result);

        if (isset($this->status)) {
          return $this->status;
        }
      }
    }

    return null;
  }

  /**
   * Refund a transaction.
   *
   * @param string $transactionID
   *          Identifier of the transaction to refund
   * @param int $amount
   *          The amount to refund
   * @return RefundStatus The RefundStatus filled with values returned from the
   *         operation or
   *         null on failure (in that case call getClientErrorMessage())
   */
  public function refundTransaction($transactionID, $amount) {
    if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
      $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_REFUND']);
      $trans = array();
      $trans['apiVersion'] = $this->apiVersion;
      $trans['amount'] = intval($amount);

      $result = $this->doPost($url, json_encode($trans));

      $this->status = null;
      if ($result != null && is_array($result)) {
        $this->status = new RefundStatus();
        if (isset($result['code'])) {
          $this->status->setCode($result['code']);
        }
        if (isset($result['message'])) {
          $this->status->setMessage($result['message']);
        }
        if (isset($result['transactionID'])) {
          $this->status->setTransactionID($result['transactionID']);
        }
        if (isset($result['operation'])) {
          $this->status->setOperation($result['operation']);
        }

        return $this->status;
      } else {
        $this->clientErrorMessage = 'No result received from refund call';
      }
    } else {
      $this->clientErrorMessage = '"transactionID" must not be null, "amount" must be a positive integer';
    }

    return null;
  }

  /**
   * Rebill a transaction.
   *
   * @param string $transactionID
   *          Identifier of the transaction to rebill
   * @param int $amount
   *          The amount to rebill
   * @return RebillStatus The RebillStatus filled with values returned from the
   *         operation or
   *         null on failure (in that case call getClientErrorMessage())
   */
  public function rebillTransaction($transactionID, $amount) {
    if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
      $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_REBILL']);
      $trans = array();
      $trans['apiVersion'] = $this->apiVersion;
      $trans['amount'] = intval($amount);

      $result = $this->doPost($url, json_encode($trans));

      $this->status = null;
      if ($result != null && is_array($result)) {
        $this->status = new RebillStatus();
        if (isset($result['code'])) {
          $this->status->setCode($result['code']);
        }
        if (isset($result['message'])) {
          $this->status->setMessage($result['message']);
        }
        if (isset($result['transactionID'])) {
          $this->status->setTransactionID($result['transactionID']);
        }
        if (isset($result['operation'])) {
          $this->status->setOperation($result['operation']);
        }

        return $this->status;
      } else {
        $this->clientErrorMessage = 'No result received from rebill call: ' . $this->clientErrorMessage;
      }
    } else {
      $this->clientErrorMessage = '"transactionID" must not be null, "amount" must be a positive integer';
    }

    return null;
  }

  /**
   * Cancel a transaction.
   *
   * @param string $transactionID
   *          Identifier of the transaction to cancel
   * @param int $amount
   *          The amount to cancel
   * @return CancelStatus The CancelStatus filled with values returned from the
   *         operation or
   *         null on failure (in that case call getClientErrorMessage())
   */
  public function cancelTransaction($transactionID, $amount) {
    if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
      $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_CANCEL']);
      $trans = array();
      $trans['apiVersion'] = $this->apiVersion;
      $trans['amount'] = intval($amount);

      $result = $this->doPost($url, json_encode($trans));

      $this->status = null;
      if ($result != null && is_array($result)) {
        $this->status = new CancelStatus();
        if (isset($result['code'])) {
          $this->status->setCode($result['code']);
        }
        if (isset($result['message'])) {
          $this->status->setMessage($result['message']);
        }
        if (isset($result['transactionID'])) {
          $this->status->setTransactionID($result['transactionID']);
        }
        if (isset($result['operation'])) {
          $this->status->setOperation($result['operation']);
        }

        return $this->status;
      } else {
        $this->clientErrorMessage = 'No result received from cancel call: ' . $this->clientErrorMessage;
      }
    } else {
      $this->clientErrorMessage = '"transactionID" must not be null, "amount" must be a positive integer';
    }

    return null;
  }

  /**
   * Capture a transaction.
   *
   * @param string $transactionID
   *          Identifier of the transaction to capture
   * @param int $amount
   *          The amount to capture, must be lower or equal to the authorized amount
   * @return CaptureStatus The CaptureStatus filled with values returned from the
   *         operation or null on failure (in that case call getClientErrorMessage())
   */
  public function captureTransaction($transactionID, $amount) {
    if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
      $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_CAPTURE']);
      $trans = array();
      $trans['apiVersion'] = $this->apiVersion;
      $trans['amount'] = intval($amount);

      $result = $this->doPost($url, json_encode($trans));

      $this->status = null;
      if ($result != null && is_array($result)) {
        $this->status = new CaptureStatus();
        if (isset($result['code'])) {
          $this->status->setCode($result['code']);
        }
        if (isset($result['message'])) {
          $this->status->setMessage($result['message']);
        }
        if (isset($result['transactionID'])) {
          $this->status->setTransactionID($result['transactionID']);
        }
        if (isset($result['operation'])) {
          $this->status->setOperation($result['operation']);
        }

        return $this->status;
      } else {
        $this->clientErrorMessage = 'No result received from capture call: ' . $this->clientErrorMessage;
      }
    } else {
      $this->clientErrorMessage = '"transactionID" must not be null, "amount" must be a positive integer';
    }

    return null;
  }

  /**
   * Do a subscription cancellation.
   *
   * @param int $subscriptionID
   *          Identifier of the subscription to cancel
   * @param int $cancelReason
   *          Identifier of the cancelReason (see _SUBSCRIPTION_CANCEL_*
   *          constants)
   * @return string The result code of the operation (200 for success) or null
   *         on
   *         failure
   */
  public function cancelSubscription($subscriptionID, $cancelReason) {
    if ($subscriptionID != null && is_numeric($subscriptionID) && isset($cancelReason) && is_numeric($cancelReason)) {
      $url = $this->url . str_replace(":subscriptionID", $subscriptionID, Connect2PayClient::$API_ROUTES['SUB_CANCEL']);
      $trans = array();
      $trans['apiVersion'] = $this->apiVersion;
      $trans['cancelReason'] = intval($cancelReason);

      $result = $this->doPost($url, json_encode($trans));

      if ($result != null && is_array($result)) {
        $this->clientErrorMessage = $result['message'];
        return $result['code'];
      }
    } else {
      $this->clientErrorMessage = 'subscriptionID and cancelReason must be not null and numeric';
    }

    return null;
  }

  /**
   * Direct WeChat transaction process.
   * Must be preceded by a payment prepare call.
   *
   * @param string $customerToken
   *          Customer token of the payment returned by the previous prepare
   *          call
   * @param WeChatDirectProcessRequest $request
   *          The WeChatDirectProcessRequest object with call parameters
   * @return WeChatDirectProcessResponse The WeChatDirectProcessResponse filled
   *         with values returned from the
   *         operation or null on failure (in that case call
   *         getClientErrorMessage())
   */
  public function directWeChatProcess($customerToken, $request) {
    if ($customerToken !== null && $request !== null) {
      $url = $this->url . str_replace(":customerToken", $customerToken, Connect2PayClient::$API_ROUTES['WECHAT_DIRECT_PROCESS']);

      $request->setApiVersion($this->getApiVersion());

      $result = $this->doPost($url, json_encode($request), false);

      if ($result != null && is_object($result)) {
        $apiResponse = WeChatDirectProcessResponse::getFromJson($result);

        return $apiResponse;
      } else {
        $this->clientErrorMessage = 'No result received from direct WeChat processing call: ' . $this->clientErrorMessage;
      }
    } else {
      $this->clientErrorMessage = '"customerToken" and "request" must not be null';
    }

    return null;
  }

  /**
   * Direct AliPay transaction process.
   * Must be preceded by a payment prepare call.
   *
   * @param string $customerToken
   *          Customer token of the payment returned by the previous prepare
   *          call
   * @param AliPayDirectProcessRequest $request
   *          The AliPayDirectProcessRequest object with call parameters
   * @return AliPayDirectProcessResponse The AliPayDirectProcessResponse filled
   *         with values returned from the
   *         operation or null on failure (in that case call
   *         getClientErrorMessage())
   */
  public function directAliPayProcess($customerToken, $request) {
    if ($customerToken !== null && $request !== null) {
      $url = $this->url . str_replace(":customerToken", $customerToken, Connect2PayClient::$API_ROUTES['ALIPAY_DIRECT_PROCESS']);

      $request->setApiVersion($this->getApiVersion());

      $result = $this->doPost($url, json_encode($request), false);

      if ($result != null && is_object($result)) {
        $apiResponse = AliPayDirectProcessResponse::getFromJson($result);

        return $apiResponse;
      } else {
        $this->clientErrorMessage = 'No result received from direct AliPay processing call: ' . $this->clientErrorMessage;
      }
    } else {
      $this->clientErrorMessage = '"customerToken" and "request" must not be null';
    }

    return null;
  }

  /**
   * Handle the callback done by the payment page application after
   * a transaction processing.
   * This will populate the status field that can be retrieved by calling
   * getStatus().
   *
   * @return true on success or false on error
   */
  public function handleCallbackStatus() {
    // Read the body of the request
    $body = @file_get_contents('php://input');

    if ($body != null && strlen(trim($body)) > 0) {
      $status = json_decode(trim($body), false);

      if ($status != null && is_object($status)) {
        $this->initStatus($status);

        return true;
      }
    }

    return false;
  }

  /**
   * Handle the data received by the POST done when payment page redirects
   * the customer to the merchant website.
   * This will populate the status field that can be retrieved by calling
   * getStatus().
   *
   * @param string $encryptedData
   *          The content of the 'data' field posted
   * @param string $merchantToken
   *          The merchant token related to this transaction
   * @return boolean True on success or false on error
   */
  public function handleRedirectStatus($encryptedData, $merchantToken) {
    $key = $this->urlsafe_base64_decode($merchantToken);
    $binData = $this->urlsafe_base64_decode($encryptedData);

    // Decrypting
    $cipher = "aes-128-ecb";
    $json = null;
    if (function_exists('openssl_decrypt') && in_array($cipher, openssl_get_cipher_methods())) {
      $json = openssl_decrypt($binData, $cipher, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
    } else {
      $this->clientErrorMessage = 'OpenSSL functions are needed to operate properly.';
    }

    if ($json !== null && $json !== false) {
      // Remove PKCS#5 padding
      $json = $this->pkcs5_unpad($json);
      $status = json_decode($json, false);

      if ($status != null && is_object($status)) {
        $this->initStatus($status);

        return true;
      }
    }

    return false;
  }

  /**
   * Returns the URL to redirect the customer to after a transaction
   * creation.
   *
   * @return string The URL to redirect the customer to.
   */
  public function getCustomerRedirectURL() {
    return $this->url . str_replace(":customerToken", $this->customerToken, Connect2PayClient::$API_ROUTES['TRANS_DOPAY']);
  }

  /**
   * Validate the current transaction data.
   *
   * @return boolean True if transaction data are valid, false otherwise
   */
  public function validate() {
    $arrErrors = array();

    $arrErrors = $this->validateFields();

    if (sizeof($arrErrors) > 0) {
      foreach ($arrErrors as $error) {
        $this->clientErrorMessage .= $error . " * ";
      }
      return false;
    }

    return true;
  }

  private function doGet($url, $params, $assoc = true) {
    return $this->doHTTPRequest("GET", $url, $params, $assoc);
  }

  private function doPost($url, $data, $assoc = true) {
    return $this->doHTTPRequest("POST", $url, $data, $assoc);
  }

  private function doHTTPRequest($type, $url, $data, $assoc = true) {
    $curl = curl_init();

    if ($type === "GET") {
      // In that case, $data is the array of params to add in the URL
      if (is_array($data) && count($data) > 0) {
        $urlParams = array();
        foreach ($data as $param => $value) {
          $urlParams[] = urlencode($param) . "=" . urlencode($value);
        }
        if (count($urlParams) > 0) {
          $url .= "?" . implode("&", $urlParams);
        }
      }
    } elseif ($type === "POST") {
      // In that case, $data is the body of the request
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    } else {
      $this->clientErrorMessage = "Bad HTTP method specified.";
      return null;
    }

    curl_setopt($curl, CURLOPT_USERAGENT, "PayXpert PHP C2P API Client/" . self::CLIENT_VERSION);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $this->merchant . ":" . $this->password);

    if ($this->proxy_host != null && $this->proxy_port != null) {
      curl_setopt($curl, CURLOPT_PROXY, $this->proxy_host);
      curl_setopt($curl, CURLOPT_PROXYPORT, $this->proxy_port);

      if ($this->proxy_username != null && $this->proxy_password != null) {
        curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxy_username . ":" . $this->proxy_password);
      }
    }

    // Extra Curl Options
    foreach ($this->extraCurlOptions as $name => $value) {
      curl_setopt($curl, $name, $value);
    }

    $json = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode != 200) {
      $this->clientErrorMessage = "Received HTTP code " . $httpCode . " from payment page.";
    } else {
      if ($json !== false) {
        $result = json_decode($json, $assoc);

        if ($result != null) {
          return $result;
        } else {
          $this->clientErrorMessage = 'JSON decoding error.';
        }
      } else {
        $this->clientErrorMessage = 'Error requesting ' . $connect2pay;
      }
    }

    return null;
  }

  private function initStatus($status) {
    $this->status = PaymentStatus::getFromJson($status);
  }

  private function urlsafe_base64_decode($string) {
    return base64_decode(strtr($string, '-_', '+/'));
  }

  private function pkcs5_unpad($text) {
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text)) {
      // The initial text was empty
      return "";
    }

    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
      // The length of the padding sequence is incorrect
      return false;
    }

    return substr($text, 0, -1 * $pad);
  }

  public function getApiVersion() {
    return $this->apiVersion;
  }

  public function getURL() {
    return $this->url;
  }

  public function setURL($url) {
    $this->url = $url;
    return ($this);
  }

  public function getMerchant() {
    return $this->merchant;
  }

  public function setMerchant($merchant) {
    $this->merchant = $merchant;
    return ($this);
  }

  public function getPassword() {
    return $this->password;
  }

  public function setPassword($password) {
    $this->password = $password;
    return ($this);
  }

  public function getAfClientId() {
    Utils::deprecation_error('The field afClientId does not exist any more');
    return null;
  }

  public function setAfClientId($afClientId) {
    Utils::deprecation_error('The field afClientId does not exist any more');
    return ($this);
  }

  public function getAfPassword() {
    Utils::deprecation_error('The field afPassword does not exist any more');
    return null;
  }

  public function setAfPassword($afPassword) {
    Utils::deprecation_error('The field afPassword does not exist any more');
    return ($this);
  }

  public function getSecure3d() {
    return $this->secure3d;
  }

  public function setSecure3d($secure3d) {
    $this->secure3d = $secure3d;
    return ($this);
  }

  public function getShopperID() {
    return $this->shopperID;
  }

  public function setShopperID($shopperID) {
    $this->shopperID = (strlen($shopperID) > 32) ? substr((string) $shopperID, 0, 32) : (string) $shopperID;
    return ($this);
  }

  public function getShopperEmail() {
    return $this->shopperEmail;
  }

  public function setShopperEmail($shopperEmail) {
    $this->shopperEmail = (strlen($shopperEmail) > 100) ? substr((string) $shopperEmail, 0, 100) : (string) $shopperEmail;
    return ($this);
  }

  public function getShipToFirstName() {
    return $this->shipToFirstName;
  }

  public function setShipToFirstName($shipToFirstName) {
    $this->shipToFirstName = (strlen($shipToFirstName) > 35) ? substr((string) $shipToFirstName, 0, 35) : (string) $shipToFirstName;
    return ($this);
  }

  public function getShipToLastName() {
    return $this->shipToLastName;
  }

  public function setShipToLastName($shipToLastName) {
    $this->shipToLastName = (strlen($shipToLastName) > 35) ? substr((string) $shipToLastName, 0, 35) : (string) $shipToLastName;
    return ($this);
  }

  public function getShipToCompany() {
    return $this->shipToCompany;
  }

  public function setShipToCompany($shipToCompany) {
    $this->shipToCompany = (strlen($shipToCompany) > 128) ? substr((string) $shipToCompany, 0, 128) : (string) $shipToCompany;
    return ($this);
  }

  public function getShipToPhone() {
    return $this->shipToPhone;
  }

  public function setShipToPhone($shipToPhone) {
    $this->shipToPhone = (strlen($shipToPhone) > 20) ? substr((string) $shipToPhone, 0, 20) : (string) $shipToPhone;
    return ($this);
  }

  public function getShipToAddress() {
    return $this->shipToAddress;
  }

  public function setShipToAddress($shipToAddress) {
    $this->shipToAddress = (strlen($shipToAddress) > 255) ? substr((string) $shipToAddress, 0, 255) : (string) $shipToAddress;
    return ($this);
  }

  public function getShipToState() {
    return $this->shipToState;
  }

  public function setShipToState($shipToState) {
    $this->shipToState = (strlen($shipToState) > 30) ? substr((string) $shipToState, 0, 30) : (string) $shipToState;
    return ($this);
  }

  public function getShipToZipcode() {
    return $this->shipToZipcode;
  }

  public function setShipToZipcode($shipToZipcode) {
    $this->shipToZipcode = (strlen($shipToZipcode) > 10) ? substr((string) $shipToZipcode, 0, 10) : (string) $shipToZipcode;
    return ($this);
  }

  public function getShipToCity() {
    return $this->shipToCity;
  }

  public function setShipToCity($shipToCity) {
    $this->shipToCity = (strlen($shipToCity) > 50) ? substr((string) $shipToCity, 0, 50) : (string) $shipToCity;
    return ($this);
  }

  public function getShipToCountryCode() {
    return $this->shipToCountryCode;
  }

  public function setShipToCountryCode($shipToCountryCode) {
    $this->shipToCountryCode = (strlen($shipToCountryCode) > 2) ? substr((string) $shipToCountryCode, 0, 2) : (string) $shipToCountryCode;
    return ($this);
  }

  public function getShopperFirstName() {
    return $this->shopperFirstName;
  }

  public function setShopperFirstName($shopperFirstName) {
    $this->shopperFirstName = (strlen($shopperFirstName) > 35) ? substr((string) $shopperFirstName, 0, 35) : (string) $shopperFirstName;
    return ($this);
  }

  public function getShopperLastName() {
    return (!C2PValidate::isEmpty($this->shopperLastName)) ? $this->shopperLastName : Connect2PayClient::UNAVAILABLE;
  }

  public function setShopperLastName($shopperLastName) {
    $this->shopperLastName = (strlen($shopperLastName) > 35) ? substr((string) $shopperLastName, 0, 35) : (string) $shopperLastName;
    return ($this);
  }

  public function getShopperPhone() {
    return (!C2PValidate::isEmpty($this->shopperPhone)) ? $this->shopperPhone : Connect2PayClient::UNAVAILABLE;
  }

  public function setShopperPhone($shopperPhone) {
    $this->shopperPhone = (strlen($shopperPhone) > 20) ? substr((string) $shopperPhone, 0, 20) : (string) $shopperPhone;
    return ($this);
  }

  public function getShopperAddress() {
    return (!C2PValidate::isEmpty($this->shopperAddress)) ? $this->shopperAddress : Connect2PayClient::UNAVAILABLE;
  }

  public function setShopperAddress($shopperAddress) {
    $this->shopperAddress = (strlen($shopperAddress) > 255) ? substr((string) $shopperAddress, 0, 255) : (string) $shopperAddress;
    return ($this);
  }

  public function getShopperState() {
    return (!C2PValidate::isEmpty($this->shopperState)) ? $this->shopperState : Connect2PayClient::UNAVAILABLE;
  }

  public function setShopperState($shopperState) {
    $this->shopperState = (strlen($shopperState) > 30) ? substr((string) $shopperState, 0, 30) : (string) $shopperState;
    return ($this);
  }

  public function getShopperZipcode() {
    return (!C2PValidate::isEmpty($this->shopperZipcode)) ? $this->shopperZipcode : Connect2PayClient::UNAVAILABLE;
  }

  public function setShopperZipcode($shopperZipcode) {
    $this->shopperZipcode = (strlen($shopperZipcode) > 10) ? substr((string) $shopperZipcode, 0, 10) : (string) $shopperZipcode;
    return ($this);
  }

  public function getShopperCity() {
    return (!C2PValidate::isEmpty($this->shopperCity)) ? $this->shopperCity : Connect2PayClient::UNAVAILABLE;
  }

  public function setShopperCity($shopperCity) {
    $this->shopperCity = (strlen($shopperCity) > 50) ? substr((string) $shopperCity, 0, 50) : (string) $shopperCity;
    return ($this);
  }

  public function getShopperCountryCode() {
    return (!C2PValidate::isEmpty($this->shopperCountryCode)) ? $this->shopperCountryCode : Connect2PayClient::UNAVAILABLE_COUNTRY;
  }

  public function setShopperCountryCode($shopperCountryCode) {
    $this->shopperCountryCode = (strlen($shopperCountryCode) > 2) ? substr((string) $shopperCountryCode, 0, 2) : (string) $shopperCountryCode;
    return ($this);
  }

  public function getShopperBirthDate() {
    return $this->shopperBirthDate;
  }

  public function setShopperBirthDate($shopperBirthDate) {
    $this->shopperBirthDate = (strlen($shopperBirthDate) > 8) ? substr((string) $shopperBirthDate, 0, 8) : (string) $shopperBirthDate;
    return ($this);
  }

  public function getShopperIDNumber() {
    return $this->shopperIDNumber;
  }

  public function setShopperIDNumber($shopperIDNumber) {
    $this->shopperIDNumber = (strlen($shopperIDNumber) > 32) ? substr((string) $shopperIDNumber, 0, 32) : (string) $shopperIDNumber;
    return ($this);
  }

  public function getShopperCompany() {
    return $this->shopperCompany;
  }

  public function setShopperCompany($shopperCompany) {
    $this->shopperCompany = (strlen($shopperCompany) > 128) ? substr((string) $shopperCompany, 0, 128) : (string) $shopperCompany;
    return ($this);
  }

  public function getShopperLoyaltyProgram() {
    return $this->shopperLoyaltyProgram;
  }

  public function setShopperLoyaltyProgram($shopperLoyaltyProgram) {
    $this->shopperLoyaltyProgram = (string) $shopperLoyaltyProgram;
    return ($this);
  }

  public function getAffiliateID() {
    return $this->affiliateID;
  }

  public function setAffiliateID($affiliateID) {
    $this->affiliateID = (strlen($affiliateID) > 16) ? substr($affiliateID, 0, 16) : $affiliateID;
    return ($this);
  }

  public function getCampaignName() {
    return $this->campaignName;
  }

  public function setCampaignName($campaignName) {
    $this->campaignName = (strlen($campaignName) > 128) ? substr($campaignName, 0, 128) : $campaignName;
    return ($this);
  }

  public function getOrderID() {
    return $this->orderID;
  }

  public function setOrderID($orderID) {
    $this->orderID = (string) $orderID;
    return ($this);
  }

  public function getOrderDescription() {
    return $this->orderDescription;
  }

  public function setOrderDescription($orderDescription) {
    $this->orderDescription = (strlen($orderDescription) > 500) ? substr((string) $orderDescription, 0, 500) : (string) $orderDescription;
    return ($this);
  }

  public function getCurrency() {
    return $this->currency;
  }

  public function setCurrency($currency) {
    $this->currency = (string) $currency;
    return ($this);
  }

  public function getAmount() {
    return $this->amount;
  }

  public function setAmount($amount) {
    $this->amount = (int) $amount;
    return ($this);
  }

  public function getOrderTotalWithoutShipping() {
    return $this->orderTotalWithoutShipping;
  }

  public function setOrderTotalWithoutShipping($orderTotalWithoutShipping) {
    $this->orderTotalWithoutShipping = (int) $orderTotalWithoutShipping;
    return ($this);
  }

  public function getOrderShippingPrice() {
    return $this->orderShippingPrice;
  }

  public function setOrderShippingPrice($orderShippingPrice) {
    $this->orderShippingPrice = (int) $orderShippingPrice;
    return ($this);
  }

  public function getOrderDiscount() {
    return $this->orderDiscount;
  }

  public function setOrderDiscount($orderDiscount) {
    $this->orderDiscount = (int) $orderDiscount;
    return ($this);
  }

  /**
   *
   * @deprecated This field is not present anymore in the API, the value is
   *             obtained from the connected user
   */
  public function getCustomerIP() {
    return null;
  }

  /**
   *
   * @deprecated This field is not present anymore in the API, the value is
   *             obtained from the connected user
   */
  public function setCustomerIP($customerIP) {
    return ($this);
  }

  public function getOrderFOLanguage() {
    return $this->orderFOLanguage;
  }

  public function setOrderFOLanguage($orderFOLanguage) {
    $this->orderFOLanguage = (string) $orderFOLanguage;
    return ($this);
  }

  public function getOrderCartContent() {
    return $this->orderCartContent;
  }

  public function setOrderCartContent($orderCartContent) {
    $this->orderCartContent = $orderCartContent;
    return ($this);
  }

  /**
   * Add a CartProduct in the orderCartContent.
   *
   * @param CartProduct $cartProduct
   *          The product to add to the cart
   * @return Connect2PayClient
   */
  public function addCartProduct($cartProduct) {
    if ($this->orderCartContent == null || !is_array($this->orderCartContent)) {
      $this->orderCartContent = array();
    }

    if ($cartProduct instanceof CartProduct) {
      $this->orderCartContent[] = $cartProduct;
    }

    return $this;
  }

  public function getShippingType() {
    return $this->shippingType;
  }

  public function setShippingType($shippingType) {
    $this->shippingType = (string) $shippingType;
    return ($this);
  }

  public function getShippingName() {
    return $this->shippingName;
  }

  public function setShippingName($shippingName) {
    $this->shippingName = (string) $shippingName;
    return ($this);
  }

  /**
   *
   * @deprecated
   */
  public function getPaymentType() {
    Utils::deprecation_error('Use getPaymentMethod().');
    return $this->getPaymentMethod();
  }

  /**
   *
   * @deprecated
   */
  public function setPaymentType($paymentType) {
    Utils::deprecation_error('Use setPaymentMethod().');
    return ($this->setPaymentMethod($paymentType));
  }

  public function getPaymentMethod() {
    return (!C2PValidate::isEmpty($this->paymentMethod)) ? $this->paymentMethod : Connect2PayClient::PAYMENT_METHOD_CREDITCARD;
  }

  public function setPaymentMethod($paymentMethod) {
    $this->paymentMethod = (string) $paymentMethod;
    return ($this);
  }

  /**
   *
   * @deprecated
   */
  public function getProvider() {
    Utils::deprecation_error('Use getPaymentNetwork().');
    return $this->getPaymentNetwork();
  }

  /**
   *
   * @deprecated
   */
  public function setProvider($provider) {
    Utils::deprecation_error('Use setPaymentNetwork().');
    return $this->setPaymentNetwork($provider);
  }

  public function getPaymentNetwork() {
    return $this->paymentNetwork;
  }

  public function setPaymentNetwork($paymentNetwork) {
    $this->paymentNetwork = $paymentNetwork;
    return $this;
  }

  public function getOperation() {
    return $this->operation;
  }

  public function setOperation($operation) {
    $this->operation = (string) $operation;
    return ($this);
  }

  public function getPaymentMode() {
    return $this->paymentMode;
  }

  public function setPaymentMode($paymentMode) {
    $this->paymentMode = (string) $paymentMode;
    return ($this);
  }

  public function getOfferID() {
    return $this->offerID;
  }

  public function setOfferID($offerID) {
    $this->offerID = (int) $offerID;
    return ($this);
  }

  public function getSubscriptionType() {
    return $this->subscriptionType;
  }

  public function setSubscriptionType($subscriptionType) {
    $this->subscriptionType = $subscriptionType;
    return ($this);
  }

  public function getTrialPeriod() {
    return $this->trialPeriod;
  }

  public function setTrialPeriod($trialPeriod) {
    $this->trialPeriod = $trialPeriod;
    return ($this);
  }

  public function getRebillAmount() {
    return $this->rebillAmount;
  }

  public function setRebillAmount($rebillAmount) {
    $this->rebillAmount = (int) $rebillAmount;
    return ($this);
  }

  public function getRebillPeriod() {
    return $this->rebillPeriod;
  }

  public function setRebillPeriod($rebillPeriod) {
    $this->rebillPeriod = $rebillPeriod;
    return ($this);
  }

  public function getRebillMaxIteration() {
    return $this->rebillMaxIteration;
  }

  public function setRebillMaxIteration($rebillMaxIteration) {
    $this->rebillMaxIteration = (int) $rebillMaxIteration;
    return ($this);
  }

  public function getCtrlRedirectURL() {
    return $this->ctrlRedirectURL;
  }

  public function setCtrlRedirectURL($ctrlRedirectURL) {
    $this->ctrlRedirectURL = (string) $ctrlRedirectURL;
    return ($this);
  }

  public function getCtrlCallbackURL() {
    return $this->ctrlCallbackURL;
  }

  public function setCtrlCallbackURL($ctrlCallbackURL) {
    $this->ctrlCallbackURL = (string) $ctrlCallbackURL;
    return ($this);
  }

  public function getCtrlCustomData() {
    return $this->ctrlCustomData;
  }

  public function setCtrlCustomData($ctrlCustomData) {
    $this->ctrlCustomData = (string) $ctrlCustomData;
    return ($this);
  }

  public function getTimeOut() {
    return $this->timeOut;
  }

  public function setTimeOut($timeOut) {
    $this->timeOut = (string) $timeOut;
    return ($this);
  }

  public function getMerchantNotification() {
    return $this->merchantNotification;
  }

  public function setMerchantNotification($merchantNotification) {
    $this->merchantNotification = $merchantNotification;
    return ($this);
  }

  public function getMerchantNotificationTo() {
    return $this->merchantNotificationTo;
  }

  public function setMerchantNotificationTo($merchantNotificationTo) {
    $this->merchantNotificationTo = $merchantNotificationTo;
    return ($this);
  }

  public function getMerchantNotificationLang() {
    return $this->merchantNotificationLang;
  }

  public function setMerchantNotificationLang($merchantNotificationLang) {
    $this->merchantNotificationLang = $merchantNotificationLang;
    return ($this);
  }

  public function getThemeID() {
    return $this->themeID;
  }

  public function setThemeID($themeID) {
    $this->themeID = (int) $themeID;
    return ($this);
  }

  public function getReturnCode() {
    return $this->returnCode;
  }

  public function getReturnMessage() {
    return $this->returnMessage;
  }

  public function getMerchantToken() {
    return $this->merchantToken;
  }

  public function getCustomerToken() {
    return $this->customerToken;
  }

  public function getStatus() {
    return $this->status;
  }

  public function getClientErrorMessage() {
    return $this->clientErrorMessage;
  }

  public function getCurrencyHelper() {
    if ($this->currencyHelper == null) {
      $this->currencyHelper = new Connect2PayCurrencyHelper();

      $this->currencyHelper->useProxy($this->proxy_host, $this->proxy_port, $this->proxy_password, $this->proxy_username);
    }

    return $this->currencyHelper;
  }

  /**
   * Set a default cart content, to be used when anti fraud system is enabled
   * and no real cart is known
   */
  public function setDefaultOrderCartContent() {
    $this->orderCartContent = array();
    $product = new CartProduct();
    $product->setCartProductId(0)->setCartProductName("NA");
    $product->setCartProductUnitPrice(0)->setCartProductQuantity(1);
    $product->setCartProductBrand("NA")->setCartProductMPN("NA");
    $product->setCartProductCategoryName("NA")->setCartProductCategoryID(0);

    $this->orderCartContent[] = $product;
  }

  /**
   * Check for fields validity
   *
   * @return array empty if everything is OK or as many elements as errors
   *         matched
   */
  private function validateFields() {
    $fieldsRequired = $this->fieldsRequired;
    $returnError = array();

    foreach ($fieldsRequired as $field) {
      if (C2PValidate::isEmpty($this->{$field}) && (!is_numeric($this->{$field})))
        $returnError[] = $field . ' is empty';
    }

    foreach ($this->fieldsSize as $field => $size) {
      if (isset($this->{$field}) && C2PValidate::strlen($this->{$field}) > $size)
        $returnError[] = $field . ' Length ' . $size;
    }

    foreach ($this->fieldsValidate as $field => $method) {
      if (!C2PValidate::isEmpty($this->{$field}) && !call_user_func(array('PayXpert\Connect2Pay\C2PValidate', $method), $this->{$field}))
        $returnError[] = $field . ' = ' . $this->{$field};
    }

    return $returnError;
  }
}

class Container {

  protected static function copyScalarProperties($properties, $src, &$dest) {
    if ($properties !== null && is_object($src) && is_object($dest)) {
      foreach ($properties as $property) {
        if (isset($src->{$property->getName()}) && is_scalar($src->{$property->getName()})) {
          $dest->{"set" . ucfirst($property->getName())}($src->{$property->getName()});
        }
      }
    }
  }
}

/**
 * Contains the information returned by the account information API call
 */
class AccountInformation extends Container {
  /**
   * API version of the response
   *
   * @var string
   */
  private $apiVersion;

  /**
   * The displayed name of the account
   *
   * @var string
   */
  private $name;

  /**
   * Indicates if Terms and conditions must be acknowledged on the payment page
   * by the shopper
   *
   * @var boolean
   */
  private $displayTerms;

  /**
   * Terms and conditions URL
   *
   * @var string
   */
  private $termsUrl;

  /**
   * URL of customer support
   *
   * @var string
   */
  private $supportUrl;

  /**
   * The number of attempts allowed to process the payment in case of failure
   *
   * @var integer
   */
  private $maxAttempts;

  /**
   * Name displayed in customers notification emails
   *
   * @var string
   */
  private $notificationSenderName;

  /**
   * Email displayed in customers notification emails
   *
   * @var string
   */
  private $notificationSenderEmail;

  /**
   * Indicates if a notification is sent to the shopper in case of payment
   * success
   *
   * @var boolean
   */
  private $notificationOnSuccess;

  /**
   * Indicates if a notification is sent to the shopper in case of payment
   * failure
   *
   * @var boolean
   */
  private $notificationOnFailure;

  /**
   * Indicates if a notification is sent to the merchant after a payment
   *
   * @var boolean
   */
  private $merchantNotification;

  /**
   * Email used to send merchant notification
   *
   * @var string
   */
  private $merchantNotificationTo;

  /**
   * The language used in merchant email notification (ISO-639 two letters code)
   *
   * @var string
   */
  private $merchantNotificationLang;

  /**
   * A list of payment methods available for the account
   *
   * @var PaymentMethodInformation[]
   */
  private $paymentMethods;

  public function getApiVersion() {
    return $this->apiVersion;
  }

  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
    return $this;
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function getDisplayTerms() {
    return $this->displayTerms;
  }

  public function setDisplayTerms($displayTerms) {
    $this->displayTerms = $displayTerms;
    return $this;
  }

  public function getTermsUrl() {
    return $this->termsUrl;
  }

  public function setTermsUrl($termsUrl) {
    $this->termsUrl = $termsUrl;
    return $this;
  }

  public function getSupportUrl() {
    return $this->supportUrl;
  }

  public function setSupportUrl($supportUrl) {
    $this->supportUrl = $supportUrl;
    return $this;
  }

  public function getMaxAttempts() {
    return $this->maxAttempts;
  }

  public function setMaxAttempts($maxAttempts) {
    $this->maxAttempts = $maxAttempts;
    return $this;
  }

  public function getNotificationSenderName() {
    return $this->notificationSenderName;
  }

  public function setNotificationSenderName($notificationSenderName) {
    $this->notificationSenderName = $notificationSenderName;
    return $this;
  }

  public function getNotificationSenderEmail() {
    return $this->notificationSenderEmail;
  }

  public function setNotificationSenderEmail($notificationSenderEmail) {
    $this->notificationSenderEmail = $notificationSenderEmail;
    return $this;
  }

  public function getNotificationOnSuccess() {
    return $this->notificationOnSuccess;
  }

  public function setNotificationOnSuccess($notificationOnSuccess) {
    $this->notificationOnSuccess = $notificationOnSuccess;
    return $this;
  }

  public function getNotificationOnFailure() {
    return $this->notificationOnFailure;
  }

  public function setNotificationOnFailure($notificationOnFailure) {
    $this->notificationOnFailure = $notificationOnFailure;
    return $this;
  }

  public function getMerchantNotification() {
    return $this->merchantNotification;
  }

  public function setMerchantNotification($merchantNotification) {
    $this->merchantNotification = $merchantNotification;
    return $this;
  }

  public function getMerchantNotificationTo() {
    return $this->merchantNotificationTo;
  }

  public function setMerchantNotificationTo($merchantNotificationTo) {
    $this->merchantNotificationTo = $merchantNotificationTo;
    return $this;
  }

  public function getMerchantNotificationLang() {
    return $this->merchantNotificationLang;
  }

  public function setMerchantNotificationLang($merchantNotificationLang) {
    $this->merchantNotificationLang = $merchantNotificationLang;
    return $this;
  }

  public function getPaymentMethods() {
    return $this->paymentMethods;
  }

  public function setPaymentMethods($paymentMethods) {
    $this->paymentMethods = $paymentMethods;
    return $this;
  }

  public static function getFromJson($infoJson) {
    $accountInfo = null;

    if ($infoJson != null && is_object($infoJson)) {
      // Root element, AccountInformation
      $accountInfo = new AccountInformation();
      $reflector = new \ReflectionClass('PayXpert\Connect2Pay\AccountInformation');
      self::copyScalarProperties($reflector->getProperties(), $infoJson, $accountInfo);

      // Payment Method Information
      if (isset($infoJson->paymentMethods) && is_array($infoJson->paymentMethods)) {
        $paymentMethods = array();

        foreach ($infoJson->paymentMethods as $paymentMethod) {
          $pmi = new PaymentMethodInformation();
          $reflector = new \ReflectionClass('PayXpert\Connect2Pay\PaymentMethodInformation');
          self::copyScalarProperties($reflector->getProperties(), $paymentMethod, $pmi);

          // Currencies array
          if (isset($paymentMethod->currencies) && is_array($paymentMethod->currencies)) {
            $pmi->setCurrencies($paymentMethod->currencies);
          }

          // Payment Method Options
          if (isset($paymentMethod->options) && is_array($paymentMethod->options)) {
            $paymentMethodOptions = array();

            foreach ($paymentMethod->options as $option) {
              $pmo = new PaymentMethodOption();
              $reflector = new \ReflectionClass('PayXpert\Connect2Pay\PaymentMethodOption');
              self::copyScalarProperties($reflector->getProperties(), $option, $pmo);

              $paymentMethodOptions[] = $pmo;
            }

            $pmi->setOptions($paymentMethodOptions);
          }

          $paymentMethods[] = $pmi;
        }

        $accountInfo->setPaymentMethods($paymentMethods);
      }
    }

    return $accountInfo;
  }
}

class PaymentMethodInformation {
  /**
   * The payment method
   *
   * @var string
   */
  private $paymentMethod;

  /**
   * The payment network
   *
   * @var string
   */
  private $paymentNetwork;

  /**
   * A list of ISO 4217 currency code for which this payment method is enabled
   *
   * @var string[]
   */
  private $currencies;

  /**
   * The operation that will be executed by default when processing this type of
   * method.
   * Can be sale, authorize or collection
   *
   * @var string
   */
  private $defaultOperation;

  /**
   * A list of payment method specific option
   *
   * @var PaymentMethodOption[]
   */
  private $options;

  public function getPaymentMethod() {
    return $this->paymentMethod;
  }

  public function setPaymentMethod($paymentMethod) {
    $this->paymentMethod = $paymentMethod;
    return $this;
  }

  public function getPaymentNetwork() {
    return $this->paymentNetwork;
  }

  public function setPaymentNetwork($paymentNetwork) {
    $this->paymentNetwork = $paymentNetwork;
    return $this;
  }

  public function getCurrencies() {
    return $this->currencies;
  }

  public function setCurrencies($currencies) {
    $this->currencies = $currencies;
    return $this;
  }

  public function getDefaultOperation() {
    return $this->defaultOperation;
  }

  public function setDefaultOperation($defaultOperation) {
    $this->defaultOperation = $defaultOperation;
    return $this;
  }

  public function getOptions() {
    return $this->options;
  }

  public function setOptions($options) {
    $this->options = $options;
    return $this;
  }
}

class PaymentMethodOption {
  /**
   * The name of the option
   *
   * @var string
   */
  private $name;

  /**
   * The value of the option
   *
   * @var string
   */
  private $value;

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function getValue() {
    return $this->value;
  }

  public function setValue($value) {
    $this->value = $value;
    return $this;
  }
}

/**
 * Represent the status of a payment returned by the payment page
 */
class PaymentStatus extends Container {
  /**
   * Status of the payment: "Authorized", "Not authorized", "Expired", "Call
   * failed", "Pending" or "Not processed"
   *
   * @var String
   */
  private $status;

  /**
   * The merchant token of this payment
   *
   * @var String
   */
  private $merchantToken;

  /**
   * Type of operation for the last transaction done for this payment: Can be
   * sale or authorize.
   *
   * @var String
   */
  private $operation;

  /**
   * Result code of the last transaction done for this payment
   *
   * @var Int
   */
  private $errorCode;

  /**
   * Error message of the last transaction done for this payment
   *
   * @var String
   */
  private $errorMessage;

  /**
   * The order ID of the payment
   *
   * @var String
   */
  private $orderID;

  /**
   * Currency for the payment
   *
   * @var String
   */
  private $currency;

  /**
   * Amount of the payment in cents (1.00€ => 100)
   *
   * @var Int
   */
  private $amount;

  /**
   * Custom data provided by merchant at payment creation.
   *
   * @var String
   */
  private $ctrlCustomData;

  /**
   * The list of transactions done to complete this payment
   *
   * @var array
   */
  private $transactions;

  public function getStatus() {
    return $this->status;
  }

  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  public function getMerchantToken() {
    return $this->merchantToken;
  }

  public function setMerchantToken($merchantToken) {
    $this->merchantToken = $merchantToken;
    return $this;
  }

  public function getOperation() {
    return $this->operation;
  }

  public function setOperation($operation) {
    $this->operation = $operation;
    return $this;
  }

  public function getErrorCode() {
    return $this->errorCode;
  }

  public function setErrorCode($errorCode) {
    $this->errorCode = $errorCode;
    return $this;
  }

  public function getErrorMessage() {
    return $this->errorMessage;
  }

  public function setErrorMessage($errorMessage) {
    $this->errorMessage = $errorMessage;
    return $this;
  }

  public function getOrderID() {
    return $this->orderID;
  }

  public function setOrderID($orderID) {
    $this->orderID = $orderID;
    return $this;
  }

  public function getCurrency() {
    return $this->currency;
  }

  public function setCurrency($currency) {
    $this->currency = $currency;
    return $this;
  }

  public function getAmount() {
    return $this->amount;
  }

  public function setAmount($amount) {
    $this->amount = $amount;
    return $this;
  }

  public function getCtrlCustomData() {
    return $this->ctrlCustomData;
  }

  public function setCtrlCustomData($ctrlCustomData) {
    $this->ctrlCustomData = $ctrlCustomData;
    return $this;
  }

  public function getTransactions() {
    return $this->transactions;
  }

  public function setTransactions($transactions) {
    $this->transactions = $transactions;
    return $this;
  }

  /**
   *
   * @deprecated Use getLastInitialTransactionAttempt()
   * @return \PayXpert\Connect2Pay\TransactionAttempt
   */
  public function getLastTransactionAttempt() {
    return $this->getLastInitialTransactionAttempt();
  }

  /**
   * Return the last initial transaction attempt done for this payment.
   * Only returns sale, authorize or submission. Transactions with a referral
   * are not considered by this method, only initial transactions done by the
   * customer to complete the payment.
   *
   * @return TransactionAttempt The last transaction attempt done for this
   *         payment
   */
  public function getLastInitialTransactionAttempt() {
    $lastAttempt = null;

    if (isset($this->transactions) && is_array($this->transactions) && count($this->transactions) > 0) {
      // Return the entry with the highest timestamp with type sale, authorize,
      // or submission
      foreach ($this->transactions as $transaction) {
        if (in_array($transaction->getOperation(), array("sale", "authorize", "submission")) && $transaction->getRefTransactionID() == null) {
          if ($lastAttempt == null || $lastAttempt->getDate() < $transaction->getDate()) {
            $lastAttempt = $transaction;
          }
        }
      }
    }

    return $lastAttempt;
  }

  /**
   * Get the transaction attempt referring to the provided transactionId with
   * the given operation.
   * In case several transactions are found will return the older one. Used for
   * example to retrieve the collection for a submission.
   *
   * @param
   *          refTransactionId The transaction identifier of the referral
   *          (initial) transaction
   * @param
   *          transactionOperation The operation of the transaction to retrieve
   *          (collection, refund...)
   * @return TransactionAttempt The TransactionAttempt found or null if not
   *         found
   */
  public function getReferringTransactionAttempt($refTransactionId, $transactionOperation) {
    $attempts = $this->getReferringTransactionAttempts($refTransactionId, $transactionOperation);

    if (is_array($attempts) && count($attempts) > 0) {
      return $attempts[0];
    }

    return null;
  }

  /**
   * Get the transaction attempts referring to the provided transactionId with
   * the given operation.
   *
   * @param
   *          refTransactionId The transaction identifier of the referral
   *          (initial) transaction
   * @param
   *          transactionOperation The operation of the transactions to retrieve
   *          (collection, refund...). If null, all operations are returned.
   *
   * @return array TransactionAttempt A list with the transactions found (sorted
   *         by date, older first) or
   *         an empty list if not found
   */
  public function getReferringTransactionAttempts($refTransactionId, $transactionOperation = null) {
    $attempts = array();

    if ($refTransactionId !== null && isset($this->transactions) && is_array($this->transactions) && count($this->transactions) > 0) {
      foreach ($this->transactions as $transaction) {
        if ($refTransactionId === $transaction->getRefTransactionId() &&
            ($transactionOperation == null || $transactionOperation === $transaction->getOperation())) {
          $attempts[] = $transaction;
        }
      }

      // Sort the array by transaction date ascending
      if (count($attempts) > 1) {
        usort($attempts,
            function ($t1, $t2) {
              $date1 = $t1->getDate();
              $date2 = $t2->getDate();

              if ($date1 === null && $date2 !== null) {
                return -1;
              }
              if ($date1 !== null && $date2 === null) {
                return 1;
              }

              return ($date1 === $date2 ? 0 : ($date1 < $date2 ? -1 : 1));
            });
      }
    }

    return $attempts;
  }

  public static function getFromJson($statusJson) {
    $paymentStatus = null;

    if ($statusJson != null && is_object($statusJson)) {
      // Root element, PaymentStatus
      $paymentStatus = new PaymentStatus();
      $reflector = new \ReflectionClass('PayXpert\Connect2Pay\PaymentStatus');
      self::copyScalarProperties($reflector->getProperties(), $statusJson, $paymentStatus);

      // Transaction attempts
      if (isset($statusJson->transactions) && is_array($statusJson->transactions)) {
        $transactionAttempts = array();

        foreach ($statusJson->transactions as $transaction) {
          $transactionAttempts[] = TransactionAttempt::getFromJson($transaction);
        }

        $paymentStatus->setTransactions($transactionAttempts);
      }
    }

    return $paymentStatus;
  }
}

class TransactionAttempt extends Container {
  /**
   * Identifier of the payment this transaction belongs to
   *
   * @var string
   */
  private $paymentID;

  /**
   * Merchant Token of the payment this transaction belongs to
   *
   * @var string
   */
  private $paymentMerchantToken;

  /**
   * Method of payment for this transaction attempt: CreditCard, BankTransfer,
   * ToditoCash, DirectDebit...
   *
   * @var String
   */
  private $paymentMethod;

  /**
   * Payment network used when the payment method is multi networks
   *
   * @var String
   */
  private $paymentNetwork;

  /**
   * Type of operation for that transaction: sale, authorize, rebill,
   * submission, collection, refund
   *
   * @var String
   */
  private $operation;

  /**
   * Date of the transaction
   *
   * @var integer
   */
  private $date;

  /**
   * Amount of the transaction
   *
   * @var integer
   */
  private $amount;

  /**
   * Amount already refunded for this transaction
   *
   * @var integer
   */
  private $refundedAmount;

  /**
   * The currency for the transaction
   *
   * @var String
   */
  private $currency;

  /**
   * The result code for this transaction
   *
   * @var String
   */
  private $resultCode;

  /**
   * The result message for this transaction
   *
   * @var String
   */
  private $resultMessage;

  /**
   * Status of the transaction: "Authorized", "Not authorized", "Expired", "Call
   * failed", "Pending" or "Not processed"
   *
   * @var String
   */
  private $status;

  /**
   * Shopper information for this transaction
   *
   * @var Shopper
   */
  private $shopper;

  /**
   * Transaction identifier of this transaction.
   *
   * @var String
   */
  private $transactionID;

  /**
   * Identifier of the transaction this transaction refers to.
   *
   * @var String
   */
  private $refTransactionID;

  /**
   * Identifier of the transaction at the provider side.
   *
   * @var String
   */
  private $providerTransactionID;

  /**
   * Identifier of the subscription this transaction is part of (if any).
   *
   * @var Int
   */
  private $subscriptionID;

  /**
   * Details of the payment mean used to process the transaction
   *
   * @var object Depends on the paymentMethod
   */
  private $paymentMeanInfo;

  /**
   * The merchant internal unique order identifier as provided during payment creation
   *
   * @var String
   */
  private $orderID;

  /**
   * The description of the product purchased by the customer as provided during payment creation
   *
   * @var String
   */
  private $orderDescription;

  public function getPaymentID() {
    return $this->paymentID;
  }

  public function setPaymentID($paymentID) {
    $this->paymentID = $paymentID;
    return $this;
  }

  public function getPaymentMerchantToken() {
    return $this->paymentMerchantToken;
  }

  public function setPaymentMerchantToken($paymentMerchantToken) {
    $this->paymentMerchantToken = $paymentMerchantToken;
    return $this;
  }

  /**
   *
   * @deprecated Use getPaymentMethod()
   */
  public function getPaymentType() {
    Utils::deprecation_error('Use getPaymentMethod().');
    return $this->getPaymentMethod();
  }

  /**
   *
   * @deprecated Use setPaymentMethod()
   */
  public function setPaymentType($paymentType) {
    Utils::deprecation_error('Use setPaymentMethod().');
    return $this->setPaymentMethod($paymentType);
  }

  public function getPaymentMethod() {
    return $this->paymentMethod;
  }

  public function setPaymentMethod($paymentMethod) {
    $this->paymentMethod = $paymentMethod;
    return $this;
  }

  public function getPaymentNetwork() {
    return $this->paymentNetwork;
  }

  public function setPaymentNetwork($paymentNetwork) {
    $this->paymentNetwork = $paymentNetwork;
    return $this;
  }

  public function getOperation() {
    return $this->operation;
  }

  public function setOperation($operation) {
    $this->operation = $operation;
    return $this;
  }

  public function getDate() {
    return $this->date;
  }

  public function getDateAsDateTime() {
    if ($this->date != null) {
      // API returns date as timestamp in milliseconds
      $timestamp = intval($this->date / 1000);
      return new \DateTime("@" . $timestamp);
    }

    return null;
  }

  public function setDate($date) {
    $this->date = $date;
    return $this;
  }

  public function getAmount() {
    return $this->amount;
  }

  public function setAmount($amount) {
    $this->amount = $amount;
    return $this;
  }

  public function getRefundedAmount() {
    return $this->refundedAmount;
  }

  public function setRefundedAmount($refundedAmount) {
    $this->refundedAmount = $refundedAmount;
    return $this;
  }

  public function getCurrency() {
    return $this->currency;
  }

  public function setCurrency($currency) {
    $this->currency = $currency;
    return $this;
  }

  public function getResultCode() {
    return $this->resultCode;
  }

  public function setResultCode($resultCode) {
    $this->resultCode = $resultCode;
    return $this;
  }

  public function getResultMessage() {
    return $this->resultMessage;
  }

  public function setResultMessage($resultMessage) {
    $this->resultMessage = $resultMessage;
    return $this;
  }

  public function getStatus() {
    return $this->status;
  }

  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  public function getShopper() {
    return $this->shopper;
  }

  public function setShopper($shopper) {
    $this->shopper = $shopper;
    return $this;
  }

  public function getTransactionID() {
    return $this->transactionID;
  }

  public function setTransactionID($transactionID) {
    $this->transactionID = $transactionID;
    return $this;
  }

  public function getRefTransactionID() {
    return $this->refTransactionID;
  }

  public function setRefTransactionID($refTransactionID) {
    $this->refTransactionID = $refTransactionID;
    return $this;
  }

  public function getProviderTransactionID() {
    return $this->providerTransactionID;
  }

  public function setProviderTransactionID($providerTransactionID) {
    $this->providerTransactionID = $providerTransactionID;
    return $this;
  }

  /**
   *
   * @deprecated Use getPaymentNetwork()
   */
  public function getProvider() {
    Utils::deprecation_error('Use getPaymentNetwork().');
    return $this->paymentNetwork;
  }

  /**
   *
   * @deprecated Use setPaymentNetwork()
   */
  public function setProvider($provider) {
    Utils::deprecation_error('Use setPaymentNetwork().');
    return $this->setPaymentNetwork($provider);
  }

  public function getSubscriptionID() {
    return $this->subscriptionID;
  }

  public function setSubscriptionID($subscriptionID) {
    $this->subscriptionID = $subscriptionID;
    return $this;
  }

  public function getPaymentMeanInfo() {
    return $this->paymentMeanInfo;
  }

  public function setPaymentMeanInfo($paymentMeanInfo) {
    $this->paymentMeanInfo = $paymentMeanInfo;
    return $this;
  }

  public function getOrderID() {
    return $this->orderID;
  }

  public function setOrderID($orderID) {
    $this->orderID = $orderID;
    return $this;
  }

  public function setOrderDescription($orderDescription) {
    $this->orderDescription = $orderDescription;
    return $this;
  }

  public function getOrderDescription() {
    return $this->orderDescription;
  }

  public static function getFromRawJson($json) {
    return self::getFromJson(json_decode($json, false));
  }

  public static function getFromJson($transaction) {
    $transAttempt = null;

    if ($transaction != null && is_object($transaction)) {
      $transAttempt = new TransactionAttempt();

      $reflector = new \ReflectionClass('PayXpert\Connect2Pay\TransactionAttempt');
      self::copyScalarProperties($reflector->getProperties(), $transaction, $transAttempt);

      // Set the shopper
      if (isset($transaction->shopper) && is_object($transaction->shopper)) {
        $shopper = new Shopper();
        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\Shopper');

        self::copyScalarProperties($reflector->getProperties(), $transaction->shopper, $shopper);

        $transAttempt->setShopper($shopper);
      }

      // Payment Mean Info
      if (isset($transaction->paymentMethod) && isset($transaction->paymentMeanInfo) && is_object($transaction->paymentMeanInfo)) {
        $paymentMeanInfo = null;
        switch ($transaction->paymentMethod) {
          case Connect2PayClient::PAYMENT_METHOD_CREDITCARD:
            $paymentMeanInfo = self::extractCreditCardPaymentMeanInfo($transaction->paymentMeanInfo);
            break;
          case Connect2PayClient::PAYMENT_METHOD_TODITOCASH:
            $paymentMeanInfo = self::extractToditoCashPaymentMeanInfo($transaction->paymentMeanInfo);
            break;
          case Connect2PayClient::PAYMENT_METHOD_BANKTRANSFER:
            $paymentMeanInfo = self::extractBankTransferPaymentMeanInfo($transaction->paymentMeanInfo);
            break;
          case Connect2PayClient::PAYMENT_METHOD_DIRECTDEBIT:
            $paymentMeanInfo = self::extractDirectDebitPaymentMeanInfo($transaction->paymentMeanInfo);
            break;
        }

        if ($paymentMeanInfo !== null) {
          $transAttempt->setPaymentMeanInfo($paymentMeanInfo);
        }
      }
    }

    return $transAttempt;
  }

  private static function extractCreditCardPaymentMeanInfo($paymentMeanInfo) {
    $ccInfo = new CreditCardPaymentMeanInfo();
    $reflector = new \ReflectionClass('PayXpert\Connect2Pay\CreditCardPaymentMeanInfo');
    self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $ccInfo);

    return $ccInfo;
  }

  private static function extractToditoCashPaymentMeanInfo($paymentMeanInfo) {
    $tcInfo = new ToditoCashPaymentMeanInfo();
    $reflector = new \ReflectionClass('PayXpert\Connect2Pay\ToditoCashPaymentMeanInfo');
    self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $tcInfo);

    return $tcInfo;
  }

  private static function extractBankTransferPaymentMeanInfo($paymentMeanInfo) {
    $btInfo = new BankTransferPaymentMeanInfo();
    $reflector = new \ReflectionClass('PayXpert\Connect2Pay\BankAccount');

    if (isset($paymentMeanInfo->sender) && is_object($paymentMeanInfo->sender)) {
      $sender = new BankAccount();
      self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->sender, $sender);
      $btInfo->setSender($sender);
    }

    if (isset($paymentMeanInfo->recipient) && is_object($paymentMeanInfo->recipient)) {
      $recipient = new BankAccount();
      self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->recipient, $recipient);
      $btInfo->setRecipient($recipient);
    }

    return $btInfo;
  }

  private static function extractDirectDebitPaymentMeanInfo($paymentMeanInfo) {
    $ddInfo = new DirectDebitPaymentMeanInfo();

    $reflector = new \ReflectionClass('PayXpert\Connect2Pay\DirectDebitPaymentMeanInfo');
    self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo, $ddInfo);

    if (is_object($paymentMeanInfo->bankAccount)) {
      $reflector = new \ReflectionClass('PayXpert\Connect2Pay\BankAccount');
      $account = new BankAccount();
      self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->bankAccount, $account);

      if (is_object($paymentMeanInfo->bankAccount->sepaMandate)) {
        $reflector = new \ReflectionClass('PayXpert\Connect2Pay\SepaMandate');
        $mandate = new SepaMandate();
        self::copyScalarProperties($reflector->getProperties(), $paymentMeanInfo->bankAccount->sepaMandate, $mandate);
        $account->setSepaMandate($mandate);
      }

      $ddInfo->setBankAccount($account);
    }

    return $ddInfo;
  }
}

class Shopper {
  /**
   * Name provided by the shopper
   *
   * @var String
   */
  private $name;

  /**
   * Address provided by the shopper
   *
   * @var String
   */
  private $address;

  /**
   * Zipcode provided by the shopper.
   *
   * @var String
   */
  private $zipcode;

  /**
   * City provided by the shopper.
   *
   * @var String
   */
  private $city;

  /**
   * State provided by the shopper
   *
   * @var String
   */
  private $state;

  /**
   * Country provided by the shopper.
   *
   * @var String
   */
  private $countryCode;

  /**
   * Phone provided by the shopper
   *
   * @var String
   */
  private $phone;

  /**
   * Email address provided by the shopper.
   *
   * @var String
   */
  private $email;

  /**
   * Birth date provided by the shopper (YYYYMMDD)
   *
   * @var string
   */
  private $birthDate;

  /**
   * ID number provided by the shopper (identity card, passport...)
   *
   * @var string
   */
  private $idNumber;

  /**
   * IP address of the shopper
   *
   * @var String
   */
  private $ipAddress;

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function getAddress() {
    return $this->address;
  }

  public function setAddress($address) {
    $this->address = $address;
    return $this;
  }

  public function getZipcode() {
    return $this->zipcode;
  }

  public function setZipcode($zipcode) {
    $this->zipcode = $zipcode;
    return $this;
  }

  public function getCity() {
    return $this->city;
  }

  public function setCity($city) {
    $this->city = $city;
    return $this;
  }

  public function getState() {
    return $this->state;
  }

  public function setState($state) {
    $this->state = $state;
    return $this;
  }

  public function getCountryCode() {
    return $this->countryCode;
  }

  public function setCountryCode($countryCode) {
    $this->countryCode = $countryCode;
    return $this;
  }

  public function getPhone() {
    return $this->phone;
  }

  public function setPhone($phone) {
    $this->phone = $phone;
    return $this;
  }

  public function getEmail() {
    return $this->email;
  }

  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }

  public function getBirthDate() {
    return $this->birthDate;
  }

  public function setBirthDate($birthDate) {
    $this->birthDate = $birthDate;
    return $this;
  }

  public function getIdNumber() {
    return $this->idNumber;
  }

  public function setIdNumber($idNumber) {
    $this->idNumber = $idNumber;
    return $this;
  }

  public function getIpAddress() {
    return $this->ipAddress;
  }

  public function setIpAddress($ipAddress) {
    $this->ipAddress = $ipAddress;
    return $this;
  }
}

class CreditCardPaymentMeanInfo {
  /**
   * The truncated card number used for this transaction
   *
   * @var string
   */
  private $cardNumber;

  /**
   * The card expiration year
   *
   * @var string
   */
  private $cardExpireYear;

  /**
   * The card expire month
   *
   * @var string
   */
  private $cardExpireMonth;

  /**
   * The name of the holder of the card
   *
   * @var string
   */
  private $cardHolderName;

  /**
   * Brand of the card (Visa, Mcrd...)
   *
   * @var string
   */
  private $cardBrand;

  /**
   * Level of the card.
   * Special permission needed
   *
   * @var string
   */
  private $cardLevel;

  /**
   * Sub type of the card.
   * Special permission needed.
   *
   * @var string
   */
  private $cardSubType;

  /**
   * ISO2 country code of the issuer of the card.
   * Special permission needed.
   *
   * @var string
   */
  private $iinCountry;

  /**
   * Card Issuer Bank Name.
   * Special permission needed.
   *
   * @var string
   */
  private $iinBankName;

  /**
   * The liability shift for 3D Secure.
   * Can be true or false
   *
   * @var boolean
   */
  private $is3DSecure;

  /**
   * Credit Card Descriptor for this transaction
   *
   * @var String
   */
  private $statementDescriptor;

  public function getCardNumber() {
    return $this->cardNumber;
  }

  public function setCardNumber($cardNumber) {
    $this->cardNumber = $cardNumber;
    return $this;
  }

  public function getCardExpireYear() {
    return $this->cardExpireYear;
  }

  public function setCardExpireYear($cardExpireYear) {
    $this->cardExpireYear = $cardExpireYear;
    return $this;
  }

  public function getCardExpireMonth() {
    return $this->cardExpireMonth;
  }

  public function setCardExpireMonth($cardExpireMonth) {
    $this->cardExpireMonth = $cardExpireMonth;
    return $this;
  }

  public function getCardHolderName() {
    return $this->cardHolderName;
  }

  public function setCardHolderName($cardHolderName) {
    $this->cardHolderName = $cardHolderName;
    return $this;
  }

  public function getCardBrand() {
    return $this->cardBrand;
  }

  public function setCardBrand($cardBrand) {
    $this->cardBrand = $cardBrand;
    return $this;
  }

  public function getCardLevel() {
    return $this->cardLevel;
  }

  public function setCardLevel($cardLevel) {
    $this->cardLevel = $cardLevel;
    return $this;
  }

  public function getCardSubType() {
    return $this->cardSubType;
  }

  public function setCardSubType($cardSubType) {
    $this->cardSubType = $cardSubType;
    return $this;
  }

  public function getIinCountry() {
    return $this->iinCountry;
  }

  public function setIinCountry($iinCountry) {
    $this->iinCountry = $iinCountry;
    return $this;
  }

  public function getIinBankName() {
    return $this->iinBankName;
  }

  public function setIinBankName($iinBankName) {
    $this->iinBankName = $iinBankName;
    return $this;
  }

  public function getIs3DSecure() {
    return $this->is3DSecure;
  }

  public function setIs3DSecure($is3DSecure) {
    $this->is3DSecure = $is3DSecure;
    return $this;
  }

  public function getStatementDescriptor() {
    return $this->statementDescriptor;
  }

  public function setStatementDescriptor($statementDescriptor) {
    $this->statementDescriptor = $statementDescriptor;
    return $this;
  }
}

class ToditoCashPaymentMeanInfo {
  /**
   * The truncated Todito card number used for this transaction
   *
   * @var string
   */
  private $cardNumber;

  public function getCardNumber() {
    return $this->cardNumber;
  }

  public function setCardNumber($cardNumber) {
    $this->cardNumber = $cardNumber;
    return $this;
  }
}

class BankTransferPaymentMeanInfo {
  /**
   * Sender account
   *
   * @var BankAccount
   */
  private $sender;

  /**
   * Recipient account
   *
   * @var BankAccount
   */
  private $recipient;

  public function getSender() {
    return $this->sender;
  }

  public function setSender($sender) {
    $this->sender = $sender;
    return $this;
  }

  public function getRecipient() {
    return $this->recipient;
  }

  public function setRecipient($recipient) {
    $this->recipient = $recipient;
    return $this;
  }
}

class DirectDebitPaymentMeanInfo {
  /**
   * Bank account
   *
   * @var BankAccount
   */
  private $bankAccount;

  /**
   * Statement Descriptor
   *
   * @var String
   */
  private $statementDescriptor;

  /**
   * Date of collection of the transaction
   *
   * @var integer
   */
  private $collectedAt;

  public function getBankAccount() {
    return $this->bankAccount;
  }

  public function setBankAccount($bankAccount) {
    $this->bankAccount = $bankAccount;
    return $this;
  }

  public function getStatementDescriptor() {
    return $this->statementDescriptor;
  }

  public function setStatementDescriptor($statementDescriptor) {
    $this->statementDescriptor = $statementDescriptor;
    return $this;
  }

  public function getCollectedAt() {
    return $this->collectedAt;
  }

  public function getCollectedAtAsDateTime() {
    if ($this->collectedAt != null) {
      // API returns date as timestamp in milliseconds
      $timestamp = intval($this->collectedAt / 1000);
      return new \DateTime("@" . $timestamp);
    }

    return null;
  }

  public function setCollectedAt($collectedAt) {
    $this->collectedAt = $collectedAt;
    return $this;
  }
}

class BankAccount {
  /**
   * The account holder name
   *
   * @var string
   */
  private $holderName;

  /**
   * Name of the bank of the account
   *
   * @var string
   */
  private $bankName;

  /**
   * IBAN number of the account (truncated)
   *
   * @var string
   */
  private $iban;

  /**
   * BIC number of the account
   *
   * @var string
   */
  private $bic;

  /**
   * ISO2 country code of the account
   *
   * @var string
   */
  private $countryCode;

  /**
   * The optional SEPA mandate associated with the account
   *
   * @var SepaMandate
   */
  private $sepaMandate;

  public function getHolderName() {
    return $this->holderName;
  }

  public function setHolderName($holderName) {
    $this->holderName = $holderName;
    return $this;
  }

  public function getBankName() {
    return $this->bankName;
  }

  public function setBankName($bankName) {
    $this->bankName = $bankName;
    return $this;
  }

  public function getIban() {
    return $this->iban;
  }

  public function setIban($iban) {
    $this->iban = $iban;
    return $this;
  }

  public function getBic() {
    return $this->bic;
  }

  public function setBic($bic) {
    $this->bic = $bic;
    return $this;
  }

  public function getCountryCode() {
    return $this->countryCode;
  }

  public function setCountryCode($countryCode) {
    $this->countryCode = $countryCode;
    return $this;
  }

  public function getSepaMandate() {
    return $this->sepaMandate;
  }

  public function setSepaMandate($sepaMandate) {
    $this->sepaMandate = $sepaMandate;
    return $this;
  }
}

class SepaMandate {
  /**
   * Description of the mandate
   *
   * @var string
   */
  private $description;

  /**
   * Status of the mandate (SIGNED...)
   *
   * @var string
   */
  private $status;

  /**
   * Type of the mandate (RECURRENT or ONETIME)
   *
   * @var string
   */
  private $type;

  /**
   * Scheme of the mandate (CORE, COR1, B2B)
   *
   * @var string
   */
  private $scheme;

  /**
   * Signature type of the mandate (CHECKBOX, SMS...)
   *
   * @var string
   */
  private $signatureType;

  /**
   * Phone number used in case of SMS signature
   *
   * @var string
   */
  private $phoneNumber;

  /**
   * Date of signature (timestamp)
   *
   * @var integer
   */
  private $signedAt;

  /**
   * Date of creation (timestamp)
   *
   * @var integer
   */
  private $createdAt;

  /**
   * Date of last use (timestamp)
   *
   * @var integer
   */
  private $lastUsedAt;

  /**
   * URL to download the mandate
   *
   * @var string
   */
  private $downloadUrl;

  /**
   * Description of the mandate
   *
   * @return string The description of the mandate
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Set the description of the mandate
   *
   * @param
   *          description
   *          The description to set
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * Status of the mandate.
   * PENDING_SIGNATURE, AUTOSIGNED, SIGNED, EXPIRED, REVOKED or USED
   *
   * @return string The current status of the mandate
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Set the current status of the mandate
   *
   * @param
   *          status
   *          The status to set
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * The type of mandate.
   * RECURRENT or ONETIME
   *
   * @return string The type of mandate
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set the current mandate type
   *
   * @param
   *          type
   *          The type to set
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * The scheme of the mandate.
   * CORE, COR1 or B2B
   *
   * @return string The scheme of the mandate
   */
  public function getScheme() {
    return $this->scheme;
  }

  /**
   * Set the current scheme of the mandate
   *
   * @param
   *          scheme
   *          The scheme to set
   */
  public function setScheme($scheme) {
    $this->scheme = $scheme;
  }

  /**
   * The type of signature used to sign the mandate.
   * CHECKBOX, BIOMETRIC or SMS
   *
   * @return string The type of signature used.
   */
  public function getSignatureType() {
    return $this->signatureType;
  }

  /**
   * Set the type of signature used to sign the mandate.
   *
   * @param
   *          signatureType
   *          The type to set
   */
  public function setSignatureType($signatureType) {
    $this->signatureType = $signatureType;
  }

  /**
   * The phone number used in case the mandate has been signed by SMS.
   *
   * @return string The phone number used for SMS signature
   */
  public function getPhoneNumber() {
    return $this->phoneNumber;
  }

  /**
   * Set the phone number
   *
   * @param
   *          phoneNumber
   *          The phone number to set
   */
  public function setPhoneNumber($phoneNumber) {
    $this->phoneNumber = $phoneNumber;
  }

  /**
   * The date of mandate signature.
   *
   * @return integer The date that mandate was signed at
   */
  public function getSignedAt() {
    return $this->signedAt;
  }

  /**
   * The date of mandate signature as DateTime.
   *
   * @return \DateTime The date that mandate was signed at as DateTime object
   */
  public function getSignedAtAsDateTime() {
    return $this->getTimestampAsDateTime($this->signedAt);
  }

  /**
   * Set the mandate signing date.
   *
   * @param
   *          signedAt
   *          The signing date to set
   */
  public function setSignedAt($signedAt) {
    $this->signedAt = $signedAt;
  }

  /**
   * The date of mandate creation.
   *
   * @return integer The date that mandate was created at
   */
  public function getCreatedAt() {
    return $this->createdAt;
  }

  /**
   * The date of mandate creation as DateTime.
   *
   * @return \DateTime The date that mandate was created at as DateTime object
   */
  public function getCreatedAtAsDateTime() {
    return $this->getTimestampAsDateTime($this->createdAt);
  }

  /**
   * Set the mandate creation date.
   *
   * @param
   *          createdAt
   *          The creation date to set
   */
  public function setCreatedAt($createdAt) {
    $this->createdAt = $createdAt;
  }

  /**
   * The date of last mandate use.
   *
   * @return integer The date that mandate was last used at
   */
  public function getLastUsedAt() {
    return $this->lastUsedAt;
  }

  /**
   * The date of last mandate use as DateTime.
   *
   * @return \DateTime The date that mandate was last used at as DateTime object
   */
  public function getLastUsedAtAsDateTime() {
    return $this->getTimestampAsDateTime($this->lastUsedAt);
  }

  /**
   * Set the mandate last used date.
   *
   * @param
   *          lastUsedAt
   *          The last used date to set
   */
  public function setLastUsedAt($lastUsedAt) {
    $this->lastUsedAt = $lastUsedAt;
  }

  /**
   * The URL at which the mandate can be downloaded
   *
   * @return string The download URL of the mandate
   */
  public function getDownloadUrl() {
    return $this->downloadUrl;
  }

  /**
   * Set the mandate download URL
   *
   * @param
   *          downloadUrl
   *          The URL to set
   */
  public function setDownloadUrl($downloadUrl) {
    $this->downloadUrl = $downloadUrl;
  }

  private function getTimestampAsDateTime($timestamp) {
    if ($timestamp != null) {
      // API returns date as timestamp in milliseconds
      $timestamp = intval($timestamp / 1000);
      return new \DateTime("@" . $timestamp);
    }

    return null;
  }
}

class OperationStatus {
  /**
   * Result code of the refund call
   *
   * @var Int
   */
  private $code;

  /**
   * Error message of the refund call
   *
   * @var String
   */
  private $message;

  /**
   * Transaction identifier of refund transaction.
   *
   * @var String
   */
  private $transactionID;

  /**
   * The effective operation done by the call
   *
   * @var String
   */
  private $operation;

  public function getCode() {
    return $this->code;
  }

  public function setCode($code) {
    $this->code = $code;
    return $this;
  }

  public function getMessage() {
    return $this->message;
  }

  public function setMessage($message) {
    $this->message = $message;
    return $this;
  }

  public function getTransactionID() {
    return $this->transactionID;
  }

  public function setTransactionID($transactionID) {
    $this->transactionID = $transactionID;
    return $this;
  }

  public function getOperation() {
    return $this->operation;
  }

  public function setOperation($operation) {
    $this->operation = $operation;
    return $this;
  }
}

class RefundStatus extends OperationStatus {
}

class RebillStatus extends OperationStatus {
}

class CancelStatus extends OperationStatus {
}

class CaptureStatus extends OperationStatus {
}

class CartProduct {
  // Fields are public otherwise json_encode can't see them...
  public $cartProductId;
  public $cartProductName;
  public $cartProductUnitPrice;
  public $cartProductQuantity;
  public $cartProductBrand;
  public $cartProductMPN;
  public $cartProductCategoryName;
  public $cartProductCategoryID;

  public function getCartProductId() {
    return $this->cartProductId;
  }

  public function setCartProductId($cartProductId) {
    $this->cartProductId = $cartProductId;
    return $this;
  }

  public function getCartProductName() {
    return $this->cartProductName;
  }

  public function setCartProductName($cartProductName) {
    $this->cartProductName = $cartProductName;
    return $this;
  }

  public function getCartProductUnitPrice() {
    return $this->cartProductUnitPrice;
  }

  public function setCartProductUnitPrice($cartProductUnitPrice) {
    $this->cartProductUnitPrice = $cartProductUnitPrice;
    return $this;
  }

  public function getCartProductQuantity() {
    return $this->cartProductQuantity;
  }

  public function setCartProductQuantity($cartProductQuantity) {
    $this->cartProductQuantity = $cartProductQuantity;
    return $this;
  }

  public function getCartProductBrand() {
    return $this->cartProductBrand;
  }

  public function setCartProductBrand($cartProductBrand) {
    $this->cartProductBrand = $cartProductBrand;
    return $this;
  }

  public function getCartProductMPN() {
    return $this->cartProductMPN;
  }

  public function setCartProductMPN($cartProductMPN) {
    $this->cartProductMPN = $cartProductMPN;
    return $this;
  }

  public function getCartProductCategoryName() {
    return $this->cartProductCategoryName;
  }

  public function setCartProductCategoryName($cartProductCategoryName) {
    $this->cartProductCategoryName = $cartProductCategoryName;
    return $this;
  }

  public function getCartProductCategoryID() {
    return $this->cartProductCategoryID;
  }

  public function setCartProductCategoryID($cartProductCategoryID) {
    $this->cartProductCategoryID = $cartProductCategoryID;
    return $this;
  }
}

class WeChatDirectProcessRequest {
  const MODE_NATIVE = "native";
  const MODE_QUICKPAY = "quickpay";
  const MODE_SDK = "sdk";
  const MODE_MINIPROGRAM = "miniprogram";

  /* ~~ */
  public $apiVersion;
  public $mode;
  public $quickPayCode;
  public $notificationLang;
  public $notificationTimeZone;
  public $openID;

  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
    return $this;
  }

  public function setMode($mode) {
    $this->mode = in_array($mode, array(self::MODE_NATIVE, self::MODE_QUICKPAY, self::MODE_SDK, self::MODE_MINIPROGRAM)) ? $mode : self::MODE_NATIVE;
    return $this;
  }

  public function setQuickPayCode($quickPayCode) {
    $this->quickPayCode = $quickPayCode;
    return $this;
  }

  public function setNotificationLang($notificationLang) {
    $this->notificationLang = $notificationLang;
    return $this;
  }

  public function setNotificationTimeZone($notificationTimeZone) {
    $this->notificationTimeZone = $notificationTimeZone;
    return $this;
  }

  public function setOpenID($openID) {
    $this->openID = $openID;
    return $this;
  }
}

class WeChatDirectProcessResponse extends Container {
  private $apiVersion;
  private $code;
  private $message;
  private $exchangeRate;
  private $qrCode;
  private $qrCodeUrl;
  private $webSocketUrl;
  private $transactionID;
  private $transactionInfo;
  // SDK mode fields
  private $appId;
  private $partnerId;
  private $prepayId;
  private $packageStr;
  private $nonceStr;
  private $timestamp;
  private $sign;
  private $paySign;
  private $signType;

  public function getApiVersion() {
    return $this->apiVersion;
  }

  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
    return $this;
  }

  public function getCode() {
    return $this->code;
  }

  public function setCode($code) {
    $this->code = $code;
    return $this;
  }

  public function getMessage() {
    return $this->message;
  }

  public function setMessage($message) {
    $this->message = $message;
    return $this;
  }

  public function getExchangeRate() {
    return $this->exchangeRate;
  }

  public function setExchangeRate($exchangeRate) {
    $this->exchangeRate = $exchangeRate;
    return $this;
  }

  public function getQrCode() {
    return $this->qrCode;
  }

  public function setQrCode($qrCode) {
    $this->qrCode = $qrCode;
    return $this;
  }

  public function getQrCodeUrl() {
    return $this->qrCodeUrl;
  }

  public function setQrCodeUrl($qrCodeUrl) {
    $this->qrCodeUrl = $qrCodeUrl;
    return $this;
  }

  public function getWebSocketUrl() {
    return $this->webSocketUrl;
  }

  public function setWebSocketUrl($webSocketUrl) {
    $this->webSocketUrl = $webSocketUrl;
    return $this;
  }

  public function getTransactionID() {
    return $this->transactionID;
  }

  public function setTransactionID($transactionID) {
    $this->transactionID = $transactionID;
    return $this;
  }

  public function getTransactionInfo() {
    return $this->transactionInfo;
  }

  public function setTransactionInfo($transactionInfo) {
    $this->transactionInfo = $transactionInfo;
    return $this;
  }

  public function getAppId() {
    return $this->appId;
  }

  public function setAppId($appId) {
    $this->appId = $appId;
    return $this;
  }

  public function getPartnerId() {
    return $this->partnerId;
  }

  public function setPartnerId($partnerId) {
    $this->partnerId = $partnerId;
    return $this;
  }

  public function getPrepayId() {
    return $this->prepayId;
  }

  public function setPrepayId($prepayId) {
    $this->prepayId = $prepayId;
    return $this;
  }

  public function getPackageStr() {
    return $this->packageStr;
  }

  public function setPackageStr($packageStr) {
    $this->packageStr = $packageStr;
    return $this;
  }

  public function getNonceStr() {
    return $this->nonceStr;
  }

  public function setNonceStr($nonceStr) {
    $this->nonceStr = $nonceStr;
    return $this;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }

  public function setTimestamp($timestamp) {
    $this->timestamp = $timestamp;
    return $this;
  }

  public function getPaySign() {
    return $this->paySign;
  }

  public function setPaySign($paySign) {
    $this->paySign = $paySign;
    return $this;
  }

  public function getSignType() {
      return $this->signType;
  }

  public function setSignType($signType) {
      $this->signType = $signType;
      return $this;
  }

  public static function getFromJson($dataJson) {
    $response = null;

    if ($dataJson != null && is_object($dataJson)) {
      // Root element, AccountInformation
      $response = new WeChatDirectProcessResponse();
      $reflector = new \ReflectionClass('PayXpert\Connect2Pay\WeChatDirectProcessResponse');
      self::copyScalarProperties($reflector->getProperties(), $dataJson, $response);

      // Transaction information
      if (isset($dataJson->transactionInfo)) {
        $response->transactionInfo = TransactionAttempt::getFromJson($dataJson->transactionInfo);
      }
    }

    return $response;
  }
}

class AliPayDirectProcessRequest {
  const MODE_POS = "pos";
  const MODE_APP = "app";
  const MODE_SDK = "sdk";
  const IDENTITY_CODE_TYPE_BARCODE = "barcode";
  const IDENTITY_CODE_TYPE_QRCODE = "qrcode";

  /* ~~ */
  public $apiVersion;
  public $mode;
  public $buyerIdentityCode;
  public $identityCodeType;
  public $notificationLang;
  public $notificationTimeZone;

  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
    return $this;
  }

  public function setMode($mode) {
    $this->mode = in_array($mode, array(self::MODE_POS, self::MODE_APP, self::MODE_SDK)) ? $mode : self::MODE_NATIVE;
    return $this;
  }

  public function setBuyerIdentityCode($buyerIdentityCode) {
    $this->buyerIdentityCode = $buyerIdentityCode;
    return $this;
  }

  public function setIdentityCodeType($identityCodeType) {
    $this->identityCodeType = $identityCodeType;
    return $this;
  }

  public function setNotificationLang($notificationLang) {
    $this->notificationLang = $notificationLang;
    return $this;
  }

  public function setNotificationTimeZone($notificationTimeZone) {
    $this->notificationTimeZone = $notificationTimeZone;
    return $this;
  }
}

class AliPayDirectProcessResponse extends Container {
  private $apiVersion;
  private $code;
  private $message;
  private $exchangeRate;
  private $qrCode;
  private $qrCodeUrl;
  private $webSocketUrl;
  private $transactionID;
  private $transactionInfo;
  // SDK mode field
  private $rawRequest;

  public function getApiVersion() {
    return $this->apiVersion;
  }

  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
    return $this;
  }

  public function getCode() {
    return $this->code;
  }

  public function setCode($code) {
    $this->code = $code;
    return $this;
  }

  public function getMessage() {
    return $this->message;
  }

  public function setMessage($message) {
    $this->message = $message;
    return $this;
  }

  public function getExchangeRate() {
    return $this->exchangeRate;
  }

  public function setExchangeRate($exchangeRate) {
    $this->exchangeRate = $exchangeRate;
    return $this;
  }

  public function getQrCode() {
    return $this->qrCode;
  }

  public function setQrCode($qrCode) {
    $this->qrCode = $qrCode;
    return $this;
  }

  public function getQrCodeUrl() {
    return $this->qrCodeUrl;
  }

  public function setQrCodeUrl($qrCodeUrl) {
    $this->qrCodeUrl = $qrCodeUrl;
    return $this;
  }

  public function getWebSocketUrl() {
    return $this->webSocketUrl;
  }

  public function setWebSocketUrl($webSocketUrl) {
    $this->webSocketUrl = $webSocketUrl;
    return $this;
  }

  public function getTransactionID() {
    return $this->transactionID;
  }

  public function setTransactionID($transactionID) {
    $this->transactionID = $transactionID;
    return $this;
  }

  public function getTransactionInfo() {
    return $this->transactionInfo;
  }

  public function setTransactionInfo($transactionInfo) {
    $this->transactionInfo = $transactionInfo;
    return $this;
  }

  public function getRawRequest() {
    return $this->rawRequest;
  }

  public function setRawRequest($rawRequest) {
    $this->rawRequest = $rawRequest;
    return $this;
  }

  public static function getFromJson($dataJson) {
    $response = null;

    if ($dataJson != null && is_object($dataJson)) {
      // Root element, AccountInformation
      $response = new AliPayDirectProcessResponse();
      $reflector = new \ReflectionClass('PayXpert\Connect2Pay\AliPayDirectProcessResponse');
      self::copyScalarProperties($reflector->getProperties(), $dataJson, $response);

      // Transaction information
      if (isset($dataJson->transactionInfo)) {
        $response->transactionInfo = TransactionAttempt::getFromJson($dataJson->transactionInfo);
      }
    }

    return $response;
  }
}

class ExportTransactionsRequest {

  /* ~~ */
  public $apiVersion;
  public $startTime;
  public $endTime;
  public $operation;
  public $paymentMethod;
  public $paymentNetwork;
  public $transactionResultCode;

  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
    return $this;
  }

  public function setStartTime($startTime) {
    $this->startTime = $startTime;
    return $this;
  }

  public function setEndTime($endTime) {
    $this->endTime = $endTime;
    return $this;
  }

  public function setOperation($operation) {
    $this->operation = $operation;
    return $this;
  }

  public function setPaymentMethod($paymentMethod) {
    $this->paymentMethod = $paymentMethod;
    return $this;
  }

  public function setPaymentNetwork($paymentNetwork) {
    $this->paymentNetwork = $paymentNetwork;
    return $this;
  }

  public function setTransactionResultCode($transactionResultCode) {
    $this->transactionResultCode = $transactionResultCode;
    return $this;
  }

  public function toParamsArray() {
    $array = array();

    foreach ($this as $key => $value) {
      if ($value !== NULL) {
        $array[$key] = $value;
      }
    }

    return $array;
  }

}

class ExportTransactionsResponse extends Container {

  private $transactions;

  public function getTransactions() {
    return $this->transactions;
  }

  public function setTransactions($transactions) {
    $this->transactions = $transactions;
    return $this;
  }

  public static function getFromJson($dataJson) {
    $response = null;

    if ($dataJson != null && is_object($dataJson)) {

      $response = new ExportTransactionsResponse();
      $response->transactions = array();

      if (isset($dataJson->transactions) && is_array($dataJson->transactions)) {
        foreach ($dataJson->transactions as $transactionJson) {
          $response->transactions[] = TransactionAttempt::getFromJson($transactionJson);
        }
      }
    }

    return $response;
  }

}

/**
 * Helper to manipulate amount in different currencies.
 * Permits to convert amount between different currencies
 * and get rates in real time from Yahoo Web service.
 */
class Connect2PayCurrencyHelper {
  // The base address to fetch currency rates
  private static $APIIO_SERVICE_URL = 'http://api.fixer.io/';

  // Optional proxy to use for outgoing request
  private static $proxy_host = null;
  private static $proxy_port = null;
  private static $proxy_username = null;
  private static $proxy_password = null;
  private static $currencies = array( /* */
      "AUD" => array("currency" => "Australian Dollar", "code" => "036", "symbol" => "$"),
      "CAD" => array("currency" => "Canadian Dollar", "code" => "124", "symbol" => "$"),
      "CHF" => array("currency" => "Swiss Franc", "code" => "756", "symbol" => "CHF"),
      "CNY" => array("currency" => "Chinese yuan", "code" => "156", "symbol" => "¥"),
      "DKK" => array("currency" => "Danish Krone", "code" => "208", "symbol" => "kr"),
      "EUR" => array("currency" => "Euro", "code" => "978", "symbol" => "€"),
      "GBP" => array("currency" => "Pound Sterling", "code" => "826", "symbol" => "£"),
      "HKD" => array("currency" => "Hong Kong Dollar", "code" => "344", "symbol" => "$"),
      "JPY" => array("currency" => "Yen", "code" => "392", "symbol" => "¥"),
      "MAD" => array("currency" => "Moroccan dirham", "code" => "504", "symbol" => "MAD"),
      "MXN" => array("currency" => "Mexican Peso", "code" => "484", "symbol" => "$"),
      "NOK" => array("currency" => "Norwegian Krone", "code" => "578", "symbol" => "kr"),
      "PHP" => array("currency" => "Philippine peso", "code" => "608", "symbol" => "₱"),
      "SEK" => array("currency" => "Swedish Krona", "code" => "752", "symbol" => "kr"),
      "USD" => array("currency" => "US Dollar", "code" => "840", "symbol" => "$"),
      "RUB" => array("currency" => "Russian ruble", "code" => "643", "symbol" => "RUB"),
      "SGD" => array("currency" => "Singapore dollar", "code" => "702", "symbol" => "SGD"),
      "PLN" => array("currency" => "Polish zloty", "code" => "985", "symbol" => "zł"),
      "TWD" => array("currency" => "New Taiwan dollar", "code" => "901", "symbol" => "NT$") /*
                                                                                             */
  );

  /**
   * Set the parameter in the case of the use of an outgoing proxy
   *
   * @param string $host
   *          The proxy host.
   * @param int $port
   *          The proxy port.
   * @param string $username
   *          The proxy username.
   * @param string $password
   *          The proxy password.
   */
  public static function useProxy($host, $port, $username = null, $password = null) {
    Connect2PayCurrencyHelper::$proxy_host = $host;
    Connect2PayCurrencyHelper::$proxy_port = $port;
    Connect2PayCurrencyHelper::$proxy_username = $username;
    Connect2PayCurrencyHelper::$proxy_password = $password;
  }

  /**
   * Return the supported currencies array.
   *
   * @return Array of all the currencies supported.
   */
  public static function getCurrencies() {
    return array_keys(Connect2PayCurrencyHelper::$currencies);
  }

  /**
   * Get a currency alphabetic code according to its numeric code in ISO4217
   *
   * @param string $code
   *          The numeric code to look for
   * @return string The alphabetic code (like EUR or USD) or null if not found.
   */
  public static function getISO4217CurrencyFromCode($code) {
    foreach (Connect2PayCurrencyHelper::$currencies as $currency => $data) {
      if ($data["code"] == $code) {
        return $currency;
      }
    }

    return null;
  }

  /**
   * Return the ISO4217 currency code.
   *
   * @param string $currency
   *          The currency to look for
   * @return string The ISO4217 code or null if not found
   */
  public static function getISO4217CurrencyCode($currency) {
    return (array_key_exists($currency, Connect2PayCurrencyHelper::$currencies)) ? Connect2PayCurrencyHelper::$currencies[$currency]["code"] : null;
  }

  /**
   * Return the currency symbol.
   *
   * @param string $currency
   *          The currency to look for
   * @return string The currency symbol or null if not found
   */
  public static function getCurrencySymbol($currency) {
    return (array_key_exists($currency, Connect2PayCurrencyHelper::$currencies)) ? Connect2PayCurrencyHelper::$currencies[$currency]["symbol"] : null;
  }

  /**
   * Return the currency name.
   *
   * @param string $currency
   *          The currency to look for
   * @return string The currency name or null if not found
   */
  public static function getCurrencyName($currency) {
    return (array_key_exists($currency, Connect2PayCurrencyHelper::$currencies)) ? Connect2PayCurrencyHelper::$currencies[$currency]["currency"] : null;
  }

  /**
   * Get a currency conversion rate from Yahoo webservice.
   *
   * @param string $from
   *          The source currency
   * @param string $to
   *          The destination currency
   */
  public static function getRate($from, $to) {
    // Check if currencies exists
    if (!Connect2PayCurrencyHelper::currencyIsAvailable($from) || !Connect2PayCurrencyHelper::currencyIsAvailable($to)) {
      return null;
    }

    // Build the request URL
    $url = Connect2PayCurrencyHelper::$APIIO_SERVICE_URL . date("Y-m-d") . "?base=" . $from . "&symbols=" . $to;

    // Do the request
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if (Connect2PayCurrencyHelper::$proxy_host != null && Connect2PayCurrencyHelper::$proxy_port != null) {
      curl_setopt($curl, CURLOPT_PROXY, Connect2PayCurrencyHelper::$proxy_host);
      curl_setopt($curl, CURLOPT_PROXYPORT, Connect2PayCurrencyHelper::$proxy_port);

      if (Connect2PayCurrencyHelper::$proxy_username != null && Connect2PayCurrencyHelper::$proxy_password != null) {
        curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_PROXYUSERPWD,
            Connect2PayCurrencyHelper::$proxy_username . ":" . Connect2PayCurrencyHelper::$proxy_password);
      }
    }

    $json = trim(curl_exec($curl));
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode == 200) {
      // Parse the CSV, we should only have a number, check this
      $obj = json_decode($json, true);

      if (is_array($obj) && array_key_exists("rates", $obj) && array_key_exists($to, $obj['rates']) &&
          preg_match('/^[0-9.]+$/', $obj['rates'][$to])) {
        return $obj['rates'][$to];
      }
    }

    return null;
  }

  /**
   * Convert an amount from one currency to another
   *
   * @param int $amount
   *          The amount to convert
   * @param string $from
   *          The source currency
   * @param string $to
   *          The destination currency
   * @param boolean $cent
   *          Specifies if the amount is in cent (default true)
   * @return integer The converted amount or null in case of error
   */
  public static function convert($amount, $from, $to, $cent = true) {
    // Get the conversion rate
    $rate = Connect2PayCurrencyHelper::getRate($from, $to);

    if ($rate != null) {
      $convert = $amount * $rate;

      // If the amount was in cent, truncate the digit after the comma
      // else round the result to 2 digits only
      return ($cent) ? round($convert, 0) : round($convert, 2);
    }

    return null;
  }

  private static function currencyIsAvailable($currency) {
    return array_key_exists($currency, Connect2PayCurrencyHelper::$currencies);
  }
}

/**
 * Validation class
 */
class C2PValidate {

  /**
   * Check for e-mail validity
   *
   * @param string $email
   *          e-mail address to validate
   * @return boolean Validity is ok or not
   */
  static public function isEmail($email) {
    return C2PValidate::isEmpty($email) or
        preg_match('/^[a-z0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z0-9]+[._a-z0-9-]*\.[a-z0-9]+$/ui', $email);
  }

  /**
   * Check for IP validity
   *
   * @param string $ip
   *          IP address to validate
   * @return boolean Validity is ok or not
   */
  static public function isIP($ip) {
    return C2PValidate::isEmpty($ip) or preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ip);
  }

  /**
   * Check for MD5 string validity
   *
   * @param string $md5
   *          MD5 string to validate
   * @return boolean Validity is ok or not
   */
  static public function isMd5($md5) {
    return preg_match('/^[a-f0-9A-F]{32}$/', $md5);
  }

  /**
   * Check for SHA1 string validity
   *
   * @param string $sha1
   *          SHA1 string to validate
   * @return boolean Validity is ok or not
   */
  static public function isSha1($sha1) {
    return preg_match('/^[a-fA-F0-9]{40}$/', $sha1);
  }

  /**
   * Check for a float number validity
   *
   * @param float $float
   *          Float number to validate
   * @return boolean Validity is ok or not
   */
  static public function isFloat($float) {
    return strval((float) ($float)) == strval($float);
  }

  static public function isUnsignedFloat($float) {
    return strval((float) ($float)) == strval($float) and $float >= 0;
  }

  /**
   * Check for name validity
   *
   * @param string $name
   *          Name to validate
   * @return boolean Validity is ok or not
   */
  static public function isName($name) {
    return preg_match('/^[^0-9!<>,;?=+()@#"°{}_$%:]*$/', stripslashes($name));
  }

  /**
   * Check for a country name validity
   *
   * @param string $name
   *          Country name to validate
   * @return boolean Validity is ok or not
   */
  static public function isCountryName($name) {
    return preg_match('/^[a-zA-Z -]+$/', $name);
  }

  /**
   * Check for a postal address validity
   *
   * @param string $address
   *          Address to validate
   * @return boolean Validity is ok or not
   */
  static public function isAddress($address) {
    return empty($address) or preg_match('/^[^!<>?=+@{}_$%]*$/', $address);
  }

  /**
   * Check for city name validity
   *
   * @param string $city
   *          City name to validate
   * @return boolean Validity is ok or not
   */
  static public function isCityName($city) {
    return preg_match('/^[^!<>;?=+@#"°{}_$%]*$/', $city);
  }

  /**
   * Check for date format
   *
   * @param string $date
   *          Date to validate
   * @return boolean Validity is ok or not
   */
  static public function isDateFormat($date) {
    return (bool) preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[1-9])|([0-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date);
  }

  /**
   * Check for date validity
   *
   * @param string $date
   *          Date to validate
   * @return boolean Validity is ok or not
   */
  static public function isDate($date) {
    if (!preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $matches))
      return false;
    return checkdate((int) $matches[2], (int) $matches[5], (int) $matches[0]);
  }

  /**
   * Check for boolean validity
   *
   * @param boolean $bool
   *          Boolean to validate
   * @return boolean Validity is ok or not
   */
  static public function isBool($bool) {
    return is_null($bool) or is_bool($bool) or preg_match('/^0|1$/', $bool);
  }

  /**
   * Check for phone number validity
   *
   * @param string $phoneNumber
   *          Phone number to validate
   * @return boolean Validity is ok or not
   */
  static public function isPhoneNumber($phoneNumber) {
    return preg_match('/^[+0-9. ()-;]*$/', $phoneNumber);
  }

  /**
   * Check for postal code validity
   *
   * @param string $postcode
   *          Postal code to validate
   * @return boolean Validity is ok or not
   */
  static public function isPostCode($postcode) {
    return empty($postcode) or preg_match('/^[a-zA-Z 0-9-]+$/', $postcode);
  }

  /**
   * Check for zip code format validity
   *
   * @param string $zip_code
   *          zip code format to validate
   * @return boolean Validity is ok or not
   */
  static public function isZipCodeFormat($zip_code) {
    if (!empty($zip_code))
      return preg_match('/^[NLCnlc -]+$/', $zip_code);
    return true;
  }

  /**
   * Check for an integer validity
   *
   * @param integer $id
   *          Integer to validate
   * @return boolean Validity is ok or not
   */
  static public function isInt($value) {
    return ((string) (int) $value === (string) $value or $value === false or empty($value));
  }

  /**
   * Check for an integer validity (unsigned)
   *
   * @param integer $id
   *          Integer to validate
   * @return boolean Validity is ok or not
   */
  static public function isUnsignedInt($value) {
    return (preg_match('#^[0-9]+$#', (string) $value) and $value < 4294967296 and $value >= 0);
  }

  /**
   * Check for an numeric string validity (unsigned)
   *
   * @param string $value
   *          Numeric string to validate
   * @return boolean Validity is ok or not
   */
  static public function isNumeric($value) {
    return (preg_match('#^[0-9]+$#', (string) $value) === 1);
  }

  /**
   * Check url valdity (disallowed empty string)
   *
   * @param string $url
   *          Url to validate
   * @return boolean Validity is ok or not
   */
  static public function isUrl($url) {
    return preg_match('/^[~:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
  }

  /**
   * Check object validity
   *
   * @param integer $object
   *          Object to validate
   * @return boolean Validity is ok or not
   */
  static public function isAbsoluteUrl($url) {
    if (!empty($url))
      return preg_match('/^https?:\/\/[:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
    return true;
  }

  /**
   * String validity (PHP one)
   *
   * @param string $data
   *          Data to validate
   * @return boolean Validity is ok or not
   */
  static public function isString($data) {
    return is_string($data);
  }

  /**
   * Shipping Type validity
   *
   * @param string $shipping
   *          Shipping Type to validate
   * @return boolean Validity is ok or not
   */
  static public function isShippingType($shipping) {
    return ((string) $shipping == "Physical" || (string) $shipping == "Virtual" || (string) $shipping == "Access");
  }

  /**
   * Payment Mean validity
   *
   * @param string $paymentMethod
   *          Payment Mean to validate
   * @return boolean Validity is ok or not
   */
  static public function isPaymentMethod($paymentMethod) {
    return ((string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_CREDITCARD ||
        (string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_TODITOCASH ||
        (string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_BANKTRANSFER ||
        (string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_DIRECTDEBIT ||
        (string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_WECHAT ||
        (string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_LINE ||
        (string) $paymentMethod == Connect2PayClient::PAYMENT_METHOD_ALIPAY);
  }

  /**
   * Payment network validity
   *
   * @param string $paymentNetwork
   *          Payment Network to validate
   * @return boolean Validity is ok or not
   */
  static public function isPaymentNetwork($paymentNetwork) {
    return ((string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_SOFORT ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_PRZELEWY24 ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_IDEAL ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_GIROPAY ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_EPS ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_POLI ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_DRAGONPAY ||
        (string) $paymentNetwork == Connect2PayClient::PAYMENT_NETWORK_TRUSTLY);
  }

  /**
   * Operation validity
   *
   * @param string $operation
   *          Operation to validate
   * @return boolean Validity is ok or not
   */
  static public function isOperation($operation) {
    return ((string) $operation == Connect2PayClient::OPERATION_TYPE_SALE ||
        (string) $operation == Connect2PayClient::OPERATION_TYPE_AUTHORIZE);
  }

  /**
   * Payment Type validity
   *
   * @param string $paymentMode
   *          Payment Mode to validate
   * @return boolean Validity is ok or not
   */
  static public function isPaymentMode($paymentMode) {
    return ((string) $paymentMode == Connect2PayClient::PAYMENT_MODE_SINGLE ||
        (string) $paymentMode == Connect2PayClient::PAYMENT_MODE_ONSHIPPING ||
        (string) $paymentMode == Connect2PayClient::PAYMENT_MODE_RECURRENT ||
        (string) $paymentMode == Connect2PayClient::PAYMENT_MODE_INSTALMENTS);
  }

  /**
   * Subscription Type validity
   *
   * @param string $subscriptionType
   *          Subscription Type to validate
   * @return boolean Validity is ok or not
   */
  static public function isSubscriptionType($subscriptionType) {
    return ((string) $subscriptionType == Connect2PayClient::SUBSCRIPTION_TYPE_NORMAL ||
        (string) $subscriptionType == Connect2PayClient::SUBSCRIPTION_TYPE_PARTPAYMENT ||
        (string) $subscriptionType == Connect2PayClient::SUBSCRIPTION_TYPE_INFINITE ||
        (string) $subscriptionType == Connect2PayClient::SUBSCRIPTION_TYPE_ONETIME ||
        (string) $subscriptionType == Connect2PayClient::SUBSCRIPTION_TYPE_LIFETIME);
  }

  /**
   * Test if a variable is set
   *
   * @param mixed $field
   * @return boolean field is set or not
   */
  public static function isEmpty($field) {
    return ($field === '' or $field === NULL);
  }

  /**
   * strlen overloaded function
   *
   * @param string $str
   * @return int size of the string
   */
  public static function strlen($str) {
    if (is_array($str))
      return false;

    if (function_exists('mb_strlen'))
      return mb_strlen($str, 'UTF-8');

    return strlen($str);
  }
}

class Utils {

  public static function deprecation_error($message) {
    trigger_error($message, version_compare(phpversion(), '5.3.0', '<') ? E_USER_NOTICE : E_USER_DEPRECATED);
  }
}
