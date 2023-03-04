<?php

class ShippingServicesBase extends PluginBase
{
    /**
     * getSystemOrder
     *
     * @param  int $opId
     * @return array
     */
    public function getSystemOrder(int $opId): array
    {
        if (1 > $opId) {
            return [];
        }

        $srch = new OrderSearch($this->langId);
        $srch->joinOrderPaymentMethod();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderProduct();
        $srch->joinOrderProductShipping();
        $srch->joinSellerProduct();
        $srch->addCondition('op.op_id', '=', $opId);
        $srch->addMultipleFields(['order_id', 'order_user_id', 'order_date_added', 'order_payment_status', 'order_tax_charged', 'order_site_commission', 'buyer.user_name as buyer_user_name', 'buyer_cred.credential_email as buyer_email', 'buyer.user_phone_dcode as buyer_phone_dcode', 'buyer.user_phone as buyer_phone', 'order_net_amount', 'opshipping_label', 'opshipping_carrier_code', 'opshipping_service_code', 'op.*', 'op_product_tax_options', 'IFNULL(plugin_name, plugin_identifier) as plugin_name', 'selprod_product_id', 'op_selprod_title', 'op_product_name', 'sp.selprod_product_id']);
        $rs = $srch->getResultSet();
        return (array) FatApp::getDb()->fetch($rs);
    }
}
