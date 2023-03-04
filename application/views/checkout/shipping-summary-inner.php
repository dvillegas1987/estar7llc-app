<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<main class="main__content">
    <div id="shipping-summary" class="step active" role="step:3">
        <ul class="list-group review-block">
            <li class="list-group-item">
                <div class="review-block__label">
                    <?php if ($hasPhysicalProd) {
                        echo Labels::getLabel('LBL_Shipping_to:', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_Billing_to:', $siteLangId);
                    } ?>
                </div>
                <div class="review-block__content" role="cell">
                    <div class="delivery-address">
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
                        echo Labels::getLabel('LBL_Shipping_Summary', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_REVIEW_CHECKOUT', $siteLangId);
                    }
                    ?>
                </h5>
            </div>
            <?php
            ksort($shippingRates);
            foreach ($shippingRates as $shippedBy => $shippedByItemArr) {
                ksort($shippedByItemArr);
                foreach ($shippedByItemArr as $shipLevel => $items) {
                    switch ($shipLevel) {
                        case Shipping::LEVEL_ORDER:
                        case Shipping::LEVEL_SHOP:
                            if (isset($items['products']) && !empty($items['products'])) {
                                $productData = $items['products'];
                                $productInfo = current($productData);
                                require('shipping-summary-group.php');
                            }
                            break;
                        case Shipping::LEVEL_PRODUCT:
                            if (isset($items['products']) && !empty($items['products'])) {
                                foreach ($items['products'] as $selProdid => $product) {
                                    require('shipping-summary-product.php');
                                }
                            }
                            if (isset($items['digital_products']) && !empty($items['digital_products'])) {
                                foreach ($items['digital_products'] as $selProdid => $product) {
                                    require('shipping-summary-product.php');
                                }
                            }
                            break;
                    }
                }
            } ?>
            <div class="step__footer">
                <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)" onclick="showAddressList();">
                    <?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>
                </a>
                <?php if ($hasPhysicalProd) { ?>
                    <a class="btn btn-brand btn-wide " onClick="setUpShippingMethod();" href="javascript:void(0)">
                        <?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?>
                    </a>
                <?php } else { ?>
                    <a class="btn btn-brand btn-wide " onClick="loadPaymentSummary();" href="javascript:void(0)">
                        <?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
</main>