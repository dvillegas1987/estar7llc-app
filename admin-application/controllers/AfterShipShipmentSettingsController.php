<?php

class AfterShipShipmentSettingsController  extends ShipmentSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
            'api_key' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "Api Key",
            ]
        ];
    }
}
