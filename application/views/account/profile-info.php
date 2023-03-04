<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Account_Settings', $siteLangId);?>
                </h2>
            </div>
            <?php if (0 == $userParentId) { ?>
            <div class="col-auto">
                <div class="btn-group">
                    <a class="btn btn-outline-brand btn-sm" href="javascript:void(0)" onclick="truncateDataRequestPopup()"><?php echo Labels::getLabel('LBL_Request_to_remove_my_data', $siteLangId); ?></a>
                    <a class="btn btn-outline-brand btn-sm" href="javascript:void(0)" onclick="requestData()"><?php echo Labels::getLabel('LBL_Request_My_Data', $siteLangId); ?></a>
                    <?php if ($showSellerActivateButton) { ?>
                    <a href="<?php echo UrlHelper::generateUrl('Seller'); ?>"
                            class="btn btn-outline-brand btn-sm panel__head_action"
                            title="<?php echo Labels::getLabel('LBL_Activate_Seller_Account', $siteLangId); ?>">
                            <strong> <?php echo Labels::getLabel('LBL_Activate_Seller_Account', $siteLangId); ?></strong>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body ">
                            <?php if ($userParentId == 0) { ?>
                            <div class="tabs ">
                                <ul class="tabs-js">
                                    <li class="is-active" id="tab-myaccount">
                                        <a href="javascript:void(0);" onClick="profileInfoForm()">
                                            <?php echo Labels::getLabel('LBL_My_Account', $siteLangId);?>
                                        </a>
                                    </li>
                                    <?php if (User::isAffiliate()) { ?>
                                    <li id="tab-paymentinfo">
                                        <a href="javascript:void(0);" onClick="affiliatePaymentInfoForm()"><?php echo Labels::getLabel('LBL_Payment_Info', $siteLangId); ?></a>
                                    </li>
                                    <?php }
                                    if (!User::isAffiliate()) { ?>
                                        <li id="tab-bankaccount">
                                            <a href="javascript:void(0);" onClick="bankInfoForm()"><?php echo Labels::getLabel('LBL_Bank_Account', $siteLangId); ?></a>
                                        </li>
                                    <?php } ?>
                                    <?php
                                    foreach ($payouts as $type => $name) { ?>
                                        <li id="tab-<?php echo $type; ?>">
                                            <a href="javascript:void(0);" onClick="pluginForm('<?php echo $type; ?>')">
                                                <?php echo $name; ?>
                                            </a>
                                        </li>
                                    <?php }
                                    ?>
                                </ul>
                            </div>
                            <?php } ?>
                            <div id="profileInfoFrmBlock">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
