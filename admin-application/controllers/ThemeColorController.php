<?php

class ThemeColorController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewThemeColor($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditThemeColor($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->_template->addJs('js/jscolor.js');
        $this->objPrivilege->canViewThemeColor();
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewThemeColor();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = ThemeColor::getSearchObject(true);
        $srch->addMultipleFields(array('theme_id , theme_name', 'theme_added_by', 'tcolor_key', 'tcolor_value'));
        $srch->addCondition('tcolor_key', '=', ThemeColor::TYPE_BRAND);
        if (!empty($post['keyword'])) {
            $srch->addCondition('theme_name', 'like', '%' . $post['keyword'] . '%');
        }
        $srch->addOrder('theme_name', 'ASC');
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function listing($themeId)
    {
        $themeId = FatUtility::int($themeId);
        if (1 > $themeId) {
            FatUtility::dieJsonError($this->str_invalid_request);
        }

        $frmSearch = $this->getSearchForm();
        $frmSearch->fill(array('theme_id' => $themeId));

        $this->set('data', $data);
        $this->set('themeId', $themeId);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function form($themeId)
    {
        $this->objPrivilege->canEditThemeColor();
        $themeId = FatUtility::int($themeId);

        $frm = $this->getForm($themeId);

        if (0 < $themeId) {
            $data = ThemeColor::getAttributesById($themeId);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $themeColors = ThemeColor::getById($themeId, $this->adminLangId, true);
            foreach ($themeColors as $tColor) {
                $data[$tColor['tcolor_key']] = $tColor['tcolor_value'];
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('theme_id', $themeId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function cloneForm($themeId = 0)
    {
        $this->objPrivilege->canEditThemeColor();
        $themeId = FatUtility::int($themeId);

        $frm = $this->getForm($themeId);

        if (0 < $themeId) {
            $data = ThemeColor::getAttributesById($themeId);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $data['theme_id'] = 0;
            $data['parent_theme_id'] = $themeId;
            $data['theme_name'] = 'Copy of ' . $data['theme_name'];

            $themeColors = ThemeColor::getById($themeId, $this->adminLangId);
            foreach ($themeColors as $tColor) {
                $data[$tColor['tcolor_key']] = $tColor['tcolor_value'];
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('theme_id', $themeId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditThemeColor();

        $frm = $this->getForm();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $themeId = $post['theme_id'];
        unset($post['theme_id']);
        $data = $post;
        $data['theme_added_by'] = $this->admin_id;
        $record = new ThemeColor($themeId);
        $record->assignValues($data);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $parentThemeId = ($post['parent_theme_id'] > 0) ? $post['parent_theme_id'] : $themeId;
        $themeColors = ThemeColor::getById($parentThemeId, $this->adminLangId);
        $newThemeId = $record->getMainTableRecordId();
        foreach ($themeColors as $tColor) {
            $dataToSave = array(
            'tcolor_theme_id' => $newThemeId,
            'tcolor_key' => $tColor['tcolor_key'],
            'tcolor_value' => isset($post[$tColor['tcolor_key']]) ? $post[$tColor['tcolor_key']] : $tColor['tcolor_value'],
            ); 
            $dataToUpdateOnDuplicate = $dataToSave;
            unset($dataToUpdateOnDuplicate['uextra_user_id']);
            if (!FatApp::getDb()->insertFromArray(ThemeColor::DB_TBL_COLORS, $dataToSave, false, array(), $dataToUpdateOnDuplicate)) {
                $message = Labels::getLabel("LBL_Details_could_not_be_saved!", $this->adminLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }

        if ($themeId > 0) {
            $this->set('msg', Labels::getLabel($this->str_update_record, $this->adminLangId));
        } else {
            $themeId = $newThemeId;
            $this->set('msg', Labels::getLabel($this->str_setup_successful, $this->adminLangId));
        }

        $this->set('themeId', $themeId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($themeId = 0)
    {
        $this->objPrivilege->canViewThemeColor();
        $themeId = FatUtility::int($themeId);
        $frm = new Form('frmThemeColor');
        $frm->addHiddenField('', 'theme_id', $themeId);
        $frm->addHiddenField('', 'parent_theme_id');

        $frm->addRequiredField(Labels::getLabel('LBL_Theme_Name', $this->adminLangId), 'theme_name');
        $themeColors = ThemeColor::getById($themeId, $this->adminLangId, true);
        $colorTypes = ThemeColor::getTypeArr($this->adminLangId);
        foreach ($themeColors as $tColor) {
            $frm->addRequiredField($colorTypes[$tColor['tcolor_key']], $tColor['tcolor_key'])->addFieldTagAttribute('class', 'jscolor');
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function activateThemeColor($themeId = 0)
    {
        $this->objPrivilege->canEditThemeColor();
        if (FatUtility::isAjaxCall()) {
            $themeId = FatApp::getPostedData('themeId', FatUtility::VAR_INT, 0);
        }

        if (0 >= $themeId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = ThemeColor::getAttributesById($themeId, array('theme_id'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $configurationObj = new Configurations();
        if (!$configurationObj->update(array('CONF_FRONT_THEME' => $themeId))) {
            Message::addErrorMessage($configurationObj->getError());
            if (FatUtility::isAjaxCall()) {
                FatUtility::dieJsonError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl('ThemeColor', ''));
            }
        }
        /* $this->updateCssFiles(); */
        if (FatUtility::isAjaxCall()) {
            $this->set('msg', Labels::getLabel('Msg_Theme_Activated_Successfully', CommonHelper::getLangId()));
            $this->_template->render(false, false, 'json-success.php');
        } else {
            Message::addMessage(Labels::getLabel('Msg_Theme_Activated_Successfully', CommonHelper::getLangId()));
            FatApp::redirectUser(UrlHelper::generateUrl('ThemeColor', ''));
        }
    }

    public function deleteTheme()
    {
        $this->objPrivilege->canEditThemeColor();
        $themeId = FatApp::getPostedData('themeId', FatUtility::VAR_INT, 0);

        if (0 >= $themeId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = ThemeColor::getAttributesById($themeId, array('theme_id'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $themeObj = new ThemeColor($themeId);
        if (!$themeObj->deleteRecord()) {
            Message::addErrorMessage($themeObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!FatApp::getDb()->deleteRecords(ThemeColor::DB_TBL_COLORS, array('smt' => 'tcolor_theme_id = ?', 'vals' => array($themeId)))) {
            Message::addErrorMessage($themeObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('Msg_Theme_Settings_Deleted_Successfully', CommonHelper::getLangId()));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function updateCssFiles()
    {
        $themeDetail = ThemeColor::getAttributesById(FatApp::getConfig('CONF_FRONT_THEME'));


        if (!$theme_detail) {
            $selected_theme = 1;
        }

        $filesArr = array(
        'common-css/1base.css' => 'css/css-templates/1base.css',
        'common-css/2nav.css' => 'css/css-templates/2nav.css',
        'common-css/3skeleton.css' => 'css/css-templates/3skeleton.css',
        'common-css/4phone.css' => 'css/css-templates/4phone.css'
        );
        $i = 1;

        foreach ($filesArr as $fileKey => $fileName) {
            $str = '';
            if (substr($fileName, '-4') != '.css') {
                continue;
            }
            $oldFile = CONF_FRONT_END_THEME_PATH . $fileName;
            if (file_exists($oldFile)) {
                $str .= file_get_contents($oldFile);
            }
            $newFileName = CONF_FRONT_END_THEME_PATH . $fileKey;
            $newFile = fopen($newFileName, 'w');
            $replace_arr = array(

            "var(--brand-color)" => $themeDetail[ThemeColor::TYPE_BRAND],
            "var(--brand-color-inverse)" => $themeDetail[ThemeColor::TYPE_BRAND_INVERSE],
            "var(--primary-color)" => $themeDetail[ThemeColor::TYPE_PRIMARY],
            "var(--primary-color-inverse)" => $themeDetail[ThemeColor::TYPE_PRIMARY_INVERSE],
            "var(--secondary-color)" => $themeDetail[ThemeColor::TYPE_SECONDARY],
            "var(--secondary-color-inverse)" => $themeDetail[ThemeColor::TYPE_SECONDARY_INVERSE],
            "var(--third-color)" => $themeDetail[ThemeColor::TYPE_THIRD],
            "var(--third-color-inverse)" => $themeDetail[ThemeColor::TYPE_THIRD_INVERSE],
            "var(--body-color)" => $themeDetail[ThemeColor::TYPE_BODY],
            "var(--gray-color)" => $themeDetail[ThemeColor::TYPE_GREY],
            "var(--gray-light)" => $themeDetail[ThemeColor::TYPE_GREY_LIGHT],
            "var(--border-color)" => $themeDetail[ThemeColor::TYPE_BORDER],
            "var(--border-dark-color)" => $themeDetail[ThemeColor::TYPE_BORDER_DARK],
            "var(--border-light-color)" => $themeDetail[ThemeColor::TYPE_BORDER_LIGHT],
            "var(--font-color)" => $themeDetail[ThemeColor::TYPE_FONT],
            "var(--font-color2)" => $themeDetail[ThemeColor::TYPE_FONT_SECONDARY],

            );

            foreach ($replace_arr as $key => $val) {
                $str = str_replace($key, "#" . $val, $str);
            }
            fwrite($newFile, $str);
        }
    } */

    public function preview($themeId)
    {
        $themeId = FatUtility::int($themeId);
        if (0 >= $themeId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatApp::redirectUser(UrlHelper::generateUrl('ThemeColor'));
        }

        /* $tObj = new ThemeColor();
        $theme = $tObj->getAttributesById($themeId); */
        if (!$themeId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatApp::redirectUser(UrlHelper::generateUrl('ThemeColor'));
        }
        $_SESSION['preview_theme'] = $themeId;

        $this->set('theme', $themeId);
        $this->_template->render(false, false);
    }
}
