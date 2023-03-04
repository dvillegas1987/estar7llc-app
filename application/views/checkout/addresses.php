<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="step active">
    <form class="form ">
        <div class="step__section">
            <div class="step__section__head">
                <h5 class="step__section__head__title">
                    <?php if ($fulfillmentType == Shipping::FULFILMENT_PICKUP || $addressType == Address::ADDRESS_TYPE_BILLING || !$cartHasPhysicalProduct) {
                        echo Labels::getLabel('LBL_Billing_Address', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_Delivery_Address', $siteLangId);
                    }
                    ?>
                </h5>
                <a onClick="showAddressFormDiv(<?php echo $addressType; ?>);" name="addNewAddress" class="link-text" href="javascript:void(0)">
                    <i class="icn"> <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#add" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#add">
                            </use>
                        </svg> </i><?php echo Labels::getLabel('LBL_Add_New_Address', $siteLangId); ?></a>
            </div>
            <?php if ($addresses) { ?>
                <ul class="list-group list-addresses list-addresses-view">
                    <?php foreach ($addresses as $address) {
                        $selected_shipping_address_id = (!$selected_shipping_address_id && $address['addr_is_default']) ? $address['addr_id'] : $selected_shipping_address_id; ?>
                        <?php $checked = false;
                        if ($addressType == 0 && $selected_shipping_address_id == $address['addr_id']) {
                            $checked = true;
                        }
                        if ($addressType == Address::ADDRESS_TYPE_BILLING && $selected_billing_address_id == $address['addr_id']) {
                            $checked = true;
                        }
                        ?>
                        <li class="list-group-item address-<?php echo $address['addr_id']; ?> <?php //echo ($checked == true) ? 'selected' : ''
                                                                                                ?>">
                               <label class="d-block" for="">
                            <div class="row">
                                <div class="col-auto">
                                    <label class="checkbox">
                                        <input <?php echo ($checked == true) ? 'checked="checked"' : ''; ?> name="shipping_address_id" value="<?php echo $address['addr_id']; ?>" type="radio"><i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="col">
                                    <div class="delivery-address">
                                        <h5><?php echo $address['addr_name']; ?><span class="tag"><?php echo ($address['addr_title'] != '') ? $address['addr_title'] : $address['addr_name']; ?></span></h5>
                                        <p><?php echo $address['addr_address1'] ;?> 
										<?php if(strlen($address['addr_address2']) > 0) { 
											echo ", ".$address['addr_address2'] ;?> 
										<?php } ?>
										</p>   
										<p><?php echo $address['addr_city'].", ".$address['state_name'].", ".$address['country_name'].", ".$address['addr_zip'] ;?></p>       
										<?php if(strlen($address['addr_phone']) > 0) { 
                                            $addrPhone = ValidateElement::formatDialCode($address['addr_phone_dcode']) . $address['addr_phone'];
                                            ?>
										<p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone ;?></p>    
										<?php } ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <?php if (!commonhelper::isAppUser()) { ?>
                                        <ul class="list-actions">
                                            <li>
                                                <a href="javascript:void(0)" onClick="editAddress('<?php echo $address['addr_id']; ?>', '<?php echo $addressType; ?>')"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit">
                                                        </use>
                                                    </svg>
                                                </a></li>
                                            <li>
                                                <a href="javascript:void(0)" onclick="removeAddress('<?php echo $address['addr_id']; ?>', '<?php echo $addressType; ?>')"><svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                                        </use>
                                                    </svg>
                                                </a></li>
                                        </ul>
                                    <?php } ?>
                                </div>
                            </div></label>                                                                 
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <div id="addressFormDiv" style="display:none">
                <?php $tplDataArr = array(
                    'siteLangId' => $siteLangId,
                    'addressFrm' => $addressFrm,
                    'labelHeading' => Labels::getLabel('LBL_Add_New_Address', $siteLangId),
                    'stateId'    =>    $stateId,
                ); ?>
                <?php $this->includeTemplate('checkout/address-form.php', $tplDataArr, false);    ?>

            </div>
        </div>
        <div class="checkout-actions">
            <?php if ($addressType == Address::ADDRESS_TYPE_BILLING) { ?>
                <a class="btn btn-outline-brand btn-wide" href="javascript:void(0);" onclick="loadPaymentSummary();">
                    <?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>
                </a>
            <?php } else { ?>
                <a class="btn btn-outline-brand btn-wide" href="javascript:void(0);" onclick="goToBack();">
                    
                    <?php echo Labels::getLabel('LBL_Back', $siteLangId); ?> 
                </a>
            <?php } ?>
            <?php if ($addressType == Address::ADDRESS_TYPE_BILLING) { ?>
                <a href="javascript:void(0)" id="btn-continue-js" onClick="setUpBillingAddressSelection(this);" class="btn btn-brand btn-wide"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            <?php } else { ?>
                <a href="javascript:void(0)" id="btn-continue-js" onClick="setUpAddressSelection();" class="btn btn-brand btn-wide"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            <?php } ?>
        </div>
    </form>
</div>