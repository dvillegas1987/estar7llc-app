<?php

require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/PayFortStart/autoload.php';
class PayFortStartPayController extends PaymentController
{
    public const KEY_NAME = "PayFortStart";

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

    public function charge($orderId = '')
    {
        if (empty($orderId)) {
            FatUtility::exitWIthErrorCode(404);
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $amount_in_cents = $this->formatPayableAmount($paymentGatewayCharge);

        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);

        $this->set('open_key', $this->settings['open_key']);
        $this->set('paymentAmount', $paymentGatewayCharge);
        $this->set('amount_in_cents', $amount_in_cents);
        $this->set('orderId', $orderId);
        $this->set('paymentgatewayImg', '');

        $this->set('orderInfo', $orderInfo);
        $this->set('currency', $orderInfo['order_currency_code']);
        $this->set('customer_email', $orderInfo['customer_email']);
        $this->set('orderPaymentGatewayDescription', $orderPaymentGatewayDescription);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'pay-fort-start-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        } else {
            $this->_template->render(true, false);
        }
    }

    public function payFortCharge()
    {
        $post = FatApp::getPostedData();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $orderId = $post["ord"];
            $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
            $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
            $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);
            $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
            $amount_in_cents = $this->formatPayableAmount($paymentGatewayCharge);
            $token = $post["startToken"];
            $email = $post["startEmail"];
            Start::setApiKey($this->settings["secret_key"]);
            try {
                $charge = Start_Charge::create(
                    array(
                    "amount" => $amount_in_cents,
                    "currency" => $orderInfo['order_currency_code'],
                    "card" => $token,
                    "email" => $email,
                    "ip" => $_SERVER["REMOTE_ADDR"],
                    "description" => $orderPaymentGatewayDescription
                    )
                );
                $charge['order_id'] = $orderId;
                /* CommonHelper::printArray($charge); die; */
                $this->notifyCallBack($charge);
            } catch (Start_Error $e) {
                $error_code = $e->getErrorCode();
                $error_message = $e->getMessage();
                if ($error_code === "card_declined") {
                    $msg = "Charge was declined";
                } else {
                    $msg = "Charge was not processed.";
                }
                $failUrl = UrlHelper::generateUrl('custom', 'paymentFailed');
                FatApp::redirectUser($failUrl);
            }
        }
        Message::addErrorMessage(Labels::getLabel('LBL_Page_not_found', $this->siteLangId));
        CommonHelper::redirectUserReferer();
    }

    protected function notifyCallBack($response)
    {
        $orderId = $response['order_id'];
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $payment_gateway_charge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $order_info = $orderPaymentObj->getOrderPrimaryinfo();

        if ($order_info) {
            if (count($response) > 0 and isset($response['state'])) {
                $order_payment_status = 0;
                switch (strtolower($response['state'])) {
                case 'captured':
                    $order_payment_status = 1;
                    break;
                case 'Failed':
                    $order_payment_status = 0;
                    break;
                default:
                    $order_payment_status = 0;
                    break;
                }
                $request = '';
                $payfortReceivePayment = $response['captured_amount'] / 100;
                $total_paid_match = ((float) $payfortReceivePayment == $payment_gateway_charge);
                /* if (!$receiver_match) {
                  $request .= "\n\n PP_STANDARD :: RECEIVER EMAIL MISMATCH! " . strtolower($post['receiver_email']) . "\n\n";
                  } */
                if (!$total_paid_match) {
                    $request .= "\n\n PP_STANDARD :: TOTAL PAID MISMATCH! " . strtolower($response['captured_amount']) . "\n\n";
                }
                if ($order_payment_status == 1 && $total_paid_match) {
                    $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $response['id'], $payment_gateway_charge, Labels::getLabel("LBL_Received_Payment", $this->siteLangId), json_encode($response));
                    FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($order_id)));
                } else {
                    TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));
                    $orderPaymentObj->addOrderPaymentComments($request);
                }
            }
        }
    }

    private function formatPayableAmount($amount = null)
    {
        if ($amount == null) {
            return false;
        }
        $amount = number_format($amount, 2, '.', '');
        return $amount * 100;
        return $amount;
    }
}
