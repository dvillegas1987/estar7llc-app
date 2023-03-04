<?php

class AppleLoginSettingsController extends SocialLoginSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
                'client_id' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Client ID / Service ID",
                ]
            ];
    }
}
