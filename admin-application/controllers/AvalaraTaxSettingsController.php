<?php

class AvalaraTaxSettingsController extends TaxSettingsController
{

    public static function getConfigurationKeys()
    {
        return [
            'account_number' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "Account Number",
            ],
            'license_key' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => false,
                'label' => "License Key",
            ],
            'company_code' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => false,
                'label' => "Company Code",
            ],
            'commit_transaction' => [
                'type' => PluginSetting::TYPE_BOOL,
                'required' => true,
                'label' => "Commit Transaction",
            ],
            'environment' => [
                'type' => PluginSetting::TYPE_BOOL,
                'required' => true,
                'label' => "Production Mode",
            ]
        ];
    }
}
