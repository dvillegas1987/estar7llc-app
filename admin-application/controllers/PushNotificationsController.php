<?php

class PushNotificationsController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewPushNotification();
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $active = (new Plugin())->getDefaultPluginData(Plugin::TYPE_PUSH_NOTIFICATION, 'plugin_active');
        if (false == $active || empty($active)) {
            Message::addErrorMessage(Labels::getlabel("MSG_NO_DEFAULT_PUSH_NOTIFICATION_PLUGIN__FOUND", $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
    }

    private function validateRequest($pNotificationId)
    {
        $pNotificationId = FatUtility::int($pNotificationId);
        $status = PushNotification::getAttributesById($pNotificationId, 'pnotification_status');
        if (0 != $status) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_NOT_ALLOWED", $this->adminLangId));
        }
    }

    public function index()
    {
        $this->canEdit = $this->objPrivilege->canEditPushNotification($this->admin_id, true);
        $frmSearch = $this->getSearchForm();
        $this->set("canEdit", $this->canEdit);
        $this->set("frmSearch", $frmSearch);
        $this->_template->addJs(array('js/jquery.datetimepicker.js', 'js/cropper.js', 'js/cropper-main.js'), false);
        $this->_template->addCss(array('css/jquery.datetimepicker.css', 'css/cropper.css'), false);
        $this->_template->render();
    }

    public function search()
    {
        $this->canEdit = $this->objPrivilege->canEditPushNotification($this->admin_id, true);

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);


        $srchFrm = $this->getSearchForm();
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

        $page = $post['page'];
        if ($page < 2) {
            $page = 1;
        }

        $srch = PushNotification::getSearchObject();

        $keyword = $post['keyword'];
        if (!empty($keyword)) {
            $srch->addCondition('pnotification_title', 'LIKE', '%' . $keyword . '%');
        }

        $status = $post['pnotification_status'];
        if ('' != $status && -1 < $status) {
            $srch->addCondition('pnotification_status', '=', $status);
        }

        $deviceType = $post['pnotification_device_os'];
        if ('' != $deviceType && -1 < $deviceType) {
            $srch->addCondition('pnotification_device_os', '=', $deviceType);
        }

        /* $notifyTo = $post['notify_to'];
        if (0 < $notifyTo) {
            switch ($notifyTo) {
                case PushNotification::NOTIFY_TO_BUYER:
                    $srch->addCondition('pnotification_for_buyer', '=', applicationConstants::YES);
                    break;
                case PushNotification::NOTIFY_TO_SELLER:
                    $srch->addCondition('pnotification_for_seller', '=', applicationConstants::YES);
                    break;
            }
        } */

        $srch->addOrder('pn.pnotification_id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $statusArr = PushNotification::getStatusArr($this->adminLangId);

        $this->set('arr_listing', $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('recordCount', $srch->recordCount());
        $this->set("canEdit", $this->canEdit);
        $this->set("statusArr", $statusArr);
        $this->_template->render(false, false);
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        $statusArr = [-1 => Labels::getLabel('LBL_DOES_NOT_MATTER', $this->adminLangId)] + PushNotification::getStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_STATUS', $this->adminLangId), 'pnotification_status', $statusArr, '', array(), '');

        $deviceTypeArr = [-1 => Labels::getLabel('LBL_DOES_NOT_MATTER', $this->adminLangId)] + User::getDeviceTypeArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_DEVICE_OPERATING_SYSTEM', $this->adminLangId), 'pnotification_device_os', $deviceTypeArr, '', array(), '');

        // $notifyToArr = array_merge([Labels::getLabel('LBL_DOES_NOT_MATTER', $this->adminLangId)], PushNotification::getUserTypeArr($this->adminLangId));
        // $frm->addSelectBox(Labels::getLabel('LBL_NOTIFY_TO', $this->adminLangId), 'notify_to', $notifyToArr, '', array(), '');

        $frm->addHiddenField('', 'page');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), ['onclick' => 'clearSearch();']);
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function form($status = 0)
    {
        $frm = new Form('PushNotificationForm', array('id' => 'PushNotificationForm'));
        $frm->addHiddenField('', 'pnotification_id');

        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'pnotification_lang_id', Language::getAllNames(), $this->adminLangId, array(), '');
        
        $userAuthType = $frm->addSelectBox(Labels::getLabel('LBL_USER_AUTH_TYPE', $this->adminLangId), 'pnotification_user_auth_type', User::getUserAuthTypeArr($this->adminLangId), '', [], Labels::getLabel('LBL_Select', $this->adminLangId));
        $userAuthType->requirements()->setRequired(true);
        $userAuthType->htmlAfterField = '<small>' . Labels::getLabel('LBL_YOU_CAN_CLONE_TO_SEND_THIS_NOTIFICATION_TO_OTHER_USER_AUTH_TYPE', $this->adminLangId) . '</small>';

        $frm->addRequiredField(Labels::getLabel('LBL_TITLE', $this->adminLangId), 'pnotification_title');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_BODY', $this->adminLangId), 'pnotification_description');
        $fld->requirements()->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_URL', $this->adminLangId), 'pnotification_url');

        $dateFld = $frm->addDateTimeField(Labels::getLabel('LBL_SCHEDULE_DATE', $this->adminLangId), 'pnotification_notified_on', date('Y-m-d H:00'), ['readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender date_js']);
        $dateFld->requirements()->setRequired(true);

        $deviceType = $frm->addSelectBox(Labels::getLabel('LBL_DEVICE_OPERATING_SYSTEM', $this->adminLangId), 'pnotification_device_os', User::getDeviceTypeArr($this->adminLangId), '', [], Labels::getLabel('LBL_Select', $this->adminLangId));
        $deviceType->requirements()->setRequired(true);

        // $frm->addCheckBox(Labels::getLabel('LBL_NOTIFY_TO_BUYERS', $this->adminLangId), 'pnotification_for_buyer', 1, [], false, 0);
        // $frm->addCheckBox(Labels::getLabel('LBL_NOTIFY_TO_SELLER', $this->adminLangId), 'pnotification_for_seller', 1, [], false, 0);

        if (0 == $status) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $this->adminLangId));
        }
        return $frm;
    }

    public function getMediaForm($pNotificationId, $status = 0)
    {
        $frm = new Form('frmPushNotificationMedia');
        $frm->addHiddenField('', 'pnotification_id', $pNotificationId);
        $frm->addHiddenField('', 'file_type', AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE);
        $ul = $frm->addHtml('', 'MediaGrids', '<ul class="grids--onethird">');

        $ul->htmlAfterField .= '<li>' . Labels::getLabel('LBL_PUSH_NOTIFICATION_IMAGE', $this->adminLangId) . '<div class="logoWrap"><div class="uploaded--image">';

        if ($imgData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE, $pNotificationId)) {
            $uploadedTime = AttachedFile::setTimeParam($imgData['afile_updated_at']);
            $ul->htmlAfterField .= '<img src="' . UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'pushNotificationImage', [$pNotificationId], CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . '">';
            if (0 == $status) {
                $ul->htmlAfterField .= '<a  class="remove--img" href="javascript:void(0);" onclick="removeImage(' . $pNotificationId . ')" ><i class="ion-close-round"></i></a>';
            }
        }

        if (0 == $status) {
            $ul->htmlAfterField .= ' </div></div><input accept="image/*" data-frm="frmPushNotificationMedia" class="btn btn-brand btn-sm" onchange="popupImage(this)" title="Upload" type="file" name="app_push_notification_image" value="'. Labels::getLabel('LBL_Upload_File', $this->adminLangId).'"><small>' . Labels::getLabel('LBL_SIZE_MUST_BE_LESS_THAN_300KB', $this->adminLangId) . '</small></li>';
        }
        return $frm;
    }

    public function selectedUsersform($status = 0)
    {
        $frm = new Form('PushNotificationUserForm', array('id' => 'PushNotificationUserForm'));
        $frm->addHiddenField('', 'pnotification_id');

        $attributes = ['placeholder' => Labels::getLabel('LBL_Search...', $this->adminLangId)];
        if (0 != $status) {
            $attributes['class'] = 'd-none';
        }

        $userFld = $frm->addTextBox(Labels::getLabel('LBL_SELECT_USER', $this->adminLangId), 'users', '', $attributes);
        $userFld->htmlAfterField = '<small>' . Labels::getLabel('LBL_SELECTED_USER_LIST_WILL_BE_DISPLAYED_HERE', $this->adminLangId) . '</small><div class="box--scroller"><ul class="columlist list--vertical" id="selectedUsersList-js"></ul></div>';
        return $frm;
    }

    public function addNotificationForm($pNotificationId = 0)
    {
        $frm = $this->form();
        $pNotificationId = FatUtility::int($pNotificationId);
        $status = 0;
        $userAuthType = '';
        if (0 < $pNotificationId) {
            $data = PushNotification::getAttributesById($pNotificationId);
            $status = $data['pnotification_status'];
            $frm = $this->form($data['pnotification_status']);
            $userAuthType = $data['pnotification_user_auth_type'];
            $frm->fill($data);
        }
        $this->set('status', $status);
        $this->set('pNotificationId', $pNotificationId);
        $this->set('userAuthType', $userAuthType);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function addMediaForm($pNotificationId)
    {
        $pNotificationId = FatUtility::int($pNotificationId);
        if (1 > $pNotificationId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $data = PushNotification::getAttributesById($pNotificationId, ['pnotification_status', 'pnotification_user_auth_type']);
        
        $this->objPrivilege->canEditPushNotification();
        $mediaFrm = $this->getMediaForm($pNotificationId, $data['pnotification_status']);
        $this->set('status', $data['pnotification_status']);
        $this->set('languages', Language::getAllNames());
        $this->set('pNotificationId', $pNotificationId);
        $this->set('userAuthType', $data['pnotification_user_auth_type']);
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
        $this->set('frm', $mediaFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $frm = $this->form();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }

        /* if (empty($post['pnotification_for_buyer']) && empty($post['pnotification_for_seller'])) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_MUST_SELECT_EITHER_BUYER_OR_SELLER", $this->adminLangId));
        } */

        unset($post['btn_submit']);

        $post['pnotification_type'] = PushNotification::TYPE_APP;
        $post['pnotification_for_buyer'] = applicationConstants::YES;

        /* if (!empty($post['pnotification_id'])) {
            $this->validateRequest($post['pnotification_id']);
            $recordDetail = PushNotification::getAttributesById($post['pnotification_id'], ['pnotification_for_buyer', 'pnotification_for_seller']);
            if ($post['pnotification_for_buyer'] != $recordDetail['pnotification_for_buyer'] || $post['pnotification_for_seller'] != $recordDetail['pnotification_for_seller']) {
                $db = FatApp::getDb();
                if (!$db->deleteRecords(PushNotification::DB_TBL_NOTIFICATION_TO_USER, ['smt' => 'pntu_pnotification_id = ?', 'vals' => [$post['pnotification_id']]])) {
                    FatUtility::dieJsonError($db->getError());
                }
            }
        } */

        $db = FatApp::getDb();
        if (!$db->insertFromArray(PushNotification::DB_TBL, $post, true, array(), $post)) {
            FatUtility::dieJsonError($db->getError());
        }

        $recordId = !empty($post['pnotification_id']) ? $post['pnotification_id'] : $db->getInsertId();

        $json['msg'] = Labels::getLabel("LBL_SETUP_SUCCESSFULLY", $this->adminLangId);
        $json['status'] = true;
        $json['recordId'] = $recordId;
        FatUtility::dieJsonSuccess($json);
    }

    public function clone($pNotificationId)
    {
        $pNotificationId = FatUtility::int($pNotificationId);
        if (1 > $pNotificationId) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_INVALID_REQUEST", $this->adminLangId));
        }
        $data = PushNotification::getAttributesById($pNotificationId);
        unset($data['pnotification_id'], $data['pnotification_status'], $data['pnotification_uauth_last_access']);
        $db = FatApp::getDb();
        if (!$db->insertFromArray(PushNotification::DB_TBL, $data, true, array(), $data)) {
            FatUtility::dieJsonError($db->getError());
        }

        $recordId = $db->getInsertId();
        $data['pnotification_id'] = $recordId;

        $frm = $this->form();
        $frm->fill($data);
        $this->set('pNotificationId', $recordId);
        $this->set('userAuthType', $data['pnotification_user_auth_type']);
        $this->set('frm', $frm);
        $this->set('status', 0);
        $this->_template->render(false, false, 'push-notifications/add-notification-form.php');
    }

    public function addSelectedUsersForm($pNotificationId)
    {
        $this->objPrivilege->canEditPushNotification();
        $pNotificationId = FatUtility::int($pNotificationId);
        if (1 > $pNotificationId) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_INVALID_REQUEST", $this->adminLangId));
        }
        $data = PushNotification::getAttributesById($pNotificationId, ['pnotification_status', 'pnotification_user_auth_type']);
        if (User::AUTH_TYPE_GUEST == $data['pnotification_user_auth_type']) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_NOT_ALLOWED", $this->adminLangId));
        }

        $frm = $this->selectedUsersform($data['pnotification_status']);
        $frm->fill(['pnotification_id' => $pNotificationId]);
        $srch = PushNotification::getSearchObject(true);
        $srch->addMultipleFields(['pnotification_id', 'pntu_user_id', 'user_name', 'credential_username', 'pnotification_user_auth_type']);
        $srch->joinTable('tbl_users', 'INNER JOIN', 'pntu_user_id = tu.user_id', 'tu');
        $srch->joinTable('tbl_user_credentials', 'INNER JOIN', 'tu.user_id = tuc.credential_user_id', 'tuc');
        $srch->addCondition('pnotification_id', "=", $pNotificationId);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        if (!empty($records) && 0 < count($records)) {
            $this->set('data', $records);
        }
        $this->set('notifyTo', PushNotification::getAttributesById($pNotificationId, ['pnotification_for_buyer', 'pnotification_for_seller']));
        $this->set('pNotificationId', $pNotificationId);
        $this->set('status', $data['pnotification_status']);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function bindUser($pNotificationId, $userId)
    {
        $this->objPrivilege->canEditPushNotification();
        $pNotificationId = FatUtility::int($pNotificationId);
        $this->validateRequest($pNotificationId);
        $userId = FatUtility::int($userId);
        if (1 > $pNotificationId || 1 > $userId) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_INVALID_REQUEST", $this->adminLangId));
        }
        $PushNotificationData = [
            'pntu_pnotification_id' => $pNotificationId,
            'pntu_user_id' => $userId
        ];
        $db = FatApp::getDb();
        if (!$db->insertFromArray(PushNotification::DB_TBL_NOTIFICATION_TO_USER, $PushNotificationData, true, array(), $PushNotificationData)) {
            FatUtility::dieJsonError($db->getError());
        }
    }

    public function unlinkUser($pNotificationId, $userId)
    {
        $this->objPrivilege->canEditPushNotification();
        $pNotificationId = FatUtility::int($pNotificationId);
        $this->validateRequest($pNotificationId);
        $userId = FatUtility::int($userId);
        if (1 > $pNotificationId || 1 > $userId) {
            FatUtility::dieJsonError(Labels::getLabel("LBL_INVALID_REQUEST", $this->adminLangId));
        }
        $db = FatApp::getDb();
        if (!$db->deleteRecords(PushNotification::DB_TBL_NOTIFICATION_TO_USER, ['smt' => 'pntu_pnotification_id = ? AND pntu_user_id = ?', 'vals' => [$pNotificationId, $userId]])) {
            FatUtility::dieJsonError($db->getError());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_SUCCESS", $this->adminLangId));
    }

    public function removeImage($pNotificationId)
    {
        $this->objPrivilege->canEditPushNotification();
        $pNotificationId = FatUtility::int($pNotificationId);
        $this->validateRequest($pNotificationId);
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE, $pNotificationId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_SUCCESS", $this->adminLangId));
    }

    public function uploadMedia()
    {
        $this->objPrivilege->canEditPushNotification();
        $post = FatApp::getPostedData();

        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->validateRequest($post['pnotification_id']);
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);

        if (!$file_type) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($file_type != AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], $file_type, $post['pnotification_id'], 0, $_FILES['cropped_image']['name'], -1, true)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('msg', $_FILES['cropped_image']['name'] . Labels::getLabel('MSG_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
