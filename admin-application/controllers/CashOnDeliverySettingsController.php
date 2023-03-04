<?php

class CashOnDeliverySettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmCashOnDelivery');

        $yesNoArr = applicationConstants::getYesNoArr($langId);
        $otpVerFld = $frm->addSelectBox(Labels::getLabel('LBL_OTP_VERIFICATION', $langId), 'otp_verification', array_reverse($yesNoArr), '', ['class' => 'fieldsVisibility-js'], '');
        $otpVerFld->requirement->setRequired(true);
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
