<?php

class TwilioSmsSettingsController extends SmsNotificationSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
            'account_sid' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "Account Sid",
            ],
            'auth_token' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "Auth Token",
            ],
            'sender_id' => [
                'type' => PluginSetting::TYPE_STRING,
                'required' => true,
                'label' => "Sender Id",
            ]
        ];
    }
}
