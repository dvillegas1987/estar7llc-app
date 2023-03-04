<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="main">
    <main class="main__content">
        <div class="step active">
            <div class="step__section">
                <div class="step__section__head"><?php echo Labels::getLabel('LBL_Payment_Summary', $siteLangId); ?>
                </div>
                <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0 && $canUseWalletForPayment) { ?>
                    <div class="wallet-balance">
                        <label class="checkbox wallet">
                            <input onChange="walletSelection(this)" type="checkbox" <?php echo ($cartSummary["cartWalletSelected"]) ? 'checked="checked"' : ''; ?> name="pay_from_wallet" id="pay_from_wallet" />
                            <i class="input-helper"></i>
                            <span class="wallet__txt">
                                <svg class="svg">
                                    <use xlink:href="/yokart/images/retina/sprite.svg#wallet" href="/yokart/images/retina/sprite.svg#wallet">
                                    </use>
                                </svg>
                                <div class="">
                                    <p><?php echo Labels::getLabel('LBL_AVAILABLE_BALANCE', $siteLangId); ?></p>
                                    <span class="currency-value" dir="ltr"><span class="currency-value" dir="ltr"><span class="currency-symbol"><?php echo CommonHelper::displayMoneyFormat($userWalletBalance, true, false, true, false, true) ?></span>
                                </div>
                            </span>
                        </label>
                        <div class="wallet-balance_info">
                            <?php if ($cartSummary["cartWalletSelected"]) {
                                $remainingWalletBalance = ($userWalletBalance - $cartSummary['orderNetAmount']);
                                $remainingWalletBalance = ($remainingWalletBalance < 0) ? 0 : $remainingWalletBalance;
                                echo Labels::getLabel('LBL_Remaining_wallet_balance', $siteLangId) . ' ' . CommonHelper::displayMoneyFormat($remainingWalletBalance, true, false, true, false, true);
                            } else {
                                echo Labels::getLabel('LBL_USE_MY_WALLET_BALANCE_TO_PAY_FOR_MY_ORDER', $siteLangId);
                            } ?>
                        </div>
                    </div>
                <?php } ?>
                <?php if ($subscriptionType == SellerPackages::PAID_TYPE && $canUseWalletForPayment) { ?>
                    <p class="note">
                        <?php echo Labels::getLabel('LBL_Note_Please_Maintain_Wallet_Balance_for_further_auto_renewal_payments', $siteLangId); ?>
                    </p>
                <?php } ?>

                <div class="">
                    <section id="payment" class="section-checkout">
                        <div class="align-items-center mb-4">
                            <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0 && $canUseWalletForPayment) { ?>
                                <?php if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary["cartWalletSelected"]) { ?>

                                    <?php $btnSubmitFld = $WalletPaymentForm->getField('btn_submit');
                                    $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-outline-brand');

                                    $WalletPaymentForm->developerTags['colClassPrefix'] = 'col-md-';
                                    $WalletPaymentForm->developerTags['fld_default_col'] = 12;
                                    echo $WalletPaymentForm->getFormHtml(); ?>

                                    <script type="text/javascript">
                                        function confirmOrder(frm) {
                                            var data = fcom.frmData(frm);
                                            var action = $(frm).attr('action')
                                            fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout',
                                                    'confirmOrder'), data,
                                                function(ans) {
                                                    $(location).attr("href", action);
                                                });
                                        }
                                    </script>
                                <?php } ?>
                            <?php }


                            if ($cartSummary['orderNetAmount'] <= 0) { ?>
                                <div class="gap"></div>
                                <div>
                                    <h6>
                                        <strong> <?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?> </strong>
                                        <?php
                                        $btnSubmitFld = $confirmPaymentFrm->getField('btn_submit');
                                        $btnSubmitFld->addFieldTagAttribute('class', 'btn btn-brand');

                                        $confirmPaymentFrm->developerTags['colClassPrefix'] = 'col-md-';
                                        $confirmPaymentFrm->developerTags['fld_default_col'] = 12;
                                        echo $confirmPaymentFrm->getFormHtml(); ?>
                                        <div class="gap"></div>
                                        <script type="text/javascript">
                                            function confirmOrder(frm) {
                                                var data = fcom.frmData(frm);
                                                var action = $(frm).attr('action')
                                                fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'confirmOrder'),
                                                    data,
                                                    function(ans) {
                                                        $(location).attr("href", action);
                                                    });
                                            }
                                        </script>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                        $gatewayCount = 0;
                        foreach ($paymentMethods as $key => $val) {
                            if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_SUBSCRIPTION])) {
                                unset($paymentMethods[$key]);
                                continue;
                            }
                            $gatewayCount++;
                        }
                        
                        if ($cartSummary['orderPaymentGatewayCharges']) { ?>
                            <div class="payment-area" <?php echo ($cartSummary['orderPaymentGatewayCharges'] <= 0) ? 'is--disabled' : ''; ?>>
                                <?php if ($cartSummary['orderPaymentGatewayCharges'] && 0 < $gatewayCount && 0 < count($paymentMethods)) { ?>
                                    <?php if ($paymentMethods) { ?>
                                        <ul class="nav nav-payments <?php echo 1 == count($paymentMethods) ? 'd-none' : ''; ?>" id="payment_methods_tab">
                                            <?php foreach ($paymentMethods as $key => $val) {
                                                if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_SUBSCRIPTION])) {
                                                    continue;
                                                }
                                                $pmethodCode = $val['plugin_code'];
                                                $pmethodId = $val['plugin_id'];
                                                $pmethodName = (isset($val['plugin_name']) && !empty($val['plugin_name'])) ? $val['plugin_name'] : $val['plugin_identifier'];
                                                /* if(strtolower($val['plugin_code']) == 'cashondelivery' && $fulfillmentType == Shipping::FULFILMENT_PICKUP){
                                    $pmethodName = Labels::getLabel('LBL_Pay_on_pickup', $siteLangId);
                                } */

                                                if (in_array($pmethodCode, $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
                                                    continue;
                                                } ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" aria-selected="true" href="<?php echo UrlHelper::generateUrl('SubscriptionCheckout', 'PaymentTab', array($orderInfo['order_id'], $pmethodId)); ?>" data-paymentmethod="<?php echo $pmethodCode; ?>">
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
                                    <?php } ?>
                                <?php } else {
                                    echo Labels::getLabel("LBL_Payment_method_is_not_available._Please_contact_your_administrator.", $siteLangId);
                                } ?>
                            </div>
                        <?php } ?>
                    </section>
                </div>
            </div>
        </div>
    </main>
