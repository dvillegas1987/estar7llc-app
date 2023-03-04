<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$shippingHanldedBySeller = isset($shippingHanldedBySeller) ? $shippingHanldedBySeller : 0;

$str = '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">
    <tr>
    <td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">' . Labels::getLabel('LBL_Product', $siteLangId) . '</td>
    <td width="10%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;">' . Labels::getLabel('L_Qty', $siteLangId) . '</td>
    <td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">' . Labels::getLabel('LBL_Price', $siteLangId) . '</td>';

if ($shippingHanldedBySeller) {
    $str .= '<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">' . Labels::getLabel('LBL_Shipping', $siteLangId) . '</td>';
}
$str .= '<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">' . Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId) .
    '</td>';

if ($orderProducts['op_tax_collected_by_seller']) {
    $str .= '<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">' . Labels::getLabel('LBL_Tax_Charges', $siteLangId) . '</td>';
}

$str .= '<td width="20%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">' . Labels::getLabel('LBL_Total', $siteLangId) . '</td>
    </tr>';

$opCustomerBuyingPrice = CommonHelper::orderProductAmount($orderProducts, 'CART_TOTAL');

$shippingPrice = 0;
if ($shippingHanldedBySeller) {
    $shippingPrice = CommonHelper::orderProductAmount($orderProducts, 'SHIPPING');
}
$volumeDiscount = CommonHelper::orderProductAmount($orderProducts, 'VOLUME_DISCOUNT');
//$rewardPoints = CommonHelper::orderProductAmount($orderProducts,'REWARDPOINT');
//$discountTotal = CommonHelper::orderProductAmount($orderProducts,'DISCOUNT');

$taxCharged = 0;
$taxOptions = array();
if ($orderProducts['op_tax_collected_by_seller']) {
    $taxOptions = $orderProducts['taxOptions'];
    $taxCharged = CommonHelper::orderProductAmount($orderProducts, 'TAX');
}
$netAmount =  CommonHelper::orderProductAmount($orderProducts, 'NETAMOUNT', false, $userType);

$skuCodes = $orderProducts["op_selprod_sku"];
$options = $orderProducts['op_selprod_options'];
$roundingOff = $orderProducts['op_rounding_off'];

$total =  ($opCustomerBuyingPrice + $shippingPrice + $taxCharged - abs($volumeDiscount) + $roundingOff);

$prodOrBatchUrl = 'javascript:void(0)';
/* if($orderProducts["op_is_batch"]){
        $prodOrBatchUrl  = UrlHelper::generateFullUrl('products','batch',array($orderProducts["op_selprod_id"]),"/");
    }else{
        $prodOrBatchUrl  = UrlHelper::generateFullUrl('products','view',array($orderProducts["op_selprod_id"]),"/");
    }     */

$str .= '<tr>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
            <a href="' . $prodOrBatchUrl . '" style="font-size:13px; color:#333;">' . $orderProducts["op_product_name"] . '</a>';
if (!empty($orderProducts["op_brand_name"])) {
    $str .= '<br/>' . Labels::getLabel('Lbl_Brand', $siteLangId) . ':' . $orderProducts["op_brand_name"];
}
$str .= '<br/>' . Labels::getLabel('Lbl_Sold_By', $siteLangId) . ':' . $orderProducts["op_shop_name"] . '<br/>' . $options . '
            </td>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">' . $orderProducts['op_qty'] . '</td>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($orderProducts["op_unit_price"]) . '</td>';
if ($shippingHanldedBySeller) {
    $str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($shippingPrice) . '</td>';
}
$str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($volumeDiscount) . '</td>';

