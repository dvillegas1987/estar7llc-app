<?php

require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/ccavenue/Crypto.php';
class CcavenuePayController extends PaymentController
{
    public const KEY_NAME = "Ccavenue";

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return ['INR'];
    }

    private function init(): void
    {
        if (false === $this->plugin->validateSettings($this->siteLangId)) {
            $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
        }

        $this->settings = $this->plugin->getSettings();
    }

    public function charge($orderId)
    {
        if (empty($orderId)) {
            FatUtility::exitWIthErrorCode(404);
        }

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
            $frm = $this->getPaymentForm($orderId, true);
            $processRequest = true;
        }

        $frm->fill(['orderId' => $orderId]);
        $this->set('frm', $frm);
        $this->set('processRequest', $processRequest);

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'ccavenue-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function iframe($orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $payment_gateway_charge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            $this->setErrorAndRedirect(Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId), FatUtility::isAjaxCall());
        }
        $working_key = $this->settings['working_key'];
        $access_code = $this->settings['access_code'];
        $merchant_data = '';
        $post = FatApp::getPostedData();
        foreach ($post as $key => $value) {
            $merchant_data .= $key . '=' . $value . '&';
        }

        //$merchant_data= str_replace("#~#","&",$merchant_data);
        $merchant_data .= "currency=" . $this->systemCurrencyCode;

        $encrypted_data = $this->encrypt($merchant_data, $working_key); // Method for encrypting the data.

        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $iframe_url = 'https://secure.ccavenue.com';
        } else {
            $iframe_url = 'https://test.ccavenue.com';
        }
        $this->paymentInitiated($orderId);
        $iframe_url .= '/transaction/transaction.do?command=initiateTransaction&encRequest=' . $encrypted_data . '&access_code=' . $access_code;
        FatApp::redirectUser($iframe_url);
    }
    public function callback()
    {
        $post = FatApp::getPostedData();
        $workingKey = $this->settings['working_key'];
        $encResponse = $post["encResp"];            //This is the response sent by the CCAvenue Server
        $rcvdString = $this->decrypt($encResponse, $workingKey);        //Crypto Decryption used as per the specified working key.
        $request = $rcvdString;
        $order_status = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            if ($i == 3) {
                $order_status = $information[1];
            }
            if ($i == 26) {
                $orderId = $information[1];
            }
            if ($i == 10) {
                $paid_amount = $information[1];
            }
            if ($i == 1) {
                $tracking_id = $information[1];
            }
        }
        $orderPaymentObj = new OrderPayment($orderId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if ($paymentGatewayCharge > 0) {
            $total_paid_match = ((float) $paid_amount == $paymentGatewayCharge);
            if (!$total_paid_match) {
                $request .= "\n\n CCAvenue :: TOTAL PAID MISMATCH! " . strtolower($paid_amount) . "\n\n";
            }
            if ($order_status == "Success" && $total_paid_match) {
                $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $tracking_id, $paymentGatewayCharge, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode($post));
                FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
            } else {
                TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($post));
                $orderPaymentObj->addOrderPaymentComments($request);
                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        }
    }

    private function getPaymentForm(string $orderId, bool $processRequest = false)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        $actionUrl = false === $processRequest ? UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', array($orderId)) : UrlHelper::generateFullUrl(self::KEY_NAME . 'Pay', 'iframe', array($orderId));

        $frm = new Form('frm-ccavenue', array('id' => 'frm-ccavenue', 'action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        } else {
            $frm->addHiddenField('', 'tid', "", array("id" => "tid"));
            $frm->addHiddenField('', 'merchant_id', $this->settings["merchant_id"]);
            $frm->addHiddenField('', 'order_id', $orderInfo['invoice']);
            $frm->addHiddenField('', 'amount', $paymentGatewayCharge);
            $frm->addHiddenField('', 'merchant_param1', $orderId);
            //$frm->addHiddenField('', 'currency', $orderInfo["order_currency_code"]);
            $frm->addHiddenField('', 'language', "EN");
            $frm->addHiddenField('', 'redirect_url', UrlHelper::generateFullUrl('CcavenuePay', 'callback'));
            $frm->addHiddenField('', 'cancel_url', CommonHelper::getPaymentCancelPageUrl());
            //$frm->addHiddenField('', 'item_name_1', $order_payment_gateway_description);
            $frm->addHiddenField('', 'billing_name', $orderInfo["customer_billing_name"]);
            $frm->addHiddenField('', 'billing_address', $orderInfo["customer_billing_address_1"] . ', ' . $orderInfo["customer_billing_address_2"]);
            $frm->addHiddenField('', 'billing_city', $orderInfo["customer_billing_city"]);
            $frm->addHiddenField('', 'billing_state', $orderInfo["customer_billing_state"]);
            $frm->addHiddenField('', 'billing_zip', $orderInfo["customer_billing_postcode"]);
            $frm->addHiddenField('', 'billing_country', $orderInfo['customer_billing_country']);
            $frm->addHiddenField('', 'billing_tel', $orderInfo['customer_billing_phone']);
            $frm->addHiddenField('', 'billing_email', $orderInfo['customer_email']);
            $frm->addHiddenField('', 'delivery_name', $orderInfo["customer_shipping_name"]);
            $frm->addHiddenField('', 'delivery_address', $orderInfo["customer_shipping_address_1"] . ', ' . $orderInfo["customer_shipping_address_2"]);
            $frm->addHiddenField('', 'delivery_city', $orderInfo["customer_shipping_city"]);
            $frm->addHiddenField('', 'delivery_state', $orderInfo["customer_shipping_state"]);
            $frm->addHiddenField('', 'delivery_zip', $orderInfo["customer_shipping_postcode"]);
            $frm->addHiddenField('', 'delivery_country', $orderInfo['customer_shipping_country']);
            $frm->addHiddenField('', 'delivery_tel', $orderInfo['customer_shipping_phone']);
            $frm->addHiddenField('', 'integration_type', 'iframe_normal');
        }
        return $frm;
    }

    public function encrypt($plainText, $key)
    {
        $secretKey = hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = openssl_encrypt($plainText, "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($encryptedText);
        return $encryptedText;
    }

    public function decrypt($encryptedText, $key)
    {
        $secretKey = hextobin(md5($key));
        $initVector =  pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = hextobin($encryptedText);
        $decryptedText =  openssl_decrypt($encryptedText, "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
}
