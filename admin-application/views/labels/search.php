<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'label_key' => Labels::getLabel('LBL_Key', $adminLangId),
    'label_caption' => Labels::getLabel('LBL_Caption', $adminLangId),
    'label_type' => Labels::getLabel('LBL_Type', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        if ($key == 'label_key') {
            $td->setAttribute('class', 'word-break');
        }
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'label_caption':
                $td->appendElement('plaintext', array(), nl2br($row[$key]), true);
                break;
            case 'label_type':
                $typeArr = Labels::getTypeArr($adminLangId);
                $td->appendElement('plaintext', array(), $typeArr[$row[$key]], true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement(
                        'a',
                        array(
                            'href' => 'javascript:void(0)',
                            'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId),
                            "onclick" => "labelsForm('" . $row['label_id'] . "', '" . $row['label_type'] . "')"
                        ),
                        "<i class='far fa-edit icon'></i>",
                        true
                    );
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
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
    'name' => 'frmLabelsSrchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
