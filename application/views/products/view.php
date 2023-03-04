<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$buyQuantity = $frmBuyProduct->getField('quantity');
$buyQuantity->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
$buyQuantity->addFieldTagAttribute('data-page', 'product-view');
?>
<div id="body" class="body detail-page">
    <section class="">
        <div class="container">
            <div class="py-4">
                <div class="breadcrumbs breadcrumbs--center">
                    <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php');  ?>
                </div>
            </div>
            <div class="detail-wrapper">
                <div class="detail-first-fold ">
                    <div class="row justify-content-between">
                        <div class="col-lg-7 relative">
                            <div id="img-static" class="product-detail-gallery">
                                <?php $data['product'] = $product;
                                $data['productImagesArr'] = $productImagesArr;
                                $data['imageGallery'] = true;
                                /* $this->includeTemplate('products/product-gallery.php',$data,false); */ ?>
                                <div class="slider-for" dir="<?php echo CommonHelper::getLayoutDirection(); ?>" id="slider-for">
                                    <?php if ($productImagesArr) { ?>
                                        <?php foreach ($productImagesArr as $afile_id => $image) {
                                            $originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'ORIGINAL', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                                            $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                                            $thumbImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                                            <img alt="" class="xzoom active" id="xzoom-default" src="<?php echo $mainImgUrl; ?>" data-xoriginal="<?php echo $originalImgUrl; ?>">
                                        <?php break;
                                        } ?>
                                    <?php } else {
                                        $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'MEDIUM', 0)), CONF_IMG_CACHE_TIME, '.jpg');
                                        $originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'ORIGINAL', 0)), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                                        <img alt="" class="xzoom" src="<?php echo $mainImgUrl; ?>" data-xoriginal="<?php echo $originalImgUrl; ?>">
                                    <?php } ?>
                                </div>
                                <?php if ($productImagesArr) { ?>
                                    <div class="slider-nav xzoom-thumbs" dir="<?php echo CommonHelper::getLayoutDirection(); ?>" id="slider-nav">
                                        <?php foreach ($productImagesArr as $afile_id => $image) {
                                            $originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'ORIGINAL', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                                            $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                                            /* $thumbImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id']) ), CONF_IMG_CACHE_TIME, '.jpg'); */ ?>
                                            <div class="thumb"><a href="<?php echo $originalImgUrl; ?>"><img alt="" class="xzoom-gallery" width="80" src="<?php echo $mainImgUrl; ?>"></a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-lg-5 col-details-right">
                            <div class="product-description">
                                <div class="product-description-inner">
                                    <div class="">
                                        <div class="products__title">
                                            <div>
                                                <h1><?php echo $product['selprod_title']; ?></h1>
                                                <div class="favourite-wrapper favourite-wrapper-detail ">
                                                    <?php include(CONF_THEME_PATH . '_partial/collection-ui.php'); ?>
                                                    <div class="dropdown">
                                                        <a class="no-after share-icon" data-display="static" href="javascript:void(0)" data-toggle="dropdown">
                                                            <i class="icn">
                                                                <svg class="svg">
                                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share">
                                                                    </use>
                                                                </svg>
                                                            </i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-anim">
                                                            <ul class="social-sharing">
                                                                <li class="social-facebook">
                                                                    <a class="st-custom-button" data-network="facebook" data-url="<?php echo UrlHelper::generateFullUrl('Products', 'view', array($product['selprod_id'])); ?>/">
                                                                        <i class="icn"><svg class="svg">
                                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb">
                                                                                </use>
                                                                            </svg></i>
                                                                    </a>
                                                                </li>
                                                                <li class="social-twitter">
                                                                    <a class="st-custom-button" data-network="twitter">
                                                                        <i class="icn"><svg class="svg">
                                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw">
                                                                                </use>
                                                                            </svg></i>
                                                                    </a>
                                                                </li>
                                                                <li class="social-pintrest">
                                                                    <a class="st-custom-button" data-network="pinterest">
                                                                        <i class="icn"><svg class="svg">
                                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt">
                                                                                </use>
                                                                            </svg></i>
                                                                    </a>
                                                                </li>
                                                                <li class="social-email">
                                                                    <a class="st-custom-button" data-network="email">
                                                                        <i class="icn"><svg class="svg">
                                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope">
                                                                                </use>
                                                                            </svg></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                                <?php /*if (round($product['prod_rating']) > 0) {*/ ?>
                                                <?php $label = (round($product['prod_rating']) > 0) ? round($product['totReviews'], 1) . ' ' . Labels::getLabel('LBL_Reviews', $siteLangId) : Labels::getLabel('LBL_No_Reviews', $siteLangId); ?>
                                                <div class="products-reviews">
                                                    <div class="products__rating">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                                                </use>
                                                            </svg>
                                                        </i>
                                                        <span class="rate"><?php echo round($product['prod_rating'], 1); ?></span>
                                                    </div>
                                                    <a href="#itemRatings" class="totals-review link nav-scroll-js"><?php echo $label; ?></a>
                                                </div>
                                                <?php /*}*/ ?>
                                                <?php /* if (round($product['prod_rating']) == 0) {  ?>
                                            <span class="be-first"> <a
                                                    href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Be_the_first_to_review_this_product', $siteLangId); ?>
                                                </a> </span>
                                            <?php } */ ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($product['brand_name'])) { ?>
                                        <div class="brand-data"><span class="txt-gray-light"><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>:</span>
                                            <?php echo $product['brand_name']; ?></div>
                                    <?php } ?>
                                    <div class="products__price">
                                        <?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?>
                                        <?php if ($product['special_price_found']) { ?>
                                            <span class="products__price_old"><?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></span>
                                            <span class="product_off"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                                        <?php } ?>
                                    </div>
                                    <?php if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0) && 0 == Tax::getActivatedServiceId()) { ?>

                                        <p class="tax-inclusive">
                                            <?php echo Labels::getLabel('LBL_Inclusive_All_Taxes', $siteLangId); ?></p>

                                    <?php } ?>
                                    <?php /* include(CONF_THEME_PATH.'_partial/product-listing-head-section.php'); */ ?>

                                    <?php /*  if ($shop['shop_free_ship_upto'] > 0 && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
                                    <?php $freeShipAmt = CommonHelper::displayMoneyFormat($shop['shop_free_ship_upto']); ?>
                                    <div class="note-messages">
                                        <?php echo str_replace('{amount}', $freeShipAmt, Labels::getLabel('LBL_Free_shipping_up_to_{amount}_purchase', $siteLangId));?>
                                    </div>
                                    <?php } */ ?>
                                    <div class="divider"></div>
                                    <?php if (!empty($optionRows)) { ?>
                                        <div class="gap"> </div>
                                        <div class="row">
                                            <?php $selectedOptionsArr = $product['selectedOptionValues'];
                                            $count = 0;
                                            foreach ($optionRows as $key => $option) {
                                                $selectedOptionValue = $option['values'][$selectedOptionsArr[$key]]['optionvalue_name'];
                                                $selectedOptionColor = $option['values'][$selectedOptionsArr[$key]]['optionvalue_color_code']; ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="h6"><?php echo $option['option_name']; ?></div>

                                                    <div class="dropdown dropdown-options">
                                                        <button class="btn btn-outline-gray dropdown-toggle" type="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                                            <span>
                                                                <?php if ($option['option_is_color']) { ?>
                                                                    <span class="colors" style="background-color:#<?php echo $selectedOptionColor; ?>;"></span>
                                                                <?php } ?>
                                                                <?php echo $selectedOptionValue; ?>
                                                            </span>
                                                        </button>
                                                        <?php if ($option['values']) { ?>
                                                            <div class="dropdown-menu dropdown-menu-anim">
                                                                <ul class="nav nav-block" data-simplebar="init" style="max-height:150px;">
                                                                    <?php foreach ($option['values'] as $opVal) {
                                                                        $isAvailable = true;
                                                                        if (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) {
                                                                            $optionUrl = UrlHelper::generateUrl('Products', 'view', array($product['selprod_id']));
                                                                        } else {
                                                                            $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id']);
                                                                            $optionUrlArr = explode("::", $optionUrl);
                                                                            if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                                                                                $optionUrl = $optionUrlArr[0];
                                                                                $isAvailable = false;
                                                                            }
                                                                        } ?>
                                                                        <li class="nav__item <?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' is-active' : ' ';
                                                                                                echo (!$optionUrl) ? ' is-disabled' : '';
                                                                                                echo (!$isAvailable) ? 'not--available' : ''; ?>">
                                                                            <?php if ($option['option_is_color'] && $opVal['optionvalue_color_code'] != '') { ?>
                                                                                <a data-optionValueId="<?php echo $opVal['optionvalue_id']; ?>" data-selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php echo $opVal['optionvalue_name'];
                                                                                                                                                                                                                                    echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>" class="dropdown-item nav__link <?php echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                                                                                                                                                                                                                                                                                                                                echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' ' : ' ';
                                                                                                                                                                                                                                                                                                                                                                echo (!$optionUrl) ? ' is-disabled' : ''; ?>" href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                                                    <span class="colors" style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name']; ?></a>
                                                                            <?php } else { ?>
                                                                                <a data-optionValueId="<?php echo $opVal['optionvalue_id']; ?>" data-selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php echo $opVal['optionvalue_name'];
                                                                                                                                                                                                                                    echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>" class="dropdown-item nav__link <?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? '' : ' ';
                                                                                                                                                                                                                                                                                                                                                                echo (!$optionUrl) ? ' is-disabled' : ''; ?>" href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                                                    <?php echo $opVal['optionvalue_name'];  ?> </a>
                                                                            <?php } ?>
                                                                        </li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <?php /*
                                            <div class="js-wrap-drop wrap-drop" id="js-wrap-drop<?php echo $count; ?>">
                                            <span>
                                                <?php if ($option['option_is_color']) { ?>
                                                <span class="colors"
                                                    style="background-color:#<?php echo $selectedOptionColor; ?>; ?>;"></span>
                                                <?php } ?>
                                                <?php echo $selectedOptionValue; ?></span>
                                            <?php if ($option['values']) { ?>
                                            <ul class="drop">
                                                <?php foreach ($option['values'] as $opVal) {
                                                $isAvailable = true;
                                                if (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) {
                                                    $optionUrl = UrlHelper::generateUrl('Products', 'view', array($product['selprod_id']));
                                                } else {
                                                    $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id']);
                                                    $optionUrlArr = explode("::", $optionUrl);
                                                    if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                                                        $optionUrl = $optionUrlArr[0];
                                                        $isAvailable = false;
                                                    }
                                                } ?>
                                                <li class="<?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' selected' : ' ';
                                            echo (!$optionUrl) ? ' is-disabled' : '';
                                            echo (!$isAvailable) ? 'not--available':''; ?>">
                                                    <?php if ($option['option_is_color'] && $opVal['optionvalue_color_code'] != '') { ?>
                                                    <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>"
                                                        selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>"
                                                        title="<?php echo $opVal['optionvalue_name'];
                                                    echo (!$isAvailable) ? ' '.Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>"
                                                        class="<?php echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' ' : ' ';
                                                    echo (!$optionUrl) ? ' is-disabled' : '';  ?>"
                                                        href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                        <span class="colors"
                                                            style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name'];?></a>
                                                    <?php } else { ?>
                                                    <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>"
                                                        selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>"
                                                        title="<?php echo $opVal['optionvalue_name'];
                                                    echo (!$isAvailable) ? ' '.Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>"
                                                        class="<?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? '' : ' '; echo (!$optionUrl) ? ' is-disabled' : '' ?>"
                                                        href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                        <?php echo $opVal['optionvalue_name'];  ?> </a>
                                                    <?php } ?>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                            <?php } ?>
                                        </div> */ ?>
                                                </div>
                                            <?php $count++;
                                            } ?>
                                        </div>
                                    <?php } ?>
                                    <?php /*if (count($productSpecifications) > 0) { ?>
                                <div class="gap"></div>
                                <div class="box box--gray box--radius box--space">
                                    <div class="h6"><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?>:
                                    </div>
                                    <div class="list list--specification">
                                        <ul>
                                            <?php $count=1;
                                        foreach ($productSpecifications as $key => $specification) {
                                            if ($count > 5) {
                                                continue;
                                            } ?>
                                            <li><?php echo '<span>'.$specification['prodspec_name']." :</span> ".$specification['prodspec_value']; ?>
                                            </li>
                                            <?php $count++;
                                        } ?>
                                            <?php if (count($productSpecifications)>5) { ?>
                                            <li class="link_li"><a
                                                    href="javascript::void(0)"><?php echo Labels::getLabel('LBL_View_All_Details', $siteLangId); ?></a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php }*/ ?>

                                    <!-- Add To Cart [ -->
                                    <?php if (0 < $currentStock) {
                                        if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) {  ?>
                                            <div class="not-available">
                                                <svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                                                    </use>
                                                </svg>
                                                <?php echo Labels::getLabel('LBL_NOT_AVAILABLE_FOR_YOUR_LOCATION', $siteLangId); ?>
                                            </div>

                                            <?php } else {
                                            echo $frmBuyProduct->getFormTag();
                                            $qtyField =  $frmBuyProduct->getField('quantity');
                                            $qtyField->value = $product['selprod_min_order_qty'];
                                            $qtyField->addFieldTagAttribute('data-min-qty', $product['selprod_min_order_qty']);
                                            $qtyFieldName =  $qtyField->getCaption();
                                            if (strtotime($product['selprod_available_from']) <= strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) { ?>
                                                <div class="row align-items-end">
                                                    <div class="col-auto mb-2">
                                                        <label class="h6"><?php echo $qtyFieldName; ?></label>
                                                        <div class="qty-wrapper">
                                                            <div class="quantity" data-stock="<?php echo $currentStock; ?>">
                                                                <span class="decrease decrease-js not-allowed"><i class="fas fa-minus"></i></span>
                                                                <div class="qty-input-wrapper" data-stock="<?php echo $currentStock; ?>">
                                                                    <?php echo $frmBuyProduct->getFieldHtml('quantity'); ?>
                                                                </div>
                                                                <span class="increase increase-js"><i class="fas fa-plus"></i></span>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="col mb-2">
                                                        <label class="h6">&nbsp;</label>
                                                        <div class="buy-group">
                                                            <?php if (strtotime($product['selprod_available_from']) <= strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) {
                                                                //echo $frmBuyProduct->getFieldHtml('btnProductBuy');
                                                                echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                                                            }
                                                            echo $frmBuyProduct->getFieldHtml('selprod_id'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="gap"></div>
                                            </form>
                                        <?php echo $frmBuyProduct->getExternalJs();
                                        }
                                    } else { ?>
                                        <div class="tag--soldout tag--soldout-full">
                                            <h3>
                                                <?php echo Labels::getLabel('LBL_Sold_Out', $siteLangId); ?></h3>
                                            <p>
                                                <?php echo Labels::getLabel('LBL_This_item_is_currently_out_of_stock', $siteLangId); ?>
                                            </p>
                                        </div>
                                    <?php } ?>
                                    <?php if (strtotime($product['selprod_available_from']) > strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) { ?>
                                        <div class="tag--soldout tag--soldout-full">
                                            <h3>
                                                <?php echo Labels::getLabel('LBL_Not_Available', $siteLangId); ?></h3>
                                            <p>
                                                <?php echo str_replace('{available-date}', FatDate::Format($product['selprod_available_from']), Labels::getLabel('LBL_This_item_will_be_available_from_{available-date}', $siteLangId)); ?>
                                            </p>
                                        </div>
                                    <?php } ?>
                                    <!-- ] -->


                                    <?php /* if ($product['product_upc']!='') { ?>
                                <div class="gap"></div>
                                <div>
                                    <?php echo Labels::getLabel('LBL_EAN/UPC/GTIN_code', $siteLangId).' : '.$product['product_upc'];?>
                                </div>
                                <?php } */ ?>

                                    <?php /* Volume Discounts[ */
                                    if (isset($volumeDiscountRows) && !empty($volumeDiscountRows) && 0 < $currentStock) { ?>
                                        <div class="gap"></div>
                                        <div class="h6">
                                            <?php echo Labels::getLabel('LBL_Wholesale_Price_(Piece)', $siteLangId); ?>:</div>
                                        <div class="<?php echo (count($volumeDiscountRows) > 1) ? 'js--discount-slider' : ''; ?> discount-slider" dir="<?php echo CommonHelper::getLayoutDirection(); ?>">
                                            <?php foreach ($volumeDiscountRows as $volumeDiscountRow) {
                                                $volumeDiscount = $product['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
                                                $price = ($product['theprice'] - $volumeDiscount); ?>
                                                <div class="item">
                                                    <div class="qty__value">
                                                        <?php echo ($volumeDiscountRow['voldiscount_min_qty']); ?>
                                                        <?php echo Labels::getLabel('LBL_Or_more', $siteLangId); ?>
                                                        (<?php echo $volumeDiscountRow['voldiscount_percentage'] . '%'; ?>) <span class="item__price"><?php echo CommonHelper::displayMoneyFormat($price); ?>
                                                            /
                                                            <?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></span></div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <script>
                                            $("document").ready(function() {
                                                $('.js--discount-slider').slick(getSlickSliderSettings(2, 1, langLbl
                                                    .layoutDirection, false, {
                                                        1199: 2,
                                                        1023: 2,
                                                        767: 2,
                                                        480: 2
                                                    }, false));
                                            });
                                        </script>
                                    <?php } /* ] */ ?>

                                    <!-- Upsell Products [ -->
                                    <?php if (count($upsellProducts) > 0) { ?>
                                        <div class="gap"></div>
                                        <div class="h6"><?php echo Labels::getLabel('LBL_Product_Add-ons', $siteLangId); ?>
                                        </div>
                                        <div class="addons-scrollbar" data-simplebar="init" data-simplebar-auto-hide="false">
                                            <ul class="list-addons list-addons--js">
                                                <?php foreach ($upsellProducts as $usproduct) {
                                                    $cancelClass = '';
                                                    $uncheckBoxClass = '';
                                                    if ($usproduct['selprod_stock'] <= 0) {
                                                        $cancelClass = 'cancel cancelled--js';
                                                        $uncheckBoxClass = 'remove-add-on';
                                                    } ?>
                                                    <li class="addon--js <?php echo $cancelClass; ?>">
                                                        <div class="item">
                                                            <figure class="item__pic"><a title="<?php echo $usproduct['selprod_title']; ?>" href="<?php echo UrlHelper::generateUrl('products', 'view', array($usproduct['selprod_id'])) ?>"><img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($usproduct['product_id'], 'MINI', $usproduct['selprod_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $usproduct['product_identifier']; ?>"> </a>
                                                            </figure>
                                                            <div class="item__description">
                                                                <div class="item__title"><a href="<?php echo UrlHelper::generateUrl('products', 'view', array($usproduct['selprod_id'])) ?>"><?php echo $usproduct['selprod_title'] ?></a>
                                                                </div>
                                                                <div class="item__price">
                                                                    <?php echo CommonHelper::displayMoneyFormat($usproduct['theprice']); ?>
                                                                </div>
                                                            </div>
                                                            <?php if ($usproduct['selprod_stock'] <= 0) { ?>
                                                                <div class="tag--soldout"><?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId); ?></div>
                                                            <?php  } ?>
                                                        </div>

                                                        <div class="qty-wrapper">
                                                            <div class="quantity quantity-2" data-stock="<?php echo $usproduct['selprod_stock']; ?>"><span class="decrease decrease-js"><i class="fas fa-minus"></i></span>
                                                                <div class="qty-input-wrapper" data-stock="<?php echo $usproduct['selprod_stock']; ?>">
                                                                    <input type="text" value="1" data-page="product-view" placeholder="Qty" class="qty-input cartQtyTextBox productQty-js" data-lang="addons[<?php echo $usproduct['selprod_id'] ?>]" name="addons[<?php echo $usproduct['selprod_id'] ?>]">
                                                                </div>
                                                                <span class="increase increase-js"><i class="fas fa-plus"></i></span>
                                                            </div>
                                                        </div>
                                                        <label class="checkbox">
                                                            <input <?php echo ($usproduct['selprod_stock'] > 0) ? 'checked="checked"' : ''; ?> type="checkbox" class="cancel <?php echo $uncheckBoxClass; ?>" name="check_addons" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                                            <i class="input-helper"></i> </label>


                                                    </li>
                                                <?php } ?>
                                            </ul>

                                        </div>
                                    <?php } ?>
                                    <!-- ] -->
                                </div>
                                <div class="gap"></div>
                                <div class="sold-by bg-gray p-4 rounded">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col">
                                            <div class="m-0 -color-light">
                                                <?php echo Labels::getLabel('LBL_Seller', $siteLangId); ?></div>
                                            <h6 class="h6">
                                                <a href="<?php echo UrlHelper::generateUrl('shops', 'View', array($shop['shop_id'])); ?>"><?php echo $shop['shop_name']; ?></a>
                                            </h6>
                                            <div class="products__rating -display-inline m-0">
                                                <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                                    <i class="icn">
                                                        <svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    <span class="rate"><?php echo round($shop_rating, 1), '', '', '';
                                                                        if ($shopTotalReviews) { ?>
                                                        <?php } ?> </span>
                                                <?php } ?>
                                            </div>


                                            <?php /*if ($shop_rating>0) { ?>
                                        <div class="products__rating"> <i class="icn"><svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                                        href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                                    </use>
                                                </svg></i> <span
                                                class="rate"><?php echo round($shop_rating, 1); ?><span></span></span>
                                        </div><br>
                                        <?php }*/ ?>

                                        </div>
                                        <div class="col-auto">
                                            <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller())) && (UserAuthentication::getLoggedUserId() != $shop['shop_user_id']))) { ?>
                                                <a href="<?php echo UrlHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'], $product['selprod_id'])); ?>" class="btn btn-brand btn--secondary btn-outline-brand  btn-sm"><?php echo Labels::getLabel('LBL_Ask_Question', $siteLangId); ?></a>
                                            <?php } ?>
                                            <?php if (count($product['moreSellersArr']) > 0) { ?>
                                                <a href="<?php echo UrlHelper::generateUrl('products', 'sellers', array($product['selprod_id'])); ?>" class="btn btn-brand btn-sm "><?php echo Labels::getLabel('LBL_All_Sellers', $siteLangId); ?></a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <?php include(CONF_THEME_PATH . '_partial/product/shipping-rates.php'); ?>
                <?php $youtube_embed_code = UrlHelper::parseYoutubeUrl($product["product_youtube_video"]); ?>
            </div>
            <!-- Don't remove scrollUpTo-js span -->
            <span id="scrollUpTo-js"></span>
            <!-- ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ -->
            <div class="nav-detail nav-detail-js">
                <ul>
                    <?php if (count($productSpecifications) > 0) { ?>
                        <li><a class="nav-scroll-js is-active" href="#specifications"><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?></a>
                        </li>
                    <?php } ?>
                    <?php if (trim($product['product_description']) != '') { ?>
                        <li class=""><a class="nav-scroll-js" href="#description"><?php echo Labels::getLabel('LBL_Description', $siteLangId); ?> </a></li>
                    <?php } ?>
                    <?php if ($youtube_embed_code) { ?>
                        <li class=""><a class="nav-scroll-js" href="#video"><?php echo Labels::getLabel('LBL_Video', $siteLangId); ?> </a></li>
                    <?php } ?>
                    <?php if ($shop['shop_payment_policy'] != '' || !empty($shop["shop_delivery_policy"] != "") || !empty($shop["shop_delivery_policy"] != "")) { ?>
                        <li class=""><a class="nav-scroll-js" href="#shop-policies"><?php echo Labels::getLabel('LBL_Shop_Policies', $siteLangId); ?> </a>
                        </li>
                    <?php } ?>
                    <?php if (!empty($product['selprodComments'])) { ?>
                        <li class=""><a class="nav-scroll-js" href="#extra-comments"><?php echo Labels::getLabel('LBL_Extra_comments', $siteLangId); ?> </a>
                        </li>
                    <?php } ?>
                    <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                        <li class=""><a class="nav-scroll-js" href="#itemRatings"><?php echo Labels::getLabel('LBL_Ratings_and_Reviews', $siteLangId); ?> </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <section class="section">
                <div class="row justify-content-center">
                    <div class="col-xl-7">
                        <?php if (count($productSpecifications) > 0) { ?>
                            <div class="section-head">
                                <div class="section__heading" id="specifications">
                                    <h2><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?></h2>
                                </div>
                            </div>
                            <div class="cms bg-gray p-4 mb-4">
                                <table>
                                    <tbody>
                                            <?php
                                            $groupname = '';
                                            $specOthersStr = '';
                                            foreach ($productSpecifications as $key => $specification) {
                                                if ($groupname != $specification['prodspec_group']) {
                                                    $groupname = $specification['prodspec_group'];
                                                    ?>                                            
                                                    <tr>
                                                        <th colspan="2"><?php echo $groupname; ?></th>
                                                    </tr>
                                                <?php
                                                }
                                                if (empty($groupname)) {
                                                    $specOthersStr .= '<tr>
                                                            <th>' . $specification['prodspec_name'] . ':</th>  
                                                            <td>' . html_entity_decode($specification['prodspec_value'], ENT_QUOTES, 'utf-8') . '</td>     
                                                         </tr>';
                                                    continue;
                                                }
                                                ?>   
                                                <tr>
                                                    <td><?php echo $specification['prodspec_name'] . ":"; ?></td>
                                                    <td><?php echo html_entity_decode($specification['prodspec_value'], ENT_QUOTES, 'utf-8'); ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            if (!empty($groupname) && !empty($specOthersStr)) {
                                                echo '<tr>
                                                        <th colspan="2">' . Labels::getLabel('LBL_Others', $siteLangId) . '</th>  
                                                     </tr>';
                                                $specOthersStr = str_replace(['<th>', '</th>'], ['<td>', '</td>'], $specOthersStr);
                                            }

                                            if (!empty($specOthersStr)) {
                                                echo $specOthersStr;
                                            }
                                            ?>
                                            
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                        <?php if (trim($product['product_description']) != '') { ?>
                            <div class="section-head">
                                <div class="section__heading" id="description">
                                    <h2><?php echo Labels::getLabel('LBL_Description', $siteLangId); ?></h2>
                                </div>
                            </div>
                            <div class="cms bg-gray p-4 mb-4">
                                <p><?php echo CommonHelper::renderHtml($product['product_description']); ?></p>
                            </div>
                        <?php } ?>
                        <?php if ($youtube_embed_code) { ?>
                            <div class="section-head">
                                <div class="section__heading" id="video">
                                    <h2><?php echo Labels::getLabel('LBL_Video', $siteLangId); ?></h2>
                                </div>
                            </div>
                            <?php if ($youtube_embed_code != "") : ?>
                                <div class="mb-4 video-wrapper">
                                    <iframe width="100%" height="315" src="//www.youtube.com/embed/<?php echo $youtube_embed_code ?>" allowfullscreen></iframe>
                                </div>
                                <span class="gap"></span>
                            <?php endif; ?>
                        <?php } ?>
                        <?php if ($shop['shop_payment_policy'] != '' || !empty($shop["shop_delivery_policy"] != "") || !empty($shop["shop_delivery_policy"] != "")) { ?>
                            <div class="section-head">
                                <div class="section__heading" id="shop-policies">
                                    <h2><?php echo Labels::getLabel('LBL_Shop_Policies', $siteLangId); ?></h2>
                                </div>
                            </div>
                            <div class="cms bg-gray p-4 mb-4">
                                <?php if ($shop['shop_payment_policy'] != '') { ?>
                                    <h6><?php echo Labels::getLabel('LBL_Payment_Policy', $siteLangId) ?></h6>
                                    <p><?php echo nl2br($shop['shop_payment_policy']); ?></p>
                                    <br>
                                <?php } ?>
                                <?php if ($shop['shop_delivery_policy'] != '') { ?>
                                    <h6><?php echo Labels::getLabel('LBL_Delivery_Policy', $siteLangId) ?></h6>
                                    <p><?php echo nl2br($shop['shop_delivery_policy']); ?></p>
                                    <br>
                                <?php } ?>
                                <?php if ($shop['shop_refund_policy'] != '') { ?>
                                    <h6><?php echo Labels::getLabel('LBL_Refund_Policy', $siteLangId) ?></h6>
                                    <p><?php echo nl2br($shop['shop_refund_policy']); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if (!empty($product['selprodComments'])) { ?>
                            <div class="section-head">
                                <div class="section__heading" id="extra-comments">
                                    <h2><?php echo Labels::getLabel('LBL_Extra_comments', $siteLangId); ?></h2>
                                </div>
                            </div>
                            <div class="cms bg-gray p-4 mb-4">
                                <p><?php echo CommonHelper::displayNotApplicable($siteLangId, nl2br($product['selprodComments'])); ?>
                                </p>
                            </div>
                        <?php } ?>

                        <div id="itemRatings">
                            <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                <?php echo $frmReviewSearch->getFormHtml(); ?>
                                <?php $this->includeTemplate('_partial/product-reviews.php', array('reviews' => $reviews, 'siteLangId' => $siteLangId, 'product_id' => $product['product_id'], 'canSubmitFeedback' => $canSubmitFeedback), false); ?>
                            <?php } ?>
                        </div>

                    </div>
                </div>
            </section>
            <section class="">
                <?php if (isset($banners) && isset($banners['blocation_active']) && $banners['blocation_active'] && count($banners['banners'])) { ?>
                    <div class="gap"></div>
                    <div class="container">
                        <div class="row">
                            <?php
                            foreach ($banners['banners'] as $val) {
                                $desktop_url = '';
                                $tablet_url = '';
                                $mobile_url = '';
                                if (!AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId)) {
                                    continue;
                                } else {
                                    $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId);
                                    foreach ($slideArr as $slideScreen) {
                                        switch ($slideScreen['afile_screen']) {
                                            case applicationConstants::SCREEN_MOBILE:
                                                $mobile_url = UrlHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_MOBILE)) . ",";
                                                break;
                                            case applicationConstants::SCREEN_IPAD:
                                                $tablet_url = UrlHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_IPAD)) . ",";
                                                break;
                                            case applicationConstants::SCREEN_DESKTOP:
                                                $desktop_url = UrlHelper::generateUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_DESKTOP)) . ",";
                                                break;
                                        }
                                    }
                                } ?>
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="banner-ppc"><a href="<?php echo UrlHelper::generateUrl('Banner', 'url', array($val['banner_id'])); ?>" target="<?php echo $val['banner_target']; ?>" title="<?php echo $val['banner_title']; ?>" class="advertise__block">
                                            <picture>
                                                <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
                                                <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
                                                <source data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>">
                                                <img data-aspect-ratio="4:1" src="<?php echo $desktop_url; ?>" alt="">
                                            </picture>
                                        </a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php }
                if (isset($val['banner_record_id']) && $val['banner_record_id'] > 0 && $val['banner_type'] == Banner::TYPE_PPC) {
                    Promotion::updateImpressionData($val['banner_record_id']);
                } ?>
            </section>
        </div>
    </section>
    <?php if ($recommendedProducts) { ?>
        <section class="section bg-second">
            <?php include(CONF_THEME_PATH . 'products/recommended-products.php'); ?>
        </section>
    <?php } ?>
    <?php if ($relatedProductsRs) { ?>
        <section class="section">
            <?php include(CONF_THEME_PATH . 'products/related-products.php'); ?>
        </section>
    <?php } ?>
    <div id="recentlyViewedProductsDiv"></div>
