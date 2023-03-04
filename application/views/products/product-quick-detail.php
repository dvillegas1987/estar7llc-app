<div class="row no-gutters">
    <div class="col-lg-6 col-md-6 quick-col-1">
        <?php include(CONF_THEME_PATH . '_partial/collection-ui.php'); ?>
        <?php if ($productImagesArr) { ?>
            <div class="js-product-gallery product-gallery" dir="<?php echo CommonHelper::getLayoutDirection(); ?>">
                <?php foreach ($productImagesArr as $afile_id => $image) {
                    $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                    $thumbImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>
                    <div class=""><?php if (isset($imageGallery) && $imageGallery) { ?>
                            <a href="<?php echo $mainImgUrl; ?>" class="gallery" rel="gallery">
                            <?php } ?>
                            <img src="<?php echo $mainImgUrl; ?>">
                            <?php if (isset($imageGallery) && $imageGallery) { ?>
                            </a>
                        <?php } ?></div>
                <?php } ?>
            </div>
        <?php } else {
            $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'MEDIUM', 0)), CONF_IMG_CACHE_TIME, '.jpg'); ?>
            <div class="item__main"><img src="<?php echo $mainImgUrl; ?>"></div>
        <?php
        } ?>
    </div>

    <div class="col-lg-6 col-md-6 quick-col-2">
        <div class="product-detail product-description product-detail-quickview">
            <div>
                <div class="product-description-inner">
                    <div class="products__title"><a title="<?php echo $product['selprod_title']; ?>" href="<?php echo !isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])) ?>"><?php echo $product['selprod_title']; ?></a>
                    </div>
                    <div class="gap"></div>
                    <div class="products__price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?>
                        <?php if ($product['special_price_found']) { ?>
                            <span class="products__price_old"><?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></span> <span class="product_off"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                        <?php } ?>
                    </div>
                    <?php if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0) && 0 == Tax::getActivatedServiceId()) { ?>

                        <p class="tax-inclusive"><?php echo Labels::getLabel('LBL_Inclusive_All_Taxes', $siteLangId); ?></p>

                    <?php } ?>
                    <div class="divider"></div>
                    <div class="gap"></div>
                </div>

                <?php if (!empty($optionRows)) { ?>
                    <div class="row">
                        <?php $selectedOptionsArr = $product['selectedOptionValues'];
                        $count = 0;
                        foreach ($optionRows as $key => $option) {
                            $selectedOptionValue = $option['values'][$selectedOptionsArr[$key]]['optionvalue_name'];
                            $selectedOptionColor = $option['values'][$selectedOptionsArr[$key]]['optionvalue_color_code']; ?>
                            <div class="col-md-6 mb-2">
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
                                            <ul class="nav nav-block" data-simplebar="init" style="max-height:130px;">
                                                <?php foreach ($option['values'] as $opVal) {
                                                    $isAvailable = true;
                                                    if (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) {
                                                        $optionUrl = UrlHelper::generateUrl('Products', 'view', array($product['selprod_id']));
                                                        $selprodId = $product['selprod_id'];
                                                    } else {
                                                        $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id']);
                                                        $selprodId = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id'], true);
                                                        $optionUrlArr = explode("::", $optionUrl);
                                                        if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                                                            $optionUrl = $optionUrlArr[0];
                                                            $isAvailable = false;
                                                        }
                                                    } ?>
                                                    <li class="nav__item <?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' selected' : ' ';
                                                                            echo (!$optionUrl) ? ' is-disabled' : '';
                                                                            echo (!$isAvailable) ? 'not--available' : ''; ?>">
                                                        <?php if ($option['option_is_color'] && $opVal['optionvalue_color_code'] != '') { ?>
                                                            <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>" selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php echo $opVal['optionvalue_name'];
                                                                                                                                                                                                        echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>" class="dropdown-item nav__link <?php echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                                                                                                                                                                                                                                                                                                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' ' : ' ';
                                                                                                                                                                                                                                                                                                                                                                    echo (!$optionUrl) ? ' is-disabled' : '';  ?>" href="javascript:void(0)" onclick="quickDetail(<?php echo $selprodId; ?>)"> <span class="colors" style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name']; ?></a>
                                                        <?php } else { ?>
                                                            <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>" selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php echo $opVal['optionvalue_name'];
                                                                                                                                                                                                        echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : ''; ?>" class="dropdown-item nav__link <?php echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? '' : ' ';
                                                                                                                                                                                                                                                                                                                                                                    echo (!$optionUrl) ? ' is-disabled' : '' ?>" href="javascript:void(0)" onclick="quickDetail(<?php echo $selprodId; ?>)">
                                                                <?php echo $opVal['optionvalue_name'];  ?> </a>
                                                        <?php } ?>
                                                    </li>
                                                <?php
                                                } ?>

                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php $count++;
                        } ?>
                    </div>
                <?php } ?>

                <div class="gap"></div>
            </div>
            <!-- Add To Cart [ -->
            <?php if ($product['in_stock']) {
                if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) {  ?>
                    
                    <div class="not-available">                                                 
                                                <svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                                                    </use>
                                                </svg><?php echo Labels::getLabel('LBL_NOT_AVAILABLE_FOR_YOUR_LOCATION', $siteLangId); ?>
                </div>
                     
                     
                <?php } else {
                    echo $frmBuyProduct->getFormTag();
                    $qtyField =  $frmBuyProduct->getField('quantity');
                    $qtyField->value = $product['selprod_min_order_qty'];
                    $qtyField->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
                    $qtyField->addFieldTagAttribute('data-page', 'product-view');
                    $qtyField->addFieldTagAttribute('data-min-qty', $product['selprod_min_order_qty']);
                    /* $fld = $frmBuyProduct->getField('btnAddToCart');
                $fld->addFieldTagAttribute('class','quickView'); */
                    $qtyFieldName =  $qtyField->getCaption(); ?>
                    <label class="h6"><?php echo $qtyFieldName; ?></label>
                    <div class="row">
                        <div class="col-auto">
                        <div class="qty-wrapper">
                            <div class="quantity" data-stock="<?php echo $product['selprod_stock']; ?>">
                                <span class="decrease decrease-js not-allowed"><i class="fas fa-minus"></i></span>
                                <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                                    <?php echo $frmBuyProduct->getFieldHtml('quantity'); ?>
                                </div>
                                <span class="increase increase-js"><i class="fas fa-plus"></i></span>
                            </div>
                        </div>
                        </div>
                        <div class="col">
                        <div class="buy-group">
                            <?php
                            if (strtotime($product['selprod_available_from']) <= strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) {
                                // echo $frmBuyProduct->getFieldHtml('btnProductBuy');
                                echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                            }
                            echo $frmBuyProduct->getFieldHtml('selprod_id'); ?>
                        </div>
                        </div>
                    </div>


                    </form>
                <?php echo $frmBuyProduct->getExternalJs();
                }
            } else { ?>
                <div class="tag--soldout tag--soldout-full">
                    <h3 class=""><?php echo Labels::getLabel('LBL_Sold_Out', $siteLangId); ?></h3>
                    <p class=""><?php echo Labels::getLabel('LBL_This_item_is_currently_out_of_stock', $siteLangId); ?></p>
                </div>
            <?php } ?>
            <?php if (strtotime($product['selprod_available_from']) > strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) { ?>
                <div class="tag--soldout tag--soldout-full">
                    <h3 class=""><?php echo Labels::getLabel('LBL_Not_Available', $siteLangId); ?></h3>
                    <p class=""><?php echo str_replace('{available-date}', FatDate::Format($product['selprod_available_from']), Labels::getLabel('LBL_This_item_will_be_available_from_{available-date}', $siteLangId)); ?></p>
                </div>
            <?php } ?>
            <!-- ] -->
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var layoutDirection = '<?php echo CommonHelper::getLayoutDirection(); ?>';
        if (layoutDirection == 'rtl') {
            $('.js-product-gallery').slick({
                dots: true,
                arrows: false,
                autoplay: false,
                pauseOnHover: false,
                slidesToShow: 1,
                draggable: true,
                rtl: true,
            });
        } else {
            $('.js-product-gallery').slick({
                dots: true,
                arrows: false,
                autoplay: false,
                pauseOnHover: false,
                slidesToShow: 1,
                draggable: true,
            });
        }

        $('#close-quick-js').click(function() {
            if ($('html').removeClass('quick-view--open')) {
                $('.quick-view').removeClass('quick-view--open');
            }
        });

        /* $('#close-quick-js').click(function () {
            if ($('html').removeClass('quick-view--open')) {
                $(document).trigger('close.facebox');
                $('.quick-view').removeClass('quick-view--open');
            }
        }); */
        /* $('#quickView-slider-for').slick( getSlickGallerySettings(false,'<?php echo CommonHelper::getLayoutDirection(); ?>') );
        $('#quickView-slider-nav').slick( getSlickGallerySettings(true,'<?php echo CommonHelper::getLayoutDirection(); ?>') ); */

        function DropDown(el) {
            this.dd = el;
            this.placeholder = this.dd.children('span');
            this.opts = this.dd.find('ul.drop li');
            this.val = '';
            this.index = -1;
            this.initEvents();
        }

        DropDown.prototype = {
            initEvents: function() {
                var obj = this;
                obj.dd.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).toggleClass('active');
                });
                obj.opts.on('click', function() {
                    var opt = $(this);
                    obj.val = opt.text();
                    obj.index = opt.index();
                    obj.placeholder.text(obj.val);
                    opt.siblings().removeClass('is-active');
                    opt.filter(':contains("' + obj.val + '")').addClass('is-active');
                    var link = opt.filter(':contains("' + obj.val + '")').find('a').attr('href');
                    window.location.replace(link);
                }).change();
            },
            getValue: function() {
                return this.val;
            },
            getIndex: function() {
                return this.index;
            }
        };

        $(function() {
            // create new variable for each menu
            $(document).click(function() {
                // close menu on document click
                $('.wrap-drop').removeClass('active');
            });

            $('.js-wrap-drop-quick').click(function() {
                $(this).parent().siblings().children('.js-wrap-drop-quick').removeClass('active');
            });
        });

        $(".js-wrap-drop-quick").each(function(index, element) {
            var div = '#js-wrap-drop-quick' + index;
            new DropDown($(div));
        });

    });
</script>