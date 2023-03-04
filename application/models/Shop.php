<?php

class Shop extends MyAppModel
{
    public const DB_TBL = 'tbl_shops';
    public const DB_TBL_PREFIX = 'shop_';

    public const DB_TBL_LANG = 'tbl_shops_lang';
    public const DB_TBL_LANG_PREFIX = 'shoplang_';

    public const DB_TBL_SHOP_FAVORITE = 'tbl_user_favourite_shops';
    public const DB_TBL_SHOP_THEME_COLOR = 'tbl_shops_to_theme';

    public const FILETYPE_SHOP_LOGO = 1;
    public const FILETYPE_SHOP_BANNER = 2;
    public const TEMPLATE_ONE = 10001;
    public const TEMPLATE_TWO = 10002;
    public const TEMPLATE_THREE = 10003;
    public const TEMPLATE_FOUR = 10004;
    public const TEMPLATE_FIVE = 10005;

    public const SHOP_VIEW_ORGINAL_URL = 'shops/view/';
    public const SHOP_REVIEWS_ORGINAL_URL = 'reviews/shop/';
    public const SHOP_POLICY_ORGINAL_URL = 'shops/policy/';
    public const SHOP_SEND_MESSAGE_ORGINAL_URL = 'shops/send-message/';
    public const SHOP_TOP_PRODUCTS_ORGINAL_URL = 'shops/top-products/';
    public const SHOP_COLLECTION_ORGINAL_URL = 'shops/collection/';

    public const USE_SHOP_POLICY = 1;

    public const SHOP_PRODUCTS_COUNT_AT_HOMEPAGE = 2;

    private $userId = 0;
    private $langId = 0;
    private $active = null;
    private $data = null;

    /**
     * __construct
     *
     * @param  int $shopId
     * @param  int $userId
     * @return void
     */
    public function __construct(int $shopId, int $userId = 0, int $langId = 0)
    {
        if (0 < $shopId) {
            $this->userId = $this->getUserId();
        }

        if (1 > $shopId && 0 < $userId) {
            $this->userId = $userId;
            $shopId = $this->getIdFromUserId();
        }

        $this->langId = $langId;

        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $shopId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject($isActive = true, $langId = 0, $joinSpecifics = false)
    {
        $langId = FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL, 's');

        if ($isActive == true) {
            $srch->addCondition(static::tblFld('active'), '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                's_l.' . static::DB_TBL_LANG_PREFIX . 'shop_id = s.' . static::tblFld('id') . ' and
                s_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                's_l'
            );
        }

        if (true === $joinSpecifics) {
            $srch->joinTable(
                ShopSpecifics::DB_TBL,
                'LEFT OUTER JOIN',
                'spec.' . ShopSpecifics::DB_TBL_PREFIX . 'shop_id = s.' . static::tblFld('id'),
                'spec'
            );
        }

        return $srch;
    }

