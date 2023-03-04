<?php
require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/PayFort/PayfortIntegration.php';
class PayFortPayController extends PaymentController
{
    public const KEY_NAME = "PayFort";
    private $testEnvironmentUrl = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';
    private $liveEnvironmentUrl = 'https://checkout.payfort.com/FortAPI/paymentPage';
    private $error = false;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [
            'AED', 'USD', 'JOD', 'KWD', 'OMR', 'TND', 'BHD', 'LYD', 'IQD', 'SAR'
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
        $paymentGatewayCharge = 0.00;
        $orderInfo  = array();
        $requestParams = $this->generatePaymentFormParams($orderId, $orderPaymentObj, $orderInfo, $paymentGatewayCharge);
        if ($requestParams) {
            $frm = $this->getPaymentForm($requestParams);
            $this->set('paymentAmount', $paymentGatewayCharge);
            $this->set('frm', $frm);
            $this->set('orderInfo', $orderInfo);
            $this->set('requestParams', $requestParams);
        } else {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST_PARAMETERS', $this->siteLangId);
        }

        if ($this->error) {
            $this->set('error', $this->error);
        }
        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('paymentAmount', $paymentGatewayCharge);
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'pay-fort-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function doPayment($orderId = '')
    {
        if (empty($orderId) && !empty($_REQUEST['merchant_reference'])) {
            $orderId = $_REQUEST['merchant_reference'];
        }
        if (!$orderId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Account', 'profileInfo'));
        }

        $paymentChargeUrl = UrlHelper::generateUrl('PayFortPay', 'charge', array($orderId));
        if (!(isset($_REQUEST['signature']) and !empty($_REQUEST['signature']))) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatApp::redirectUser($paymentChargeUrl);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = 0.00;
        $orderInfo = array();

        $requestFormParams = $this->generatePaymentFormParams($orderId, $orderPaymentObj, $orderInfo, $paymentGatewayCharge, true);

        if ($requestFormParams === false || !$orderInfo) {
            Message::addErrorMessage($this->error);
            FatApp::redirectUser($paymentChargeUrl);
        }

        //calculate Signature after back to merchant and comapre it with request Signature
        $arrData = $_REQUEST;
        unset($arrData['signature']);
        unset($arrData['url']);
        unset($_REQUEST['expiry_date']);
        unset($_REQUEST['card_security_code']);

        $payfortIntegration = new PayfortIntegration();

        $returnSignature = $payfortIntegration->calculateSignature($arrData, $this->settings['sha_response_phrase'], $this->settings['sha_type']);

        if ($returnSignature == $_REQUEST['signature'] && substr($_REQUEST['response_code'], 2) == '000' && $_REQUEST['amount'] == $paymentGatewayCharge && $_REQUEST['currency'] == $this->systemCurrencyCode && $_REQUEST['merchant_reference'] == $orderInfo['id']) {
            $message = array();

            foreach ($_REQUEST as $key => $value) {
                $key = str_replace('_', ' ', $key);
                $message[] = ucwords($key) . ': ' . (string) $value;
            }

            $gateWayCharges = ($paymentGatewayCharge / 100);
            $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $_REQUEST['fort_id'], $gateWayCharges, 'Received Payment', json_encode($_REQUEST));
            FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
        } else {
            $orderPaymentObj->addOrderPaymentComments('#' . $_REQUEST['response_code'] . ': ' . $_REQUEST['response_message']);
        }
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($_REQUEST));
        if (substr($_REQUEST['response_code'], 2) == '072') {
            FatApp::redirectUser(CommonHelper::getPaymentCancelPageUrl());
        } else {
            FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
        }
    }
    private function generatePaymentFormParams($orderId, $orderPaymentObj, &$orderInfo, &$paymentGatewayCharge = 0.00, $returnParams = true)
    {
        if (!$orderId || !$orderPaymentObj) {
            $this->error = Labels::getLabel('MSG_Invalid_order_request', $this->siteLangId);
            return false;
        }

        $paymentGatewayCharge = $this->formatPayableAmount($orderPaymentObj->getOrderPaymentGatewayAmount());
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            $this->error = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            return false;
        } elseif ($orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $this->error = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            return false;
        }

        $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);

        if ($returnParams) {
            $return_url = UrlHelper::generateFullUrl('PayFortPay', 'doPayment', array($orderId), '', false);

            $paramsValues = array(
                'access_code' => $this->settings['access_code'],
                'amount' => $paymentGatewayCharge,
                'command' => 'PURCHASE',
                'currency' => mb_strtoupper($this->systemCurrencyCode),
                'customer_email' => $orderInfo['customer_email'],
                'language' => mb_strtolower($orderInfo['order_language']),
                'merchant_identifier' => $this->settings['merchant_id'],
                'merchant_reference' => $orderInfo['id'],
                'order_description' => $orderPaymentGatewayDescription,
                'return_url' => $return_url,
            );

            $payfortIntegration = new PayfortIntegration();
            $signature      = $payfortIntegration->calculateSignature($paramsValues, $this->settings['sha_request_phrase'], $this->settings['sha_type']);
            $paramsValues['signature'] = $signature;

            return $paramsValues;
        } else {
            return array();
        }
    }

    private function formatPayableAmount($amount = null)
    {
        if ($amount == null) {
            return false;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
    }

    private function getPaymentForm($requestParams = array())
    {
        $actionUrl = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) ? $this->liveEnvironmentUrl : $this->testEnvironmentUrl;

        $frm = new Form('frmPayFort', array('id' => 'frmPayFort', 'class' => 'form', 'action' => $actionUrl));
        foreach ($requestParams as $a => $b) {
            $frm->addHiddenField('', htmlentities($a), htmlentities($b));
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_CANCEL', $this->siteLangId));
        return $frm;
    }
}
