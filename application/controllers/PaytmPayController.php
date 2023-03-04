<?php

class PaytmPayController extends PaymentController
{

    public const KEY_NAME = "Paytm";

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

        if (false === $this->plugin->init()) {
            $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
        }
    }

    public function charge($orderId)
    {

        if (empty($orderId)) {
            $msg = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
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
            if (false === ($token = $this->plugin->getPaymentToken($orderId))) {
                $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
            }
            $frm = $this->getPaymentForm($orderId, true);
            $frm->fill(['orderId' => $orderId, 'mid' => $this->plugin->getMerchantId(), 'txnToken' => $token]);
            $processRequest = true;
        }

        $this->set('frm', $frm);
        $this->set('processRequest', $processRequest);

        $this->set('orderInfo', $orderInfo);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'paytm-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function callback()
    {
        $post = FatApp::getPostedData();
        if (!isset($post['ORDERID']) || empty($post['ORDERID'])) {
            FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        }
        $orderId = $post['ORDERID'];
        if (!$this->plugin->verifySignature(FatApp::getPostedData())) {
            $this->logFailure($orderId, Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        if (!$this->plugin->verifyPayment($orderId)) {
            $this->logFailure($orderId);
        }

        $orderPaymentObj = new OrderPayment($orderId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if (false === $orderPaymentObj->addOrderPayment(self::KEY_NAME, $post['TXNID'], $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($post))) {
            $msg = $orderPaymentObj->getError();
            $this->logFailure($orderId, $msg);
        }
        FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
    }

    private function getPaymentForm(string $orderId, bool $processRequest = false)
    {

        $actionUrl = false === $processRequest ? UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', [$orderId]) : $this->plugin->getApiUrl() . "showPaymentPage?mid=" . $this->plugin->getMerchantId() . "&orderId=" . $orderId;
        $frm = new Form('frmPaytm', array('id' => 'frmPaytm', 'action' => $actionUrl, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId', $orderId);

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        } else {
            $frm->addHiddenField('', 'mid');
            $frm->addHiddenField('', 'txnToken');
        }
        return $frm;
    }

    private function logFailure(string $orderId, string $msg = '', array $response = [])
    {
        $response = !empty($response) ? $response : $_REQUEST;
        $orderPaymentObj = new OrderPayment($orderId);
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));

        if (empty($msg)) {
            $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $this->plugin->getError()]);
        }

        $orderPaymentObj->addOrderPaymentComments($msg);
        Message::addErrorMessage($msg);
        FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        die;
    }

}
