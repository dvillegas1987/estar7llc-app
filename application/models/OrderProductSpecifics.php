<?php

class OrderProductSpecifics extends MyAppModel
{
    public const DB_TBL = 'tbl_order_product_specifics';
    public const DB_TBL_PREFIX = 'ops_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'op_id', $id);
    }

    public static function getSearchObject()
    {
        return new SearchBase(static::DB_TBL, 'ops');
    }

    public function joinOrderProduct()
    {
        $this->joinTable(OrderProduct::DB_TBL, 'LEFT JOIN', 'ops.' . static::DB_TBL_PREFIX . 'op_id = ops.ops_op_id', 'op');
    }
}
