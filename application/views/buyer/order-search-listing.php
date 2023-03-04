<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'order_id'    =>    Labels::getLabel('LBL_Order_ID_Date', $siteLangId),
        'product'    =>    Labels::getLabel('LBL_Details', $siteLangId),
        'total'        =>    Labels::getLabel('LBL_Total', $siteLangId),
        'status'    =>    Labels::getLabel('LBL_Status', $siteLangId),
        'action'    =>    '',
    );
    $tableClass = '';
    if (0 < count($orders)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = 0;
    $canCancelOrder = true;
    $canReturnRefund = true;
    foreach ($orders as $sn => $order) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));
        $orderDetailUrl = UrlHelper::generateUrl('Buyer', 'viewOrder', array($order['order_id'], $order['op_id']));

        if ($order['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $canCancelOrder = (in_array($order["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses(true)));
            $canReturnRefund = (in_array($order["op_status_id"], (array) Orders::getBuyerAllowedOrderReturnStatuses(true)));
        } else {
            $canCancelOrder = (in_array($order["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses()));
            $canReturnRefund = (in_array($order["op_status_id"], (array) Orders::getBuyerAllowedOrderReturnStatuses()));
        }
        $isValidForReview = false;
        if (in_array($order["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $isValidForReview = true;
        }
        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'order_id':
                    $txt = '<a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . $orderDetailUrl . '">';
                    if ($order['totOrders'] > 1) {
                        $txt .= $order['op_invoice_number'];
                    } else {
                        $txt .= $order['order_id'];
                    }
                    $txt .= '</a><br/>' . FatDate::format($order['order_date_added']);
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'product':
                    $txt = '<div class="item__description">';
                    if ($order['op_selprod_title'] != '') {
                        $txt .= '<div class="item__title">' . $order['op_selprod_title'] . '</div>';
                    }
                    $txt .= '<div class="item__sub_title">' . $order['op_product_name'] . ' (' . Labels::getLabel('LBL_Qty', $siteLangId) . ': ' . $order['op_qty'] . ')</div>';
                    $txt .= '<div class="item__brand">';
                    if (!empty($order['op_brand_name'])) {
                        $txt .=  Labels::getLabel('LBL_Brand', $siteLangId) . ': ' . $order['op_brand_name'];
                    }
                    if (!empty($order['op_brand_name']) && !empty($order['op_selprod_options'])) {
                        $txt .= ' | ';
                    }
                    if ($order['op_selprod_options'] != '') {
                        $txt .= $order['op_selprod_options'];
                    }
                    $txt .= '</div>';
                    if ($order['totOrders'] > 1) {
                        $txt .= '<div class="item__specification">' . Labels::getLabel('LBL_Part_combined_order', $siteLangId) . ' <a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . UrlHelper::generateUrl('Buyer', 'viewOrder', array($order['order_id'])) . '">' . $order['order_id'] . '</div>';
                    }
                    $txt .= '</div>';
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'total':
                    $txt = '';
                    /* if( $order['totOrders'] == 1 ){
                    $txt .= CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true);
                } else {
                    $txt .= '-';
                } */
                    // var_dump($order['totOrders']);
                    // CommonHelper::displayMoneyFormat($order['order_net_amount'], true, true);
                    $txt .= CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order));
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'status':
                    $orderStatus = ucwords($order['orderstatus_name']);
                    if (Orders::ORDER_PAYMENT_CANCELLED == $order["order_payment_status"]) {
                        $orderStatus = Labels::getLabel('LBL_CANCELLED', $siteLangId);
                        $labelClass = 'label-danger';
                    } else {
                        $pMethod = '';
                        $paymentMethodCode = Plugin::getAttributesById($order['order_pmethod_id'], 'plugin_code');

                        /* if (strtolower($paymentMethodCode) == 'cashondelivery' && $order['opshipping_fulfillment_type'] == Shipping::FULFILMENT_PICKUP && $order['op_status_id'] != FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")) {
                            $orderStatus = Labels::getLabel('LBL_PAY_ON_PICKUP', $siteLangId);
                        } else if (strtolower($paymentMethodCode) == 'cashondelivery' && $order['order_status'] == FatApp::getConfig('CONF_DEFAULT_ORDER_STATUS')) {
                            $pMethod = " - " . $order['plugin_name'];
                        } */
                        if (in_array(strtolower($paymentMethodCode), ['cashondelivery', 'payatstore'])) {
                            if ($orderStatus != $order['plugin_name']) {
                                $orderStatus .= " - " . $order['plugin_name'];
                            }
                        }

                        $labelClass = isset($classArr[$order['orderstatus_color_class']]) ? $classArr[$order['orderstatus_color_class']] : 'label-info';
                    }

                    $td->appendElement('span', array('class' => 'label label-inline ' . $labelClass), $orderStatus . '<br>', true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);

                    $opCancelUrl = UrlHelper::generateUrl('Buyer', 'orderCancellationRequest', array($order['op_id']));
                    $now = time(); // or your date as well
                    $orderDate = strtotime($order['order_date_added']);
                    $datediff = $now - $orderDate;
                    $daysSpent = round($datediff / (60 * 60 * 24));
                    $returnAge = !empty($order['return_age']) ? $order['return_age'] : FatApp::getConfig("CONF_DEFAULT_RETURN_AGE", FatUtility::VAR_INT, 7);

                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array(
                            'href' => $orderDetailUrl, 'class' => '',
                            'title' => Labels::getLabel('LBL_View_Order', $siteLangId)
                        ),
                        '<i class="fa fa-eye"></i>',
                        true
                    );

                    if ($canCancelOrder && false === OrderCancelRequest::getCancelRequestById($order['op_id']) && $order['cancellation_age'] >= $daysSpent) {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                            'a',
                            array(
                                'href' => $opCancelUrl, 'class' => '',
                                'title' => Labels::getLabel('LBL_Cancel_Order', $siteLangId)
                            ),
                            '<i class="fas fa-times"></i>',
                            true
                        );
                    }
                    $canSubmitFeedback = Orders::canSubmitFeedback($order['order_user_id'], $order['order_id'], $order['op_selprod_id']);
                    if ($canSubmitFeedback && $isValidForReview) {
                        $opFeedBackUrl = UrlHelper::generateUrl('Buyer', 'orderFeedback', array($order['op_id']));
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                            'a',
                            array(
                                'href' => $opFeedBackUrl, 'class' => '',
                                'title' => Labels::getLabel('LBL_Feedback', $siteLangId)
                            ),
                            '<i class="fa fa-star"></i>',
                            true
                        );
                    }

                    if ($canReturnRefund && ($order['return_request'] == 0 && $order['cancel_request'] == 0) && $returnAge >= $daysSpent) {
                        $opRefundRequestUrl = UrlHelper::generateUrl('Buyer', 'orderReturnRequest', array($order['op_id']));
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                            'a',
                            array(
                                'href' => $opRefundRequestUrl, 'class' => '',
                                'title' => Labels::getLabel('LBL_Refund', $siteLangId)
                            ),
                            '<i class="fas fa-dollar-sign"></i>',
                            true
                        );
                    }

                    $cartUrl = UrlHelper::generateUrl('cart');
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array(
                            'href' => 'javascript:void(0)', 'onClick' => 'return addItemsToCart("' . $order['order_id'] . '");',
                            'title' => Labels::getLabel('LBL_Re-Order', $siteLangId)
                        ),
                        '<i class="fa fa-cart-plus"></i>',
                        true
                    );

                    if (!$order['order_deleted'] && !$order["order_payment_status"] && 'TransferBank' == $order['plugin_code']) {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                            'a',
                            array(
                                'href' => UrlHelper::generateUrl('Buyer', 'viewOrder', [$order['order_id']]),
                                'title' => Labels::getLabel('LBL_ADD_PAYMENT_DETAIL', $siteLangId)
                            ),
                            '<i class="fas fa-box-open"></i>',
                            true
                        );
                    }
                    break;
                default:
                    $td->appendElement('plaintext', array(), $order[$key], true);
                    break;
            }
        }
    }
    echo $tbl->getHtml();
    if (count($orders) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    } ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToOrderSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
