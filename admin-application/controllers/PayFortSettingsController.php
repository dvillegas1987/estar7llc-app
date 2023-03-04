<?php

class PayFortSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Identifier', $langId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Code', $langId), 'access_code');
        $frm->addSelectBox(Labels::getLabel('LBL_SHA_Type', $langId), 'sha_type', array( 'sha128' => 'SHA-128', 'sha256' => 'SHA-256', 'sha512' => 'SHA-512' ), 'sha512', [], Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();
        $frm->addRequiredField(Labels::getLabel('LBL_SHA_Request_Phrase', $langId), 'sha_request_phrase');
        $frm->addRequiredField(Labels::getLabel('LBL_SHA_Response_Phrase', $langId), 'sha_response_phrase');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
