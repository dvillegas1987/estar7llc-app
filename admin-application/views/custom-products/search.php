<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'product_identifier' => Labels::getLabel('LBL_Product', $adminLangId),
    'shop_name' => Labels::getLabel('LBL_Shop', $adminLangId),
    'preq_added_on' => Labels::getLabel('LBL_Added_on', $adminLangId),
    'preq_requested_on' => Labels::getLabel('LBL_Requested_on', $adminLangId),
    'preq_status' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => ''
);
if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => 'hide--mobile'));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'shop_name':
                $td->appendElement('plaintext', array(), $row[$key] . '<br>', true);
                if ($row['user_parent'] > 0) {
                    $td->appendElement('plaintext', array(), '(' . $row['user_name'] . ')', true);
                }
                break;
            case 'preq_status':
                $text = '<label class="label label-' . $reqStatusClassArr[$row[$key]] . '">' . $reqStatusArr[$row[$key]] . '</label>';
                $text .= '<br>';
                $text .= '<span class="date">' . FatDate::Format($row['preq_status_updated_on']) . '</span>';
                $td->appendElement('plaintext', array(), $text, true);
                break;
            case 'preq_added_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'preq_requested_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'action':
                if ($row['preq_status'] != ProductRequest::STATUS_APPROVED) {
                    $td->appendElement(
                        'a',
                        array('href' => 'javascript:void(0)', "onclick" => "addProductForm(" . $row['preq_id'] . ")", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)),
                        "<i class='far fa-edit icon'></i>",
                        true
                    );

                    $td->appendElement(
                        'a',
                        array('href' => 'javascript:void(0)', "onclick" => "productImagesForm(" . $row['preq_id'] . ")", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Images', $adminLangId)),
                        "<i class='far fa-images'></i>",
                        true
                    );

                    $td->appendElement(
                        "a",
                        array('title' => Labels::getLabel('LBL_Change_Status', $adminLangId), 'onclick' => 'updateStatusForm(' . $row['preq_id'] . ')', 'href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon'),
                        "<i class='fas fa-toggle-off'></i>",
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
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmCustomProdReqSrchPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId, 'callBackJsFunc' => 'goToCustomCatalogProductSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
