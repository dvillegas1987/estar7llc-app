<?php

class StripeSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $langId), 'privateKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Publishable_Key', $langId), 'publishableKey');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
