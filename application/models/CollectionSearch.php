<?php

class CollectionSearch extends SearchBase
{
    private $langId;

    private $joinCollectionRecords = false;

    private $commonLangId;
    
    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $this->langId = $langId;

        parent::__construct(Collections::DB_TBL, 'c');
        /* $this->commonLangId = CommonHelper::getLangId();
        $this->langId = FatUtility::int( $langId ); */

        if ($this->langId > 0) {
            $this->joinTable(
                Collections::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'c_l.' . Collections::DB_TBL_LANG_PREFIX . 'collection_id = c.' . Collections::tblFld('id') . ' and
			c_l.' . Collections::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->langId,
                'c_l'
            );
        }

        $this->addCondition(Collections::tblFld('active'), '=', applicationConstants::ACTIVE);
        $this->addCondition(Collections::tblFld('deleted'), '=', 0);
    }
    
    /**
     * joinCollectionRecords
     *
     * @return void
     */
    public function joinCollectionRecords()
    {
        $this->joinCollectionRecords = true;

        $this->joinTable(
            Collections::DB_TBL_COLLECTION_TO_RECORDS,
            'LEFT OUTER JOIN',
            'ctsp.' . Collections::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id = c.' . Collections::tblFld('id'),
            'ctsp'
        );
    }
       
    /**
     * joinSellerProductsForPrice
     *
     * @param  int $langId
     * @param  string $forDate
     * @return void
     */
    public function joinSellerProductsForPrice(int $langId = 0, string $forDate = ''): void
    {
        $langId = (0 < $langId) ? $langId : $this->langId;
        
        if (!$this->joinCollectionRecords) {
            trigger_error(Labels::getLabel('ERR_joinCollectionRecords_must_be_joined.', $this->commonLangId), E_USER_ERROR);
        }

        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        if ('' == $forDate) {
            $forDate = $now;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', SellerProduct::DB_TBL_PREFIX . 'id = ' . Collections::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'selprod_id', 'sprods');

        if ($langId > 0) {
            $this->joinTable(SellerProduct::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'sprods_l.selprodlang_selprod_id = ' . SellerProduct::DB_TBL_PREFIX . 'id AND sprods_l.selprodlang_lang_id = ' . $langId, 'sprods_l');
        }

        $this->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN',
            'splprice_selprod_id = selprod_id AND \'' . $forDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );
    }
    
    /**
     * joinProducts
     *
     * @param  int $langId
     * @return void
     */
    public function joinProducts(int $langId = 0): void
    {
        $langId = (0 < $langId) ? $langId : $this->langId;

        $this->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', Product::DB_TBL_PREFIX . 'id = ' . SellerProduct::DB_TBL_PREFIX . 'product_id', 'p');

        if ($langId > 0) {
            $this->joinTable(Product::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'p_l.productlang_product_id = ' . Product::tblFld('id') . ' AND p_l.productlang_lang_id = ' . $langId, 'p_l');
        }
    }
}
