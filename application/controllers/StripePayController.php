<?php

require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/stripe/init.php';
class StripePayController extends PaymentController
{
    public const KEY_NAME = 'Stripe';

    private $error = false;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [
            'USD', 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BIF',
            'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK',
            'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD',
            'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW',
            'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR',
            'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR',
            'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD',
            'STD', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'UYU', 'UZS', 'VND', 'VUV', 'WST',
            'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW'
        ];
    }

    protected function minChargeAmountCurrencies()
    {
        return [
            'USD' => 0.50, 'AED' => 2.00, 'AUD' => 0.50, 'BGN' => 1.00, 'BRL' => 0.50, 'CAD' => 0.50, 'CHF' => 0.50, 'CZK' => 15.00,
            'DKK' => 2.50, 'EUR' => 0.50, 'GBP' => 0.30, 'HKD' => 4.00, 'HUF' => 175.00, 'INR' => 0.50, 'JPY' => 50, 'MXN' => 10,
            'MYR' => 2, 'NOK' => 3.00, 'NZD' => 0.50, 'PLN' => 2.00, 'RON' => 2.00, 'SEK' => 3.00, 'SGD' => 0.50
        ];
    }

    protected function zeroDecimalCurrencies()
    {
        return [
            'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'
        ];
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
        if (empty(trim($orderId))) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($message, FatUtility::isAjaxCall());
        }

        $stripe = array(
            'secret_key' => $this->settings['privateKey'],
            'publishable_key' => $this->settings['publishableKey']
        );
        $this->set('stripe', $stripe);

        if (!isset($this->settings['privateKey']) && !isset($this->settings['publishableKey'])) {
            $message = Labels::getLabel('STRIPE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId);
            $this->setErrorAndRedirect($message, FatUtility::isAjaxCall());
        }

        if (strlen(trim($this->settings['privateKey'])) > 0 && strlen(trim($this->settings['publishableKey'])) > 0) {
            if (strpos($this->settings['privateKey'], 'test') !== false || strpos($this->settings['publishableKey'], 'test') !== false) {
            }
            \Stripe\Stripe::setApiKey($stripe['secret_key']);
        } else {
            $this->error = Labels::getLabel('STRIPE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR', $this->siteLangId);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (array_key_exists($this->systemCurrencyCode, $this->minChargeAmountCurrencies())) {
            $stripeMinAmount = $this->minChargeAmountCurrencies()[$this->systemCurrencyCode];
            if ($stripeMinAmount > $paymentAmount) {
                $this->error = CommonHelper::replaceStringData(Labels::getLabel('MSG_MINIMUM_STRIPE_CHARGE_AMOUNT_IS_{MIN-AMOUNT}', $this->siteLangId), ['{MIN-AMOUNT}' => $stripeMinAmount]);
            }
        }

        if (!$orderInfo['id']) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($message, FatUtility::isAjaxCall());
        } elseif ($orderInfo && $orderInfo["order_payment_status"] == Orders::ORDER_PAYMENT_PENDING) {
            /* $checkPayment = $this->doPayment($payableAmount, $orderInfo); */
            $frm = $this->getPaymentForm($orderId);
            $this->set('frm', $frm);

            if (!empty($_POST['cc_number']) && 1 < count($_POST)) {
                $charge = $this->stripeAuthentication($orderId);
                $this->paymentInitiated($orderId);
                if (isset($charge['id']) && $charge['id']) {
                    $payment_method = \Stripe\PaymentMethod::create([
                        'type' => 'card',
                        'card' => [
                            'number' => $_POST['cc_number'],
                            'exp_month' => $_POST['cc_expire_date_month'],
                            'exp_year' => $_POST['cc_expire_date_year'],
                            'cvc' => $_POST['cc_cvv'],
                        ],
                    ]);
                    $payment_method = $payment_method->__toArray();

                    $this->set('order_id', $orderId);
                    $this->set('payment_intent_id', $charge['id']);
                    $this->set('payment_method_id', $payment_method['id']);
                    $this->set('client_secret', $charge['client_secret']);
                } else {
                    $this->error = Labels::getLabel('LBL_STRIPE_AUTHENTICATION_ERROR', $this->siteLangId);
                }
            }
        } else {
            $message = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            $this->error = $message;
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

        $this->set('cancelBtnUrl', $cancelBtnUrl);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall() && !isset($_POST['chargeAjax'])) {
            $json['html'] = $this->_template->render(false, false, 'stripe-pay/charge-ajax.php', true, false);
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

        if (in_array($this->systemCurrencyCode, $this->zeroDecimalCurrencies())) {
            return round($amount);
        }

        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
    }

    private function getPaymentForm($orderId)
    {
        $frm = new Form('frmPaymentForm', array('id' => 'frmPaymentForm', 'action' => UrlHelper::generateUrl('StripePay', 'charge', array($orderId)), 'class' => "form form--normal"));
        $frm->addRequiredField(Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $this->siteLangId), 'cc_number');
        $frm->addRequiredField(Labels::getLabel('LBL_CARD_HOLDER_NAME', $this->siteLangId), 'cc_owner');
        $data['months'] = applicationConstants::getMonthsArr($this->siteLangId);
        $today = getdate();
        $data['year_expire'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
        }
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_MONTH', $this->siteLangId), 'cc_expire_date_month', $data['months'], '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_YEAR', $this->siteLangId), 'cc_expire_date_year', $data['year_expire'], '', array(), '');
        $frm->addPasswordField(Labels::getLabel('LBL_CVV_SECURITY_CODE', $this->siteLangId), 'cc_cvv')->requirements()->setRequired();
        /* $frm->addCheckBox(Labels::getLabel('LBL_SAVE_THIS_CARD_FOR_FASTER_CHECKOUT',$this->siteLangId), 'cc_save_card','1'); */
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));

        return $frm;
    }

    public function stripeAuthentication($orderId = 0)
    {
        $stripeToken = FatApp::getPostedData('stripeToken', FatUtility::VAR_STRING, '');

        if (empty($stripeToken)) {
            $message = Labels::getLabel('MSG_The_Stripe_Token_was_not_generated_correctly', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL || FatUtility::isAjaxCall()) {
                FatUtility::dieJsonError($message);
            }
            throw new Exception($message);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $paymentAmount = $this->formatPayableAmount($paymentAmount);

        $stripe = array(
            'secret_key' => $this->settings['privateKey'],
            'publishable_key' => $this->settings['publishableKey']
        );

        $this->set('stripe', $stripe);

        if (!empty(trim($this->settings['privateKey'])) && !empty(trim($this->settings['publishableKey']))) {
            \Stripe\Stripe::setApiKey($stripe['secret_key']);
        }

        try {
            if (!empty(trim($this->settings['privateKey'])) && !empty(trim($this->settings['publishableKey']))) {
                \Stripe\Stripe::setApiKey($stripe['secret_key']);

                $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

                $customer = \Stripe\Customer::create([
                    "email" => $orderInfo['customer_email'],
                    "source" => $_POST['stripeToken'],
                ]);
                $charge = \Stripe\PaymentIntent::create([
                    "customer" => $customer->id,
                    'amount' => $paymentAmount,
                    'currency' => $this->systemCurrencyCode,
                    'payment_method_types' => ['card'],
                    'payment_method_options' => array('card' => array('installments' => null, 'request_three_d_secure' => 'any')),
                    'metadata' => array('order_id' => $orderId)


                ]);

                $charge = $charge->__toArray();
                return $charge;
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }

        if ($this->error) {
            $this->setErrorAndRedirect($this->error, FatUtility::isAjaxCall());
        }
    }

    public function stripeSuccess()
    {
        $stripe = [
            'secret_key' => $this->settings['privateKey'],
            'publishable_key' => $this->settings['publishableKey']
        ];

        \Stripe\Stripe::setApiKey($stripe['secret_key']);

        $charge = \Stripe\PaymentIntent::retrieve(
            $_POST['payment_intent_id']
        );

        $charge = $charge->__toArray();

        $orderPaymentObj = new OrderPayment($_POST['order_id']);

        $message = Labels::getLabel("MSG_PAYMENT_FAILED", $this->siteLangId);
        if (strtolower($charge['status']) == 'succeeded') {

            /* Recording Payment in DB */
            /* $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
            $payableAmount = $this->formatPayableAmount($paymentAmount); */
            $payment_amount = $charge['charges']['data'][0]['amount'];
            if (!in_array($this->systemCurrencyCode, $this->zeroDecimalCurrencies())) {
                $payment_amount = $payment_amount / 100;
            }
            $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $charge['id'], ($payment_amount), Labels::getLabel("MSG_Received_Payment", $this->siteLangId), json_encode($charge));
            /* End Recording Payment in DB */
            if (false === MOBILE_APP_API_CALL) {
                FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($_POST['order_id'])));
            }
        } else {
            TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $_POST['order_id'], json_encode($charge));
            $orderPaymentObj->addOrderPaymentComments($message);
            if (false === MOBILE_APP_API_CALL) {
                FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentFailed'));
            }
        }
    }

    public function getExternalLibraries()
    {
        $json['libraries'] = [
            'https://js.stripe.com/v3/',
            'https://js.stripe.com/v2/',
        ];
        FatUtility::dieJsonSuccess($json);
    }
}
