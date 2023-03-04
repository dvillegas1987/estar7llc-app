<?php

class FcmPushNotification extends PushNotificationBase
{
    public const KEY_NAME = __CLASS__;
    private const PRODUCTION_URL = 'https://fcm.googleapis.com/fcm/send';
    public const LIMIT = 1000;

    private $deviceTokens;

    public $requiredKeys = ['server_api_key'];

    /**
     * __construct
     *
     * @param int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = $langId;
    }
        
    /**
     * formatOutput
     *
     * @param  int $status
     * @param  string $msg
     * @param  array $data
     * @return array
     */
    private function formatOutput(int $status, string $msg, array $data = [])
    {
        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
        ];        
    }
    
    /**
     * setDeviceTokens
     *
     * @param  array $deviceTokens
     * @return void
     */
    public function setDeviceTokens(array $deviceTokens): void
    {
        $this->deviceTokens = $deviceTokens;
    }
    
    /**
     * notify
     *
     * @param  string $title
     * @param  string $message
     * @param  int $os
     * @param  array $data
     * @return array
     */
    public function notify(string $title, string $message, int $os, array $data = []): array
    {
        if (false === $this->validateSettings($this->langId)) {
            return $this->formatOutput(Plugin::RETURN_FALSE, $this->error);
        }

        if (empty($this->deviceTokens) || 1000 < count($this->deviceTokens)) {
            $this->error = Labels::getLabel('LBL_ARRAY_MUST_CONTAIN_AT_LEAST_1_AND_AT_MOST_1000_REGISTRATION_TOKENS', $this->langId);
            return $this->formatOutput(Plugin::RETURN_FALSE, $this->error);
        }
            
        if (empty($title) || empty($message)) {
            $this->error = Labels::getLabel('LBL_INVALID_REQUEST', $this->langId);
            return $this->formatOutput(Plugin::RETURN_FALSE, $this->error);
        }

        $msg = [
            'title' => $title,
            'body' => $message,
            'image' => isset($data['image']) ? $data['image'] : ''
        ];
        
        $fields = [
            'registration_ids' => $this->deviceTokens,
            'notification' => $msg,
            'data' => isset($data['customData']) ? $data['customData'] : [],
            'priority' => 'high'
        ];

        if (User::DEVICE_OS_ANDROID == $os) {
            unset($fields['notification']);
            $fields['data'] = array_merge($msg, $fields['data']);
        }
        
        $fields['data'] = empty($fields['data']) ? (object) [] : $fields['data'];

        $headers = [
            'Authorization: key=' . $this->settings['server_api_key'],
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, static::PRODUCTION_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        $msg = Labels::getLabel('MSG_SUCCESS', $this->langId);
        return $this->formatOutput(Plugin::RETURN_TRUE, $msg, $result);
    }
}
