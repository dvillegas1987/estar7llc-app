<?php defined('SYSTEM_INIT') or die('Invalid Usage . '); ?>
<table width="100%" cellspacing="0" cellpadding="20" border="0" style="font-size: 14px;background: #f2f2f2;">
    <tbody>
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;border:2px solid #ddd;background-color: #fff;">
                    <tbody>
                        <tr>
                            <td>
                                <!--main Start-->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 32px;font-weight: 700;color: #000;padding: 15px;text-align:center;border-bottom: 1px solid #ddd;"><?php echo Labels::getlabel('LBL_Tax_Invoice', $adminLangId); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width="100%" border="0" cellpadding="10px" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding:15px;border-bottom: 1px solid #ddd;">
                                                <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Sold_By', $adminLangId); ?>: <?php echo $orderDetail['op_shop_name']; ?></h4>
                                                <p style="margin:0;padding-bottom: 15px;"><?php echo Labels::getLabel('LBL_Shop_Address', $adminLangId); ?>: <?php echo $orderDetail['shop_city'] . ', ' . $orderDetail['shop_state_name'] . ', ' . $orderDetail['shop_country_name'] . ' - ' . $orderDetail['shop_postalcode']; ?></p>
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0"> <?php $shopCodes = $orderDetail['shop_invoice_codes'];
                                                                                                                $codesArr = explode("\n", $shopCodes); ?>
                                                    <tbody>
                                                        <?php $count = 1; ?>
                                                        <tr>
                                                            <?php foreach ($codesArr as $code) { ?>
                                                                <td style="<?php echo ($count % 2 == 0) ? 'text-align: right;' : ''; ?> font-weight: 700;"><?php echo $code; ?></td>
                                                            <?php
                                                                if ($count % 3 == 0) {
                                                                    echo '</tr><tr>';
                                                                }
                                                                $count++;
                                                            } ?>

                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php if ($orderDetail['op_product_type'] != Product::PRODUCT_TYPE_DIGITAL) { ?>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="border-bottom: 1px solid #ddd;">
                                                    <table width="100%" border="0" cellpadding="10px" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding:15px;border-right: 1px solid #ddd;">
                                                                    <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Bill_to', $adminLangId); ?></h4>
                                                                    <p style="margin:0;padding-bottom: 15px;">
                                                                        <?php $billingAddress = $orderDetail['billingAddress']['oua_name'] . '<br/>';
                                                                        if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_address1'] . '<br/>';
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_address2'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_address2'] . '<br/>';
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_city'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_city'] . ', ';
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_state'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_state'] . ', ';
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_country'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_country'];
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_zip'] != '') {
                                                                            $billingAddress  .= '-' . $orderDetail['billingAddress']['oua_zip'];
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_phone'] != '') {
                                                                            $billingAddress  .= '<br/>' . ValidateElement::formatDialCode($orderDetail['billingAddress']['oua_phone_dcode']) . $orderDetail['billingAddress']['oua_phone'];
                                                                        }
                                                                        ?>
                                                                        <?php echo $billingAddress; ?>
                                                                    </p>
                                                                </td>
                                                                <?php if (!empty($orderDetail['shippingAddress'])) {  ?>
                                                                    <td style="padding:15px;">
                                                                        <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Ship_to', $adminLangId); ?></h4>
                                                                        <p style="margin:0;padding-bottom: 15px;">
                                                                            <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'] . '<br/>';
                                                                            if ($orderDetail['shippingAddress']['oua_address1'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_address1'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_address2'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_city'] . ',';
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_state'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_state'] . ', ';
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_country'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_country'];
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_zip'] != '') {
                                                                                $shippingAddress .= '-' . $orderDetail['shippingAddress']['oua_zip'];
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_phone'] != '') {
                                                                                $shippingAddress .= '<br/>' . ValidateElement::formatDialCode($orderDetail['shippingAddress']['oua_phone_dcode']) . $orderDetail['shippingAddress']['oua_phone'];
                                                                            } ?>
                                                                            <?php echo $shippingAddress; ?>
                                                                        </p>
                                                                    </td>
                                                                <?php } ?>
                                                                <?php if (!empty($orderDetail['pickupAddress'])) { ?>
                                                                    <td style="padding:15px;">
                                                                        <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Pickup_Details', $adminLangId); ?></h4>
                                                                        <p style="margin:0;padding-bottom: 15px;">
                                                                            <?php $pickUpAddress = $orderDetail['pickupAddress']['oua_name'] . '<br/>';
                                                                            if ($orderDetail['pickupAddress']['oua_address1'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_address1'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_address2'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_address2'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_city'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_city'] . ',';
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_state'];
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                                                                $pickUpAddress .= '-' . $orderDetail['pickupAddress']['oua_zip'];
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_phone'] != '') {
                                                                                $pickUpAddress .= '<br/>' . ValidateElement::formatDialCode($orderDetail['pickupAddress']['oua_phone_dcode']) . $orderDetail['pickupAddress']['oua_phone'];
                                                                            } ?>
                                                                            <?php echo $pickUpAddress; ?>
                                                                        </p>
                                                                    </td>
                                                                <?php } ?>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php } ?>

                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="border-bottom: 1px solid #ddd;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding:15px;">
                                                                <p><strong><?php echo Labels::getLabel('LBL_Order', $adminLangId); ?>:</strong> <?php echo $orderDetail['op_order_id']; ?> </p>
                                                                <p><strong><?php echo Labels::getLabel('LBL_Invoice_Number', $adminLangId); ?>:</strong> <?php echo $orderDetail['op_invoice_number']; ?></p>
                                                                <p><strong><?php echo Labels::getLabel('LBL_Payment_Method', $adminLangId); ?>:</strong>
                                                                    <?php
                                                                    $paymentMethodName = empty($orderDetail['plugin_name']) ? $orderDetail['plugin_identifier'] : $orderDetail['plugin_name'];
                                                                    if (!empty($paymentMethodName) && $orderDetail['order_pmethod_id'] > 0 && $orderDetail['order_is_wallet_selected'] > 0) {
                                                                        $paymentMethodName  .= ' + ';
                                                                    }
                                                                    if ($orderDetail['order_is_wallet_selected'] > 0) {
                                                                        $paymentMethodName .= Labels::getLabel("LBL_Wallet", $adminLangId);
                                                                    }
                                                                    echo $paymentMethodName;
                                                                    ?>
                                                                </p>
                                                            </td>
                                                            <td style="padding:15px;">
                                                                <p><strong><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?>:</strong> <?php echo FatDate::format($orderDetail['order_date_added']); ?> </p>
                                                                <?php
                                                                /* <p><strong><?php echo Labels::getLabel('LBL_Invoice_Date', $adminLangId); ?>:</strong> <?php echo (!empty($orderDetail['opshipping_date'])) ? FatDate::format($orderDetail['opshipping_date']) : 'NA'; ?></p> */
                                                                ?>
                                                                <?php if (!empty($orderDetail['opship_tracking_number'])) { ?>
                                                                    <p><strong><?php echo Labels::getLabel('LBL_Tracking_ID', $adminLangId); ?>:</strong> <?php echo (!empty($orderDetail['opship_tracking_number'])) ? $orderDetail['opship_tracking_number'] : 'NA'; ?> </p>
                                                                    <?php /* <p><strong><?php echo Labels::getLabel('LBL_Tote-Id', $adminLangId);?>: </strong>  LOREM </p> */ ?>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="border-bottom: 1px solid #ddd;">
                                                <table width="100%" border="0" cellpadding="10px" cellspacing="0">
                                                    <tbody>
                                                        <tr>
                                                            <th style="padding:10px;font-size:12px;text-align: left; border-bottom:1px solid #ddd;" colspan="<?php echo $orderDetail['op_tax_collected_by_seller'] ? 1: 2;  ?>"><?php echo Labels::getLabel('LBL_Item', $adminLangId); ?></th>
                                                            <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                                                <th style="padding:10px;font-size:12px;text-align: center; border-bottom:1px solid #ddd;">
                                                                    <?php if (FatApp::getConfig('CONF_TAX_CATEGORIES_CODE', FatUtility::VAR_INT, 1)) {
                                                                            echo ($orderDetail['op_tax_code'] != '') ? $orderDetail['op_tax_code'] . ' (' . Labels::getLabel('LBL_Tax', $adminLangId) . ')' : Labels::getLabel('LBL_Tax', $adminLangId); ?>
                                                                        <?php } else {
                                                                            echo Labels::getLabel('LBL_Tax', $adminLangId);
                                                                    } ?>
                                                                </th>
                                                            <?php } ?>
                                                            <th style="padding:10px;font-size:12px;text-align: center; border-bottom:1px solid #ddd;"><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>
                                                            <th style="padding:10px;font-size:12px;text-align: center; border-bottom:1px solid #ddd;"><?php echo Labels::getLabel('LBL_Price', $adminLangId); ?></th>
                                                            <?php /* <th style="padding:10px;font-size:12px;text-align: center; border-bottom:1px solid #ddd;"><?php echo Labels::getLabel('LBL_Savings', $adminLangId);?></th> */ ?>
                                                            <th style="padding:10px;font-size:12px;text-align: center; border-bottom:1px solid #ddd;"><?php echo Labels::getLabel('LBL_Total_Amount', $adminLangId); ?></th>
                                                        </tr>
                                                        <tr>
                                                            <?php $volumeDiscount = CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT'); ?>
                                                            <td style="padding:10px;font-size:12px;text-align: left;" colspan="<?php echo $orderDetail['op_tax_collected_by_seller'] ? 1: 2;  ?>">
                                                                <?php
                                                                echo ($orderDetail['op_selprod_title'] != '') ? $orderDetail['op_selprod_title'] : $orderDetail['op_product_name'];
                                                                echo '<br>';
                                                                echo Labels::getLabel('Lbl_Brand', $adminLangId) . ' : ';
                                                                echo CommonHelper::displayNotApplicable($adminLangId, $orderDetail['op_brand_name']);
                                                                echo '<br>';
                                                                if ($orderDetail['op_selprod_options'] != '') {
                                                                    echo $orderDetail['op_selprod_options'] . '<br>';
                                                                }
                                                                echo Labels::getLabel('LBL_Sold_By', $adminLangId) . ': ' . $orderDetail['op_shop_name'];
                                                                if ($orderDetail['op_shipping_duration_name'] != '') {
                                                                    echo '<br>';
                                                                    echo Labels::getLabel('LBL_Shipping_Method', $adminLangId) . ' : ';
                                                                    echo $orderDetail['op_shipping_durations'] . '-' . $orderDetail['op_shipping_duration_name'];
                                                                }
                                                                ?>
                                                            </td>
                                                            <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                                                <td style="padding:10px;font-size:12px;text-align: center;">
                                                                    <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true); ?>
                                                                </td>
                                                                    <?php } ?>
                                                            <td style="padding:10px;font-size:12px;text-align: center;"><?php echo $orderDetail['op_qty']; ?></td>
                                                            <td style="padding:10px;font-size:12px;text-align: center;"><?php echo CommonHelper::displayMoneyFormat($orderDetail['op_unit_price'], true, false, true, false, true); ?></td>
                                                            <?php /* $couponDiscount = CommonHelper::orderProductAmount($orderDetail, 'DISCOUNT'); 
                                                            $volumeDiscount = CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT'); 
                                                            $totalSavings = $couponDiscount + $volumeDiscount; ?>     
                                                            <td style="padding:10px;font-size:12px;text-align: center;">
                                                                <?php echo CommonHelper::displayMoneyFormat($totalSavings); ?>
                                                            </td>  */ ?>
                                                            <td style="padding:10px;font-size:12px;text-align: center;"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL'), true, false, true, false, true); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:10px;font-size:12px;font-size:18px;text-align: left;font-weight:700;background-color: #f0f0f0;" colspan="2"><?php echo Labels::getLabel('Lbl_Summary', $adminLangId) ?> </td>
                                                            <td style="padding:10px;font-size:12px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo $orderDetail['op_qty']; ?></strong></td>
                                                            <td style="padding:10px;font-size:12px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo CommonHelper::displayMoneyFormat($orderDetail['op_unit_price'], true, false, true, false, true); ?></strong></td>
                                                            <?php /* <td style="padding:10px;font-size:12px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo CommonHelper::displayMoneyFormat($totalSavings); ?></strong></td> */ ?>
                                                            <td style="padding:10px;font-size:12px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL'), true, false, true, false, true); ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <?php /* <td style="padding:15px 15px;font-size:20px;text-align: left;font-weight:700; vertical-align: top;" colspan="3" rowspan="6">
											<?php
											if ($totalSavings != 0) {
												$str = Labels::getLabel("LBL_You_have_saved_{totalsaving}_on_this_order", $adminLangId);
												$str = str_replace("{totalsaving}", -$totalSavings, $str);
												echo $str;
											} ?> 
											</td> */ ?>
                                                            <td style="padding:15px 15px;font-size:20px;text-align: left;font-weight:700; vertical-align: top;" colspan="2" rowspan="5"></td>
                                                            <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('Lbl_Cart_Total', $adminLangId) ?></td>
                                                            <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'cart_total'), true, false, true, false, true); ?></td>
                                                        </tr>
                                                        <?php if ($shippedBySeller) { ?>
                                                            <tr>
                                                                <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Delivery_Charges', $adminLangId) ?></td>
                                                                <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'shipping'), true, false, true, false, true); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                                            <tr>
                                                                <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Total_Tax', $adminLangId) ?></td>
                                                                <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php if (array_key_exists('order_rounding_off', $orderDetail) && 0 != $orderDetail['order_rounding_off']) { ?>
                                                            <tr>
                                                                <td style="padding:10px; font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo (0 < $orderDetail['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $adminLangId) : Labels::getLabel('LBL_Rounding_Down', $adminLangId); ?></td>
                                                                <td style="padding:10px; font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"> <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_rounding_off'], true, false, true, false, true); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php $volumeDiscount = CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT');
                                                        if ($volumeDiscount != 0) { ?>
                                                            <tr>
                                                                <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId) ?></td>
                                                                <td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($volumeDiscount, true, false, true, false, true); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php /* $rewardPointDiscount = CommonHelper::orderProductAmount($orderDetail, 'REWARDPOINT');
										if ($rewardPointDiscount != 0) { ?>
										<tr>                                                                              
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $adminLangId) ?></td>                    
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount, true, false, true, false, true); ?></td>                                           
										</tr>
										<?php } */ ?>
                                                        <?php
                                                        /* if (CommonHelper::orderProductAmount($orderDetail, 'TAX') > 0) { ?>
										<tr>                                                                              
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Tax_Charges', $adminLangId) ?></td>                    
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true); ?></td>      
										</tr>
										<?php } */ ?>
                                                        <?php
                                                        /* if ($totalSavings != 0) { ?>
										<tr>                                                                              
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Total_Savings', $adminLangId) ?></td>                    
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($totalSavings, true, false, true, false, true); ?></td>      
										</tr>
										<?php } */ ?>
                                                        <?php
                                                        /* $rewardPointDiscount = CommonHelper::orderProductAmount($orderDetail, 'REWARDPOINT');
										if ($rewardPointDiscount != 0) { ?>
										<tr>                                                                              
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Reward_Points', $adminLangId) ?></td>                    
											<td style="padding:10px;font-size:12px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount, true, false, true, false, true); ?></td>      
										</tr>
										<?php } */ ?>
                                                        <tr>
                                                            <td style="padding:10px;font-size:12px;text-align: center;font-weight:700;font-size: 18px;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><strong><?php echo Labels::getLabel('LBL_Grand_Total', $adminLangId) ?></strong> </td>
                                                            <td style="padding:10px;font-size:12px;text-align: center;font-weight:700;font-size: 18px;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER), true, false, true, false, true); ?></strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding:15px;vertical-align: top; border:none;">
                                                <h2 style="font-size: 20px;text-align: center;"><?php echo $orderDetail['op_shop_name']; ?></h2>
                                                <span style="padding-top: 150px;display: block;text-align: center;"><?php echo Labels::getLabel('LBL_Authorized_Signatory', $adminLangId); ?> </span>
                                            </td>
                                            <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                                <td style="text-align: center;  border:none;">
                                                    <table width="100%" border="0" cellpadding="10px" cellspacing="0" style="">
                                                        <tbody>
                                                            <tr>
                                                                <th style="padding:15px;background-color: #f0f0f0;" colspan="2"><?php echo Labels::getLabel('LBL_Tax_break-up', $adminLangId); ?></th>
                                                            </tr>

                                                            <?php if (!empty($orderDetail['taxOptions'])) {
                                                                    foreach ($orderDetail['taxOptions'] as $key => $val) { ?>
                                                                     <tr> 
                                                                        <td style="padding:10px;border:1px solid #ddd;">
                                                                            <?php echo CommonHelper::displayTaxPercantage($val, true) ?>
                                                                        </td>
                                                                        <td style="padding:10px;border:1px solid #ddd;">
                                                                            <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true) ?>
                                                                        </td>
                                                                    </tr> 
                                                                <?php
                                                                    }
                                                                } ?>                                       
                                                            <tr>
                                                                <td style="padding:10px;font-size:12px;text-align: left;border:1px solid #ddd;border-bottom:none;border-right:none;font-size: 12px;background-color: #f0f0f0;" colspan="4">*<?php echo Labels::getLabel('LBL_Appropriated_product-wise_and_Rate_applicable_thereunder', $adminLangId); ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width="100%" border="0" cellpadding="10px" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding:20px 15px;border-top:1px solid #ddd">
                                                <p><strong><?php echo Labels::getLabel('LBL_Regd._office', $adminLangId); ?>:</strong><?php echo nl2br(FatApp::getConfig('CONF_ADDRESS_' . $adminLangId, FatUtility::VAR_STRING, '')); ?></p>
                                                <?php $site_conatct = FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_INT, '');
                                                $email_id = FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, '');
                                                if ($site_conatct || $email_id) { ?>
                                                    <p><strong><?php echo Labels::getLabel('LBL_Contact', $adminLangId) ?>:</strong>
                                                        <?php if ($site_conatct) {
                                                            echo $site_conatct;
                                                        } ?>
                                                        <?php if ($email_id) {
                                                            echo '|| ' . $email_id;
                                                        } ?>
                                                    </p>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--main End-->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>