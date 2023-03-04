<?php

class FixerCurrencyConverterSettingsController extends CurrencyApiSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
                'access_key' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Access Key",
                ]
            ];
    }
}
