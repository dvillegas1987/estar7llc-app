<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'question_identifier' => Labels::getLabel('LBL_Question', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
);
$tbl = new HtmlElement(
    'table',
    array('width' => '100%', 'class' => 'table table-responsive')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    if ($row['question_active'] != applicationConstants::ACTIVE) {
        $tr->setAttribute("class", "fat-inactive");
    }
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'question_identifier':
                if (!empty($row['question_title'])) {
                    $td->appendElement('plaintext', array(), $row['question_title'] . '<br/>(' . $row['question_identifier'] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row['question_identifier'], true);
                }
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class" => "actions"));
                if ($canEdit) {
                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array(
                        'href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Edit', $adminLangId),
                        "onclick" => "questionForm(" . $row['question_qbank_id'] . "," . $row['question_id'] . ")"
                    ), '<i class="far fa-edit icon"></i>', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array(
                        'href' => "javascript:void(0)", 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['question_id'] . ")"
                    ), '<i class="fa fa-trash  icon"></i>', true);
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
    'name' => 'frmQuestionSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
