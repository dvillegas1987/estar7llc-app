<?php

class Twocheckout extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;
    public const PRODUCTION_URL = 'https://www.2checkout.com';

    public $requiredKeys = [
        'sellerId',
        'publishableKey',
        'privateKey',
        'hashSecretWord',
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
