<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
        /* 'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId), */
        'listserial'=> '#',
        'record_name'=> Labels::getLabel('LBL_Name', $adminLangId),
        'action'=>'',
    );
    if (!$canEdit) {
        unset($arr_flds['select_all'], $arr_flds['action']);
    }
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page==1?0:$pageSize*($page-1);

foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="record_ids[]" value='.$row['record_id'].'><i class="input-helper"></i></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'btn btn-clean btn-sm btn-icon', 'title'=>Labels::getLabel('LBL_Edit', $adminLangId),"onclick"=>"attributeForm(".$row['record_id'].")"), "<i class='far fa-edit icon'></i>", true);
                    /* $td->appendElement('a', array('href'=>"javascript:void(0)", 'class'=>'btn btn-clean btn-sm btn-icon', 'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteRecord(".$row['record_id'].")"), "<i class='fa fa-trash  icon'></i>", true); */
                }
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

$frm = new Form('frmImgAttributeListing', array('id'=>'frmImgAttributeListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('UrlRewriting', 'deleteSelected'));
$frm->addHiddenField('', 'status');
$frm->addHiddenField('', 'module_type', $moduleType);

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmImgAttrPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
<script>
var moduleType = <?php echo $moduleType; ?>;
</script>