<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmOrderSrch->setFormTagAttribute('onSubmit', 'searchOrders(this); return false;');
$frmOrderSrch->setFormTagAttribute('class', 'form');
$frmOrderSrch->developerTags['colClassPrefix'] = 'col-lg-';
$frmOrderSrch->developerTags['fld_default_col'] = 4;

$keywordFld = $frmOrderSrch->getField('keyword');
$keywordFld->developerTags['col'] = 4;
$keywordFld->developerTags['noCaptionTag'] = true;
/* $keywordFld->htmlAfterField = '<small class="form-text text-muted">'.Labels::getLabel('LBL_Buyer_account_orders_listing_search_form_keyword_help_txt', $siteLangId).'</small>'; */

/* $statusFld = $frmOrderSrch->getField('status');
$statusFld->developerTags['col'] = 4; */

$dateFromFld = $frmOrderSrch->getField('date_from');
$dateFromFld->setFieldTagAttribute('class', 'field--calender');
$dateFromFld->developerTags['col'] = 2;
$dateFromFld->developerTags['noCaptionTag'] = true;

$dateToFld = $frmOrderSrch->getField('date_to');
$dateToFld->setFieldTagAttribute('class', 'field--calender');
$dateToFld->developerTags['col'] = 2;
$dateToFld->developerTags['noCaptionTag'] = true;

/* $priceFromFld = $frmOrderSrch->getField('price_from');
$priceFromFld->developerTags['col'] = 2;

$priceToFld = $frmOrderSrch->getField('price_to');
$priceToFld->developerTags['col'] = 2; */

$submitBtnFld = $frmOrderSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->setWrapperAttribute('class', 'col-6');
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmOrderSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->setWrapperAttribute('class', 'col-6');
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Subscriptions', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Search_Subscriptions', $siteLangId);?></h5>
                            <?php if ($currentActivePlan) {
                                if (strtotime(date("Y-m-d"))>=strtotime('-3 day', strtotime($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date']))) {
                                    if ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])>0) {
                                        $message = sprintf(Labels::getLabel('MSG_Your_Subscription_is_going_to_expire_in_%s_day(s),Please_maintain_your_wallet_to_continue_your_subscription,_Amount_required_%s', $siteLangId), FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date']), CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                    } elseif ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])==0) {
                                        $message = sprintf(Labels::getLabel('MSG_Your_Subscription_is_going_to_expire_today,_Please_maintain_your_wallet_to_continue_your_subscription,_Amount_required_%s', $siteLangId), CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                    } elseif ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])<0 && $autoRenew) {
                                        $message = sprintf(Labels::getLabel('MSG_Your_Subscription_has_been_expired,Please_purchase_new_plan_or_maintain_your_wallet_to_continue_your_subscription,_Amount_required_%s', $siteLangId), CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                    } elseif ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])<0  && !$autoRenew) {
                                        $message = sprintf(Labels::getLabel('MSG_Your_Subscription_has_been_expired,Please_purchase_new_plan_or_add_%s_in_your_wallet_before_renewing_your_subscription', $siteLangId), CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                    } elseif ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])>0) {
                                        $message = sprintf(Labels::getLabel('MSG_Your_Free_Subscription_is_going_to_expire_in_%s_day(s),Please_Purchase_new_Subscription_to_continue_services', $siteLangId), FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date']));
                                    } elseif ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])==0) {
                                        $message = sprintf(Labels::getLabel('MSG_Your_Free_Subscription_is_going_to_expire_today,_Please_Purchase_new_Subscription_to_continue_services', $siteLangId));
                                    } elseif ($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE && FatDate::diff(date("Y-m-d"), $currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])<0) {
                                        $message = Labels::getLabel('MSG_Your_Free_Subscription_has_been_expired,Please_Purchase_new_Subscription_to_continue_services', $siteLangId);
                                    } ?> <?php
                                }
                            } ?>
                            <?php if($canEdit) {?>
                            <div class="auto-renew text-right">
                                <p><?php echo Labels::getLabel('LBL_AutoRenew_Subscription', $siteLangId); ?></p>
                                <?php
                                $active = "";
                                if ($autoRenew) {
                                    $active = 'checked';
                                }
                                $onOffArr = applicationConstants::getOnOffArr($siteLangId); ?>
                                <label class="toggle-switch mb-0">
                                    <input <?php echo $active; ?> type="checkbox" onclick="toggleAutoRenewal()">
                                    <div class="slider round"></div>
                                </label>
                            </div>
                            <?php } ?>
                        </div>
                        <?php if (isset($message)) { ?>
                            <p class="highlighted-note"> <?php  echo $message;?> </p>
                        <?php }?>
                        <div class="card-body ">
                            <?php echo $frmOrderSrch->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                        </div>
                        <div class="card-body ">
                            <div id="ordersListing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
