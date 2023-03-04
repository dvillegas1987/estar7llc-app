<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
<?php $arr_flds = [
    'listserial' => Labels::getLabel('LBL_#', $siteLangId),
    'adsbatch_name' => Labels::getLabel('LBL_BATCH_NAME', $siteLangId),
    'adsbatch_lang_id' => Labels::getLabel('LBL_CONTENT_LANG', $siteLangId),
    'adsbatch_target_country_id' => Labels::getLabel('LBL_TARGET_COUNTRY', $siteLangId),
    'adsbatch_expired_on' => Labels::getLabel('LBL_EXPIRY_DATE', $siteLangId),
    'adsbatch_synced_on' => Labels::getLabel('LBL_LAST_SYNCED', $siteLangId),
    'adsbatch_status' => Labels::getLabel('LBL_STATUS', $siteLangId),
    'action' => '',
];
if (!$canEdit) {
	unset($arr_flds['action']);
}
if (1 > count($arrListing)) {
    unset($arr_flds['select_all']);
}
$tableClass = '';
if (0 < count($arrListing)) {
	$tableClass = "table-justified";
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table '.$tableClass,'id' => 'plugin'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = 0;
foreach ($arrListing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( 'id' => $row['adsbatch_id'], 'class' => '' ));
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'adsbatch_name':
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
            case 'adsbatch_lang_id':
                $languages = Language::getAllNames();
                $td->appendElement('plaintext', array(), $languages[$row[$key]], true);
                break;
            case 'adsbatch_target_country_id':
                $countryObj = new Countries();
                $countriesArr = $countryObj->getCountriesArr($siteLangId);
                $td->appendElement('plaintext', array(), $countriesArr[$row[$key]], true);
                break;
            case 'adsbatch_expired_on':
                $timestamp = strtotime($row[$key]);
                $date = 0 < $timestamp ? date('Y-m-d', strtotime($row[$key])) : Labels::getLabel('LBL_N/A', $siteLangId);
                $td->appendElement('plaintext', array(), $date, true);
                break;
            case 'adsbatch_synced_on':
                $timestamp = strtotime($row[$key]);
                $date = 0 < $timestamp ? date('Y-m-d H:i:s', strtotime($row[$key])) : Labels::getLabel('LBL_IN_QUEUE', $siteLangId);
                $td->appendElement('plaintext', array(), $date, true);
                break;
            case 'adsbatch_status':
                $statusArr = AdsBatch::statusArr();
                switch ($row[$key]) {
                    case AdsBatch::STATUS_PENDING:
                        $class = 'badge-info';
                        break;
                    case AdsBatch::STATUS_PUBLISHED:
                        $class = 'badge-success';
                        break;
                    default:
                        $class = 'badge-dark';
                        break;
                }
                $htm = '<span class="badge ' . $class . '">'  . $statusArr[$row[$key]] . '</span>';
                $td->appendElement('plaintext', array(), $htm, true);
                break;
            case 'action':
                if (AdsBatch::STATUS_PUBLISHED != $row['adsbatch_status']) {
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);

                    $li = $ul->appendElement('li');
                    $li->appendElement(
                        'a',
                        [
                            'href' => 'javascript:void(0)',
                            'title' => Labels::getLabel('LBL_PUBLISH', $siteLangId),
                            'onclick' => "publishBatch(" . $row['adsbatch_id'] . ")"
                        ],
                        '<i class="fa fa-rss"></i>',
                        true
                    );
                    $li = $ul->appendElement('li');
                    $li->appendElement(
                        'a',
                        [
                            'href' => UrlHelper::generateUrl($keyName, 'bindProducts', [$row['adsbatch_id']]),
                            'title' => Labels::getLabel('LBL_BIND_PRODUCTS', $siteLangId)
                        ],
                        '<i class="fa fa-link"></i>',
                        true
                    );
                    $li = $ul->appendElement('li');
                    $li->appendElement(
                        'a',
                        [
                            'href' => 'javascript:void(0)',
                            'title' => Labels::getLabel('LBL_EDIT', $siteLangId),
                            'onclick' => "batchForm(" . $row['adsbatch_id'] . ")"
                        ],
                        '<i class="fa fa-edit"></i>',
                        true
                    );
                    $li = $ul->appendElement('li');
                    $li->appendElement(
                        'a',
                        [
                            'href' => 'javascript:void(0)',
                            'title' => Labels::getLabel('LBL_DELETE', $siteLangId), "onclick" => "deleteBatch(" . $row['adsbatch_id'] . ")"
                        ],
                        '<i class="fa fa-trash"></i>',
                        true
                    );
                }
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_RECORD_NOT_FOUND', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', ['siteLangId' => $siteLangId, 'message' => $message]);
}?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchPaging'));
$pagingArr = ['pageCount' => $pageCount, 'page' => $page];
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
