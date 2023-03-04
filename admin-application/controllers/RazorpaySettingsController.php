<?php

class RazorpaySettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Key_Id', $langId), 'merchant_key_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Key_Secret', $langId), 'merchant_key_secret');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
