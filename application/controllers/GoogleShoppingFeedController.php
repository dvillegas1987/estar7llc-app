<?php

require_once CONF_INSTALLATION_PATH . 'vendor/autoload.php';

class GoogleShoppingFeedController extends AdvertisementFeedBaseController
{
    public const KEY_NAME = 'GoogleShoppingFeed';
    public const SCOPE = 'https://www.googleapis.com/auth/content';

    private $client;
    private $googleShoppingFeed;
    private $accessToken;
    private $adsBatchId;

    /**
     * __construct
     *
     * @param  mixed $action
     * @return void
     */
    public function __construct(string $action)
    {
        parent::__construct($action);
        $this->init();
        $this->userPrivilege->canViewAdvertisementFeed(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
    }

    /**
     * init
     *
     * @return void
     */
    private function init()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $this->googleShoppingFeed = PluginHelper::callPlugin(self::KEY_NAME, [$this->siteLangId, $userId], $error, $this->siteLangId);
        if (false === $this->googleShoppingFeed) {
            $this->setError($error, 'Seller');
        }

        if (false === $this->googleShoppingFeed->validateSettings($this->siteLangId)) {
            $this->setError('', 'Seller');
        }

        $this->settings = $this->googleShoppingFeed->getSettings();
        if (empty($this->settings)) {
            $this->setError('', 'Seller');
        }
    }

