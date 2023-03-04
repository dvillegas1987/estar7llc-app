<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'op_invoice_number' =>    Labels::getLabel('LBL_INV_No', $adminLangId),
    'vendor' => Labels::getLabel('LBL_Seller', $adminLangId),
    'buyer_name' => Labels::getLabel('LBL_Customer', $adminLangId),
    'order_date_added' => Labels::getLabel('LBL_Date', $adminLangId),
    'order_net_amount' => Labels::getLabel('LBL_Amount', $adminLangId),
    'op_status_id' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}
$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);

foreach ($vendorOrdersList as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'op_invoice_number':
                $td->appendElement('a', array('target' => '_blank', 'href' => UrlHelper::generateUrl('SellerOrders', 'view', array($row['op_id']))), $row[$key], true);
                break;
            case 'vendor':
                $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_Seller_Name', $adminLangId) . ':  </strong>', true);
                if ($canViewUsers) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['op_selprod_user_id'] . ')'), $row['op_shop_owner_name'], true);
                } else {
                    $td->appendElement('plaintext', array(), $row['op_shop_owner_name'], true);
                }
                $txt = '<br/><strong>' . Labels::getLabel('LBL_Shop', $adminLangId) . ':  </strong>' . $row['op_shop_name'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_User_Name', $adminLangId) . ':  </strong>' . $row['op_shop_owner_username'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_Email', $adminLangId) . ':   </strong><a href="mailto:' . $row['op_shop_owner_email'] . '">' . $row['op_shop_owner_email'] . '</a>';
                /* $txt .= '<br/><strong>'.Labels::getLabel('LBL_Phone',$adminLangId).':   </strong>'.ValidateElement::formatDialCode($row['op_shop_owner_phone_dcode']) . $row['op_shop_owner_phone']; */
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'buyer_name':
                $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_Name', $adminLangId) . ':  </strong>', true);
                if ($canViewUsers) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['user_id'] . ')'), $row[$key], true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                $txt = '<br/><strong>' . Labels::getLabel('LBL_User_Name', $adminLangId) . ':  </strong>' . $row['buyer_username'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_Email', $adminLangId) . ':  </strong><a href="mailto:' . $row['buyer_email'] . '">' . $row['buyer_email'] . '</a>';
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_Phone', $adminLangId) . ':  </strong>' . ValidateElement::formatDialCode($row['buyer_phone_dcode']) . $row['buyer_phone'];
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'order_net_amount':
                $amt = CommonHelper::orderProductAmount($row, 'netamount', false, User::USER_TYPE_SELLER);
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($amt, true, true), true);
                break;
            case 'op_status_id':
                if (Orders::ORDER_PAYMENT_CANCELLED == $row["order_payment_status"]) {
                    $status = Labels::getLabel('LBL_CANCELLED', $adminLangId);
                    $labelClass = 'label-danger';
                } else {
                    $status = $row['orderstatus_name'];

                    /* if (in_array(strtolower($row['plugin_code']), ['cashondelivery', 'payatstore']) && $row['op_status_id'] != FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")) {
                        $status = $row['plugin_name'];
                    } */
                    $labelClass = isset($classArr[$row['orderstatus_color_class']]) ? $classArr[$row['orderstatus_color_class']] : 'label-info';
                }
                $td->appendElement('span', array('class' => 'label label-inline ' . $labelClass), $status, true);
                break;
            case 'order_date_added':
                $timeZone = FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get());
                $td->appendElement('plaintext', array(), FatDate::format($row[$key], true, true, $timeZone));
                break;
            case 'action':
                $td->appendElement('a', array('href' => UrlHelper::generateUrl('SellerOrders', 'view', array($row['op_id'])), 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View_Order_Detail', $adminLangId)), "<i class='far fa-eye icon'></i>", true);

                $orderObj = new Orders($row['order_id']);
                $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();
                if (!in_array($row["op_status_id"], $notAllowedStatues) && $canEdit) {
                    $td->appendElement('a', array('href' => UrlHelper::generateUrl('SellerOrders', 'CancelOrder', array($row['op_id'])), 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Cancel_Order', $adminLangId)), "<i class='fas fa-times'></i>", true);
                }
                $shipBySeller = CommonHelper::canAvailShippingChargesBySeller($row['op_selprod_user_id'], $row['opshipping_by_seller_user_id']);
                if ($row['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL && !$shipBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $row['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $row['order_payment_status']) && !empty($row['opshipping_carrier_code']) && !empty($row['opshipping_service_code'])) {
                    if (empty($row['opship_response']) && empty($row['opship_tracking_number'])) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onclick' => 'generateLabel(' . $row['op_id'] . ')', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_GENERATE_LABEL', $adminLangId)), '<i class="fas fa-file-download"></i>', true);
                    } elseif (!empty($row['opship_response'])) {
                        $td->appendElement('a', array('href' => UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$row['op_id']]), 'target' => '_blank', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_PREVIEW_LABEL', $adminLangId)), '<i class="fas fa-file-export"></i>', true);
                    }
                }

                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($vendorOrdersList) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmVendorOrderSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
