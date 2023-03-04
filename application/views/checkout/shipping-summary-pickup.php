<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<main class="main__content">
    <div id="shipping-summary" class="step active" role="step:3">
        <ul class="list-group review-block">
            <li class="list-group-item">
                <div class="review-block__label">
                    <?php echo Labels::getLabel('LBL_Billing_to:', $siteLangId); ?>
                </div>
                <div class="review-block__content" role="cell">
                    <p><?php echo $addresses['addr_name'] . ', ' . $addresses['addr_address1']; ?>
                        <?php if (strlen($addresses['addr_address2']) > 0) {
                            echo ", " . $addresses['addr_address2']; ?>
                        <?php } ?>
                    </p>
                    <p><?php echo $addresses['addr_city'] . ", " . $addresses['state_name'] . ", " . $addresses['country_name'] . ", " . $addresses['addr_zip']; ?></p>
                    <?php if (strlen($addresses['addr_phone']) > 0) { 
                        $addrPhone = ValidateElement::formatDialCode($addresses['addr_phone_dcode']) . $addresses['addr_phone'];
                        ?>
                        <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone; ?></p>
                    <?php } ?>
                </div>
                <div class="review-block__link" role="cell">
                    <a class="link" href="javascript:void(0);" onClick="showAddressList()"><span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                </div>
            </li>
        </ul>

        <div class="step__section">
            <div class="step__section__head">
                <h5 class="step__section__head__title">
                    <?php 
                    $cartObj = new Cart();
                    if ($cartObj->hasPhysicalProduct()) {
                        echo Labels::getLabel('LBL_Pickup_Summary', $siteLangId); 
                    } else {
                        echo Labels::getLabel('LBL_REVIEW_CHECKOUT', $siteLangId);
                    }
                    ?>
                </h5>
            </div>
            <?php
            ksort($shippingRates);
            $levelNo = 0;
            foreach ($shippingRates as $pickUpBy => $levelItems) { ?>
                <ul class="list-group list-cart list-cart-page list-shippings">
                    <?php //if (count($levelItems['products']) > 0 && $pickUpBy == 0) {
                    if (isset($levelItems['products']) && count($levelItems['products']) > 0 && $pickUpBy == 0) {
                        $productData = current($levelItems['products']);
                    ?>
                        <li class="list-group-item shipping-select">
                            <div class="shop-name"><?php echo ($pickUpBy == Shipping::LEVEL_SHOP) ? $productData['shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, ''); ?></div>
                            <div class="shop-address js-slot-addr_<?php echo $pickUpBy; ?>">
                                <?php $seletedSlotId = '';
                                $seletedSlotDate = '';
                                $seletedAddrId = '';
                                if (!empty($levelItems['pickup_address'])) {
                                    $address = $levelItems['pickup_address'];
                                    $seletedSlotId = $address['time_slot_id'];
                                    $seletedSlotDate = $address['time_slot_date'];
                                    $seletedAddrId = $address['addr_id'];
                                    $fromTime = date('H:i', strtotime($address["time_slot_from"]));
                                    $toTime = date('H:i', strtotime($address["time_slot_to"]));
                                ?>
                                    <p><?php echo $address['addr_name'] . ', ' . $address['addr_address1']; ?>
                                        <?php if (strlen($address['addr_address2']) > 0) {
                                            echo ", " . $address['addr_address2']; ?>
                                        <?php } ?>
                                    </p>
                                    <p><?php echo $address['addr_city'] . ", " . $address['state_name']; ?></p>
                                    <p><?php echo $address['country_name'] . ", " . $address['addr_zip']; ?></p>
                                    <?php if (strlen($address['addr_phone']) > 0) { 
                                        $addrPhone = ValidateElement::formatDialCode($address['addr_phone_dcode']) . $address['addr_phone'];
                                        ?>
                                        <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone; ?></p>
                                    <?php } ?>
                                    <p class="time-txt"><i class="fas fa-calendar-day"></i><?php echo FatDate::format($address["time_slot_date"]) . ' ' . $fromTime . ' - ' . $toTime; ?></p>
                                <?php } ?>
                            </div>
                            <div class="shipping-method js-slot-addr-<?php echo $pickUpBy; ?>" data-addr-id="<?php echo $seletedAddrId; ?>">
                                <input type="hidden" name="slot_id[<?php echo $pickUpBy; ?>]" class="js-slot-id" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotId; ?>">
                                <input type="hidden" name="slot_date[<?php echo $pickUpBy; ?>]" class="js-slot-date" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotDate; ?>">
                                <?php if (count($levelItems['pickup_options']) > 0) { ?>
                                    <a class="btn btn-secondary btn-sm pickupAddressBtn-<?php echo $pickUpBy; ?>-js" href="javascript:void(0)" onclick="displayPickupAddress(<?php echo $pickUpBy; ?>, 0)">
                                        <?php
                                        if (!empty($levelItems['pickup_address'])) {
                                            echo Labels::getLabel('LBL_CHANGE_PICKUP', $siteLangId);
                                        } else {
                                            echo Labels::getLabel('LBL_SELECT_PICKUP', $siteLangId);
                                        }
                                        ?>
                                    </a>
                                <?php } else {
                                    echo Labels::getLabel('MSG_NO_PICKUP_ADDRESS_CONFIGURED', $siteLangId);
                                } ?>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if (isset($levelItems['products'])) {
                        foreach ($levelItems['products'] as $product) {
                            $productUrl = !$isAppUser ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : 'javascript:void(0)';
                            $shopUrl = !$isAppUser ? UrlHelper::generateUrl('Shops', 'View', array($product['shop_id'])) : 'javascript:void(0)';
                            $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                            <?php if ($levelNo != $pickUpBy) {
                                if (count($levelItems['products']) > 0  && $pickUpBy != 0) {
                                    //if (count($levelItems['products']) > 0 && $pickUpBy != 0) {
                            ?>
                                    <li class="list-group-item shipping-select">
                                        <div class="shop-name"><?php echo $product['shop_name']; ?></div>
                                        <div class="shop-address js-slot-addr_<?php echo $pickUpBy; ?>">
                                            <?php $seletedSlotId = '';
                                            $seletedSlotDate = '';
                                            $seletedAddrId = '';
                                            if (!empty($levelItems['pickup_address'])) {
                                                $address = $levelItems['pickup_address'];
                                                $seletedSlotId = $address['time_slot_id'];
                                                $seletedSlotDate = $address['time_slot_date'];
                                                $seletedAddrId = $address['addr_id'];
                                                $fromTime = date('H:i', strtotime($address["time_slot_from"]));
                                                $toTime = date('H:i', strtotime($address["time_slot_to"]));
                                            ?>
                                                <p><?php echo $address['addr_name'] . ', ' . $address['addr_address1']; ?>
                                                    <?php if (strlen($address['addr_address2']) > 0) {
                                                        echo ", " . $address['addr_address2']; ?>
                                                    <?php } ?>
                                                </p>
                                                <p><?php echo $address['addr_city'] . ", " . $address['state_name']; ?></p>
                                                <p><?php echo $address['country_name'] . ", " . $address['addr_zip']; ?></p>
                                                <?php if (strlen($address['addr_phone']) > 0) { 
                                                    $addrPhone = ValidateElement::formatDialCode($address['addr_phone_dcode']) . $address['addr_phone'];
                                                    ?>
                                                    <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone; ?></p>
                                                <?php } ?>
                                                <p class="time-txt"><i class="fas fa-calendar-day"></i><?php echo FatDate::format($address["time_slot_date"]) . ' ' . $fromTime . ' - ' . $toTime; ?></p>
                                            <?php } ?>

                                        </div>
                                        <div class="shipping-method js-slot-addr-<?php echo $pickUpBy; ?>" data-addr-id="<?php echo $seletedAddrId; ?>">
                                            <input type="hidden" name="slot_id[<?php echo $pickUpBy; ?>]" class="js-slot-id" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotId; ?>">
                                            <input type="hidden" name="slot_date[<?php echo $pickUpBy; ?>]" class="js-slot-date" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotDate; ?>">
                                            <?php if (count($levelItems['pickup_options']) > 0) { ?>
                                                <a class="btn btn-secondary btn-sm pickupAddressBtn-<?php echo $pickUpBy; ?>-js" href="javascript:void(0)" onclick="displayPickupAddress(<?php echo $pickUpBy; ?>, <?php echo $product['shop_id']; ?>)">
                                                <?php
                                                    if (!empty($levelItems['pickup_address'])) {
                                                        echo Labels::getLabel('LBL_CHANGE_PICKUP', $siteLangId);
                                                    } else {
                                                        echo Labels::getLabel('LBL_SELECT_PICKUP', $siteLangId);
                                                    }
                                                ?>
                                                </a>
                                            <?php } else {
                                                echo Labels::getLabel('MSG_NO_PICKUP_ADDRESS_CONFIGURED', $siteLangId);
                                            } ?>
                                        </div>
                                    </li>
                                <?php } ?>
                            <?php
                            }
                            $levelNo = $pickUpBy; ?>
                            <li class="list-group-item">
                                <div class="product-profile">
                                    <div class="product-profile__thumbnail">
                                        <a href="<?php echo $productUrl; ?>">
                                            <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                                        </a></div>
                                    <div class="product-profile__data">
                                        <div class="title"><a class="" href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a></div>
                                        <div class="options">
                                            <p class=""> <?php if (isset($product['options']) && count($product['options'])) {
                                                                $optionStr = '';
                                                                foreach ($product['options'] as $option) {
                                                                    $optionStr .= $option['optionvalue_name'] . '|';
                                                                }
                                                                echo rtrim($optionStr, '|');
                                                            } ?></p>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="wrap-qty-price">
                                <div class="quantity quantity-2">
                                            <span class="decrease decrease-js"><i class="fas fa-minus"></i></span>
                                            <input class="qty-input no-focus cartQtyTextBox productQty-js" title="<?php echo Labels::getLabel('LBL_Quantity', $siteLangId) ?>" data-page="checkout" type="text" name="qty_<?php echo md5($product['key']); ?>" data-key="<?php echo md5($product['key']); ?>" value="<?php echo $product['quantity']; ?>">
                                            <span class="increase increase-js"><i class="fas fa-plus"></i></span>
                                </div>

                                <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($product['theprice'] * $product['quantity']); ?>
                                    <?php if ($product['special_price_found']) { ?>
                                        <del><?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></del>
                                    <?php } ?>
                                </div>
                                </div>
                                <div class="product-action">
                                    <ul class="list-actions">
                                        <li>
                                            <a href="javascript:void(0);" onclick="cart.remove('<?php echo md5($product['key']); ?>','checkout')">
                                                <svg class="svg" width="24px" height="24px">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                                    </use>
                                                </svg>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <?php if (isset($levelItems['products']) && count($levelItems['products']) == 1) { ?> </ul> <?php } ?>
    <?php }
                    } ?>

    <?php if (isset($levelItems['products']) && count($levelItems['products']) > 1) { ?>
        </ul>
    <?php }

                if (isset($levelItems['digital_products']) && count($levelItems['digital_products']) > 0) { ?>
        <ul class="list-group list-cart list-cart-page list-shippings">
            <?php $count = 0;
                    foreach ($levelItems['digital_products'] as $product) {
                        $productUrl = !$isAppUser ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : 'javascript:void(0)';
                        $shopUrl = !$isAppUser ? UrlHelper::generateUrl('Shops', 'View', array($product['shop_id'])) : 'javascript:void(0)';
                        $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        if ($count == 0) {
            ?>
                    <li class="list-group-item shipping-select">
                        <div class="shop-name"><?php echo $product['shop_name']; ?></div>
                    </li>
                <?php } ?>
                <li class="list-group-item">
                    <div class="product-profile">
                        <div class="product-profile__thumbnail">
                            <a href="<?php echo $productUrl; ?>">
                                <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                            </a></div>
                        <div class="product-profile__data">
                            <div class="title"><a class="" href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a></div>
                            <div class="options">
                                <p class=""> <?php if (isset($product['options']) && count($product['options'])) {
                                                    $optionStr = '';
                                                    foreach ($product['options'] as $option) {
                                                        $optionStr .= $option['optionvalue_name'] . '|';
                                                    }
                                                    echo rtrim($optionStr, '|');
                                                } ?></p>
                            </div>
                            
                        </div>
                    </div>
                    <div class="wrap-qty-price">
                    <div class="quantity quantity-2">
                                <span class="decrease decrease-js"><i class="fas fa-minus"></i></span>
                                <input class="qty-input no-focus cartQtyTextBox productQty-js" title="<?php echo Labels::getLabel('LBL_Quantity', $siteLangId) ?>" data-page="checkout" type="text" name="qty_<?php echo md5($product['key']); ?>" data-key="<?php echo md5($product['key']); ?>" value="<?php echo $product['quantity']; ?>">
                                <span class="increase increase-js"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($product['theprice'] * $product['quantity']); ?>
                        <?php if ($product['special_price_found']) { ?>
                            <del><?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></del>
                        <?php } ?>
                    </div>
                    </div>
                    <div class="product-action">
                        <ul class="list-actions">
                            <li>
                                <a href="javascript:void(0);" onclick="cart.remove('<?php echo md5($product['key']); ?>','checkout')">
                                    <svg class="svg" width="24px" height="24px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                        </use>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php $count++;
                    } ?>
        </ul>
<?php   }
            }
?>
        </div>
        <div class="step__footer">
            <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)" onclick="showAddressList();"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
            <?php if ($hasPhysicalProd) { ?>
                <a class="btn btn-brand btn-wide " onClick="setUpPickup();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            <?php } else { ?>
                <a class="btn btn-brand btn-wide " onClick="loadPaymentSummary();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            <?php } ?>
        </div>
    </div>
</main>