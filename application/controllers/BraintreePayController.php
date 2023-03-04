<?php

class BraintreePayController extends PaymentController
{
    public const ENVOIRMENT_LIVE = 'production';
    public const ENVOIRMENT_SANDBOX = 'sandbox';

    public const KEY_NAME = "Braintree";

    private $error = false;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [
            'AED', 'AMD', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BYN', 'BZD', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'MAD', 'MDL', 'MKD', 'MNT', 'MOP', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VES', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZWD'
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
            $this->error = Labels::getLabel('ERR_INVALID_ACCESS', $this->siteLangId);
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieJsonError($this->error);
            }
            Message::addErrorMessage($this->error);
            CommonHelper::redirectUserReferer();
        }

        $clientToken = $this->getClientToken();

        if (!$clientToken) {
            $this->error = empty($this->error) ? Labels::getLabel('ERR_BRAINTREE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId) : $this->error;
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieJsonError($this->error);
            }
            Message::addErrorMessage($this->error);
            CommonHelper::redirectUserReferer();
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!$orderInfo['id']) {
            FatUtility::exitWithErrorCode(404);
        }
        $currencyCode = '';
        if (count($orderInfo) < 1 || (count($orderInfo) > 1 && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING)) {
            $this->error = Labels::getLabel('ERR_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
        } else {
            $currencyCode = strtolower($orderInfo["order_currency_code"]);
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);
            if (isset($_POST['paymentMethodNonce'])) {
                $this->doPayment($payableAmount, $orderInfo);
            }
        }

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        if ($this->error) {
            $this->set('error', $this->error);
        }

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set("currencyCode", $currencyCode);
        $this->set('orderId', $orderId);
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('clientToken', $clientToken);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'braintree-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function checkCardType()
    {
        $post = FatApp::getPostedData();
        $res = ValidateElement::ccNumber($post['cc']);
        echo json_encode($res);
        exit;
    }

    private function formatPayableAmount($amount = null)
    {
        if ($amount == null) {
            return false;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
    }

    private function getPaymentForm($orderId)
    {
        $frm = new Form('frmPaymentForm', array('id' => 'frmPaymentForm', 'action' => UrlHelper::generateUrl('BraintreePay', 'charge', array($orderId), CONF_WEBROOT_FRONTEND), 'class' => "form form--normal"));
        $frm->addButton('', 'btn_submit', Labels::getLabel('BTN_PAY_NOW', $this->siteLangId), array("disabled" => "disabled", "id" => "submit-button"));
        return $frm;
    }

    private function doPayment($payment_amount = null, $orderInfo = null)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        if ($payment_amount == null || $orderInfo['id'] == null) {
            $this->error = Labels::getLabel('ERR_INVALID_ORDER', $this->siteLangId);
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieJsonError($this->error);
            }
            Message::addErrorMessage($this->error);
            CommonHelper::redirectUserReferer();
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            try {
                if (!isset($_POST['paymentMethodNonce'])) {
                    throw new Exception("The paymentMethod Nonce was not generated correctly");
                } else {
                    if (!$this->getClientToken()) {
                        $this->error = Labels::getLabel('ERR_BRAINTREE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId);
                        if (FatUtility::isAjaxCall()) {
                            FatUtility::dieJsonError($this->error);
                        }
                        Message::addErrorMessage($this->error);
                        CommonHelper::redirectUserReferer();
                    }
                    $this->paymentInitiated($orderInfo['id']);
                    $charge = Braintree_Transaction::sale(
                        array(
                            'amount' => $_POST['amount'],
                            'paymentMethodNonce' => $_POST['paymentMethodNonce'],
                            'options' => [
                                'submitForSettlement' => true
                            ]
                        )
                    );

                    $charge = (array) $charge;
                    $message = Labels::getLabel("ERR_PAYMENT_FAILED", $this->siteLangId);
                    $orderPaymentObj = new OrderPayment($orderInfo['id']);
                    if (!empty($charge) && 0 < count($charge)) {
                        if (isset($charge['success']) && 0 < $charge['success'] || (isset($charge['transaction']) && !is_null($charge['transaction']))) {

                            /* Recording Payment in DB */
                            $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $charge['transaction']->id, ($payment_amount / 100), Labels::getLabel("MSG_Received_Payment", $this->siteLangId), json_encode($charge));
                            /* End Recording Payment in DB */

                            FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderInfo['id'])));
                        } else {
                            SystemLog::transaction(json_encode($charge), self::KEY_NAME . "-" . $orderInfo['id']);
                            $orderPaymentObj->addOrderPaymentComments($message);
                            FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentFailed'));
                        }
                    } else {
                        SystemLog::transaction(json_encode($charge), self::KEY_NAME . "-" . $orderInfo['id']);
                        $orderPaymentObj->addOrderPaymentComments($message);
                        FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentFailed'));
                    }
                }
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieJsonError($this->error);
                }
                Message::addErrorMessage($this->error);
                CommonHelper::redirectUserReferer();
            }
        }
    }

    private function getClientToken()
    {
        $this->autoloadRequiredFunctions();
        try {
            if (!isset($this->settings['private_key']) || !isset($this->settings['public_key']) || !isset($this->settings['merchant_id'])) {
                $this->error = Labels::getLabel('ERR_MISSING_CONFIGURATION_KEYS', $this->siteLangId);
                return false;
            }
            $envoirment = (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) ? static::ENVOIRMENT_LIVE : static::ENVOIRMENT_SANDBOX;

            Braintree_Configuration::environment($envoirment);
            Braintree_Configuration::merchantId($this->settings['merchant_id']);
            Braintree_Configuration::publicKey($this->settings['public_key']);
            Braintree_Configuration::privateKey($this->settings['private_key']);

            return Braintree_ClientToken::generate();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            if (empty($this->error)) {
                $this->error = Labels::getLabel('ERR_SOMETHING_WENT_WRONG_WITH_PAYMENT_GATEWAY_CONFIGURATION', $this->siteLangId);
            }
            return false;
        }
    }

    public function autoloadRequiredFunctions()
    {
        spl_autoload_register(function ($className) {
            if (strpos($className, 'Braintree') !== 0) {
                return;
            }

            $fileName = CONF_INSTALLATION_PATH . 'library' . DIRECTORY_SEPARATOR . 'braintree' . DIRECTORY_SEPARATOR;

            if ($lastNsPos = strripos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }

            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            if (is_file($fileName)) {
                require_once $fileName;
            }
        });
    }

    public function getExternalLibraries()
    {
        $json['libraries'] = [
            'https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js',
        ];
        FatUtility::dieJsonSuccess($json);
    }
}
