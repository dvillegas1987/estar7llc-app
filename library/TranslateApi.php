<?php
/* 
    Microsoft Translator Text API 3.0
*/
 
class TranslateApi
{
    private $subscriptionKey;
    private $host;
    private $translatePath;
    private $fromLang;
    private $error;

    public function __construct($fromLang)
    {
        $this->subscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        if (empty($this->subscriptionKey)) {
            $this->error = Labels::getLabel('MSG_YOU_HAVE_NOT_ENTERED_A_VALID_SUBSCRIPTION_KEY', CommonHelper::getLangId());
            return false;
        }
        if (empty($fromLang)) {
            $this->error = Labels::getLabel('MSG_INVALID_SOURCE_LANGUAGE', CommonHelper::getLangId());
            return false;
        }
        $this->host = 'https://api.cognitive.microsofttranslator.com';
        $this->translatePath = '/translate?api-version=3.0';
        $this->fromLang = $fromLang;
    }

    public function translateData($to, $requestBody)
    {
        if (empty($to) || empty($requestBody)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST_TYPE', CommonHelper::getLangId());
            return false;
        }

        $content = LibHelper::convertToJson($requestBody, JSON_UNESCAPED_UNICODE);

        $curl_headers = array(
            'Content-type: application/json',
            'Content-length: ' . strlen($content) ,
            'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey ,
            'X-ClientTraceId: ' . $this->comCreateGuid()
        );
        $url = $this->host . $this->translatePath;

        //Language Translate From, Translate To
        $url .= $to . "&from=" . $this->fromLang . "&textType=html";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = str_replace('\"', '', htmlspecialchars_decode($result));
        return json_decode($result, true);
    }

    private function comCreateGuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public function getError()
    {
        return $this->error;
    }
}
