<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'dragdrop' => '',
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'slide_identifier' => Labels::getLabel('LBL_Title', $adminLangId),
    /* 'slide_image' => Labels::getLabel('LBL_Image',$adminLangId), */
    'slide_url' => Labels::getLabel('LBL_URL', $adminLangId),
    'slide_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);

if (!$canEdit || empty($arrListing)) {
    unset($arr_flds['dragdrop']);
    unset($arr_flds['select_all']);
}
if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered', 'id' => 'slideList'));
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
    /* $tr = $tbl->appendElement('tr',array('class' => ($row['slide_active'] != applicationConstants::ACTIVE) ? 'fat-inactive' : '' )); */
    $tr = $tbl->appendElement('tr', array());
    $tr->setAttribute("id", $row['slide_id']);
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                if ($row['slide_active'] == applicationConstants::ACTIVE) {
                    $td->appendElement('i', array('class' => 'ion-arrow-move icon'));
                    $td->setAttribute("class", 'dragHandle');
                }
                break;
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="slide_ids[]" value=' . $row['slide_id'] . '><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'slide_identifier':
                if ($row['slide_title'] != '') {
                    $td->appendElement('plaintext', array(), $row['slide_title'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row['slide_identifier'] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row['slide_identifier'], true);
                }
                break;
                /* case 'slide_image':
                if( $languages ){
                    foreach($languages as $lang_id=>$lang_name){
                        $img = "<strong>".$lang_name.'</strong><br/><img src="'.UrlHelper::generateFullUrl('Image','slide',array($row['slide_id'],$lang_id,'THUMB'),CONF_WEBROOT_FRONT_URL).'" /><br/>';
                        $td->appendElement('plaintext', array(), $img ,true);
                    }
                }
                break; */
            case 'slide_url':
                $url = CommonHelper::processURLString($row['slide_url']);
                $td->appendElement('plaintext', array(), CommonHelper::displayNotApplicable($adminLangId, CommonHelper::truncateCharacters($url, 85)), true);
                break;
            case 'slide_active':
                /* $td->appendElement("plaintext",array(), applicationConstants::getActiveInactiveArr($adminLangId)[$row[$key]]); */
                $active = "";
                if ($row['slide_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                     <input ' . $active . ' type="checkbox" id="switch' . $row['slide_id'] . '" value="' . $row['slide_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                    <i class="switch-handles ' . $statusClass . '"></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addSlideForm(" . $row['slide_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['slide_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

$frm = new Form('frmSlidesListing', array('id' => 'frmSlidesListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Slides', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<script>
    $(document).ready(function() {
        $('#slideList').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('Slides', 'updateOrder'), order, function(res) {
                    var ans = $.parseJSON(res);
                    if (ans.status == 1) {
                        fcom.displaySuccessMessage(ans.msg);
                    } else {
                        fcom.displayErrorMessage(ans.msg);
                    }
                });
            },
            dragHandle: ".dragHandle",
        });
    });
</script>