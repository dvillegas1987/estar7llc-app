<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'prodcat_identifier' => Labels::getLabel('LBL_Category_Name', $adminLangId),
    'prodcat_parent' => Labels::getLabel('LBL_Parent_category', $adminLangId),
    'shop_name' => Labels::getLabel('LBL_Requested_BY', $adminLangId),
    'prodcat_requested_on' => Labels::getLabel('LBL_Requested_On', $adminLangId),
    'action' => '',
);

if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['prodcat_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'shop_name':
                $name = (0 < $row['prodcat_seller_id'] ? $row['shop_name'] . '(' . $row['user_name'] . ')' : Labels::getLabel('LBL_ADMIN', $adminLangId));
                $td->appendElement('plaintext', array(), $name);
                break;
            case 'prodcat_parent':
                $prodCat = new productCategory();
                $name = $prodCat->getParentTreeStructure($row['prodcat_id'], 0, '', $adminLangId, false, -1);
                $td->appendElement('plaintext', array(), $name, true);
                break;
            case 'prodcat_identifier':
                if ($row['prodcat_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['prodcat_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'prodcat_requested_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "editProdCatRequestForm(" . $row['prodcat_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}
echo $tbl->getHtml();

if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmCategorySearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
