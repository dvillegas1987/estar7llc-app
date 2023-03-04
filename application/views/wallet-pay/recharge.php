<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php $gatewayCount = 0;
foreach ($paymentMethods as $key => $val) {
    if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_ADD_MONEY_TO_WALLET])) {
        unset($paymentMethods[$key]);
        continue;
    }
    $gatewayCount++;
} ?>
<section class="section bg-gray-dark">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-head  section--head--center">
                    <div class="section__heading">
                        <h2><?php echo Labels::getLabel('LBL_ADD_MONEY_TO_WALLET', $siteLangId); ?></h2>
                    </div>
                </div>
                <?php if ($orderInfo['order_net_amount']) { ?>
                    <?php if ($gatewayCount > 0) { ?>
                        <div class="col-md-8">
                            <div class="you-pay">
                                <?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?> : <?php echo CommonHelper::displayMoneyFormat($orderInfo['order_net_amount'], true, false, true, false, true); ?>
                                <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                                    <p><?php echo CommonHelper::currencyDisclaimer($siteLangId, $orderInfo['order_net_amount']);  ?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <section id="payment" class="section-checkout">
                                <div class="payment-area">
                                    <ul class="nav nav-payments <?php echo 1 == count($paymentMethods) ? 'd-none' : ''; ?>" role="tablist" id="payment_methods_tab">
                                        <?php foreach ($paymentMethods as $key => $val) {
                                            $pmethodCode = $val['plugin_code'];
                                            $pmethodId = $val['plugin_id'];
                                            $pmethodName = $val['plugin_name']; ?>
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
                                </div>
                            </section>
                        </div>
                    <?php } else {
                        echo Labels::getLabel("LBL_Payment_method_is_not_available._Please_contact_your_administrator.", $siteLangId);
                    } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php if ($orderInfo['order_net_amount']) { ?>
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

        sendPayment = function(frm, dv = '') {
            var data = fcom.frmData(frm);
            var action = $(frm).attr('action');
            fcom.ajax(action, data, function(t) {
                // debugger;
                try {
                    var json = $.parseJSON(t);
                    if (typeof json.status != 'undefined' && 1 > json.status) {
                        $.systemMessage(json.msg, 'alert--danger');
                        return false;
                    }
                    if (typeof json.html != 'undefined') {
                        $(dv).append(json.html);
                    }
                    if (json['redirect']) {
                        $(location).attr("href", json['redirect']);
                    }
                } catch (e) {
                    $(dv).append(t);
                }
            });
        };
    </script>
<?php } ?>