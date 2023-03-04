<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'shipapi_courier' => Labels::getLabel('LBL_Ship_Api_Courier', $adminLangId),
    'tracking_courier' => Labels::getLabel('LBL_Tracking_Courier', $adminLangId),
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($carriers as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());
    //$tr->setAttribute("data-ship-id", $row['tccr_shipapi_plugin_id']);
    //$tr->setAttribute("data-ship-code", $row['tccr_shipapi_courier_code']);
    //$tr->setAttribute("data-tracking-id", $row['tccr_tracking_plugin_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'shipapi_courier':
                $td->appendElement('plaintext', array(), $row['name']);
                break;

            case 'tracking_courier':
                $trackingApiCode = '';
                foreach ($records as $data) {
                    if ($row['code'] == $data['tccr_shipapi_courier_code']) {
                        $trackingApiCode = $data['tccr_tracking_courier_code'];
                        break;
                    }
                }

                $selectBox = "<select id=" . $row['code'] . " onChange='setUpCourierRelation(this)'><option value=''>" . Labels::getLabel('LBL_Select', $adminLangId) . "</option>";
                foreach ($trackingCourier as $code => $courier) {
                    $selected = ($trackingApiCode == $code) ? 'selected=selected' : '';
                    $selectBox .= "<option " . $selected . " value=" . $code . ">" . $courier . "</option>";
                }
                $selectBox .= "</select>";
                $td->appendElement('plaintext', array(), $selectBox, true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

if (count($carriers) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
