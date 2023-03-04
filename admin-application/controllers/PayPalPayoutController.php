<?php

class PayPalPayoutController extends PayoutBaseController
{
    public const KEY_NAME = 'PayPalPayout';
    private const PRODUCTION_PAYOUT_SANDBOX_URL = 'https://api.sandbox.paypal.com/v1/payments/payouts';
    private const PRODUCTION_PAYOUT_LIVE_URL = 'https://api.paypal.com/v1/payments/payouts';
    
    private const PRODUCTION_ACCESS_TOKEN_SANDBOX_URL = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    private const PRODUCTION_ACCESS_TOKEN_LIVE_URL = 'https://api.paypal.com/v1/oauth2/token';

    private const COMMISSION = [
        'AUD' => 0.30,
        'NZD' => 0.45,
        'BRL' => 0.60,
        'NOK' => 2.80,
        'CAD' => 0.30,
        'PHP' => 15.00,
        'CZK' => 10.00,
        'PLN' => 1.35,
        'DKK' => 2.60,
        'RUB' => 10,
        'EUR' => 0.35,
        'SGD' => 0.50,
        'HKD' => 2.35,
        'SEK' => 3.25,
        'HUF' => 90,
        'CHF' => 0.55,
        'ILS' => 1.20,
        'TWD' => 10.00,
        'JPY' => 40,
        'THB' => 11.00,
        'MYR' => 2.00,
        'GBP' => 0.20,
        'MXN' => 4.00,
        'USD' => 0.30
    ];

    public $requiredKeys = [
        'client_id',
        'client_secret',
    ];

    public function __construct($action)
    {
        parent::__construct($action);
        if (false == $this->validateSettings($this->adminLangId)) {
            LibHelper::dieJsonError($this->error);
        }
    }

    private function getAccessTokenUrl()
    {
        return  (false === $this->envoirment) ? static::PRODUCTION_ACCESS_TOKEN_SANDBOX_URL : static::PRODUCTION_ACCESS_TOKEN_LIVE_URL;
    }

    private function getPayoutUrl()
    {
        return  (false === $this->envoirment) ? static::PRODUCTION_PAYOUT_SANDBOX_URL : static::PRODUCTION_PAYOUT_LIVE_URL;
    }

    private function getAccessToken()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getAccessTokenUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERPWD, $this->settings['client_id'] . ':' . $this->settings['client_secret']);
        
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: en_US';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $message = 'Error:' . curl_error($ch);
            LibHelper::dieJsonError($message);
        }
        curl_close($ch);
        
        if (!$response) {
            $message = Labels::getLabel('LBL_INVALID_RESPONSE', CommonHelper::getLangId());
            LibHelper::dieJsonError($message);
        }
        $response = json_decode($response, true);
        if (!array_key_exists('access_token', $response)) {
            $message = $response['error'] . ' : ' . $response['error_description'];
            LibHelper::dieJsonError($message);
        }
        return $response['access_token'];
    }

    private function formatData($requestId, $amount, $receiverAddress, $type)
    {
        $sender_batch_id = "Payout_" . strtotime(date('Ymd')) . '_' . $requestId;
        $currencyId = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyCode = strtoupper(Currency::getAttributesById($currencyId, 'currency_code'));
        $amount = FatUtility::float($amount);
        $amount = sprintf('%0.2f', $amount - static::COMMISSION[$currencyCode]);
        
        return [
            "sender_batch_header" => [
                "sender_batch_id" => $sender_batch_id,
                "email_subject" => Labels::getLabel('LBL_YOU_HAVE_A_PAYOUT!!', CommonHelper::getLangId()),
                "email_message" => Labels::getLabel('LBL_YOU_HAVE_A_RECEIVED_A_PAYOUT', CommonHelper::getLangId())
            ],
            "items" => [
                [
                    "recipient_type" => $type,
                    "amount" => [
                        "value" => $amount,
                        "currency" => $currencyCode
                    ],
                    "note" => CommonHelper::replaceStringData(Labels::getLabel('LBL_TRANSACTION_FEE_CHARGED_:_{COMMISSION}', CommonHelper::getLangId()), ['{COMMISSION}' => static::COMMISSION[$currencyCode]]),
                    "sender_item_id" => strtotime(date('Ymd')) . '_' . $requestId,
                    "receiver" => $receiverAddress,
                ]
            ]
        ];
    }

    public function release($requestId, $specifics = '')
    {
        if (empty($requestId) || empty($specifics) || (empty($specifics['paypal_id']) && empty($specifics['email']))) {
            $message = Labels::getLabel('LBL_INVALID_REQUEST_PARAMETERS', CommonHelper::getLangId());
            LibHelper::dieJsonError($message);
        }

        $recipientType = empty($specifics['paypal_id']) ? 'EMAIL' : 'PAYPAL_ID';
        $receiverAddress = empty($specifics['paypal_id']) ? $specifics['email'] : $specifics['paypal_id'];

        $dataToRequest = $this->formatData($requestId, $specifics['amount'], $receiverAddress, $recipientType);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getPayoutUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToRequest));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $this->getAccessToken();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $message = 'Error : ' . curl_error($ch);
            LibHelper::dieJsonError($message);
        }
        curl_close($ch);
        if (!$response) {
            $message = Labels::getLabel('LBL_INVALID_RESPONSE', CommonHelper::getLangId());
            LibHelper::dieJsonError($message);
        }
        $response = json_decode($response, true);
        
        if (!array_key_exists('batch_header', $response)) {
            if (array_key_exists('message', $response)) {
                Message::addErrorMessage($response['name'] . ' : ' . $response['message']);
            }
            if (array_key_exists('details', $response)) {
                foreach ($response['details'] as $value) {
                    Message::addErrorMessage($value['issue']);
                }
            }
            LibHelper::dieJsonError(Message::getHtml());
        }
        return [
            'status' => true,
            'data' => $response
        ];
    }
}
