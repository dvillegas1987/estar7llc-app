<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'dragdrop' => '',
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'sformfield_identifier' => Labels::getLabel('LBL_Caption', $adminLangId),
    'sformfield_type' => Labels::getLabel('LBL_Type', $adminLangId),
    'sformfield_required' => Labels::getLabel('LBL_Required', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['dragdrop'], $arr_flds['action']);
}
$tbl = new HtmlElement(
    'table',
    array('width' => '100%', 'class' => 'table table-responsive', 'id' => 'formFields')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['sformfield_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                $td->appendElement('i', array('class' => 'ion-arrow-move icon'));
                $td->setAttribute("class", 'dragHandle');
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'sformfield_required':
                $td->appendElement('plaintext', array(), $yesNoArr[$row[$key]], true);
                break;
            case 'sformfield_type':
                $td->appendElement('plaintext', array(), $fieldTypeArr[$row[$key]], true);
                break;
            case 'sformfield_identifier':
                if (isset($row['sformfield_caption']) && $row['sformfield_caption'] != '') {
                    $td->appendElement('plaintext', array(), $row['sformfield_caption'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addFormFields(" . $row['sformfield_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    if ($row['sformfield_mandatory'] == 0) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteFieldsRecord(" . $row['sformfield_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
                    }
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement(
        'td',
        array(
            'colspan' => count($arr_flds)
        ),
        Labels::getLabel('LBL_No_Records_Found', $adminLangId)
    );
}
echo $tbl->getHtml();
?> <script>
    $(document).ready(function() {
        $('#formFields').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('Users', 'setFieldsOrder'), order, function(res) {
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