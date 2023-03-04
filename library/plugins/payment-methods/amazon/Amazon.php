<?php

class Amazon extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'amazon_merchantId',
        'amazon_accessKey',
        'amazon_secretKey',
        'amazon_clientId'
    ];

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();
    }
}
