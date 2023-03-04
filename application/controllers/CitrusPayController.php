<?php

class CitrusPayController extends PaymentController
{
    public const KEY_NAME = "Citrus";

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

        $this->set('orderInfo', $orderInfo);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'citrus-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function callback()
    {
        $post = FatApp::getPostedData();
        $orderId = (isset($post['TxId'])) ? $post['TxId'] : 0;
        $orderPaymentObj = new OrderPayment($orderId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $request = '';
        foreach ($post as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        }
        if ($paymentGatewayCharge > 0) {
            if (strtoupper($post['TxStatus']) == 'SUCCESS') {
                //resp signature validation
                $str = $post['TxId'] . $post['TxStatus'] . $post['amount'] . $post['pgTxnNo'] . $post['issuerRefNo'] . $post['authIdCode'] . $post['firstName'] . $post['lastName'] . $post['pgRespCode'] . $post['addressZip'];
                $respSig = $post['signature'];
                if (hash_hmac('sha1', $str, $this->settings['merchant_secret_key']) == $respSig) {
                    $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $post['pgTxnNo'], $paymentGatewayCharge, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), $request);
                    FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
                } else {
                    $request .= "\n\n Citrus :: Invalid or forged transactiond.  \n\n";
                    $orderPaymentObj->addOrderPaymentComments($request);
                    FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
                }
            } else {
                $orderPaymentObj->addOrderPaymentComments($request);
                if ($post['pgRespCode'] == 3) {
                    FatApp::redirectUser(CommonHelper::getPaymentCancelPageUrl());
                }

                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        } else {
            FatUtility::exitWithErrorCode(404);
        }
    }


    private function getPaymentForm(string $orderId, bool $processRequest = false)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $vanityUrl = $this->settings['merchant_vanity_url'];
        $currency = 'INR';
        $merchantTxnId = $orderId;
        $orderAmount = $paymentGatewayCharge;
        $tmpdata = "$vanityUrl$orderAmount$merchantTxnId$currency";
        $secSignature = hash_hmac('sha1', $tmpdata, $this->settings['merchant_secret_key']);
        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $actionUrl = 'https://production.citruspay.com/sslperf/';
        } elseif (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == false) {
            $actionUrl = 'https://sandbox.citruspay.com/sslperf/';
        }
        $actionUrl = $actionUrl . "$vanityUrl";

        $actionUrl = false === $processRequest ? UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', array($orderId)) : $actionUrl;

        $frm = new Form('frm-citrus-payment', array('id' => 'frm-citrus-payment', 'action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        } else {
            $frm->addHiddenField('', 'merchantTxnId', $orderId);
            $frm->addHiddenField('', 'orderAmount', $paymentGatewayCharge);
            $frm->addHiddenField('', 'currency', "INR");
            $frm->addHiddenField('', 'secSignature', $secSignature);
            $frm->addHiddenField('', 'returnUrl', UrlHelper::generateFullUrl('CitrusPay', 'callback'));
            $frm->addHiddenField('', 'email', $orderInfo["customer_email"]);
            $frm->addHiddenField('', 'phoneNumber', $orderInfo["customer_phone"]);
            $frm->addHiddenField('', 'addressState', $orderInfo["customer_billing_state"]);
            $frm->addHiddenField('', 'addressCity', $orderInfo["customer_billing_city"]);
            $frm->addHiddenField('', 'addressStreet1', $orderInfo["customer_billing_address_1"]);
            $frm->addHiddenField('', 'addressStreet2', $orderInfo["customer_billing_address_2"]);
            $frm->addHiddenField('', 'addressCountry', $orderInfo["customer_billing_country"]);
            $frm->addHiddenField('', 'addressZip', $orderInfo["customer_billing_postcode"]);
            $custName = explode(" ", $orderInfo["customer_name"]);
            $firstName = $lastName = !empty($custName[0]) ? $custName[0] : '';
            $lastName = !empty($custName[1]) ? $custName[1] : '';
            $frm->addHiddenField('', 'firstName', $firstName);
            $frm->addHiddenField('', 'lastName', $lastName);
        }
        return $frm;
    }
}
