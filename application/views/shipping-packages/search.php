<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
<?php if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId));
} else {
    $arr_flds = array(
        'listserial' => '#',
        'shippack_name' => Labels::getLabel('LBL_Name', $siteLangId),
        'shippack_units' => Labels::getLabel('LBL_Dimensions', $siteLangId)
    );

     $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
    $th = $tbl->appendElement('thead')->appendElement('tr');
    foreach ($arr_flds as $key => $val) {
        $th->appendElement('th', array(), $val);
    }

    $sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
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
                    $unitType = (isset($unitTypeArray[$row['shippack_units']])) ? $unitTypeArray[$row['shippack_units']] : '' ;

                    $dimension = $row['shippack_length'] . ' x ' . $row['shippack_width'] . ' x ' . $row['shippack_height'] . ' ' . $unitType;

                    $td->appendElement('plaintext', array(), $dimension, true);
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
</div>
<?php $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmPackageSearchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToPackagesSearchPage', 'siteLangId' => $siteLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}