<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
} else {
    $arr_flds1 = array(
        'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
        'listserial' => Labels::getLabel('LBL_#', $adminLangId),
        'product_identifier' => Labels::getLabel('LBL_Name', $adminLangId)
    );

    $arr_flds2 = array(
        'user_name' => Labels::getLabel('LBL_User', $adminLangId),
        //'attrgrp_name'=>Labels::getLabel('LBL_Attribute_Group',$adminLangId),
        'product_added_on' => Labels::getLabel('LBL_Date', $adminLangId),
        'product_approved' => Labels::getLabel('LBL_Status', $adminLangId),
        'product_active' => Labels::getLabel('LBL_Publish', $adminLangId),
        'action' => Labels::getLabel('', $adminLangId)
    );
    $arr_flds = $arr_flds1 + $arr_flds2;
    if (!$canEdit) {
        unset($arr_flds['select_all']);
    }
    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
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
        $tr = $tbl->appendElement('tr', array());

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="product_ids[]" value=' . $row['product_id'] . '><i class="input-helper"></i></label>', true);
                    break;
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no);
                    break;
                case 'product_identifier':
                    $td->appendElement('plaintext', array(), $row['product_name'] . '<br>', true);
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                    break;
                case 'user_name':
                    if ($canViewUsers) {
                        !empty($row[$key]) ? $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '",' . $row['product_seller_id'] . ')'), $row[$key]) : $td->appendElement('plaintext', array(), (!empty($row[$key]) ? $row[$key] : 'Admin'), true);
                    } else {
                        $td->appendElement('plaintext', array(), (!empty($row[$key]) ? $row[$key] : 'Admin'), true);
                    }
                    break;
                case 'attrgrp_name':
                    $td->appendElement('plaintext', array(), CommonHelper::displayNotApplicable($adminLangId, $row[$key]), true);
                    break;
                case 'product':
                    $td->appendElement('plaintext', array(), ($row['product_seller_id']) ? 'Custom' : 'Catalog');
                    break;
                case 'product_approved':
                    $approveUnApproveArr = Product::getApproveUnApproveArr($adminLangId);
                    $td->appendElement('plaintext', array(), $approveUnApproveArr[$row[$key]], true);
                    break;
                case 'product_active':
                    $active = "";
                    if ($row['product_active']) {
                        $active = 'checked';
                    }
                    $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                    $statusClass = ($canEdit === false) ? 'disabled' : '';
                    $str = '<label class="statustab -txt-uppercase">
                         <input ' . $active . ' type="checkbox" id="switch' . $row['product_id'] . '" value="' . $row['product_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                        <i class="switch-handles ' . $statusClass . '"></i></label>';
                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'product_added_on':
                    $td->appendElement('plaintext', array(), FatDate::format($row[$key], true));
                    break;
                case 'action':
                    if ($canEdit) {
                        $td->appendElement('a', array('href' => UrlHelper::generateUrl('Products', 'form', array($row['product_id'])), 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="far fa-edit icon"></i>', true);

                        $td->appendElement('a', array('href' => "javascript:;", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteProduct(" . $row['product_id'] . ")"), '<i class="fa fa-trash  icon"></i>', true);
                    }
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
        $sr_no--;
    }


    $frm = new Form('frmProdListing', array('id' => 'frmProdListing'));
    $frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    $frm->setFormTagAttribute('action', UrlHelper::generateUrl('Products', 'toggleBulkStatuses'));
    $frm->addHiddenField('', 'status');

    echo $frm->getFormTag();
    echo $frm->getFieldHtml('status');
    echo $tbl->getHtml(); ?>
    </form>
<?php 
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
