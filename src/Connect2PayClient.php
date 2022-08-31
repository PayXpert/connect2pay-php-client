<?php

namespace PayXpert\Connect2Pay;

use PayXpert\Connect2Pay\containers\request\PaymentPrepareRequest;
use PayXpert\Connect2Pay\containers\response\PaymentPrepareResponse;
use PayXpert\Connect2Pay\helpers\Utils;
use PayXpert\Connect2Pay\containers\response\AccountInformation;
use PayXpert\Connect2Pay\containers\response\TransactionAttempt;
use PayXpert\Connect2Pay\containers\request\ExportTransactionsRequest;
use PayXpert\Connect2Pay\containers\response\ExportTransactionsResponse;
use PayXpert\Connect2Pay\containers\response\RefundStatus;
use PayXpert\Connect2Pay\containers\response\RebillStatus;
use PayXpert\Connect2Pay\containers\response\CancelStatus;
use PayXpert\Connect2Pay\containers\response\CaptureStatus;
use PayXpert\Connect2Pay\containers\request\WeChatDirectProcessRequest;
use PayXpert\Connect2Pay\containers\response\WeChatDirectProcessResponse;
use PayXpert\Connect2Pay\containers\request\AliPayDirectProcessRequest;
use PayXpert\Connect2Pay\containers\response\AliPayDirectProcessResponse;
use PayXpert\Connect2Pay\containers\response\PaymentStatus;
use PayXpert\Connect2Pay\containers\CartProduct;
use PayXpert\Connect2Pay\containers\Shopper;
use PayXpert\Connect2Pay\containers\Account;
use PayXpert\Connect2Pay\containers\Shipping;
use PayXpert\Connect2Pay\containers\Order;
use PayXpert\Connect2Pay\containers\constant\PaymentMethod;
use PayXpert\Connect2Pay\containers\constant\PaymentNetwork;
use PayXpert\Connect2Pay\containers\constant\OperationType;
use PayXpert\Connect2Pay\containers\constant\PaymentMode;
use PayXpert\Connect2Pay\containers\constant\SubscriptionType;
use PayXpert\Connect2Pay\containers\constant\Lang;
use PayXpert\Connect2Pay\containers\constant\SubscriptionCancelReason;
use PayXpert\Connect2Pay\containers\constant\Unavailable;

/**
 * Copyright 2013-2022 PayXpert
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
 * - Instantiate a PaymentPrepareRequest instance
 * - Set all the required parameters of the payment on the PaymentPrepareRequest
 * - Call preparePayment(PaymentPrepareRequest) to create the payment and receive the PaymentPrepareResponse
 * - Call getCustomerRedirectURL(PaymentPrepareResponse) and redirect the customer to this URL
 * - If receiving result via callback, use handleCallbackStatus to initialize
 * the status from the POST request (don't forget to authenticate the received
 * callback)
 * - If receiving result via customer redirection, use handleRedirectStatus to
 * initialize the status from the POST data
 * - Status of the payment can also be explicitely requested by using getPaymentStatus($merchantToken)
 *
 * The setters of the various parameters tries to ensure as much as possible that the passed data will not produce an error
 * when processing the payment but it is the responsability of the caller to pay attention to this.
 *
 * Every text must be encoded as UTF-8.
 *
 * PHP dependencies:
 * PHP >= 5.4.0
 * PHP CURL extension
 * PHP OpenSSL extension
 */
class Connect2PayClient
{
    /**
     * Client version
     */
    const CLIENT_VERSION = "2.71.0";

    /**
     * API version implemented by this class
     */
    const API_VERSION = "002.71";

    /**
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
   * Fields validation constraints
   */
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

    // Data returned from prepare call
    private $returnCode;
    private $returnMessage;
    private $merchantToken;
    private $customerToken;

    /**
     * Data returned from status call
     *
     * @var PaymentStatus
     */
    private $status;

    // Internal data
    private $clientErrorMessage;

    // HTTP Proxy data
    private $proxy_host = null;
    private $proxy_port = null;
    private $proxy_username = null;
    private $proxy_password = null;

    // Extra CURL options that can be set by the caller
    private $extraCurlOptions = array();

