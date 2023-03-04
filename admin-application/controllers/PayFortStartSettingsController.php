<?php

class PayFortStartSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Transaction_Mode', $langId), 'transaction_mode', array(0 => "Test/Sandbox", "1" => "Live"), 'transaction_mode', [], Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();
        $frm->addRequiredField(Labels::getLabel('LBL_API_Secret_Key', $langId), 'secret_key');
        $frm->addRequiredField(Labels::getLabel('LBL_API_Open_Key', $langId), 'open_key');

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