if ($orderProducts['op_tax_collected_by_seller']) {
    if (empty($taxOptions)) {
        $str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($taxCharged) . '</td>';
    } else {
        $taxChargedTxt = '';
        foreach ($taxOptions as $key => $val) {
            $taxChargedTxt .= '<p style="color:#333"><strong>' . CommonHelper::displayTaxPercantage($val, true) . ': </strong>' . CommonHelper::displayMoneyFormat($val['value']) . '</p>';
        }
        $str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . $taxChargedTxt . '</td>';
    }
}
$str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($total) . '</td>
        </tr>';

/*     $str .= '<tr><td colspan="4" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('L_TOTAL', $siteLangId).'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($total).'</td></tr>'; */

if ($orderProducts["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
    $fromTime = date('H:i', strtotime($orderProducts["opshipping_time_slot_from"]));
    $toTime = date('H:i', strtotime($orderProducts["opshipping_time_slot_to"]));
    $pickupDateAndTime =  FatDate::format($orderProducts["opshipping_date"]) . ' ' . $fromTime . ' - ' . $toTime;
    $str .= '<tr><td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="left">' . Labels::getLabel('LBL_Pickup_Date', $siteLangId) . ': ' . $pickupDateAndTime . '</td></tr>';
}


$colCount = 6;
if (!$shippingHanldedBySeller) {
    $colCount = $colCount - 1;
}

if (!$orderProducts['op_tax_collected_by_seller']) {
    $colCount = $colCount - 1;
}

$str .= '<tr><td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . Labels::getLabel('L_CART_TOTAL_(_QTY_*_Product_price_)', $siteLangId) . '</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($opCustomerBuyingPrice) . '</td></tr>';

if ($shippingPrice > 0 && $shippingHanldedBySeller) {
    $str .= '<tr>
        <td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . Labels::getLabel('LBL_SHIPPING', $siteLangId) . '</td>
        <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($shippingPrice) . '</td>
        </tr>';
}

if ($taxCharged > 0 && $orderProducts['op_tax_collected_by_seller'] > 0) {
    if (empty($taxOptions)) {
        $str .= '<tr>
            <td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . Labels::getLabel('LBL_Tax', $siteLangId) . '</td>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($taxCharged) . '</td>
            </tr>';
    } else {
        foreach ($taxOptions as $key => $val) {
            $str .= '<tr>
                <td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayTaxPercantage($val) . '</td>
                <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($val['value']) . '</td>
                </tr>';
        }
    }
}
/* if ( $discountTotal ){
    $str.='<tr>
        <td colspan="'.$colCount.'" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Discount',$siteLangId).'</td>
        <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($discountTotal).'</td>
        </tr>';
    } */

if ($volumeDiscount) {
    $str .= '<tr>
        <td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId) . '</td>
        <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($volumeDiscount) . '</td>
        </tr>';
}

/* if ( $rewardPoints ){
    $str.='<tr>
        <td colspan="'.$colCount.'" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Reward_Point_Discount',$siteLangId).'</td>
        <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($rewardPoints).'</td>
        </tr>';
    } */

