<?php

/*
 *  Reference : https://www.currencyconverterapi.com
 *  Note : Maximum of 2 is supported for this free version.
 */

use Curl\Curl;

class CurrencyConverter extends CurrencyConverterBase
{
    public const KEY_NAME = __CLASS__;
    private const PRODUCTION_URL = 'https://free.currconv.com/api/v7/';
    
    public $requiredKeys = ['api_key'];

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
     * getAction
     *
     * @return string
     */
    private function getActionUri(): string
    {
        return $this->actionUri;
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
     * bindApiKey
     *
     * @return bool
     */
    private function bindApiKey(): bool
    {
        $this->actionUri = $this->getActionUri() . '?apiKey=' . $this->settings['api_key'];
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
        $this->initiateUri('convert');
        $this->bindApiKey();

        $toCurrenciesQuery = empty($this->toCurrencies) ? "" : $this->getBaseCurrencyCode() . '_' . implode(',' . $this->getBaseCurrencyCode() . '_', $this->toCurrencies);

        $queryString = empty($this->toCurrencies) ? '' : '&compact=ultra&q=' . $toCurrenciesQuery;
        $this->bindQueryString($queryString);

        $curl = new Curl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->get($this->getActionUri());
        
        if ($curl->error) {
            $this->error = $curl->errorCode . ' : ' . $curl->errorMessage;
            $this->error .= !empty($curl->getResponse()->error) ? $curl->getResponse()->error : '';
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
        $response = $this->getResponse();

        $status = Plugin::RETURN_TRUE;
        $msg = Labels::getLabel("MSG_SUCCESS", $this->langId);

        if (!empty($response->error)) {
            $status = Plugin::RETURN_FALSE;
            $msg = $response->error;
        }

        $data = [];
        foreach ($response as $key => $rate) {
            $data[str_replace($this->getBaseCurrencyCode() . '_', '', $key)] = $rate;
        }

        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        ];
    }
}
