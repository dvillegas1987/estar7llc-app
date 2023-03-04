<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($addresses)) {
    if (count($addresses) == 1 && $addresses[0]['addr_is_default'] != 1) {
        $addresses[0]['addr_is_default'] = 1;
    } ?>
    <ul class="my-addresses">
        <?php foreach ($addresses as $address) {
            $address['addr_title'] = ($address['addr_title'] == '') ? '&nbsp;' : $address['addr_title']; ?>
            <li class="<?php echo ($address['addr_is_default'] == 1) ? 'is-active' : ''; ?>">
                <div class="my-addresses__body">
                    <span class="radio">
                        <?php
                        $action = "setDefaultAddress(" . $address['addr_id'] . ", event)";
                        if (1 == $address['addr_is_default']) {
                            $action = 'return false';
                        }
                        ?>
                        <input type="radio" <?php echo ($address['addr_is_default'] == 1) ? 'checked=""' : ''; ?> name="1" onClick="<?php echo $action; ?>"><i class="input-helper"></i>
                    </span>
                    <address class="delivery-address">
                        <h5><?php echo $address['addr_name']; ?><span class="tag"><?php echo $address['addr_title']; ?></span></h5>
                        <p>
                            <?php echo $address['addr_address1'] . '<br>'; ?>
                            <?php echo (strlen($address['addr_address2']) > 0) ? $address['addr_address2'] . '<br>' : ''; ?>
                            <?php echo (strlen($address['addr_city']) > 0) ? $address['addr_city'] . ',' : ''; ?>
                            <?php echo (strlen($address['state_name']) > 0) ? $address['state_name'] . '<br>' : ''; ?>
                            <?php echo (strlen($address['country_name']) > 0) ? $address['country_name'] . '<br>' : ''; ?>
                            <?php echo (strlen($address['addr_zip']) > 0) ? Labels::getLabel('LBL_Zip:', $siteLangId) . $address['addr_zip'] . '<br>' : ''; ?>
                        </p>
                        <p class="phone-txt">
                            <i class="fas fa-mobile-alt"></i>
                            <?php 
                            $addrPhone = (strlen($address['addr_phone']) > 0) ? $address['addr_phone'] : '';
                            if (!empty($addrPhone) && array_key_exists('addr_phone_dcode', $address)) {
                                $addrPhone = ValidateElement::formatDialCode($address['addr_phone_dcode']) . $addrPhone;
                            }
                            echo (!empty($addrPhone)) ? Labels::getLabel('LBL_Phone:', $siteLangId) . $addrPhone . '<br>' : ''; ?>
                        </p>
                    </address>
                </div>
                <div class="my-addresses__footer">
                    <div class="actions">
                        <a href="javascript:void(0)" onClick="addAddressForm(<?php echo $address['addr_id']; ?>)">
                            <?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?>
                        </a>
                        <a href="javascript:void(0)" onClick="removeAddress(<?php echo $address['addr_id']; ?>)">
                            <?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?>
                        </a>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
<?php } elseif (isset($noRecordsHtml)) {
    echo FatUtility::decodeHtmlEntities($noRecordsHtml);
}
