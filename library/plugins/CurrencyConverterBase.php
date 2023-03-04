<?php

class CurrencyConverterBase extends PluginBase
{
    public $systemCurrencyCode = '';

    /**
     * loadBaseCurrency
     *
     * @return bool
     */
    protected function loadBaseCurrency(): bool
    {
        if (!empty($this->systemCurrencyCode)) {
            return true;
        }

        $currency = Currency::getDefault();
        if (empty($currency)) {
            $this->error = Labels::getLabel('MSG_DEFAULT_CURRENCY_NOT_SET', $this->langId);
            return false;
        }
        $this->systemCurrencyCode = strtoupper($currency['currency_code']);
        return true;
    }
    
    /**
     * getBaseCurrencyCode
     *
     * @return string
     */
    protected function getBaseCurrencyCode(): string
    {
        return $this->systemCurrencyCode;
    }
}
