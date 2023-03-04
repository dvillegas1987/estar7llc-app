<?php

class ShipStationShippingSettingsController extends ShippingServicesSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
            'environment' => [
                'type' => PluginSetting::TYPE_BOOL,
                'required' => true,
                'label' => "Production Mode",
            ],
            'api_key' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "API Key",
            ],
            'api_secret_key' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "API Secret Key",
            ]
        ];
    }
}
