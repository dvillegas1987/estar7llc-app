<?php

class CurrencyConverterBaseController extends PluginSettingController
{
    protected $baseCurrencyId = '';

    public function __construct($action)
    {
        parent::__construct($action);
        $this->baseCurrencyId = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
    }

    protected function getAllCurrencies($exceptDefault = false)
    {
        $currencies = Currency::getCurrencyAssoc($this->adminLangId);
        if (true === $exceptDefault) {
            unset($currencies[$this->baseCurrencyId]);
        }
        return $currencies;
    }

    public function update()
    {
        $defaultConverter = get_called_class();
        if (__CLASS__ === $defaultConverter) {
            $msg = Labels::getLabel('MSG_INVALID_ACCESS', $this->adminLangId);
            LibHelper::dieJsonError($msg);
        }
        
        $currencies = $this->getAllCurrencies(true);
        $obj = new $defaultConverter(__FUNCTION__);
        $currenciesData = $obj->getRates($currencies);
        if (empty($currenciesData) || false === $currenciesData['status'] || !isset($currenciesData['data']) || empty($currenciesData['data'])) {
            $msg = !empty($currenciesData['msg']) ? $currenciesData['msg'] : Labels::getLabel('MSG_UNABLE_TO_UPDATE', $this->adminLangId);
            LibHelper::dieJsonError($msg);
        }

        $currObj = new Currency();
        if (false === $currObj->updatePricingRates($currenciesData['data'])) {
            LibHelper::dieJsonError($currObj->getError());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_UPDATED_SUCCESSFULLY', $this->adminLangId));
    }
}
