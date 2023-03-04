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
if (strtolower($order['plugin_code']) == 'cashondelivery' && $order['opshipping_fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
    $selected_method = Labels::getLabel('LBL_PAY_ON_PICKUP', $adminLangId);
}

$orderStatusLbl = Labels::getLabel('LBL_AWAITING_SHIPMENT', $adminLangId);
$orderStatus = '';
if (!empty($order["thirdPartyorderInfo"]) && isset($order["thirdPartyorderInfo"]['orderStatus'])) {
    $orderStatus = $order["thirdPartyorderInfo"]['orderStatus'];
    $orderStatusLbl = strpos($orderStatus, "_") ? str_replace('_', ' ', $orderStatus) : $orderStatus;
}

if (!empty($order['opship_tracking_url'])) {
    $orderStatusLbl = Labels::getLabel('LBL_SHIPPED', $adminLangId);
}

?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <?php if (!$print) { ?>
                    <div class="page__title no-print">
                        <div class="row">
                            <div class="col--first col-lg-6">
                                <span class="page__icon"><i class="ion-android-star"></i></span>
                                <h5><?php echo Labels::getLabel('LBL_Orders_Details', $adminLangId); ?></h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Seller_Order_Details', $adminLangId); ?></h4>
                        <?php
                        if (!$print) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => UrlHelper::generateUrl('SellerOrders'),
                                            'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-arrow-left"></i>'
                                    ],
                                ]
                            ];

                            $data['otherButtons'][] = [
                                'attr' => [
                                    'href' => Fatutility::generateUrl('sellerOrders', 'viewInvoice', [$order['op_id']]),
                                    'target' => '_blank',
                                    'title' => Labels::getLabel('LBL_Print', $adminLangId)
                                ],
                                'label' => '<i class="fas fa-print"></i>'
                            ];
                            $data['otherButtons'][] = [
                                'attr' => [
                                    'href' => Fatutility::generateUrl('Orders', 'viewBuyerOrderInvoice', [$order['order_id'],$order['op_id']]),
                                    'target' => '_blank',
                                    'title' => Labels::getLabel('LBL_PRINT_BUYER_INVOICE', $adminLangId)
                                ],
                                'label' => '<i class="fas fa-print"></i>'
                            ];

                            if (!$shippingHanldedBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $order['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $order['order_payment_status'])) {
                                if (empty($order['opship_response']) && empty($order['opship_tracking_number'])) {
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'generateLabel(' . $order['op_id'] . ')',
                                            'title' => Labels::getLabel('LBL_GENERATE_LABEL', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-file-download"></i>'
                                    ];
                                } elseif (!empty($order['opship_response'])) {
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$order['op_id']]),
                                            'target' => "_blank",
                                            'title' => Labels::getLabel('LBL_PREVIEW_LABEL', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-file-export"></i>'
                                    ];
                                }

                                if (!empty($orderStatus) && 'awaiting_shipment' == $orderStatus && !empty($order['opship_response'])) {
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'proceedToShipment(' . $order['op_id'] . ')',
                                            'title' => Labels::getLabel('LBL_PROCEED_TO_SHIPMENT', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-shipping-fast"></i>'
                                    ];
                                }
                            }

                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        } ?>
                    </div>
                    <div class="sectionbody">
                        <table class="table table--details">
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Invoice_Id', $adminLangId); ?> : </strong><?php echo $order["op_invoice_number"] ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?> : </strong><?php echo FatDate::format($order["order_date_added"], true) ?></td>
                                <td>
                                    <strong><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?> : </strong>
                                    <?php
                                    if (Orders::ORDER_PAYMENT_CANCELLED == $order["order_payment_status"]) {
                                        echo Orders::getOrderPaymentStatusArr($adminLangId)[$order["order_payment_status"]];
                                    } else {
                                        echo $order["orderstatus_name"];
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Customer/Guest', $adminLangId); ?> : </strong><?php echo $order["buyer_user_name"] . ' (' . $order['buyer_username'] . ')'; ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?> : </strong> <?php echo $selected_method; ?> </td>
                                <td>
                                    <?php if ($order["op_refund_qty"] > 0) { ?>
                                        <strong>
                                            <?php echo Labels::getLabel('LBL_Refund_for_Qty.', $adminLangId); ?> [<?php echo $order["op_refund_qty"] ?>] :
                                        </strong>
                                    <?php echo CommonHelper::displayMoneyFormat($order["op_refund_amount"], true, true);
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Commission_Charged', $adminLangId); ?>[<?php echo $order["op_commission_percentage"] ?>%]:
                                    </strong><?php echo CommonHelper::displayMoneyFormat($order['op_commission_charged'] - $order['op_refund_commission'], true, true); ?> </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Cart_Total', $adminLangId); ?> : </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'CART_TOTAL'), true, true); ?> </td>
                                <td><strong>
                                        <?php if ($shippingHanldedBySeller) {
                                            echo Labels::getLabel('LBL_Delivery/Shipping', $adminLangId); ?>:
                                    </strong><?php echo '+' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'SHIPPING'), true, true);
                                            } ?>
                                </td>
                            </tr>
                            <tr>
                                <?php if ($order['op_tax_collected_by_seller']) { ?>
                                    <td>
                                        <?php
                                        if (empty($order['taxOptions'])) { ?>
                                            <strong><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?> : </strong>
                                            <?php echo '+' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'TAX'), true, true);
                                        } else {
                                            foreach ($order['taxOptions'] as $key => $val) { ?>
                                                <p><strong><?php echo ucwords(CommonHelper::displayTaxPercantage($val, true)); ?> : </strong> <?php echo CommonHelper::displayMoneyFormat($val['value']); ?></p>
                                        <?php }
                                        } ?>
                                    </td>
                                <?php } ?>
                                <td><strong><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId); ?></strong> : <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT'), true, true); ?> </td>
                                <td><strong><?php echo Labels::getLabel('LBL_Total_Paid', $adminLangId); ?> : </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, User::USER_TYPE_SELLER), true, true); ?>
                                </td>
                            </tr>
                            <tr>
                                <?php if (array_key_exists('op_rounding_off', $order) && 0 != $order['op_rounding_off']) { ?>
                                    <td>
                                        <strong><?php echo (0 < $order['op_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $adminLangId) : Labels::getLabel('LBL_Rounding_Down', $adminLangId); ?>:
                                        </strong><?php echo CommonHelper::displayMoneyFormat($order['op_rounding_off'], true, true); ?>
                                    </td>
                                <?php } ?>
                                <?php if ($order["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) { ?>
                                    <td>
                                        <strong><?php echo Labels::getLabel('LBL_Pickup_Date', $adminLangId); ?> : </strong>
                                        <?php
                                        $fromTime = date('H:i', strtotime($order["opshipping_time_slot_from"]));
                                        $toTime = date('H:i', strtotime($order["opshipping_time_slot_to"]));
                                        echo FatDate::format($order["opshipping_date"]) . ' ' . $fromTime . ' - ' . $toTime;
                                        ?>
                                    </td>
                                <?php } ?>
                            </tr>

                        </table>
                    </div>
                </section>
                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Seller/_Customer_Details', $adminLangId); ?></h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Seller_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Shop_Name', $adminLangId); ?> : </strong><?php echo $order["op_shop_name"] ?><br><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>:
                                        </strong><?php echo $order["op_shop_owner_name"] ?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?> : </strong>
                                        <?php echo $order["op_shop_owner_email"] ?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?> : </strong> <?php echo $order["op_shop_owner_phone_dcode"] . $order["op_shop_owner_phone"]; ?></p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?> : </strong><?php echo $order["buyer_name"] ?><br><strong><?php echo Labels::getLabel('LBL_UserName', $adminLangId); ?>:
                                        </strong><?php echo $order["buyer_username"]; ?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?> : </strong><?php echo $order["buyer_email"] ?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?> : </strong>
                                        <?php echo $order["buyer_phone"] ?></p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4>
                                    <?php
                                    if (!empty($order['pickupAddress'])) {
                                        echo Labels::getLabel('LBL_Billing_/_Pickup_Details', $adminLangId);
                                    } else {
                                        echo Labels::getLabel('LBL_Billing_/_Shipping_Details', $adminLangId);
                                    }
                                    ?>
                                </h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Billing_Details', $adminLangId); ?> </h5>
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
                                        ?><br>
                                    </p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?php if (!empty($order['shippingAddress'])) { ?>
                                        <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?></h5>
                                        <p>
                                            <strong>
                                                <?php echo $order['shippingAddress']['oua_name']; ?></strong><br>
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

                                            echo $shippingAddress;
                                        }
                                        if (!empty($order['pickupAddress'])) { ?>
                                        <h5><?php echo Labels::getLabel('LBL_Pickup_Details', $adminLangId); ?></h5>
                                        <p>
                                            <strong>
                                                <?php
                                                $opshippingDate = isset($order['opshipping_date']) ? $order['opshipping_date'] . ' ' : '';
                                                $timeSlotFrom = isset($order['opshipping_time_slot_from']) ? date('H:i', strtotime($order['opshipping_time_slot_from'])) . ' - ' : '';
                                                $timeSlotTo = isset($order['opshipping_time_slot_to']) ? date('H:i', strtotime($order['opshipping_time_slot_to'])) : '';
                                                echo $opshippingDate . ' (' . $timeSlotFrom . $timeSlotTo . ')';
                                                ?>
                                            </strong><br>
                                            <?php echo $order['pickupAddress']['oua_name']; ?>,
                                        <?php
                                            $pickupAddress = '';
                                            if ($order['pickupAddress']['oua_address1'] != '') {
                                                $pickupAddress .= $order['pickupAddress']['oua_address1'] . '<br>';
                                            }

                                            if ($order['pickupAddress']['oua_address2'] != '') {
                                                $pickupAddress .= $order['pickupAddress']['oua_address2'] . '<br>';
                                            }

                                            if ($order['pickupAddress']['oua_city'] != '') {
                                                $pickupAddress .= $order['pickupAddress']['oua_city'] . ',';
                                            }

                                            if ($order['pickupAddress']['oua_zip'] != '') {
                                                $pickupAddress .= ' ' . $order['pickupAddress']['oua_state'];
                                            }

                                            if ($order['pickupAddress']['oua_zip'] != '') {
                                                $pickupAddress .= '-' . $order['pickupAddress']['oua_zip'];
                                            }

                                            if ($order['pickupAddress']['oua_phone'] != '') {
                                                $pickupAddress .= '<br>' . Labels::getLabel('LBL_PHONE', $adminLangId) . ': ' . ValidateElement::formatDialCode($order['pickupAddress']['oua_phone_dcode']) . $order['pickupAddress']['oua_phone'];
                                            }
                                            echo $pickupAddress;
                                        } ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th>#</th>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $adminLangId); ?></th>
                                <?php if (empty($order['pickupAddress'])) { ?>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?>
                                    </th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Unit_Price', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>
                                <?php if ($shippingHanldedBySeller) { ?>
                                    <th><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <?php } ?>
                                <?php if ($order['op_tax_collected_by_seller']) { ?>
                                    <th><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Total', $adminLangId); ?></th>
                            </tr>
                            <tr>
                                <td>#</td>
                                <td>
                                    <?php
                                    $txt = '';
                                    if ($order['op_selprod_title'] != '') {
                                        $txt .= $order['op_selprod_title'] . '<br/>';
                                    }
                                    $txt .= $order['op_product_name'];
                                    $txt .= '<br/>';
                                    if (!empty($order['op_brand_name'])) {
                                        $txt .=  Labels::getLabel('LBL_Brand', $adminLangId) . ': ' . $order['op_brand_name'];
                                    }
                                    if (!empty($order['op_brand_name']) && !empty($order['op_selprod_options'])) {
                                        $txt .= ' | ';
                                    }
                                    if ($order['op_selprod_options'] != '') {
                                        $txt .= $order['op_selprod_options'];
                                    }
                                    if ($order['op_selprod_sku'] != '') {
                                        $txt .= '<br/>' . Labels::getLabel('LBL_SKU', $adminLangId) . ':  ' . $order['op_selprod_sku'];
                                    }
                                    if ($order['op_product_model'] != '') {
                                        $txt .= '<br/>' . Labels::getLabel('LBL_Model', $adminLangId) . ':  ' . $order['op_product_model'];
                                    }
                                    echo $txt;
                                    ?>
                                </td>
                                <?php if (Shipping::FULFILMENT_PICKUP != $order['opshipping_fulfillment_type']) { ?>
                                    <td>
                                        <?php if ($order['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) { ?>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Shipping_Class', $adminLangId); ?> :
                                            </strong>
                                            <?php echo CommonHelper::displayNotApplicable($adminLangId, $order["opshipping_label"]); ?><br>
                                            <?php if (!empty($order["opshipping_service_code"])) { ?>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_SHIPPING_SERVICES', $adminLangId); ?> :
                                                </strong>
                                                <?php echo $order["opshipping_service_code"]; ?><br>
                                            <?php } ?>
                                            <?php if (!empty($orderStatusLbl)) { ?>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_ORDER_STATUS', $adminLangId); ?>:
                                                </strong>
                                                <?php echo ucwords($orderStatusLbl); ?>
                                            <?php } ?>
                                        <?php } else {
                                            echo Labels::getLabel('LBL_N/A', $adminLangId);
                                        } ?>
                                    </td>
                                <?php } ?>
                                <td><?php echo CommonHelper::displayMoneyFormat($order["op_unit_price"], true, true); ?></td>
                                <td><?php echo $order["op_qty"] ?></td>
                                <?php if ($shippingHanldedBySeller) { ?>
                                    <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'SHIPPING'), true, true); ?></td>
                                <?php } ?>
                                <?php if ($order['op_tax_collected_by_seller']) { ?>
                                    <td>
                                        <?php
                                        if (empty($order['taxOptions'])) {
                                            echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'TAX'), true, true);
                                        } else {
                                            foreach ($order['taxOptions'] as $key => $val) { ?>
                                                <p><strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?> : </strong> <?php echo CommonHelper::displayMoneyFormat($val['value']); ?></p>
                                        <?php }
                                        } ?>
                                    </td>
                                <?php } ?> <td>
                                    <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT'), true, true); ?></td>
                                <td>
                                    <?php
                                    echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, User::USER_TYPE_SELLER), true, true);

                                    /* if ($roundingOff = CommonHelper::getRoundingOff($order)) {
                                        echo '(+' . $roundingOff . ')';
                                    } */
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </section>
                <?php if (!empty($digitalDownloads) && !$print) { ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Downloads', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_#', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_File', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Language', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Downloaded_count', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Expired_on', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Action', $adminLangId); ?></th>
                                </tr>
                                <?php $sr_no = 1;
                                foreach ($digitalDownloads as $key => $row) {
                                    $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                                    if ($row['afile_lang_id'] > 0) {
                                        $lang_name = $allLanguages[$row['afile_lang_id']]['language_name'];
                                    }
                                    if ($row['downloadable']) {
                                        $fileName = '<a href="' . UrlHelper::generateUrl('SellerOrders', 'digitalDownloads', array($row['afile_id'], $row['afile_record_id'])) . '">' . $row['afile_name'] . '</a>';
                                    } else {
                                        $fileName = $row['afile_name'];
                                    }
                                    $downloads = '<a href="' . UrlHelper::generateUrl('SellerOrders', 'digitalDownloads', array($row['afile_id'], $row['afile_record_id'])) . '">' . Labels::getLabel('LBL_Downloads', $adminLangId) . '</a>';

                                    $expiry = Labels::getLabel('LBL_N/A', $adminLangId);
                                    if ($row['expiry_date'] != '') {
                                        $expiry = FatDate::Format($row['expiry_date']);
                                    } ?>
                                    <tr>
                                        <td><?php echo $sr_no; ?></td>
                                        <td><?php echo $fileName; ?></td>
                                        <td><?php echo $lang_name; ?></td>
                                        <td><?php echo $row['afile_downloaded_times']; ?></td>
                                        <td><?php echo $expiry; ?></td>
                                        <td><?php echo $downloads; ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </section>
                <?php }
                if (!empty($digitalDownloadLinks) && !$print) { ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Downloads', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_#', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Link', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Download_times', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Downloaded_count', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Expired_on', $adminLangId); ?></th>
                                </tr>
                                <?php $sr_no = 1;
                                foreach ($digitalDownloadLinks as $key => $row) {
                                    /* $fileName = '<a href="'.UrlHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'">'.$row['afile_name'].'</a>'; */
                                    /* $downloads = '<li><a href="'.UrlHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'"><i class="fa fa-download"></i></a></li>'; */

                                    $expiry = Labels::getLabel('LBL_N/A', $adminLangId);
                                    if ($row['expiry_date'] != '') {
                                        $expiry = FatDate::Format($row['expiry_date']);
                                    }

                                    $downloadableCount = Labels::getLabel('LBL_N/A', $adminLangId);
                                    if ($row['downloadable_count'] != -1) {
                                        $downloadableCount = $row['downloadable_count'];
                                    } ?>
                                    <tr>
                                        <td><?php echo $sr_no; ?></td>
                                        <td><a target="_blank" href="<?php echo $row['opddl_downloadable_link']; ?>" title="<?php echo Labels::getLabel('LBL_Click_to_download', $adminLangId); ?>"><?php echo $row['opddl_downloadable_link']; ?></a></td>
                                        <td><?php echo $downloadableCount; ?></td>
                                        <td><?php echo $row['opddl_downloaded_times']; ?></td>
                                        <td><?php echo $expiry; ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </section>
                <?php }
                if (!empty($order['comments']) && !$print) {  ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Order_Comments', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?></td>
                                    <th><?php echo Labels::getLabel('LBL_Customer_Notified', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?></th>
                                </tr>
                                <?php foreach ($order['comments'] as $row) { ?>
                                    <tr>
                                        <td><?php echo FatDate::format($row['oshistory_date_added']); ?></td>
                                        <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?></td>
                                        <td><?php echo $orderStatuses[$row['oshistory_orderstatus_id']]; ?></td>
                                        <td>
                                            <?php
                                            echo nl2br($row['oshistory_comments']);
                                            echo ' ' . (($row['oshistory_orderstatus_id'] > 0) ? $orderStatuses[$row['oshistory_orderstatus_id']] : CommonHelper::displayNotApplicable($adminLangId, '')) . ' ';
                                            if ($row['oshistory_orderstatus_id'] ==  OrderStatus::ORDER_SHIPPED) {
                                                if (empty($row['oshistory_courier'])) {
                                                    $str = !empty($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $adminLangId) . ' ' . $row['oshistory_tracking_number'] : '';
                                                    if (empty($order['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                        $str .=  " VIA <em>" . CommonHelper::displayNotApplicable($adminLangId, $order["opshipping_label"]) . "</em>";
                                                    } elseif (!empty($order['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                        $str .=  " <a class='btn btn-outline-secondary btn-sm' href='" . $order['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $adminLangId) . "</a>";
                                                    }
                                                    echo $str;
                                                } else {
                                                    echo ($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $adminLangId) : '';
                                                    $trackingNumber = $row['oshistory_tracking_number'];
                                                    $carrier = $row['oshistory_courier'];
                                            ?>
                                                    <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_TRACK', $adminLangId); ?>" onClick="trackOrder('<?php echo trim($trackingNumber); ?>', '<?php echo trim($carrier); ?>', '<?php echo $order["op_invoice_number"]; ?>')">
                                                        <?php echo $trackingNumber; ?>
                                                    </a>
                                                    <?php echo Labels::getLabel('LBL_VIA', $adminLangId); ?> <em><?php echo CommonHelper::displayNotApplicable($adminLangId, $order["opshipping_label"]); ?></em>
                                            <?php }
                                            } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </section>
                <?php }
                if ($displayShippingUserForm) { ?>
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Assign_to_shipping_company_user', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <?php
                            $shippingUserFrm->setFormTagAttribute('onsubmit', 'updateShippingCompany(this); return(false);');
                            $shippingUserFrm->setFormTagAttribute('class', 'web_form form_horizontal');
                            $shippingUserFrm->developerTags['colClassPrefix'] = 'col-md-';
                            $shippingUserFrm->developerTags['fld_default_col'] = 12;

                            $statusFld = $shippingUserFrm->getField('optsu_user_id');
                            $statusFld->setFieldTagAttribute('class', 'shippingUser-js');
                            echo $shippingUserFrm->getFormHtml();
                            ?>
                        </div>
                    </section>
                <?php }

                if ($displayForm && !$print) { ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Comments_on_order', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <?php
                            $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                            $frm->setFormTagAttribute('class', 'web_form markAsShipped-js');

                            $frm->developerTags['colClassPrefix'] = 'col-md-';
                            $frm->developerTags['fld_default_col'] = 12;

                            $manualFld = $frm->getField('manual_shipping');

                            $statusFld = $frm->getField('op_status_id');
                            $statusFld->setFieldTagAttribute('class', 'status-js fieldsVisibility-js');
                            $statusFld->developerTags['col'] = (null != $manualFld) ? 4 : 6;

                            $notiFld = $frm->getField('customer_notified');
                            $notiFld->setFieldTagAttribute('class', 'notifyCustomer-js');
                            $notiFld->developerTags['col'] = (null != $manualFld) ? 4 : 6;

                            if (null != $manualFld) {
                                $manualFld->setFieldTagAttribute('class', 'manualShipping-js fieldsVisibility-js');
                                $manualFld->developerTags['col'] = 4;

                                $fldTracking = $frm->getField('tracking_number');
                                $fldTracking->developerTags['col'] = 4;

                                $fld = $frm->getField('opship_tracking_url');
                                $courierFld = $frm->getField('oshistory_courier');
                                if (null != $fld) {
                                    $fld->developerTags['col'] = 4;
                                    $fld->setWrapperAttribute('class', 'trackingUrlBlk--js');
                                    $fld->setFieldTagAttribute('class', 'trackingUrlFld--js');
                                    if (null != $courierFld) {
                                        $fld->htmlAfterField = '<a href="javascript:void(0)" onclick="courierFld()">' . Labels::getLabel(
                                            'LBL_OR_SELECT_COURIER_?',
                                            $adminLangId
                                        ) . '</a>';
                                    }
                                }

                                if (null != $courierFld) {
                                    $courierFld->developerTags['col'] = 4;
                                    $courierFld->setWrapperAttribute('class', 'courierBlk--js d-none');
                                    $courierFld->setFieldTagAttribute('class', 'courierFld--js');
                                    $courierFld->htmlAfterField = '<a href="javascript:void(0)" onclick="trackingUrlFld()">' . Labels::getLabel(
                                        'LBL_OR_TRACK_THROUGH_URL_?',
                                        $adminLangId
                                    ) . '</a>';
                                }
                            }

                            echo $frm->getFormHtml(); ?>
                        </div>
                    </section>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php if ($print) { ?>
    <script>
        window.print();
        window.onafterprint = function() {
            location.href = history.back();
        }
    </script>
<?php } ?>

<script>
    var canShipByPlugin = <?php echo (true === $canShipByPlugin ? 1 : 0); ?>;
    var orderShippedStatus = <?php echo OrderStatus::ORDER_SHIPPED; ?>;
    trackOrder = function(trackingNumber, courier, orderNumber) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('SellerOrders', 'orderTrackingInfo', [trackingNumber, courier, orderNumber]), '', function(res) {
                $.facebox(res, 'medium-fb-width');
                $(".medium-fb-width").parent('.popup').addClass('h-min-auto');
            });
        });
    };
</script>