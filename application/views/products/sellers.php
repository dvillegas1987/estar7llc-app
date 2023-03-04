<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$displayProductNotAvailableLable = false;
if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
    $displayProductNotAvailableLable = true;
}
?>
<div id="body" class="body">
    <div class="bg-second pt-3 pb-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="prod-info">
                        <div class="prod-info__left">
                            <div class="product-avtar"><a title="<?php echo $product['selprod_title']; ?>" href="<?php echo UrlHelper::generateUrl('products', 'view', array($product['selprod_id'])); ?>"><img alt="" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "SMALL", $product['selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"></a>
                            </div>
                        </div>
                        <div class="prod-info__right">
                            <div class="avtar__info">
                                <h5><a title="<?php echo $product['selprod_title']; ?>" href="<?php echo UrlHelper::generateUrl('products', 'view', array($product['selprod_id'])); ?>"><?php echo $product['selprod_title']; ?></a></h5>
                                <?php if (round($product['prod_rating']) > 0  && FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                                ?> <div class="products__rating"><i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                                            </svg> </i><span class="rate"><?php echo round($product['prod_rating'], 1); ?></span> </div> <?php
                                                                                                                                        } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_All_Sellers', $siteLangId); ?></h5>
                </div>

                <div class="card-body">



                    <div class="js-scrollable table-wrap"> <?php
                                                            $arr_flds = array(
                                                                'shop_name'    =>    Labels::getLabel('LBL_Seller', $siteLangId),
                                                                'theprice'    =>    Labels::getLabel('LBL_Price', $siteLangId),
                                                                'COD'    =>    Labels::getLabel('LBL_COD_AVAILABLE', $siteLangId),
                                                                'viewDetails'    =>    '',
                                                                'Action'    =>    '',

                                                            );
                                                            $tbl = new HtmlElement('table', array('class' => 'table table-justified'));
                                                            $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
                                                            foreach ($arr_flds as $val) {
                                                                $e = $th->appendElement('th', array(), $val);
                                                            }

                                                            $sr_no = 0;
                                                            foreach ($product['moreSellersArr'] as $sn => $moresellers) {
                                                                $sr_no++;

                                                                $tr = $tbl->appendElement('tr', array('class' => ''));

                                                                foreach ($arr_flds as $key => $val) {
                                                                    $td = $tr->appendElement('td');
                                                                    switch ($key) {
                                                                        case 'shop_name':
                                                                            $txt = '<div class="item">
                                <figure class="item__pic item__pic-seller">
                                    <a title="' . $moresellers[$key] . '" href="' . UrlHelper::generateUrl('shops', 'view', array($moresellers['shop_id'])) . '">
                                        <img data-ratio="1:1 (150x150)" src="' . UrlHelper::generateUrl('image', 'shopLogo', array($moresellers['shop_id'], $siteLangId, 'SMALL')) . '" alt="' . $moresellers['shop_name'] . '">
                                    </a>
                                </figure>
                                <div class="item__description">
                                    <div class="item__title">
                                        <a title="' . $moresellers[$key] . '" href="' . UrlHelper::generateUrl('shops', 'view', array($moresellers['shop_id'])) . '">
                                            ' . $moresellers[$key] . '
                                        </a>
                                    </div>
                                    <div class="item__brand">
                                        <a href="' . UrlHelper::generateUrl('shops', 'view', array($moresellers['shop_id'])) . '">
                                            ' . $moresellers['shop_state_name'] . "," . $moresellers['shop_country_name'] . '
                                        </a>
                                    </div>';
                                                                            if (isset($product['rating'][$moresellers['selprod_user_id']]) && $product['rating'][$moresellers['selprod_user_id']] > 0) {
                                                                                $txt .= '<div class="products__rating">
                                                    <i class="icn">
                                                        <svg class="svg">
                                                            <use xlink:href="' . CONF_WEBROOT_URL . 'images/retina/sprite.svg#star-yellow" href="' . CONF_WEBROOT_URL . 'images/retina/sprite.svg#star-yellow">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    <span class="rate">
                                                        ' . round($product['rating'][$moresellers['selprod_user_id']], 1) . '
                                                    </span>
                                                </div>';
                                                                            }
                                                                            $txt .= '</div>
                            </div>';
                                                                            $td->appendElement('plaintext', array(), $txt, true);
                                                                            break;

                                                                        case 'theprice':
                                                                            $txt = ' <div class=""><div class="item__price">' . CommonHelper::displayMoneyFormat($moresellers['theprice']);
                                                                            if ($moresellers['special_price_found']) {
                                                                                $txt .= '  <span class="item__price_old">' . CommonHelper::displayMoneyFormat($moresellers['selprod_price']) . '</span>
                              <div class="item__price_off">' . CommonHelper::showProductDiscountedText($moresellers, $siteLangId) . '</div>';
                                                                            }
                                                                            $txt .= '</div></div>';
                                                                            $td->appendElement('plaintext', array(), $txt, true);
                                                                            break;
                                                                        case 'COD':
                                                                            $codAvailableTxt = Labels::getLabel('LBL_N/A', $siteLangId);;
                                                                            if (!empty($product['cod'][$moresellers['selprod_user_id']]) && $product['cod'][$moresellers['selprod_user_id']]) {
                                                                                $codAvailableTxt = Labels::getLabel('LBL_Cash_on_delivery_available', $siteLangId);
                                                                            }
                                                                            $td->appendElement('plaintext', array(), $codAvailableTxt, true);
                                                                            break;
                                                                        case 'viewDetails':
                                                                            $td->appendElement(
                                                                                'a',
                                                                                array('href' => UrlHelper::generateUrl('products', 'view', array($moresellers['selprod_id'])), 'class' => 'link--arrow', 'title' => Labels::getLabel('LBL_View_Details', $siteLangId), true),
                                                                                Labels::getLabel('LBL_View_Details', $siteLangId),
                                                                                true
                                                                            );
                                                                            break;

                                                                        case 'Action':
                                                                            if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) {
                                                                                $txt = '<span class="text-danger">'.Labels::getLabel('LBL_NOT_AVAILABLE', $siteLangId).'</span>';
                                                                            } else {
                                                                                if (date('Y-m-d', strtotime($moresellers['selprod_available_from'])) <=  FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d')) {
                                                                                    //$txt ='<div> <a data-id="'.$moresellers['selprod_id'].'" data-min-qty="'.$moresellers['selprod_min_order_qty'].'"  href="javascript:void(0)" class="btn btn-brand btn-sm ripplelink block-on-mobile btnProductBuy--js">  '.Labels::getLabel('LBL_Buy_Now', $siteLangId).'</a> <a data-id="'.$moresellers['selprod_id'].'" data-min-qty="'.$moresellers['selprod_min_order_qty'].'"  href="javascript:void(0)" class="btn btn-outline-brand btn-sm ripplelink block-on-mobile btnAddToCart--js">  '.Labels::getLabel('LBL_Add_To_Cart', $siteLangId).'</a> </div>';
                                                                                    $txt = '<div> <a data-id="' . $moresellers['selprod_id'] . '" data-min-qty="' . $moresellers['selprod_min_order_qty'] . '"  href="javascript:void(0)" class="btn btn-outline-brand btn-sm btnAddToCart--js">  ' . Labels::getLabel('LBL_Add_To_Cart', $siteLangId) . '</a> </div>';
                                                                                } else {
                                                                                    $txt = str_replace('{available-date}', FatDate::Format($moresellers['selprod_available_from']), Labels::getLabel('LBL_This_item_will_be_available_from_{available-date}', $siteLangId));
                                                                                }
                                                                            }
                                                                            $td->appendElement('plaintext', array(), $txt, true);

                                                                            break;

                                                                        default:
                                                                            $td->appendElement('plaintext', array(), $moresellers[$key], true);
                                                                            break;
                                                                    }
                                                                }
                                                            }
                                                            echo $tbl->getHtml(); ?> </div>
                </div>
            </div>
        </div>
    </section>
</div>