<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$rewardPoints = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId());
?>
<main class="main__content">
    <div class="step active" role="step:4">

        <ul class="list-group review-block">
            <li class="list-group-item">
                <div class="review-block__label">
                    <?php
                    if ($fulfillmentType == Shipping::FULFILMENT_PICKUP || $cartHasPhysicalProduct == false) {
                        echo Labels::getLabel('LBL_Billing_to:', $siteLangId);
                        $address = $billingAddressArr;
                    } else {
                        echo Labels::getLabel('LBL_Shipping_to:', $siteLangId);
                        $address = $shippingAddressArr;
                    }
                    ?>
                </div>
                <div class="review-block__content" role="cell">
                    <div class="delivery-address">
                        <p><?php echo $address['addr_name'] . ', ' . $address['addr_address1']; ?>
                            <?php if (strlen($address['addr_address2']) > 0) {
                                echo ", " . $address['addr_address2']; ?>
                            <?php } ?>
                        </p>
                        <p><?php echo $address['addr_city'] . ", " . $address['state_name'] . ", " . $address['country_name'] . ", " . $address['addr_zip']; ?></p>
                        <?php if (strlen($address['addr_phone']) > 0) { 
                            $addrPhone = ValidateElement::formatDialCode($address['addr_phone_dcode']) . $address['addr_phone'];
                            ?>
                            <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone; ?></p>
                        <?php } ?>
                    </div>
                </div>
                <div class="review-block__link" role="cell">
                    <?php
                    if ($fulfillmentType == Shipping::FULFILMENT_PICKUP || $cartHasPhysicalProduct == false) {
                        $onclick = 'loadAddressDiv(' . Address::ADDRESS_TYPE_BILLING . ');';
                    } else {
                        $onclick = 'loadAddressDiv();';
                    }
                    ?>
                    <a class="link" href="javascript:void(0);" onClick="<?php echo $onclick; ?>"><span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                </div>
            </li>

            <?php if ($fulfillmentType == Shipping::FULFILMENT_PICKUP && !empty($orderPickUpData)) { ?>
                <li class="list-group-item">
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Pickup_Address:', $siteLangId); ?>
                    </div>
                    <div class="review-block__content" role="cell">
                        <div class="delivery-address">
                            <?php foreach ($orderPickUpData as $address) { ?>
                                <p><strong><?php echo ($address['opshipping_by_seller_user_id'] > 0) ? $address['op_shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, ''); ?></strong></p>
                                <p><?php echo $address['oua_name'] . ', ' . $address['oua_address1']; ?>
                                    <?php if (strlen($address['oua_address2']) > 0) {
                                        echo ", " . $address['oua_address2']; ?>
                                    <?php } ?>
                                </p>
                                <p><?php echo $address['oua_city'] . ", " . $address['oua_state'] . ", " . $address['oua_country'] . ", " . $address['oua_zip']; ?></p>
                                <?php if (strlen($address['oua_phone']) > 0) { ?>
                                    <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo ValidateElement::formatDialCode($address['oua_phone_dcode']) . $address['oua_phone']; ?></p>
                                <?php } ?>

                                <?php
                                $fromTime = isset($address["opshipping_time_slot_from"]) && !empty($address["opshipping_time_slot_from"]) ? date('H:i', strtotime($address["opshipping_time_slot_from"])) : '';
                                $toTime = isset($address["opshipping_time_slot_to"]) && !empty($address["opshipping_time_slot_to"]) ? date('H:i', strtotime($address["opshipping_time_slot_to"])) : '';
                                ?>
                                <p class="time-txt">
                                    <i class="fas fa-calendar-day"></i>
                                    <?php
                                    $opshippingDate = isset($address["opshipping_date"]) ? FatDate::format($address["opshipping_date"]) : '';
                                    echo $opshippingDate . ' ' . $fromTime . ' - ' . $toTime;
                                    ?>
                                </p>
                                <?php if (count($orderPickUpData) > 1) { ?>
                                    <a class="link plus-more" href="javascript:void(0);" onClick="orderPickUpData('<?php echo $orderId; ?>')"><?php echo '+ ' . (count($orderPickUpData) - 1) . ' ' . Labels::getLabel('LBL_More', $siteLangId); ?></a>
                                <?php break;
                                } ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="review-block__link" role="cell">
                        <a class="link" href="javascript:void(0);" onClick="loadShippingSummaryDiv();"><span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>
            <?php } ?>

            <?php if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP && !empty($orderShippingData)) { ?>
                <li class="list-group-item">
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Shipping:', $siteLangId); ?>
                    </div>
                    <div class="review-block__content" role="cell">
                        <div class="shipping-data">
                            <ul class="media-more media-more-sm show">
                                <?php foreach ($orderShippingData as $shipData) { ?>
                                    <?php foreach ($shipData as $data) { ?>
                                        <li>
                                            <span class="circle" data-toggle="tooltip" data-placement="top" title="<?php echo $data['op_selprod_title']; ?>" data-original-title="<?php echo $data['op_selprod_title']; ?>">
                                                <img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($data['selprod_product_id'], "THUMB", $data['op_selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $data['op_selprod_title']; ?>">
                                            </span>
                                        </li>
                                    <?php } ?>
                                    <?php if (count($orderShippingData) > 1) { ?>
                                        <li> <span class="circle plus-more" onClick="orderShippingData('<?php echo $orderId; ?>')"><?php echo '+ ' . (count($orderShippingData) - 1); ?></span></li>
                                    <?php }  ?>
                                <?php break;
                                }
                                ?>
                            </ul>

                            <div class="shipping-data_title"><?php echo $data['opshipping_label']; ?></div>

                        </div>
                    </div>
                    <div class="review-block__link" role="cell">
                        <a class="link" href="javascript:void(0);" onClick="loadShippingSummaryDiv();"><span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>
            <?php } ?>

            <?php if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP && $shippingAddressId != $billingAddressId) { ?>
                <li class="list-group-item">
                    <div class="review-block__label">
                        <?php echo Labels::getLabel('LBL_Billing_to:', $siteLangId); ?>
                    </div>
                    <div class="review-block__content" role="cell">
                        <p><?php echo $billingAddressArr['addr_name'] . ', ' . $billingAddressArr['addr_address1']; ?>
                            <?php if (strlen($billingAddressArr['addr_address2']) > 0) {
                                echo ", " . $billingAddressArr['addr_address2']; ?>
                            <?php } ?>
                        </p>
                        <p><?php echo $billingAddressArr['addr_city'] . ", " . $billingAddressArr['state_name']; ?></p>
                        <p><?php echo $billingAddressArr['country_name'] . ", " . $billingAddressArr['addr_zip']; ?></p>
                        <?php if (strlen($billingAddressArr['addr_phone']) > 0) { 
                            $addrPhone = ValidateElement::formatDialCode($billingAddressArr['addr_phone_dcode']) . $billingAddressArr['addr_phone'];
                            ?>
                            <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone; ?></p>
                        <?php } ?>
                    </div>
                    <div class="review-block__link" role="cell">
                        <a class="link" href="javascript:void(0);" onClick="loadAddressDiv(<?php echo Address::ADDRESS_TYPE_BILLING; ?>)"><span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span></a>
                    </div>
                </li>
            <?php  } ?>
        </ul>

        <div class="step__section">
            <div class="step__section__head">
                <h5 class="step__section__head__title"><?php echo Labels::getLabel('LBL_Payment_Summary', $siteLangId); ?></h5>
            </div>
            <?php if ($fulfillmentType == Shipping::FULFILMENT_SHIP && $shippingAddressId == $billingAddressId) { ?>
                <label class="checkbox mb-4"><input onClick="billingAddress(this);" type="checkbox" checked='checked' name="isShippingSameAsBilling" value="1"><?php echo Labels::getLabel('LBL_MY_BILLING_IS_SAME_AS_SHIPPING_ADDRESS', $siteLangId); ?> <i class="input-helper"></i>
                </label>
            <?php } ?>
            <?php if (empty($cartSummary['cartRewardPoints'])) { ?>
                <?php if ($rewardPoints > 0) { ?>
                    <div class="rewards">
                        <div class="rewards__points">
                            <ul>
                                <li>
                                    <p><?php echo Labels::getLabel('LBL_AVAILABLE_REWARDS_POINTS', $siteLangId); ?></p>
                                    <span class="count"><?php echo $rewardPoints; ?></span>
                                </li>
                                <li>
                                    <p><?php echo Labels::getLabel('LBL_POINTS_WORTH', $siteLangId); ?></p>
                                    <span class="count"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($rewardPoints), true, false, true, false, true); ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="info">
                            <span>
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info"></use>
                                </svg>
                                <?php
                                $cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
                                $cartDiscounts = isset($cartSummary['cartDiscounts']["coupon_discount_total"]) ? $cartSummary['cartDiscounts']["coupon_discount_total"] : 0;
                                $canBeUsed = min(min($rewardPoints, CommonHelper::convertCurrencyToRewardPoint($cartTotal - $cartDiscounts)), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));
                                $str = Labels::getLabel('LBL_MAXIMUM_{REWARDS}_REWARDS_POINT_REDEEM_FOR_THIS_ORDER', $siteLangId);
                                echo CommonHelper::replaceStringData($str, ['{REWARDS}' => $canBeUsed]); ?>
                            </span>
                        </div>
                        <?php
                        $redeemRewardFrm->setFormTagAttribute('class', 'form form-inline');
                        $redeemRewardFrm->setFormTagAttribute('onsubmit', 'useRewardPoints(this); return false;');
                        $redeemRewardFrm->setJsErrorDisplay('afterfield');
                        $fld = $redeemRewardFrm->getField('redeem_rewards');
                        $fld->setFieldTagAttribute('class', 'form-control');
                        $fld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Use_Reward_Point', $siteLangId));
                        $fld = $redeemRewardFrm->getField('btn_submit');
                        $fld->setFieldTagAttribute('class', 'btn btn-submit');
                        echo $redeemRewardFrm->getFormTag();  ?>
                        <?php echo $redeemRewardFrm->getFieldHtml('redeem_rewards'); ?>
                        <?php echo $redeemRewardFrm->getFieldHtml('btn_submit'); ?>
                        </form>
                        <?php echo  $redeemRewardFrm->getExternalJs(); ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="info">
                    <span> <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                            </use>
                        </svg> <?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId); ?> <strong><?php echo $cartSummary['cartRewardPoints']; ?>
                            (<?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints']), true, false, true, false, true); ?>)</strong>
                        <?php echo Labels::getLabel('LBL_Successfully_Used', $siteLangId); ?></span>
                    <ul class="list-actions">
                        <li>
                            <a href="javascript:void(0)" onClick="removeRewardPoints()"><svg class="svg" width="24px" height="24px">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                    </use>
                                </svg>
                            </a></li>
                    </ul>
                </div>
            <?php } ?>
        </div>
    </div>
</main>
<?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0 && $canUseWalletForPayment) { ?>
    <div class="wallet-balance">
        <label class="checkbox wallet">
            <input onChange="walletSelection(this)" type="checkbox" <?php echo ($cartSummary["cartWalletSelected"]) ? 'checked="checked"' : ''; ?> name="pay_from_wallet" id="pay_from_wallet" value="1">
            <i class="input-helper"></i>
            <span class="wallet__txt">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wallet" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wallet">
                    </use>
                </svg>
                <div class="">
                    <p><?php echo Labels::getLabel('LBL_AVAILABLE_BALANCE', $siteLangId); ?></p>
                    <span class="currency-value" dir="ltr"><?php echo CommonHelper::displayMoneyFormat($userWalletBalance, true, false, true, false, true); ?></span>
                </div>
            </span>
        </label>
        <?php if ($cartSummary["cartWalletSelected"] && $userWalletBalance >= $cartSummary['orderNetAmount']) {
            $btnSubmitFld = $WalletPaymentForm->getField('btn_submit');
            $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
            $btnSubmitFld->value = Labels::getLabel('LBL_PAY', $siteLangId) . ' ' . CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, false);
            $WalletPaymentForm->developerTags['colClassPrefix'] = 'col-md-';
            $WalletPaymentForm->developerTags['fld_default_col'] = 12;
            echo $WalletPaymentForm->getFormHtml(); ?>
            <script type="text/javascript">
                function confirmOrder(frm) {
                    var data = fcom.frmData(frm);
                    var action = $(frm).attr('action');
                    fcom.updateWithAjax(fcom.makeUrl('Checkout', 'confirmOrder'), data, function(ans) {
                        $(location).attr("href", action);
                    });
                }
            </script>
        <?php } else { ?>
            <div class="wallet-balance_info"><?php echo Labels::getLabel('LBL_USE_MY_WALLET_BALANCE_TO_PAY_FOR_MY_ORDER', $siteLangId); ?></div>
        <?php } ?>
    </div>
