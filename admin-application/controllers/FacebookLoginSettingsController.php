<?php

class FacebookLoginSettingsController extends SocialLoginSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
                'app_id' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "App Id",
                ],
                'app_secret' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "App Secret",
                ],
            ];
    }
}
