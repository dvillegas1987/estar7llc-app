<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'usuprequest_reference' => Labels::getLabel('LBL_Reference_Number', $adminLangId),
    'user_name' => Labels::getLabel('LBL_Name', $adminLangId),
    'user_details' => Labels::getLabel('LBL_Username/Email', $adminLangId),
    'usuprequest_date' => Labels::getLabel('LBL_Requested_On', $adminLangId),
    'status' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
$tbl = new HtmlElement(
    'table',
    array('width' => '100%', 'class' => 'table table-responsive')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'user_details':
                $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_U', $adminLangId) . ': </strong> ' . $row['credential_username'], true);
                $td->appendElement('br', array());
                $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_E', $adminLangId) . ': </strong> ' . $row['credential_email'], true);
                break;
            case 'status':
                $td->appendElement('plaintext', array(), $reqStatusArr[$row['usuprequest_status']], true);

                break;
            case 'action':
                if ($canViewSellerApprovalRequests) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View', $adminLangId), "onclick" => "viewSellerRequest(" . $row['usuprequest_id'] . ")"), "<i class='far fa-eye icon'></i>", true);
                }
                if ($canEditSellerApprovalRequests && $row['usuprequest_status'] == User::SUPPLIER_REQUEST_PENDING) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Change_Status', $adminLangId), "onclick" => "updateSellerRequestForm(" . $row['usuprequest_id'] . ")"), '<i class="fas fa-toggle-off"></i>', true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement(
        'td',
        array(
            'colspan' => count($arr_flds)
        ),
        Labels::getLabel('LBL_No_Records_Found', $adminLangId)
    );
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>