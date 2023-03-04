<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<script>
    var data = {
        value: <?php echo $orderInfo['order_net_amount']; ?>,
        currency: '<?php echo $orderInfo['order_currency_code']; ?>'
    };
    events.purchase(data);
</script>
<?php
$products = $orderInfo['orderProducts'];
$shippingMethod = '';
if (Orders::ORDER_PRODUCT == $orderInfo['order_type']) {
    foreach ($products as $op) {
        $shippingMethod .= !empty($op['opshipping_label']) ? '<li>' . $op['opshipping_label'] . '</li>' : '';
    }
}

$fulfillmentType = Shipping::FULFILMENT_SHIP;
array_walk($orderFulFillmentTypeArr, function ($row) use (&$fulfillmentType) {
    if (Product::PRODUCT_TYPE_PHYSICAL == $row['op_product_type']) {
        $fulfillmentType = $row['opshipping_fulfillment_type'];
        return;
    }
});
?>
<div id="body" class="body">
    <section class="section">
        <div class="order-completed">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-9">
                        <div class="thanks-screen text-center">
                            <!-- Icon -->
                            <div class="success-animation">
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"></circle>
                                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"></path>
                                </svg>
                            </div>
                            <h2><?php echo Labels::getLabel('LBL_THANK_YOU!', $siteLangId); ?></h2>
                            <h3>
                                <?php
                                if (Orders::ORDER_PRODUCT == $orderInfo['order_type']) {
                                    $msg = Labels::getLabel('LBL_YOUR_ORDER_{ORDER-ID}_HAS_BEEN_PLACED!', $siteLangId);
                                    $orderDetailUrl = UrlHelper::generateUrl('Buyer', 'viewOrder', array($orderInfo['order_id']));
                                    $orderDetailLinkHtml = '<a href="' . $orderDetailUrl . '" class="link">#' . $orderInfo['order_id'] . '</a>';
                                } else {
                                    $msg = Labels::getLabel('LBL_ORDER_#{ORDER-ID}_TRANSACTION_COMPLETED!', $siteLangId);
                                    $orderProducts = current($orderInfo['orderProducts']);
                                    $orderDetailUrl = UrlHelper::generateUrl('Seller', 'viewSubscriptionOrder', array($orderProducts['ossubs_id']));
                                    $orderDetailLinkHtml = $orderInfo['order_id'];
                                    if (isset($orderProducts['ossubs_id'])) {
                                        $orderDetailLinkHtml = '<a href="' . $orderDetailUrl . '">' . $orderInfo['order_id'] . '</a>';
                                    }
                                }
                                $msg = CommonHelper::replaceStringData($msg, ['{ORDER-ID}' => $orderDetailLinkHtml]);
                                echo $msg;
                                ?>
                            </h3>
                            <?php if (!CommonHelper::isAppUser()) { ?>
                                <p><?php echo CommonHelper::renderHtml($textMessage); ?></p>
                            <?php } ?>
                            <?php if ($orderInfo['order_type'] != Orders::ORDER_WALLET_RECHARGE) { ?>
                                <p>
                                    <svg class="svg" width="22px" height="22px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#TimePlaced" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#TimePlaced">
                                        </use>
                                    </svg>
                                    <?php
                                    $replace = [
                                        '{TIME-PLACED}' => '<strong>' . Labels::getLabel('LBL_TIME_PLACED', $siteLangId) . '</strong>',
                                        '{DATE-TIME}' => $orderInfo['order_date_added'],
                                    ];
                                    $msg = Labels::getLabel('LBL_{TIME-PLACED}:_{DATE-TIME}', $siteLangId);
                                    $msg = CommonHelper::replaceStringData($msg, $replace);
                                    echo $msg;
                                    ?>
                                    &nbsp;&nbsp;&nbsp;
                                    <span class="no-print">

                                        <a class="btn btn-link" href="<?php echo UrlHelper::generateUrl('Custom', 'PaymentSuccess', [$orderInfo['order_id'], 'print']); ?>"> <svg class="svg" width="22px" height="22px">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#print" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#print">
                                                </use>
                                            </svg> <?php echo Labels::getLabel("LBL_PRINT", $siteLangId); ?></a>
                                    </span>
                                </p>
                            <?php } ?>
                        </div>

                        <ul class="completed-detail">
                            <?php if (!empty($orderInfo['shippingAddress'])) {
                                $shippingAddress = $orderInfo['shippingAddress']; ?>
                                <li>
                                    <h4>
                                        <svg class="svg" width="22px" height="22px">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping">
                                            </use>
                                        </svg>
                                        <?php echo Labels::getLabel("LBL_SHIPPING_ADDRESS", $siteLangId); ?>
                                    </h4>
                                    <p>
                                        <strong><?php echo $shippingAddress['oua_name']; ?></strong><br>
                                        <?php
                                        echo $shippingAddress['oua_address1'];
                                        if (!empty($shippingAddress['oua_address2'])) {
                                            echo ', ' . $shippingAddress['oua_address2'];
                                        }
                                        echo '<br>' . $shippingAddress['oua_city'] . ', ' . $shippingAddress['oua_state'];
                                        echo '<br>' . $shippingAddress['oua_country'] . '(' . $shippingAddress['oua_zip'] . ')';
                                        echo '<br>' . ValidateElement::formatDialCode($shippingAddress['oua_phone_dcode']) . $shippingAddress['oua_phone'];
                                        ?>
                                    </p>
                                </li>
                                <?php }
                            if (Orders::ORDER_PRODUCT == $orderInfo['order_type']) {
                                if (!empty($orderFulFillmentTypeArr) && Shipping::FULFILMENT_PICKUP == $fulfillmentType) { ?>
                                    <li>
                                        <h4>
                                            <svg class="svg" width="22px" height="22px">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping">
                                                </use>
                                            </svg> <?php echo Labels::getLabel('LBL_ORDER_PICKUP', $siteLangId); ?>
                                        </h4>

                                        <?php
                                        foreach ($orderFulFillmentTypeArr as $orderAddDet) {
                                            if (empty($orderAddDet['addr_id'])) {
                                                continue;
                                            }
                                        ?>
                                            <p>
                                                <strong>
                                                    <?php
                                                    $opshippingDate = isset($orderAddDet['opshipping_date']) ? $orderAddDet['opshipping_date'] . ' ' : '';
                                                    $timeSlotFrom = isset($orderAddDet['opshipping_time_slot_from']) ? $orderAddDet['opshipping_time_slot_from'] . ' - ' : '';
                                                    $timeSlotTo = isset($orderAddDet['opshipping_time_slot_to']) ? $orderAddDet['opshipping_time_slot_to'] : '';
                                                    echo '#' . $orderAddDet['op_invoice_number'] . '<br>' . $opshippingDate . $timeSlotFrom . $timeSlotTo;
                                                    ?>
                                                </strong><br>
                                                <?php echo $orderAddDet['addr_name']; ?>,
                                                <?php
                                                $address1 = !empty($orderAddDet['addr_address1']) ? $orderAddDet['addr_address1'] : '';
                                                $address2 = !empty($orderAddDet['addr_address2']) ? ', ' . $orderAddDet['addr_address2'] : '';
                                                $city = !empty($orderAddDet['addr_city']) ? '<br>' . $orderAddDet['addr_city'] : '';
                                                $state = !empty($orderAddDet['state_name']) ? ', ' . $orderAddDet['state_name'] : '';
                                                $country = !empty($orderAddDet['country_name']) ? ', ' . $orderAddDet['country_name'] : '';
                                                $zip = !empty($orderAddDet['addr_zip']) ? '(' . $orderAddDet['addr_zip'] . ')' : '';
                                                $phone = !empty($orderAddDet['addr_phone']) ? $orderAddDet['addr_phone'] : '';
                                                if (!empty($phone) && array_key_exists('addr_phone_dcode', $orderAddDet)) {
                                                    $phone = ValidateElement::formatDialCode($orderAddDet['addr_phone_dcode']) . $phone;
                                                }
                                                $phone = '<br>' . $phone;
                                                echo $address1 . $address2 . $city . $state . $country . $zip . $phone;
                                                ?>
                                            </p>
                                        <?php } ?>
                                    </li>
                                <?php } else if (!empty($shippingMethod)) { ?>
                                    <li>
                                        <h4>
                                            <svg class="svg" width="22px" height="22px">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-method" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-method">
                                                </use>
                                            </svg> <?php echo Labels::getLabel('LBL_SHIPPING_METHOD', $siteLangId); ?> </h4>
                                        <p><?php echo Labels::getLabel('LBL_PREFERRED_METHOD', $siteLangId); ?>: <br>
                                            <ol class="preferred-shipping-list">
                                                <?php echo $shippingMethod; ?>
                                            </ol>
                                        </p>
                                    </li>
                                <?php }
                                if (!empty($orderInfo['billingAddress'])) { ?>
                                    <li>
                                        <?php $billingAddress = $orderInfo['billingAddress']; ?>
                                        <h4>
                                            <svg class="svg" width="22px" height="22px">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#billing-detail" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#billing-detail">
                                                </use>
                                            </svg>
                                            <?php echo Labels::getLabel("LBL_BILLING_ADDRESS", $siteLangId); ?>
                                        </h4>
                                        <p>
                                            <strong><?php echo $billingAddress['oua_name']; ?></strong><br>
                                            <?php
                                            echo $billingAddress['oua_address1'];
                                            if (!empty($billingAddress['oua_address2'])) {
                                                echo ', ' . $billingAddress['oua_address2'];
                                            }
                                            echo '<br>' . $billingAddress['oua_city'] . ', ' . $billingAddress['oua_state'];
                                            echo '<br>' . $billingAddress['oua_country'] . '(' . $billingAddress['oua_zip'] . ')';
                                            echo '<br>' . ValidateElement::formatDialCode($billingAddress['oua_phone_dcode']) . $billingAddress['oua_phone'];
                                            ?>
                                        </p>
                                    </li>
                            <?php }
                            } ?>
                        </ul>
                        <?php if ($orderInfo['order_type'] != Orders::ORDER_WALLET_RECHARGE) { ?>
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <div class="completed-cart">
                                        <div class="row justify-content-between">
                                            <div class="col-md-7">
                                                <div class="bg-gray rounded p-4">
                                                    <h5><?php echo Labels::getLabel('LBL_ORDER_DETAIL', $siteLangId); ?></h5>
                                                    <ul class="list-group list-cart list-cart-checkout">
                                                        <?php
                                                        $shippingCharges = $subTotal = 0;
                                                        if (Orders::ORDER_PRODUCT == $orderInfo['order_type']) {
                                                            foreach ($products as $key => $product) {
                                                                $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['op_selprod_id']));
                                                                $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['op_shop_id']));
                                                                $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['selprod_product_id'], "MINI", $product['op_selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                                                $productTitle =  ($product['op_selprod_title']) ? $product['op_selprod_title'] : $product['op_product_name'];
                                                        ?>
                                                                <li class="list-group-item">
                                                                    <div class="product-profile">
                                                                        <div class="product-profile__thumbnail">
                                                                            <a href="<?php echo $productUrl; ?>">
                                                                                <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['op_product_name']; ?>" title="<?php echo $product['op_product_name']; ?>">
                                                                            </a>
                                                                            <span class="product-qty"><?php echo $product['op_qty']; ?></span>
                                                                        </div>
                                                                        <div class="product-profile__data">
                                                                            <div class="title"><a class="" href="<?php echo $productUrl; ?>"><?php echo $productTitle; ?></a> </div>
                                                                            <div class="options">
                                                                                <p class=""> <?php echo $product['op_selprod_options']; ?></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="product-price">
                                                                        <?php
                                                                        $subTotal += $txnAmount = ($product["op_unit_price"] * $product["op_qty"]);
                                                                        echo CommonHelper::displayMoneyFormat($txnAmount);

                                                                        $shippingCharges += $product['op_actual_shipping_charges'];
                                                                        ?>
                                                                    </div>
                                                                </li>
                                                            <?php }
                                                        } else {
                                                            foreach ($products as $subscription) { ?>
                                                                <li class="list-group-item"><?php echo Labels::getLabel("LBL_COMMISION_RATE", $siteLangId); ?> <span><?php echo CommonHelper::displayComissionPercentage($subscription['ossubs_commission']); ?>%</span></li>
                                                                <li class="list-group-item"><?php echo Labels::getLabel("LBL_ACTIVE_PRODUCTS", $siteLangId); ?> <span><?php echo $subscription['ossubs_products_allowed']; ?></span></li>
                                                                <li class="list-group-item"><?php echo Labels::getLabel("LBL_PRODUCT_INVENTORY", $siteLangId); ?> <span><?php echo $subscription['ossubs_inventory_allowed']; ?></span></li>
                                                                <li class="list-group-item"><?php echo Labels::getLabel("LBL_IMAGES_PER_PRODUCT", $siteLangId); ?> <span><?php echo $subscription['ossubs_images_allowed']; ?></span></li>
                                                        <?php }
                                                        } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="bg-gray rounded p-4">
                                                    <h5><?php echo Labels::getLabel('LBL_ORDER_SUMMARY', $siteLangId); ?></h5>
                                                    <div class="cart-total">
                                                        <ul class="list-group list-group-flush-x list-group-flush-y mt-4">
                                                            <?php if (0 < $subTotal) { ?>
                                                                <li class="list-group-item">
                                                                    <span class="label">
                                                                        <?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?>
                                                                    </span>
                                                                    <span class="ml-auto">
                                                                        <?php echo CommonHelper::displayMoneyFormat($subTotal); ?>
                                                                    </span>
                                                                </li>
                                                            <?php }
                                                            if (0 < $orderInfo['order_reward_point_value'] || 0 < $orderInfo['order_discount_total']) {
                                                                $msg = "LBL_REWARD_POINTS";
                                                                $totalDiscount = $orderInfo['order_reward_point_value'];
                                                                if (!empty($orderInfo['order_discount_total']) && 0 < $orderInfo['order_discount_total']) {
                                                                    $msg .= "_&_DISCOUNT";
                                                                    $totalDiscount += $orderInfo['order_discount_total'];
                                                                }
                                                            ?>
                                                                <li class="list-group-item ">
                                                                    <span class="label"><?php echo Labels::getLabel($msg, $siteLangId); ?></span>
                                                                    <span class="ml-auto">- <?php echo CommonHelper::displayMoneyFormat($totalDiscount); ?></span>
                                                                </li>
                                                            <?php }
                                                            if (0 < $orderInfo['order_volume_discount_total']) {
                                                                $msg = 'LBL_Loyalty/Volume_Discount';
                                                                $totalDiscount = $orderInfo['order_volume_discount_total'];
                                                            ?>
                                                                <li class="list-group-item ">
                                                                    <span class="label"><?php echo Labels::getLabel($msg, $siteLangId); ?></span>
                                                                    <span class="ml-auto">- <?php echo CommonHelper::displayMoneyFormat($totalDiscount); ?></span>
                                                                </li>
                                                            <?php }
                                                            if (0 < $orderInfo['order_tax_charged']) { ?>
                                                                <li class="list-group-item ">
                                                                    <span class="label"><?php echo Labels::getLabel('LBL_TAX', $siteLangId); ?></span>
                                                                    <span class="ml-auto"><?php echo CommonHelper::displayMoneyFormat($orderInfo['order_tax_charged']); ?></span>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (0 < $shippingCharges) { ?>
                                                                <li class="list-group-item ">
                                                                    <span class="label"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId); ?></span>
                                                                    <span class="ml-auto"><?php echo CommonHelper::displayMoneyFormat($shippingCharges); ?></span>
                                                                </li>
                                                            <?php  } ?>
                                                            <?php if (array_key_exists('order_rounding_off', $orderInfo) && $orderInfo['order_rounding_off'] != 0) { ?>
                                                                <li class="list-group-item">
                                                                    <span class="label"><?php echo (0 < $orderInfo['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?></span>
                                                                    <span class="ml-auto"><?php echo CommonHelper::displayMoneyFormat($orderInfo['order_rounding_off']); ?></span>
                                                                </li>
                                                            <?php } ?>
                                                            <li class="list-group-item hightlighted">
                                                                <span class="label"><?php echo Labels::getLabel('LBL_NET_AMOUNT', $siteLangId); ?></span>
                                                                <span class="ml-auto"><?php echo CommonHelper::displayMoneyFormat($orderInfo['order_net_amount']); ?></span>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
<?php if (true === $print) { ?>
    <script>
        $(document).ready(function() {
            setTimeout(() => {
                window.print();
            }, 1000);
            window.onafterprint = function() {
                location.href = history.back();
            }
        });
    </script>
<?php } 


 if (Orders::ORDER_PRODUCT == $orderInfo['order_type'] && !empty(FatApp::getConfig("CONF_ANALYTICS_ID"))) {
    ?>
    <script>
        $(document).ready(function() {
            ga('require', 'ecommerce');
            <?php
            echo EcommerceTrackingHelper::getTransactionJs([
                'id' => $orderInfo['order_id'],
                'shipping' => $shippingCharges,
                'tax' => $orderInfo['order_tax_charged'],
                'affiliation' => FatApp::getConfig("CONF_WEBSITE_NAME_" . $siteLangId),
                'currency' => $orderInfo['order_currency_code'],
                'revenue' => $orderInfo['order_net_amount']
            ]);
            foreach ($products as $product) {
                $productTitle = ($product['op_selprod_title']) ? $product['op_selprod_title'] : $product['op_product_name'];
                echo EcommerceTrackingHelper::getItemJs($orderInfo['order_id'], [
                    'name' => $productTitle,
                    'sku' => $product['op_selprod_sku'],
                    'price' => $product["op_unit_price"],
                    'quantity' => $product['op_qty']
                ]);
            }
            ?>
            ga('ecommerce:send');
        });
    </script>
<?php } ?>