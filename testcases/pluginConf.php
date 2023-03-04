<?php
$keys = [
    'CurrencyConverter' => [
        'api_key' => 'c22be3bbee2ffc600da0'
    ],
    'FacebookLogin' => [
        'app_id' => '300972908012491',
        'app_secret' => '772448424077daa677ff01903746a2e4'
    ],
    'FcmPushNotification' => [
        'server_api_key' => 'AAAAld-BZYQ:APA91bEwdNyqPBYqiuXFAY_kYZRqju5wuiduZiuUx1RwcTasWLz__uiHUMnsKV95CQVi_BJVnX062LOdUWCd1-gwYDdA2139jNXPccLIckl5cH2ANeJyufAoS-UJGIMjZtbRAW0fAyk1'
    ],
    'FixerCurrencyConverter' => [
        'access_key' => 'a95a5e7415cb80554448f926ca7f68d8'
    ],
    'GoogleLogin' => [
        'client_id' => '293307261869-g6ns17slnsutjf8smv6v0210mbqnajl2.apps.googleusercontent.com',
        'client_secret' => 'Q7RN2uPn0jY7QPzsK2WB1iUL',
        'developer_key' => 'AIzaSyAYRPS5jwNbMHoowNhNvna5b_bdYffcwdE',
    ],
    'GoogleShoppingFeed' => [
        'client_id' => '989922044446-f9uj7vt2uir3amtmv7ieufqqt98k8llg.apps.googleusercontent.com',
        'client_secret' => 'Eug6sn8yKkd4iAZHTtCkaZ6p',
        'developer_key' => 'AIzaSyAQeP-6U2NVbQODmAbEStE_yLEg49Ew20E',
    ],
    'InstagramLogin' => [
        'client_id' => '2614237385501383',
        'client_secret' => '542c48a187e0828d07690b43fa9e3a43',
    ],
    'Mpesa' => [
        'env' => PLUGIN::ENV_SANDBOX,
        'consumer_key' => '1ay0T0g8uZ6eVrzwocZB4c945gcmYz9m',
        'consumer_secret' => 'EBD7NmLBwF5LOvTA',
        'account_reference' => 'YOKART2020',
        'shortcode' => '174379',
        'passkey' => 'AAAAld-BZYQ:APA91bEwdNyqPBYqiuXFAY_kYZRqju5wuiduZiuUx1RwcTasWLz__uiHUMnsKV95CQVi_BJVnX062LOdUWCd1-gwYDdA2139jNXPccLIckl5cH2ANeJyufAoS-UJGIMjZtbRAW0fAyk1'
    ],
    'AppleLogin' => [
        'client_id' => 'com.fatbit.YoKartMarketplaceLogin'
    ],
    'AfterShipShipment' => [
        'api_key' => 'a603e21c-339c-4496-9c19-7da8d8457ab7'
    ],
    'ShipStationShipping' => [
        'api_key' => '366da0dfeea246d0926798bc10ac60c8',
        'api_secret_key' => '60e27c9440d44c92a9387f7cdfebb773'
    ],
];

$keys = array_key_exists($class, $keys) ? $keys[$class] : [];
$keys = array_merge(['plugin_active' => 1], $keys);
$this->classObj->settings = $keys;