<?php } ?>
<section id="payment" class="section-checkout">
    <div class="align-items-center mb-4">
        <?php if ($cartSummary['orderNetAmount'] <= 0) { ?>
            <div class="gap"></div>
            <div id="wallet">
                <h6><?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?>
                    <strong><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, true); ?></strong>
                </h6> <?php
                        $btnSubmitFld = $confirmForm->getField('btn_submit');
                        $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');

                        $confirmForm->developerTags['colClassPrefix'] = 'col-md-';
                        $confirmForm->developerTags['fld_default_col'] = 12;
                        echo $confirmForm->getFormHtml(); ?>
                <div class="gap"></div>
            </div>
        <?php } ?>
    </div>
    <?php
    if ($cartSummary['orderPaymentGatewayCharges']) { ?>
        <div class="payment-area" <?php echo ($cartSummary['orderPaymentGatewayCharges'] <= 0) ? 'is--disabled' : ''; ?>>
            <?php if ($cartSummary['orderPaymentGatewayCharges'] && 0 < count($paymentMethods)) { ?>
                <ul class="nav nav-payments <?php echo 1 == count($paymentMethods) ? 'd-none' : ''; ?>" role="tablist" id="payment_methods_tab">
                    <?php foreach ($paymentMethods as $key => $val) {
                        $pmethodCode = $val['plugin_code'];
                        if ($cartHasDigitalProduct && in_array(strtolower($pmethodCode), ['cashondelivery', 'payatstore'])) {
                            continue;
                        }
                        $pmethodId = $val['plugin_id'];
                        $pmethodName = $val['plugin_name'];

                        if (in_array($pmethodCode, $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
                            continue;
                        } ?>
                        <li class="nav-item">
                            <a class="nav-link" aria-selected="true" href="<?php echo UrlHelper::generateUrl('Checkout', 'PaymentTab', array($orderInfo['order_id'], $pmethodId)); ?>" data-paymentmethod="<?php echo $pmethodCode; ?>">
                                <div class="payment-box">
                                    <span><?php echo $pmethodName; ?></span>
                                </div>
                            </a>
                        </li>
                    <?php
                    } ?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel">
                        <div class="tabs-container" id="tabs-container"></div>
                    </div>
                </div>
            <?php } else {
                echo Labels::getLabel("LBL_PAYMENT_METHOD_IS_NOT_AVAILABLE._PLEASE_CONTACT_YOUR_ADMINISTRATOR.", $siteLangId);
            } ?>
        </div>
    <?php } ?>
</section>

<script>
    var enableGcaptcha = false;
</script>
<?php
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
$paymentMethods = new PaymentMethods();
if (!empty($siteKey) && !empty($secretKey) && true === $paymentMethods->cashOnDeliveryIsActive()) { ?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
    <script>
        var enableGcaptcha = true;
    </script>
<?php } ?>

<?php if ($cartSummary['orderPaymentGatewayCharges']) { ?>
    <script type="text/javascript">
        var tabsId = '#payment_methods_tab';
        $(document).ready(function() {
            $(tabsId + " li:first a").addClass('active');
            if ($(tabsId + ' li a.active').length > 0) {
                loadTab($(tabsId + ' li a.active'));
            }
            $(tabsId + ' a').click(function() {
                if ($(this).hasClass('active')) {
                    return false;
                }
                $(tabsId + ' li a.active').removeClass('active');
                $(this).addClass('active');
                loadTab($(this));
                return false;
            });
        });

        function loadTab(tabObj) {
            if (isUserLogged() == 0) {
                loginPopUpBox();
                return false;
            }
            if (!tabObj || !tabObj.length) {
                return;
            }
	
            fcom.ajax(tabObj.attr('href'), '', function(response) {
                var paymentMethod = tabObj.data('paymentmethod');
				if ('paypal' != paymentMethod.toLowerCase() && 0 < $("#paypal-buttons").length) {
					$("#paypal-buttons").html("");	
				}
				
				$('#tabs-container').html(response);
                if ('cashondelivery' == paymentMethod.toLowerCase() || 'payatstore' == paymentMethod.toLowerCase()) {
                    if (true == enableGcaptcha) {
                        googleCaptcha();
                    }
                    $.mbsmessage.close();
                } else {
                    var form = '#tabs-container form';
                    if (0 < $(form).length) {
                        $('#tabs-container').append(fcom.getLoader());
                        if (0 < $(form + " input[type='submit']").length) {
                            $(form + " input[type='submit']").val(langLbl.requestProcessing);
                        }
                        setTimeout(function() {
                            $(form).submit()
                        }, 100);
                    }
                }
            });
        }
    </script>
<?php }