    /**
     * @var PaymentPrepareRequest
     */
    private $prepareRequest;

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
    public function __construct($url, $merchant, $password, $data = null)
    {
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
    public function useProxy($host, $port, $username = null, $password = null)
    {
        $this->proxy_host = $host;
        $this->proxy_port = $port;
        $this->proxy_username = $username;
        $this->proxy_password = $password;
    }

    /**
     * Add extra curl options
     */
    public function setExtraCurlOption($name, $value)
    {
        $this->extraCurlOptions[$name] = $value;
    }

    /**
     * Get information about the API account.
     */
    public function getAccountInformation()
    {
        $url = $this->url . self::$API_ROUTES['ACCOUNT_INFO'];
        $url .= '?apiVersion=' . $this->getApiVersion();

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
     * Prepare a new payment on the payment page application.
     * This method will validate the payment data and call
     * the payment page application to create a new payment.
     * The fields returnCode, returnMessage, merchantToken and
     * customerToken will be populated according to the call result.
     *
     * @param PaymentPrepareRequest $request The request information container used to prepare the payment
     * @return PaymentPrepareResponse if creation is successful, false otherwise
     */
    public function preparePayment($request = null)
    {
        if ($request != null && $this->prepareRequest != null) {
            Utils::error('Inconsistent state, implicit prepare request found but explicit request provided');
            return false;
        }
        if ($request == null) {
            $request = $this->prepareRequest;
        }

        if ($request != null) {
            $post_data = json_encode($request, JSON_UNESCAPED_SLASHES);
            $url = $this->url . Connect2PayClient::$API_ROUTES['TRANS_PREPARE'];

            $result = $this->doPost($url, $post_data);

            if ($result != null && is_array($result)) {
                $response = new PaymentPrepareResponse();
                $response->setCode($result['code']);
                $response->setMessage($result['message']);

                // Stay backward compatible
                $this->returnCode = $result['code'];
                $this->returnMessage = $result['message'];

                if ($response->getCode() == "200") {
                    $response->setMerchantToken($result['merchantToken']);
                    $response->setCustomerToken($result['customerToken']);

                    // Stay backward compatible
                    $this->merchantToken = $result['merchantToken'];
                    $this->customerToken = $result['customerToken'];
                } else {
                    $response->setMessage($this->clientErrorMessage);

                    // Stay backward compatible
                    $this->clientErrorMessage = $this->returnMessage;
                }

                return $response;
            }
        } else {
            Utils::error('PaymentPrepareRequest is null');
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
    public function getTransactionInfo($transactionId)
    {
        if ($transactionId != null && strlen(trim($transactionId)) > 0) {
            $url = $this->url . str_replace(":transactionID", $transactionId, Connect2PayClient::$API_ROUTES['TRANS_INFO']);
            $url .= '?apiVersion=' . $this->getApiVersion();

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
    public function exportTransactions($request)
    {
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
     * Do a transaction status request on the payment page application.
     *
     * @param string $merchantToken
     *          The merchant token related to this payment
     * @return \PayXpert\Connect2Pay\containers\PaymentStatus The PaymentStatus object of the payment or null on
     *         error
     */
    public function getPaymentStatus($merchantToken)
    {
        if ($merchantToken != null && strlen(trim($merchantToken)) > 0) {
            $url = $this->url . str_replace(":merchantToken", $merchantToken, Connect2PayClient::$API_ROUTES['PAYMENT_STATUS']);
            $url .= '?apiVersion=' . $this->getApiVersion();

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
    public function refundTransaction($transactionID, $amount)
    {
        if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
            $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_REFUND']);
            $trans = array();
            $trans['apiVersion'] = $this->getApiVersion();
            $trans['amount'] = intval($amount);

            return $this->doOperation($url, $trans, new RefundStatus());
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
     *         operation or null on failure (in that case call getClientErrorMessage())
     */
    public function rebillTransaction($transactionID, $amount)
    {
        if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
            $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_REBILL']);
            $trans = array();
            $trans['apiVersion'] = $this->getApiVersion();
            $trans['amount'] = intval($amount);

            return $this->doOperation($url, $trans, new RebillStatus());
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
     *         operation or null on failure (in that case call getClientErrorMessage())
     */
    public function cancelTransaction($transactionID, $amount)
    {
        if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
            $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_CANCEL']);
            $trans = array();
            $trans['apiVersion'] = $this->getApiVersion();
            $trans['amount'] = intval($amount);

            return $this->doOperation($url, $trans, new CancelStatus());
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
    public function captureTransaction($transactionID, $amount)
    {
        if ($transactionID !== null && $amount !== null && (is_int($amount) || ctype_digit($amount))) {
            $url = $this->url . str_replace(":transactionID", $transactionID, Connect2PayClient::$API_ROUTES['TRANS_CAPTURE']);
            $trans = array();
            $trans['apiVersion'] = $this->getApiVersion();
            $trans['amount'] = intval($amount);

            return $this->doOperation($url, $trans, new CaptureStatus());
        } else {
            $this->clientErrorMessage = '"transactionID" must not be null, "amount" must be a positive integer';
        }

        return null;
    }

    private function doOperation($url, $request, $response)
    {
        if ($url !== null && $request != null && $response !== null) {
            $result = $this->doPost($url, json_encode($request));

            if ($result != null && is_array($result)) {
                $this->status = $response;

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
                $this->clientErrorMessage = 'No result received from operation call: ' . $this->clientErrorMessage;
            }
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
     *         on failure
     */
    public function cancelSubscription($subscriptionID, $cancelReason)
    {
        if ($subscriptionID != null && is_numeric($subscriptionID) && isset($cancelReason) && is_numeric($cancelReason)) {
            $url = $this->url . str_replace(":subscriptionID", $subscriptionID, Connect2PayClient::$API_ROUTES['SUB_CANCEL']);
            $trans = array();
            $trans['apiVersion'] = $this->getApiVersion();
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
     *         with values returned from the operation or null on failure (in that case call
     *         getClientErrorMessage())
     */
    public function directWeChatProcess($customerToken, $request)
    {
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
     *         with values returned from the operation or null on failure (in that case call
     *         getClientErrorMessage())
     */
    public function directAliPayProcess($customerToken, $request)
    {
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
    public function handleCallbackStatus()
    {
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
    public function handleRedirectStatus($encryptedData, $merchantToken)
    {
        $key = Utils::urlSafeBase64Decode($merchantToken);
        $binData = Utils::urlSafeBase64Decode($encryptedData);

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
            $json = Utils::pkcs5Unpad($json);
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
     * @param PaymentPrepareResponse $result Response received from payment prepare call
     * @return string The URL to redirect the customer to.
     */
    public function getCustomerRedirectURL($result = null)
    {
        $customerToken = $this->customerToken;
        if ($result != null) {
            $customerToken = $result->getCustomerToken();
        }

        return $this->url . str_replace(":customerToken", $customerToken, Connect2PayClient::$API_ROUTES['TRANS_DOPAY']);
    }

    private function doGet($url, $params, $assoc = true)
    {
        return $this->doHTTPRequest("GET", $url, $params, $assoc);
    }

    private function doPost($url, $data, $assoc = true)
    {
        return $this->doHTTPRequest("POST", $url, $data, $assoc);
    }

    private function doHTTPRequest($type, $url, $data, $assoc = true)
    {
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
                $this->clientErrorMessage = 'Error requesting ' . $url;
            }
        }

        return null;
    }

    private function initStatus($status)
    {
        $this->status = PaymentStatus::getFromJson($status);
    }

    public function getApiVersion()
    {
        return self::API_VERSION;
    }

    public function getURL()
    {
        return $this->url;
    }

    public function setURL($url)
    {
        $this->url = $url;
        return ($this);
    }

    public function getMerchant()
    {
        return $this->merchant;
    }

    public function setMerchant($merchant)
    {
        $this->merchant = $merchant;
        return ($this);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return ($this);
    }

    public function getReturnCode()
    {
        return $this->returnCode;
    }

    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    public function getMerchantToken()
    {
        return $this->merchantToken;
    }

    public function getCustomerToken()
    {
        return $this->customerToken;
    }

    /**
     * @return PaymentStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getClientErrorMessage()
    {
        return $this->clientErrorMessage;
    }

    /**
     * For compatibility and to support deprecated methods below
     */
    private function initPrepareRequest()
    {
        if ($this->prepareRequest == null) {
            Utils::deprecation_error('Creating PaymentPrepareRequest on the fly. Consider managing it explicitely');

            $this->prepareRequest = new PaymentPrepareRequest();
            $this->prepareRequest->setShopper(new Shopper());
            $this->prepareRequest->getShopper()->setAccount(new Account());
            $this->prepareRequest->setShipping(new Shipping());
            $this->prepareRequest->setOrder(new Order());
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // ~~~~~~~~ Deprecated elements, will be removed in a future version ~~~~~~~~
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    /*
  * Payment methods constants
  */
    /**
     * @deprecated Use PaymentMethod::CREDITCARD
     */
    const PAYMENT_METHOD_CREDITCARD = PaymentMethod::CREDIT_CARD;
    /**
     * @deprecated Unsupported payment method
     */
    const PAYMENT_METHOD_TODITOCASH = PaymentMethod::TODITO_CASH;
    /**
     * @deprecated Use PaymentMethod::BANKTRANSFER
     */
    const PAYMENT_METHOD_BANKTRANSFER = PaymentMethod::BANK_TRANSFER;
    /**
     * @deprecated Use PaymentMethod::DIRECTDEBIT
     */
    const PAYMENT_METHOD_DIRECTDEBIT = PaymentMethod::DIRECT_DEBIT;
    /**
     * @deprecated Use PaymentMethod::WECHAT
     */
    const PAYMENT_METHOD_WECHAT = PaymentMethod::WECHAT;
    /**
     * @deprecated Use PaymentMethod::LINE
     */
    const PAYMENT_METHOD_LINE = PaymentMethod::LINE;
    /**
     * @deprecated Use PaymentMethod::ALIPAY
     */
    const PAYMENT_METHOD_ALIPAY = PaymentMethod::ALIPAY;

    /*
   * Legacy payment types
   */
    /**
     *
     * @deprecated Use PaymentMethod::CREDITCARD
     */
    const _PAYMENT_TYPE_CREDITCARD = self::PAYMENT_METHOD_CREDITCARD;
    /**
     *
     * @deprecated Unsupported payment method
     */
    const _PAYMENT_TYPE_TODITOCASH = self::PAYMENT_METHOD_TODITOCASH;
    /**
     *
     * @deprecated Use PaymentMethod::BANKTRANSFER
     */
    const _PAYMENT_TYPE_BANKTRANSFER = self::PAYMENT_METHOD_BANKTRANSFER;
    /**
     *
     * @deprecated Use PaymentMethod::DIRECTDEBIT
     */
    const _PAYMENT_TYPE_DIRECTDEBIT = self::PAYMENT_METHOD_DIRECTDEBIT;
    /**
     *
     * @deprecated Use PaymentMethod::WECHAT
     */
    const _PAYMENT_TYPE_WECHAT = self::PAYMENT_METHOD_WECHAT;
    /**
     *
     * @deprecated Use PaymentMethod::LINE
     */
    const _PAYMENT_TYPE_LINE = self::PAYMENT_METHOD_LINE;

    /*
   * Payment networks constants
   */
    /**
     * @deprecated Use PaymentNetwork::SOFORT
     */
    const PAYMENT_NETWORK_SOFORT = PaymentNetwork::SOFORT;
    /**
     * @deprecated Use PaymentNetwork::PRZELEWY24
     */
    const PAYMENT_NETWORK_PRZELEWY24 = PaymentNetwork::PRZELEWY24;
    /**
     * @deprecated Use PaymentNetwork::IDEAL
     */
    const PAYMENT_NETWORK_IDEAL = PaymentNetwork::IDEAL;
    /**
     * @deprecated Use PaymentNetwork::GIROPAY
     */
    const PAYMENT_NETWORK_GIROPAY = PaymentNetwork::GIROPAY;
    /**
     * @deprecated Use PaymentNetwork::EPS
     */
    const PAYMENT_NETWORK_EPS = PaymentNetwork::EPS;
    /**
     * @deprecated Use PaymentNetwork::POLI
     */
    const PAYMENT_NETWORK_POLI = PaymentNetwork::POLI;
    /**
     * @deprecated Use PaymentNetwork::DRAGONPAY
     */
    const PAYMENT_NETWORK_DRAGONPAY = PaymentNetwork::DRAGONPAY;
    /**
     * @deprecated Use PaymentNetwork::SOFTRUSTLYORT
     */
    const PAYMENT_NETWORK_TRUSTLY = PaymentNetwork::TRUSTLY;

    /*
   * Legacy payment providers constants
   */
    /**
     *
     * @deprecated Use PaymentNetwork::SOFORT
     */
    const _PAYMENT_PROVIDER_SOFORT = self::PAYMENT_NETWORK_SOFORT;
    /**
     *
     * @deprecated Use PaymentNetwork::PRZELEWY24
     */
    const _PAYMENT_PROVIDER_PRZELEWY24 = self::PAYMENT_NETWORK_PRZELEWY24;
    /**
     *
     * @deprecated Use PaymentNetwork::IDEAL
     */
    const _PAYMENT_PROVIDER_IDEAL = self::PAYMENT_NETWORK_IDEAL;
    /**
     *
     * @deprecated Use PaymentNetwork::GIROPAY
     */
    const _PAYMENT_PROVIDER_GIROPAY = self::PAYMENT_NETWORK_GIROPAY;

    /*
   * Operation types constants
   */
    /**
     * @deprecated Use OperationType::SALE
     */
    const OPERATION_TYPE_SALE = OperationType::SALE;
    /**
     * @deprecated Use OperationType::AUTHORIZE
     */
    const OPERATION_TYPE_AUTHORIZE = OperationType::AUTHORIZE;
    /**
     * @deprecated Use OperationType::SALE
     */
    const _OPERATION_TYPE_SALE = self::OPERATION_TYPE_SALE;
    /**
     * @deprecated Use OperationType::AUTHORIZE
     */
    const _OPERATION_TYPE_AUTHORIZE = self::OPERATION_TYPE_AUTHORIZE;

    /*
   * Payment modes constants
   */
    /**
     * @deprecated Use PaymentMode::SINGLE
     */
    const PAYMENT_MODE_SINGLE = PaymentMode::SINGLE;
    /**
     * @deprecated Use PaymentMode::ONSHIPPING
     */
    const PAYMENT_MODE_ONSHIPPING = PaymentMode::ONSHIPPING;
    /**
     * @deprecated Use PaymentMode::RECURRENT
     */
    const PAYMENT_MODE_RECURRENT = PaymentMode::RECURRENT;
    /**
     * @deprecated Use PaymentMode::INSTALMENTS
     */
    const PAYMENT_MODE_INSTALMENTS = PaymentMode::INSTALMENTS;
    /**
     *
     * @deprecated Use PaymentMode::SINGLE
     */
    const _PAYMENT_MODE_SINGLE = self::PAYMENT_MODE_SINGLE;
    /**
     *
     * @deprecated Use PaymentMode::ONSHIPPING
     */
    const _PAYMENT_MODE_ONSHIPPING = self::PAYMENT_MODE_ONSHIPPING;
    /**
     *
     * @deprecated Use PaymentMode::RECURRENT
     */
    const _PAYMENT_MODE_RECURRENT = self::PAYMENT_MODE_RECURRENT;
    /**
     *
     * @deprecated Use PaymentMode::INSTALMENTS
     */
    const _PAYMENT_MODE_INSTALMENTS = self::PAYMENT_MODE_INSTALMENTS;

    /*
   * Subscription types constants
   */
    /**
     * @deprecated Use SubscriptionType::NORMAL
     */
    const SUBSCRIPTION_TYPE_NORMAL = SubscriptionType::NORMAL;
    /**
     * @deprecated Use SubscriptionType::PARTPAYMENT
     */
    const SUBSCRIPTION_TYPE_PARTPAYMENT = SubscriptionType::PARTPAYMENT;
    /**
     * @deprecated Use SubscriptionType::LIFETIME
     */
    const SUBSCRIPTION_TYPE_LIFETIME = SubscriptionType::LIFETIME;
    /**
     * @deprecated Use SubscriptionType::ONETIME
     */
    const SUBSCRIPTION_TYPE_ONETIME = SubscriptionType::ONETIME;
    /**
     * @deprecated Use SubscriptionType::INFINITE
     */
    const SUBSCRIPTION_TYPE_INFINITE = SubscriptionType::INFINITE;
    /**
     *
     * @deprecated Use SubscriptionType::NORMAL
     */
    const _SUBSCRIPTION_TYPE_NORMAL = self::SUBSCRIPTION_TYPE_NORMAL;
    /**
     *
     * @deprecated Use SubscriptionType::LIFETIME
     */
    const _SUBSCRIPTION_TYPE_LIFETIME = self::SUBSCRIPTION_TYPE_LIFETIME;
    /**
     *
     * @deprecated Use SubscriptionType::ONETIME
     */
    const _SUBSCRIPTION_TYPE_ONETIME = self::SUBSCRIPTION_TYPE_ONETIME;
    /**
     *
     * @deprecated Use SubscriptionType::INFINITE
     */
    const _SUBSCRIPTION_TYPE_INFINITE = self::SUBSCRIPTION_TYPE_INFINITE;

    /*
   * Lang constants
   */
    /**
     * @deprecated Use Lang::EN
     */
    const LANG_EN = Lang::EN;
    /**
     * @deprecated Use Lang::FR
     */
    const LANG_FR = Lang::FR;
    /**
     * @deprecated Use Lang::ES
     */
    const LANG_ES = Lang::ES;
    /**
     * @deprecated Use Lang::IT
     */
    const LANG_IT = Lang::IT;
    /**
     * @deprecated Use Lang::DE
     */
    const LANG_DE = Lang::DE;
    /**
     * @deprecated Use Lang::ZH_HANT
     */
    const LANG_ZH_HANT = Lang::ZH_HANT;

    /**
     *
     * @deprecated Use Lang::EN
     */
    const _LANG_EN = self::LANG_EN;
    /**
     *
     * @deprecated Use Lang::FR
     */
    const _LANG_FR = self::LANG_FR;
    /**
     *
     * @deprecated Use Lang::ES
     */
    const _LANG_ES = self::LANG_ES;
    /**
     *
     * @deprecated Use Lang::IT
     */
    const _LANG_IT = self::LANG_IT;

    /*
   * ~~~~
   * Subscription cancel reasons
   * ~~~~
   */
    /**
     * @deprecated Use SubscriptionCancelReason::BANK_DENIAL
     */
    const SUBSCRIPTION_CANCEL_BANK_DENIAL = SubscriptionCancelReason::BANK_DENIAL;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::BANK_DENIAL
     */
    const _SUBSCRIPTION_CANCEL_BANK_DENIAL = self::SUBSCRIPTION_CANCEL_BANK_DENIAL;

    /**
     * @deprecated Use SubscriptionCancelReason::REFUNDED
     */
    const SUBSCRIPTION_CANCEL_REFUNDED = SubscriptionCancelReason::REFUNDED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::REFUNDED
     */
    const _SUBSCRIPTION_CANCEL_REFUNDED = self::SUBSCRIPTION_CANCEL_REFUNDED;

    /**
     * @deprecated Use SubscriptionCancelReason::RETRIEVAL
     */
    const SUBSCRIPTION_CANCEL_RETRIEVAL = SubscriptionCancelReason::RETRIEVAL;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::RETRIEVAL
     */
    const _SUBSCRIPTION_CANCEL_RETRIEVAL = self::SUBSCRIPTION_CANCEL_RETRIEVAL;

    /**
     * @deprecated Use SubscriptionCancelReason::BANK_LETTER
     */
    const SUBSCRIPTION_CANCEL_BANK_LETTER = SubscriptionCancelReason::BANK_LETTER;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::BANK_LETTER
     */
    const _SUBSCRIPTION_CANCEL_BANK_LETTER = self::SUBSCRIPTION_CANCEL_BANK_LETTER;

    /**
     * @deprecated Use SubscriptionCancelReason::CHARGEBACK
     */
    const SUBSCRIPTION_CANCEL_CHARGEBACK = SubscriptionCancelReason::CHARGEBACK;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::CHARGEBACK
     */
    const _SUBSCRIPTION_CANCEL_CHARGEBACK = self::SUBSCRIPTION_CANCEL_CHARGEBACK;

    /**
     * @deprecated Use SubscriptionCancelReason::COMPANY_ACCOUNT_CLOSED
     */
    const SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED = SubscriptionCancelReason::COMPANY_ACCOUNT_CLOSED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::COMPANY_ACCOUNT_CLOSED
     */
    const _SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED = self::SUBSCRIPTION_CANCEL_COMPANY_ACCOUNT_CLOSED;

    /**
     * @deprecated Use SubscriptionCancelReason::WEBSITE_ACCOUNT_CLOSED
     */
    const SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED = SubscriptionCancelReason::WEBSITE_ACCOUNT_CLOSED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::WEBSITE_ACCOUNT_CLOSED
     */
    const _SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED = self::SUBSCRIPTION_CANCEL_WEBSITE_ACCOUNT_CLOSED;

    /**
     * @deprecated Use SubscriptionCancelReason::DID_NOT_LIKE
     */
    const SUBSCRIPTION_CANCEL_DID_NOT_LIKE = SubscriptionCancelReason::DID_NOT_LIKE;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::DID_NOT_LIKE
     */
    const _SUBSCRIPTION_CANCEL_DID_NOT_LIKE = self::SUBSCRIPTION_CANCEL_DID_NOT_LIKE;

    /**
     * @deprecated Use SubscriptionCancelReason::DISAGREE
     */
    const SUBSCRIPTION_CANCEL_DISAGREE = SubscriptionCancelReason::DISAGREE;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::DISAGREE
     */
    const _SUBSCRIPTION_CANCEL_DISAGREE = self::SUBSCRIPTION_CANCEL_DISAGREE;

    /**
     * @deprecated Use SubscriptionCancelReason::WEBMASTER_FRAUD
     */
    const SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD = SubscriptionCancelReason::WEBMASTER_FRAUD;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::WEBMASTER_FRAUD
     */
    const _SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD = self::SUBSCRIPTION_CANCEL_WEBMASTER_FRAUD;

    /**
     * @deprecated Use SubscriptionCancelReason::COULD_NOT_GET_INTO
     */
    const SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO = SubscriptionCancelReason::COULD_NOT_GET_INTO;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::COULD_NOT_GET_INTO
     */
    const _SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO = self::SUBSCRIPTION_CANCEL_COULD_NOT_GET_INTO;

    /**
     * @deprecated Use SubscriptionCancelReason::NO_PROBLEM
     */
    const SUBSCRIPTION_CANCEL_NO_PROBLEM = SubscriptionCancelReason::NO_PROBLEM;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::NO_PROBLEM
     */
    const _SUBSCRIPTION_CANCEL_NO_PROBLEM = self::SUBSCRIPTION_CANCEL_NO_PROBLEM;

    /**
     * @deprecated Use SubscriptionCancelReason::NOT_UPDATED
     */
    const SUBSCRIPTION_CANCEL_NOT_UPDATED = SubscriptionCancelReason::NOT_UPDATED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::NOT_UPDATED
     */
    const _SUBSCRIPTION_CANCEL_NOT_UPDATED = self::SUBSCRIPTION_CANCEL_NOT_UPDATED;

    /**
     * @deprecated Use SubscriptionCancelReason::TECH_PROBLEM
     */
    const SUBSCRIPTION_CANCEL_TECH_PROBLEM = SubscriptionCancelReason::TECH_PROBLEM;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::TECH_PROBLEM
     */
    const _SUBSCRIPTION_CANCEL_TECH_PROBLEM = self::SUBSCRIPTION_CANCEL_TECH_PROBLEM;

    /**
     * @deprecated Use SubscriptionCancelReason::TOO_SLOW
     */
    const SUBSCRIPTION_CANCEL_TOO_SLOW = SubscriptionCancelReason::TOO_SLOW;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::TOO_SLOW
     */
    const _SUBSCRIPTION_CANCEL_TOO_SLOW = self::SUBSCRIPTION_CANCEL_TOO_SLOW;

    /**
     * @deprecated Use SubscriptionCancelReason::DID_NOT_WORK
     */
    const SUBSCRIPTION_CANCEL_DID_NOT_WORK = SubscriptionCancelReason::DID_NOT_WORK;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::DID_NOT_WORK
     */
    const _SUBSCRIPTION_CANCEL_DID_NOT_WORK = self::SUBSCRIPTION_CANCEL_DID_NOT_WORK;

    /**
     * @deprecated Use SubscriptionCancelReason::TOO_EXPENSIVE
     */
    const SUBSCRIPTION_CANCEL_TOO_EXPENSIVE = SubscriptionCancelReason::TOO_EXPENSIVE;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::TOO_EXPENSIVE
     */
    const _SUBSCRIPTION_CANCEL_TOO_EXPENSIVE = self::SUBSCRIPTION_CANCEL_TOO_EXPENSIVE;

    /**
     * @deprecated Use SubscriptionCancelReason::UNAUTH_FAMILLY
     */
    const SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY = SubscriptionCancelReason::UNAUTH_FAMILLY;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::UNAUTH_FAMILLY
     */
    const _SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY = self::SUBSCRIPTION_CANCEL_UNAUTH_FAMILLY;

    /**
     * @deprecated Use SubscriptionCancelReason::UNDETERMINED
     */
    const SUBSCRIPTION_CANCEL_UNDETERMINED = SubscriptionCancelReason::UNDETERMINED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::UNDETERMINED
     */
    const _SUBSCRIPTION_CANCEL_UNDETERMINED = self::SUBSCRIPTION_CANCEL_UNDETERMINED;

    /**
     * @deprecated Use SubscriptionCancelReason::WEBMASTER_REQUESTED
     */
    const SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED = SubscriptionCancelReason::WEBMASTER_REQUESTED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::WEBMASTER_REQUESTED
     */
    const _SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED = self::SUBSCRIPTION_CANCEL_WEBMASTER_REQUESTED;

    /**
     * @deprecated Use SubscriptionCancelReason::NOTHING_RECEIVED
     */
    const SUBSCRIPTION_CANCEL_NOTHING_RECEIVED = SubscriptionCancelReason::NOTHING_RECEIVED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::NOTHING_RECEIVED
     */
    const _SUBSCRIPTION_CANCEL_NOTHING_RECEIVED = self::SUBSCRIPTION_CANCEL_NOTHING_RECEIVED;

    /**
     * @deprecated Use SubscriptionCancelReason::DAMAGED
     */
    const SUBSCRIPTION_CANCEL_DAMAGED = SubscriptionCancelReason::DAMAGED;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::DAMAGED
     */
    const _SUBSCRIPTION_CANCEL_DAMAGED = self::SUBSCRIPTION_CANCEL_DAMAGED;

    /**
     * @deprecated Use SubscriptionCancelReason::EMPTY_BOX
     */
    const SUBSCRIPTION_CANCEL_EMPTY_BOX = SubscriptionCancelReason::EMPTY_BOX;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::EMPTY_BOX
     */
    const _SUBSCRIPTION_CANCEL_EMPTY_BOX = self::SUBSCRIPTION_CANCEL_EMPTY_BOX;

    /**
     * @deprecated Use SubscriptionCancelReason::INCOMPLETE_ORDER
     */
    const SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER = SubscriptionCancelReason::INCOMPLETE_ORDER;
    /**
     *
     * @deprecated Use SubscriptionCancelReason::INCOMPLETE_ORDER
     */
    const _SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER = self::SUBSCRIPTION_CANCEL_INCOMPLETE_ORDER;

    /*
   * Field content constants
   */
    /**
     * @deprecated Use Unavailable::PARAM
     */
    const UNAVAILABLE_PARAM = Unavailable::PARAM;
    /**
     * @deprecated Use Unavailable::COUNTRY
     */
    const UNAVAILABLE_COUNTRY = Unavailable::COUNTRY;
    /**
     * @deprecated Use Unavailable::PARAM
     */
    const _UNAVAILABLE = self::UNAVAILABLE_PARAM;
    /**
     * @deprecated Use Unavailable::COUNTRY
     */
    const _UNAVAILABLE_COUNTRY = self::UNAVAILABLE_COUNTRY;

    /**
     * Force the validation of the Connect2Pay SSL certificate.
     *
     * @param string $certFilePath
     *          The path to the PEM file containing the certification chain.
     *          If not set, defaults to
     *          "_current-dir_/ssl/connect2pay-signing-ca-cert.pem"
     * @deprecated Has no effect anymore
     */
    public function forceSSLValidation($certFilePath = null)
    {
        Utils::deprecation_error('Custom certificate file path is deprecated. Will use the system CA.');
    }

    /**
     *
     * @deprecated Use preparePayment() instead.
     */
    public function prepareTransaction()
    {
        Utils::deprecation_error('Method prepareTransaction() is deprecated, use preparePayment() instead');

        return $this->preparePayment();
    }

    /**
     *
     * @param string $merchantToken
     * @deprecated Use getPaymentStatus($merchantToken) instead.
     *
     */
    public function getTransactionStatus($merchantToken)
    {
        Utils::deprecation_error('getTransactionStatus is deprecated, use getPaymentStatus instead');

        return $this->getPaymentStatus($merchantToken);
    }

    /**
     * @return null
     * @deprecated
     */
    public function getAfClientId()
    {
        Utils::deprecation_error('The field afClientId does not exist any more');
        return null;
    }

    /**
     * @return Connect2PayClient
     * @deprecated
     */
    public function setAfClientId($afClientId)
    {
        Utils::deprecation_error('The field afClientId does not exist any more');
        return ($this);
    }

    /**
     * @return null
     * @deprecated
     */
    public function getAfPassword()
    {
        Utils::deprecation_error('The field afPassword does not exist any more');
        return null;
    }

    /**
     * @return Connect2PayClient
     * @deprecated
     */
    public function setAfPassword($afPassword)
    {
        Utils::deprecation_error('The field afPassword does not exist any more');
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getSecure3d()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getSecure3d() : null;
    }

    /**
     * @deprecated
     */
    public function setSecure3d($secure3d)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setSecure3d($secure3d);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperID()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getId() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperID($shopperID)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setId($shopperID);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperEmail()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getEmail() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperEmail($shopperEmail)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setEmail($shopperEmail);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToFirstName()
    {
        Utils::deprecation_error('The field shipToFirstName does not exist any more');
        return null;
    }

    /**
     * @deprecated
     */
    public function setShipToFirstName($shipToFirstName)
    {
        Utils::deprecation_error('The field shipToFirstName does not exist any more');
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToLastName()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getName() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToLastName($shipToLastName)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setName($shipToLastName);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToCompany()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getCompany() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToCompany($shipToCompany)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setCompany($shipToCompany);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToPhone()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getPhone() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToPhone($shipToPhone)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setPhone($shipToPhone);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToAddress()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getAddress1() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToAddress($shipToAddress)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setAddress1($shipToAddress);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToState()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getState() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToState($shipToState)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setState($shipToState);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToZipcode()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getZipcode() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToZipcode($shipToZipcode)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setZipcode($shipToZipcode);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToCity()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getCity() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToCity($shipToCity)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setCity($shipToCity);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShipToCountryCode()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getCountryCode() : null;
    }

    /**
     * @deprecated
     */
    public function setShipToCountryCode($shipToCountryCode)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setCountryCode($shipToCountryCode);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperFirstName()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getFirstName() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperFirstName($shopperFirstName)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setFirstName($shopperFirstName);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperLastName()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getLastName() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperLastName($shopperLastName)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setLastName($shopperLastName);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperPhone()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getHomePhone() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperPhone($shopperPhone)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setHomePhone($shopperPhone);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperAddress()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getAddress1() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperAddress($shopperAddress)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setAddress1($shopperAddress);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperState()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getState() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperState($shopperState)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setState($shopperState);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperZipcode()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getZipcode() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperZipcode($shopperZipcode)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setZipcode($shopperZipcode);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperCity()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getCity() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperCity($shopperCity)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setCity($shopperCity);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperCountryCode()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getCountryCode() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperCountryCode($shopperCountryCode)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setCountryCode($shopperCountryCode);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperBirthDate()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getBirthDate() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperBirthDate($shopperBirthDate)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setBirthDate($shopperBirthDate);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperIDNumber()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getIdNumber() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperIDNumber($shopperIDNumber)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setIdNumber($shopperIDNumber);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperCompany()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShopper()->getCompany() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperCompany($shopperCompany)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShopper()->setCompany($shopperCompany);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShopperLoyaltyProgram()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getShopperLoyaltyProgram() : null;
    }

    /**
     * @deprecated
     */
    public function setShopperLoyaltyProgram($shopperLoyaltyProgram)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setShopperLoyaltyProgram($shopperLoyaltyProgram);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getAffiliateID()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getAffiliateID() : null;
    }

    /**
     * @deprecated
     */
    public function setAffiliateID($affiliateID)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setAffiliateID($affiliateID);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getCampaignName()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getCampaignName() : null;
    }

    /**
     * @deprecated
     */
    public function setCampaignName($campaignName)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setCampaignName($campaignName);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOrderID()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getId() : null;
    }

    /**
     * @deprecated
     */
    public function setOrderID($orderID)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setId($orderID);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOrderDescription()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getDescription() : null;
    }

    /**
     * @deprecated
     */
    public function setOrderDescription($orderDescription)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setDescription($orderDescription);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getCurrency()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getCurrency() : null;
    }

    /**
     * @deprecated
     */
    public function setCurrency($currency)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setCurrency($currency);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getAmount()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getAmount() : null;
    }

    /**
     * @deprecated
     */
    public function setAmount($amount)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setAmount((int)$amount);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOrderTotalWithoutShipping()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getTotalWithoutShipping() : null;
    }

    /**
     * @deprecated
     */
    public function setOrderTotalWithoutShipping($orderTotalWithoutShipping)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setTotalWithoutShipping((int)$orderTotalWithoutShipping);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOrderShippingPrice()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getShippingPrice() : null;
    }

    /**
     * @deprecated
     */
    public function setOrderShippingPrice($orderShippingPrice)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setShippingPrice((int)$orderShippingPrice);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOrderDiscount()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getDiscount() : null;
    }

    /**
     * @deprecated
     */
    public function setOrderDiscount($orderDiscount)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setDiscount((int)$orderDiscount);
        return ($this);
    }

    /**
     * @deprecated This field is not present anymore in the API, the value is
     *             obtained from the connected user
     */
    public function getCustomerIP()
    {
        Utils::deprecation_error('The field customerIP does not exist any more');
        return null;
    }

    /**
     *
     * @deprecated This field is not present anymore in the API, the value is
     *             obtained from the connected user
     */
    public function setCustomerIP($customerIP)
    {
        Utils::deprecation_error('The field customerIP does not exist any more');
        return ($this);
    }

    /**
     * @deprecated This field is not present anymore in the API
     */
    public function getOrderFOLanguage()
    {
        Utils::deprecation_error('The field orderFOLanguage does not exist any more');
        return null;
    }

    /**
     * @deprecated This field is not present anymore in the API
     */
    public function setOrderFOLanguage($orderFOLanguage)
    {
        Utils::deprecation_error('The field orderFOLanguage does not exist any more');
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOrderCartContent()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getCartContent() : null;
    }

    /**
     * @deprecated
     */
    public function setOrderCartContent($orderCartContent)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setCartContent($orderCartContent);
        return ($this);
    }

    /**
     * @param CartProduct $cartProduct
     *          The product to add to the cart
     * @return Connect2PayClient
     * @deprecated
     * Add a CartProduct in the orderCartContent.
     *
     */
    public function addCartProduct($cartProduct)
    {
        $this->initPrepareRequest();

        if ($this->prepareRequest->getOrder()->getCartContent() == null) {
            $this->prepareRequest->getOrder()->setCartContent(array());
        }

        if ($cartProduct instanceof CartProduct) {
            $this->prepareRequest->getOrder()->setCartContent(array_merge($this->prepareRequest->getOrder()->getCartContent(), [$cartProduct]));
        }

        return $this;
    }

    /**
     * @deprecated
     */
    public function getShippingType()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOrder()->getType() : null;
    }

    /**
     * @deprecated
     */
    public function setShippingType($shippingType)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setShippingType($shippingType);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getShippingName()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getShipping()->getName() : null;
    }

    /**
     * @deprecated
     */
    public function setShippingName($shippingName)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->getShipping()->setName($shippingName);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getPaymentType()
    {
        helpers\Utils::deprecation_error('Use getPaymentMethod().');
        return $this->getPaymentMethod();
    }

    /**
     * @deprecated
     */
    public function setPaymentType($paymentType)
    {
        helpers\Utils::deprecation_error('Use setPaymentMethod().');
        return ($this->setPaymentMethod($paymentType));
    }

    /**
     * @deprecated
     */
    public function getPaymentMethod()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getPaymentMethod() : null;
    }

    /**
     * @deprecated
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setPaymentMethod($paymentMethod);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getProvider()
    {
        Utils::deprecation_error('Use getPaymentNetwork().');
        return $this->getPaymentNetwork();
    }

    /**
     * @deprecated
     */
    public function setProvider($provider)
    {
        Utils::deprecation_error('Use setPaymentNetwork().');
        return $this->setPaymentNetwork($provider);
    }

    /**
     * @deprecated
     */
    public function getPaymentNetwork()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getPaymentNetwork() : null;
    }

    /**
     * @deprecated
     */
    public function setPaymentNetwork($paymentNetwork)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setPaymentNetwork($paymentNetwork);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOperation()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOperation() : null;
    }

    /**
     * @deprecated
     */
    public function setOperation($operation)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setOperation($operation);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getPaymentMode()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getPaymentMode() : null;
    }

    /**
     * @deprecated
     */
    public function setPaymentMode($paymentMode)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setPaymentMode($paymentMode);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getOfferID()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getOfferID() : null;
    }

    /**
     * @deprecated
     */
    public function setOfferID($offerID)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setOfferID((int)$offerID);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getSubscriptionType()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getSubscriptionType() : null;
    }

