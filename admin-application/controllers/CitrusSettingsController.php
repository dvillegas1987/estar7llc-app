<?php

class CitrusSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Vanity_Url', $langId), 'merchant_vanity_url');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Key', $langId), 'merchant_access_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $langId), 'merchant_secret_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
