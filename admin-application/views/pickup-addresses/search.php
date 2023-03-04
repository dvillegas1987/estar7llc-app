<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'addr_id' => Labels::getLabel('LBL_Address', $adminLangId),
    'action' =>  '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');

foreach ($arr_flds as $key => $val) {
    $e = $th->appendElement('th', array(), $val);
}
$sr_no = count($arr_listing);
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['addr_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'addr_id':
                $addr2 = (strlen($row['addr_address2']) > 0) ? ', ' . $row['addr_address2'] . '<br>' : '';
                $addrCity = (strlen($row['addr_city']) > 0) ? $row['addr_city'] . ', ' : '';
                $addrState = (strlen($row['state_name']) > 0) ? $row['state_name'] . ', ' : '';
                $addrCountry = (strlen($row['country_name']) > 0) ? $row['country_name'] . '<br>' : '';
                $addrZip = (strlen($row['addr_zip']) > 0) ? Labels::getLabel('LBL_Zip:', $adminLangId) . $row['addr_zip'] : '';
                $addrPhone = (strlen($row['addr_phone']) > 0) ? $row['addr_phone'] : '';
                if (!empty($addrPhone) && array_key_exists('addr_phone_dcode', $row)) {
                    $addrPhone = ValidateElement::formatDialCode($row['addr_phone_dcode']) . $addrPhone;
                }
                $addrPhone = ', ' . Labels::getLabel('LBL_Phone:', $adminLangId) . $addrPhone;
                $address = "<address>
                                <p>" . $row['addr_address1'] . $addr2 . $addrCity . $addrState . $addrCountry . $addrZip . $addrPhone .
                    "</address>";
                $td->appendElement('plaintext', array(), $address, true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addAddressForm(" . $row['addr_id'] . "," . $row['addr_lang_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['addr_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
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

echo $tbl->getHtml();
