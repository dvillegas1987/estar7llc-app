<?php

class ProductSpecifics extends MyAppModel
{
    public const DB_TBL = 'tbl_product_specifics';
    public const DB_TBL_PREFIX = 'ps_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'product_id', $id);
    }

    public static function getSearchObject()
    {
        return new SearchBase(static::DB_TBL, 'ps');
    }

    public function joinProduct()
    {
        $this->joinTable(Product::DB_TBL, 'LEFT JOIN', 'ps.' . static::DB_TBL_PREFIX . 'product_id = p.product_id', 'p');
    }
}
