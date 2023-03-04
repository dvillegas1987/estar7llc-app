<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'selprod_title' => Labels::getLabel('LBL_Product', $adminLangId),
    'seller_username' => Labels::getLabel('LBL_Review_For', $adminLangId),
    'reviewed_by' => Labels::getLabel('LBL_Reviewed_By', $adminLangId),
    'sprating_rating' => Labels::getLabel('LBL_Rating', $adminLangId),
    'spreview_posted_on' => Labels::getLabel('LBL_Date', $adminLangId),
    'spreview_status' => Labels::getLabel('LBL_Status', $adminLangId),
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

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'selprod_title':
                $td->appendElement('a', array('href' => UrlHelper::generateFullUrl('Products', 'View', array($row['selprod_id']), CONF_WEBROOT_FRONT_URL), 'target' => '_blank'), $row[$key], true);
                break;
            case 'spreview_status':
                $td->appendElement('plaintext', array(), $reviewStatus[$row[$key]], true);
                break;

            case 'sprating_rating':
                $rating = '<ul class="rating list-inline">';
                for ($j = 1; $j <= 5; $j++) {
                    $class = ($j <= round($row[$key])) ? "active" : "in-active";
                    $fillColor = ($j <= round($row[$key])) ? "#ff3a59" : "#474747";
                    $rating .= '<li class="' . $class . '">
					<svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
					<g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="' . $fillColor . '" /></g></svg>

				  </li>';
                }
                $rating .= '</ul>';
                $td->appendElement('plaintext', array(), $rating, true);
                break;

            case 'spreview_posted_on':
                $td->appendElement('plaintext', array(), FatDate::format($row[$key], true));
                break;

            case 'seller_username':
                if ($canViewUsers) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['shop_user_id'] . ')'), $row[$key], true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }

                $td->appendElement('plaintext', array(), '<br/>' . $row['shop_name'], true);
                break;

            case 'reviewed_by':
                if ($canViewUsers) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['credential_user_id'] . ')'), $row[$key], true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;

            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "viewReview(" . $row['spreview_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
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
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmReviewSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
