<?php

class SellerPluginBaseController extends SellerBaseController
{
    use PluginHelper;

    public function __construct($action)
    {
        parent::__construct($action);
    }
}
