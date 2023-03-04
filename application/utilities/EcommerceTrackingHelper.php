<?php

class EcommerceTrackingHelper
{
    /* Function to return the JavaScript representation of a TransactionData object. */

    public static function getTransactionJs($trans)
    {
        return <<<HTML
            ga('ecommerce:addTransaction', {
              'id': '{$trans['id']}',
              'affiliation': '{$trans['affiliation']}',
              'revenue': '{$trans['revenue']}',
              'shipping': '{$trans['shipping']}',
              'tax': '{$trans['tax']}'
            });
        HTML;
    }

    /* Function to return the JavaScript representation of an ItemData object. */

    public static function getItemJs($transId, $item)
    {
        return <<<HTML
            ga('ecommerce:addItem', {
              'id': '$transId',
              'name': '{$item['name']}',
              'sku': '{$item['sku']}',              
              'price': '{$item['price']}',
              'quantity': '{$item['quantity']}'
            });
        HTML;
    }

}
