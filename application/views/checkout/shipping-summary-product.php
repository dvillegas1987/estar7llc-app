<?php
$productUrl = !$isAppUser ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : 'javascript:void(0)';
$shopUrl = !$isAppUser ? UrlHelper::generateUrl('Shops', 'View', array($product['shop_id'])) : 'javascript:void(0)';
$imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
?>
<ul class="list-group list-cart list-cart-page list-shippings">
    <li class="list-group-item shipping-select">
        <div class="shop-name"><?php echo $product['shop_name']; ?></div>
        <div class="shipping-method">
            <?php
            $rates = isset($shippedByItemArr[$shipLevel]['rates']) ? $shippedByItemArr[$shipLevel]['rates'] : [];
            $priceListCount = !empty($rates) && isset($rates[$product['selprod_id']]) ? count($rates[$product['selprod_id']]) : 0;
            if ($priceListCount > 0) {
                $name = current($rates[$product['selprod_id']])['code'];
                echo '<select class="form-control custom-select" name="shipping_services[' . $name . ']">';
                foreach ($rates[$product['selprod_id']] as $key => $shippingcharge) {
                    $selected = '';
                    if (!empty($orderShippingData)) {
                        foreach ($orderShippingData as $shipdata) {
                            if ($shipdata['opshipping_code'] == $name && ($key == $shipdata['opshipping_carrier_code'] . "|" . $shipdata['opshipping_label'] || $key == $shipdata['opshipping_rate_id'])) {
                                $selected = 'selected=selected';
                                break;
                            }
                        }
                    }
                    echo '<option ' . $selected . ' value="' . $key . '">' . $shippingcharge['title'] . ' ( ' . CommonHelper::displayMoneyFormat($shippingcharge['cost']) . ' ) </option>';
                }
                echo '</select>';
            } elseif ($product['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                echo Labels::getLabel('MSG_Product_is_not_available_for_shipping', $siteLangId);
            }
            ?>
        </div>
    </li>
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
                    <a href="#" onclick="cart.remove('<?php echo md5($product['key']); ?>','checkout')">
                        <svg class="svg" width="24px" height="24px">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                            </use>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>