</div>
<script>
    var mainSelprodId = <?php echo $product['selprod_id']; ?>;
    var layout = '<?php echo CommonHelper::getLayoutDirection(); ?>';

    $("document").ready(function() {
        recentlyViewedProducts(<?php echo $product['selprod_id']; ?>);
        /*zheight = $(window).height() - 180; */
        zwidth = $(window).width() / 3 - 15;

        if (layout == 'rtl') {
            $('.xzoom, .xzoom-gallery').xzoom({
                zoomWidth: zwidth,
                /*zoomHeight: zheight,*/
                title: true,
                tint: '#333',
                position: 'left'
            });
        } else {
            $('.xzoom, .xzoom-gallery').xzoom({
                zoomWidth: zwidth,
                /*zoomHeight: zheight,*/
                title: true,
                tint: '#333',
                Xoffset: 2
            });
        }

        window.setInterval(function() {
            var scrollPos = $(window).scrollTop();
            if (scrollPos > 0) {
                setProductWeightage('<?php echo $product['selprod_code']; ?>');
            }
        }, 5000);

    });

    <?php /* if( isset($banners['Product_Detail_Page_Banner']) && $banners['Product_Detail_Page_Banner']['blocation_active'] && count($banners['Product_Detail_Page_Banner']['banners']) ) { ?>
$(function() {
    if ($(window).width() > 1050) {
        $(window).scroll(sticky_relocate);
        sticky_relocate();
    }
});
<?php } */ ?>
</script>
<script>
    $(document).ready(function() {
        $("#btnAddToCart").addClass("quickView");
        $('#slider-for').slick(getSlickGallerySettings(false));
        $('#slider-nav').slick(getSlickGallerySettings(true, '<?php echo CommonHelper::getLayoutDirection(); ?>'));

        /* for toggling of tab/list view[ */
        $('.list-js').hide();
        $('.view--link-js').on('click', function(e) {
            $('.view--link-js').removeClass("btn--active");
            $(this).addClass("btn--active");
            if ($(this).hasClass('list')) {
                $('.tab-js').hide();
                $('.list-js').show();
            } else if ($(this).hasClass('tab')) {
                $('.list-js').hide();
                $('.tab-js').show();
            }
        });
        /* ] */

        $(".nav-scroll-js").click(function(event) {
            event.preventDefault();
            var full_url = this.href;
            var parts = full_url.split("#");
            var trgt = parts[1];
            /* var target_offset = $("#" + trgt).offset();

            var target_top = target_offset.top - $('#header').height();
            $('html, body').animate({
                scrollTop: target_top
            }, 800); */
            $('html, body').animate({
                scrollTop: parseInt($("#" + trgt).position().top) + parseInt($("#scrollUpTo-js")
                    .position().top)
            }, 800);

        });
        $('.nav-detail-js li a').click(function() {
            $('.nav-detail-js li a').removeClass('is-active');
            $(this).addClass('is-active');
        });

        var headerHeight = $("#header").height();
        $(".nav-detail-js").css('top', headerHeight);

    });
