<?php

class AmazonSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Id', $langId), 'amazon_merchantId');
        $frm->addRequiredField(Labels::getLabel('LBL_Access_Key', $langId), 'amazon_accessKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $langId), 'amazon_secretKey');
        $frm->addRequiredField(Labels::getLabel('LBL_Client_Id', $langId), 'amazon_clientId');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
