<?php

/**
 * Description: This class deals with both mode of 2Checkout payment types i.e. Hosted Checkout and API Checkout.
 * Hosted Checkout:
 *           1. Customer is redirected to 2checkout server for the payment.
 *           2. All the details related to shipping and billing is passed to 2checkout server.
 *           3. Customer enter the credit card information to make the payments and on success, redirected to thankyou page or payment failure page in other case.
 * API Checkout: -- DISABLED
 *           1. Customer is taken to payments page on our server.
 *           2. Customer enter the credit card information to make the payments and on success, redirected to thankyou page or payment failure page in other case.
 */
class TwocheckoutPayController extends PaymentController
{
    public const KEY_NAME = "Twocheckout";
    private $paymentType = ""; //holds two values HOSTED or API

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [
            'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'RON', 'CZK', 'HUF', 'TRY', 'ZAR', 'EGP', 'MXN', 'PEN'
        ];
    }

    private function init(): void
    {
        if (false === $this->plugin->validateSettings($this->siteLangId)) {
            $this->setErrorAndRedirect($this->plugin->getError());
        }

        $this->settings = $this->plugin->getSettings();
    }

    public function charge($orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $frm = $this->getPaymentForm($orderId);
        $postOrderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
        $processRequest = false;
        if (!empty($postOrderId) && $orderId = $postOrderId) {
            $this->paymentInitiated($orderId);
            $frm = $this->getPaymentForm($orderId, true);
            $processRequest = true;
        }

        $frm->fill(['orderId' => $orderId]);
        $this->set('frm', $frm);
        $this->set('processRequest', $processRequest);

        if ($this->paymentType != 'HOSTED') {
            /***
             * Adding here because we want these values in the js script
             **/
            $this->set('sellerId', $this->settings['sellerId']);
            $this->set('publishableKey', $this->settings['publishableKey']);
            $this->set('privateKey', $this->settings['privateKey']);
            if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
                $this->set('transaction_mode', 'production');
            } else {
                $this->set('transaction_mode', 'sandbox');
            }
        }

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);

        $this->set('paymentAmount', $paymentAmount);
        $this->set('paymentType', $this->paymentType);
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('envoirment', $this->settings['env']);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'twocheckout-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    /**
     * Description: This method will be called when the payment type is HOSTED CHECKOUT i.e. $paymentType has HOSTED value.
     */
    public function callback()
    {
        $request = $_REQUEST;
        $orderId = $request['li_0_product_id']; //in our case it is order id (hosted checkout case)
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderPaymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $hashSecretWord = $this->settings['hashSecretWord']; //2Checkout Secret Word
        $hashSid = $this->settings['sellerId']; //2Checkout account number
        $hashOrder = $request['order_number']; //2Checkout Order Number
        if ($request['demo'] == 'Y') {
            $hashOrder = '1';
        }
        $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $request['total']));

        if ($StringToHash == $request['key'] && (float)$request['total'] == (float)$orderPaymentAmount) {
            if ($request['credit_card_processed'] == 'Y') {
                /* Recording Payment in DB */
                $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $request['invoice_id'], $orderPaymentAmount, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode($request));
                /* End Recording Payment in DB */
                FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
            }
        }
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($request));
        Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
        FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
    }

    /**
     * Description: This function will be called in case of Payment type is API CHECKOUT i.e. $paymentType = API.
     */
    public function send($orderId)
    {
        $post = FatApp::getPostedData();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        /* Retrieve Payment to charge corresponding to your order */
        $orderPaymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if ($orderPaymentAmount > 0) {
            /* Retrieve Primary Info corresponding to your order */
            $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
            $order_actual_paid = number_format(round($orderPaymentAmount, 2), 2, ".", "");
            $params = array(
                "merchantOrderId" => $orderId,
                "token" => $post['token'],
                "currency" => $orderInfo["order_currency_code"],
                "total" => $order_actual_paid,
                "billingAddr" => array(
                    "name" => FatUtility::decodeHtmlEntities($orderInfo['customer_name'], ENT_QUOTES, 'UTF-8'),
                    "addrLine1" => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . FatUtility::decodeHtmlEntities($orderInfo['customer_billing_address_2'], ENT_QUOTES, 'UTF-8'),
                    "city" => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_city'], ENT_QUOTES, 'UTF-8'),
                    "state" => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_state'], ENT_QUOTES, 'UTF-8'),
                    "zipCode" => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_postcode'], ENT_QUOTES, 'UTF-8'),
                    "country" => FatUtility::decodeHtmlEntities($orderInfo['customer_billing_country'], ENT_QUOTES, 'UTF-8'),
                    "email" => $orderInfo['customer_email'],
                    "phoneNumber" => ValidateElement::formatDialCode($orderInfo['customer_phone_dcode']) . $orderInfo['customer_phone']
                ),
                "shippingAddr" => array(
                    "name" => FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_name'], ENT_QUOTES, 'UTF-8'),
                    "addrLine1" => FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_address_2'], ENT_QUOTES, 'UTF-8'),
                    "city" => FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_city'], ENT_QUOTES, 'UTF-8'),
                    "state" => FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_state'], ENT_QUOTES, 'UTF-8'),
                    "zipCode" => FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_postcode'], ENT_QUOTES, 'UTF-8'),
                    "country" => FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_country'], ENT_QUOTES, 'UTF-8'),
                    "email" => $orderInfo['customer_email'],
                    "phoneNumber" => ValidateElement::formatDialCode($orderInfo['customer_phone_dcode']) . $orderInfo['customer_phone']
                )
            );

            $url = $this->plugin::PRODUCTION_URL . '/checkout/api/1/' . $this->settings['sellerId'] . '/rs/authService';

            $params['sellerId'] = $this->settings['sellerId'];
            $params['privateKey'] = $this->settings['privateKey'];

            $curl = curl_init($url);
            $params = json_encode($params);
            $header = array("content-type:application/json", "content-length:" . strlen($params));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_USERAGENT, "2Checkout PHP/0.1.0%s");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            $result = curl_exec($curl);
            $json = array();
            $json['redirect'] = UrlHelper::generateUrl('custom', 'paymentFailed');
            if (curl_error($curl)) {
                $json['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
            } elseif ($result) {
                $object = json_decode($result, true);
                $result_array = array();
                foreach ($object as $member => $data) {
                    $result_array[$member] = $data;
                }
                /**
                 * "validationErrors": null,
                 * "exception": {
                 * "errorMsg": "Payment Authorization Failed:  Please verify your Credit Card details are entered correctly and try again, or try another payment method.",
                 * "httpStatus": "400",
                 * "exception": false,
                 * "errorCode": "602"
                 * },
                 * "response": null
                 **/
                /* CommonHelper::printArray($result_array); die; */
                $exception = $result_array['exception']; //must be null in case of successful orders
                $response = $result_array['response'];
                $message = '';
                if (!is_null($response)) {
                    $errors = $response['errors'];
                    $validationErrors = !empty($response['validationErrors']) ? $response['validationErrors'] : ''; // '' or null
                    if (is_null($errors)) {
                        $responseCode = $response['responseCode']; //APPROVED : Code indicating the result of the authorization attempt.
                        $responseMsg = $response['responseMsg']; //Message indicating the result of the authorization attempt.
                        $orderNumber = $response['orderNumber']; //2Checkout Order Number
                        $merchantOrderId = $response['merchantOrderId']; //must be equal to order id sent
                        $transactionId = $response['transactionId']; //2Checkout Invoice ID
                        $message .= 'Response Code: ' . $responseCode . "\n";
                        $message .= 'Order Number: ' . $orderNumber . "\n";
                        $message .= 'Merchant Order Id: ' . $merchantOrderId . "\n";
                        $message .= 'Transaction Id: ' . $transactionId . "\n";
                        $message .= 'Payment Method: 2Checkout API' . "\n";
                        $message .= 'Response Message: ' . $responseMsg . "\n";
                        if ($responseCode == 'APPROVED') {
                            /* Recording Payment in DB */
                            $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $transactionId, $orderPaymentAmount, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), $message);
                            $json['redirect'] = UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
                            /* End Recording Payment in DB */
                        }
                    } else {
                        $json['error'] = $exception;
                    }
                } else {
                    $json['error'] = $exception['errorMsg'];
                }
            } else {
                $json['error'] = Labels::getLabel('MSG_EMPTY_GATEWAY_RESPONSE', $this->siteLangId);
            }
        } else {
            $json['error'] = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
        }
        curl_close($curl);
        echo json_encode($json);
    }

    private function getPaymentForm(string $orderId, bool $processRequest = false)
    {
        // $this->paymentType = $this->settings['payment_type']; // Form Submission from own server code not working.
        $this->paymentType = 'HOSTED';
        if ($this->paymentType == 'HOSTED') { /* check admin controller for confirmation */
            return $this->getHostedCheckoutForm($orderId, $processRequest);
        } else {
            return $this->getAPICheckoutForm($orderId, $processRequest);
        }
    }

    private function getHostedCheckoutForm(string $orderId, bool $processRequest = false)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $payment_gateway_charge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        /* Retrieve Primary Info corresponding to your order */
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $actionUrl = $this->plugin::PRODUCTION_URL . '/checkout/purchase';
        $actionUrl = false === $processRequest ? UrlHelper::generateUrl('TwocheckoutPay', 'charge', array($orderId)) : $actionUrl;

        $frm = new Form('frmTwoCheckout', array('id' => 'frmTwoCheckout', 'action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        } else {
            $frm->addHiddenField('', 'sid', $this->settings["sellerId"]);
            $frm->addHiddenField('', 'mode', '2CO'); //it should always be 2CO (We're using hosted payment approach)
            $txnid = $orderInfo["invoice"];
            $frm->addHiddenField('', 'li_0_name', 'Payment for Order - Invoice #' . $txnid);
            $frm->addHiddenField('', 'li_0_price', $payment_gateway_charge);
            $frm->addHiddenField('', 'li_0_product_id', $orderId); //in our case it is order id
            $frm->addHiddenField('', 'li_0_tangible', 'N'); //no need of charging or calculating shipping as we have already handled the same at our end.
            $frm->addHiddenField('', 'currency_code', $orderInfo["order_currency_code"]);
            $frm->addHiddenField('', 'merchant_order_id', $txnid);
            $frm->addHiddenField('', 'purchase_step', 'payment-method');
            $frm->addHiddenField('', 'x_receipt_link_url', UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', 'callback', [], '', false));
            /**
             * Pre-populate Billing Information
             **/
            $frm->addHiddenField('', 'card_holder_name', FatUtility::decodeHtmlEntities($orderInfo['customer_name'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'street_address', FatUtility::decodeHtmlEntities($orderInfo['customer_billing_address_1'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'street_address2', FatUtility::decodeHtmlEntities($orderInfo['customer_billing_address_2'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'city', FatUtility::decodeHtmlEntities($orderInfo['customer_billing_city'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'state', FatUtility::decodeHtmlEntities($orderInfo['customer_billing_state'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'zip', FatUtility::decodeHtmlEntities($orderInfo['customer_billing_postcode'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'country', FatUtility::decodeHtmlEntities($orderInfo['customer_billing_country'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'email', FatUtility::decodeHtmlEntities($orderInfo['customer_email'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'phone', FatUtility::decodeHtmlEntities($orderInfo['customer_phone'], ENT_QUOTES, 'UTF-8'));

            /**
             * Pre-populate Shipping Information
             **/
            $frm->addHiddenField('', 'ship_name', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_name'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'ship_street_address', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_address_1'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'ship_street_address2', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_address_2'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'ship_city', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_city'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'ship_state', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_state'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'ship_zip', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_postcode'], ENT_QUOTES, 'UTF-8'));
            $frm->addHiddenField('', 'ship_country', FatUtility::decodeHtmlEntities($orderInfo['customer_shipping_country'], ENT_QUOTES, 'UTF-8'));

            if (Plugin::ENV_SANDBOX == $this->settings['env']) {
                $frm->addHiddenField('', 'demo', 'Y');
            }
        }
        return $frm;
    }

    private function getAPICheckoutForm($orderId)
    {
        $frm = new Form('frmTwoCheckout', array('id' => 'frmTwoCheckout', 'action' => UrlHelper::generateUrl('TwocheckoutPay', 'send', array($orderId)), 'class' => "form form--normal"));

        $frm->addRequiredField(Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $this->siteLangId), 'ccNo');
        $frm->addHiddenField('', 'token', '');

        $data['months'] = applicationConstants::getMonthsArr($this->siteLangId);
        $today = getdate();
        $data['year_expire'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
        }
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_MONTH', $this->siteLangId), 'expMonth', $data['months'], '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_YEAR', $this->siteLangId), 'expYear', $data['year_expire'], '', array(), '');
        $fld = $frm->addPasswordField(Labels::getLabel('LBL_CVV_SECURITY_CODE', $this->siteLangId), 'cvv');
        $fld->requirements()->setRequired(true);
        /* $frm->addCheckBox(Labels::getLabel('LBL_SAVE_THIS_CARD_FOR_FASTER_CHECKOUT',$this->siteLangId), 'cc_save_card','1'); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));

        return $frm;
    }

    public function getExternalLibraries()
    {
        $json['libraries'] = [
            $this->plugin::PRODUCTION_URL . "/checkout/api/2co.min.js",
        ];
        FatUtility::dieJsonSuccess($json);
    }
}
