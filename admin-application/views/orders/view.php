<?php
$selected_method = '';
if ($order['order_pmethod_id']) {
    $selected_method .= CommonHelper::displayNotApplicable($adminLangId, $order["plugin_name"]);
}
if ($order['order_is_wallet_selected'] == applicationConstants::YES) {
    $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Wallet", $adminLangId) : Labels::getLabel("LBL_Wallet", $adminLangId);
}
if ($order['order_reward_point_used'] > 0) {
    $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Rewards", $adminLangId) : Labels::getLabel("LBL_Rewards", $adminLangId);
}
?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Order_Detail', $adminLangId); ?>
                            </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Customer_Order_Detail', $adminLangId); ?>
                        </h4>
                        <?php
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => false,
                            'otherButtons' => [
                                [
                                    'attr' => [
                                        'href' => UrlHelper::generateUrl('Orders'),
                                        'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-arrow-left"></i>'
                                ]
                            ]
                        ];

                        $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <div class="sectionbody">
                        <table class="table table--details">
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Order/Invoice_ID', $adminLangId); ?>:</strong>
                                    <?php echo $order["order_id"]; ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?>:
                                    </strong> <?php echo FatDate::format($order['order_date_added'], true, true, FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())); ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Payment_Status', $adminLangId); ?>:</strong>
                                    <?php echo Orders::getOrderPaymentStatusArr($adminLangId)[$order['order_payment_status']];
                                    if (in_array(strtolower($order['plugin_code']), ['cashondelivery', 'payatstore'])) {
                                        echo ' (' . $order['plugin_name'] . ' )';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Customer', $adminLangId); ?>:
                                    </strong> <?php echo $order["buyer_user_name"] ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?>:</strong>
                                    <?php echo $selected_method; ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Site_Commission', $adminLangId); ?>:</strong>
                                    <?php echo CommonHelper::displayMoneyFormat($order['order_site_commission'], true, true); ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Order_Amount', $adminLangId); ?>:
                                    </strong> <?php echo CommonHelper::displayMoneyFormat($order["order_net_amount"], true, true); ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?>:
                                    </strong>- <?php echo CommonHelper::displayMoneyFormat($order["order_discount_total"], true, true); ?>
                                </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $adminLangId); ?>:
                                    </strong><?php echo CommonHelper::displayMoneyFormat($order["order_reward_point_value"], true, true); ?>
                                </td>
                            </tr>
                            <tr>
                                <?php if (array_key_exists('order_rounding_off', $order) && 0 != $order['order_rounding_off']) { ?>
                                    <td>
                                        <strong><?php echo (0 < $order['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $adminLangId) : Labels::getLabel('LBL_Rounding_Down', $adminLangId); ?>:
                                        </strong><?php echo CommonHelper::displayMoneyFormat($order['order_rounding_off'], true, true); ?>
                                    </td>
                                <?php } ?>
                                <td><strong><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $adminLangId); ?>:
                                    </strong>-<?php echo CommonHelper::displayMoneyFormat($order['order_volume_discount_total'], true, true); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?>
                        </h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th>#</td>
                                <th><?php echo Labels::getLabel('LBL_Child_Order_Invoice_ID', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Product/Shop/Seller_Details', $adminLangId); ?></th>
                                <?php
                                if (!empty($order['products']) && Shipping::FULFILMENT_PICKUP == current($order['products'])['opshipping_fulfillment_type']) { ?>
                                    <th><?php echo Labels::getLabel('LBL_PICKUP_DETAIL', $adminLangId); ?></th>
                                <?php } else { ?>
                                    <th><?php echo Labels::getLabel('LBL_Shipping_Detail', $adminLangId); ?></th>
                                <?php  } ?>
                                <th><?php echo Labels::getLabel('LBL_Unit_Price', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>
                                <?php if (!empty($order['products']) && Shipping::FULFILMENT_PICKUP != current($order['products'])['opshipping_fulfillment_type']) { ?>
                                    <th class="text-right"><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $adminLangId); ?></th>
                                <th class="text-right"><?php echo Labels::getLabel('LBL_Total', $adminLangId); ?></th>
                            </tr>
                            <?php
                            $k = 1;
                            $cartTotal = 0;
                            $shippingTotal = 0;
                            $taxOptionsTotal = array();
                            foreach ($order["products"] as $op) {
                                $shippingCost = CommonHelper::orderProductAmount($op, 'SHIPPING');
                                $volumeDiscount = CommonHelper::orderProductAmount($op, 'VOLUME_DISCOUNT');
                                $total = CommonHelper::orderProductAmount($op, 'cart_total') + $shippingCost + $volumeDiscount;
                                $cartTotal = $cartTotal + CommonHelper::orderProductAmount($op, 'cart_total');
                                $shippingTotal = $shippingTotal + CommonHelper::orderProductAmount($op, 'shipping');
                                $invoiceNumber = $op['op_invoice_number'];
                                $opId = FatUtility::int($op['op_id']);
                            ?>
                                <tr>
                                    <td><?php echo $k; ?></td>
                                    <td><?php echo $invoiceNumber; ?></td>
                                    <td><?php echo $op['orderstatus_name']; ?></td>
                                    <td>
                                        <?php
                                        $txt = '';
                                        if ($op['op_selprod_title'] != '') {
                                            $txt .= $op['op_selprod_title'] . '<br/>';
                                        }
                                        $txt .= $op['op_product_name'];
                                        $txt .= '<br/>';
                                        if (!empty($op['op_brand_name'])) {
                                            $txt .=  Labels::getLabel('LBL_Brand', $adminLangId) . ': ' . $op['op_brand_name'];
                                        }
                                        if (!empty($op['op_brand_name']) && !empty($op['op_selprod_options'])) {
                                            $txt .= ' | ';
                                        }
                                        if ($op['op_selprod_options'] != '') {
                                            $txt .= $op['op_selprod_options'];
                                        }
                                        if ($op['op_selprod_sku'] != '') {
                                            $txt .= '<br/>' . Labels::getLabel('LBL_SKU', $adminLangId) . ': ' . $op['op_selprod_sku'];
                                        }
                                        if ($op['op_product_model'] != '') {
                                            $txt .= '<br/>' . Labels::getLabel('LBL_Model', $adminLangId) . ':  ' . $op['op_product_model'];
                                        }
                                        $txt .= '<br/><strong>' . Labels::getLabel('LBL_Shop_Detail', $adminLangId) . ':</strong><br/>' . Labels::getLabel('LBL_Shop_Name', $adminLangId) . ': ' . $op['op_shop_name'];
                                        $txt .= '<br/>' . Labels::getLabel('LBL_Seller_Name', $adminLangId) . ': ' . $op['op_shop_owner_name'] . ' <br/>' . Labels::getLabel('LBL_Seller_Email_Id', $adminLangId) . ': ' . $op['op_shop_owner_email'];
                                        if ($op['op_shop_owner_phone'] != '') {
                                            $phone = ValidateElement::formatDialCode($op['op_shop_owner_phone_dcode']) . $op['op_shop_owner_phone'];
                                            $txt .= '<br/>' . Labels::getLabel('LBL_Seller_Phone', $adminLangId) . ': ' . $phone;
                                        }
                                        echo $txt; ?>
                                    </td>
                                    <td>
                                        <?php if (Shipping::FULFILMENT_PICKUP == $op['opshipping_fulfillment_type']) { ?>
                                            <p>
                                                <strong>
                                                    <?php
                                                    $opshippingDate = isset($op['opshipping_date']) ? $op['opshipping_date'] . ' ' : '';
                                                    $timeSlotFrom = isset($op['opshipping_time_slot_from']) ? ' (' . date('H:i', strtotime($op['opshipping_time_slot_from'])) . ' - ' : '';
                                                    $timeSlotTo = isset($op['opshipping_time_slot_to']) ? date('H:i', strtotime($op['opshipping_time_slot_to'])) . ')' : '';
                                                    echo $opshippingDate . $timeSlotFrom . $timeSlotTo;
                                                    ?>
                                                </strong><br>
                                                <?php echo $op['addr_name']; ?>,
                                                <?php
                                                $address1 = !empty($op['addr_address1']) ? $op['addr_address1'] : '';
                                                $address2 = !empty($op['addr_address2']) ? ', ' . $op['addr_address2'] : '';
                                                $city = !empty($op['addr_city']) ? '<br>' . $op['addr_city'] : '';
                                                $state = !empty($op['state_code']) ? ', ' . $op['state_code'] : '';
                                                $country = !empty($op['country_code']) ? ' ' . $op['country_code'] : '';
                                                $zip = !empty($op['addr_zip']) ? '(' . $op['addr_zip'] . ')' : '';

                                                echo $address1 . $address2 . $city . $state . $country . $zip;
                                                ?>
                                            </p>
                                        <?php } else { ?>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Shipping_Class', $adminLangId); ?>:
                                            </strong>
                                            <?php echo CommonHelper::displayNotApplicable($adminLangId, $op["opshipping_label"]); ?>
                                            <br>
                                            <?php if (!empty($op["opshipping_service_code"])) { ?>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_SHIPPING_SERVICES:', $adminLangId); ?>
                                                </strong>
                                            <?php echo CommonHelper::displayNotApplicable($adminLangId, $op["opshipping_service_code"]);
                                            }
                                            $orderStatusLbl = '';
                                            if (!empty($op["thirdPartyorderInfo"]) && isset($op["thirdPartyorderInfo"]['orderStatus'])) {
                                                $orderStatus = $op["thirdPartyorderInfo"]['orderStatus'];
                                                $orderStatusLbl = strpos($orderStatus, "_") ? ucwords(str_replace('_', ' ', $orderStatus)) : $orderStatus;
                                            ?>
                                                <br>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_ORDER_STATUS', $adminLangId); ?>:
                                                </strong>
                                                <?php echo $orderStatusLbl; ?>
                                            <?php } ?>
                                            <?php if (!empty($op["opship_tracking_number"])) { ?>
                                                <br>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_TRACKING_NUMBER', $adminLangId); ?>:
                                                </strong>
                                                <?php echo $op["opship_tracking_number"]; ?>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php echo CommonHelper::displayMoneyFormat($op["op_unit_price"], true, true); ?>
                                    </td>
                                    <td>
                                        <?php echo $op['op_qty']; ?>
                                    </td>
                                    <?php if (Shipping::FULFILMENT_PICKUP != current($order['products'])['opshipping_fulfillment_type']) { ?>
                                        <td class="text-right">
                                            <?php echo CommonHelper::displayMoneyFormat($shippingCost, true, true); ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($op, 'VOLUME_DISCOUNT')); ?>
                                    </td>
                                    <td class="text-right">
                                        <?php echo CommonHelper::displayMoneyFormat($total, true, true); ?>
                                    </td>
                                </tr>
                            <?php
                                $k++;
                                if (!empty($op['taxOptions'])) {
                                    foreach ($op['taxOptions'] as $key => $val) {
                                        if (!isset($taxOptionsTotal[$key]['value'])) {
                                            $taxOptionsTotal[$key]['value'] = 0;
                                        }
                                        $taxOptionsTotal[$key]['value'] += $val['value'];
                                        $taxOptionsTotal[$key]['title'] = CommonHelper::displayTaxPercantage($val);
                                    }
                                }
                            } ?>
                            <tr>
                                <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Cart_Total', $adminLangId); ?>
                                </td>
                                <td class="text-right" colspan="2"><?php echo CommonHelper::displayMoneyFormat($cartTotal, true, true); ?>
                                    </th>
                            </tr>
                            <?php if (0 < $shippingTotal) { ?>
                                <tr>
                                    <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Delivery/Shipping', $adminLangId); ?>
                                    </td>
                                    <td class="text-right" colspan="2">+<?php echo CommonHelper::displayMoneyFormat($shippingTotal, true, true); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($taxOptionsTotal)) { ?>
                                <tr>
                                    <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?>
                                    </td>
                                    <td class="text-right" colspan="2"><?php echo '+' . CommonHelper::displayMoneyFormat($order['order_tax_charged'], true, true); ?>
                                    </td>
                                </tr>
                                <?php } else {
                                foreach ($taxOptionsTotal as $key => $val) { ?>
                                    <tr>
                                        <td colspan="8" class="text-right"><?php echo $val['title'] ?>
                                        </td>
                                        <td class="text-right" colspan="2"><?php echo CommonHelper::displayMoneyFormat($val['value']); ?>
                                        </td>
                                    </tr>
                            <?php }
                            } ?>
                            <?php if ($order['order_discount_total'] > 0) { ?>
                                <tr>
                                    <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?>
                                    </td>
                                    <td class="text-right" colspan="2">-<?php echo CommonHelper::displayMoneyFormat($order['order_discount_total'], true, true); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if ($order['order_reward_point_value'] > 0) { ?>
                                <tr>
                                    <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $adminLangId); ?>
                                    </td>
                                    <td class="text-right" colspan="2">-<?php echo CommonHelper::displayMoneyFormat($order['order_reward_point_value'], true, true); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if ($order['order_volume_discount_total'] > 0) { ?>
                                <tr>
                                    <td colspan="8" class="text-right"><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $adminLangId); ?>
                                    </td>
                                    <td class="text-right" colspan="2">-<?php echo CommonHelper::displayMoneyFormat($order['order_volume_discount_total'], true, true); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if (array_key_exists('order_rounding_off', $order) && 0 != $order['order_rounding_off']) { ?>
                                <tr>
                                    <td colspan="8" class="text-right">
                                    <?php echo (0 < $order['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $adminLangId) : Labels::getLabel('LBL_Rounding_Down', $adminLangId); ?>
                                    </td>
                                    <td class="text-right" colspan="2">
                                        </strong><?php echo CommonHelper::displayMoneyFormat($order['order_rounding_off'], true, true); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="8" class="text-right"><strong><?php echo Labels::getLabel('LBL_Order_Total', $adminLangId); ?></strong>
                                </td>
                                <td class="text-right" colspan="2"><strong><?php echo CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true); ?></strong>
                                </td>
                            </tr>

                        </table>
                    </div>
                </section>
                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?>
                                </h4>
                            </div>
                            <div class="row space">
                                <div class="address-group">
                                    <h5><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?>
                                    </h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>:
                                        </strong><?php echo $order["buyer_user_name"] ?><br><strong><?php echo Labels::getLabel('LBL_Email', $adminLangId); ?>:
                                        </strong><?php echo $order['buyer_email']; ?><br><strong><?php echo Labels::getLabel('LBL_Phone_Number', $adminLangId); ?>:</strong>
                                        <?php echo CommonHelper::displayNotApplicable($adminLangId, ValidateElement::formatDialCode($order['buyer_phone_dcode']) . $order['buyer_phone']); ?>
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Billing_/_Shipping_Details', $adminLangId); ?>
                                </h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Billing_Details', $adminLangId); ?>
                                    </h5>
                                    <p><strong><?php echo $order['billingAddress']['oua_name']; ?></strong><br>
                                        <?php
                                        $billingAddress = '';
                                        if ($order['billingAddress']['oua_address1'] != '') {
                                            $billingAddress .= $order['billingAddress']['oua_address1'] . '<br>';
                                        }

                                        if ($order['billingAddress']['oua_address2'] != '') {
                                            $billingAddress .= $order['billingAddress']['oua_address2'] . '<br>';
                                        }

                                        if ($order['billingAddress']['oua_city'] != '') {
                                            $billingAddress .= $order['billingAddress']['oua_city'] . ',';
                                        }

                                        if ($order['billingAddress']['oua_zip'] != '') {
                                            $billingAddress .= ' ' . $order['billingAddress']['oua_state'];
                                        }

                                        if ($order['billingAddress']['oua_zip'] != '') {
                                            $billingAddress .= '-' . $order['billingAddress']['oua_zip'];
                                        }

                                        if ($order['billingAddress']['oua_phone'] != '') {
                                            $billingAddress .= '<br>' . Labels::getLabel('LBL_PHONE', $adminLangId) . ': ' . ValidateElement::formatDialCode($order['billingAddress']['oua_phone_dcode']) . $order['billingAddress']['oua_phone'];
                                        }
                                        echo $billingAddress;
                                        ?>
                                    </p>
                                </div>
                                <?php if (!empty($order['shippingAddress'])) { ?>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?>
                                        </h5>
                                        <p><strong><?php echo $order['shippingAddress']['oua_name']; ?></strong><br>
                                            <?php
                                            $shippingAddress = '';
                                            if ($order['shippingAddress']['oua_address1'] != '') {
                                                $shippingAddress .= $order['shippingAddress']['oua_address1'] . '<br>';
                                            }

                                            if ($order['shippingAddress']['oua_address2'] != '') {
                                                $shippingAddress .= $order['shippingAddress']['oua_address2'] . '<br>';
                                            }

                                            if ($order['shippingAddress']['oua_city'] != '') {
                                                $shippingAddress .= $order['shippingAddress']['oua_city'] . ',';
                                            }

                                            if ($order['shippingAddress']['oua_zip'] != '') {
                                                $shippingAddress .= ' ' . $order['shippingAddress']['oua_state'];
                                            }

                                            if ($order['shippingAddress']['oua_zip'] != '') {
                                                $shippingAddress .= '-' . $order['shippingAddress']['oua_zip'];
                                            }

                                            if ($order['shippingAddress']['oua_phone'] != '') {
                                                $shippingAddress .= '<br>' . Labels::getLabel('LBL_PHONE', $adminLangId) . ': ' . ValidateElement::formatDialCode($order['shippingAddress']['oua_phone_dcode']) . $order['shippingAddress']['oua_phone'];
                                            }

                                            echo $shippingAddress; ?>

                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                        </section>
                    </div>
                </div>
                <?php
                if (isset($order["comments"]) && is_array($order["comments"]) && count($order["comments"]) > 0) { ?>
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Order_Status_History', $adminLangId); ?>
                            </h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?>
                                        </th>
                                        <th width="15%"><?php echo Labels::getLabel('LBL_Customer_Notified', $adminLangId); ?>
                                        </th>
                                        <th width="15%"><?php echo Labels::getLabel('LBL_Payment_Status', $adminLangId); ?>
                                        </th>
                                        <th width="60%"><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?>
                                        </th>
                                    </tr>
                                    <?php foreach ($order["comments"] as $key => $row) { ?>
                                        <tr>
                                            <td><?php echo FatDate::format($row['oshistory_date_added']); ?>
                                            </td>
                                            <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?>
                                            </td>
                                            <td><?php echo ($row['oshistory_orderstatus_id'] > 0) ? $orderStatuses[$row['oshistory_orderstatus_id']] : CommonHelper::displayNotApplicable($adminLangId, ''); ?>
                                            </td>
                                            <td>
                                                <div class="break-me"><?php echo nl2br($row['oshistory_comments']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php } ?>
                <?php if (!$order['order_deleted']) { ?>
                    <?php if (!empty($order['payments'])) { ?>
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Order_Payment_History', $adminLangId); ?>
                                </h4>
                            </div>
                            <div class="sectionbody">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th width="10%"><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?>
                                            </th>
                                            <th width="10%"><?php echo Labels::getLabel('LBL_Txn_ID', $adminLangId); ?>
                                            </th>
                                            <th width="15%"><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?>
                                            </th>
                                            <th width="10%"><?php echo Labels::getLabel('LBL_Amount', $adminLangId); ?>
                                            </th>
                                            <th width="15%"><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?>
                                            </th>
                                            <th width="25%"><?php echo Labels::getLabel('LBL_Gateway_Response', $adminLangId); ?>
                                            </th>
                                            <th width="15%"><?php echo Labels::getLabel('LBL_STATUS', $adminLangId); ?>
                                            </th>
                                            <th width="15%"><?php echo Labels::getLabel('LBL_ACTION', $adminLangId); ?>
                                            </th>
                                        </tr>
                                        <?php foreach ($order["payments"] as $key => $row) { ?>
                                            <tr>
                                                <td><?php echo FatDate::format($row['opayment_date']); ?>
                                                </td>
                                                <td><?php echo $row['opayment_gateway_txn_id']; ?>
                                                </td>
                                                <td><?php echo $row['opayment_method']; ?>
                                                </td>
                                                <td><?php echo CommonHelper::displayMoneyFormat($row['opayment_amount'], true, true); ?>
                                                </td>
                                                <td>
                                                    <div class="break-me"><?php echo nl2br($row['opayment_comments']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="break-me">
                                                        <a href="javascript:void(0);" onclick="viewPaymemntGatewayResponse('<?php echo $row['opayment_gateway_response']; ?>')">View</a>
                                                        <?php //echo nl2br($row['opayment_gateway_response']); 
                                                        ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="break-me">
                                                        <?php
                                                        $cls = '';
                                                        $msg = '';
                                                        switch ($row['opayment_txn_status']) {
                                                            case Orders::ORDER_PAYMENT_PENDING:
                                                                $cls = 'label-info';
                                                                $msg = Labels::getLabel("LBL_PENDING", $adminLangId);
                                                                break;
                                                            case Orders::ORDER_PAYMENT_PAID:
                                                                $cls = 'label-success';
                                                                $msg = Labels::getLabel("LBL_APPROVED", $adminLangId);
                                                                break;
                                                            case Orders::ORDER_PAYMENT_CANCELLED:
                                                                $cls = 'label-danger';
                                                                $msg = Labels::getLabel("LBL_REJECTED", $adminLangId);
                                                                break;
                                                        }
                                                        ?>
                                                        <span class='label <?php echo $cls; ?>'><?php echo $msg; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (0 == FatUtility::int($row['opayment_txn_status'])) { ?>
                                                        <a href="javascript::void(0);" onclick="approve('<?php echo $row['opayment_id']; ?>')" class="btn btn-secondary btn-sm"><?php echo Labels::getLabel("LBL_APPROVE", $adminLangId); ?></a>
                                                        <a href="javascript::void(0);" onclick="reject('<?php echo $row['opayment_id']; ?>')" class="btn btn-outline-secondary btn-sm"><?php echo Labels::getLabel("LBL_REJECT", $adminLangId); ?></a>
                                                    <?php } else {
                                                        echo Labels::getLabel("LBL_N/A", $adminLangId);
                                                    } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if (!$order["order_payment_status"] && $canEdit && 'CashOnDelivery' != $order['plugin_code']) { ?>
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Order_Payments', $adminLangId); ?>
                                </h4>
                            </div>
                            <div class="sectionbody space">
                                <?php
                                $frm->setFormTagAttribute('onsubmit', 'updatePayment(this); return(false);');
                                $frm->setFormTagAttribute('class', 'web_form');
                                $frm->developerTags['colClassPrefix'] = 'col-md-';
                                $frm->developerTags['fld_default_col'] = 12;


                                $paymentFld = $frm->getField('opayment_method');
                                $paymentFld->developerTags['col'] = 4;

                                $gatewayFld = $frm->getField('opayment_gateway_txn_id');
                                $gatewayFld->developerTags['col'] = 4;

                                $amountFld = $frm->getField('opayment_amount');
                                $amountFld->developerTags['col'] = 4;

                                $submitFld = $frm->getField('btn_submit');
                                $submitFld->developerTags['col'] = 4;

                                echo $frm->getFormHtml(); ?>
                            </div>
                        </section>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>