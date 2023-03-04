<?php

class PayPalPayoutController extends PayoutBaseController
{
    public const KEY_NAME = 'PayPalPayout';
    public static function reqFields()
    {
        $reqFields = [
            'amount' => [
                'type' => PluginSetting::TYPE_FLOAT,
                'required' => true,
                'label' => "Amount",
            ]
        ];
        $formFields = static::formFields();
        return array_merge($reqFields, $formFields);
    }

    public function getRequestForm()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $frm = $this->getFormObj(static::reqFields());

        $userObj = new User($userId);
        $data = $userObj->getUserInfo('credential_email as email');
        if (!empty($data)) {
            $frm->fill($data);
        }
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public static function formFields()
    {
        return [
            'email' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => false,
                'label' => "Email Id",
            ],
            'paypal_id' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => false,
                'label' => "PayPal Id",
            ],
        ];
    }

    public function form()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $frm = $this->getFormObj(static::formFields());

        $data = User::getUserMeta($userId);
        if (!empty($data)) {
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setupAccountForm()
    {
        $frm = $this->getFormObj(self::formFields());

        $post = array_filter($frm->getFormDataFromArray(FatApp::getPostedData()));
        unset($post['keyName'], $post['plugin_id']);
        $this->updateUserInfo($post);
    }

    public function saveWithdrawalSpecifics($withdrawalId, $data, $elements)
    {
        if (empty($withdrawalId) || empty($data) || empty($elements)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', CommonHelper::getLangId());
            return false;
        }

        foreach ($data as $key => $val) {
            if (!in_array($key, $elements)) {
                continue;
            }
            $updateData = [
                'uwrs_withdrawal_id' => $withdrawalId,
                'uwrs_key' => $key,
                'uwrs_value' => is_array($val) ? serialize($val) : $val,
            ];

            if (!FatApp::getDb()->insertFromArray(User::DB_TBL_USR_WITHDRAWAL_REQ_SPEC, $updateData, true, array(), $updateData)) {
                $message = Labels::getLabel('LBL_ACTION_TRYING_PERFORM_NOT_VALID', $this->siteLangId);
                FatUtility::dieJsonError($message);
            }
        }
        return true;
    }

    public function setup()
    {
        $this->validateWithdrawalRequest();

        $frm = PluginSetting::getForm(self::reqFields(), $this->siteLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $post['withdrawal_amount'] = $post['amount'];
        if (empty($post['email']) && empty($post['paypal_id'])) {
            $post['email'] = UserAuthentication::getLoggedUserAttribute('user_email');
        }

        if (false === $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        $userId = UserAuthentication::getLoggedUserId();
        $userObj = new User($userId);
        $withdrawal_payment_method = FatApp::getPostedData('plugin_id', FatUtility::VAR_INT, 0);
        if (1 > $withdrawal_payment_method) {
            $withdrawal_payment_method = Plugin::getAttributesByCode(self::KEY_NAME, 'plugin_id');
        }
        // $withdrawal_payment_method = ($withdrawal_payment_method > 0 && array_key_exists($withdrawal_payment_method, User::getAffiliatePaymentMethodArr($this->siteLangId))) ? $withdrawal_payment_method  : User::AFFILIATE_PAYMENT_METHOD_BANK;


        $post['withdrawal_payment_method'] = $withdrawal_payment_method;

        if (!$withdrawRequestId = $userObj->addWithdrawalRequest(array_merge($post, array("ub_user_id" => $userId)), $this->siteLangId)) {
            $message = Labels::getLabel($userObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $this->saveWithdrawalSpecifics($withdrawRequestId, $post, array_keys(self::reqFields()));

        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendWithdrawRequestNotification($withdrawRequestId, $this->siteLangId, "A")) {
            $message = Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        //send notification to admin
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_WITHDRAWAL_REQUEST,
            'notification_record_id' => $withdrawRequestId,
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::WITHDRAWL_REQUEST_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $this->set('msg', Labels::getLabel('MSG_Withdraw_request_placed_successfully', $this->siteLangId));

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function callback()
    {
        $post = file_get_contents('php://input');
        if (empty($post)) {
            $message = Labels::getLabel('LBL_INVALID_REQUEST', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        $webhookData = json_decode($post, true);
        $event_type = $webhookData['event_type'];
        $requestData = $webhookData['resource'];
        $senderBatchIdArr = explode('_', $requestData['sender_batch_id']);
        $recordId = end($senderBatchIdArr);
        $recordId = FatUtility::int($recordId);

        $txnStatus = '';
        switch ($event_type) {
            case "PAYMENT.PAYOUTS-ITEM.SUCCEEDED":
                $withdrawStatus = Transactions::WITHDRAWL_STATUS_COMPLETED;
                $txnStatus = Transactions::STATUS_COMPLETED;
                break;

            case "PAYMENT.PAYOUTS-ITEM.CANCELED":
            case "PAYMENT.PAYOUTS-ITEM.DENIED":
                $withdrawStatus = Transactions::WITHDRAWL_STATUS_DECLINED;
                $txnStatus = Transactions::STATUS_DECLINED;
                break;

            case "PAYMENT.PAYOUTS-ITEM.FAILED":
                $withdrawStatus = Transactions::WITHDRAWL_STATUS_PAYOUT_FAILED;
                $txnStatus = Transactions::STATUS_DECLINED;
                break;
            case "PAYMENT.PAYOUTS-ITEM.UNCLAIMED":
                $withdrawStatus = Transactions::WITHDRAWL_STATUS_PAYOUT_UNCLAIMED;
                $txnStatus = Transactions::STATUS_COMPLETED;
                break;
        }
        $this->updateWithdrawalRequest($recordId, $post, $withdrawStatus, $txnStatus);
    }
}
