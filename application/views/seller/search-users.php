<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
<?php $arr_flds = array();
if (count($arrListing) > 0) {
    $arr_flds['select_all'] = '';
}
$arr_flds['listserial'] = Labels::getLabel('LBL_#', $siteLangId);
$arr_flds['credential_username'] = Labels::getLabel('LBL_Username', $siteLangId);
$arr_flds['credential_email'] = Labels::getLabel('LBL_Email', $siteLangId);
$arr_flds['credential_active'] = Labels::getLabel('LBL_Status', $siteLangId);
$arr_flds['action'] = '';
$tableClass = '';
if (0 < count($arrListing)) {
	$tableClass = "table-justified";
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--orders '.$tableClass));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        if (count($arrListing) > 0) {
            $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" title="' . $val . '" class="selectAll-js"><i class="input-helper"></i></label>', true);
        }
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}
$sr_no = 0;
if ($page > 1) {
    $sr_no = ($page - 1) * $pageSize;
}
foreach ($arrListing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ($row['credential_active'] != applicationConstants::ACTIVE) ? '' : '' ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="user_ids[]" value=' . $row['user_id'] . '><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'credential_active':
                $active = "";
                if (applicationConstants::ACTIVE == $row['credential_active']) {
                    $active = 'checked';
                }

                $str = '<label class="toggle-switch" for="switch' . $row['user_id'] . '"><input ' . $active . ' type="checkbox" value="' . $row['user_id'] . '" id="switch' . $row['user_id'] . '" onclick="toggleSellerUserStatus(event,this)"/><div class="slider round"></div></label>';

                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class" => "actions"), '', true);

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Change_Password', $siteLangId), "onclick"=>"userPasswordForm(".$row['user_id'].")"),
                    '<i class="fas fa-key"></i>',
                    true
                );

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId), "onclick"=>"addUserForm(".$row['user_id'].")"),
                    '<i class="fa fa-edit"></i>',
                    true
                );

                /*$li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), "onclick" => "sellerUserDelete(" . $row['user_id'] . ")"),
                    '<i class="fa fa-trash"></i>',
                    true
                );*/

                $li = $ul->appendElement("li");
                $li->appendElement(
                    'a',
                    array('href' => UrlHelper::generateUrl('Seller', 'UserPermissions', array($row['user_id'])), 'title' => Labels::getLabel('LBL_Permissions', $siteLangId)),
                    '<i class="fa fa-lock"></i>',
                    true
                );

                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    echo $tbl->getHtml();
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId,'message' => $message));
} else {
    $frm = new Form('frmSellerUsersListing', array('id' => 'frmSellerUsersListing'));
    $frm->setFormTagAttribute('class', 'form');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
    $frm->setFormTagAttribute('action', UrlHelper::generateUrl('Seller', 'toggleSellerUserStatus'));
    $frm->addHiddenField('', 'status');

    echo $frm->getFormTag();
    echo $frm->getFieldHtml('status');
    echo $tbl->getHtml(); ?> </form> <?php
} ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmUserSearchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page,'recordCount' => $recordCount, 'callBackJsFunc' => 'goToUserSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
