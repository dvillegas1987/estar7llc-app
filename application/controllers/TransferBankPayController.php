<?php

class TransferBankPayController extends PaymentController
{
    public const KEY_NAME = "TransferBank";

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [$this->systemCurrencyCode];
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
        if (!$orderInfo['id']) {
            FatUtility::exitWIthErrorCode(404);
        } elseif ($orderInfo && $orderInfo["order_payment_status"] == Orders::ORDER_PAYMENT_PENDING) {
            $frm = $this->getTransferBankForm($this->siteLangId, $orderId);
            $this->set('frm', $frm);
            $this->set('paymentAmount', $paymentAmount);
        } else {
            $this->set('error', Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }
        $this->set('orderInfo', $orderInfo);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('settings', $this->settings);
        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }
        $this->set('cancelBtnUrl', $cancelBtnUrl);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'transfer-bank-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }       
        $this->_template->render(true, false);
    }

    public function send($orderId)
    {
        $frm = $this->getTransferBankForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if ($orderInfo) {
            $this->paymentInitiated($orderId);
            $cartObj = new Cart();
            $cartObj->clear();
            $cartObj->updateUserCart();
            if (1 < count(array_filter($post))) {
                if (!$orderPaymentObj->addOrderPayment($post["opayment_method"], $post['opayment_gateway_txn_id'], $post["opayment_amount"], $post["opayment_comments"], '', false, 0, Orders::ORDER_PAYMENT_PENDING)) {
                    FatUtility::dieJsonError($orderPaymentObj->getError());
                }
            } else {
                $comment = Labels::getLabel('MSG_PAYMENT_INSTRUCTIONS', $this->siteLangId) . "\n\n";
                $comment .= $this->settings["business_name"] . "\n\n";
                $comment .= Labels::getLabel('MSG_PAYMENT_NOTE', $this->siteLangId);
                $orderPaymentObj->addOrderPaymentComments($comment, true);
            }

            if (true === MOBILE_APP_API_CALL) {
                $this->set('msg', Labels::getLabel('MSG_ORDER_PLACED_SUCCESSFULLY', $this->siteLangId));
                $this->_template->render();
            }

            $json['redirect'] = UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
        } else {
            $msg = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($msg);
            }
            $json['error'] = $msg;
        }
        echo json_encode($json);
    }
}
