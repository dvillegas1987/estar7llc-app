<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}else{

$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'option_identifier' => Labels::getLabel('LBL_Option_Name', $adminLangId),
    'user_name' => Labels::getLabel('LBL_Added_By', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['select_all'], $arr_flds['action']);
}
$tbl = new HtmlElement(
    'table',
    array('width' => '100%', 'class' => 'table table-responsive table--hovered', 'id' => 'options')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['option_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="option_ids[]" value=' . $row['option_id'] . '><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'option_identifier':
                if ($row['option_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['option_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'user_name':
                if ($row['user_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['user_name'], true);
                } else {
                    $td->appendElement('plaintext', array(), 'Admin', true);
                }
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array(
                        'href' => 'javascript:void(0)',
                        'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId),
                        "onclick" => "addOptionFormNew(" . $row['option_id'] . ")"
                    ), "<i class='far fa-edit icon'></i>", true);

                    $td->appendElement(
                        'a',
                        array(
                            'href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon',
                            'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteOptionRecord(" . $row['option_id'] . ")"
                        ),
                        "<i class='fa fa-trash  icon'></i>",
                        true
                    );
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
        array('colspan' => count($arr_flds)),
        Labels::getLabel('LBL_No_records_Found', $adminLangId)
    );
}
$frm = new Form('frmOptionsListing', array('id' => 'frmOptionsListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Options', 'deleteSelected'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmOptionsSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}