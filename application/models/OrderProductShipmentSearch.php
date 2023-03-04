<?php

class OrderProductShipmentSearch extends SearchBase
{
    private $langId;
    private $joinOrderProduct = false;
    
    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId = 0)
    {
        parent::__construct(OrderProductShipment::DB_TBL, 'o');
        $this->langId = FatUtility::int($langId);
    }
    
    /**
     * joinOrderProduct
     *
     * @param  int $langId
     * @return void
     */
    public function joinOrderProduct(int $langId = 0)
    {
        $langId = (0 < $langId ? $langId : $this->langId);
        $this->joinTable(OrderProduct::DB_TBL, 'LEFT JOIN', OrderProductShipment::DB_TBL_PREFIX . 'op_id = op.' . OrderProduct::DB_TBL_PREFIX . 'id', 'op');
        if (0 < $langId) {
            $this->joinTable(
                OrderProduct::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'op_l.oplang_op_id = op.op_id AND op_l.oplang_lang_id = ' . $langId,
                'op_l'
            );
        }
        $this->joinOrderProduct = true;
    }
    
    /**
     * joinOrder
     *
     * @return void
     */
    public function joinOrder()
    {
        if (true === $this->joinOrderProduct) {
            $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_id = op.op_order_id', 'o');
        }
    }
}
