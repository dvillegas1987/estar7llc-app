<?php

class TransferBankSettingsController extends PaymentMethodSettingsController
{
    /* public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addTextArea(Labels::getLabel('LBL_Bank_Details', $langId), 'bank_details');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    } */
    public static function getConfigurationKeys()
    {
        return [
                'business_name' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Business Name",
                ],
                'bank_name' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Bank Name",
                ],
                'bank_branch' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Bank Branch",
                ],
                'account_number' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Account #",
                ],
                'ifsc' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "IFSC / MICR",
                ],
                'routing' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => false,
                    'label' => "Routing #",
                ],
                'bank_notes' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => false,
                    'label' => "Other Notes",
                ],
            ];
    }
}
