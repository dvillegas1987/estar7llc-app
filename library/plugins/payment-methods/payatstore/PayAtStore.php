<?php

class PayAtStore extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

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
