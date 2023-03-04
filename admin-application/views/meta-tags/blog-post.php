<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'post_title' => Labels::getLabel('LBL_Post_Title', $adminLangId),
    'meta_title' => Labels::getLabel('LBL_Title', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $metaId = FatUtility::int($row['meta_id']);
    $recordId = FatUtility::int($row['post_id']);
    $tr->setAttribute("id", $metaId);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement(
                        'a',
                        array(
                            'href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon',
                            'title' => 'Edit', "onclick" => "editMetaTagLangForm($metaId," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ",'$metaType',$recordId)"
                        ),
                        '<i class="far fa-edit icon"></i>',
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
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), 'No Records Found');
}
echo $tbl->getHtml();

if (isset($pageCount)) {
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array(
        'name' => 'frmMetaTagSearchPaging'
    ));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
