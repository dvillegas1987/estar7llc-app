<?php

class SmsNotificationController extends PluginBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function callback($keyName)
    {
        $error = '';
        if (false === PluginHelper::includePlugin($keyName, 'sms-notification', $error, $this->siteLangId)) {
            FatUtility::dieJsonError($error);
        }
        $smsNotification = new $keyName($this->siteLangId);
        if (false === $smsNotification->callback()) {
            FatUtility::dieJsonError($smsNotification->getError());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_SUCCESS', $this->siteLangId));
    }
}
