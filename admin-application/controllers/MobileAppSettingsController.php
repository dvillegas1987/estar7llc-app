<?php

class MobileAppSettingsController extends AdminBaseController
{
    private const APP_IMAGES = [
        AttachedFile::FILETYPE_APP_MAIN_SCREEN_IMAGE,
        AttachedFile::FILETYPE_APP_LOGO,
    ];
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
    }

    private function getAppThemeForm()
    {
        $frm = new Form('frmAppThemeSettings');
        $frm->addRequiredField(Labels::getLabel('LBL_PRIMARY_THEME_COLOR', $this->adminLangId), 'CONF_PRIMARY_APP_THEME_COLOR')->addFieldTagAttribute('class', 'jscolor');
        $frm->addRequiredField(Labels::getLabel('LBL_PRIMARY_INVERSE_THEME_COLOR', $this->adminLangId), 'CONF_PRIMARY_INVERSE_APP_THEME_COLOR')->addFieldTagAttribute('class', 'jscolor');
        $frm->addRequiredField(Labels::getLabel('LBL_SECONDARY_THEME_COLOR', $this->adminLangId), 'CONF_SECONDARY_APP_THEME_COLOR')->addFieldTagAttribute('class', 'jscolor');
        $frm->addRequiredField(Labels::getLabel('LBL_SECONDARY_INVERSE_THEME_COLOR', $this->adminLangId), 'CONF_SECONDARY_INVERSE_APP_THEME_COLOR')->addFieldTagAttribute('class', 'jscolor');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save", $this->adminLangId));
        return $frm;
    }

    public function appTheme()
    {
        $this->objPrivilege->canViewAppThemeSettings();

        $record = Configurations::getConfigurations();

        $frm = $this->getAppThemeForm();
        $frm->fill($record);

        $this->set('frm', $frm);
        $this->_template->addJs('js/jscolor.js');
        $this->_template->render();
    }

    public function setupAppTheme()
    {
        $this->objPrivilege->canEditAppThemeSettings();

        $post = FatApp::getPostedData();

        $frm = $this->getAppThemeForm();
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['btn_submit']);

        $record = new Configurations();
        if (!$record->update($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
