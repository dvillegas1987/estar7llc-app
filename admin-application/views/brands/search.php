<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}else{
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'brand_logo' => Labels::getLabel('LBL_Logo', $adminLangId),
    'brand_identifier' => Labels::getLabel('LBL_Brand_Name', $adminLangId),
    'brand_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['select_all'], $arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ("brand_logo" == $key) {
        $e = $th->appendElement('th', array('style' => 'text-align:center; width: 20px;'), $val);
    } elseif ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
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
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="brandIds[]" value=' . $row['brand_id'] . '><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'brand_logo':
                $td->appendElement(
                    'plaintext',
                    array('style' => 'text-align:center'),
                    '<img class="max-img" src="' . UrlHelper::generateUrl('image', 'brand', array($row['brand_id'], $adminLangId, 'MINITHUMB'), CONF_WEBROOT_FRONT_URL) . '">',
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
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                      <input ' . $active . ' type="checkbox" id="switch' . $row['brand_id'] . '" value="' . $row['brand_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                     <i class="switch-handles ' . $statusClass . '"></i>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addBrandForm(" . $row['brand_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['brand_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}
// if (count($arr_listing) == 0) {
//     //$tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
//     $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
// }
$frm = new Form('frmBrandListing', array('id' => 'frmBrandListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Brands', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmBrandSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