if ($orderProducts['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
    $str .= '</table>';
    echo $str;
} else {
    if (0 != $roundingOff) {
        $roundingLabel = (0 < $roundingOff) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId);
        $str .= '<tr>
    <td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . $roundingLabel . '</td>
    <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">' . CommonHelper::displayMoneyFormat($roundingOff) . '</td></tr>';
    }

    $str .= '<tr>
    <td colspan="' . $colCount . '" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>' . Labels::getLabel('LBL_ORDER_TOTAL', $siteLangId) . '</strong></td>
    <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProducts, 'NETAMOUNT', false, $userType)) . '</strong></td></tr>';


    $billingInfo = $billingAddress['oua_name'] . '<br>';
    if ($billingAddress['oua_address1'] != '') {
        $billingInfo .= $billingAddress['oua_address1'] . '<br>';
    }

    if ($billingAddress['oua_address2'] != '') {
        $billingInfo .= $billingAddress['oua_address2'] . '<br>';
    }

    if ($billingAddress['oua_city'] != '') {
        $billingInfo .= $billingAddress['oua_city'] . ',';
    }

    if ($billingAddress['oua_zip'] != '') {
        $billingInfo .= $billingAddress['oua_state'];
    }

    if ($billingAddress['oua_zip'] != '') {
        $billingInfo .= '-' . $billingAddress['oua_zip'];
    }

    if ($billingAddress['oua_phone'] != '') {
        $billingInfo .= '<br>' . ValidateElement::formatDialCode($billingAddress['oua_phone_dcode']) . $billingAddress['oua_phone'];
    }

    $str .= '</table><br/><br/><table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;"><tr><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;"  bgcolor="#f0f0f0"><strong>' . Labels::getLabel('LBL_Order_Billing_Details', $siteLangId) . '</strong></td>';
    if (!empty($shippingAddress)) {
        $str .= '<td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" bgcolor="#f0f0f0"><strong>' . Labels::getLabel('L_Order_Shipping_Details', $siteLangId) . '</strong></td>';
    }
    if (!empty($orderProducts['pickupAddress'])) {
        $str .= '<td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" bgcolor="#f0f0f0"><strong>' . Labels::getLabel('L_Order_Pickup_Details', $siteLangId) . '</strong></td>';
    }
    $str .= '</tr><tr><td valign="top" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" >' . $billingInfo . '</td>';

    if (!empty($shippingAddress)) {
        $shippingInfo = $shippingAddress['oua_name'] . '<br>';
        if ($shippingAddress['oua_address1'] != '') {
            $shippingInfo .= $shippingAddress['oua_address1'] . '<br>';
        }

        if ($shippingAddress['oua_address2'] != '') {
            $shippingInfo .= $shippingAddress['oua_address2'] . '<br>';
        }

        if ($shippingAddress['oua_city'] != '') {
            $shippingInfo .= $shippingAddress['oua_city'] . ',';
        }

        if ($shippingAddress['oua_zip'] != '') {
            $shippingInfo .= $shippingAddress['oua_state'];
        }

        if ($shippingAddress['oua_zip'] != '') {
            $shippingInfo .= '-' . $shippingAddress['oua_zip'];
        }

        if ($shippingAddress['oua_phone'] != '') {
            $shippingInfo .= '<br>' . ValidateElement::formatDialCode($shippingAddress['oua_phone_dcode']) . $shippingAddress['oua_phone'];
        }
        $str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">' . $shippingInfo . '</td>';
    }

    if (!empty($orderProducts['pickupAddress'])) {
        $pickUpAddressInfo = $orderProducts['pickupAddress']['oua_name'] . '<br>';
        if ($orderProducts['pickupAddress']['oua_address1'] != '') {
            $pickUpAddressInfo .= $orderProducts['pickupAddress']['oua_address1'] . '<br>';
        }

        if ($orderProducts['pickupAddress']['oua_address2'] != '') {
            $pickUpAddressInfo .= $orderProducts['pickupAddress']['oua_address2'] . '<br>';
        }

        if ($orderProducts['pickupAddress']['oua_city'] != '') {
            $pickUpAddressInfo .= $orderProducts['pickupAddress']['oua_city'] . ',';
        }

        if ($orderProducts['pickupAddress']['oua_zip'] != '') {
            $pickUpAddressInfo .= $orderProducts['pickupAddress']['oua_state'];
        }

        if ($orderProducts['pickupAddress']['oua_zip'] != '') {
            $pickUpAddressInfo .= '-' . $orderProducts['pickupAddress']['oua_zip'];
        }

        if ($orderProducts['pickupAddress']['oua_phone'] != '') {
            $pickUpAddressInfo .= '<br>' . ValidateElement::formatDialCode($orderProducts['pickupAddress']['oua_phone_dcode']) . $orderProducts['pickupAddress']['oua_phone'];
        }
        $str .= '<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">' . $pickUpAddressInfo . '</td>';
    }
    $str .= '</tr></table>';
    echo $str;
}
