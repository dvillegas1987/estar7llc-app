<?php

class FcmPushNotificationTest extends YkPluginTest
{
    public const KEY_NAME = 'FcmPushNotification';
    public const PLUGIN_TYPE = Plugin::TYPE_PUSH_NOTIFICATION;

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        $deviceTokens = [
            'dk3NLiO2u1E:APA91bFvzOfGpl6shjmNSQYcQ5TQGpePXuHqC5KxbO0Ej8k9dezACGPdPxWxPjjPKMvWWtbc_jHe22YRCUY6TOQbsE5mG1Pw3X-NvCzDqolqNXpJU0nkSmfKsqyQwYkfF-J4xlyYxEEV',
            'fRgOw1XauQU:APA91bHUecfYKOZLAGjvLzJvKfvR8Y3gFTmHGiq9FDJS9dNWTTzN7SxK6GtuyERVgyZLbZ96879mCU3AWR5EhN21ewAVO8B6Y_xzev_mPLrOmtLsbTJ03W-yi6FeWLJOjgkBHRGqeFKH',
            'd3eOtP4gspU:APA91bFZ78Kj_cC0AR_2FvU9mhrYWYCRwIy6Pgz44Ie6vI5u_478j_7nFZuH5NKEZMucmVjAty2E5lrFYnotZHVz6AIm7UUkj9PqUjnbz4BetGmGWgqVuYFzFYquz0JWVK4560R3S2xa',
            'fDHtNBApNF0:APA91bEvSSMp6O9DZSO6Y3zNEvKgFmd07WY84Xyz7AilNIepX3gWdvbm70fNNgaDBQJMXwyI8LB5kO62Ajb1lMy-azAZboNpJ4Bfxub433lMRoOr3qUOhaJjslVImuKSvamyNFjqC6Zh'
        ];
        $this->classObj->setDeviceTokens($deviceTokens);
    }

    /**
     * @test
     *
     * @dataProvider feedNotify
     * @param  int $expected
     * @param  mixed $title
     * @param  mixed $message
     * @param  mixed $os
     * @param  mixed $data
     * @param  mixed $responseStatus
     * @return void
     */
    public function notify($expected, $title, $message, $os, $data)
    {
        $this->expectedReturnType(static::TYPE_ARRAY);
        $response = $this->execute(self::KEY_NAME, [SYSTEM_LANG_ID], 'notify', [$title, $message, $os, $data]);
        
        $status = 0;
        if (!empty($response)) {
            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('msg', $response);
            $this->assertArrayHasKey('data', $response);
            $status = $response['status'];
        }
        $this->assertEquals($expected, $status);
    }

    /**
     * feedNotify
     *
     * @return array
     */
    public function feedNotify()
    {
        // Returned false in case of invalid or missing Plugin Keys. Fail in case of opposite expectation.
        return [
            [0, '', '', 0, []], // Return 0 in case of all input empty and Invalid.
            [1, 'Title3', 'Message3', 0, []], // Return 1 Either Invalid Device token or OS is 0 but function run successfully. Because It will tell number of successfully sent and number of failure.
            [1, 'Title4', 'Message4', 1, []], // Return 1 Either Invalid Device token but function run successfully. Because It will tell number of successfully sent and number of failure.
            [1, 'Title5', 'Message5', 1, ['test' => 'body']], // Return 1 Either Invalid Device token but function run successfully. Because It will tell number of successfully sent and number of failure.
            [0, 123, 'Message5', 1, ['test' => 'body']], // Invalid value type. Return 0
            [0, 'Title6', 123, 1, ['test' => 'body']], // Invalid value type. Return 0
            [0, 'Title7', 'Message5', 'ANDROID', ['test' => 'body']], // Invalid value type. Return 0
            [0, 'Title8', 'Message5', 1, 1], // Invalid value type. Return 0
            [0, 'Title8', 'Message5', 1, 'avs'], // Invalid value type. Return 0
            [0, 123, 123, 'IOS', 1], // Invalid value type. Return 0
        ];
    }
}