    /**
     * setError
     *
     * @param  string $msg
     * @param  string $controller
     * @param  string $action
     * @return void
     */
    private function setError(string $msg = '', string $controller = '', string $action = '')
    {
        $msg = !empty($msg) ? $msg : $this->googleShoppingFeed->getError();
        Message::addErrorMessage($msg);
        $this->redirectBack($controller, $action);
    }

    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $this->set('userData', $this->getUserMeta());
        $this->set('keyName', self::KEY_NAME);
        $this->set('pluginName', $this->settings['plugin_name']);
        $this->_template->render();
    }

    /**
     * setupConfiguration
     *
     * @return void
     */
    private function setupConfiguration(): void
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->siteLangId)); // Set your application name
        $this->client->setScopes(self::SCOPE);
        $this->client->setClientId($this->settings['client_id']);
        $this->client->setClientSecret($this->settings['client_secret']);
        $this->client->setRedirectUri(UrlHelper::generateFullUrl(static::KEY_NAME, 'getAccessToken', [], '', false));
        $this->client->setDeveloperKey($this->settings['developer_key']);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
    }

    /**
     * getAccessToken
     *
     * @return void
     */
    public function getAccessToken()
    {
        $this->setupConfiguration();

        $get = FatApp::getQueryStringData();
        if (isset($get['code'])) {
            $this->client->authenticate($get['code']);
            $this->accessToken = $this->client->getAccessToken();
            if (!empty($this->accessToken)) {
                $this->setupMerchantDetail();
            }
            $this->redirectBack();
        }
        $authUrl = $this->client->createAuthUrl();
        FatApp::redirectUser($authUrl);
    }

    /**
     * setupMerchantDetail
     *
     * @return void
     */
    private function setupMerchantDetail()
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $this->client->setAccessToken($this->accessToken);
        $service = new Google_Service_ShoppingContent($this->client);
        $authDetail = $service->accounts->authinfo();
        $accountDetail = $authDetail->accountIdentifiers;
        if (empty($accountDetail)) {
            $msg = Labels::getLabel("MSG_MERCHANT_ACCOUNT_DETAIL_NOT_FOUND", $this->siteLangId);
            $this->setError($msg, 'Seller');
        }
        $merchantId = array_shift($accountDetail)->merchantId;
        $this->updateMerchantInfo([self::KEY_NAME . '_merchantId' => $merchantId]);
    }

    /**
     * getServiceAccountForm
     *
     * @return object
     */
    private function getServiceAccountForm(): object
    {
        $frm = new Form('frmServiceAccount');
        $privateKey = $frm->addTextArea(Labels::getLabel('LBL_SERVICE_ACCOUNT_DETAIL', $this->siteLangId), 'service_account');
        $privateKey->requirements()->setRequired();
        $privateKey->htmlAfterField = $this->settings['plugin_description'];
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    /**
     * serviceAccountForm
     *
     * @return void
     */
    public function serviceAccountForm()
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $data = $this->getUserMeta();
        $frm = $this->getServiceAccountForm();
        if (!empty($data) && 0 < count($data)) {
            $frm->fill($data);
        }
        $this->set('frm', $frm);
        $this->set('keyName', self::KEY_NAME);
        $this->_template->render(false, false);
    }

    /**
     * setupServiceAccountForm
     *
     * @return void
     */
    public function setupServiceAccountForm()
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $frm = $this->getServiceAccountForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['btn_submit']);
        $this->updateMerchantInfo($post, false);
    }

    /**
     * validateBatchRequest
     *
     * @return void
     */
    private function validateBatchRequest()
    {
        $recordData = AdsBatch::getBatchesByUserId(UserAuthentication::getLoggedUserId(), $this->adsBatchId);
        $status = AdsBatch::getAttributesById($this->adsBatchId, 'adsbatch_status');
        if (1 > $this->adsBatchId || empty($recordData) || AdsBatch::STATUS_PENDING != $status) {
            $this->error = Labels::getLabel("LBL_INVALID_REQUEST", $this->siteLangId);
            return false;
        }
        return true;
    }

    /**
     * getBatchForm
     *
     * @return object
     */
    private function getBatchForm(): object
    {
        $frm = new Form('frmAdsBatch');
        $frm->addHiddenField('', 'adsbatch_id');
        $frm->addRequiredField(Labels::getLabel('LBL_BATCH_NAME', $this->siteLangId), 'adsbatch_name');
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'adsbatch_lang_id', Language::getAllNames(), '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_TARGET_COUNTRY', $this->siteLangId), 'adsbatch_target_country_id', $countriesArr, '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addDateField(Labels::getLabel('LBL_EXPIRY_DATE', $this->siteLangId), 'adsbatch_expired_on', '', array('readonly' => 'readonly'));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearForm();'));
        return $frm;
    }

    /**
     * getBindProductForm
     *
     * @return object
     */
    private function getBindProductForm(): object
    {
        $frm = new Form('frm');
        $frm->addHiddenField('', 'abprod_selprod_id');
        $frm->addHiddenField('', 'abprod_cat_id');
        $frm->addHiddenField('', 'abprod_adsbatch_id');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_PRODUCT', $this->siteLangId), 'product_name');
        $fld->requirement->setRequired(true);
        $fld = $frm->addTextBox(Labels::getLabel('LBL_GOOGLE_PRODUCT_CATEGORY', $this->siteLangId), 'google_product_category');
        $fld->requirement->setRequired(true);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_AGE_GROUP', $this->siteLangId), 'abprod_age_group', (self::KEY_NAME)::ageGroup($this->siteLangId), '', [], Labels::getLabel('LBL_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId));
        return $frm;
    }

    /**
     * batchForm
     *
     * @param  int $adsBatchId
     * @return void
     */
    public function batchForm(int $adsBatchId = 0)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $prodBatchAdsFrm = $this->getBatchForm($adsBatchId);

        if (0 < $adsBatchId) {
            $data = AdsBatch::getAttributesById($adsBatchId);
            if ($data === false) {
                LibHelper::dieJsonError($this->str_invalid_request);
            }
            $prodBatchAdsFrm->fill($data);
        }

        $this->set('frm', $prodBatchAdsFrm);
        $this->_template->render(false, false);
    }

    /**
     * bindProducts
     *
     * @param  int $adsBatchId
     * @return void
     */
    public function bindProducts(int $adsBatchId)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $adsBatchId = FatUtility::int($adsBatchId);
        $this->set('adsBatchId', $adsBatchId);
        $this->_template->render();
    }

    /**
     * bindProductForm
     *
     * @param  int $adsBatchId
     * @param  int $selProdId
     * @return void
     */
    public function bindProductForm(int $adsBatchId, int $selProdId = 0)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $this->adsBatchId = $adsBatchId;
        if (false === $this->validateBatchRequest()) {
            $this->setError($this->error, 'Seller');
        }

        $frm = $this->getBindProductForm();
        $data = ['abprod_adsbatch_id' => $this->adsBatchId];
        if (1 < $selProdId) {
            $data = AdsBatch::getBatchProdDetail($this->adsBatchId, $selProdId);
            $categoryArr = $this->getProductCategory(true);
            $selProdData = SellerProduct::getSelProdDataById($selProdId, $this->siteLangId);
            $data['google_product_category'] = $categoryArr[$data['abprod_cat_id']];
            $data['product_name'] = $selProdData['selprod_title'];
        }
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    /**
     * setupBatch
     *
     * @return void
     */
    public function setupBatch()
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $frm = $this->getBatchForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        $this->adsBatchId = $post['adsbatch_id'];
        if (0 < $this->adsBatchId) {
            if (false === $this->validateBatchRequest()) {
                LibHelper::dieJsonError($this->error);
            }
        }
        unset($post['adsbatch_id']);
        $post['adsbatch_user_id'] = UserAuthentication::getLoggedUserId();
        $adsBatchObj = new AdsBatch($this->adsBatchId);
        $adsBatchObj->assignValues($post);

        if (!$adsBatchObj->save()) {
            LibHelper::dieJsonError($adsBatchObj->getError());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_ADS_BATCH_SETUP_SUCCESSFULLY', $this->siteLangId));
    }

    /**
     * setupProductsToBatch
     *
     * @return void
     */
    public function setupProductsToBatch()
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $frm = $this->getBindProductForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        $productId = SellerProduct::getAttributesById($post['abprod_selprod_id'], 'selprod_product_id');
        $productIdentifier = strtoupper(Product::getAttributesById($productId, 'product_identifier'));
        $productIdentifier = explode(' ', $productIdentifier);
        $post['abprod_item_group_identifier'] = $productIdentifier[0] . $productId;

        unset($post['btn_submit'], $post['product_name'], $post['btn_clear'], $post['google_product_category']);
        $db = FatApp::getDb();
        if (!$db->insertFromArray(AdsBatch::DB_TBL_BATCH_PRODS, $post, false, array(), $post)) {
            LibHelper::dieJsonError($db->getError());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_ADS_BATCH_SETUP_SUCCESSFULLY', $this->siteLangId));
    }

    /**
     * search
     *
     * @return void
     */
    public function search()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);

        $srch = AdsBatch::getSearchObject();
        $attr = [
            'adsbatch_id',
            'adsbatch_name',
            'adsbatch_lang_id',
            'adsbatch_target_country_id',
            'adsbatch_expired_on',
            'adsbatch_synced_on',
            'adsbatch_status',
        ];
        $srch->addMultipleFields($attr);
        $srch->addCondition(AdsBatch::DB_TBL_PREFIX . 'user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition(AdsBatch::DB_TBL_PREFIX . 'status', '!=', AdsBatch::STATUS_DELETED);
        $srch->setPageNumber($page);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);
        $this->set("arrListing", $arrListing);

        $this->set('keyName', self::KEY_NAME);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->set('canEdit', $this->userPrivilege->canEditAdvertisementFeed(0, true));
        $this->_template->render(false, false);
    }

    /**
     * deleteBatch
     *
     * @param  int $adsBatchId
     * @return void
     */
    public function deleteBatch(int $adsBatchId)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $this->adsBatchId = $adsBatchId;
        if (false === $this->validateBatchRequest()) {
            LibHelper::dieJsonError($this->error);
        }

        $adsBatchObj = new AdsBatch($this->adsBatchId);
        $adsBatchObj->assignValues(['adsbatch_status' => AdsBatch::STATUS_DELETED]);

        if (!$adsBatchObj->save()) {
            LibHelper::dieJsonError($adsBatchObj->getError());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_SUCCESSFULLY_DELETED', $this->siteLangId));
    }

    /**
     * getBatchProductsObj
     *
     * @return void
     */
    private function getBatchProductsObj()
    {
        $srch = AdsBatch::getSearchObject(true);
        $srch->addCondition(AdsBatch::DB_TBL_BATCH_PRODS_PREFIX . 'adsbatch_id', '=', $this->adsBatchId);
        $srch->addCondition(AdsBatch::DB_TBL_PREFIX . 'user_id', '=', UserAuthentication::getLoggedUserId());
        return $srch;
    }

    /**
     * searchProducts
     *
     * @param  int $adsBatchId
     * @return void
     */
    public function searchProducts(int $adsBatchId)
    {
        $this->adsBatchId = $adsBatchId;
        if (1 > $this->adsBatchId) {
            LibHelper::dieJsonError(Labels::getLabel('LBL_INVALID_REQUEST', $this->siteLangId));
        }

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');

        $attr = [
            'abprod_adsbatch_id',
            'abprod_selprod_id',
            'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
            'product_identifier',
            'adsbatch_name',
            'abprod_cat_id',
            'abprod_age_group',
            'abprod_item_group_identifier',
        ];

        $srch = $this->getBatchProductsObj();
        $srch->addMultipleFields($attr);

        if (!empty($keyword)) {
            $srch->addCondition(AdsBatch::DB_TBL_PREFIX . 'name', 'LIKE', '%' . $keyword . '%');
        }
        $srch->setPageNumber($page);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);
        $this->set("arrListing", $arrListing);

        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->set('catIdArr', $this->getProductCategory(true));
        $this->_template->render(false, false);
    }

    /**
     * unlinkProduct
     *
     * @param  int $adsBatchId
     * @param  int $selProdId
     * @param  bool $return
     * @return void
     */
    public function unlinkProduct(int $adsBatchId, int $selProdId, bool $return = false)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $this->adsBatchId = $adsBatchId;
        if (false === $this->validateBatchRequest()) {
            LibHelper::dieJsonError($this->error);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(AdsBatch::DB_TBL_BATCH_PRODS, ['smt' => 'abprod_adsbatch_id = ? AND abprod_selprod_id = ?', 'vals' => [$this->adsBatchId, $selProdId]])) {
            LibHelper::dieJsonError($db->getError());
        }

        if (true == $return) {
            return true;
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_SUCCESSFULLY_DELETED', $this->siteLangId));
    }

    /**
     * unlinkProducts
     *
     * @param  mixed $adsBatchId
     * @return void
     */
    public function unlinkProducts(int $adsBatchId)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $adsBatchId = FatUtility::int($adsBatchId);
        $sellerProducts = FatApp::getPostedData('selprod_ids');
        if (1 > $adsBatchId || !is_array($sellerProducts) || 1 > count($sellerProducts)) {
            LibHelper::dieJsonError(Labels::getLabel("LBL_INVALID_REQUEST", $this->siteLangId));
        }

        foreach ($sellerProducts as $selProdId) {
            $this->unlinkProduct($adsBatchId, $selProdId, true);
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_SUCCESSFULLY_DELETED', $this->siteLangId));
    }

    /**
     * getProductCategory
     *
     * @param  bool $returnFullArray
     * @return void
     */
    public function getProductCategory(bool $returnFullArray = false)
    {
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $data = $this->googleShoppingFeed->getProductCategory($keyword, $returnFullArray);
        if (true === $returnFullArray) {
            return $data;
        }
        CommonHelper::jsonEncodeUnicode($data, true);
    }

    /**
     * getData
     *
     * @return void
     */
    private function getData()
    {
        $db = FatApp::getDb();
        $srch = $this->getBatchProductsObj();

        $srch->addMultipleFields(
            [
                'selprod_id', 'selprod_title', 'selprod_stock', 'selprod_condition', 'selprod_price', 'selprod_available_from', 'product_id', 'product_description', 'product_upc', 'language_code', 'country_code', 'IFNULL(brand_name, brand_identifier) as brand_name', 'abprod_item_group_identifier', 'adsbatch_expired_on', 'abprod_cat_id'
            ]
        );
        $rs = $srch->getResultSet();
        $productData = $db->fetchAll($rs);
        if (empty($productData)) {
            LibHelper::dieJsonError(Labels::getLabel("MSG_PLEASE_ADD_ATLEAST_ONE_PRODUCT_TO_THE_BATCH", $this->siteLangId));
        }

        foreach ($productData as &$prodDetail) {
            $srch = new SearchBase(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'spo');
            $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
            $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = ' . $this->siteLangId, 'ov_lang');
            $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
            $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = ' . $this->siteLangId, 'o_lang');
            $srch->addMultipleFields(['optionvalue_identifier', 'option_is_color', 'option_name']);
            $srch->addCondition('selprodoption_selprod_id', '=', $prodDetail['selprod_id']);
            $rs = $srch->getResultSet();
            $prodDetail['optionsData'] = $db->fetchAll($rs);
            $prodDetail['selprod_condition'] = (Product::getConditionArr($this->siteLangId))[$prodDetail['selprod_condition']];
            $prodDetail['selprod_stock'] = (0 < $prodDetail['selprod_stock'] ? "in stock" : 'out of stock');
        }
        return $productData;
    }

    /**
     * publishBatch
     *
     * @param  int $adsBatchId
     * @return void
     */
    public function publishBatch(int $adsBatchId)
    {
        $this->userPrivilege->canEditAdvertisementFeed();
        $this->adsBatchId = $adsBatchId;
        if (false === $this->validateBatchRequest()) {
            LibHelper::dieJsonError($this->error);
        }

        $productData = $this->getData();
        $data = [
            'batchId' => $this->adsBatchId,
            'currency_code' => strtoupper(Currency::getAttributesById($this->siteCurrencyId, 'currency_code')),
            'data' => $productData
        ];

        $response = $this->googleShoppingFeed->publishBatch($data);
        if (false === $response['status'] || Plugin::RETURN_FALSE === $response['status'] ) {
            LibHelper::dieJsonError($this->googleShoppingFeed->getError());
        }
        
        $dataToUpdate = [
            'adsbatch_status' => AdsBatch::STATUS_PUBLISHED,
            'adsbatch_synced_on' => date('Y-m-d H:i:s')
        ];
        if (false === AdsBatch::updateDetail($this->adsBatchId, $dataToUpdate)) {
            LibHelper::dieJsonError(Labels::getLabel("MSG_UNABLE_TO_UPDATE", $this->siteLangId));
        }

        FatUtility::dieJsonSuccess($response['msg']);
    }
}
