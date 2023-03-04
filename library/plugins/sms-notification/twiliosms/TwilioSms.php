<?php

use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

class TwilioSms extends SmsNotificationBase
{
    public const KEY_NAME = __CLASS__;
    
    public $langId = 0;
    
    public $requiredKeys = [
        'account_sid',
        'auth_token',
        'sender_id'
    ];

    public function __construct($langId)
    {
        $this->langId = FatUtility::int($langId);
        if (1 > $this->langId) {
            $this->langId = CommonHelper::getLangId();
        }
    }
    
    public function send($to, $body)
    {
        if (false == $this->validateSettings($this->langId)) {
            return [
                'status' => false,
                'msg' => $this->error
            ];
        }

        if (empty($to) || empty($body)) {
            return [
                'status' => false,
                'msg' => Labels::getLabel('LBL_INVALID_REQUEST', $this->langId)
            ];
        }

        try {
            $twilio = new Client($this->settings['account_sid'], $this->settings['auth_token']);
            $response = $twilio->messages->create(
                $to,
                [
                    "body" => $body,
                    "from" => $this->settings['sender_id'],
                    "statusCallback" => UrlHelper::generateFullUrl('SmsNotification', 'callback', [static::KEY_NAME])
                ]
            );
        } catch ( RestException $e ) {
            return [
                'status' => false,
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'msg' => $e->getMessage()
            ];
        }
        
        return [
            'status' => true,
            'msg' => Labels::getLabel("MSG_SUCCESS", $this->langId),
            'response_id' => $response->sid,
            'data' => $response
        ];
    }

    public function callback()
    {
        $data = FatApp::getPostedData();
        if (empty($data) || !array_key_exists('MessageSid', $data)) {
            $this->error = Labels::getLabel('LBL_INVALID_REQUEST', $this->langId);
            return false;
        }
        return SmsArchive::updateStatus($this->langId, $data['MessageSid'], $data['MessageStatus'], $data);
    }
}
