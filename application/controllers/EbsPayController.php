<?php

class EbsPayController extends PaymentController
{
    public const KEY_NAME = "Ebs";
    public const PRODUCTION_URL = "https://secure.ebs.in/pg/ma/payment/request";
    private $error = false;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [
            'INR', 'USD', 'GBP', 'EUR', 'AED', 'QAR', 'OMR', 'CAD', 'HKD', 'SGD', 'AUD'
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
        if (empty(trim($orderId))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $ebs = array(
            'account_id' => trim($this->settings['accountId']),
            'secret_key' => trim($this->settings['secretKey'])
        );
        $this->set('ebs', $ebs);

        if (!strlen(trim($ebs['account_id'])) > 0 && strlen(trim($ebs['secret_key'])) > 0) {
            $this->error = Labels::getLabel('MSG_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
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

        $this->set('success', true);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        if ($this->error) {
            $this->set('error', $this->error);
        }

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'ebs-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    private function formatPayableAmount($amount = null)
    {
        if ($amount == null) {
            return false;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
    }

    private function getPaymentForm(string $orderId, bool $processRequest = false)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $actionUrl = false === $processRequest ? UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', array($orderId)) : self::PRODUCTION_URL;

        $frm = new Form('payment', array('id' => 'frmPaymentForm', 'action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        } else {
            if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
                $mode = "LIVE";
            } else {
                $mode = "TEST";
            }

            $order_payment_gateway_description = sprintf(Labels::getLabel('M_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);
            $return_url = UrlHelper::generateFullUrl('ebsPay', 'callback');

            $dataToPost = [
                'account_id' => $this->settings["accountId"],
                'address' => $orderInfo["customer_billing_address_1"] . ' ' . $orderInfo["customer_billing_address_2"],
                'amount' => $paymentAmount,
                'channel' => 0,
                'city' => $orderInfo["customer_billing_city"],
                'country' => $orderInfo["customer_billing_country_code"],
                'currency' => $this->systemCurrencyCode,
                'description' => $order_payment_gateway_description,
                'display_currency' => $this->systemCurrencyCode,
                'display_currency_rates' => applicationConstants::YES,
                'email' => $orderInfo['customer_email'],
                'mode' => $mode,
                'name' => $orderInfo["customer_name"],
                'phone' => $orderInfo['customer_billing_phone'],
                'postal_code' => $orderInfo["customer_billing_postcode"],
                'reference_no' => $orderId,
                'return_url' => $return_url . '?DR={DR}',
                'ship_address' => $orderInfo["customer_shipping_address_1"] . ' ' . $orderInfo["customer_shipping_address_2"],
                'ship_city' => $orderInfo["customer_shipping_city"],
                'ship_country' => $orderInfo["customer_shipping_country_code"],
                'ship_name' => $orderInfo["customer_shipping_name"],
                'ship_phone' => $orderInfo['customer_shipping_phone'],
                'ship_postal_code' => $orderInfo["customer_shipping_postcode"],
                'ship_state' => $orderInfo["customer_shipping_state"],
                'state' => $orderInfo["customer_billing_state"],
            ];

            $hashData = $this->settings["secretKey"]; //Pass your Registered Secret Key
            foreach ($dataToPost as $key => $value) {
                if (strlen($value) > 0) {
                    $hashData .= '|' . $value;
                }
                $frm->addHiddenField('', $key, $value);
            }
            $secure_hash = '';
            if (strlen($hashData) > 0) {
                $secure_hash = strtoupper(hash("sha512", $hashData)); //for SHA512
                //$secure_hash = strtoupper(hash("sha1",$hashData));//for SHA1
                //$secure_hash = strtoupper(md5($hashData));//for MD5
                $frm->addHiddenField('', 'secure_hash', $secure_hash);
            }
            $frm->setJsErrorDisplay('afterfield');
        }
        return $frm;
    }

    public function callback()
    {
        $get = FatApp::getQueryStringData();
        if (isset($get['DR'])) {
            include_once CONF_INSTALLATION_PATH . 'library/payment-plugins/ebs/Rc43.php';

            $secret_key = $this->settings["secretKey"];
            $DR = preg_replace("/\s/", "+", $get['DR']);
            $rc4 = new Crypt_RC4($secret_key);
            $QueryString = base64_decode($DR);
            $rc4->decrypt($QueryString);
            $QueryString = explode('&', $QueryString);
            $response = array();
            foreach ($QueryString as $param) {
                $param = explode('=', $param);
                $response[$param[0]] = urldecode($param[1]);
            }

            $data['response'] = $response;
            $orderId = (isset($response['MerchantRefNo'])) ? $response['MerchantRefNo'] : 0;
            $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
            $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
            if ($response['ResponseCode'] == '0') {
                $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $response['TransactionID'], $paymentAmount, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode($response));
                FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
            } else {
                TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));
                $orderPaymentObj->addOrderPaymentComments(serialize($response));
                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        }
    }
}