</div>
<aside class="sidebar" role="complementary">
    <div class="sidebar__content">
        <div id="order-summary" class="order-summary summary-listing-js">
            <h5 class="mb-2"><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?></h5>
            <div class="order-summary__sections">
                <div class="order-summary__section order-summary__section--total-lines">
                    <div class="cart-total my-3">
                        <div class="">
                            <ul class="list-group list-group-flush list-group-flush-x">
                                <li class="list-group-item">
                                    <span class="label"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></span>
                                    <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal'], true, false, true, false, true); ?></span>
                                </li>
                                <?php if ($cartSummary['cartAdjustableAmount'] > 0) { ?>
                                    <li class="list-group-item ">
                                        <span class="label"><?php echo Labels::getLabel('LBL_Adjusted_Amount', $siteLangId); ?></span>
                                        <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartAdjustableAmount'], true, false, true, false, true); ?></span>
                                    </li>
                                <?php } ?>
                                <?php
                                $cartDiscounts = isset($cartSummary['cartDiscounts']["coupon_discount_total"]) ? $cartSummary['cartDiscounts']["coupon_discount_total"] : 0;
                                if ($cartDiscounts > 0) { ?>
                                    <li class="list-group-item ">
                                        <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
                                        <span class="mleft-auto">
                                            <?php echo CommonHelper::displayMoneyFormat($cartDiscounts, true, false, true, false, true); ?></span>
                                    </li>
                                <?php } ?>
                                <li class="list-group-item hightlighted">
                                    <span class="label"><?php echo Labels::getLabel('LBL_You_Pay', $siteLangId); ?></span>
                                    <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], true, false, true, false, true); ?>
                                    </span>
                                </li>
                            </ul>

                        </div>
                    </div>

                </div>
            </div>

        </div>
        <?php //echo FatUtility::decodeHtmlEntities($pageData['epage_content']);
        ?>
    </div>
</aside>

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
                $('#tabs-container').html(response);
                var paymentMethod = tabObj.data('paymentmethod');
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
            });
        }
    </script>
<?php }