    /**
     * @deprecated
     */
    public function setSubscriptionType($subscriptionType)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setSubscriptionType($subscriptionType);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getTrialPeriod()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getTrialPeriod() : null;
    }

    /**
     * @deprecated
     */
    public function setTrialPeriod($trialPeriod)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setTrialPeriod($trialPeriod);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getRebillAmount()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getRebillAmount() : null;
    }

    /**
     * @deprecated
     */
    public function setRebillAmount($rebillAmount)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setRebillAmount((int)$rebillAmount);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getRebillPeriod()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getRebillPeriod() : null;
    }

    /**
     * @deprecated
     */
    public function setRebillPeriod($rebillPeriod)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setRebillPeriod($rebillPeriod);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getRebillMaxIteration()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getRebillMaxIteration() : null;
    }

    /**
     * @deprecated
     */
    public function setRebillMaxIteration($rebillMaxIteration)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setRebillMaxIteration((int)$rebillMaxIteration);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getCtrlRedirectURL()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getCtrlRedirectURL() : null;
    }

    /**
     * @deprecated
     */
    public function setCtrlRedirectURL($ctrlRedirectURL)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setCtrlRedirectURL($ctrlRedirectURL);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getCtrlCallbackURL()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getCtrlCallbackURL() : null;
    }

    /**
     * @deprecated
     */
    public function setCtrlCallbackURL($ctrlCallbackURL)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setCtrlCallbackURL($ctrlCallbackURL);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getCtrlCustomData()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getCtrlCustomData() : null;
    }

    /**
     * @deprecated
     */
    public function setCtrlCustomData($ctrlCustomData)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setCtrlCustomData($ctrlCustomData);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getTimeOut()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getTimeOut() : null;
    }

    /**
     * @deprecated
     */
    public function setTimeOut($timeOut)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setTimeOut($timeOut);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getMerchantNotification()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getMerchantNotification() : null;
    }

    /**
     * @deprecated
     */
    public function setMerchantNotification($merchantNotification)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setMerchantNotification($merchantNotification);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getMerchantNotificationTo()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getMerchantNotificationTo() : null;
    }

    /**
     * @deprecated
     */
    public function setMerchantNotificationTo($merchantNotificationTo)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setMerchantNotificationTo($merchantNotificationTo);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getMerchantNotificationLang()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getMerchantNotificationLang() : null;
    }

    /**
     * @deprecated
     */
    public function setMerchantNotificationLang($merchantNotificationLang)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setMerchantNotificationLang($merchantNotificationLang);
        return ($this);
    }

    /**
     * @deprecated
     */
    public function getThemeID()
    {
        return ($this->prepareRequest != null) ? $this->prepareRequest->getThemeID() : null;
    }

    /**
     * @deprecated
     */
    public function setThemeID($themeID)
    {
        $this->initPrepareRequest();
        $this->prepareRequest->setThemeID((int)$themeID);
        return ($this);
    }

    /**
     * Set a default cart content, to be used when anti fraud system is enabled
     * and no real cart is known
     *
     * @deprecated
     */
    public function setDefaultOrderCartContent()
    {
        $orderCartContent = array();
        $product = new CartProduct();
        $product->setCartProductId(0)->setCartProductName("NA");
        $product->setCartProductUnitPrice(0)->setCartProductQuantity(1);
        $product->setCartProductBrand("NA")->setCartProductMPN("NA");
        $product->setCartProductCategoryName("NA")->setCartProductCategoryID(0);

        $orderCartContent[] = $product;

        $this->initPrepareRequest();
        $this->prepareRequest->getOrder()->setCartContent($orderCartContent);
        return ($this);
    }

    /**
     * Validate the current transaction data.
     *
     * @return boolean True if transaction data are valid, false otherwise
     * @deprecated
     */
    public function validate($request = null)
    {
        return true;
    }
}
