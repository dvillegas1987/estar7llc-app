<?php

class PayuIndiaSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Key', $langId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Salt', $langId), 'salt');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
