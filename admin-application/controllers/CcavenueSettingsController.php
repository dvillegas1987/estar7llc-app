<?php

class CcavenueSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_ID', $langId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Code', $langId), 'access_code');
        $frm->addTextBox(Labels::getLabel('LBL_Working_Key', $langId), 'working_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