</script>
<!-- Product Schema Code -->
<?php
$image = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id']); ?>
<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "Product",        
        <?php if (isset($reviews['prod_rating']) && 0 < $reviews['prod_rating']) { ?> "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "<?php echo round(FatUtility::convertToType($reviews['prod_rating'], FatUtility::VAR_FLOAT), 1); ?>",
                "reviewCount": "<?php echo FatUtility::int($reviews['totReviews']); ?>"
            },
        <?php } ?> "description": "<?php echo strip_tags(CommonHelper::renderHtml($product['product_description'])); ?>",
        "name": "<?php echo $product['selprod_title']; ?>",
        <?php if (isset($product['brand_name']) && $product['brand_name'] != '') { ?> "brand": "<?php echo $product['brand_name']; ?>",
        <?php } ?>
        <?php if (isset($product['selprod_sku']) && $product['selprod_sku'] != '') { ?> "sku": "<?php echo $product['selprod_sku']; ?>",
        <?php } ?> "image": "<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>",
        "offers": {
            "@type": "Offer",
            "availability": "http://schema.org/InStock",
            "price": "<?php echo $product['theprice']; ?>",
            "url": "<?php echo UrlHelper::generateFullUrl('Products', 'view', [$product['selprod_id']]); ?>",
            "priceCurrency": "<?php echo CommonHelper::getCurrencyCode(); ?>"
        }
    }
</script>

<!-- End Product Schema Code -->

<!--Here is the facebook OG for this product  -->
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>