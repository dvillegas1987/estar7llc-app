<?php

/*
 *    Reference : https://data.fixer.io
 *    Please note: The default base currency is EUR. It can be changed, depend upon your subscription plan.
 */

use Curl\Curl;

class FixerCurrencyConverter extends CurrencyConverterBase
{
    public const KEY_NAME = __CLASS__;
    private const PRODUCTION_URL = 'http://data.fixer.io/api/';
    
    public $requiredKeys = ['access_key'];

    private $actionUri = '';
    private $response = '';
    private $toCurrencies = [];
    
    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = FatUtility::int($langId);
        if (1 > $this->langId) {
            $this->langId = CommonHelper::getLangId();
        }
    }
    
    /**
     * init
     *
     * @return void
     */
    public function init(): bool
    {
        if (false == $this->validateSettings($this->langId)) {
            return false;
        }

        if (false === $this->loadBaseCurrency()) {
            return false;
        }
        
        return true;
    }
            
    /**
     * initiateUri
     *
     * @param  string $action
     * @return bool
     */
    private function initiateUri(string $action = ''): bool
    {
        $this->actionUri = self::PRODUCTION_URL . $action;
        return true;
    }

    /**
     * getAction
     *
     * @return string
     */
    private function getActionUri(): string
    {
        return $this->actionUri;
    }
    
    /**
     * bindAccessKey
     *
     * @return bool
     */
    private function bindAccessKey(): bool
    {
        $this->actionUri = $this->getActionUri() . '?access_key=' . $this->settings['access_key'];
        return true;
    }

    /**
     * bindBaseCurrency
     *
     * @return bool
     */
    private function bindBaseCurrency(): bool
    {
        $this->actionUri = $this->getActionUri() . '&base=' . $this->getBaseCurrencyCode();
        return true;
    }
    
    /**
     * bindQueryString
     *
     * @param  string $queryString
     * @return bool
     */
    private function bindQueryString(string $queryString): bool
    {
        $this->actionUri = $this->getActionUri() . $queryString;
        return true;
    }

    /**
     * getResponse
     *
     * @return object
     */
    private function getResponse(): object
    {
        return $this->response;
    }


    /**
     * convert
     *
     * @return bool
     */
    private function convert(): bool
    {
        $this->initiateUri('latest');
        $this->bindAccessKey();
        $this->bindBaseCurrency();

        $queryString = empty($this->toCurrencies) ? '' : '&symbols=' . implode(',', $this->toCurrencies);
        $this->bindQueryString($queryString);

        $curl = new Curl();
        $curl->get($this->getActionUri());

        if ($curl->error) {
            $this->error = $curl->errorCode . ' : ' . $curl->errorMessage;
            return false;
        }

        $this->response = $curl->getResponse();
        return true;
    }
    
    /**
     * getRates
     *
     * @param  array $toCurrencies - To which you want to convert
     * @return array
     */
    public function getRates(array $toCurrencies = []): array
    {
        if (false === $this->init()) {
            return [
                'status' => Plugin::RETURN_FALSE,
                'msg' => $this->getError(),
                'data' => []
            ];
        }

        $this->toCurrencies = is_array($toCurrencies) ? array_filter($toCurrencies) : [];
        if (false === $this->convert()) {
            return [
                'status' => Plugin::RETURN_FALSE,
                'msg' => $this->getError(),
                'data' => []
            ];
        }
        $data = $this->getResponse();

        $status = Plugin::RETURN_TRUE;
        $msg = Labels::getLabel("MSG_SUCCESS", $this->langId);

        if (!empty($data->error)) {
            $status = Plugin::RETURN_FALSE;
            $msg = Labels::getLabel("MSG_ERROR_CODE_{error-code}_:_{message}", $this->langId);
            $msg = CommonHelper::replaceStringData($msg, ['{error-code}' => $data->error->code, '{message}' => $data->error->type]);
        }

        return [
            'status' => $status,
            'msg' => $msg,
            'data' => isset($data->rates) ? (array) $data->rates : []
        ];
    }
}
