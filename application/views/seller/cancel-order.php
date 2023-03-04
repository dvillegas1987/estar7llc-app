<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title "><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId);?></h5>
                    <div class="btn-group"><a href="<?php echo UrlHelper::generateUrl('Seller', 'sales');?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_order', $siteLangId);?></a></div>
                </div>
                <div class="card-body ">
                    <div class="box__body">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="info--order">
                                    <p><strong><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId);?>: </strong><?php echo $orderDetail['user_name'];?></p>
                                    <p><strong><?php echo Labels::getLabel('LBL_Status', $siteLangId);?>: </strong><?php echo $orderStatuses[$orderDetail['op_status_id']];?></p>
                                    <p><strong><?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL'));?></p>
                                    <p><strong><?php echo Labels::getLabel('LBL_Delivery', $siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'SHIPPING'));?></p>
                                    <?php if (empty($orderDetail['taxOptions'])) { ?>
                                    <p><strong><?php echo Labels::getLabel('LBL_Tax', $siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'));?></p>
                                    <?php } else {
                                        foreach ($orderDetail['taxOptions'] as $key => $val) { ?>
                                          <p><strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($val['value']); ?></p>
                                        <?php }
                                    }?>
                                    <p><strong><?php echo Labels::getLabel('LBL_Order_Total', $siteLangId);?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER));?></p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="info--order">
                                    <p><strong><?php echo Labels::getLabel('LBL_Invoice', $siteLangId);?> #: </strong><?php echo $orderDetail['op_invoice_number'];?></p>
                                    <p><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId);?>: </strong><?php echo FatDate::format($orderDetail['order_date_added']);?></p>
                                </div>
                            </div>
                        </div>
                        <div class="gap"></div>
						<div class="js-scrollable table-wrap">
							<table class="table">
								<tbody>
									<tr class="">
										<th colspan="2"><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId);?></th>
										<?php if (!empty($orderDetail['shippingAddress'])) { ?>
											<th>
												<?php echo Labels::getLabel('LBL_SHIPPING_METHOD', $siteLangId); ?>
											</th>
										<?php } ?>
										<th><?php echo Labels::getLabel('LBL_Qty', $siteLangId);?></th>
										<th><?php echo Labels::getLabel('LBL_Price', $siteLangId);?></th>
										<th><?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId);?></th>
										<th><?php echo Labels::getLabel('LBL_Total', $siteLangId);?></th>
									</tr>
									<tr>
										<td>
											<?php
											$prodOrBatchUrl = 'javascript:void(0)';
											if ($orderDetail['op_is_batch']) {
												$prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($orderDetail['op_selprod_id']));
												$prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($orderDetail['op_selprod_id'],$siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
											} else {
												if (Product::verifyProductIsValid($orderDetail['op_selprod_id']) == true) {
													$prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($orderDetail['op_selprod_id']));
												}
												$prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($orderDetail['selprod_product_id'], "SMALL", $orderDetail['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
											}  ?>
											<figure class="item__pic"><a href="<?php echo $prodOrBatchUrl;?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $orderDetail['op_product_name'];?>"
														alt="<?php echo $orderDetail['op_product_name']; ?>"></a></figure>
										</td>
										<td>
											<div class="item__description">
												<span class="item__title">
													<a title="<?php echo $orderDetail['op_product_name'];?>" href="<?php echo $prodOrBatchUrl;?>">
														<?php if ($orderDetail['op_selprod_title']!='') {
															echo  $orderDetail['op_selprod_title'].'<br/>';
														} else {
															echo $orderDetail['op_product_name'];
														}
															?>
													</a>
												</span>
												<p><?php echo Labels::getLabel('Lbl_Brand', $siteLangId)?>: <?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail['op_brand_name']);?></p>
												<?php if ($orderDetail['op_selprod_options'] != '') { ?>
												<p><?php echo $orderDetail['op_selprod_options'];?></p>
												<?php }?>
											</div>
										</td>
										<?php if (!empty($orderDetail['shippingAddress'])) { ?>
											<td>
												<?php echo $orderDetail['opshipping_label'];?>
											</td>
										<?php } ?>
										<td><?php echo $orderDetail['op_qty'];?></td>
										<td><?php echo CommonHelper::displayMoneyFormat($orderDetail['op_unit_price']);?></td>
										<td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'shipping'));?></td>
										<td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER));?></td>
									</tr>
								</tbody>
							</table>
						</div>
                        <div class="gap"></div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="info--order">
                                    <h5><?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId);?></h5>
                                    <?php $billingAddress = $orderDetail['billingAddress']['oua_name'].'<br>';
                                        if ($orderDetail['billingAddress']['oua_address1']!='') {
                                            $billingAddress.=$orderDetail['billingAddress']['oua_address1'].'<br>';
                                        }

                                        if ($orderDetail['billingAddress']['oua_address2']!='') {
                                            $billingAddress.=$orderDetail['billingAddress']['oua_address2'].'<br>';
                                        }

                                        if ($orderDetail['billingAddress']['oua_city']!='') {
                                            $billingAddress.=$orderDetail['billingAddress']['oua_city']. ', ';
                                        }

                                        if ($orderDetail['billingAddress']['oua_state']!='') {
                                            $billingAddress.=$orderDetail['billingAddress']['oua_state']. ', ';
                                        }
										
										if ($orderDetail['billingAddress']['oua_country'] != '') {
											$billingAddress .= $orderDetail['billingAddress']['oua_country'];
										}
							
                                        if ($orderDetail['billingAddress']['oua_zip']!='') {
                                            $billingAddress.= '-'.$orderDetail['billingAddress']['oua_zip'];
                                        }

                                        if ($orderDetail['billingAddress']['oua_phone']!='') {
                                            $billingAddress.= '<br>'.ValidateElement::formatDialCode($orderDetail['billingAddress']['oua_phone_dcode']) . $orderDetail['billingAddress']['oua_phone'];
                                        }
                                    ?>
                                    <p><?php echo $billingAddress;?></p>
                                </div>
                            </div>
                            <?php if (!empty($orderDetail['shippingAddress'])) {?>
                                <div class="col-lg-6 col-md-6 col-sm-6">
                                    <div class="info--order">
                                        <h5><?php echo Labels::getLabel('LBL_Shipping_Detail', $siteLangId);?></h5>
                                        <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'].'<br>';
                                            if ($orderDetail['shippingAddress']['oua_address1']!='') {
                                                $shippingAddress.=$orderDetail['shippingAddress']['oua_address1'].'<br>';
                                            }

                                            if ($orderDetail['shippingAddress']['oua_address2']!='') {
                                                $shippingAddress.=$orderDetail['shippingAddress']['oua_address2'].'<br>';
                                            }

                                            if ($orderDetail['shippingAddress']['oua_city']!='') {
                                                $shippingAddress.=$orderDetail['shippingAddress']['oua_city'].',';
                                            }

                                            if ($orderDetail['shippingAddress']['oua_state']!='') {
                                                $shippingAddress.=$orderDetail['shippingAddress']['oua_state'] . ', ';
                                            }
                                            
                                            if ($orderDetail['shippingAddress']['oua_country'] != '') {
                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_country'];
                                            }
            
                                            if ($orderDetail['shippingAddress']['oua_zip']!='') {
                                                $shippingAddress.= '-'.$orderDetail['shippingAddress']['oua_zip'];
                                            }

                                            if ($orderDetail['shippingAddress']['oua_phone']!='') {
                                                $shippingAddress.= '<br>'.ValidateElement::formatDialCode($orderDetail['shippingAddress']['oua_phone_dcode']) . $orderDetail['shippingAddress']['oua_phone'];
                                            }
                                        ?>
                                        <p><?php echo $shippingAddress;?></p>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (!empty($orderDetail['pickupAddress'])) {?>
                                <div class="col-lg-6 col-md-6 col-sm-6">
                                    <div class="info--order">
                                        <h5><?php echo Labels::getLabel('LBL_Pickup_Details', $siteLangId); ?></h5>
                                        <p>
                                        <strong>
                                            <?php
                                            $opshippingDate = isset($orderDetail['opshipping_date']) ? $orderDetail['opshipping_date'] . ' ' : '';
                                            $timeSlotFrom = isset($orderDetail['opshipping_time_slot_from']) ? date('H:i', strtotime($orderDetail['opshipping_time_slot_from'])) . ' - ' : '';
                                            $timeSlotTo = isset($orderDetail['opshipping_time_slot_to']) ? date('H:i', strtotime($orderDetail['opshipping_time_slot_to'])) : '';
                                            echo $opshippingDate . ' (' . $timeSlotFrom . $timeSlotTo . ')'; 
                                            ?>
                                        </strong><br>
                                                <?php echo $orderDetail['pickupAddress']['oua_name']; ?>, 
                                                <?php
                                                $pickupAddress = '';
                                                if ($orderDetail['pickupAddress']['oua_address1'] != '') {
                                                    $pickupAddress .= $orderDetail['pickupAddress']['oua_address1'] . '<br>';
                                                }

                                                if ($orderDetail['pickupAddress']['oua_address2'] != '') {
                                                    $pickupAddress .= $orderDetail['pickupAddress']['oua_address2'] . '<br>';
                                                }

                                                if ($orderDetail['pickupAddress']['oua_city'] != '') {
                                                    $pickupAddress .= $orderDetail['pickupAddress']['oua_city'] . ',';
                                                }

                                                if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                                    $pickupAddress .= ' ' . $orderDetail['pickupAddress']['oua_state'];
                                                }

                                                if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                                    $pickupAddress .= '-' . $orderDetail['pickupAddress']['oua_zip'];
                                                }

                                                if ($orderDetail['pickupAddress']['oua_phone'] != '') {
                                                    $pickupAddress .= '<br>' . Labels::getLabel('LBL_PHONE', $siteLangId) . ': ' . ValidateElement::formatDialCode($orderDetail['pickupAddress']['oua_phone_dcode']) . $orderDetail['pickupAddress']['oua_phone'];
                                                }
                                                echo $pickupAddress; ?>
                                    </div>
                                </div>
                            <?php }?>
                        </div>


                        <span class="gap"></span>
                        <?php if (!empty($orderDetail['comments'])) {?>
                        <div class="section--repeated js-scrollable table-wrap">
                            <h5><?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId);?></h5>
                            <table class="table align--left">
                                <tbody>
                                    <tr class="hide--mobile">
                                        <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId);?></th>
                                        <th><?php echo Labels::getLabel('LBL_Customer_Notified', $siteLangId);?></th>
                                        <th><?php echo Labels::getLabel('LBL_Status', $siteLangId);?></th>
                                        <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId);?></th>
                                    </tr>
                                    <?php
                                    foreach ($orderDetail['comments'] as $row) {?>
                                        <tr>
                                            <td><?php echo FatDate::format($row['oshistory_date_added']);?></td>
                                            <td><?php echo $yesNoArr[$row['oshistory_customer_notified']];?></td>
                                            <td><?php echo $orderStatuses[$row['oshistory_orderstatus_id']];?></td>
                                            <td><?php echo nl2br($row['oshistory_comments']);?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                        <?php if (!$notEligible) {?>
                        <div class="gap"></div>
                        <div class="section--repeated no-print cancelReason-js">
                            <h5><?php echo Labels::getLabel('LBL_Reason_for_cancellation', $siteLangId);?></h5>
                            <?php
                        $frm->setFormTagAttribute('onsubmit', 'cancelReason(this); return(false);');
                        $frm->setFormTagAttribute('class', 'form');
                        $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                        $frm->developerTags['fld_default_col'] = 12;
                            
                        $btnSubmit = $frm->getField('btn_submit');
                        $btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
                        echo $frm->getFormHtml();?>
                        </div>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
