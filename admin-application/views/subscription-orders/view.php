<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>

<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Subscription_Order_Detail', $adminLangId); ?>
                        </h4>
                        <?php
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => false,
                            'otherButtons' => [
                                [
                                    'attr' => [
                                        'href' => UrlHelper::generateUrl('SubscriptionOrders'),
                                        'title' => Labels::getLabel('LBL_Back_To_Subscription_Orders', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-arrow-left"></i>'
                                ],
                            ]
                        ];
    
                        $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="sectionbody">
                            <div class="box_content clearfix toggle_container">
                                <table class="table table--details">
                                    <tr>
                                        <td><strong><?php echo Labels::getLabel('LBL_Order/Invoice_ID', $adminLangId); ?>:</strong>
                                            <?php echo $order["order_id"]; ?>
                                        </td>
                                        <td><strong><?php echo Labels::getLabel('LBL_Payment_Status', $adminLangId); ?>:
                                            </strong><?php echo Orders::getOrderPaymentStatusArr($adminLangId)[$order['order_payment_status']]?>
                                        </td>
                                        <td><strong><?php echo Labels::getLabel('LBL_Customer', $adminLangId); ?>:</strong>
                                            <?php echo $order["buyer_user_name"]?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo Labels::getLabel('LBL_PAYMENT_MODE', $adminLangId); ?>:
                                            </strong><?php echo ($order['order_pmethod_id']) ? CommonHelper::displayNotApplicable($adminLangId, $order["plugin_name"]) : 'Wallet'; ?>
                                        </td>
                                        <td><strong><?php echo Labels::getLabel('LBL_Order_Date', $adminLangId); ?>:
                                            </strong><?php echo FatDate::format($order['order_date_added'], true, true, FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())); ?>
                                        </td>
                                        <td><strong><?php echo Labels::getLabel('LBL_Order_Amount', $adminLangId); ?>:</strong><?php echo CommonHelper::displayMoneyFormat($order["order_net_amount"], true, true); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?>:
                                            </strong>-<?php echo CommonHelper::displayMoneyFormat($order["order_discount_total"], true, true); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
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
                                <th>#</th>
                                <th><?php echo Labels::getLabel('LBL_Order_Invoice_ID', $adminLangId); ?>
                                </th>
                                <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?>
                                </th>
                                <th><?php echo Labels::getLabel('LBL_Subscription_Details', $adminLangId); ?>
                                </th>
                                <th><?php echo Labels::getLabel('LBL_Subscription_Validation', $adminLangId); ?>
                                </th>
                                <th><?php echo Labels::getLabel('LBL_Unit_Price', $adminLangId); ?>
                                </th>
                                <th><?php echo Labels::getLabel('LBL_Cart_Total', $adminLangId); ?>
                                </th>
                                <?php if ($order['order_discount_total'] > 0) {?>
                                <th><?php echo Labels::getLabel('LBL_Discount', $adminLangId); ?>
                                </th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Order_Total', $adminLangId); ?>
                                </th>
                            </tr>
                            <?php
                                    $k = 1;
                                    $cartTotal = 0;
                                    $shippingTotal = 0;
                                    foreach ($order["products"] as $op) {
                                        ?>
                            <tr>
                                <td><?php echo $k; ?>
                                </td>
                                <td><?php echo $op['ossubs_invoice_number']; ?>
                                </td>
                                <td><?php if ($op['ossubs_status_id']==FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') && $op['ossubs_till_date']<date("Y-m-d")) {
                                            echo Labels::getLabel('LBL_Expired', $adminLangId);
                                        } else {
                                            echo $orderStatuses[$op['ossubs_status_id']];
                                        } ?>
                                </td>
                                <td><?php echo OrderSubscription::getSubscriptionTitle($op, $adminLangId); ?>
                                </td>
                                <td><?php echo $op['ossubs_from_date']. " - ".$op['ossubs_till_date']; ?>
                                </td>

                                <td><?php echo CommonHelper::displayMoneyFormat($op["ossubs_price"], true, true); ?>
                                </td>
                                <td><?php echo CommonHelper::displayMoneyFormat($op['ossubs_price'], true, true); ?>
                                </td>
                                <?php if ($order['order_discount_total'] > 0) {?>
                                <td>-<?php echo CommonHelper::displayMoneyFormat($order['order_discount_total'], true, true); ?>
                                </td>
                                <?php } ?>
                                <td><strong><?php echo CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true); ?></strong>
                                </td>
                            </tr>
                            <?php
                                        $k++;
                                    }
                                    ?>

                        </table>
                    </div>
                </section>

                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?>
                        </h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?></strong>
                                    </th>
                                    <th><strong><?php echo Labels::getLabel('LBL_Email', $adminLangId); ?></strong>
                                    </th>
                                    <th><strong><?php echo Labels::getLabel('LBL_Phone_Number', $adminLangId); ?></strong>
                                    </th>
                                </tr>
                                <tr>
                                    <td><?php echo $order["buyer_user_name"]?>
                                    </td>
                                    <td><?php echo $order['buyer_email']; ?>
                                    </td>
                                    <td><?php echo CommonHelper::displayNotApplicable($adminLangId, $order['buyer_phone']); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>


                <?php if (count($order["comments"])>0) {?>
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
                                <?php foreach ($order["comments"] as $key=>$row) {?>
                                <tr>
                                    <td><?php echo FatDate::format($row['oshistory_date_added']);?>
                                    </td>
                                    <td><?php echo $yesNoArr[$row['oshistory_customer_notified']];?>
                                    </td>
                                    <td><?php echo ($row['oshistory_orderstatus_id']>0)?$orderStatuses[$row['oshistory_orderstatus_id']]:CommonHelper::displayNotApplicable($adminLangId, '');?>
                                    </td>
                                    <td>
                                        <div class="break-me"><?php echo nl2br($row['oshistory_comments']);?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php }?>
                <?php if (!empty($order['payments'])) {?>
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
                                    <th width="40%"><?php echo Labels::getLabel('LBL_Gateway_Response', $adminLangId); ?>
                                    </th>
                                </tr>
                                <?php foreach ($order["payments"] as $key=>$row) { ?>
                                <tr>
                                    <td><?php echo FatDate::format($row['opayment_date']);?>
                                    </td>
                                    <td><?php echo $row['opayment_gateway_txn_id'];?>
                                    </td>
                                    <td><?php echo $row['opayment_method'];?>
                                    </td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($row['opayment_amount'], true, true);?>
                                    </td>
                                    <td>
                                        <div class="break-me"><?php echo nl2br($row['opayment_comments']);?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="break-me"><?php echo nl2br($row['opayment_gateway_response']);?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php }?>
                <?php if (!$order["order_payment_status"] && $canEdit) {?>
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
                <?php }?>
            </div>
        </div>
    </div>
</div>