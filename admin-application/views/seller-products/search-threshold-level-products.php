<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php

if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}else{

$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'product_name' => Labels::getLabel('LBL_Product_Name', $adminLangId),
    'selprod_stock' => Labels::getLabel('LBL_Stock_left', $adminLangId),
    'selprod_threshold_stock_level' => Labels::getLabel('LBL_Threshold_Stock', $adminLangId),
    'emailarchive_sent_on' => Labels::getLabel('LBL_Last_Email_Sent', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
);
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['selprod_id']);

    if ($row['selprod_threshold_stock_level'] < $row['selprod_stock']) {
        $tr->setAttribute("class", "fat-inactive");
    }
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array(
                        'href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon',
                        'title' => Labels::getLabel('LBL_Email_Seller', $adminLangId), "onclick" => "sendMailForm(" . $row['selprod_user_id'] . "," . $row['selprod_id'] . ")"
                    ), '<i class="ion-email icon"></i>', true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmProductSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}