<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
} else {
    $arr_flds = array(
        'listserial' => Labels::getLabel('LBL_#', $adminLangId),
        'shippack_name' => Labels::getLabel('LBL_Name', $adminLangId),
        'shippack_units' => Labels::getLabel('LBL_Dimensions', $adminLangId),
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
                case 'shippack_units':
                    $unitType = (isset($unitTypeArray[$row['shippack_units']])) ? $unitTypeArray[$row['shippack_units']] : '';

                    $dimension = $row['shippack_length'] . ' x ' . $row['shippack_width'] . ' x ' . $row['shippack_height'] . ' ' . $unitType;

                    $td->appendElement('plaintext', array(), $dimension, true);
                    break;
                case 'action':
                    if ($canEdit) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'addPackageForm(' . $row['shippack_id'] . ')', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="far fa-edit icon"></i>', true);
                    }
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }


    $frm = new Form('frmPackageListing', array('id' => 'frmPackageListing'));
    $frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    echo $frm->getFormTag();
    echo $tbl->getHtml(); ?>
    </form>
<?php $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmPackageSearchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
