<?php

class ImportExportController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewImportExport();
    }

    public function index()
    {
        $this->_template->addJs('js/import-export.js');
        $this->set('action', 'generalInstructions');
        $this->_template->render();
    }

    public function exportData($actionType)
    {
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $exportDataRange = FatApp::getPostedData('export_data_range', FatUtility::VAR_INT, 0);
        $startId = FatApp::getPostedData('start_id', FatUtility::VAR_INT, 0);
        $endId = FatApp::getPostedData('end_id', FatUtility::VAR_INT, 0);
        $batchCount = FatApp::getPostedData('batch_count', FatUtility::VAR_INT, 0);
        $batchNumber = FatApp::getPostedData('batch_number', FatUtility::VAR_INT, 1);
        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);

        if (1 > $langId) {
            $langId = CommonHelper::getLangId();
        }

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canViewSellerProducts();
                break;
            case Importexport::TYPE_OPTIONS:
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                break;
            case Importexport::TYPE_ZONES:
                $this->objPrivilege->canViewZones();
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                break;
            case Importexport::TYPE_USERS:
                $this->objPrivilege->canViewUsers();
                break;
            case Importexport::TYPE_TAX_CATEGORY:
                $this->objPrivilege->canViewTax();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj = new Importexport();
        $min = null;
        $max = null;
        switch ($exportDataRange) {
            case Importexport::BY_ID_RANGE:
                if (isset($startId) && $startId > 0) {
                    $min = $startId;
                }

                if (isset($endId) && $endId > 1 && $endId > $min) {
                    $max = $endId;
                }
                $obj->export($actionType, $langId, $sheetType, null, null, $min, $max);
                break;
            case Importexport::BY_BATCHES:
                if (isset($batchNumber) && $batchNumber > 0) {
                    $min = $batchNumber;
                }

                $max = Importexport::MAX_LIMIT;
                if (isset($batchCount) && $batchCount > 0 && $batchCount <= Importexport::MAX_LIMIT) {
                    $max = $batchCount;
                }
                $min = (!$min) ? 1 : $min;
                $obj->export($actionType, $langId, $sheetType, $min, $max, null, null);
                break;

            default:
                $obj->export($actionType, $langId, $sheetType, null, null, null, null);
                break;
        }
    }

    public function importData($actionType)
    {
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sheetType = FatApp::getPostedData('sheet_type', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canEditProducts();
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canEditSellerProducts();
                break;
            case Importexport::TYPE_OPTIONS:
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canEditOptions();
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canEditTags();
                break;
            case Importexport::TYPE_ZONES:
                $this->objPrivilege->canEditZones();
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canEditCountries();
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canEditStates();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canEditPolicyPoints();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj->import($actionType, $langId, $sheetType);
    }

    public function exportMedia($actionType)
    {
        $post = FatApp::getPostedData();
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $exportDataRange = FatApp::getPostedData('export_data_range', FatUtility::VAR_INT, 0);
        $startId = FatApp::getPostedData('start_id', FatUtility::VAR_INT, 0);
        $endId = FatApp::getPostedData('end_id', FatUtility::VAR_INT, 0);
        $batchCount = FatApp::getPostedData('batch_count', FatUtility::VAR_INT, 0);
        $batchNumber = FatApp::getPostedData('batch_number', FatUtility::VAR_INT, 1);

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canViewSellerProducts();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj = new Importexport();

        $min = null;
        $max = null;

        switch ($exportDataRange) {
            case Importexport::BY_ID_RANGE:
                if (isset($startId) && $startId > 0) {
                    $min = $startId;
                }

                if (isset($endId) && $endId > 1 && $endId > $min) {
                    $max = $endId;
                }

                $obj->exportMedia($actionType, $langId, null, null, $min, $max);
                break;
            case Importexport::BY_BATCHES:
                if (isset($batchNumber) && $batchNumber > 0) {
                    $min = $batchNumber;
                }

                $max = Importexport::MAX_LIMIT;
                if (isset($batchCount) && $batchCount > 0 && $batchCount <= Importexport::MAX_LIMIT) {
                    $max = $batchCount;
                }
                $min = (!$min) ? 1 : $min;
                $obj->exportMedia($actionType, $langId, $min, $max, null, null);
                break;

            default:
                $obj->exportMedia($actionType, $langId, null, null, null, null);
                break;
        }
    }

    public function importMedia($actionType)
    {
        $post = FatApp::getPostedData();

        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new Importexport();
        if (!$obj->isUploadedFileValidMimes($_FILES['import_file'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canEditProducts();
                break;
            default:
                Message::addErrorMessage($this->str_invalid_request);
                break;
        }

        $obj->importMedia($actionType, $post, $langId);
    }

    public function importMediaForm($actionType)
    {
        $langId = $this->adminLangId;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                $title = Labels::getLabel('LBL_Import_Categories_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'IMPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                $title = Labels::getLabel('LBL_Import_Brands_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'IMPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canEditProducts();
                $title = Labels::getLabel('LBL_Import_Catalog_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'IMPORT_MEDIA', $actionType);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function exportMediaForm($actionType)
    {
        $langId = $this->adminLangId;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                $title = Labels::getLabel('LBL_Export_Categories_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                $title = Labels::getLabel('LBL_Export_Brands_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $title = Labels::getLabel('LBL_Export_Catalogs_Media', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canViewSellerProducts();
                $title = Labels::getLabel('LBL_Export_Digital_Files', $langId);
                $frm = $this->getImportExportForm($langId, 'EXPORT_MEDIA', $actionType);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function importForm($actionType)
    {
        $langId = $this->adminLangId;
        $displayMediaTab = true;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                $title = Labels::getLabel('LBL_Import_Categories', $langId);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                $title = Labels::getLabel('LBL_Import_Brands', $langId);
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $title = Labels::getLabel('LBL_Import_Catalogs', $langId);
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canViewSellerProducts();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_IMPORT_SELLER_INVENTORIES', $langId);
                break;
            case Importexport::TYPE_OPTIONS:
                $this->objPrivilege->canViewOptions();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Options', $langId);
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Option_Values', $langId);
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Tags', $langId);
                break;
            case Importexport::TYPE_ZONES:
                $this->objPrivilege->canViewZones();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Zones', $langId);
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Countries', $langId);
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_States', $langId);
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                $displayMediaTab = false;
                $title = Labels::getLabel('LBL_Import_Policy_Points', $langId);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $frm = $this->getImportExportForm($langId, 'IMPORT', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }

    public function importInstructions($actionType)
    {
        $langId = $this->adminLangId;
        $obj = new Extrapage();
        $pageData = '';
        $displayMediaTab = false;
        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canEditProductCategories();
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_PRODUCTS_CATEGORIES_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canEditBrands();
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_BRANDS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $displayMediaTab = true;
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_CATALOG_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canViewSellerProducts();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_PRODUCT_INVENTORY_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_OPTIONS:
                $this->objPrivilege->canViewOptions();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_OPTIONS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_OPTIONS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_TAGS_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_ZONES:
                $this->objPrivilege->canViewZones();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_ZONE_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_COUNTRIES_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_STATE_MANAGEMENT_INSTRUCTIONS, $langId);
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                $pageData = $obj->getContentByPageType(Extrapage::ADMIN_TYPE_POLICY_POINTS, $langId);
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }
        $title = Labels::getLabel('LBL_Import_Instructions', $langId);
        $this->set('pageData', $pageData);
        $this->set('title', $title);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->_template->render(false, false);
    }

    public function exportForm($actionType)
    {
        $langId = $this->adminLangId;
        $displayMediaTab = false;

        $options = Importexport::getImportExportTypeArr('export', $this->adminLangId, false);
        $title = $options[$actionType];

        switch ($actionType) {
            case Importexport::TYPE_CATEGORIES:
                $this->objPrivilege->canViewProductCategories();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_BRANDS:
                $this->objPrivilege->canViewBrands();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->objPrivilege->canViewProducts();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_INVENTORIES:
                $this->objPrivilege->canViewSellerProducts();
                $displayMediaTab = true;
                break;
            case Importexport::TYPE_OPTIONS:
                $this->objPrivilege->canViewOptions();
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $this->objPrivilege->canViewOptions();
                break;
            case Importexport::TYPE_TAG:
                $this->objPrivilege->canViewTags();
                break;
            case Importexport::TYPE_ZONES:
                $this->objPrivilege->canViewZones();
                break;
            case Importexport::TYPE_COUNTRY:
                $this->objPrivilege->canViewCountries();
                break;
            case Importexport::TYPE_STATE:
                $this->objPrivilege->canViewStates();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $this->objPrivilege->canViewPolicyPoints();
                break;
            case Importexport::TYPE_USERS:
                $this->objPrivilege->canViewUsers();
                break;
            case Importexport::TYPE_TAX_CATEGORY:
                $this->objPrivilege->canViewTax();
                break;
            default:
                FatUtility::dieWithError($this->str_invalid_request);
                break;
        }

        $frm = $this->getImportExportForm($langId, 'EXPORT', $actionType);
        $this->set('frm', $frm);
        $this->set('actionType', $actionType);
        $this->set('displayMediaTab', $displayMediaTab);
        $this->set('title', $title);
        $this->_template->render(false, false);
    }


    public function getImportExportForm($langId, $type = 'EXPORT', $actionType)
    {
        $frm = new Form('frmImportExport', array('id' => 'frmImportExport'));
        $languages = Language::getAllNames();

        /* if($type != 'EXPORT_MEDIA'){ */
        if ($type == 'IMPORT_MEDIA') {
            $frm->addSelectBox(Labels::getLabel('LBL_Upload_File_Language', $langId), 'lang_id', $languages, '', array(), '')->requirements()->setRequired();
        } elseif ($type == 'EXPORT_MEDIA') {
            $frm->addSelectBox(Labels::getLabel('LBL_Export_File_Language', $langId), 'lang_id', $languages, '', array(), '')->requirements()->setRequired();
        } else {
            $frm->addSelectBox(Labels::getLabel('LBL_Language', $langId), 'lang_id', $languages, '', array(), '')->requirements()->setRequired();
        }
        /* } */

        $displayRangeFields = false;

        switch (strtoupper($type)) {
            case 'EXPORT':
                switch ($actionType) {
                    case Importexport::TYPE_PRODUCTS:
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $displayRangeFields = true;
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getProductCatalogContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_INVENTORIES:
                        $displayRangeFields = true;
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getSellerProductContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_USERS:
                        $displayRangeFields = true;
                        break;
                }
                break;
            case 'EXPORT_MEDIA':
                switch ($actionType) {
                    case Importexport::TYPE_PRODUCTS:
                    case Importexport::TYPE_SELLER_PRODUCTS:
                    case Importexport::TYPE_INVENTORIES:
                        $displayRangeFields = true;
                        break;
                }
                break;
            case 'IMPORT':
                switch ($actionType) {
                    case Importexport::TYPE_PRODUCTS:
                    case Importexport::TYPE_SELLER_PRODUCTS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getProductCatalogContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    case Importexport::TYPE_INVENTORIES:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getSellerProductContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break;
                    /* case Importexport::TYPE_OPTIONS:
                        $frm->addSelectBox(Labels::getLabel('LBL_Select_Data', $langId), 'sheet_type', Importexport::getOptionContentTypeArr($langId), '', array(), '')->requirements()->setRequired();
                        break; */
                }
                $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'import_file', array('id' => 'import_file'));
                $fldImg->requirement->setRequired(true);
                $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
                $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField = '<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $langId) . '</label></div>';
                break;
            case 'IMPORT_MEDIA':
                $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_File_to_be_uploaded:', $langId), 'import_file', array('id' => 'import_file'));
                $fldImg->requirement->setRequired(true);
                $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
                $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename" id="importFileName"></span>';
                $fldImg->htmlAfterField = '<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $langId) . '</label></div>';
                break;
        }

        if ($displayRangeFields) {
            $dataRangeArr = array(0 => Labels::getLabel('LBL_Does_not_matter', $langId)) + Importexport::getDataRangeArr($langId);
            $rangeTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Export_data_range', $langId), 'export_data_range', $dataRangeArr, '', array(), '');

            /* Start Id[ */
            $frm->addIntegerField(Labels::getLabel('LBL_start_id', $langId), 'start_id', 1);
            $startIdUnReqObj = new FormFieldRequirement('start_id', Labels::getLabel('LBL_start_id', $langId));
            $startIdUnReqObj->setRequired(false);

            $startIdReqObj = new FormFieldRequirement('start_id', Labels::getLabel('LBL_start_id', $langId));
            $startIdReqObj->setRequired(true);
            /*]*/

            /* End Id[ */
            $frm->addIntegerField(Labels::getLabel('LBL_end_id', $langId), 'end_id', Importexport::MAX_LIMIT);
            $endIdUnReqObj = new FormFieldRequirement('end_id', Labels::getLabel('LBL_end_id', $langId));
            $endIdUnReqObj->setRequired(false);

            $endIdReqObj = new FormFieldRequirement('end_id', Labels::getLabel('LBL_end_id', $langId));
            $endIdReqObj->setRequired(true);
            //$endIdReqObj->setRange(1,Importexport::MAX_LIMIT);
            /*]*/

            /* Batch Count[ */
            $frm->addIntegerField(Labels::getLabel('LBL_counts_per_batch', $langId), 'batch_count', Importexport::MAX_LIMIT);
            $batchCountUnReqObj = new FormFieldRequirement('batch_count', Labels::getLabel('LBL_counts_per_batch', $langId));
            $batchCountUnReqObj->setRequired(false);

            $batchCountReqObj = new FormFieldRequirement('batch_count', Labels::getLabel('LBL_counts_per_batch', $langId));
            $batchCountReqObj->setRequired(true);
            $batchCountReqObj->setRange(1, Importexport::MAX_LIMIT);
            /*]*/

            /* Batch Number[ */
            $frm->addIntegerField(Labels::getLabel('LBL_batch_number', $langId), 'batch_number', 1);
            $batchNumberUnReqObj = new FormFieldRequirement('batch_number', Labels::getLabel('LBL_batch_number', $langId));
            $batchNumberUnReqObj->setRequired(false);

            $batchNumberReqObj = new FormFieldRequirement('batch_number', Labels::getLabel('LBL_batch_number', $langId));
            $batchNumberReqObj->setRequired(true);
            /*]*/

            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'batch_count', $batchCountUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'batch_number', $batchNumberUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'start_id', $startIdUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(0, 'eq', 'end_id', $endIdUnReqObj);

            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'batch_count', $batchCountUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'batch_number', $batchNumberUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'start_id', $startIdReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_ID_RANGE, 'eq', 'end_id', $endIdReqObj);

            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'start_id', $startIdUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'end_id', $endIdUnReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'batch_count', $batchCountReqObj);
            $rangeTypeFld->requirements()->addOnChangerequirementUpdate(Importexport::BY_BATCHES, 'eq', 'batch_number', $batchNumberReqObj);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $langId));
        return $frm;
    }


    public function loadForm($formType)
    {
        switch (strtoupper($formType)) {
            case 'GENERAL_INSTRUCTIONS':
                $this->generalInstructions();
                break;
            case 'IMPORT':
                $this->import();
                break;
            case 'EXPORT':
                $this->export();
                break;
            case 'SETTINGS':
                $this->settings();
                break;
            case 'BULK_MEDIA':
                $this->bulkMedia();
                break;
        }
    }

    public function generalInstructions()
    {
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::GENERAL_SETTINGS_INSTRUCTIONS, $this->adminLangId);
        $this->set('pageData', $pageData);
        $this->set('action', 'generalInstructions');
        $this->_template->render(false, false, 'import-export/general-instructions.php');
    }

    public function export()
    {
        $frm = $this->getExportForm($this->adminLangId);
        $this->set('action', 'export');
        $this->set('frm', $frm);
        $this->_template->render(false, false, 'import-export/export.php');
    }

    private function getExportForm($langId)
    {
        $frm = new Form('frmExport', array('id' => 'frmExport'));
        $options = Importexport::getImportExportTypeArr('export', $langId, false);
        $fld = $frm->addRadioButtons(
            '',
            'export_option',
            $options,
            '',
            array('class' => 'list-inline list-col-2'),
            array('onClick' => 'exportForm(this.value)')
        );
        return $frm;
    }

    public function import()
    {
        $frm = $this->getImportForm($this->adminLangId);

        $this->set('action', 'import');
        $this->set('frm', $frm);
        $this->set('sitelangId', $this->adminLangId);
        $this->_template->render(false, false, 'import-export/import.php');
    }

    private function getImportForm($langId)
    {
        $frm = new Form('frmImport', array('id' => 'frmImport'));
        $options = Importexport::getImportExportTypeArr('import', $langId, false);
        /* if (!FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) {
            unset($options[Importexport::TYPE_PRODUCTS]);
        } */
        $fld = $frm->addRadioButtons(
            '',
            'export_option',
            $options,
            '',
            array('class' => 'list-inline list-col-2'),
            array('onClick' => 'getInstructions(this.value)')
        );

        return $frm;
    }

    public function settings()
    {
        $frm = $this->getSettingForm();
        $obj = new Importexport();
        $settingArr = $obj->getSettings(0);
        $frm->fill($settingArr);
        $this->set('frm', $frm);
        $this->set('action', 'settings');
        $this->_template->render(false, false, 'import-export/settings.php');
    }

    private function getSettingForm()
    {
        $frm = new Form('frmImportExportSetting', array('id' => 'frmImportExportSetting'));

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_brand_id_instead_of_brand_identifier", $this->adminLangId), 'CONF_USE_BRAND_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_brand_id_instead_of_brand_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_category_id_instead_of_category_identifier", $this->adminLangId), 'CONF_USE_CATEGORY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_category_id_instead_of_category_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_catalog_product_id_instead_of_catalog_product_identifier", $this->adminLangId), 'CONF_USE_PRODUCT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_catalog_product_id_instead_of_catalog_product_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_user_id_instead_of_username", $this->adminLangId), 'CONF_USE_USER_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_user_id_instead_of_username_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_id_instead_of_option_identifier", $this->adminLangId), 'CONF_USE_OPTION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_option_id_instead_of_option_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_value_id_instead_of_option_identifier", $this->adminLangId), 'CONF_OPTION_VALUE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_option_value_id_instead_of_option_value_identifier_in_worksheets", $this->adminLangId) . '</small>';

        /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_option_type_id_instead_of_option_type_identifier",$this->adminLangId),'CONF_USE_OPTION_TYPE_ID',1,array(),false,0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_option_type_id_instead_of_option_type_identifier_in_worksheets",$this->adminLangId) . '</small>'; */

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_tag_id_instead_of_tag_identifier", $this->adminLangId), 'CONF_USE_TAG_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_tag_id_instead_of_tag_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_tax_id_instead_of_tax_identifier", $this->adminLangId), 'CONF_USE_TAX_CATEOGRY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_tax_category_id_instead_of_tax_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_product_type_id_instead_of_product_type_identifier", $this->adminLangId), 'CONF_USE_PRODUCT_TYPE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_product_type_id_instead_of_product_type_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_dimension_unit_id_instead_of_dimension_unit_identifier", $this->adminLangId), 'CONF_USE_DIMENSION_UNIT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_dimension_unit_id_instead_of_dimension_unit_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_weight_unit_id_instead_of_weight_unit_identifier", $this->adminLangId), 'CONF_USE_WEIGHT_UNIT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_weight_unit_id_instead_of_weight_unit_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_lang_id_instead_of_lang_code", $this->adminLangId), 'CONF_USE_LANG_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_language_id_instead_of_language_code_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_currency_id_instead_of_currency_code", $this->adminLangId), 'CONF_USE_CURRENCY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_currency_id_instead_of_currency_code_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_Product_condition_id_instead_of_condition_identifier", $this->adminLangId), 'CONF_USE_PROD_CONDITION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_Product_condition_id_instead_of_condition_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_persent_or_flat_condition_id_instead_of_identifier", $this->adminLangId), 'CONF_USE_PERSENT_OR_FLAT_CONDITION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_persent_or_flat_condition_id_instead_of_identifier_in_worksheets", $this->adminLangId) . '</small>';
        
        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_zone_id_instead_of_zone_identifier", $this->adminLangId), 'CONF_USE_ZONE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_zone_id_instead_of_zone_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_country_id_instead_of_country_code", $this->adminLangId), 'CONF_USE_COUNTRY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_country_id_instead_of_country_code_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_state_id_instead_of_state_identifier", $this->adminLangId), 'CONF_USE_STATE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_state_id_instead_of_state_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_policy_point_id_instead_of_policy_point_identifier", $this->adminLangId), 'CONF_USE_POLICY_POINT_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_policy_point_id_instead_of_policy_point_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_company_id_instead_of_shipping_company_identifier", $this->adminLangId), 'CONF_USE_SHIPPING_COMPANY_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_shipping_company_id_instead_of_shipping_company_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_policy_point_type_id_instead_of_policy_point_type_identifier", $this->adminLangId), 'CONF_USE_POLICY_POINT_TYPE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_policy_point_type_id_instead_of_policy_point_type_identifier_in_worksheets", $this->adminLangId) . '</small>';

        /* $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_method_id_instead_of_shipping_method_identifier",$this->adminLangId),'CONF_USE_SHIPPING_METHOD_ID',1,array(),false,0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_shipping_method_id_instead_of_shipping_method_identifier_in_worksheets",$this->adminLangId) . '</small>'; */

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_duration_id_instead_of_shipping_duration_identifier", $this->adminLangId), 'CONF_USE_SHIPPING_DURATION_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_shipping_duration_id_instead_of_shipping_duration_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_profile_id_instead_of_shipping_profile_identifier", $this->adminLangId), 'CONF_USE_SHIPPING_PROFILE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_shipping_profile_id_instead_of_shipping_profile_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_shipping_package_id_instead_of_shipping_package_identifier", $this->adminLangId), 'CONF_USE_SHIPPING_PACKAGE_ID', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_shipping_package_id_instead_of_shipping_package_identifier_in_worksheets", $this->adminLangId) . '</small>';

        $fld = $frm->addCheckBox(Labels::getLabel("LBL_Use_1_for_yes_0_for_no", $this->adminLangId), 'CONF_USE_O_OR_1', 1, array(), false, 0);
        $fld->htmlAfterField = '<br><small>' . Labels::getLabel("MSG_Use_1_for_yes_0_for_no_for_status_type_data", $this->adminLangId) . '</small>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->adminLangId));
        return $frm;
    }

    public function updateSettings()
    {
        $frm = $this->getSettingForm($this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $record = new Configurations();
        if (!$record->update($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Settings_Updated_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function bulkMedia()
    {
        $frm = $this->getbulkMediaForm($this->adminLangId);
        $this->set('action', 'bulkMedia');
        $this->set('frm', $frm);
        $this->_template->render(false, false, 'import-export/bulk-media.php');
    }

    private function getbulkMediaForm()
    {
        $frm = new Form('uploadBulkImages', array('id' => 'uploadBulkImages'));

        $fldImg = $frm->addFileUpload('', 'bulk_images', array('id' => 'bulk_images', 'accept' => '.zip'));
        $fldImg->requirement->setRequired(true);
        $fldImg->setFieldTagAttribute('onChange', '$("#uploadFileName").html(this.value)');
        $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename" id="uploadFileName">' . Labels::getLabel('LBL_Select_File_To_Upload', $this->adminLangId) . '</span>';
        $fldImg->htmlAfterField = '<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $this->adminLangId) . '</label></div>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->adminLangId));
        return $frm;
    }

    public function bulkMediaList()
    {
        $bulkImage = new UploadBulkImages();
        $srch = $bulkImage->bulkMediaFileObject();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("records", $records);
        $this->set("canEdit", $this->objPrivilege->canEditImportExport(0, true));
        $this->_template->render(false, false);
    }

    public function upload()
    {
        $frm = $this->getbulkMediaForm();
        $post = $frm->getFormDataFromArray($_FILES);

        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Data', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileName = $_FILES['bulk_images']['name'];
        $tmpName = $_FILES['bulk_images']['tmp_name'];

        $uploadBulkImgobj = new UploadBulkImages();
        $savedFile = $uploadBulkImgobj->upload($fileName, $tmpName);
        if (false === $savedFile) {
            Message::addErrorMessage($uploadBulkImgobj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH;
        $filePath = AttachedFile::FILETYPE_BULK_IMAGES_PATH . $savedFile;

        $msg = '<br>' . str_replace('{path}', '<br><b>' . $filePath . '</b>', Labels::getLabel('MSG_Your_uploaded_files_path_will_be:_{path}', $this->adminLangId));
        $msg = Labels::getLabel('MSG_Uploaded_Successfully.', $this->adminLangId) . ' ' . $msg;
        $json = [
            "msg" => $msg,
            "path" => base64_encode($path . $savedFile)
        ];
        FatUtility::dieJsonSuccess($json);
    }

    public function downloadPathsFile($path)
    {
        if (empty($path)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
        }
        $filesPathArr = UploadBulkImages::getAllFilesPath(base64_decode($path));
        if (!empty($filesPathArr) && 0 < count($filesPathArr)) {
            $headers[] = ['File Path', 'File Name'];
            $filesPathArr = array_merge($headers, $filesPathArr);
            CommonHelper::convertToCsv($filesPathArr, time() . '.csv');
            exit;
        }
        Message::addErrorMessage(Labels::getLabel('MSG_No_File_Found', $this->adminLangId));
        CommonHelper::redirectUserReferer();
    }

    public function removeDir($directory)
    {
        $directory = CONF_UPLOADS_PATH . base64_decode($directory);
        $obj = new UploadBulkImages();
        $msg = $obj->deleteSingleBulkMediaDir($directory);
        FatUtility::dieJsonSuccess($msg);
    }

    public function exportLabels()
    {
        $srch = new SearchBase(Labels::DB_TBL, 'lbl');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinTable(Language::DB_TBL, 'INNER JOIN', 'label_lang_id = language_id AND language_active = ' . applicationConstants::ACTIVE);
        $srch->addOrder('label_key', 'DESC');
        $srch->addOrder('label_lang_id', 'ASC');
        $srch->addMultipleFields(array('label_id', 'label_key', 'label_lang_id', 'label_caption'));
        $rs = $srch->getResultSet();

        $langSrch = Language::getSearchObject();
        $langSrch->doNotCalculateRecords();
        $langSrch->addMultipleFields(array('language_id', 'language_code', 'language_name'));
        $langSrch->addOrder('language_id', 'ASC');
        $langRs = $langSrch->getResultSet();
        $languages = FatApp::getDb()->fetchAll($langRs);
        $sheetData = array();

        /* Sheet Heading Row[ */
        $arr = array(Labels::getLabel('LBL_Key', $this->adminLangId));
        if ($languages) {
            foreach ($languages as $lang) {
                array_push($arr, $lang['language_code']);
            }
        }
        array_push($sheetData, $arr);
        /* ] */

        $key = '';
        $counter = 0;
        $arr = array();
        $langArr = array();

        while ($row = FatApp::getDb()->fetch($rs)) {
            if ($key != $row['label_key']) {
                if (!empty($langArr)) {
                    $arr[$counter] = array('label_key' => $key);
                    foreach ($langArr as $k => $val) {
                        if (is_array($val)) {
                            foreach ($val as $key => $v) {
                                $val[$key] = htmlentities($v);
                            }
                        }
                        $arr[$counter]['data'] = $val;
                    }
                    $counter++;
                }
                $key = $row['label_key'];
                $langArr = array();
                foreach ($languages as $lang) {
                    $langArr[$key][$lang['language_id']] = '';
                }
                $langArr[$key][$row['label_lang_id']] = $row['label_caption'];
            } else {
                $langArr[$key][$row['label_lang_id']] = $row['label_caption'];
            }
        }

        foreach ($arr as $a) {
            $sheetArr = array();
            $sheetArr = array($a['label_key']);
            if (!empty($a['data'])) {
                foreach ($a['data'] as $langId => $caption) {
                    array_push($sheetArr, html_entity_decode($caption));
                }
            }
            array_push($sheetData, $sheetArr);
        }
        CommonHelper::convertToCsv($sheetData, 'Labels_' . date("d-M-Y") . '.csv', ',');
    }

    public function importLabelsForm()
    {
        $frm = $this->getImportLabelsForm();
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    private function getImportLabelsForm()
    {
        $frm = new Form('frmImportLabels', array('id' => 'frmImportLabels'));
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Select_File_To_Upload:', $this->adminLangId), 'import_file', array('id' => 'import_file'));
        $fldImg->setFieldTagAttribute('onChange', '$(\'#importFileName\').html(this.value)');
        $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename" id="importFileName"></span>';
        $fldImg->htmlAfterField = '<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $this->adminLangId) . '</label></div>';

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Import', $this->adminLangId));

        return $frm;
    }

    public function uploadLabelsImportedFile()
    {
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_CSV_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!in_array($_FILES['import_file']['type'], CommonHelper::isCsvValidMimes())) {
            Message::addErrorMessage(Labels::getLabel("LBL_Not_a_Valid_CSV_File", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        /* All Languages[  */
        $langSrch = Language::getSearchObject();
        $langSrch->doNotCalculateRecords();
        $langSrch->addMultipleFields(array('language_id', 'language_code', 'language_name'));
        $langSrch->addOrder('language_id', 'ASC');
        $langRs = $langSrch->getResultSet();
        $languages = $db->fetchAll($langRs, 'language_code');
        /* ] */

        $csvFilePointer = fopen($_FILES['import_file']['tmp_name'], 'r');

        $firstLine = fgetcsv($csvFilePointer);
        array_shift($firstLine);
        $firstLineLangArr = $firstLine;
        $langIndexLangIds = array();
        foreach ($firstLineLangArr as $key => $langCode) {
            if (!array_key_exists($langCode, $languages)) {
                Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $langIndexLangIds[$key] = $languages[$langCode]['language_id'];
        }

        while (($line = fgetcsv($csvFilePointer)) !== false) {
            if ($line[0] != '') {
                $labelKey = array_shift($line);
                $type = Labels::TYPE_WEB;
                if (strtoupper(substr($labelKey, 0, 3)) == 'APP') {
                    $type = Labels::TYPE_APP;
                }

                foreach ($line as $key => $caption) {
                    $dataToSaveArr = array(
                        'label_key' => $labelKey,
                        'label_lang_id' => $langIndexLangIds[$key],
                        'label_caption' => $caption,
                        'label_type' => $type ,
                    );
                    $db->insertFromArray(Labels::DB_TBL, $dataToSaveArr, false, array(), array('label_caption' => $caption));
                    /* $sql = "SELECT label_key FROM ". Labels::DB_TBL ." WHERE label_key = " . $db->quoteVariable($labelKey). " AND label_lang_id = " .  $langIndexLangIds[$key];
                    $rs = $db->query($sql);
                    if ($row = $db->fetch($rs)) {
                        $db->updateFromArray(Labels::DB_TBL, array( 'label_caption' => $caption ), array('smt' => 'label_key = ? AND label_lang_id = ?', 'vals' => array( $labelKey, $langIndexLangIds[$key] ) ));
                    } else {
                        $dataToSaveArr = array(
                        'label_key'        =>    $labelKey,
                        'label_lang_id'    =>    $langIndexLangIds[$key],
                        'label_caption'    =>    $caption,
                        );
                        $db->insertFromArray(Labels::DB_TBL, $dataToSaveArr);
                    } */
                }
            }
        }

        $labelsUpdatedAt = array('conf_name' => 'CONF_LANG_LABELS_UPDATED_AT', 'conf_val' => time());
        $db->insertFromArray('tbl_configurations', $labelsUpdatedAt, false, array(), $labelsUpdatedAt);
        Message::addMessage(Labels::getLabel('LBL_Labels_data_imported/updated_Successfully', $this->adminLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }
}
