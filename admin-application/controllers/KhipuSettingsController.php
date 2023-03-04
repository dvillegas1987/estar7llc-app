<?php

class KhipuSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Receiver_Id', $langId), 'receiver_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $langId), 'secret_key');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