    public static function getAttributesByUserId($userId, $attr = null, $isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $userId = FatUtility::int($userId);

        $db = FatApp::getDb();
        $srch = static::getSearchObject($isActive, $langId, true);
        $srch->addCondition(static::tblFld('user_id'), '=', $userId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }
        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public static function getAttributesById($recordId, $attr = null, $joinSpecifics = false)
    {
        $recordId = FatUtility::int($recordId);
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL, 'ts');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(static::tblFld('id'), '=', $recordId);

        if (true === $joinSpecifics) {
            $srch->joinTable(
                ShopSpecifics::DB_TBL,
                'LEFT OUTER JOIN',
                'ss.' . ShopSpecifics::DB_TBL_PREFIX . 'shop_id = ts.' . static::tblFld('id'),
                'ss'
            );
        }

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public static function getProdCategoriesObj($userId, $siteLangId, $shopId = 0, $prodcat_id = 0)
    {
        $userId = FatUtility::int($userId);
        $prodcat_id = FatUtility::int($prodcat_id);
        $shopId = FatUtility::int($shopId);

        $srch = new ProductSearch();
        $srch->joinSellerProducts();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory($siteLangId);

        $srch->addCondition('selprod_user_id', '=', $userId);
        if ($shopId > 0) {
            $srch->addCondition('shop_id', '=', $shopId);
        }

        if ($prodcat_id > 0) {
            $srch->addCondition('prodcat_id', '=', $prodcat_id);
        }
        $srch->addGroupBy('prodcat_id');
        $srch->addMultipleFields(array('prodcat_id', 'ifnull(prodcat_name,prodcat_identifier) as prodcat_name', 'shop_id'));
        return $srch;
    }

    public static function getShopAddress($shop_id, $isActive = true, $langId = 0, $attr = array())
    {
        $shop_id = FatUtility::int($shop_id);
        $langId = FatUtility::int($langId);
        $db = FatApp::getDb();
        $srch = static::getSearchObject($isActive, $langId);
        $srch->addCondition(static::tblFld('id'), '=', $shop_id);
        $srch->joinTable(States::DB_TBL, 'LEFT JOIN', 's.shop_state_id=ss.state_id and ss.state_active=' . applicationConstants::ACTIVE, 'ss');
        $srch->joinTable(Countries::DB_TBL, 'LEFT JOIN', 's.shop_country_id=sc.country_id and sc.country_active=' . applicationConstants::ACTIVE, 'sc');

        if (0 < $langId) {
            $srch->joinTable(States::DB_TBL_LANG, 'LEFT JOIN', 'ss_l.statelang_state_id=ss.state_id and ss_l.statelang_lang_id=' . $langId, 'ss_l');
        }

        if ($isActive) {
            $srch->addCondition('s.shop_active', '=', $isActive);
        }
        $srch->addCondition('s.shop_id', '=', $shop_id);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }
        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public static function getFilterSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox('', 'keyword');
        $frm->addHiddenField('', 'shop_id');
        $frm->addHiddenField('', 'join_price');
        $frm->addSubmitButton('', 'btnProductSrchSubmit', '');
        return $frm;
    }

