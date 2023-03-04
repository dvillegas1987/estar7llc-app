<?php

class AdvertisementFeedBaseController extends SellerPluginBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);

        $class = get_called_class();
        if (!defined($class . '::KEY_NAME')) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_PLUGIN', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }
        $this->keyName = $class::KEY_NAME;
    }

    protected function redirectBack(string $controller = '', string $action = '')
    {
        $controller = empty($controller) ? $this->keyName : $controller;
        FatApp::redirectUser(UrlHelper::generateUrl($controller, $action));
    }

    protected function updateMerchantInfo($detail = [], $redirect = true)
    {
        if (!is_array($detail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $obj = new User(UserAuthentication::getLoggedUserId());
        foreach ($detail as $key => $value) {
            if (false === $obj->updateUserMeta($key, $value)) {
                Message::addErrorMessage($obj->getError());
                if (true === $redirect) {
                    $this->redirectBack();
                }
            }
        }
        Message::addMessage(Labels::getLabel("MSG_SUCCESSFULLY_UPDATED", $this->siteLangId));
        if (false === $redirect) {
            FatUtility::dieJsonSuccess(Message::getHtml());
        }
        $this->redirectBack();
    }

    protected function getUserMeta($key = '')
    {
        return User::getUserMeta(UserAuthentication::getLoggedUserId(), $key);
    }
}
