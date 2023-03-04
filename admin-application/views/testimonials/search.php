<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'testimonial_identifier' => Labels::getLabel('LBL_Testimonial_Identifier', $adminLangId),
    'testimonial_title' => Labels::getLabel('LBL_Testimonial_Title', $adminLangId),
    'testimonial_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['select_all'], $arr_flds['action']);
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

$sr_no = count($arr_listing);
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['testimonial_id']);

    if ($row['testimonial_active'] != applicationConstants::ACTIVE) {
        /* $tr->setAttribute ("class","fat-inactive"); */
    }
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="testimonial_ids[]" value=' . $row['testimonial_id'] . '><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'testimonial_active':
                $active = "";
                if ($row['testimonial_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                 <input ' . $active . ' type="checkbox" id="switch' . $row['testimonial_id'] . '" value="' . $row['testimonial_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                <i class="switch-handles ' . $statusClass . '"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "editTestimonialFormNew(" . $row['testimonial_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['testimonial_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
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

$frm = new Form('frmTestimonialListing', array('id' => 'frmTestimonialListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Testimonials', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>