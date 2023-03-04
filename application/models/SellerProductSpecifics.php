<?php

class SellerProductSpecifics extends MyAppModel
{
    public const DB_TBL = 'tbl_seller_product_specifics';
    public const DB_TBL_PREFIX = 'sps_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'selprod_id', $id);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getSearchObject()
    {
        return new SearchBase(static::DB_TBL, 'sps');
    }

    public function joinSellerProduct()
    {
        $this->joinTable(SellerProduct::DB_TBL, 'LEFT JOIN', 'sps.' . static::DB_TBL_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
    }
}
