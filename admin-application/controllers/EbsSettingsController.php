<?php

class EbsSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Account_ID', $langId), 'accountId');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $langId), 'secretKey');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