    private function _rewriteUrl($keyword, $type = 'shop', $collectionId = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }
        $originalUrl = $this->getRewriteOriginalUrl($type, $collectionId);        
        $seoUrl = $this->sanitizeSeoUrl($keyword,$type);
        
        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);
        return UrlRewrite::update($originalUrl, $customUrl);
    }
    
    private function getRewriteOriginalUrl($type = 'shop', $collectionId = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }
        switch (strtolower($type)) {
            case 'top-products':
                $originalUrl = Shop::SHOP_TOP_PRODUCTS_ORGINAL_URL . $this->mainTableRecordId;
                break;
            case 'reviews':
                $originalUrl = Shop::SHOP_REVIEWS_ORGINAL_URL . $this->mainTableRecordId;
                break;
            case 'contact':
                $originalUrl = Shop::SHOP_SEND_MESSAGE_ORGINAL_URL . $this->mainTableRecordId;
                break;
            case 'policy':
                $originalUrl = Shop::SHOP_POLICY_ORGINAL_URL . $this->mainTableRecordId;
                break;
            case 'collection':
                $originalUrl = Shop::SHOP_COLLECTION_ORGINAL_URL . $this->mainTableRecordId . '/' . $collectionId;
                break;
            default:
                $originalUrl = Shop::SHOP_VIEW_ORGINAL_URL . $this->mainTableRecordId;
                break;
        }
        return $originalUrl;
    }

    public function sanitizeSeoUrl($keyword, $type = 'shop')
    {
        $seoUrl = CommonHelper::seoUrl($keyword);
        switch (strtolower($type)) {
            case 'top-products':                
                $seoUrl = preg_replace('/-top-products$/', '', $seoUrl);
                $seoUrl .= '-top-products';
                break;
            case 'reviews':               
                $seoUrl = preg_replace('/-reviews$/', '', $seoUrl);
                $seoUrl .= '-reviews';
                break;
            case 'contact':                
                $seoUrl = preg_replace('/-contact$/', '', $seoUrl);
                $seoUrl .= '-contact';
                break;
            case 'policy':                
                $seoUrl = preg_replace('/-policy$/', '', $seoUrl);
                $seoUrl .= '-policy';
                break;
            case 'collection':               
                $shopUrl = static::getRewriteCustomUrl($this->mainTableRecordId);
                $seoUrl = preg_replace('/-' . $shopUrl . '$/', '', $seoUrl);
                $seoUrl .= '-' . $shopUrl;
                break;
            default:                
                break;
        }
        return $seoUrl;        
    }

    public function setupCollectionUrl($keyword, $collectionId)
    {
        return $this->_rewriteUrl($keyword, 'collection', $collectionId);
    }

    public function rewriteUrlShop($keyword)
    {
        return $this->_rewriteUrl($keyword);
    }
    
    public function getRewriteShopOriginalUrl(){
        return $this->getRewriteOriginalUrl('shop');
    }

    public function rewriteUrlReviews($keyword)
    {
        return $this->_rewriteUrl($keyword, 'reviews');
    }

    public function rewriteUrlTopProducts($keyword)
    {
        return $this->_rewriteUrl($keyword, 'top-products');
    }

    public function rewriteUrlContact($keyword)
    {
        return $this->_rewriteUrl($keyword, 'contact');
    }

    public function rewriteUrlpolicy($keyword)
    {
        return $this->_rewriteUrl($keyword, 'policy');
    }

    /**
     * setFavorite
     *
     * @param  int $userId
     * @return bool
     */
    public function setFavorite(int $userId): bool
    {
        if (1 > $this->mainTableRecordId || 1 > $userId) {
            return false;
        }

        $data_to_save = array('ufs_user_id' => $userId, 'ufs_shop_id' => $this->mainTableRecordId);
        $data_to_save_on_duplicate = array('ufs_shop_id' => $this->mainTableRecordId);
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_SHOP_FAVORITE, $data_to_save, false, array(), $data_to_save_on_duplicate)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    /**
     * getRewriteCustomUrl
     *
     * @param  int $shopId
     * @return string
     */
    public static function getRewriteCustomUrl(int $shopId = 0): string
    {
        $db = FatApp::getDb();
        $shopOriginalUrl = 'shops/view/' . $shopId;
        $urlSrch = UrlRewrite::getSearchObject();
        $urlSrch->doNotCalculateRecords();
        $urlSrch->doNotLimitRecords();
        $urlSrch->addCondition('urlrewrite_original', '=', $shopOriginalUrl);
        $urlSrch->addFld('urlrewrite_custom');
        $rs = $urlSrch->getResultSet();
        $row = $db->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        return $row['urlrewrite_custom'];
    }

    /**
     * getName
     *
     * @param  int $shopId
     * @param  int $langId
     * @param  bool $isActive
     * @return string
     */
    public static function getName(int $shopId, int $langId = 0, bool $isActive = true): string
    {
        if (1 > $shopId) {
            return false;
        }

        $srch = static::getSearchObject($isActive, $langId);
        $srch->addMultipleFields(array('IFNULL(shop_name, shop_identifier) as shop_name'));
        $srch->addCondition('shop_id', '=', $shopId);
        $srch->setPageSize(1);
        $shopRs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($shopRs);
        if ($row) {
            return $row['shop_name'];
        }

        return false;
    }

    /**
     * isActive
     *
     * @return int
     */
    public function isActive(): int
    {
        if (1 > $this->mainTableRecordId) {
            return 0;
        }
        if (null != $this->active) {
            return $this->active;
        }

        if (null != $this->data) {
            return $this->active = $this->data['shop_active'];
        }

        $this->getData();

        if (!empty($this->data)) {
            return $this->active = $this->data['shop_active'];
        }

        return 0;
    }

    /**
     * getData
     *
     * @return array
     */
    public function getData(): array
    {
        if (1 > $this->mainTableRecordId) {
            trigger_error('Shop instance not initialized!', E_USER_ERROR);
        }

        if (null == $this->data || empty($this->data)) {
            $this->setData();
        }

        return $this->data;
    }

    /**
     * setData
     *
     * @return void
     */
    private function setData(): void
    {
        if (1 > $this->mainTableRecordId) {
            trigger_error('Shop instance not initialized!', E_USER_ERROR);
        }

        if (null != $this->data && !empty($this->data)) {
            return;
        }

        $this->data = self::getAttributesById($this->mainTableRecordId);
    }

    /**
     * getIdFromUserId
     *
     * @return int
     */
    private function getIdFromUserId(): int
    {
        if (0 < $this->mainTableRecordId) {
            return  $this->mainTableRecordId;
        }

        if (1 > $this->userId) {
            return 0;
        }

        return self::getAttributesByUserId($this->userId, 'shop_id');
    }

    /**
     * getUserId
     *
     * @return int
     */
    private function getUserId(): int
    {
        if (1 > $this->mainTableRecordId) {
            return  0;
        }

        if (0 < $this->userId) {
            return $this->userId;
        }

        if (null != $this->data) {
            return $this->userId = $this->data['shop_user_id'];
        }

        $this->getData();

        if (!empty($this->data)) {
            return $this->userId = $this->data['shop_user_id'];
        }

        return 0;
    }
}
