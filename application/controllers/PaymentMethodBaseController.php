<?php

class PaymentMethodBaseController extends SellerPluginBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);

        $class = get_called_class();
        if (!defined($class . '::KEY_NAME')) {
            $msg = Labels::getLabel('MSG_INVALID_PLUGIN', $this->siteLangId);
            LibHelper::exitWithError($msg, false, true);
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }
        $this->keyName = $class::KEY_NAME;
        if (false === Plugin::isActive($this->keyName)) {
            $msg = Labels::getLabel('MSG_NO_PAYMENT_METHOD_PLUGIN_ACTIVE', $this->siteLangId);
            LibHelper::exitWithError($msg, false, true);
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }
    }
    
    protected function getPluginData($attr = '')
    {
        $attr = empty($attr) ? Plugin::ATTRS : $attr;
        return Plugin::getAttributesByCode($this->keyName, $attr, $this->siteLangId);
    }

    protected function getUserMeta($key = '')
    {
        return User::getUserMeta(UserAuthentication::getLoggedUserId(), $key);
    }
}
