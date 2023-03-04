<?php

class InstagramLoginSettingsController extends SocialLoginSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
                'client_id' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Client Id",
                ],
                'client_secret' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Client Secret",
                ],
            ];
    }
}
