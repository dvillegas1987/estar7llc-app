<?php

class AuthorizeAimSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Login_ID', $langId), 'login_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Transaction_Key', $langId), 'transaction_key');
        // $frm->addTextBox(Labels::getLabel('LBL_MD5_Hash', $langId), 'md5_hash');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
