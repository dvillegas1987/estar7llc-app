<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'taxcat_name' => Labels::getLabel('LBL_Tax_Category', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId)
);
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => 'hide--mobile'));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize * ($page - 1));
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), '<span class="caption--td">' . $val . '</span>' . $sr_no, true);
                break;
            case 'taxcat_name':
                $td->appendElement('plaintext', array(), '<span class="caption--td">' . $val . '</span>' . $row[$key] . '<br>', true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class" => "actions"), '<span class="caption--td">' . $val . '</span>', true);
                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array(
                        'href' => 'javascript:void(0)', 'class' => '',
                        'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "changeTaxRates(" . $row['taxcat_id'] . ")"
                    ),
                    '<i class="icon far fa-edit"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), '<span class="caption--td">' . $val . '</span>' . $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_products_found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchTaxCatPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
