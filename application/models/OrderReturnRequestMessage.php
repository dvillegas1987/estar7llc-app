<?php

class OrderReturnRequestMessage extends MyAppModel
{
    public const DB_TBL = 'tbl_order_return_request_messages';
    public const DB_TBL_PREFIX = 'orrmsg_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }
}
