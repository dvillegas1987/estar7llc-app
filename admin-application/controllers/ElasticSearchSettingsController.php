<?php
class ElasticSearchSettingsController extends FullTextSearchSettingsController
{
    public static function getConfigurationKeys()
    {
        return [
                'host' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => true,
                    'label' => "Elastic Search host Url",
                ],
                'username' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => false,
                    'label' => "Elastic Search username",
                ],
                'password' => [
                    'type' => PluginSetting::TYPE_STRING,
                    'required' => false,
                    'label' => "Elastic Search password",
                ]
            ];
    }
}
