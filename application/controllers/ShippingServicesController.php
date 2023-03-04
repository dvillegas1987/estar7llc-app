<?php

class ShippingServicesController extends SellerBaseController
{    
    use ShippingServices;

    /**
     * __construct
     *
     * @param  string $action
     * @return void
     */
    public function __construct($action)
    {
        parent::__construct($action);
        $this->langId = $this->siteLangId;
        $this->init();
    }
}
