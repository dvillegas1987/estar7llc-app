<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'dragdrop' => '',
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    // 'listserial' => Labels::getLabel('LBL_Display_Order', $adminLangId),
    'collection_identifier' => Labels::getLabel('LBL_Collection_Identifier/Name', $adminLangId),
    'collection_type' => Labels::getLabel('LBL_Type', $adminLangId),
    'collection_layout_type' => Labels::getLabel('LBL_Layout_Type', $adminLangId),
    'collection_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);

if (!$canEdit || empty($arr_listing)) {
    unset($arr_flds['dragdrop']);
    unset($arr_flds['select_all']);
}
if (!$canEdit) {
    unset($arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered','id' => 'collectionList'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['collection_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                if ($row['collection_active'] == applicationConstants::ACTIVE) {
                    $td->appendElement('i', array('class' => 'ion-arrow-move icon'));
                    $td->setAttribute("class", 'dragHandle');
                }
                break;
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="collection_ids[]" value='.$row['collection_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'collection_identifier':
                $td->appendElement('plaintext', array(), $row['collection_name'] . '<br>', true);
                $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                break;
            case 'collection_type':
                $td->appendElement('plaintext', array(), Collections::getTypeArr($adminLangId)[$row[$key]]);
                break;
            case 'collection_layout_type':
                $td->appendElement('plaintext', array(), Collections::getLayoutTypeArr($adminLangId)[$row[$key]]);
                break;
            case 'action':
                if($canEdit){
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon',
                        'title' => Labels::getLabel('LBL_Edit', $adminLangId) , "onclick" => "collectionForm(".$row['collection_type'].",".$row['collection_layout_type']."," . $row['collection_id'] . ")"),'<i class="far fa-edit icon"></i>', true);
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon',
                        'title' => Labels::getLabel('LBL_Delete', $adminLangId) , "onclick" => "deleteRecord(" . $row['collection_id'] . ")"),'<i class="fa fa-trash  icon"></i>', true);
                }
                break;
            case 'collection_active':
            case 'collection_active':
                $active = "";
                if ($row['collection_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' .applicationConstants::NO. ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                <input '.$active.' type="checkbox" id="switch'.$row['collection_id'].'" value="'.$row['collection_id'].'" onclick="'.$statusAct.'" class="switch-labels"/>
                <i class="switch-handles '.$statusClass.'"></i>
                </label>';

                $td->appendElement('plaintext', array(), $str, true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
$frm = new Form('frmCollectionListing', array('id' => 'frmCollectionListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Collections', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php
/* $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
        'name' => 'frmCollectionSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false); */
?> <script>
    $(document).ready(function() {
        $('#collectionList').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('Collections', 'updateOrder'), order, function(res) {
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
