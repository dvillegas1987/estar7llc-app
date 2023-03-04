<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'bpcomment_author_name' => Labels::getLabel('LBL_Author_Name', $adminLangId),
    'bpcomment_author_email' => Labels::getLabel('LBL_Author_Email', $adminLangId),
    'bpcomment_approved' => Labels::getLabel('LBL_Status', $adminLangId),
    'post_title' => Labels::getLabel('LBL_Post_Title', $adminLangId),
    'bpcomment_added_on' => Labels::getLabel('LBL_Posted_On', $adminLangId),
    'action' => '',
);

if (!$canEdit) {
    unset($arr_flds['select_all'], $arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered', 'id' => 'post'));
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

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="bpcomment_ids[]" value=' . $row['bpcomment_id'] . '><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'bpcomment_added_on':
                $td->appendElement('plaintext', array(), FatDate::format($row['bpcomment_added_on'], true));
                break;
            case 'bpcomment_author_name':
                $td->appendElement('plaintext', array(), CommonHelper::displayName($row[$key]), true);
                break;
            case 'bpcomment_approved':
                $statusArr = applicationConstants::getBlogCommentStatusArr($adminLangId);
                $td->appendElement('plaintext', array(), $statusArr[$row[$key]], true);
                break;
            case 'post_title':
                $td->appendElement('plaintext', array(), CommonHelper::displayName($row[$key]), true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "view(" . $row['bpcomment_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['bpcomment_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
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
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

$frm = new Form('frmBlogCmtsListing', array('id' => 'frmBlogCmtsListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
