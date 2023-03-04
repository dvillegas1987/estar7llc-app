<?php

class BraintreeSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_MerchantId', $langId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Public_Key', $langId), 'public_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Private_Key', $langId), 'private_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
