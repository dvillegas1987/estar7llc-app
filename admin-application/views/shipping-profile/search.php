<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
} else {
    $arr_flds = array(
        'listserial' => Labels::getLabel('LBL_#', $adminLangId),
        'shipprofile_name' => Labels::getLabel('LBL_Name', $adminLangId),
        'totalProducts' => Labels::getLabel('LBL_Products', $adminLangId),
        'rates' => Labels::getLabel('LBL_Rates_for', $adminLangId),
        'action' => Labels::getLabel('', $adminLangId)
    );

    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
    $th = $tbl->appendElement('thead')->appendElement('tr');
    foreach ($arr_flds as $key => $val) {
        $th->appendElement('th', array(), $val);
    }

    $sr_no = ($page == 1) ? 0 : ($pageSize * ($page - 1));
    foreach ($arr_listing as $sn => $row) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array());

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no);
                    break;
                case 'shipprofile_name':
                    $badge = '';
                    if ($row['shipprofile_default'] == 1) {
                        $badge = ' <span class="badge badge--unified-brand badge--inline badge--pill">' . Labels::getLabel('LBL_Default', $adminLangId) . '</span>';
                    }
                    $td->appendElement('plaintext', array(), $row[$key] . $badge, true);
                    break;
                case 'rates':
                    $str = '';
                    $profileId = $row['shipprofile_id'];
                    $zoneData = (isset($zones[$profileId])) ? $zones[$profileId] : array();
                    if (!empty($zoneData)) {
                        $str = '<ul class="list-tags">';
                        //$str .= '<li class="font-bold">' . Labels::getLabel('LBL_Rates_for', $adminLangId) . '</li>';
                        foreach ($zoneData as $data) {
                            $str .= '<li><span>' . $data['shipzone_name'] . '</span></li>';
                        }
                        $str .= '</ul>';
                    }
                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'action':
                    if ($canEdit) {
                        $td->appendElement('a', array('href' => UrlHelper::generateUrl('shippingProfile', 'form', array($row['shipprofile_id'])),  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="fa fa-edit icon"></i>', true);
                        if ($row['shipprofile_default'] != applicationConstants::YES) {
                            $td->appendElement('a', array('href' => 'javascript:void(0)',  'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "deleteRecord(" . $row['shipprofile_id'] . ")"), '<i class="fa fa-trash icon"></i>', true);
                        }
                    }
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }


    $frm = new Form('frmProfileListing', array('id' => 'frmProfileListing'));
    $frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    echo $frm->getFormTag();
    echo $tbl->getHtml(); ?>
    </form>
<?php $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProfileSearchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
