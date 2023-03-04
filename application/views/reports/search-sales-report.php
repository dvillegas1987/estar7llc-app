<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
<?php $arrFlds1 = array(
    'listserial' => Labels::getLabel('LBL_#', $siteLangId),
    'order_date' => Labels::getLabel('LBL_Date', $siteLangId),
    'totOrders' => Labels::getLabel('LBL_No._of_Orders', $siteLangId),
);
$arrFlds2  = array(
    'listserial' => Labels::getLabel('LBL_#', $siteLangId),
    'op_invoice_number' => Labels::getLabel('LBL_Invoice_Number', $siteLangId),
);
$arr = array(
    'totQtys' => Labels::getLabel('LBL_No._of_Qty', $siteLangId),
    'totRefundedQtys' => Labels::getLabel('LBL_Refunded_Qty', $siteLangId),
    'inventoryValue' => Labels::getLabel('LBL_Inventory_Value', $siteLangId),
    'orderNetAmount' => Labels::getLabel('LBL_Order_Net_Amount', $siteLangId),
    'taxTotal' => Labels::getLabel('LBL_Tax_Charged', $siteLangId),
    'shippingTotal' => Labels::getLabel('LBL_Shipping_Charges', $siteLangId),
    'totalRefundedAmount' => Labels::getLabel('LBL_Refunded_Amount', $siteLangId),
    'totalSalesEarnings' => Labels::getLabel('LBL_Commission_Charges', $siteLangId)
);
if (empty($orderDate)) {
    $arr_flds = array_merge($arrFlds1, $arr);
} else {
    $arr_flds = array_merge($arrFlds2, $arr);
}

$tbl = new HtmlElement('table', array('class' => 'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arrListing as $sn => $row) {
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;

            case 'order_date':
                $td->appendElement('plaintext', array(), '<a href="' . UrlHelper::generateUrl(
                    'Reports',
                    'salesReport',
                    array($row[$key])
                ) . '">' . FatDate::format($row[$key]) . '</a>', true);
                break;

            case 'totalSalesEarnings':
            case 'totalRefundedAmount':
            case 'inventoryValue':
            case 'orderNetAmount':
            case 'taxTotal':
            case 'shippingTotal':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true));
                break;

            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }

    $sr_no--;
}
echo $tbl->getHtml();
if (count($arrListing) == 0) {
    
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
} ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSalesReportSrchPaging', 'method' => 'post'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSalesReportSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
