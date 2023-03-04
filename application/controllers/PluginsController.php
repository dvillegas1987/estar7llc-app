<?php

class PluginsController extends LoggedUserController
{
    use PluginHelper;

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function getKeys()
    {
        $this->keyName = FatApp::getPostedData('plugin_code', FatUtility::VAR_STRING, '');
        if (empty($this->keyName)) {
            $msg = Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId);
            FatUtility::dieJsonError($msg);
        }

        $data = self::getSettings();
        $this->set('data', ['pluginDetail' => (object)$data]);
        $this->_template->render();
    }
}
