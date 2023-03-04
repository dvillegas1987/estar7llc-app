<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}else{

$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'shop_name' => Labels::getLabel('LBL_Requested_BY', $adminLangId),
    'brand_logo' => Labels::getLabel('LBL_Logo', $adminLangId),
    'brand_identifier' => Labels::getLabel('LBL_Brand_Name', $adminLangId),
    'brand_requested_on' => Labels::getLabel('LBL_Requested_On', $adminLangId),
    'action' => '',
);

if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ($key == "brand_logo") {
        $e = $th->appendElement('th', array('style' => 'text-align:center; width: 20px;'), $val);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['brand_id']);

    foreach ($arr_flds as $key => $val) {
        if ($key == "brand_logo") {
            $td = $tr->appendElement('td', array('style' => 'text-align:center;'));
        } else {
            $td = $tr->appendElement('td');
        }
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'shop_name':
                $name = $row['shop_name'] . '(' . $row['user_name'] . ')';
                $td->appendElement('plaintext', array(), $name);
                break;
            case 'brand_logo':
                $td->appendElement(
                    'plaintext',
                    array('style' => 'text-align:center'),
                    '<img  class="max-img"  src="' . UrlHelper::generateUrl('image', 'brand', array($row['brand_id'], $adminLangId, 'MINITHUMB'), CONF_WEBROOT_FRONT_URL) . '">',
                    true
                );
                break;
            case 'brand_identifier':
                if ($row['brand_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['brand_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'brand_active':
                $active = "";
                if ($row['brand_active']) {
                    $active = 'checked';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str = '<div class="checkbox-switch"><input ' . $active . ' type="checkbox" id="switch' . $row['brand_id'] . '" value="' . $row['brand_id'] . '" onclick="' . $statucAct . '"/><label for="switch' . $row['brand_id'] . '">Toggle</label></div>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'brand_requested_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addBrandRequestForm(" . $row['brand_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
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
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmBrandSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}